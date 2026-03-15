<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'reason',
        'failed_attempts',
        'banned_at',
        'expires_at',
        'banned_by',
        'is_permanent',
        'notes',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    /**
     * Get the user who banned this IP.
     */
    public function bannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Check if the ban is still active.
     */
    public function isActive(): bool
    {
        if ($this->is_permanent) {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if an IP address is banned.
     */
    public static function isBanned(string $ipAddress): bool
    {
        $ban = static::where('ip_address', $ipAddress)->first();

        if (!$ban) {
            return false;
        }

        // Check if ban is still active
        if (!$ban->isActive()) {
            // Auto-delete expired bans
            $ban->delete();
            return false;
        }

        return true;
    }

    /**
     * Ban an IP address.
     */
    public static function banIp(
        string $ipAddress,
        string $reason = 'Too many failed attempts',
        int $failedAttempts = 0,
        ?int $bannedBy = null,
        bool $isPermanent = false,
        ?string $notes = null,
        ?int $durationMinutes = 60
    ): self {
        $expiresAt = $isPermanent ? null : now()->addMinutes($durationMinutes);

        return static::updateOrCreate(
            ['ip_address' => $ipAddress],
            [
                'reason' => $reason,
                'failed_attempts' => $failedAttempts,
                'banned_at' => now(),
                'expires_at' => $expiresAt,
                'banned_by' => $bannedBy,
                'is_permanent' => $isPermanent,
                'notes' => $notes,
            ]
        );
    }

    /**
     * Unban an IP address.
     */
    public static function unbanIp(string $ipAddress): bool
    {
        return static::where('ip_address', $ipAddress)->delete() > 0;
    }

    /**
     * Clean up expired bans.
     */
    public static function cleanupExpired(): int
    {
        return static::where('is_permanent', false)
            ->where('expires_at', '<', now())
            ->delete();
    }
}
