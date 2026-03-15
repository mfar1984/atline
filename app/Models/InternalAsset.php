<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalAsset extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'asset_tag', 'name', 'category_id', 'brand_id', 'model',
        'serial_number', 'location_id', 'status', 'condition',
        'purchase_price', 'purchase_date', 'warranty_expiry', 'notes'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(InternalCategory::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(InternalBrand::class, 'brand_id');
    }

    public function location()
    {
        return $this->belongsTo(InternalLocation::class, 'location_id');
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class, 'internal_asset_id');
    }

    public function currentMovement()
    {
        return $this->hasOne(AssetMovement::class, 'internal_asset_id')
            ->where('status', 'checked_out')
            ->latest();
    }
}
