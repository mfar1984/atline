<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use Carbon\Carbon;

class FormatHelper
{
    /**
     * Format date according to system settings
     */
    public static function date($date, $includeTime = false): string
    {
        if (!$date) {
            return '-';
        }
        
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        $dateFormat = SystemSetting::dateFormat();
        $timeFormat = SystemSetting::getValue('regional', 'time_format', 'H:i');
        
        if ($includeTime) {
            return $date->format($dateFormat . ' ' . $timeFormat);
        }
        
        return $date->format($dateFormat);
    }
    
    /**
     * Format datetime according to system settings
     */
    public static function datetime($date): string
    {
        return self::date($date, true);
    }
    
    /**
     * Format time according to system settings
     */
    public static function time($date): string
    {
        if (!$date) {
            return '-';
        }
        
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        $timeFormat = SystemSetting::getValue('regional', 'time_format', 'H:i');
        return $date->format($timeFormat);
    }
    
    /**
     * Format currency according to system settings
     */
    public static function currency($amount, $showSymbol = true): string
    {
        if ($amount === null) {
            return '-';
        }
        
        $symbol = SystemSetting::currencySymbol();
        $formatted = number_format((float) $amount, 2);
        
        if ($showSymbol) {
            return $symbol . ' ' . $formatted;
        }
        
        return $formatted;
    }
    
    /**
     * Get currency symbol
     */
    public static function currencySymbol(): string
    {
        return SystemSetting::currencySymbol();
    }
    
    /**
     * Get date format string for HTML date inputs (always Y-m-d)
     */
    public static function inputDateFormat(): string
    {
        return 'Y-m-d';
    }
    
    /**
     * Format date for display with custom format
     */
    public static function dateCustom($date, string $format): string
    {
        if (!$date) {
            return '-';
        }
        
        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}
