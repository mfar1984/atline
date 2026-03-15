<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory, RecycleBin;

    protected $fillable = [
        'name',
        'original_filename',
        'file_type',
        'file_extension',
        'file_size',
        'storage_path',
        'storage_url',
        'status',
        'upload_progress',
        'download_count',
        'error_message',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'upload_progress' => 'integer',
        'download_count' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-gray-100 text-gray-800',
            'uploading' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }
}
