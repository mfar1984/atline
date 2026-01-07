<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];
    
    /**
     * Get a setting value by group and key
     */
    public static function getValue(string $group, string $key, $default = null)
    {
        $cacheKey = "system_setting.{$group}.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $default) {
            $setting = static::where('group', $group)->where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return static::castValue($setting->value, $setting->type);
        });
    }
    
    /**
     * Set a setting value
     */
    public static function setValue(string $group, string $key, $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );
        
        Cache::forget("system_setting.{$group}.{$key}");
        Cache::forget("system_settings.{$group}");
    }
    
    /**
     * Get all settings for a group
     */
    public static function getGroup(string $group): array
    {
        $cacheKey = "system_settings.{$group}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            $settings = static::where('group', $group)->get();
            $result = [];
            
            foreach ($settings as $setting) {
                $result[$setting->key] = static::castValue($setting->value, $setting->type);
            }
            
            return $result;
        });
    }
    
    /**
     * Get all settings grouped
     */
    public static function getAllGrouped(): array
    {
        $settings = static::all();
        $result = [];
        
        foreach ($settings as $setting) {
            if (!isset($result[$setting->group])) {
                $result[$setting->group] = [];
            }
            $result[$setting->group][$setting->key] = static::castValue($setting->value, $setting->type);
        }
        
        return $result;
    }
    
    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $groups = ['company', 'regional', 'security', 'notification', 'defaults', 'rate_limiter'];
        
        foreach ($groups as $group) {
            Cache::forget("system_settings.{$group}");
            
            $settings = static::where('group', $group)->get();
            foreach ($settings as $setting) {
                Cache::forget("system_setting.{$group}.{$setting->key}");
            }
        }
    }
    
    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
    
    /**
     * Helper methods for common settings
     */
    public static function companyName(): string
    {
        return static::getValue('company', 'name', 'Atline Sdn Bhd');
    }
    
    public static function companyShortName(): string
    {
        return static::getValue('company', 'short_name', 'ATLINE');
    }
    
    public static function timezone(): string
    {
        return static::getValue('regional', 'timezone', 'Asia/Kuala_Lumpur');
    }
    
    public static function dateFormat(): string
    {
        return static::getValue('regional', 'date_format', 'd/m/Y');
    }
    
    public static function currency(): string
    {
        return static::getValue('regional', 'currency', 'MYR');
    }
    
    public static function currencySymbol(): string
    {
        return static::getValue('regional', 'currency_symbol', 'RM');
    }
    
    public static function paginationSize(): int
    {
        return static::getValue('defaults', 'pagination_size', 15);
    }
    
    public static function systemName(): string
    {
        return static::getValue('company', 'system_name', 'Atline Administration System');
    }
    
    /**
     * Rate Limiter helper methods
     */
    public static function rateLimiterEnabled(): bool
    {
        return static::getValue('rate_limiter', 'enabled', true);
    }
    
    public static function loginPageLimit(): int
    {
        return static::getValue('rate_limiter', 'login_page_limit', 10);
    }
    
    public static function loginAttemptLimit(): int
    {
        return static::getValue('rate_limiter', 'login_attempt_limit', 5);
    }
    
    public static function guestProtectionLimit(): int
    {
        return static::getValue('rate_limiter', 'guest_protection_limit', 20);
    }
}
