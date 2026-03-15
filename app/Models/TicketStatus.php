<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'sort_order',
        'is_default',
        'is_closed',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_closed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }
}
