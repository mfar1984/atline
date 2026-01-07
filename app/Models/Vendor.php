<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, RecycleBin;

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
        'contact_person',
        'phone',
        'email',
        'incharge_name',
        'incharge_phone',
        'incharge_whatsapp',
        'incharge_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // Check if vendor can be deleted (only check assets - projects table doesn't have vendor_id)
    public function canBeDeleted(): bool
    {
        return $this->assets()->count() === 0;
    }

    // Scope for active vendors
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
