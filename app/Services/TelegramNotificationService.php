<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Action icons mapping
     */
    private static array $actionIcons = [
        'create' => '✅',
        'update' => '📝',
        'delete' => '🗑️',
        'export' => '📤',
        'assign' => '👥',
        'chat' => '💬',
        'reply' => '💬',
        'restore' => '♻️',
        'download' => '📥',
        'upload' => '📤',
        'login' => '🔐',
        'logout' => '🚪',
        'login_failed' => '⚠️',
        'checkout' => '📦',
        'checkin' => '📦',
        'view' => '👁️',
        'print' => '🖨️',
        'password_change' => '🔑',
        '2fa_enabled' => '🛡️',
        '2fa_disabled' => '🛡️',
    ];

    /**
     * Get Telegram settings
     */
    private static function getSettings(): ?array
    {
        $setting = IntegrationSetting::where('integration_type', IntegrationSetting::TYPE_TELEGRAM)
            ->where('is_active', true)
            ->first();

        if (!$setting) {
            return null;
        }

        $credentials = $setting->getDecryptedCredentials();

        if (empty($credentials['bot_token']) || empty($credentials['channel_id'])) {
            return null;
        }

        return $credentials;
    }

    /**
     * Send activity log notification to Telegram
     */
    public static function sendActivityNotification(array $data): bool
    {
        $settings = self::getSettings();

        if (!$settings) {
            return false;
        }

        $message = self::formatActivityMessage($data);

        return self::sendMessage($settings['bot_token'], $settings['channel_id'], $message);
    }

    /**
     * Format activity message for Telegram
     */
    private static function formatActivityMessage(array $data): string
    {
        $action = $data['action'] ?? 'unknown';
        $module = $data['module'] ?? 'System';
        $description = $data['description'] ?? '';
        $userName = $data['user_name'] ?? 'System';
        $userRole = $data['user_role'] ?? '';
        $ipAddress = $data['ip_address'] ?? 'Unknown';
        $timestamp = $data['timestamp'] ?? now()->format('d M Y, H:i:s');

        $icon = self::$actionIcons[$action] ?? '📋';
        $actionLabel = ucfirst(str_replace('_', ' ', $action));
        $moduleLabel = self::formatModuleName($module);

        // Build user info with role
        $userInfo = $userName;
        if ($userRole) {
            $userInfo .= " ({$userRole})";
        }

        $message = "{$icon} <b>Atline Administration System Activity</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📌 <b>Action:</b> {$actionLabel}\n";
        $message .= "📁 <b>Module:</b> {$moduleLabel}\n";
        $message .= "━━━━━━━━━━━━━━━━━━\n";
        $message .= "📝 {$description}\n";
        $message .= "👤 <b>By:</b> {$userInfo}\n";
        $message .= "⏰ <b>Time:</b> {$timestamp}\n";
        $message .= "🌐 <b>IP:</b> {$ipAddress}";

        return $message;
    }

    /**
     * Format module name for display
     */
    private static function formatModuleName(string $module): string
    {
        // Convert snake_case to Title Case
        $formatted = str_replace('_', ' ', $module);
        $formatted = ucwords($formatted);
        
        // Handle special cases
        $replacements = [
            'Internal Inventory' => 'Internal > Inventory',
            'External Inventory' => 'External > Inventory',
            'External Projects' => 'External > Projects',
            'External Settings' => 'External > Settings',
            'Settings Config' => 'System Settings',
            'Settings Users' => 'Users',
            'Settings Roles' => 'Roles',
            'Auth' => 'Authentication',
        ];

        return $replacements[$formatted] ?? $formatted;
    }

    /**
     * Send message to Telegram
     */
    public static function sendMessage(string $botToken, string $channelId, string $message): bool
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $channelId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return isset($data['ok']) && $data['ok'] === true;
            }

            Log::warning('Telegram notification failed', [
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Telegram notification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
