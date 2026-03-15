<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IntegrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_type',
        'provider',
        'credentials',
        'settings',
        'is_active',
        'last_tested_at',
        'last_test_status',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    // Integration type constants
    public const TYPE_EMAIL = 'email';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_STORAGE = 'storage';
    public const TYPE_WEATHER = 'weather';
    public const TYPE_WEBHOOK = 'webhook';
    public const TYPE_TELEGRAM = 'telegram';

    // Provider constants
    public const PROVIDER_SMTP = 'smtp';
    public const PROVIDER_GOOGLE_EMAIL = 'google';
    public const PROVIDER_CHIP_ASIA = 'chip-asia';
    public const PROVIDER_GOOGLE_DRIVE = 'google-drive';
    public const PROVIDER_OPENWEATHERMAP = 'openweathermap';

    /**
     * Get decrypted credentials
     */
    public function getDecryptedCredentials(): array
    {
        if (empty($this->credentials)) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($this->credentials);
            return json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set encrypted credentials
     */
    public function setEncryptedCredentials(array $credentials): void
    {
        $this->credentials = Crypt::encryptString(json_encode($credentials));
    }

    /**
     * Scope for specific integration type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('integration_type', $type);
    }

    /**
     * Scope for active integrations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get or create setting by type
     */
    public static function getOrCreateByType(string $type): self
    {
        return self::firstOrCreate(
            ['integration_type' => $type],
            ['provider' => '', 'is_active' => false]
        );
    }

    /**
     * Check if connection was tested successfully
     */
    public function isConnected(): bool
    {
        return $this->is_active && $this->last_test_status === 'success';
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        if (!$this->is_active) {
            return 'Not Configured';
        }
        
        if ($this->last_test_status === 'success') {
            return 'Connected';
        }
        
        if ($this->last_test_status === 'failed') {
            return 'Connection Failed';
        }
        
        return 'Not Tested';
    }
}
