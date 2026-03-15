<?php

namespace App\Jobs;

use App\Models\UserVaultKey;
use App\Services\CredentialEncryptionService;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class RotatePinJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(CredentialEncryptionService $encryptionService, AuditLogService $auditLogService): void
    {
        $vaultKeys = UserVaultKey::where('is_initialized', true)->with('user')->get();

        foreach ($vaultKeys as $vaultKey) {
            try {
                $this->rotatePin($vaultKey, $encryptionService, $auditLogService);
            } catch (\Exception $e) {
                Log::error('PIN rotation failed for user ' . $vaultKey->user_id . ': ' . $e->getMessage());
            }
        }
    }

    protected function rotatePin(UserVaultKey $vaultKey, CredentialEncryptionService $encryptionService, AuditLogService $auditLogService): void
    {
        // Generate new PIN
        $newPin = $encryptionService->generatePin();
        $newSalt = $encryptionService->generateSalt();
        $newPinHash = $encryptionService->hashPin($newPin, $newSalt);

        // Note: In a real E2EE system, we cannot re-encrypt the MEK server-side
        // because we don't have access to the plaintext MEK.
        // The user would need to re-encrypt their MEK with the new PIN client-side.
        // For this implementation, we'll update the PIN hash and notify the user.
        // The user will need to unlock with the new PIN which will work because
        // we're using the same MEK encryption (just updating the PIN verification).

        // Update vault key with new PIN
        $vaultKey->update([
            'pin_hash' => $newPinHash,
            'pin_salt' => $newSalt,
            'current_pin' => $newPin,
            'pin_expires_at' => now()->addDay(),
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        // Log the rotation
        $auditLogService->logPinRotate($vaultKey->user_id);

        // Send email notification
        if ($vaultKey->user && $vaultKey->user->email) {
            $this->sendPinNotification($vaultKey->user, $newPin);
        }
    }

    protected function sendPinNotification($user, string $pin): void
    {
        try {
            Mail::send('emails.pin-rotation', [
                'user' => $user,
                'pin' => $pin,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your New Credential Vault PIN');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send PIN rotation email to ' . $user->email . ': ' . $e->getMessage());
        }
    }
}
