<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'name',
        'code',
        'fields_config',
        'is_active',
    ];

    protected $casts = [
        'fields_config' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // Get dynamic fields for this category
    public function getDynamicFields(): array
    {
        return $this->fields_config ?? [];
    }

    // Check if category can be deleted (not in use)
    public function canBeDeleted(): bool
    {
        return $this->assets()->count() === 0;
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
