<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketNotificationJob;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketEmailTemplate;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketReply;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\HelpdeskEmailService;
use App\Services\TicketNotificationService;
use App\Traits\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpdeskController extends Controller
{
    use ProjectAccess;

    /**
     * Calculate average response time for tickets
     */
    private function calculateAvgResponseTime($projectIds = null)
    {
        $query = Ticket::whereNotNull('resolved_at');
        
        if ($projectIds !== null) {
            $query->whereHas('asset', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        $tickets = $query->get();
        
        if ($tickets->isEmpty()) {
            return '0h';
        }

        $totalHours = 0;
        foreach ($tickets as $ticket) {
            $totalHours += $ticket->created_at->diffInHours($ticket->resolved_at);
        }

        $avgHours = $totalHours / $tickets->count();
        
        if ($avgHours < 24) {
            return round($avgHours) . 'h';
        } else {
            return round($avgHours / 24, 1) . 'd';
        }
    }

    /**
     * Calculate average resolution time for tickets (from first reply to resolved)
     */
    private function calculateAvgResolutionTime($projectIds = null)
    {
        $query = Ticket::whereNotNull('resolved_at')->whereHas('replies');
        
        if ($projectIds !== null) {
            $query->whereHas('asset', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        $tickets = $query->with(['replies' => function($q) {
            $q->orderBy('created_at', 'asc')->limit(1);
        }])->get();
        
        if ($tickets->isEmpty()) {
            return '0h';
        }

        $totalHours = 0;
        $count = 0;
        foreach ($tickets as $ticket) {
            $firstReply = $ticket->replies->first();
            if ($firstReply) {
                $totalHours += $firstReply->created_at->diffInHours($ticket->resolved_at);
                $count++;
            }
        }

        if ($count === 0) {
            return '0h';
        }

        $avgHours = $totalHours / $count;
        
        if ($avgHours < 24) {
            return round($avgHours) . 'h';
        } else {
            return round($avgHours / 24, 1) . 'd';
        }
    }

    /**
     * Check if user has helpdesk permission (staff)
     * Note: isStaff() is inherited from ProjectAccess trait
     */
    private function hasHelpdeskPermission()
    {
        $user = Auth::user();
        if (!$user->role) return false;
        
        // Check if user is Administrator (has all permissions)
        if ($user->role->name === 'Administrator') {
            return true;
        }
        
        $permissions = $user->role->permissions ?? [];
        
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (str_starts_with($permission, 'helpdesk_') && str_ends_with($permission, '.view')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if user can see all tickets (only Administrator)
     */
    private function canSeeAllTickets()
    {
        $user = Auth::user();
        if (!$user->role) return false;
        
        return $user->role->name === 'Administrator';
    }

    /**
     * Check if user can assign tickets
     */
    private function canAssign()
    {
        $user = Auth::user();
        if (!$user->role) return false;
        
        if ($user->role->name === 'Administrator') {
            return true;
        }
        
        $permissions = $user->role->permissions ?? [];
        
        if (is_array($permissions)) {
            if (in_array('helpdesk_tickets.assign', $permissions)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get employees who can be assigned tickets
     */
    private function getAssignableEmployees()
    {
        return Employee::whereNotNull('user_id')
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->where('status', 'active')
            ->with(['user.role'])
            ->orderBy('full_name')
            ->get();
    }

    /**
     * Get ticket IDs that the current user can access
     * Based on projects they have access to (via assets)
     */
    private function getAccessibleTicketQuery()
    {
        $user = Auth::user();
        $projectIds = $this->getAccessibleProjectIds();
        
        $query = Ticket::query();
        
        // If staff/admin, they see based on their role
        if ($this->isStaff()) {
            if ($this->canSeeAllTickets()) {
                // Administrator sees all
                return $query;
            }
            // Staff sees tickets they created or assigned to them
            return $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhereHas('assignees', function($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }
        
        // Client user - see tickets for assets in their accessible projects
        if ($projectIds !== null) {
            return $query->where(function($q) use ($projectIds, $user) {
                // Tickets for assets in accessible projects
                $q->whereHas('asset', function($q2) use ($projectIds) {
                    $q2->whereIn('project_id', $projectIds);
                })
                // Or tickets they created themselves
                ->orWhere('created_by', $user->id);
            });
        }
        
        // No access
        return $query->whereRaw('1 = 0');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $perPage = \App\Models\SystemSetting::paginationSize();

        $isStaff = $this->isStaff();
        $isClientUser = !$isStaff;
        $canAssign = $this->canAssign();
        $projectIds = $this->getAccessibleProjectIds();

        // If not client user and not staff with permission, deny access
        if ($isClientUser && ($projectIds === null || empty($projectIds))) {
            // Client user with no project access
            if (!$this->hasHelpdeskPermission()) {
                abort(403, 'You do not have permission to access helpdesk.');
            }
        }

        if (!$isClientUser && !$this->hasHelpdeskPermission()) {
            abort(403, 'You do not have permission to access helpdesk.');
        }

        // Define tabs with their permissions in order
        // Client users can ALWAYS access tickets and reports tabs (data is isolated by project access)
        // Staff users can access all tabs (if permitted)
        $tabPermissions = $isClientUser ? [
            'tickets' => null, // Client users always have access to their tickets
            'reports' => null, // Client users always have access to their reports (isolated data)
        ] : [
            'tickets' => 'helpdesk_tickets.view',
            'templates' => 'helpdesk_templates.view',
            'priorities' => 'helpdesk_priorities.view',
            'categories' => 'helpdesk_categories.view',
            'statuses' => 'helpdesk_statuses.view',
            'reports' => 'helpdesk_reports.view',
        ];

        // Get requested tab or find first accessible tab
        $requestedTab = $request->get('tab');
        $activeTab = null;

        if ($requestedTab && array_key_exists($requestedTab, $tabPermissions)) {
            // Check if user has permission for requested tab
            // For client users, null permission means always allowed (data is isolated)
            $permission = $tabPermissions[$requestedTab];
            if ($permission === null || $user->hasPermission($permission)) {
                $activeTab = $requestedTab;
            }
        }

        // If no valid tab yet, find first accessible tab
        if (!$activeTab) {
            foreach ($tabPermissions as $tab => $permission) {
                // For client users, null permission means always allowed
                if ($permission === null || $user->hasPermission($permission)) {
                    $activeTab = $tab;
                    break;
                }
            }
        }

        // Default to tickets if no permission found (for backwards compatibility)
        if (!$activeTab) {
            $activeTab = 'tickets';
        }

        // If requested tab differs from active tab (no permission), redirect
        if ($requestedTab && $requestedTab !== $activeTab) {
            return redirect()->route('helpdesk.index', ['tab' => $activeTab]);
        }

        $query = $this->getAccessibleTicketQuery()
            ->with(['client', 'creator', 'assignee', 'assignees.employee', 'asset.project']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('asset', function($q) use ($search) {
                      $q->where('serial_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('asset.project', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        // Get organizations for filter (staff only)
        $organizations = $canAssign ? Organization::active()->orderBy('name')->get() : collect();
        
        // Get assignable employees
        $assignableEmployees = $canAssign ? $this->getAssignableEmployees() : collect();

        // Stats - based on user's visible tickets
        $statsQuery = $this->getAccessibleTicketQuery();
        $stats = [
            'open' => (clone $statsQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'resolved')->count(),
        ];

        // Load ticket categories for the categories tab
        $ticketCategories = collect();
        if ($activeTab === 'categories' && $isStaff) {
            $categoryQuery = TicketCategory::withCount('tickets');
            
            if ($search) {
                $categoryQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->get('status')) {
                $categoryQuery->where('is_active', $request->get('status') === 'active');
            }
            
            $ticketCategories = $categoryQuery->ordered()->paginate($perPage)->withQueryString();
        }

        // Load ticket priorities for the priorities tab
        $ticketPriorities = collect();
        if ($activeTab === 'priorities' && $isStaff) {
            $priorityQuery = TicketPriority::withCount('tickets');
            
            if ($search) {
                $priorityQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->get('status')) {
                $priorityQuery->where('is_active', $request->get('status') === 'active');
            }
            
            $ticketPriorities = $priorityQuery->ordered()->paginate($perPage)->withQueryString();
        }

        // Load ticket statuses for the statuses tab
        $ticketStatuses = collect();
        if ($activeTab === 'statuses' && $isStaff) {
            $statusQuery = TicketStatus::withCount('tickets');
            
            if ($search) {
                $statusQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->get('status')) {
                $statusQuery->where('is_active', $request->get('status') === 'active');
            }
            
            $ticketStatuses = $statusQuery->ordered()->paginate($perPage)->withQueryString();
        }

        // Get active categories and priorities for the create ticket form
        $activeCategories = TicketCategory::active()->ordered()->get();
        $activePriorities = TicketPriority::active()->ordered()->get();
        $activeStatuses = TicketStatus::active()->ordered()->get();

        // Load email templates for the templates tab
        $emailTemplates = collect();
        if ($activeTab === 'templates' && $isStaff) {
            $templateQuery = TicketEmailTemplate::with('updatedByUser');
            
            if ($search) {
                $templateQuery->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->get('recipient_type')) {
                $templateQuery->where('recipient_type', $request->get('recipient_type'));
            }
            
            $emailTemplates = $templateQuery->orderBy('recipient_type')->orderBy('title')->paginate($perPage)->withQueryString();
        }

        // Load reports data for the reports tab
        $reportStats = [];
        $ticketsByStatus = collect();
        $ticketsByPriority = collect();
        $ticketsByCategory = collect();
        $topClientsByTickets = collect();
        $monthlyTrend = collect();
        $weeklyResolved = collect();
        $replyComparison = collect();
        $responseTimeDistribution = collect();
        $recentTickets = collect();
        $isClient = $isClientUser;

        if ($activeTab === 'reports') {
            // Build base query with access filter
            $baseQuery = $this->getAccessibleTicketQuery();

            // Get reply counts
            $totalReplies = TicketReply::query();
            $staffReplies = TicketReply::whereHas('user', function($q) {
                $q->whereHas('employee');
            });
            $clientReplies = TicketReply::whereHas('user', function($q) {
                $q->whereDoesntHave('employee');
            });

            if ($projectIds !== null) {
                $ticketIds = (clone $baseQuery)->pluck('id')->toArray();
                $totalReplies->whereIn('ticket_id', $ticketIds);
                $staffReplies->whereIn('ticket_id', $ticketIds);
                $clientReplies->whereIn('ticket_id', $ticketIds);
            }

            // Report Stats
            $reportStats = [
                'total_tickets' => (clone $baseQuery)->count(),
                'open_tickets' => (clone $baseQuery)->where('status', 'open')->count(),
                'in_progress_tickets' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'pending_tickets' => (clone $baseQuery)->where('status', 'pending')->count(),
                'resolved_tickets' => (clone $baseQuery)->where('status', 'resolved')->count(),
                'closed_tickets' => (clone $baseQuery)->where('status', 'closed')->count(),
                'avg_response_time' => $this->calculateAvgResponseTime($projectIds),
                'avg_resolution_time' => $this->calculateAvgResolutionTime($projectIds),
                'total_replies' => $totalReplies->count(),
                'staff_replies' => $staffReplies->count(),
                'client_replies' => $clientReplies->count(),
                'total_organizations' => $isStaff ? Organization::count() : 
                    ($projectIds ? Project::whereIn('id', $projectIds)->distinct('organization_id')->count('organization_id') : 0),
            ];

            // Tickets by Status
            $ticketsByStatus = TicketStatus::withCount(['tickets' => function($q) use ($projectIds) {
                if ($projectIds !== null) {
                    $q->whereHas('asset', function($q2) use ($projectIds) {
                        $q2->whereIn('project_id', $projectIds);
                    });
                }
            }])->active()->ordered()->get()->map(function($status) {
                return [
                    'name' => $status->name,
                    'count' => $status->tickets_count,
                    'color' => $status->color,
                ];
            });

            // Tickets by Priority
            $ticketsByPriority = TicketPriority::withCount(['tickets' => function($q) use ($projectIds) {
                if ($projectIds !== null) {
                    $q->whereHas('asset', function($q2) use ($projectIds) {
                        $q2->whereIn('project_id', $projectIds);
                    });
                }
            }])->active()->ordered()->get()->map(function($priority) {
                return [
                    'name' => $priority->name,
                    'count' => $priority->tickets_count,
                    'color' => $priority->color,
                ];
            });

            // Tickets by Category
            $ticketsByCategory = TicketCategory::withCount(['tickets' => function($q) use ($projectIds) {
                if ($projectIds !== null) {
                    $q->whereHas('asset', function($q2) use ($projectIds) {
                        $q2->whereIn('project_id', $projectIds);
                    });
                }
            }])->active()->ordered()->get()->map(function($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->tickets_count,
                ];
            })->filter(fn($cat) => $cat['count'] > 0)->values();

            // Top Organizations by Tickets (staff only)
            if ($isStaff) {
                $topClientsByTickets = Organization::withCount(['tickets'])
                    ->orderByDesc('tickets_count')
                    ->limit(5)
                    ->get()
                    ->map(function($org) {
                        return [
                            'name' => $org->name,
                            'count' => $org->tickets_count,
                        ];
                    })->filter(fn($o) => $o['count'] > 0)->values();
            }

            // Monthly Trend (last 6 months)
            $monthlyTrend = collect();
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $createdQuery = (clone $baseQuery)->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
                $resolvedQuery = (clone $baseQuery)->whereYear('resolved_at', $date->year)
                    ->whereMonth('resolved_at', $date->month);

                $monthlyTrend->push([
                    'month' => $date->format('M'),
                    'created' => $createdQuery->count(),
                    'resolved' => $resolvedQuery->count(),
                ]);
            }

            // Weekly Resolved (last 4 weeks)
            $weeklyResolved = collect();
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = now()->subWeeks($i)->startOfWeek();
                $endOfWeek = now()->subWeeks($i)->endOfWeek();
                $weekQuery = (clone $baseQuery)->whereBetween('resolved_at', [$startOfWeek, $endOfWeek]);

                $weeklyResolved->push([
                    'week' => 'Week ' . (4 - $i),
                    'count' => $weekQuery->count(),
                ]);
            }

            // Reply Comparison (Staff vs Client) - staff only
            if ($isStaff) {
                $replyComparison = collect();
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $staffCount = TicketReply::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->whereHas('user', fn($q) => $q->whereHas('employee'))
                        ->count();
                    $clientCount = TicketReply::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->whereHas('user', fn($q) => $q->whereDoesntHave('employee'))
                        ->count();

                    $replyComparison->push([
                        'month' => $date->format('M'),
                        'staff' => $staffCount,
                        'client' => $clientCount,
                    ]);
                }

                // Response Time Distribution
                $responseTimeCounts = [
                    '< 1 hour' => 0,
                    '1-4 hours' => 0,
                    '4-24 hours' => 0,
                    '1-3 days' => 0,
                    '> 3 days' => 0,
                ];

                $ticketsWithFirstReply = Ticket::whereHas('replies')->with(['replies' => function($q) {
                    $q->orderBy('created_at', 'asc')->limit(1);
                }])->get();

                foreach ($ticketsWithFirstReply as $t) {
                    $firstReply = $t->replies->first();
                    if ($firstReply) {
                        $hours = $t->created_at->diffInHours($firstReply->created_at);
                        if ($hours < 1) {
                            $responseTimeCounts['< 1 hour']++;
                        } elseif ($hours < 4) {
                            $responseTimeCounts['1-4 hours']++;
                        } elseif ($hours < 24) {
                            $responseTimeCounts['4-24 hours']++;
                        } elseif ($hours < 72) {
                            $responseTimeCounts['1-3 days']++;
                        } else {
                            $responseTimeCounts['> 3 days']++;
                        }
                    }
                }

                $responseTimeDistribution = collect([
                    ['range' => '< 1 hour', 'count' => $responseTimeCounts['< 1 hour']],
                    ['range' => '1-4 hours', 'count' => $responseTimeCounts['1-4 hours']],
                    ['range' => '4-24 hours', 'count' => $responseTimeCounts['4-24 hours']],
                    ['range' => '1-3 days', 'count' => $responseTimeCounts['1-3 days']],
                    ['range' => '> 3 days', 'count' => $responseTimeCounts['> 3 days']],
                ]);
            }

            // Recent Tickets
            $recentTickets = (clone $baseQuery)
                ->with(['asset.project.organization', 'ticketPriority', 'ticketStatus'])
                ->withCount('replies')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        // For backward compatibility with views
        $client = $this->getClientForUser();
        $clients = collect(); // Legacy - not used

        return view('helpdesk.index', compact(
            'tickets', 'clients', 'organizations', 'assignableEmployees', 'stats', 'client', 
            'isStaff', 'canAssign', 'activeTab', 'ticketCategories', 'ticketPriorities', 
            'ticketStatuses', 'activeCategories', 'activePriorities', 'activeStatuses', 
            'emailTemplates', 'reportStats', 'ticketsByStatus', 'ticketsByPriority', 
            'ticketsByCategory', 'topClientsByTickets', 'monthlyTrend', 'weeklyResolved', 
            'replyComparison', 'responseTimeDistribution', 'recentTickets', 'isClient'
        ));
    }

    /**
     * Verify serial number belongs to user's accessible projects
     */
    public function verifySerialNumber(Request $request)
    {
        $serialNumber = $request->input('serial_number');
        $projectIds = $this->getAccessibleProjectIds();

        if ($projectIds === null) {
            // Staff can verify any serial
            $asset = Asset::where('serial_number', $serialNumber)
                ->with(['project.organization', 'category', 'brand'])
                ->first();
        } else {
            // Client can only verify serial from their accessible projects
            $asset = Asset::where('serial_number', $serialNumber)
                ->whereIn('project_id', $projectIds)
                ->with(['project.organization', 'category', 'brand'])
                ->first();
        }

        if (!$asset) {
            return response()->json([
                'valid' => false, 
                'message' => 'Serial number not found or does not belong to your projects'
            ]);
        }

        return response()->json([
            'valid' => true,
            'asset' => [
                'id' => $asset->id,
                'serial_number' => $asset->serial_number,
                'asset_tag' => $asset->asset_tag,
                'model' => $asset->model,
                'category' => $asset->category->name ?? '-',
                'brand' => $asset->brand->name ?? '-',
                'project' => $asset->project->name ?? '-',
                'organization' => $asset->project->organization->name ?? '-',
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isStaff = $this->isStaff();
        $canAssign = $this->canAssign();
        $projectIds = $this->getAccessibleProjectIds();

        // Get default status (Open)
        $defaultStatus = TicketStatus::where('is_default', true)->first();
        $statusId = $defaultStatus ? $defaultStatus->id : null;

        if (!$isStaff) {
            // Client user must provide asset_id (verified serial number)
            $validated = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            // Verify asset belongs to user's accessible projects
            $asset = Asset::where('id', $validated['asset_id'])
                ->whereIn('project_id', $projectIds ?? [])
                ->with('project.organization')
                ->first();

            if (!$asset) {
                return back()->with('error', 'Invalid asset. Please verify serial number again.');
            }

            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'organization_id' => $asset->project->organization_id ?? null,
                'asset_id' => $asset->id,
                'created_by' => $user->id,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => strtolower($priority->name ?? 'medium'),
                'priority_id' => $validated['priority_id'],
                'category' => $category->name ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'status' => 'open',
                'status_id' => $statusId,
            ];
        } elseif ($canAssign) {
            // Staff with assign can create ticket
            $validated = $request->validate([
                'organization_id' => 'nullable|exists:organizations,id',
                'asset_id' => 'nullable|exists:assets,id',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            // If asset provided, get organization from asset's project
            $organizationId = $validated['organization_id'] ?? null;
            if ($validated['asset_id']) {
                $asset = Asset::with('project')->find($validated['asset_id']);
                if ($asset && $asset->project) {
                    $organizationId = $asset->project->organization_id;
                }
            }

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'organization_id' => $organizationId,
                'asset_id' => $validated['asset_id'] ?? null,
                'created_by' => $user->id,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => strtolower($priority->name ?? 'medium'),
                'priority_id' => $validated['priority_id'],
                'category' => $category->name ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'status' => 'open',
                'status_id' => $statusId,
            ];
        } else {
            // Staff without assign: create internal ticket
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'organization_id' => null,
                'asset_id' => null,
                'created_by' => $user->id,
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'priority' => strtolower($priority->name ?? 'medium'),
                'priority_id' => $validated['priority_id'],
                'category' => $category->name ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'status' => 'open',
                'status_id' => $statusId,
            ];
        }

        $ticket = Ticket::create($ticketData);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('ticket-attachments/' . $ticket->id, $filename, 'local');
                
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        try {
            ActivityLogService::logCreate($ticket, 'helpdesk', "Created ticket: #{$ticket->ticket_number} - {$ticket->subject}");
        } catch (\Exception $e) {
            // Silent fail
        }

        // Dispatch email notification job
        $emailService = new HelpdeskEmailService();
        $notificationService = new TicketNotificationService($emailService);
        $creatorType = $notificationService->determineCreatorType($user);
        
        SendTicketNotificationJob::dispatch($ticket->id, $user->id, $creatorType);

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket created successfully. Ticket #' . $ticket->ticket_number);
    }

    public function show(Ticket $ticket)
    {
        $user = Auth::user();
        $isStaff = $this->isStaff();
        $canAssign = $this->canAssign();
        $projectIds = $this->getAccessibleProjectIds();

        // Check access
        if (!$isStaff) {
            // Client user - check if ticket's asset is in their accessible projects
            if ($ticket->asset_id) {
                $asset = Asset::find($ticket->asset_id);
                if (!$asset || ($projectIds !== null && !in_array($asset->project_id, $projectIds))) {
                    // Also allow if they created the ticket
                    if ($ticket->created_by !== $user->id) {
                        abort(403);
                    }
                }
            } elseif ($ticket->created_by !== $user->id) {
                abort(403);
            }
        } elseif (!$this->canSeeAllTickets()) {
            // Staff (non-Administrator) can only see their own tickets or assigned
            $isAssigned = $ticket->assignees()->where('user_id', $user->id)->exists();
            if ($ticket->created_by !== $user->id && $ticket->assigned_to !== $user->id && !$isAssigned) {
                abort(403, 'You can only view tickets you created or assigned to you.');
            }
        }

        $ticket->load([
            'creator', 'assignee', 'assignees.employee', 
            'asset.project.organization', 'asset.category', 'asset.brand', 
            'replies.user', 'replies.attachments', 'attachments', 
            'ticketCategory', 'ticketPriority', 'ticketStatus', 'organization'
        ]);
        
        try {
            ActivityLogService::logView($ticket, 'helpdesk', "Viewed ticket: #{$ticket->ticket_number}");
        } catch (\Exception $e) {
            // Silent fail
        }

        $assignableEmployees = $canAssign ? $this->getAssignableEmployees() : collect();
        $activeStatuses = TicketStatus::active()->ordered()->get();
        $activeCategories = TicketCategory::active()->ordered()->get();
        $activePriorities = TicketPriority::active()->ordered()->get();

        // For backward compatibility
        $client = $this->getClientForUser();

        return view('helpdesk.show', compact(
            'ticket', 'assignableEmployees', 'client', 'isStaff', 'canAssign', 
            'activeStatuses', 'activeCategories', 'activePriorities'
        ));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $isStaff = $this->isStaff();
        $projectIds = $this->getAccessibleProjectIds();

        // Check access (same as show)
        if (!$isStaff) {
            if ($ticket->asset_id) {
                $asset = Asset::find($ticket->asset_id);
                if (!$asset || ($projectIds !== null && !in_array($asset->project_id, $projectIds))) {
                    if ($ticket->created_by !== $user->id) {
                        abort(403);
                    }
                }
            } elseif ($ticket->created_by !== $user->id) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $isInternalNote = $isStaff ? ($validated['is_internal_note'] ?? false) : false;

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal_note' => $isInternalNote,
        ]);

        try {
            ActivityLogService::logReply($ticket, "Added reply to ticket: #{$ticket->ticket_number}");
        } catch (\Exception $e) {
            // Silent fail
        }

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('ticket-attachments/' . $ticket->id, $filename, 'local');
                
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'reply_id' => $reply->id,
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Send reply notification
        $emailService = new HelpdeskEmailService();
        $notificationService = new TicketNotificationService($emailService);
        $notificationService->sendReplyNotifications($ticket, Auth::user(), $isInternalNote);

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Reply added successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:ticket_statuses,id',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = TicketStatus::find($validated['status_id']);
        $statusSlug = strtolower(str_replace(' ', '_', $newStatus->name));

        $ticket->update([
            'status_id' => $validated['status_id'],
            'status' => $statusSlug,
            'resolved_at' => $newStatus->name === 'Resolved' ? now() : $ticket->resolved_at,
            'closed_at' => $newStatus->is_closed ? now() : $ticket->closed_at,
        ]);

        // Log status update
        try {
            ActivityLogService::logUpdate($ticket, 'helpdesk', ['status' => $oldStatus], "Updated ticket #{$ticket->ticket_number} status to: {$newStatus->name}");
        } catch (\Exception $e) {
            // Silent fail
        }

        if ($oldStatus !== $statusSlug) {
            $emailService = new HelpdeskEmailService();
            $notificationService = new TicketNotificationService($emailService);
            $notificationService->sendStatusChangeNotifications($ticket, $oldStatus);
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket status updated.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to assign tickets.');
        }

        $validated = $request->validate([
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $currentAssigneeIds = $ticket->assignees()->pluck('user_id')->toArray();
        $assigneeIds = $validated['assignee_ids'] ?? [];
        $ticket->assignees()->sync($assigneeIds);
        $ticket->update(['assigned_to' => null]);

        if ($ticket->status === 'open' && count($assigneeIds) > 0) {
            $ticket->update(['status' => 'in_progress']);
        }

        $newAssigneeIds = array_diff($assigneeIds, $currentAssigneeIds);
        if (!empty($newAssigneeIds)) {
            $emailService = new HelpdeskEmailService();
            $notificationService = new TicketNotificationService($emailService);
            $notificationService->sendAssignmentNotifications($ticket, $newAssigneeIds);
        }

        // Log assign activity
        try {
            $assigneeNames = User::whereIn('id', $assigneeIds)->pluck('name')->toArray();
            $assigneeList = !empty($assigneeNames) ? implode(', ', $assigneeNames) : 'Unassigned';
            ActivityLogService::logAssign($ticket, 'helpdesk', "Assigned ticket #{$ticket->ticket_number} to: {$assigneeList}", [
                'assignee_ids' => $assigneeIds,
                'assignee_names' => $assigneeNames,
            ]);
        } catch (\Exception $e) {
            // Silent fail
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }

    public function downloadAttachment(TicketAttachment $attachment)
    {
        $user = Auth::user();
        $isStaff = $this->isStaff();
        $projectIds = $this->getAccessibleProjectIds();
        $ticket = $attachment->ticket;

        // Check access
        if (!$isStaff) {
            if ($ticket->asset_id) {
                $asset = Asset::find($ticket->asset_id);
                if (!$asset || ($projectIds !== null && !in_array($asset->project_id, $projectIds))) {
                    if ($ticket->created_by !== $user->id) {
                        abort(403);
                    }
                }
            } elseif ($ticket->created_by !== $user->id) {
                abort(403);
            }
        }

        $path = 'ticket-attachments/' . $attachment->ticket_id . '/' . $attachment->filename;
        
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, $attachment->original_filename);
    }

    public function destroy(Ticket $ticket)
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $ticketNumber = $ticket->ticket_number;
        $ticketSubject = $ticket->subject;

        try {
            ActivityLogService::logDelete($ticket, 'helpdesk', "Deleted ticket: #{$ticketNumber} - {$ticketSubject}");
        } catch (\Exception $e) {
            // Silent fail
        }

        Storage::disk('local')->deleteDirectory('ticket-attachments/' . $ticket->id);
        $ticket->delete();

        return redirect()->route('helpdesk.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    // ==================== CATEGORY MANAGEMENT ====================

    public function storeCategory(Request $request)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        $maxOrder = TicketCategory::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        $category = TicketCategory::create($validated);

        try {
            ActivityLogService::logCreate($category, 'helpdesk', "Created ticket category: {$category->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'categories'])
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, TicketCategory $category)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage categories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
        ]);

        $oldValues = $category->only(['name', 'description', 'color', 'icon']);
        $category->update($validated);

        try {
            ActivityLogService::logUpdate($category, 'helpdesk', $oldValues, "Updated ticket category: {$category->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'categories'])
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(TicketCategory $category)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage categories.');
        }

        if ($category->tickets()->count() > 0) {
            return redirect()->route('helpdesk.index', ['tab' => 'categories'])
                ->with('error', 'Cannot delete category with existing tickets.');
        }

        $categoryName = $category->name;

        try {
            ActivityLogService::logDelete($category, 'helpdesk', "Deleted ticket category: {$categoryName}");
        } catch (\Exception $e) {}

        $category->delete();

        return redirect()->route('helpdesk.index', ['tab' => 'categories'])
            ->with('success', 'Category deleted successfully.');
    }

    public function toggleCategoryStatus(TicketCategory $category)
    {
        if (!$this->canAssign()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $category->update(['is_active' => !$category->is_active]);
        return response()->json(['success' => true]);
    }

    // ==================== PRIORITY MANAGEMENT ====================

    public function storePriority(Request $request)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage priorities.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'level' => 'required|integer|min:1|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        $maxOrder = TicketPriority::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            TicketPriority::where('is_default', true)->update(['is_default' => false]);
        }

        $priority = TicketPriority::create($validated);

        try {
            ActivityLogService::logCreate($priority, 'helpdesk', "Created ticket priority: {$priority->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'priorities'])
            ->with('success', 'Priority created successfully.');
    }

    public function updatePriority(Request $request, TicketPriority $priority)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage priorities.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'level' => 'required|integer|min:1|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        $oldValues = $priority->only(['name', 'description', 'color', 'icon', 'level', 'is_default']);
        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            TicketPriority::where('is_default', true)->where('id', '!=', $priority->id)->update(['is_default' => false]);
        }

        $priority->update($validated);

        try {
            ActivityLogService::logUpdate($priority, 'helpdesk', $oldValues, "Updated ticket priority: {$priority->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'priorities'])
            ->with('success', 'Priority updated successfully.');
    }

    public function destroyPriority(TicketPriority $priority)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage priorities.');
        }

        if ($priority->tickets()->count() > 0) {
            return redirect()->route('helpdesk.index', ['tab' => 'priorities'])
                ->with('error', 'Cannot delete priority with existing tickets.');
        }

        $priorityName = $priority->name;

        try {
            ActivityLogService::logDelete($priority, 'helpdesk', "Deleted ticket priority: {$priorityName}");
        } catch (\Exception $e) {}

        $priority->delete();

        return redirect()->route('helpdesk.index', ['tab' => 'priorities'])
            ->with('success', 'Priority deleted successfully.');
    }

    public function togglePriorityStatus(TicketPriority $priority)
    {
        if (!$this->canAssign()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $priority->update(['is_active' => !$priority->is_active]);
        return response()->json(['success' => true]);
    }

    public function setDefaultPriority(TicketPriority $priority)
    {
        if (!$this->canAssign()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        TicketPriority::where('is_default', true)->update(['is_default' => false]);
        $priority->update(['is_default' => true]);
        return response()->json(['success' => true]);
    }

    // ==================== STATUS MANAGEMENT ====================

    public function storeStatus(Request $request)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage statuses.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'is_default' => 'nullable|boolean',
            'is_closed' => 'nullable|boolean',
        ]);

        $maxOrder = TicketStatus::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['is_closed'] = $validated['is_closed'] ?? false;

        if ($validated['is_default']) {
            TicketStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $status = TicketStatus::create($validated);

        try {
            ActivityLogService::logCreate($status, 'helpdesk', "Created ticket status: {$status->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'statuses'])
            ->with('success', 'Status created successfully.');
    }

    public function updateTicketStatus(Request $request, TicketStatus $status)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage statuses.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'is_default' => 'nullable|boolean',
            'is_closed' => 'nullable|boolean',
        ]);

        $oldValues = $status->only(['name', 'description', 'color', 'icon', 'is_default', 'is_closed']);
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['is_closed'] = $validated['is_closed'] ?? false;

        if ($validated['is_default']) {
            TicketStatus::where('is_default', true)->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($validated);

        try {
            ActivityLogService::logUpdate($status, 'helpdesk', $oldValues, "Updated ticket status: {$status->name}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'statuses'])
            ->with('success', 'Status updated successfully.');
    }

    public function destroyStatus(TicketStatus $status)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage statuses.');
        }

        if ($status->tickets()->count() > 0) {
            return redirect()->route('helpdesk.index', ['tab' => 'statuses'])
                ->with('error', 'Cannot delete status with existing tickets.');
        }

        $statusName = $status->name;

        try {
            ActivityLogService::logDelete($status, 'helpdesk', "Deleted ticket status: {$statusName}");
        } catch (\Exception $e) {}

        $status->delete();

        return redirect()->route('helpdesk.index', ['tab' => 'statuses'])
            ->with('success', 'Status deleted successfully.');
    }

    public function toggleStatusActive(TicketStatus $status)
    {
        if (!$this->canAssign()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $status->update(['is_active' => !$status->is_active]);
        return response()->json(['success' => true]);
    }

    public function setDefaultStatus(TicketStatus $status)
    {
        if (!$this->canAssign()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        TicketStatus::where('is_default', true)->update(['is_default' => false]);
        $status->update(['is_default' => true]);
        return response()->json(['success' => true]);
    }

    // ==================== EMAIL TEMPLATE MANAGEMENT ====================

    public function editTemplate(TicketEmailTemplate $emailTemplate)
    {
        if (!$this->isStaff()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $emailTemplate->id,
                'slug' => $emailTemplate->slug,
                'title' => $emailTemplate->title,
                'description' => $emailTemplate->description,
                'recipient_type' => $emailTemplate->recipient_type,
                'subject' => $emailTemplate->subject,
                'content' => $emailTemplate->content,
            ]
        ]);
    }

    public function updateTemplate(Request $request, TicketEmailTemplate $emailTemplate)
    {
        if (!$this->isStaff()) {
            abort(403, 'You do not have permission to manage email templates.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $oldValues = $emailTemplate->only(['subject', 'content']);

        $emailTemplate->update([
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'updated_by' => Auth::id(),
        ]);

        try {
            ActivityLogService::logUpdate($emailTemplate, 'helpdesk', $oldValues, "Updated email template: {$emailTemplate->title}");
        } catch (\Exception $e) {}

        return redirect()->route('helpdesk.index', ['tab' => 'templates'])
            ->with('success', 'Email template updated successfully.');
    }
}
