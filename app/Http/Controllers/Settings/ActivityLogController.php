<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs with tabs.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'activity');
        
        $data = [
            'activeTab' => $activeTab,
        ];

        if ($activeTab === 'audit') {
            // Audit tab - User Activity Audit
            $data['users'] = User::orderBy('name')->get();
            $data['selectedUser'] = null;
            $data['auditLogs'] = collect();
            $data['auditStats'] = null;

            // Only show results if user_id is selected
            if ($request->filled('user_id')) {
                $selectedUser = User::find($request->user_id);
                $data['selectedUser'] = $selectedUser;

                if ($selectedUser) {
                    $query = ActivityLog::with(['user'])
                        ->where('user_id', $request->user_id)
                        ->orderBy('created_at', 'desc');

                    // DateTime range filter (supports datetime-local format)
                    if ($request->filled('date_from')) {
                        $query->where('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->where('created_at', '<=', $request->date_to);
                    }

                    $data['auditLogs'] = $query->paginate(50)->withQueryString();

                    // Calculate stats for this user in date range
                    $statsQuery = ActivityLog::where('user_id', $request->user_id);
                    if ($request->filled('date_from')) {
                        $statsQuery->where('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $statsQuery->where('created_at', '<=', $request->date_to);
                    }

                    $data['auditStats'] = [
                        'total' => $statsQuery->count(),
                        'logins' => (clone $statsQuery)->where('action', 'login')->count(),
                        'failed_logins' => (clone $statsQuery)->where('action', 'login_failed')->count(),
                        'creates' => (clone $statsQuery)->where('action', 'create')->count(),
                        'updates' => (clone $statsQuery)->where('action', 'update')->count(),
                        'deletes' => (clone $statsQuery)->where('action', 'delete')->count(),
                        'views' => (clone $statsQuery)->where('action', 'view')->count(),
                        'downloads' => (clone $statsQuery)->where('action', 'download')->count(),
                        'first_activity' => (clone $statsQuery)->orderBy('created_at', 'asc')->first()?->created_at,
                        'last_activity' => (clone $statsQuery)->orderBy('created_at', 'desc')->first()?->created_at,
                        'modules' => (clone $statsQuery)->select('module')->distinct()->pluck('module')->filter()->values(),
                        'ip_addresses' => (clone $statsQuery)->select('ip_address')->distinct()->pluck('ip_address')->filter()->values(),
                    ];
                }
            }
        } elseif ($activeTab === 'suspicious') {
            // Suspicious tab - Auto detect suspicious activity
            $suspiciousData = $this->detectSuspiciousActivity($request);
            $data['suspiciousLogs'] = $suspiciousData['logs'];
            $data['suspiciousStats'] = $suspiciousData['stats'];
        } elseif ($activeTab === 'activity') {
            $query = ActivityLog::with(['user', 'client', 'employee'])
                ->orderBy('created_at', 'desc');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhere('module', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Action filter
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            // Module filter
            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }

            // User filter
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Client filter
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            // Employee filter
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // User type filter (client/staff/system)
            if ($request->filled('user_type')) {
                $userType = $request->user_type;
                if ($userType === 'client') {
                    $query->whereNotNull('client_id');
                } elseif ($userType === 'staff') {
                    $query->whereNotNull('employee_id');
                } elseif ($userType === 'system') {
                    $query->whereNull('client_id')->whereNull('employee_id');
                }
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $data['logs'] = $query->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
            $data['users'] = User::where('is_active', true)->orderBy('name')->get();
            $data['clients'] = Client::orderBy('name')->get();
            $data['employees'] = Employee::orderBy('full_name')->get();
            $data['actions'] = ActivityLog::distinct()->pluck('action')->filter()->sort()->values();
            $data['modules'] = ActivityLog::distinct()->pluck('module')->filter()->sort()->values();
            
            // Calculate storage usage
            $storageInfo = $this->calculateStorageUsage();
            $data['storageUsed'] = $storageInfo['formatted'];
            $data['storagePercent'] = $storageInfo['percent'];
        }

        return view('settings.activity-logs.index', $data);
    }

    /**
     * Delete activity logs older than specified days.
     */
    public function delete(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|in:30,60,90',
        ]);

        $days = $request->days;
        $cutoffDate = now()->subDays($days);

        $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

        return redirect()
            ->route('settings.activity-logs.index', ['tab' => 'activity'])
            ->with('success', "Successfully deleted {$deletedCount} activity logs older than {$days} days.");
    }

    /**
     * Detect suspicious activity patterns.
     */
    private function detectSuspiciousActivity(Request $request): array
    {
        $suspiciousLogs = collect();
        $stats = [
            'failed_logins' => 0,
            'brute_force_attempts' => 0,
            'injection_attempts' => 0,
            'unusual_hours' => 0,
            'mass_operations' => 0,
            'multiple_ips' => 0,
            'total' => 0,
        ];

        // SQL/XSS Injection patterns to detect
        $injectionPatterns = [
            'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'UNION', 'OR 1=1', 'OR 1 = 1',
            '--', ';--', '/*', '*/', 'xp_', 'EXEC', 'EXECUTE',
            '<script', '</script>', 'javascript:', 'onerror=', 'onload=', 'onclick=',
            'alert(', 'document.cookie', 'eval(', '.innerHTML',
            '../', '..\\', '/etc/passwd', 'cmd.exe', 'powershell',
        ];

        // 1. Failed login attempts (last 7 days)
        $failedLogins = ActivityLog::with('user')
            ->where('action', 'login_failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($failedLogins as $log) {
            $log->suspicious_type = 'Failed Login';
            $log->risk_level = 'medium';
            $suspiciousLogs->push($log);
            $stats['failed_logins']++;
        }

        // 2. Brute force detection (3+ failed logins from same IP in 1 hour)
        $bruteForceIPs = ActivityLog::where('action', 'login_failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('ip_address', DB::raw('COUNT(*) as attempt_count'), DB::raw('MIN(created_at) as first_attempt'))
            ->groupBy('ip_address')
            ->having('attempt_count', '>=', 3)
            ->get();

        foreach ($bruteForceIPs as $bf) {
            $stats['brute_force_attempts']++;
        }

        // 3. Check for injection patterns in properties/description
        $allLogs = ActivityLog::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        foreach ($allLogs as $log) {
            $checkText = strtoupper($log->description . ' ' . json_encode($log->properties));
            
            foreach ($injectionPatterns as $pattern) {
                if (stripos($checkText, $pattern) !== false) {
                    $log->suspicious_type = 'Possible Injection Attempt';
                    $log->risk_level = 'high';
                    $log->detected_pattern = $pattern;
                    if (!$suspiciousLogs->contains('id', $log->id)) {
                        $suspiciousLogs->push($log);
                        $stats['injection_attempts']++;
                    }
                    break;
                }
            }
        }

        // 4. Unusual hours activity (outside 6 AM - 10 PM)
        $unusualHoursLogs = ActivityLog::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->whereRaw('HOUR(created_at) < 6 OR HOUR(created_at) > 22')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        foreach ($unusualHoursLogs as $log) {
            if (!$suspiciousLogs->contains('id', $log->id)) {
                $log->suspicious_type = 'Unusual Hours Activity';
                $log->risk_level = 'low';
                $suspiciousLogs->push($log);
                $stats['unusual_hours']++;
            }
        }

        // 5. Mass operations (user doing 20+ same action in 10 minutes)
        $massOps = ActivityLog::where('created_at', '>=', now()->subDays(7))
            ->select('user_id', 'action', DB::raw('COUNT(*) as op_count'), DB::raw('DATE(created_at) as op_date'))
            ->groupBy('user_id', 'action', DB::raw('DATE(created_at)'))
            ->having('op_count', '>=', 20)
            ->get();

        foreach ($massOps as $op) {
            $stats['mass_operations']++;
        }

        // 6. Multiple IPs for same user in short time
        $multipleIPs = ActivityLog::where('created_at', '>=', now()->subDays(1))
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('COUNT(DISTINCT ip_address) as ip_count'))
            ->groupBy('user_id')
            ->having('ip_count', '>=', 3)
            ->get();

        foreach ($multipleIPs as $mip) {
            $user = User::find($mip->user_id);
            if ($user) {
                $recentLogs = ActivityLog::with('user')
                    ->where('user_id', $mip->user_id)
                    ->where('created_at', '>=', now()->subDays(1))
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($recentLogs as $log) {
                    if (!$suspiciousLogs->contains('id', $log->id)) {
                        $log->suspicious_type = 'Multiple IPs Detected';
                        $log->risk_level = 'medium';
                        $suspiciousLogs->push($log);
                        $stats['multiple_ips']++;
                    }
                }
            }
        }

        // Sort by risk level and date
        $suspiciousLogs = $suspiciousLogs->sortByDesc(function ($log) {
            $riskOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            return ($riskOrder[$log->risk_level] ?? 0) * 1000000 + $log->created_at->timestamp;
        })->values();

        $stats['total'] = $suspiciousLogs->count();

        return [
            'logs' => $suspiciousLogs->take(100),
            'stats' => $stats,
        ];
    }

    /**
     * Calculate storage usage for activity_logs table.
     * Limit is 5MB.
     */
    private function calculateStorageUsage(): array
    {
        $maxSizeBytes = 5 * 1024 * 1024; // 5MB in bytes
        
        try {
            // Get table size from MySQL
            $tableName = (new ActivityLog)->getTable();
            $dbName = config('database.connections.mysql.database');
            
            $result = DB::select("
                SELECT 
                    (data_length + index_length) as size_bytes
                FROM information_schema.tables 
                WHERE table_schema = ? 
                AND table_name = ?
            ", [$dbName, $tableName]);

            $sizeBytes = $result[0]->size_bytes ?? 0;
            
            // Format size
            if ($sizeBytes >= 1024 * 1024) {
                $formatted = number_format($sizeBytes / (1024 * 1024), 2) . ' MB';
            } elseif ($sizeBytes >= 1024) {
                $formatted = number_format($sizeBytes / 1024, 2) . ' KB';
            } else {
                $formatted = $sizeBytes . ' B';
            }

            $percent = round(($sizeBytes / $maxSizeBytes) * 100, 1);

            return [
                'bytes' => $sizeBytes,
                'formatted' => $formatted,
                'percent' => min($percent, 100),
            ];
        } catch (\Exception $e) {
            // Fallback: estimate based on row count
            $rowCount = ActivityLog::count();
            $estimatedSizeBytes = $rowCount * 500; // Estimate ~500 bytes per row
            
            if ($estimatedSizeBytes >= 1024 * 1024) {
                $formatted = number_format($estimatedSizeBytes / (1024 * 1024), 2) . ' MB';
            } elseif ($estimatedSizeBytes >= 1024) {
                $formatted = number_format($estimatedSizeBytes / 1024, 2) . ' KB';
            } else {
                $formatted = $estimatedSizeBytes . ' B';
            }

            $percent = round(($estimatedSizeBytes / $maxSizeBytes) * 100, 1);

            return [
                'bytes' => $estimatedSizeBytes,
                'formatted' => $formatted,
                'percent' => min($percent, 100),
            ];
        }
    }
}
