<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'parent_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // Get full path (Site > Building > Floor > Room)
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    // Check if location can be deleted
    public function canBeDeleted(): bool
    {
        return $this->assets()->count() === 0 && $this->children()->count() === 0;
    }

    // Scope for active locations
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for root locations (no parent)
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
