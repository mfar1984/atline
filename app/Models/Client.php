<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'name',
        'user_id',
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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Check if client can be deleted
    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0 && $this->tickets()->count() === 0;
    }

    // Scope for active clients
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
