<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'name',
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

    // Check if brand can be deleted
    public function canBeDeleted(): bool
    {
        return $this->assets()->count() === 0;
    }

    // Scope for active brands
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
