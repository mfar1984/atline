<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A bearer token for the External read-only API.
 *
 * Only the SHA-256 hash is persisted. The plaintext is returned once by
 * {@see self::issue()} and cannot be recovered afterwards.
 */
class ApiToken extends Model
{
    protected $fillable = [
        'name',
        'token_hash',
        'abilities',
        'last_used_at',
        'last_used_ip',
        'expires_at',
        'revoked_at',
        'created_by',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'revoked_at'   => 'datetime',
    ];

    /** Hidden so the hash never leaks through a JSON response. */
    protected $hidden = ['token_hash'];

    public const ABILITY_READ = 'external:read';

    public static function hashToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    /**
     * Create a token and return [model, plaintext]. Show the plaintext once.
     */
    public static function issue(string $name, ?int $userId = null, ?\DateTimeInterface $expiresAt = null): array
    {
        // 48 random bytes, url-safe. Prefixed so a leaked string is identifiable.
        $plain = 'atlh_' . Str::random(56);

        $token = self::create([
            'name'       => $name,
            'token_hash' => self::hashToken($plain),
            'abilities'  => self::ABILITY_READ,
            'expires_at' => $expiresAt,
            'created_by' => $userId,
        ]);

        return [$token, $plain];
    }

    /** Resolve a usable token from a plaintext value, or null. */
    public static function findValid(string $plain): ?self
    {
        $token = self::where('token_hash', self::hashToken($plain))->first();
        if (!$token || !$token->isUsable()) {
            return null;
        }
        return $token;
    }

    public function isUsable(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    public function hasAbility(string $ability): bool
    {
        $granted = array_map('trim', explode(',', (string) $this->abilities));
        return in_array($ability, $granted, true) || in_array('*', $granted, true);
    }

    /**
     * Record usage. Throttled to once a minute so a busy sync does not write
     * a row update on every single request.
     */
    public function touchUsage(?string $ip = null): void
    {
        if ($this->last_used_at && $this->last_used_at->diffInSeconds(now()) < 60) {
            return;
        }
        $this->forceFill([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ])->saveQuietly();
    }

    public function getStatusAttribute(): string
    {
        if ($this->revoked_at !== null) {
            return 'revoked';
        }
        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return 'expired';
        }
        return 'active';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUsable($query)
    {
        return $query->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
