<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

trait RecycleBin
{
    use SoftDeletes;

    /**
     * Boot the RecycleBin trait for a model.
     * Auto-set deleted_by when soft deleting.
     */
    public static function bootRecycleBin(): void
    {
        static::deleting(function ($model) {
            if (!$model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });

        static::restoring(function ($model) {
            $model->deleted_by = null;
        });
    }

    /**
     * Get the user who deleted this model.
     */
    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the display name for the recycle bin.
     */
    public function getRecycleBinName(): string
    {
        // Try common name fields
        if (isset($this->name) && $this->name) {
            return $this->name;
        }
        if (isset($this->title) && $this->title) {
            return $this->title;
        }
        if (isset($this->subject) && $this->subject) {
            return $this->subject;
        }
        if (isset($this->full_name) && $this->full_name) {
            return $this->full_name;
        }
        if (isset($this->asset_tag) && $this->asset_tag) {
            return $this->asset_tag;
        }
        if (isset($this->ticket_number) && $this->ticket_number) {
            return $this->ticket_number;
        }

        return "#{$this->id}";
    }

    /**
     * Get the type label for the recycle bin.
     */
    public function getRecycleBinType(): string
    {
        return class_basename($this);
    }

    /**
     * Get the icon for this model type in recycle bin.
     */
    public function getRecycleBinIcon(): string
    {
        $icons = [
            'Project' => 'folder',
            'Asset' => 'inventory_2',
            'Client' => 'business',
            'Vendor' => 'local_shipping',
            'Category' => 'category',
            'Brand' => 'branding_watermark',
            'Location' => 'location_on',
            'Employee' => 'person',
            'InternalAsset' => 'devices',
            'Credential' => 'key',
            'Download' => 'download',
            'Ticket' => 'confirmation_number',
        ];

        $type = $this->getRecycleBinType();
        return $icons[$type] ?? 'delete';
    }

    /**
     * Get the color class for this model type in recycle bin.
     */
    public function getRecycleBinColor(): string
    {
        $colors = [
            'Project' => 'text-blue-600 bg-blue-100',
            'Asset' => 'text-green-600 bg-green-100',
            'Client' => 'text-purple-600 bg-purple-100',
            'Vendor' => 'text-orange-600 bg-orange-100',
            'Category' => 'text-gray-600 bg-gray-100',
            'Brand' => 'text-indigo-600 bg-indigo-100',
            'Location' => 'text-red-600 bg-red-100',
            'Employee' => 'text-teal-600 bg-teal-100',
            'InternalAsset' => 'text-cyan-600 bg-cyan-100',
            'Credential' => 'text-amber-600 bg-amber-100',
            'Download' => 'text-lime-600 bg-lime-100',
            'Ticket' => 'text-pink-600 bg-pink-100',
        ];

        $type = $this->getRecycleBinType();
        return $colors[$type] ?? 'text-gray-600 bg-gray-100';
    }
}
