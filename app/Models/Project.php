<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'client_name', // Keep for backward compatibility
        'project_value',
        'start_date',
        'end_date',
        'status',
        'purchase_date',
        'po_number',
        'warranty_period',
        'warranty_expiry',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'project_value' => 'decimal:2',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function projectAttachments()
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Check if project can be deleted (no linked assets)
    public function canBeDeleted(): bool
    {
        return $this->assets()->count() === 0;
    }

    // Check if warranty is expiring within N days
    public function isWarrantyExpiring(int $days = 30): bool
    {
        if (!$this->warranty_expiry) {
            return false;
        }
        return $this->warranty_expiry->isBetween(now(), now()->addDays($days));
    }

    // Scope for warranty expiring
    public function scopeWarrantyExpiring($query, int $days = 30)
    {
        return $query->whereNotNull('warranty_expiry')
            ->whereBetween('warranty_expiry', [now(), now()->addDays($days)]);
    }
}
