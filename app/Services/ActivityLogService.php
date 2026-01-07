<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Sensitive keys that should be excluded from properties.
     */
    private static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'api_key',
        'api_secret',
        'secret_key',
        'access_key',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'encrypted_password',
        'encrypted_username',
        'smtp_password',
        'r2_secret_access_key',
    ];

    /**
     * Get client_id and employee_id for current authenticated user.
     */
    private static function getUserContext(): array
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return ['client_id' => null, 'employee_id' => null];
        }

        // Check if user is linked to a client
        $client = Client::where('user_id', $userId)->first();
        
        // Check if user is linked to an employee
        $employee = Employee::where('user_id', $userId)->first();

        return [
            'client_id' => $client?->id,
            'employee_id' => $employee?->id,
        ];
    }

    /**
     * Sanitize properties to remove sensitive data.
     */
    private static function sanitizeProperties(?array $properties): ?array
    {
        if (!$properties) {
            return null;
        }

        return self::recursiveSanitize($properties);
    }

    /**
     * Recursively sanitize array to remove sensitive keys.
     */
    private static function recursiveSanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::$sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = self::recursiveSanitize($value);
            }
        }

        return $data;
    }

    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        string $description,
        ?string $module = null,
        ?Model $subject = null,
        ?array $properties = null
    ): ActivityLog {
        $userContext = self::getUserContext();

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'client_id' => $userContext['client_id'],
            'employee_id' => $userContext['employee_id'],
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'properties' => self::sanitizeProperties($properties),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a login activity.
     */
    public static function logLogin(): ActivityLog
    {
        return self::log('login', 'User logged in', 'auth');
    }

    /**
     * Log a logout activity.
     */
    public static function logLogout(): ActivityLog
    {
        return self::log('logout', 'User logged out', 'auth');
    }

    /**
     * Log a create activity.
     */
    public static function logCreate(Model $model, string $module, ?string $description = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = $description ?? "Created {$modelName}";
        
        return self::log('create', $description, $module, $model, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log an update activity.
     */
    public static function logUpdate(Model $model, string $module, array $oldValues, ?string $description = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = $description ?? "Updated {$modelName}";
        
        return self::log('update', $description, $module, $model, [
            'old' => $oldValues,
            'new' => $model->getChanges(),
        ]);
    }

    /**
     * Log a delete activity.
     */
    public static function logDelete(Model $model, string $module, ?string $description = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = $description ?? "Deleted {$modelName}";
        
        return self::log('delete', $description, $module, $model, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log a view activity.
     */
    public static function logView(Model $model, string $module, ?string $description = null): ActivityLog
    {
        $modelName = class_basename($model);
        $description = $description ?? "Viewed {$modelName}";
        
        return self::log('view', $description, $module, $model);
    }

    /**
     * Log an export activity.
     */
    public static function logExport(string $module, string $description, ?array $filters = null): ActivityLog
    {
        return self::log('export', $description, $module, null, [
            'filters' => $filters,
        ]);
    }

    /**
     * Log a download activity.
     */
    public static function logDownload(string $module, string $description, ?array $fileDetails = null): ActivityLog
    {
        return self::log('download', $description, $module, null, [
            'file' => $fileDetails,
        ]);
    }

    /**
     * Log a password change activity.
     */
    public static function logPasswordChange(): ActivityLog
    {
        return self::log('password_change', 'User changed password', 'auth');
    }

    /**
     * Log a 2FA change activity.
     */
    public static function log2FAChange(string $action): ActivityLog
    {
        $description = $action === 'enabled' ? 'User enabled 2FA' : 'User disabled 2FA';
        return self::log('2fa_' . $action, $description, 'auth');
    }

    /**
     * Log a failed login attempt.
     */
    public static function logFailedLogin(string $email): ActivityLog
    {
        return self::log('login_failed', "Failed login attempt for {$email}", 'auth', null, [
            'email' => $email,
        ]);
    }

    /**
     * Log a ticket reply activity.
     */
    public static function logReply(Model $ticket, string $description): ActivityLog
    {
        return self::log('reply', $description, 'helpdesk', $ticket);
    }

    /**
     * Log an asset movement (checkout/checkin) activity.
     */
    public static function logMovement(Model $asset, Model $employee, string $action): ActivityLog
    {
        $description = $action === 'checkout' 
            ? "Asset checked out to {$employee->name}"
            : "Asset checked in from {$employee->name}";
        
        return self::log($action, $description, 'internal_inventory', $asset, [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
        ]);
    }

    /**
     * Log a settings update activity.
     */
    public static function logSettingsUpdate(string $category, array $changes): ActivityLog
    {
        return self::log('update', "Updated {$category} settings", 'settings_config', null, [
            'category' => $category,
            'changes' => $changes,
        ]);
    }
}
