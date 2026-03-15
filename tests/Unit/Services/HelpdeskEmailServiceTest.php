<?php

namespace Tests\Unit\Services;

use App\Models\IntegrationSetting;
use App\Services\HelpdeskEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: helpdesk-email-notification
 * Property 1: Email Provider Configuration Usage
 * Validates: Requirements 1.1, 1.4
 */
class HelpdeskEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private HelpdeskEmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HelpdeskEmailService();
    }

    /**
     * Test isConfigured returns false when no settings exist
     */
    public function test_is_configured_returns_false_when_no_settings(): void
    {
        $this->assertFalse($this->service->isConfigured());
    }

    /**
     * Test isConfigured returns false when settings exist but inactive
     */
    public function test_is_configured_returns_false_when_inactive(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => false,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp.example.com',
            'port' => 587,
            'from_address' => 'test@example.com',
            'from_name' => 'Test',
        ]);
        $setting->save();

        $this->service->clearCache();
        $this->assertFalse($this->service->isConfigured());
    }

    /**
     * Test isConfigured returns true when SMTP properly configured
     */
    public function test_is_configured_returns_true_when_smtp_configured(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'user@example.com',
            'password' => 'password123',
            'encryption' => 'tls',
            'from_address' => 'noreply@example.com',
            'from_name' => 'Test System',
        ]);
        $setting->save();

        $this->service->clearCache();
        $this->assertTrue($this->service->isConfigured());
    }

    /**
     * Test isConfigured returns false when SMTP missing required fields
     */
    public function test_is_configured_returns_false_when_smtp_missing_host(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'port' => 587,
            'from_address' => 'noreply@example.com',
            'from_name' => 'Test System',
        ]);
        $setting->save();

        $this->service->clearCache();
        $this->assertFalse($this->service->isConfigured());
    }

    /**
     * Test isConfigured returns true when Google properly configured
     */
    public function test_is_configured_returns_true_when_google_configured(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_GOOGLE_EMAIL,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'google_client_id' => 'client-id-123',
            'google_client_secret' => 'client-secret-456',
            'from_address' => 'noreply@example.com',
            'from_name' => 'Test System',
        ]);
        $setting->save();

        $this->service->clearCache();
        $this->assertTrue($this->service->isConfigured());
    }

    /**
     * Test getConfig returns null when no settings
     */
    public function test_get_config_returns_null_when_no_settings(): void
    {
        $this->assertNull($this->service->getConfig());
    }

    /**
     * Test getConfig returns correct credentials for SMTP
     */
    public function test_get_config_returns_correct_smtp_credentials(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp.example.com',
            'port' => 465,
            'username' => 'user@example.com',
            'password' => 'secret123',
            'encryption' => 'ssl',
            'from_address' => 'noreply@example.com',
            'from_name' => 'ATLINE System',
        ]);
        $setting->save();

        $this->service->clearCache();
        $config = $this->service->getConfig();

        $this->assertNotNull($config);
        $this->assertEquals(IntegrationSetting::PROVIDER_SMTP, $config['provider']);
        $this->assertEquals('smtp.example.com', $config['host']);
        $this->assertEquals(465, $config['port']);
        $this->assertEquals('user@example.com', $config['username']);
        $this->assertEquals('secret123', $config['password']);
        $this->assertEquals('ssl', $config['encryption']);
        $this->assertEquals('noreply@example.com', $config['from_address']);
        $this->assertEquals('ATLINE System', $config['from_name']);
    }

    /**
     * Property 1: Email Provider Configuration Usage
     * For any email sent, the from_address and from_name must match IntegrationSetting
     */
    public function test_property_email_uses_configured_from_address_and_name(): void
    {
        $expectedFromAddress = 'helpdesk@company.com';
        $expectedFromName = 'Company Helpdesk';

        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'user@example.com',
            'password' => 'password',
            'encryption' => 'tls',
            'from_address' => $expectedFromAddress,
            'from_name' => $expectedFromName,
        ]);
        $setting->save();

        $this->service->clearCache();
        $config = $this->service->getConfig();

        // Property: from_address and from_name must match configured values
        $this->assertEquals($expectedFromAddress, $config['from_address']);
        $this->assertEquals($expectedFromName, $config['from_name']);
    }

    /**
     * Test send returns false when not configured
     */
    public function test_send_returns_false_when_not_configured(): void
    {
        $result = $this->service->send(
            'recipient@example.com',
            'Recipient Name',
            'Test Subject',
            '<p>Test content</p>'
        );

        $this->assertFalse($result);
    }

    /**
     * Test config caching works correctly
     */
    public function test_config_is_cached(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp.example.com',
            'port' => 587,
            'from_address' => 'test@example.com',
            'from_name' => 'Test',
        ]);
        $setting->save();

        $this->service->clearCache();
        
        // First call
        $config1 = $this->service->getConfig();
        
        // Second call should return cached value
        $config2 = $this->service->getConfig();

        $this->assertEquals($config1, $config2);
    }

    /**
     * Test clearCache resets the cached config
     */
    public function test_clear_cache_resets_config(): void
    {
        $setting = IntegrationSetting::create([
            'integration_type' => IntegrationSetting::TYPE_EMAIL,
            'provider' => IntegrationSetting::PROVIDER_SMTP,
            'is_active' => true,
        ]);
        $setting->setEncryptedCredentials([
            'host' => 'smtp1.example.com',
            'port' => 587,
            'from_address' => 'test@example.com',
            'from_name' => 'Test',
        ]);
        $setting->save();

        $this->service->clearCache();
        $config1 = $this->service->getConfig();
        $this->assertEquals('smtp1.example.com', $config1['host']);

        // Update setting
        $setting->setEncryptedCredentials([
            'host' => 'smtp2.example.com',
            'port' => 587,
            'from_address' => 'test@example.com',
            'from_name' => 'Test',
        ]);
        $setting->save();

        // Without clearCache, should still return old value
        $config2 = $this->service->getConfig();
        $this->assertEquals('smtp1.example.com', $config2['host']);

        // After clearCache, should return new value
        $this->service->clearCache();
        $config3 = $this->service->getConfig();
        $this->assertEquals('smtp2.example.com', $config3['host']);
    }
}
