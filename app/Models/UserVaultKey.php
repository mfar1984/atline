<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVaultKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'encrypted_mek',
        'mek_iv',
        'pin_hash',
        'pin_salt',
        'current_pin',
        'pin_expires_at',
        'failed_attempts',
        'locked_until',
        'is_initialized',
    ];

    protected $casts = [
        'pin_expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_initialized' => 'boolean',
        'failed_attempts' => 'integer',
    ];

    protected $hidden = [
        'pin_hash',
        'pin_salt',
        'encrypted_mek',
        'mek_iv',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isPinExpired(): bool
    {
        return $this->pin_expires_at && $this->pin_expires_at->isPast();
    }

    public function incrementFailedAttempts(): void
    {
        $this->failed_attempts++;
        
        if ($this->failed_attempts >= 5) {
            $this->locked_until = now()->addMinutes(15);
        }
        
        $this->save();
    }

    public function resetFailedAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }
}
