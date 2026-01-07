<?php

namespace App\Models;

use App\Traits\RecycleBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory, RecycleBin;

    const TYPE_SSH = 'ssh';
    const TYPE_WINDOWS = 'windows';
    const TYPE_LICENSE_KEY = 'license_key';
    const TYPE_DATABASE = 'database';
    const TYPE_API_KEY = 'api_key';
    const TYPE_OTHER = 'other';

    const TYPES = [
        self::TYPE_SSH => 'SSH',
        self::TYPE_WINDOWS => 'Windows',
        self::TYPE_LICENSE_KEY => 'License Key',
        self::TYPE_DATABASE => 'Database',
        self::TYPE_API_KEY => 'API Key',
        self::TYPE_OTHER => 'Other',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'encrypted_data',
        'data_iv',
        'notes',
        'created_by',
    ];

    protected $hidden = [
        'encrypted_data',
        'data_iv',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(CredentialAuditLog::class);
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Unknown';
    }

    public static function getTypeOptions(): array
    {
        return self::TYPES;
    }

    public static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::TYPES);
    }
}
