<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope for active measurements
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope by type
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
