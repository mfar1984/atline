<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    // Storage type constants
    const STORAGE_LOCAL = 'local';
    const STORAGE_R2 = 'r2';

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'file_name',
        'file_path',
        'storage_type',
        'storage_path',
        'storage_url',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    // Relationships
    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Get formatted file size
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Check if file is an image
    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type), ['image/jpeg', 'image/png', 'image/jpg']);
    }

    // Check if file is a document
    public function isDocument(): bool
    {
        return in_array(strtolower($this->file_type), [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    }

    /**
     * Check if attachment is stored in R2
     */
    public function isR2Storage(): bool
    {
        return $this->storage_type === self::STORAGE_R2;
    }

    /**
     * Check if attachment is stored locally
     */
    public function isLocalStorage(): bool
    {
        return $this->storage_type === self::STORAGE_LOCAL;
    }

    /**
     * Get storage location label
     */
    public function getStorageLocationAttribute(): string
    {
        return $this->storage_type === self::STORAGE_R2 ? 'Cloud Storage' : 'Local Storage';
    }
}
