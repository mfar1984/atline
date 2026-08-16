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
        'latitude',
        'longitude',
        'website',
        'phone',
        'email',
        'contact_person',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        // Cast as strings, not floats: these travel to the field app and are used
        // in a distance calculation that decides whether a signature is allowed.
        // Letting PHP turn them into floats would reintroduce the rounding the
        // DECIMAL column exists to avoid.
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Can this organization anchor a geofence?
     *
     * Both coordinates are needed — a latitude with no longitude is not half a
     * position, it is unusable.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

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
