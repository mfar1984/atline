<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class HelpdeskEmailService
{
    private ?array $config = null;
    private ?IntegrationSetting $setting = null;

    /**
     * Check if email service is configured and active
     */
    public function isConfigured(): bool
    {
        $setting = $this->getSetting();
        
        if (!$setting || !$setting->is_active) {
            return false;
        }

        $credentials = $setting->getDecryptedCredentials();
        
        if ($setting->provider === IntegrationSetting::PROVIDER_SMTP) {
            return !empty($credentials['host']) 
                && !empty($credentials['from_address'])
                && !empty($credentials['from_name']);
        }
        
        if ($setting->provider === IntegrationSetting::PROVIDER_GOOGLE_EMAIL) {
            return !empty($credentials['google_client_id'])
                && !empty($credentials['google_client_secret'])
                && !empty($credentials['from_address'])
                && !empty($credentials['from_name']);
        }

        return false;
    }

    /**
     * Get email configuration from IntegrationSetting
     */
    public function getConfig(): ?array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        $setting = $this->getSetting();
        
        if (!$setting || !$setting->is_active) {
            return null;
        }

        $credentials = $setting->getDecryptedCredentials();
        
        $this->config = [
            'provider' => $setting->provider,
            'from_address' => $credentials['from_address'] ?? null,
            'from_name' => $credentials['from_name'] ?? null,
            'host' => $credentials['host'] ?? null,
            'port' => $credentials['port'] ?? 587,
            'username' => $credentials['username'] ?? null,
            'password' => $credentials['password'] ?? null,
            'encryption' => $credentials['encryption'] ?? 'tls',
            'google_client_id' => $credentials['google_client_id'] ?? null,
            'google_client_secret' => $credentials['google_client_secret'] ?? null,
        ];

        return $this->config;
    }

    /**
     * Send email using configured provider
     */
    public function send(string $to, string $toName, string $subject, string $htmlContent): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('HelpdeskEmailService: Email not configured, skipping send', [
                'to' => $to,
                'subject' => $subject,
            ]);
            return false;
        }

        $config = $this->getConfig();

        try {
            if ($config['provider'] === IntegrationSetting::PROVIDER_SMTP) {
                return $this->sendViaSMTP($to, $toName, $subject, $htmlContent, $config);
            }
            
            if ($config['provider'] === IntegrationSetting::PROVIDER_GOOGLE_EMAIL) {
                return $this->sendViaGoogle($to, $toName, $subject, $htmlContent, $config);
            }

            Log::error('HelpdeskEmailService: Unknown provider', [
                'provider' => $config['provider'],
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('HelpdeskEmailService: Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send email via SMTP
     */
    private function sendViaSMTP(string $to, string $toName, string $subject, string $htmlContent, array $config): bool
    {
        $encryption = $config['encryption'] ?? 'tls';
        $port = (int) ($config['port'] ?? 587);
        $host = $config['host'];
        $username = $config['username'];
        $password = $config['password'];

        // Build DSN based on encryption type
        $useImplicitSsl = ($encryption === 'ssl' && $port == 465);
        
        if ($useImplicitSsl) {
            $dsn = sprintf(
                'smtps://%s:%s@%s:%d',
                urlencode($username),
                urlencode($password),
                $host,
                $port
            );
        } else {
            $dsn = sprintf(
                'smtp://%s:%s@%s:%d',
                urlencode($username),
                urlencode($password),
                $host,
                $port
            );
        }

        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from(new Address($config['from_address'], $config['from_name']))
            ->to(new Address($to, $toName))
            ->subject($subject)
            ->html($htmlContent);

        $mailer->send($email);

        Log::info('HelpdeskEmailService: Email sent successfully via SMTP', [
            'to' => $to,
            'subject' => $subject,
        ]);

        return true;
    }

    /**
     * Send email via Google API
     * Note: This is a placeholder - Google OAuth implementation would be more complex
     */
    private function sendViaGoogle(string $to, string $toName, string $subject, string $htmlContent, array $config): bool
    {
        // For now, log that Google provider is not fully implemented
        // In production, this would use Google API with OAuth
        Log::warning('HelpdeskEmailService: Google provider not fully implemented, falling back to SMTP-like behavior', [
            'to' => $to,
            'subject' => $subject,
        ]);

        // Google SMTP relay can be used as fallback
        // smtp.gmail.com with OAuth or App Password
        return false;
    }

    /**
     * Get IntegrationSetting for email
     */
    private function getSetting(): ?IntegrationSetting
    {
        if ($this->setting !== null) {
            return $this->setting;
        }

        $this->setting = IntegrationSetting::where('integration_type', IntegrationSetting::TYPE_EMAIL)
            ->first();

        return $this->setting;
    }

    /**
     * Clear cached config (useful for testing)
     */
    public function clearCache(): void
    {
        $this->config = null;
        $this->setting = null;
    }
}
