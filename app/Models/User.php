<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'last_login_at',
        'avatar',
        'failed_login_attempts',
        'locked_until',
        'last_failed_login',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * Get the role that the user belongs to
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the employee record for this user
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the client record for this user (legacy)
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Get all projects this user has access to (many-to-many)
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withTimestamps();
    }

    /**
     * Get IDs of all projects this user can access
     */
    public function getAccessibleProjectIds(): array
    {
        return $this->projects()->pluck('projects.id')->toArray();
    }

    /**
     * Check if user has access to a specific project
     */
    public function hasProjectAccess(int $projectId): bool
    {
        return $this->projects()->where('projects.id', $projectId)->exists();
    }

    /**
     * Check if user is a client user (has project access via project_user)
     */
    public function isClientUser(): bool
    {
        // User is a client if they have the Client role
        if ($this->role && $this->role->name === 'Client') {
            return true;
        }
        return false;
    }

    /**
     * Check if user has specific permission
     * Permission is checked from role's permissions array
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    /**
     * Check if user has access to a module
     * Module access is checked from role's permissions array
     */
    public function hasModuleAccess(string $module): bool
    {
        return $this->role?->hasModuleAccess($module) ?? false;
    }

    /**
     * Get user initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials ?: 'U';
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if user account is locked
     */
    public function isLocked(): bool
    {
        if (!$this->locked_until) {
            return false;
        }
        
        return $this->locked_until->isFuture();
    }

    /**
     * Get remaining lockout time in minutes
     */
    public function getLockoutRemainingMinutes(): int
    {
        if (!$this->isLocked()) {
            return 0;
        }
        
        return (int) now()->diffInMinutes($this->locked_until, false);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedAttempts(): void
    {
        $maxAttempts = SystemSetting::getValue('security', 'max_login_attempts', 5);
        $lockoutDuration = SystemSetting::getValue('security', 'lockout_duration', 15);
        
        $this->failed_login_attempts++;
        $this->last_failed_login = now();
        
        // Lock account if max attempts reached
        if ($this->failed_login_attempts >= $maxAttempts) {
            $this->locked_until = now()->addMinutes($lockoutDuration);
        }
        
        $this->save();
    }

    /**
     * Reset failed login attempts on successful login
     */
    public function resetFailedAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->last_failed_login = null;
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_failed_login' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
