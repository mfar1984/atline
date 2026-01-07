<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use App\Services\ActivityLogService;
use App\Services\RecycleBinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegrationController extends Controller
{
    protected RecycleBinService $recycleBinService;

    public function __construct(RecycleBinService $recycleBinService)
    {
        $this->recycleBinService = $recycleBinService;
    }

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'recycle-bin');
        
        $emailSetting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_EMAIL);
        $paymentSetting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_PAYMENT);
        $storageSetting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_STORAGE);
        $weatherSetting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEATHER);
        $webhookSetting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEBHOOK);
        
        // Calculate storage usage from attachments
        $storageUsage = $this->calculateStorageUsage();
        
        // Recycle bin data
        $recycleBinItems = null;
        $recycleBinStats = null;
        $recycleBinTypes = null;
        
        if ($activeTab === 'recycle-bin') {
            $filters = [
                'search' => $request->get('search'),
                'type' => $request->get('type'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];
            $recycleBinItems = $this->recycleBinService->getAllTrashedItems($filters);
            $recycleBinStats = $this->recycleBinService->getStatistics();
            $recycleBinTypes = $this->recycleBinService->getAvailableTypes();
        }
        
        return view('settings.integrations.index', compact(
            'activeTab',
            'emailSetting',
            'paymentSetting',
            'storageSetting',
            'weatherSetting',
            'webhookSetting',
            'storageUsage',
            'recycleBinItems',
            'recycleBinStats',
            'recycleBinTypes'
        ));
    }
    
    /**
     * Calculate storage usage from all attachments
     */
    private function calculateStorageUsage(): array
    {
        $totalBytes = \App\Models\Attachment::sum('file_size') ?? 0;
        $totalFiles = \App\Models\Attachment::count();
        
        // R2 Free tier is 10GB
        $freeLimit = 10 * 1024 * 1024 * 1024; // 10GB in bytes
        $usedBytes = $totalBytes;
        $freeBytes = max(0, $freeLimit - $usedBytes);
        $usagePercent = $freeLimit > 0 ? min(100, ($usedBytes / $freeLimit) * 100) : 0;
        
        return [
            'used_bytes' => $usedBytes,
            'used_formatted' => $this->formatBytes($usedBytes),
            'free_bytes' => $freeBytes,
            'free_formatted' => $this->formatBytes($freeBytes),
            'total_bytes' => $freeLimit,
            'total_formatted' => '10 GB',
            'usage_percent' => round($usagePercent, 1),
            'total_files' => $totalFiles,
        ];
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:smtp,google',
            'host' => 'required_if:provider,smtp',
            'port' => 'required_if:provider,smtp|numeric',
            'username' => 'required_if:provider,smtp',
            'password' => 'nullable',
            'encryption' => 'required_if:provider,smtp|in:tls,ssl,none',
            'from_address' => 'required|email',
            'from_name' => 'required',
            'google_client_id' => 'required_if:provider,google',
            'google_client_secret' => 'required_if:provider,google',
        ]);

        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_EMAIL);
        
        $credentials = $setting->getDecryptedCredentials();
        
        if ($request->provider === 'smtp') {
            $credentials = [
                'host' => $request->host,
                'port' => $request->port,
                'username' => $request->username,
                'encryption' => $request->encryption,
                'from_address' => $request->from_address,
                'from_name' => $request->from_name,
            ];
            if ($request->filled('password')) {
                $credentials['password'] = $request->password;
            } elseif (isset($setting->getDecryptedCredentials()['password'])) {
                $credentials['password'] = $setting->getDecryptedCredentials()['password'];
            }
        } else {
            $credentials = [
                'google_client_id' => $request->google_client_id,
                'from_address' => $request->from_address,
                'from_name' => $request->from_name,
            ];
            if ($request->filled('google_client_secret')) {
                $credentials['google_client_secret'] = $request->google_client_secret;
            } elseif (isset($setting->getDecryptedCredentials()['google_client_secret'])) {
                $credentials['google_client_secret'] = $setting->getDecryptedCredentials()['google_client_secret'];
            }
        }
        
        $setting->provider = $request->provider;
        $setting->setEncryptedCredentials($credentials);
        $setting->is_active = true;
        $setting->save();

        try {
            ActivityLogService::logSettingsUpdate('email_integration', [
                'provider' => $request->provider,
                'from_address' => $request->from_address,
                'from_name' => $request->from_name,
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'email'])
            ->with('success', 'Email settings saved successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);
        
        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_EMAIL);
        $credentials = $setting->getDecryptedCredentials();
        
        if (empty($credentials) || empty($credentials['host'])) {
            return response()->json(['success' => false, 'message' => 'Email not configured. Please save settings first.']);
        }

        try {
            if ($setting->provider === 'smtp') {
                $encryption = $credentials['encryption'] ?? 'tls';
                if ($encryption === 'none') {
                    $encryption = null;
                }
                
                $testEmail = $request->test_email;
                $fromName = $credentials['from_name'];
                $fromAddress = $credentials['from_address'];
                $host = $credentials['host'];
                $port = (int) ($credentials['port'] ?? 587);
                $username = $credentials['username'] ?? null;
                $password = $credentials['password'] ?? null;
                
                // Build DSN based on encryption type
                // For SSL (port 465): smtps://user:pass@host:465
                // For TLS/STARTTLS (port 587): smtp://user:pass@host:587
                // For None: smtp://user:pass@host:port
                
                // Note: Port 587 typically uses STARTTLS, port 465 uses implicit SSL
                // If user selects SSL with port 587, we'll use STARTTLS instead
                $useImplicitSsl = ($encryption === 'ssl' && $port == 465);
                
                if ($useImplicitSsl) {
                    // Use smtps:// scheme for implicit SSL (port 465)
                    $dsn = sprintf(
                        'smtps://%s:%s@%s:%d',
                        urlencode($username),
                        urlencode($password),
                        $host,
                        $port
                    );
                } else {
                    // Use smtp:// scheme - STARTTLS will be negotiated automatically
                    $dsn = sprintf(
                        'smtp://%s:%s@%s:%d',
                        urlencode($username),
                        urlencode($password),
                        $host,
                        $port
                    );
                }
                
                $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
                $mailer = new \Symfony\Component\Mailer\Mailer($transport);
                
                // Render the email template
                $htmlContent = view('emails.test-connection', [
                    'fromName' => $fromName,
                    'fromAddress' => $fromAddress,
                ])->render();
                
                // Create the email message
                $email = (new \Symfony\Component\Mime\Email())
                    ->from(new \Symfony\Component\Mime\Address($fromAddress, $fromName))
                    ->to($testEmail)
                    ->subject('ATLINE - Test Email Connection')
                    ->html($htmlContent);
                
                // Send the email
                $mailer->send($email);
            }
            
            $setting->last_tested_at = now();
            $setting->last_test_status = 'success';
            $setting->save();
            
            return response()->json([
                'success' => true, 
                'message' => 'Test email sent successfully to ' . $request->test_email . '. Please check your inbox and spam folder.'
            ]);
        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            
            return response()->json(['success' => false, 'message' => 'SMTP Connection Failed: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'brand_id' => 'required',
            'secret_key' => 'nullable',
            'webhook_url' => 'nullable|url',
        ]);

        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_PAYMENT);
        
        $credentials = $setting->getDecryptedCredentials();
        $credentials['brand_id'] = $request->brand_id;
        $credentials['webhook_url'] = $request->webhook_url;
        
        if ($request->filled('secret_key')) {
            $credentials['secret_key'] = $request->secret_key;
        }
        
        $setting->provider = IntegrationSetting::PROVIDER_CHIP_ASIA;
        $setting->setEncryptedCredentials($credentials);
        $setting->is_active = true;
        $setting->save();

        try {
            ActivityLogService::logSettingsUpdate('payment_integration', [
                'provider' => IntegrationSetting::PROVIDER_CHIP_ASIA,
                'brand_id' => $request->brand_id,
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'payment'])
            ->with('success', 'Payment gateway settings saved successfully.');
    }

    public function testPayment(Request $request)
    {
        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_PAYMENT);
        $credentials = $setting->getDecryptedCredentials();
        
        if (empty($credentials['brand_id']) || empty($credentials['secret_key'])) {
            return response()->json(['success' => false, 'message' => 'Payment gateway not configured']);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $credentials['secret_key'],
            ])->get('https://gate.chip-in.asia/api/v1/brands/' . $credentials['brand_id']);
            
            if ($response->successful()) {
                $setting->last_tested_at = now();
                $setting->last_test_status = 'success';
                $setting->save();
                return response()->json(['success' => true, 'message' => 'Connection successful!']);
            }
            
            throw new \Exception('Invalid credentials or brand ID');
        } catch (\Exception $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateStorage(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'access_key_id' => 'required',
            'bucket_name' => 'required',
            'folder_path' => 'nullable',
            'secret_access_key' => 'nullable',
            'public_url' => 'nullable|url',
        ]);

        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_STORAGE);
        
        $credentials = $setting->getDecryptedCredentials();
        $credentials['account_id'] = $request->account_id;
        $credentials['access_key_id'] = $request->access_key_id;
        $credentials['bucket_name'] = $request->bucket_name;
        $credentials['folder_path'] = $request->folder_path ?? 'downloads';
        $credentials['public_url'] = $request->public_url;
        
        if ($request->filled('secret_access_key')) {
            $credentials['secret_access_key'] = $request->secret_access_key;
        }
        
        $setting->provider = 'cloudflare-r2';
        $setting->setEncryptedCredentials($credentials);
        $setting->is_active = true;
        $setting->save();

        try {
            ActivityLogService::logSettingsUpdate('storage_integration', [
                'provider' => 'cloudflare-r2',
                'bucket_name' => $request->bucket_name,
                'folder_path' => $request->folder_path ?? 'downloads',
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'storage'])
            ->with('success', 'Cloudflare R2 settings saved successfully.');
    }

    public function testStorage(Request $request)
    {
        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_STORAGE);
        $credentials = $setting->getDecryptedCredentials();
        
        if (empty($credentials['account_id']) || empty($credentials['access_key_id']) || empty($credentials['secret_access_key']) || empty($credentials['bucket_name'])) {
            return response()->json(['success' => false, 'message' => 'Cloudflare R2 not configured. Please provide Account ID, Access Key, Secret Key, and Bucket Name.']);
        }

        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => 'auto',
                'endpoint' => "https://{$credentials['account_id']}.r2.cloudflarestorage.com",
                'credentials' => [
                    'key' => $credentials['access_key_id'],
                    'secret' => $credentials['secret_access_key'],
                ],
            ]);
            
            // Try to list objects to verify connection
            $s3Client->listObjectsV2([
                'Bucket' => $credentials['bucket_name'],
                'MaxKeys' => 1,
            ]);
            
            $setting->last_tested_at = now();
            $setting->last_test_status = 'success';
            $setting->save();
            
            return response()->json(['success' => true, 'message' => 'Connection successful! Bucket "' . $credentials['bucket_name'] . '" is accessible.']);
        } catch (\Exception $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateWeather(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable',
            'default_city' => 'nullable',
            'units' => 'required|in:metric,imperial',
        ]);

        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEATHER);
        
        $credentials = $setting->getDecryptedCredentials();
        $credentials['default_city'] = $request->default_city;
        $credentials['units'] = $request->units;
        
        if ($request->filled('api_key')) {
            $credentials['api_key'] = $request->api_key;
        }
        
        $setting->provider = IntegrationSetting::PROVIDER_OPENWEATHERMAP;
        $setting->setEncryptedCredentials($credentials);
        $setting->is_active = true;
        $setting->save();

        try {
            ActivityLogService::logSettingsUpdate('weather_integration', [
                'provider' => IntegrationSetting::PROVIDER_OPENWEATHERMAP,
                'default_city' => $request->default_city,
                'units' => $request->units,
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'weather'])
            ->with('success', 'Weather settings saved successfully.');
    }

    public function testWeather(Request $request)
    {
        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEATHER);
        $credentials = $setting->getDecryptedCredentials();
        
        if (empty($credentials['api_key'])) {
            return response()->json(['success' => false, 'message' => 'API key not configured']);
        }

        try {
            $city = $credentials['default_city'] ?? 'Kuala Lumpur';
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => $credentials['api_key'],
                'units' => $credentials['units'] ?? 'metric',
            ]);
            
            if ($response->successful()) {
                $setting->last_tested_at = now();
                $setting->last_test_status = 'success';
                $setting->save();
                
                $data = $response->json();
                return response()->json([
                    'success' => true, 
                    'message' => "Connection successful! Current weather in {$city}: {$data['main']['temp']}°"
                ]);
            }
            
            throw new \Exception('Invalid API key');
        } catch (\Exception $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateWebhook(Request $request)
    {
        $request->validate([
            'webhook_url' => 'nullable|url',
            'webhook_secret' => 'nullable',
            'events' => 'nullable|array',
        ]);

        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEBHOOK);
        
        $credentials = [
            'webhook_url' => $request->webhook_url,
            'webhook_secret' => $request->webhook_secret,
        ];
        
        $setting->settings = ['events' => $request->events ?? []];
        $setting->setEncryptedCredentials($credentials);
        $setting->is_active = !empty($request->webhook_url);
        $setting->save();

        try {
            ActivityLogService::logSettingsUpdate('webhook_integration', [
                'webhook_url' => $request->webhook_url,
                'events' => $request->events ?? [],
            ]);
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'webhooks'])
            ->with('success', 'Webhook settings saved successfully.');
    }

    public function testWebhook(Request $request)
    {
        $setting = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_WEBHOOK);
        $credentials = $setting->getDecryptedCredentials();
        
        if (empty($credentials['webhook_url'])) {
            return response()->json(['success' => false, 'message' => 'Webhook URL not configured']);
        }

        try {
            $payload = [
                'event' => 'test.connection',
                'timestamp' => now()->toIso8601String(),
                'data' => ['message' => 'Test webhook from ATLINE System'],
            ];
            
            $response = Http::timeout(10)->post($credentials['webhook_url'], $payload);
            
            if ($response->successful()) {
                $setting->last_tested_at = now();
                $setting->last_test_status = 'success';
                $setting->save();
                return response()->json(['success' => true, 'message' => 'Webhook test sent successfully!']);
            }
            
            throw new \Exception('Webhook endpoint returned error: ' . $response->status());
        } catch (\Exception $e) {
            $setting->last_tested_at = now();
            $setting->last_test_status = 'failed';
            $setting->save();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Restore a trashed item from recycle bin
     */
    public function restoreItem(Request $request, string $type, int $id)
    {
        if (!auth()->user()->hasPermission('settings_integrations_recycle_bin.update')) {
            return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
                ->with('error', 'You do not have permission to restore items.');
        }

        $result = $this->recycleBinService->restore($type, $id);

        if ($result) {
            return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
                ->with('success', 'Item restored successfully.');
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
            ->with('error', 'Failed to restore item. Item may not exist.');
    }

    /**
     * Permanently delete a trashed item
     */
    public function forceDeleteItem(Request $request, string $type, int $id)
    {
        if (!auth()->user()->hasPermission('settings_integrations_recycle_bin.delete')) {
            return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
                ->with('error', 'You do not have permission to permanently delete items.');
        }

        $result = $this->recycleBinService->forceDelete($type, $id);

        if ($result) {
            return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
                ->with('success', 'Item permanently deleted.');
        }

        return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
            ->with('error', 'Failed to delete item. Item may not exist.');
    }

    /**
     * Bulk delete items older than specified days
     */
    public function bulkDeleteRecycleBin(Request $request)
    {
        if (!auth()->user()->hasPermission('settings_integrations_recycle_bin.delete')) {
            return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
                ->with('error', 'You do not have permission to delete items.');
        }

        $request->validate([
            'days' => 'required|in:30,60,90',
        ]);

        $days = (int) $request->days;
        $deletedCount = $this->recycleBinService->bulkDeleteByAge($days);

        return redirect()->route('settings.integrations.index', ['tab' => 'recycle-bin'])
            ->with('success', "Deleted {$deletedCount} items older than {$days} days.");
    }
}
