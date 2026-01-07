<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Credential;
use App\Models\UserVaultKey;
use App\Models\CredentialAuditLog;
use App\Services\ActivityLogService;
use App\Services\CredentialEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialController extends Controller
{
    protected $encryptionService;

    public function __construct(CredentialEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    /**
     * Display credentials list
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();
        
        $query = Credential::with('creator')->where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $credentials = $query->orderByDesc('created_at')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
        $types = Credential::getTypeOptions();

        return view('internal.credentials.index', compact('credentials', 'types', 'vaultKey'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = Auth::user();
        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();
        
        if (!$vaultKey || !$vaultKey->is_initialized) {
            return redirect()->route('internal.credentials.index')
                ->with('error', 'Please initialize your vault first.');
        }

        $types = Credential::getTypeOptions();
        return view('internal.credentials.create', compact('types', 'vaultKey'));
    }

    /**
     * Store new credential
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ssh,windows,license_key,database,api_key,other',
            'encrypted_data' => 'required|string',
            'data_iv' => 'required|string|size:24',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $credential = Credential::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'encrypted_data' => $validated['encrypted_data'],
            'data_iv' => $validated['data_iv'],
            'notes' => $validated['notes'],
            'created_by' => $user->id,
        ]);

        // Log the action
        $this->logAction($user->id, $credential->id, 'create', $request);
        
        // Log activity (without sensitive data)
        try {
            ActivityLogService::logCreate($credential, 'internal_credentials', "Created credential {$credential->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.credentials.index')
            ->with('success', 'Credential created successfully.');
    }

    /**
     * Show credential detail
     */
    public function show(Credential $credential)
    {
        $user = Auth::user();
        
        if ($credential->user_id !== $user->id) {
            abort(403);
        }

        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();
        $types = Credential::getTypeOptions();
        
        // Log view activity
        try {
            ActivityLogService::logView($credential, 'internal_credentials', "Viewed credential {$credential->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return view('internal.credentials.show', compact('credential', 'vaultKey', 'types'));
    }

    /**
     * Show edit form
     */
    public function edit(Credential $credential)
    {
        $user = Auth::user();
        
        if ($credential->user_id !== $user->id) {
            abort(403);
        }

        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();
        $types = Credential::getTypeOptions();

        return view('internal.credentials.edit', compact('credential', 'vaultKey', 'types'));
    }

    /**
     * Update credential
     */
    public function update(Request $request, Credential $credential)
    {
        $user = Auth::user();
        
        if ($credential->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:ssh,windows,license_key,database,api_key,other',
            'encrypted_data' => 'required|string',
            'data_iv' => 'required|string|size:24',
            'notes' => 'nullable|string',
        ]);

        $credential->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'encrypted_data' => $validated['encrypted_data'],
            'data_iv' => $validated['data_iv'],
            'notes' => $validated['notes'],
        ]);

        // Log the action
        $this->logAction($user->id, $credential->id, 'update', $request);
        
        // Log activity (without sensitive data)
        try {
            ActivityLogService::logUpdate($credential, 'internal_credentials', ['name' => $credential->name], "Updated credential {$credential->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.credentials.show', $credential)
            ->with('success', 'Credential updated successfully.');
    }

    /**
     * Delete credential
     */
    public function destroy(Request $request, Credential $credential)
    {
        $user = Auth::user();
        
        if ($credential->user_id !== $user->id) {
            abort(403);
        }

        // Log the action before deletion
        $this->logAction($user->id, $credential->id, 'delete', $request);
        
        // Log activity (without sensitive data)
        try {
            ActivityLogService::logDelete($credential, 'internal_credentials', "Deleted credential {$credential->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $credential->delete();

        return redirect()->route('internal.credentials.index')
            ->with('success', 'Credential deleted successfully.');
    }

    /**
     * Initialize vault for user - Step 1: Generate PIN and salt
     */
    public function initializeVault(Request $request)
    {
        $user = Auth::user();
        
        // Check if already initialized
        $existingKey = UserVaultKey::where('user_id', $user->id)->first();
        if ($existingKey && $existingKey->is_initialized) {
            return response()->json(['error' => 'Vault already initialized'], 400);
        }

        // Check if this is step 1 (get PIN) or step 2 (save encrypted MEK)
        if (!$request->has('encrypted_mek')) {
            // Step 1: Generate PIN and salt, return to browser
            $pin = $this->encryptionService->generatePin();
            $salt = $this->encryptionService->generateSalt();
            
            // Store PIN hash temporarily (not initialized yet)
            $pinHash = $this->encryptionService->hashPin($pin, $salt);
            
            UserVaultKey::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'encrypted_mek' => '',
                    'mek_iv' => '',
                    'pin_hash' => $pinHash,
                    'pin_salt' => $salt,
                    'current_pin' => $pin,
                    'pin_expires_at' => now()->addDay(),
                    'is_initialized' => false,
                    'failed_attempts' => 0,
                    'locked_until' => null,
                ]
            );

            return response()->json([
                'success' => true,
                'step' => 1,
                'pin' => $pin,
                'salt' => $salt,
            ]);
        }

        // Step 2: Save encrypted MEK
        $validated = $request->validate([
            'encrypted_mek' => 'required|string',
            'mek_iv' => 'required|string|size:24',
        ]);

        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();
        if (!$vaultKey) {
            return response()->json(['error' => 'Please start initialization first'], 400);
        }

        $vaultKey->update([
            'encrypted_mek' => $validated['encrypted_mek'],
            'mek_iv' => $validated['mek_iv'],
            'is_initialized' => true,
        ]);

        return response()->json([
            'success' => true,
            'step' => 2,
            'message' => 'Vault initialized successfully.',
        ]);
    }

    /**
     * Verify PIN and return encrypted MEK
     */
    public function verifyPin(Request $request)
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:8',
        ]);

        $user = Auth::user();
        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();

        if (!$vaultKey || !$vaultKey->is_initialized) {
            return response()->json(['error' => 'Vault not initialized'], 400);
        }

        // Check if locked
        if ($vaultKey->isLocked()) {
            $minutes = $vaultKey->locked_until->diffInMinutes(now());
            return response()->json([
                'error' => "Account locked. Try again in {$minutes} minutes.",
                'locked' => true,
            ], 403);
        }

        // Verify PIN
        if (!$this->encryptionService->verifyPin($validated['pin'], $vaultKey->pin_salt, $vaultKey->pin_hash)) {
            $vaultKey->incrementFailedAttempts();
            
            $remaining = 5 - $vaultKey->failed_attempts;
            return response()->json([
                'error' => "Invalid PIN. {$remaining} attempts remaining.",
                'attempts_remaining' => $remaining,
            ], 401);
        }

        // Reset failed attempts on success
        $vaultKey->resetFailedAttempts();

        // Log unlock
        $this->logAction($user->id, null, 'unlock', $request);

        return response()->json([
            'success' => true,
            'encrypted_mek' => $vaultKey->encrypted_mek,
            'mek_iv' => $vaultKey->mek_iv,
            'pin_salt' => $vaultKey->pin_salt,
        ]);
    }

    /**
     * Get encrypted credential data for decryption
     */
    public function getEncryptedData(Request $request, Credential $credential)
    {
        $user = Auth::user();
        
        if ($credential->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Log view action
        $this->logAction($user->id, $credential->id, 'view', $request);

        return response()->json([
            'encrypted_data' => $credential->encrypted_data,
            'data_iv' => $credential->data_iv,
        ]);
    }

    /**
     * Get current PIN (for display)
     */
    public function getCurrentPin()
    {
        $user = Auth::user();
        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();

        if (!$vaultKey || !$vaultKey->is_initialized) {
            return response()->json(['error' => 'Vault not initialized'], 400);
        }

        // For security, we don't store plaintext PIN
        // This endpoint is only for checking vault status
        return response()->json([
            'is_initialized' => $vaultKey->is_initialized,
            'pin_expires_at' => $vaultKey->pin_expires_at->toIso8601String(),
            'is_expired' => $vaultKey->isPinExpired(),
        ]);
    }

    /**
     * Log credential action
     */
    protected function logAction($userId, $credentialId, $action, Request $request)
    {
        CredentialAuditLog::create([
            'user_id' => $userId,
            'credential_id' => $credentialId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
