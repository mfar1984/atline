<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!is_array($this->permissions)) {
            return false;
        }
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if role has any permission for a module
     * Supports parent modules with sub-modules (e.g., internal_inventory checks internal_inventory_*)
     */
    public function hasModuleAccess(string $module): bool
    {
        if (!is_array($this->permissions)) {
            return false;
        }
        
        foreach ($this->permissions as $permission) {
            // Direct module match (e.g., internal_credentials.view)
            if (str_starts_with($permission, $module . '.')) {
                return true;
            }
            // Sub-module match for parent modules (e.g., internal_inventory checks internal_inventory_assets.view)
            if (str_starts_with($permission, $module . '_')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if role can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->users()->count() === 0;
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
