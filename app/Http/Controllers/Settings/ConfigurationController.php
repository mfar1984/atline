<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::getAllGrouped();
        $canUpdate = auth()->user()->hasPermission('settings_configuration.update');
        
        // Timezone options
        $timezones = [
            'Asia/Kuala_Lumpur' => 'Asia/Kuala Lumpur (GMT+8)',
            'Asia/Singapore' => 'Asia/Singapore (GMT+8)',
            'Asia/Jakarta' => 'Asia/Jakarta (GMT+7)',
            'Asia/Bangkok' => 'Asia/Bangkok (GMT+7)',
            'Asia/Hong_Kong' => 'Asia/Hong Kong (GMT+8)',
            'Asia/Tokyo' => 'Asia/Tokyo (GMT+9)',
            'Australia/Sydney' => 'Australia/Sydney (GMT+10)',
            'Europe/London' => 'Europe/London (GMT+0)',
            'America/New_York' => 'America/New York (GMT-5)',
        ];
        
        // Date format options
        $dateFormats = [
            'd/m/Y' => 'DD/MM/YYYY (31/12/2026)',
            'm/d/Y' => 'MM/DD/YYYY (12/31/2026)',
            'Y-m-d' => 'YYYY-MM-DD (2026-12-31)',
            'd M Y' => 'DD Mon YYYY (31 Dec 2026)',
            'd F Y' => 'DD Month YYYY (31 December 2026)',
        ];
        
        // Time format options
        $timeFormats = [
            'H:i' => '24-hour (14:30)',
            'h:i A' => '12-hour (02:30 PM)',
        ];
        
        // Currency options
        $currencies = [
            'MYR' => 'Malaysian Ringgit (MYR)',
            'SGD' => 'Singapore Dollar (SGD)',
            'USD' => 'US Dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
        ];
        
        // Language options
        $languages = [
            'en' => 'English',
            'ms' => 'Bahasa Malaysia',
        ];
        
        return view('settings.configuration.index', compact(
            'settings',
            'canUpdate',
            'timezones',
            'dateFormats',
            'timeFormats',
            'currencies',
            'languages'
        ));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            // Company
            'system_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_short_name' => 'required|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_website' => 'nullable|url|max:255',
            'company_ssm_number' => 'nullable|string|max:50',
            
            // Regional
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'language' => 'required|string|max:10',
            
            // Security
            'session_timeout' => 'required|integer|min:15|max:480',
            'password_min_length' => 'required|integer|min:6|max:32',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:5|max:60',
            
            // Defaults
            'pagination_size' => 'required|integer|min:10|max:100',
            'ticket_auto_close_days' => 'required|integer|min:1|max:30',
            'attachment_max_size' => 'required|integer|min:1|max:50',
            'allowed_file_types' => 'required|string|max:500',
            
            // Rate Limiter
            'rate_limit_login_page' => 'required|integer|min:5|max:60',
            'rate_limit_login_attempt' => 'required|integer|min:3|max:20',
            'rate_limit_guest_protection' => 'required|integer|min:10|max:100',
        ]);
        
        // Company settings
        SystemSetting::setValue('company', 'system_name', $request->system_name);
        SystemSetting::setValue('company', 'name', $request->company_name);
        SystemSetting::setValue('company', 'short_name', $request->company_short_name);
        SystemSetting::setValue('company', 'email', $request->company_email ?? '');
        SystemSetting::setValue('company', 'phone', $request->company_phone ?? '');
        SystemSetting::setValue('company', 'address', $request->company_address ?? '');
        SystemSetting::setValue('company', 'website', $request->company_website ?? '');
        SystemSetting::setValue('company', 'ssm_number', $request->company_ssm_number ?? '');
        
        // Regional settings
        SystemSetting::setValue('regional', 'timezone', $request->timezone);
        SystemSetting::setValue('regional', 'date_format', $request->date_format);
        SystemSetting::setValue('regional', 'time_format', $request->time_format);
        SystemSetting::setValue('regional', 'currency', $request->currency);
        SystemSetting::setValue('regional', 'currency_symbol', $request->currency_symbol);
        SystemSetting::setValue('regional', 'language', $request->language);
        
        // Security settings
        SystemSetting::setValue('security', 'session_timeout', $request->session_timeout, 'integer');
        SystemSetting::setValue('security', 'password_min_length', $request->password_min_length, 'integer');
        SystemSetting::setValue('security', 'require_2fa', $request->has('require_2fa') ? '1' : '0', 'boolean');
        SystemSetting::setValue('security', 'max_login_attempts', $request->max_login_attempts, 'integer');
        SystemSetting::setValue('security', 'lockout_duration', $request->lockout_duration, 'integer');
        
        // Notification settings
        SystemSetting::setValue('notification', 'email_ticket_created', $request->has('email_ticket_created') ? '1' : '0', 'boolean');
        SystemSetting::setValue('notification', 'email_ticket_replied', $request->has('email_ticket_replied') ? '1' : '0', 'boolean');
        SystemSetting::setValue('notification', 'email_ticket_status_changed', $request->has('email_ticket_status_changed') ? '1' : '0', 'boolean');
        SystemSetting::setValue('notification', 'email_ticket_assigned', $request->has('email_ticket_assigned') ? '1' : '0', 'boolean');
        SystemSetting::setValue('notification', 'email_user_created', $request->has('email_user_created') ? '1' : '0', 'boolean');
        
        // Default settings
        SystemSetting::setValue('defaults', 'pagination_size', $request->pagination_size, 'integer');
        SystemSetting::setValue('defaults', 'ticket_auto_close_days', $request->ticket_auto_close_days, 'integer');
        SystemSetting::setValue('defaults', 'attachment_max_size', $request->attachment_max_size, 'integer');
        SystemSetting::setValue('defaults', 'allowed_file_types', $request->allowed_file_types);
        
        // Rate Limiter settings
        SystemSetting::setValue('rate_limiter', 'enabled', $request->has('rate_limiter_enabled') ? '1' : '0', 'boolean');
        SystemSetting::setValue('rate_limiter', 'login_page_limit', $request->rate_limit_login_page, 'integer');
        SystemSetting::setValue('rate_limiter', 'login_attempt_limit', $request->rate_limit_login_attempt, 'integer');
        SystemSetting::setValue('rate_limiter', 'guest_protection_limit', $request->rate_limit_guest_protection, 'integer');
        
        // Clear cache
        SystemSetting::clearCache();
        
        try {
            ActivityLogService::logSettingsUpdate('system_configuration', [
                'company' => $request->only(['system_name', 'company_name', 'company_short_name', 'company_email', 'company_phone']),
                'regional' => $request->only(['timezone', 'date_format', 'time_format', 'currency', 'language']),
                'security' => $request->only(['session_timeout', 'password_min_length', 'max_login_attempts', 'lockout_duration']),
                'defaults' => $request->only(['pagination_size', 'ticket_auto_close_days', 'attachment_max_size']),
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }
        
        return redirect()->route('settings.configuration.index')
            ->with('success', 'System configuration updated successfully.');
    }
}
