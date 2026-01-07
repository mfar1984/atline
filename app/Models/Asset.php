<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'project_id',
        'category_id',
        'asset_tag',
        'brand_id',
        'model',
        'serial_number',
        'status',
        'specs',
        'unit_price',
        'location_id',
        'vendor_id',
        'assigned_to',
        'department',
        'notes',
    ];

    protected $casts = [
        'specs' => 'array',
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class);
    }

    // Scope for active assets
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
