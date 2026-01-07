<?php

namespace App\Providers;

use App\Helpers\FormatHelper;
use App\Models\SystemSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Rate Limiters for Login Protection
        $this->configureRateLimiting();
        
        // Permission Blade directive
        Blade::if('permission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
        
        // Module access Blade directive
        Blade::if('moduleAccess', function (string $module) {
            return auth()->check() && auth()->user()->hasModuleAccess($module);
        });
        
        // Format date directive - usage: @formatDate($date) or @formatDate($date, true) for datetime
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::date($expression); ?>";
        });
        
        // Format datetime directive - usage: @formatDateTime($date)
        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::datetime($expression); ?>";
        });
        
        // Format time directive - usage: @formatTime($date)
        Blade::directive('formatTime', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::time($expression); ?>";
        });
        
        // Format currency directive - usage: @formatCurrency($amount) or @formatCurrency($amount, false)
        Blade::directive('formatCurrency', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::currency($expression); ?>";
        });
        
        // Get currency symbol - usage: @currencySymbol
        Blade::directive('currencySymbol', function () {
            return "<?php echo \App\Helpers\FormatHelper::currencySymbol(); ?>";
        });
    }
    
    /**
     * Configure rate limiting for login protection.
     * Uses Redis for high-performance rate limiting.
     * Values are read from SystemSetting (configurable via System Configuration page).
     */
    protected function configureRateLimiting(): void
    {
        // Check if rate limiter is enabled
        $enabled = $this->getRateLimiterSetting('enabled', true);
        
        if (!$enabled) {
            // If disabled, set very high limits (effectively no limit)
            RateLimiter::for('login-page', fn() => Limit::perMinute(1000));
            RateLimiter::for('login-attempt', fn() => Limit::perMinute(1000));
            RateLimiter::for('guest-protection', fn() => Limit::perMinute(1000));
            return;
        }
        
        // Get configurable limits from database
        $loginPageLimit = $this->getRateLimiterSetting('login_page_limit', 10);
        $loginAttemptLimit = $this->getRateLimiterSetting('login_attempt_limit', 5);
        $guestProtectionLimit = $this->getRateLimiterSetting('guest_protection_limit', 20);
        
        // Login page rate limiter - ketat untuk guest
        RateLimiter::for('login-page', function (Request $request) use ($loginPageLimit) {
            return Limit::perMinute($loginPageLimit)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak cubaan. Sila tunggu sebentar.',
                        'retryAfter' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });
        
        // Login attempt rate limiter - lebih ketat untuk POST login
        RateLimiter::for('login-attempt', function (Request $request) use ($loginAttemptLimit) {
            return Limit::perMinute($loginAttemptLimit)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak cubaan login. IP anda telah direkodkan. Sila tunggu 1 minit.',
                        'retryAfter' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });
        
        // Aggressive rate limiter untuk brute force protection
        RateLimiter::for('guest-protection', function (Request $request) use ($guestProtectionLimit) {
            return Limit::perMinute($guestProtectionLimit)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response('Terlalu banyak request. IP anda direkodkan.', 429, $headers);
                });
        });
    }
    
    /**
     * Get rate limiter setting with fallback.
     * Uses try-catch to handle cases where database/cache is not yet available.
     */
    protected function getRateLimiterSetting(string $key, $default)
    {
        try {
            // Try to get from database directly to avoid cache issues
            $setting = \App\Models\SystemSetting::where('group', 'rate_limiter')
                ->where('key', $key)
                ->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->type === 'boolean' 
                ? (bool) $setting->value 
                : (int) $setting->value;
        } catch (\Exception $e) {
            // Database/cache not available (e.g., during migrations or Redis not installed)
            return $default;
        }
    }
}
