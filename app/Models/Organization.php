<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes, RecycleBin;

    protected $fillable = [
        'name',
        'organization_type',
        'address_1',
        'address_2',
        'postcode',
        'district',
        'state',
        'country',
        'website',
        'phone',
        'email',
        'contact_person',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all projects for this organization
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get all tickets for this organization
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Check if organization can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0 && $this->tickets()->count() === 0;
    }

    /**
     * Scope for active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
