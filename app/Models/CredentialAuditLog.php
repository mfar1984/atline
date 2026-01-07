<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CredentialAuditLog extends Model
{
    use HasFactory;

    const ACTION_UNLOCK = 'unlock';
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_PIN_ROTATE = 'pin_rotate';

    const ACTIONS = [
        self::ACTION_UNLOCK => 'Vault Unlocked',
        self::ACTION_VIEW => 'Credential Viewed',
        self::ACTION_CREATE => 'Credential Created',
        self::ACTION_UPDATE => 'Credential Updated',
        self::ACTION_DELETE => 'Credential Deleted',
        self::ACTION_PIN_ROTATE => 'PIN Rotated',
    ];

    protected $fillable = [
        'user_id',
        'credential_id',
        'action',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credential()
    {
        return $this->belongsTo(Credential::class);
    }

    public function getActionNameAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? 'Unknown';
    }
}
