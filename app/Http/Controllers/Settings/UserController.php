<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\UserVaultKey;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get password validation rules based on system settings
     */
    private function getPasswordRules(bool $required = true): array
    {
        $minLength = SystemSetting::getValue('security', 'password_min_length', 8);
        
        $rules = [];
        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        $rules[] = 'confirmed';
        $rules[] = 'min:' . $minLength;
        
        return $rules;
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(SystemSetting::paginationSize())->withQueryString();
        $roles = Role::active()->orderBy('name')->get();

        return view('settings.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::active()->orderBy('name')->get();
        $passwordMinLength = SystemSetting::getValue('security', 'password_min_length', 8);
        return view('settings.users.create', compact('roles', 'passwordMinLength'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => $this->getPasswordRules(true),
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'],
        ]);

        try {
            ActivityLogService::logCreate($user, 'settings_users', "Created user: {$user->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('role');
        $vaultKey = UserVaultKey::where('user_id', $user->id)->first();

        try {
            ActivityLogService::logView($user, 'settings_users', "Viewed user: {$user->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return view('settings.users.show', compact('user', 'vaultKey'));
    }

    public function edit(User $user)
    {
        $roles = Role::active()->orderBy('name')->get();
        $passwordMinLength = SystemSetting::getValue('security', 'password_min_length', 8);
        return view('settings.users.edit', compact('user', 'roles', 'passwordMinLength'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => $this->getPasswordRules(false),
            'role_id' => 'nullable|exists:roles,id',
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $user->only(['name', 'email', 'role_id', 'is_active']);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        try {
            ActivityLogService::logUpdate($user, 'settings_users', $oldValues, "Updated user: {$user->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('settings.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;

        try {
            ActivityLogService::logDelete($user, 'settings_users', "Deleted user: {$userName}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
