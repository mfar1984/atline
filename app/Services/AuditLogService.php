<?php

namespace App\Services;

use App\Models\CredentialAuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log vault unlock action
     */
    public function logUnlock(int $userId, Request $request): CredentialAuditLog
    {
        return $this->log($userId, null, CredentialAuditLog::ACTION_UNLOCK, $request);
    }

    /**
     * Log credential view action
     */
    public function logView(int $userId, int $credentialId, Request $request): CredentialAuditLog
    {
        return $this->log($userId, $credentialId, CredentialAuditLog::ACTION_VIEW, $request);
    }

    /**
     * Log credential create action
     */
    public function logCreate(int $userId, int $credentialId, Request $request): CredentialAuditLog
    {
        return $this->log($userId, $credentialId, CredentialAuditLog::ACTION_CREATE, $request);
    }

    /**
     * Log credential update action
     */
    public function logUpdate(int $userId, int $credentialId, Request $request): CredentialAuditLog
    {
        return $this->log($userId, $credentialId, CredentialAuditLog::ACTION_UPDATE, $request);
    }

    /**
     * Log credential delete action
     */
    public function logDelete(int $userId, int $credentialId, Request $request): CredentialAuditLog
    {
        return $this->log($userId, $credentialId, CredentialAuditLog::ACTION_DELETE, $request);
    }

    /**
     * Log PIN rotation action
     */
    public function logPinRotate(int $userId, Request $request = null): CredentialAuditLog
    {
        return CredentialAuditLog::create([
            'user_id' => $userId,
            'credential_id' => null,
            'action' => CredentialAuditLog::ACTION_PIN_ROTATE,
            'ip_address' => $request?->ip() ?? 'system',
            'user_agent' => $request?->userAgent() ?? 'PIN Rotation Job',
        ]);
    }

    /**
     * Create audit log entry
     */
    protected function log(int $userId, ?int $credentialId, string $action, Request $request): CredentialAuditLog
    {
        return CredentialAuditLog::create([
            'user_id' => $userId,
            'credential_id' => $credentialId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
