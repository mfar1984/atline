<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_asset_id', 'employee_id', 'checkout_date', 'expected_return_date',
        'actual_return_date', 'checkout_condition', 'return_condition',
        'purpose', 'status', 'approved_by', 'notes'
    ];

    protected $casts = [
        'checkout_date' => 'datetime',
        'expected_return_date' => 'date',
        'actual_return_date' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(InternalAsset::class, 'internal_asset_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isOverdue()
    {
        return $this->status === 'checked_out' && 
               $this->expected_return_date < now()->startOfDay();
    }
}
