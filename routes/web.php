<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\External\ProjectController;
use App\Http\Controllers\External\AssetController;
use App\Http\Controllers\External\ExternalSettingsController;
use App\Http\Controllers\External\ReportController;
use App\Http\Controllers\AttachmentController;

// Guest routes with rate limiting protection
Route::middleware('guest')->group(function () {
    // Login page - rate limited (10 req/min per IP)
    Route::get('login', [LoginController::class, 'showLoginForm'])
        ->name('login')
        ->middleware('throttle:login-page');
    
    // Login attempt - stricter rate limit (5 attempts/min per IP)
    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:login-attempt');
    
    // 2FA Challenge (accessible without full auth)
    Route::get('2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge')->withoutMiddleware('guest');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify')->withoutMiddleware('guest');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/2fa/setup', [\App\Http\Controllers\ProfileController::class, 'show2FASetup'])->name('profile.2fa.setup');
    Route::post('/profile/2fa/enable', [\App\Http\Controllers\ProfileController::class, 'enable2FA'])->name('profile.2fa.enable');
    Route::get('/profile/2fa/recovery', [\App\Http\Controllers\ProfileController::class, 'showRecoveryCodes'])->name('profile.2fa.recovery');
    Route::post('/profile/2fa/regenerate', [\App\Http\Controllers\ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.2fa.regenerate');
    Route::delete('/profile/2fa', [\App\Http\Controllers\ProfileController::class, 'disable2FA'])->name('profile.2fa.disable');
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware('permission');
    
    // Internal
    Route::prefix('internal')->name('internal.')->middleware('permission')->group(function () {
        // Credentials
        Route::get('/credentials', [\App\Http\Controllers\Internal\CredentialController::class, 'index'])->name('credentials.index');
        Route::get('/credentials/create', [\App\Http\Controllers\Internal\CredentialController::class, 'create'])->name('credentials.create');
        Route::post('/credentials', [\App\Http\Controllers\Internal\CredentialController::class, 'store'])->name('credentials.store');
        Route::get('/credentials/{credential}', [\App\Http\Controllers\Internal\CredentialController::class, 'show'])->name('credentials.show');
        Route::get('/credentials/{credential}/edit', [\App\Http\Controllers\Internal\CredentialController::class, 'edit'])->name('credentials.edit');
        Route::put('/credentials/{credential}', [\App\Http\Controllers\Internal\CredentialController::class, 'update'])->name('credentials.update');
        Route::delete('/credentials/{credential}', [\App\Http\Controllers\Internal\CredentialController::class, 'destroy'])->name('credentials.destroy');
        Route::post('/credentials/initialize', [\App\Http\Controllers\Internal\CredentialController::class, 'initializeVault'])->name('credentials.initialize');
        Route::post('/credentials/verify-pin', [\App\Http\Controllers\Internal\CredentialController::class, 'verifyPin'])->name('credentials.verify-pin');
        Route::get('/credentials/{credential}/encrypted-data', [\App\Http\Controllers\Internal\CredentialController::class, 'getEncryptedData'])->name('credentials.encrypted-data');
        Route::get('/credentials/current-pin', [\App\Http\Controllers\Internal\CredentialController::class, 'getCurrentPin'])->name('credentials.current-pin');
        
        // Download
        Route::get('/download', [\App\Http\Controllers\Internal\DownloadController::class, 'index'])->name('download.index');
        Route::get('/download/{download}', [\App\Http\Controllers\Internal\DownloadController::class, 'show'])->name('download.show');
        Route::post('/download', [\App\Http\Controllers\Internal\DownloadController::class, 'store'])->name('download.store');
        Route::delete('/download/{download}', [\App\Http\Controllers\Internal\DownloadController::class, 'destroy'])->name('download.destroy');
        Route::get('/download/{download}/file', [\App\Http\Controllers\Internal\DownloadController::class, 'download'])->name('download.file');
        Route::get('/download/{download}/progress', [\App\Http\Controllers\Internal\DownloadController::class, 'checkProgress'])->name('download.progress');
        
        // Internal Inventory
        Route::get('/inventory', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'index'])->name('inventory.index');
        
        // Assets
        Route::post('/inventory/assets', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'storeAsset'])->name('inventory.assets.store');
        Route::put('/inventory/assets/{asset}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'updateAsset'])->name('inventory.assets.update');
        Route::delete('/inventory/assets/{asset}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'destroyAsset'])->name('inventory.assets.destroy');
        
        // Checkout/Checkin
        Route::post('/inventory/checkout', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'checkout'])->name('inventory.checkout');
        Route::post('/inventory/checkin/{movement}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'checkin'])->name('inventory.checkin');
        
        // Locations
        Route::post('/inventory/locations', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'storeLocation'])->name('inventory.locations.store');
        Route::put('/inventory/locations/{location}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'updateLocation'])->name('inventory.locations.update');
        Route::delete('/inventory/locations/{location}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'destroyLocation'])->name('inventory.locations.destroy');
        
        // Brands
        Route::post('/inventory/brands', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'storeBrand'])->name('inventory.brands.store');
        Route::put('/inventory/brands/{brand}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'updateBrand'])->name('inventory.brands.update');
        Route::delete('/inventory/brands/{brand}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'destroyBrand'])->name('inventory.brands.destroy');
        
        // Categories
        Route::post('/inventory/categories', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'storeCategory'])->name('inventory.categories.store');
        Route::put('/inventory/categories/{category}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'updateCategory'])->name('inventory.categories.update');
        Route::delete('/inventory/categories/{category}', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'destroyCategory'])->name('inventory.categories.destroy');
        
        // Toggle Status
        Route::post('/inventory/{type}/{id}/toggle-status', [\App\Http\Controllers\Internal\InternalInventoryController::class, 'toggleStatus'])->name('inventory.toggle-status');
        
        // Employee Management
        Route::resource('employee', \App\Http\Controllers\Internal\EmployeeController::class);
        Route::get('employee/attachment/{attachment}/download', [\App\Http\Controllers\Internal\EmployeeController::class, 'downloadAttachment'])->name('employee.attachment.download');
    });
    
    // External
    Route::prefix('external')->name('external.')->middleware('permission')->group(function () {
        Route::resource('projects', ProjectController::class);
        
        // Inventory/Assets
        Route::resource('inventory', AssetController::class);
        Route::get('inventory/category/{category}/fields', [AssetController::class, 'getDynamicFields'])->name('inventory.fields');
        Route::get('inventory/category/{category}/generate-id', [AssetController::class, 'generateAssetId'])->name('inventory.generate-id');
        
        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        
        // Attachments
        Route::get('attachments', [\App\Http\Controllers\External\AttachmentController::class, 'index'])->name('attachments.index');
        Route::get('attachments/{attachment}/download', [\App\Http\Controllers\External\AttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('attachments/{attachment}', [\App\Http\Controllers\External\AttachmentController::class, 'destroy'])->name('attachments.destroy');
        
        // Settings
        Route::get('settings', [ExternalSettingsController::class, 'index'])->name('settings.index');
        
        // Clients
        Route::post('settings/clients', [ExternalSettingsController::class, 'storeClient'])->name('settings.clients.store');
        Route::put('settings/clients/{client}', [ExternalSettingsController::class, 'updateClient'])->name('settings.clients.update');
        Route::delete('settings/clients/{client}', [ExternalSettingsController::class, 'destroyClient'])->name('settings.clients.destroy');
        
        // Vendors
        Route::post('settings/vendors', [ExternalSettingsController::class, 'storeVendor'])->name('settings.vendors.store');
        Route::put('settings/vendors/{vendor}', [ExternalSettingsController::class, 'updateVendor'])->name('settings.vendors.update');
        Route::delete('settings/vendors/{vendor}', [ExternalSettingsController::class, 'destroyVendor'])->name('settings.vendors.destroy');
        
        // Locations
        Route::post('settings/locations', [ExternalSettingsController::class, 'storeLocation'])->name('settings.locations.store');
        Route::put('settings/locations/{location}', [ExternalSettingsController::class, 'updateLocation'])->name('settings.locations.update');
        Route::delete('settings/locations/{location}', [ExternalSettingsController::class, 'destroyLocation'])->name('settings.locations.destroy');
        
        // Brands
        Route::post('settings/brands', [ExternalSettingsController::class, 'storeBrand'])->name('settings.brands.store');
        Route::put('settings/brands/{brand}', [ExternalSettingsController::class, 'updateBrand'])->name('settings.brands.update');
        Route::delete('settings/brands/{brand}', [ExternalSettingsController::class, 'destroyBrand'])->name('settings.brands.destroy');
        
        // Categories
        Route::post('settings/categories', [ExternalSettingsController::class, 'storeCategory'])->name('settings.categories.store');
        Route::put('settings/categories/{category}', [ExternalSettingsController::class, 'updateCategory'])->name('settings.categories.update');
        Route::delete('settings/categories/{category}', [ExternalSettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');
        
        // Toggle Status
        Route::post('settings/{type}/{id}/toggle-status', [ExternalSettingsController::class, 'toggleStatus'])->name('settings.toggle-status');
    });
    
    // Attachments
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    
    // Helpdesk
    Route::prefix('helpdesk')->name('helpdesk.')->middleware('permission')->group(function () {
        Route::get('/', [\App\Http\Controllers\HelpdeskController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\HelpdeskController::class, 'store'])->name('store');
        Route::post('/verify-serial', [\App\Http\Controllers\HelpdeskController::class, 'verifySerialNumber'])->name('verify-serial');
        Route::get('/attachment/{attachment}/download', [\App\Http\Controllers\HelpdeskController::class, 'downloadAttachment'])->name('attachment.download');
        
        // Ticket Categories
        Route::post('/categories', [\App\Http\Controllers\HelpdeskController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [\App\Http\Controllers\HelpdeskController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [\App\Http\Controllers\HelpdeskController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/categories/{category}/toggle-status', [\App\Http\Controllers\HelpdeskController::class, 'toggleCategoryStatus'])->name('categories.toggle-status');
        
        // Ticket Priorities
        Route::post('/priorities', [\App\Http\Controllers\HelpdeskController::class, 'storePriority'])->name('priorities.store');
        Route::put('/priorities/{priority}', [\App\Http\Controllers\HelpdeskController::class, 'updatePriority'])->name('priorities.update');
        Route::delete('/priorities/{priority}', [\App\Http\Controllers\HelpdeskController::class, 'destroyPriority'])->name('priorities.destroy');
        Route::post('/priorities/{priority}/toggle-status', [\App\Http\Controllers\HelpdeskController::class, 'togglePriorityStatus'])->name('priorities.toggle-status');
        Route::post('/priorities/{priority}/set-default', [\App\Http\Controllers\HelpdeskController::class, 'setDefaultPriority'])->name('priorities.set-default');
        
        // Ticket Statuses
        Route::post('/statuses', [\App\Http\Controllers\HelpdeskController::class, 'storeStatus'])->name('statuses.store');
        Route::put('/statuses/{status}', [\App\Http\Controllers\HelpdeskController::class, 'updateTicketStatus'])->name('statuses.update');
        Route::delete('/statuses/{status}', [\App\Http\Controllers\HelpdeskController::class, 'destroyStatus'])->name('statuses.destroy');
        Route::post('/statuses/{status}/toggle-active', [\App\Http\Controllers\HelpdeskController::class, 'toggleStatusActive'])->name('statuses.toggle-active');
        Route::post('/statuses/{status}/set-default', [\App\Http\Controllers\HelpdeskController::class, 'setDefaultStatus'])->name('statuses.set-default');
        
        // Email Templates (edit only, no create/delete)
        Route::get('/templates/{emailTemplate}/edit', [\App\Http\Controllers\HelpdeskController::class, 'editTemplate'])->name('templates.edit');
        Route::put('/templates/{emailTemplate}', [\App\Http\Controllers\HelpdeskController::class, 'updateTemplate'])->name('templates.update');
        
        Route::get('/{ticket}', [\App\Http\Controllers\HelpdeskController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\HelpdeskController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/status', [\App\Http\Controllers\HelpdeskController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/assign', [\App\Http\Controllers\HelpdeskController::class, 'assign'])->name('assign');
        Route::delete('/{ticket}', [\App\Http\Controllers\HelpdeskController::class, 'destroy'])->name('destroy');
    });
    
    // System Settings
    Route::prefix('settings')->name('settings.')->middleware('permission')->group(function () {
        // Configuration
        Route::get('/configuration', [\App\Http\Controllers\Settings\ConfigurationController::class, 'index'])->name('configuration.index');
        Route::post('/configuration', [\App\Http\Controllers\Settings\ConfigurationController::class, 'update'])->name('configuration.update');
        
        // Roles
        Route::resource('roles', \App\Http\Controllers\Settings\RoleController::class);
        
        // Users
        Route::resource('users', \App\Http\Controllers\Settings\UserController::class);
        
        // Integrations
        Route::get('/integrations', [\App\Http\Controllers\Settings\IntegrationController::class, 'index'])->name('integrations.index');
        Route::post('/integrations/email', [\App\Http\Controllers\Settings\IntegrationController::class, 'updateEmail'])->name('integrations.email.update');
        Route::post('/integrations/email/test', [\App\Http\Controllers\Settings\IntegrationController::class, 'testEmail'])->name('integrations.email.test');
        Route::post('/integrations/payment', [\App\Http\Controllers\Settings\IntegrationController::class, 'updatePayment'])->name('integrations.payment.update');
        Route::post('/integrations/payment/test', [\App\Http\Controllers\Settings\IntegrationController::class, 'testPayment'])->name('integrations.payment.test');
        Route::post('/integrations/storage', [\App\Http\Controllers\Settings\IntegrationController::class, 'updateStorage'])->name('integrations.storage.update');
        Route::post('/integrations/storage/test', [\App\Http\Controllers\Settings\IntegrationController::class, 'testStorage'])->name('integrations.storage.test');
        Route::post('/integrations/weather', [\App\Http\Controllers\Settings\IntegrationController::class, 'updateWeather'])->name('integrations.weather.update');
        Route::post('/integrations/weather/test', [\App\Http\Controllers\Settings\IntegrationController::class, 'testWeather'])->name('integrations.weather.test');
        Route::post('/integrations/webhook', [\App\Http\Controllers\Settings\IntegrationController::class, 'updateWebhook'])->name('integrations.webhook.update');
        Route::post('/integrations/webhook/test', [\App\Http\Controllers\Settings\IntegrationController::class, 'testWebhook'])->name('integrations.webhook.test');
        
        // Recycle Bin
        Route::post('/integrations/recycle-bin/{type}/{id}/restore', [\App\Http\Controllers\Settings\IntegrationController::class, 'restoreItem'])->name('integrations.recycle-bin.restore');
        Route::delete('/integrations/recycle-bin/{type}/{id}', [\App\Http\Controllers\Settings\IntegrationController::class, 'forceDeleteItem'])->name('integrations.recycle-bin.force-delete');
        Route::delete('/integrations/recycle-bin/bulk-delete', [\App\Http\Controllers\Settings\IntegrationController::class, 'bulkDeleteRecycleBin'])->name('integrations.recycle-bin.bulk-delete');
        
        Route::get('/activity-logs', [\App\Http\Controllers\Settings\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::delete('/activity-logs', [\App\Http\Controllers\Settings\ActivityLogController::class, 'delete'])->name('activity-logs.delete');
    });
});
