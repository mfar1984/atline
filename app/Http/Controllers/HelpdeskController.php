<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketNotificationJob;
use App\Models\Asset;
use App\Models\Client;
use App\Models\Employee;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HelpdeskController extends Controller
{
    /**
     * Check if current user is a client
     */
    private function getClientForUser()
    {
        return Client::where('user_id', Auth::id())->first();
    }

    /**
     * Calculate average response time for tickets
     */
    private function calculateAvgResponseTime($client = null)
    {
        $query = Ticket::whereNotNull('resolved_at');
        
        if ($client) {
            $query->where('client_id', $client->id);
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
    private function calculateAvgResolutionTime($client = null)
    {
        $query = Ticket::whereNotNull('resolved_at')->whereHas('replies');
        
        if ($client) {
            $query->where('client_id', $client->id);
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
     */
    private function isStaff()
    {
        $user = Auth::user();
        if (!$user->role) return false;
        
        // Check if user is Administrator (has all permissions)
        if ($user->role->name === 'Administrator') {
            return true;
        }
        
        $permissions = $user->role->permissions ?? [];
        
        if (is_array($permissions)) {
            // Check for any helpdesk sub-module view permission
            // Actual permissions are: helpdesk_tickets.view, helpdesk_templates.view, etc.
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
        
        // Only Administrator can see all tickets
        return $user->role->name === 'Administrator';
    }

    /**
     * Check if user can assign tickets (Administrator or has helpdesk_tickets.assign permission)
     */
    private function canAssign()
    {
        $user = Auth::user();
        if (!$user->role) return false;
        
        // Administrator can always assign
        if ($user->role->name === 'Administrator') {
            return true;
        }
        
        $permissions = $user->role->permissions ?? [];
        
        if (is_array($permissions)) {
            // Check for helpdesk_tickets.assign permission
            if (in_array('helpdesk_tickets.assign', $permissions)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get employees who can be assigned tickets
     * All active employees with user accounts can be assigned
     */
    private function getAssignableEmployees()
    {
        // Get all active employees who have user accounts
        return Employee::whereNotNull('user_id')
            ->whereHas('user', function($q) {
                $q->where('is_active', true);
            })
            ->where('status', 'active')
            ->with(['user.role'])
            ->orderBy('full_name')
            ->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $perPage = \App\Models\SystemSetting::paginationSize();
        $activeTab = $request->get('tab', 'tickets');

        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        $canAssign = $this->canAssign();

        // If not client and not staff with permission, deny access
        if (!$client && !$isStaff) {
            abort(403, 'You do not have permission to access helpdesk.');
        }

        $query = Ticket::with(['client', 'creator', 'assignee', 'assignees.employee', 'asset']);
        $canSeeAllTickets = $this->canSeeAllTickets();

        // Client can only see their own tickets
        if ($client) {
            $query->where('client_id', $client->id);
        } elseif (!$canSeeAllTickets) {
            // Staff (non-Administrator): only see tickets they created or assigned to them
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhereHas('assignees', function($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }
        // Administrator: see ALL tickets (no filter)

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('asset', function($q) use ($search) {
                      $q->where('serial_number', 'like', "%{$search}%");
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
        
        // For staff with assign permission, get all clients. Otherwise no need
        $clients = $canAssign ? Client::active()->orderBy('name')->get() : collect();
        
        // Get assignable employees (those with helpdesk.assign permission)
        $assignableEmployees = $canAssign ? $this->getAssignableEmployees() : collect();

        // Stats - based on user's visible tickets
        if ($client) {
            $stats = [
                'open' => Ticket::where('client_id', $client->id)->where('status', 'open')->count(),
                'in_progress' => Ticket::where('client_id', $client->id)->where('status', 'in_progress')->count(),
                'pending' => Ticket::where('client_id', $client->id)->where('status', 'pending')->count(),
                'resolved' => Ticket::where('client_id', $client->id)->where('status', 'resolved')->count(),
            ];
        } elseif (!$canSeeAllTickets) {
            // Staff (non-Administrator): stats for their tickets only
            $stats = [
                'open' => Ticket::where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('assignees', function($q2) use ($user) {
                          $q2->where('user_id', $user->id);
                      });
                })->where('status', 'open')->count(),
                'in_progress' => Ticket::where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('assignees', function($q2) use ($user) {
                          $q2->where('user_id', $user->id);
                      });
                })->where('status', 'in_progress')->count(),
                'pending' => Ticket::where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('assignees', function($q2) use ($user) {
                          $q2->where('user_id', $user->id);
                      });
                })->where('status', 'pending')->count(),
                'resolved' => Ticket::where(function($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('assigned_to', $user->id)
                      ->orWhereHas('assignees', function($q2) use ($user) {
                          $q2->where('user_id', $user->id);
                      });
                })->where('status', 'resolved')->count(),
            ];
        } else {
            // Administrator: all stats
            $stats = [
                'open' => Ticket::where('status', 'open')->count(),
                'in_progress' => Ticket::where('status', 'in_progress')->count(),
                'pending' => Ticket::where('status', 'pending')->count(),
                'resolved' => Ticket::where('status', 'resolved')->count(),
            ];
        }

        // Load ticket categories for the categories tab
        $ticketCategories = collect();
        if ($activeTab === 'categories' && !$client) {
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
        if ($activeTab === 'priorities' && !$client) {
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
        if ($activeTab === 'statuses' && !$client) {
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
        if ($activeTab === 'templates' && !$client) {
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
        $isClient = (bool) $client;

        if ($activeTab === 'reports') {
            // Build base query for client isolation
            $baseQuery = Ticket::query();
            if ($client) {
                $baseQuery->where('client_id', $client->id);
            }

            // Get reply counts
            $totalReplies = TicketReply::query();
            $staffReplies = TicketReply::whereHas('user', function($q) {
                $q->whereHas('employee');
            });
            $clientReplies = TicketReply::whereHas('user', function($q) {
                $q->whereHas('client');
            });

            if ($client) {
                $totalReplies->whereHas('ticket', fn($q) => $q->where('client_id', $client->id));
                $staffReplies->whereHas('ticket', fn($q) => $q->where('client_id', $client->id));
                $clientReplies->whereHas('ticket', fn($q) => $q->where('client_id', $client->id));
            }

            // Report Stats
            $reportStats = [
                'total_tickets' => (clone $baseQuery)->count(),
                'open_tickets' => (clone $baseQuery)->where('status', 'open')->count(),
                'in_progress_tickets' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'pending_tickets' => (clone $baseQuery)->where('status', 'pending')->count(),
                'resolved_tickets' => (clone $baseQuery)->where('status', 'resolved')->count(),
                'closed_tickets' => (clone $baseQuery)->where('status', 'closed')->count(),
                'avg_response_time' => $this->calculateAvgResponseTime($client),
                'avg_resolution_time' => $this->calculateAvgResolutionTime($client),
                'total_replies' => $totalReplies->count(),
                'staff_replies' => $staffReplies->count(),
                'client_replies' => $clientReplies->count(),
                'total_clients' => $client ? 1 : Client::count(),
            ];

            // Tickets by Status
            $ticketsByStatus = TicketStatus::withCount(['tickets' => function($q) use ($client) {
                if ($client) {
                    $q->where('client_id', $client->id);
                }
            }])->active()->ordered()->get()->map(function($status) {
                return [
                    'name' => $status->name,
                    'count' => $status->tickets_count,
                    'color' => $status->color,
                ];
            });

            // Tickets by Priority
            $ticketsByPriority = TicketPriority::withCount(['tickets' => function($q) use ($client) {
                if ($client) {
                    $q->where('client_id', $client->id);
                }
            }])->active()->ordered()->get()->map(function($priority) {
                return [
                    'name' => $priority->name,
                    'count' => $priority->tickets_count,
                    'color' => $priority->color,
                ];
            });

            // Tickets by Category
            $ticketsByCategory = TicketCategory::withCount(['tickets' => function($q) use ($client) {
                if ($client) {
                    $q->where('client_id', $client->id);
                }
            }])->active()->ordered()->get()->map(function($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->tickets_count,
                ];
            })->filter(fn($cat) => $cat['count'] > 0)->values();

            // Top Clients by Tickets (only for staff)
            if (!$client) {
                $topClientsByTickets = Client::withCount('tickets')
                    ->orderByDesc('tickets_count')
                    ->limit(5)
                    ->get()
                    ->map(function($c) {
                        return [
                            'name' => $c->name,
                            'count' => $c->tickets_count,
                        ];
                    })->filter(fn($c) => $c['count'] > 0)->values();
            }

            // Monthly Trend (last 6 months) - Created vs Resolved
            $monthlyTrend = collect();
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $createdQuery = Ticket::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
                $resolvedQuery = Ticket::whereYear('resolved_at', $date->year)
                    ->whereMonth('resolved_at', $date->month);
                
                if ($client) {
                    $createdQuery->where('client_id', $client->id);
                    $resolvedQuery->where('client_id', $client->id);
                }

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
                $weekQuery = Ticket::whereBetween('resolved_at', [$startOfWeek, $endOfWeek]);
                
                if ($client) {
                    $weekQuery->where('client_id', $client->id);
                }

                $weeklyResolved->push([
                    'week' => 'Week ' . (4 - $i),
                    'count' => $weekQuery->count(),
                ]);
            }

            // Reply Comparison (Staff vs Client) - last 6 months
            if (!$client) {
                $replyComparison = collect();
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $staffCount = TicketReply::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->whereHas('user', fn($q) => $q->whereHas('employee'))
                        ->count();
                    $clientCount = TicketReply::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->whereHas('user', fn($q) => $q->whereHas('client'))
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
            $recentQuery = Ticket::with(['client', 'ticketPriority', 'ticketStatus'])
                ->withCount('replies');
            if ($client) {
                $recentQuery->where('client_id', $client->id);
            }
            $recentTickets = $recentQuery->orderByDesc('created_at')->limit(10)->get();
        }

        return view('helpdesk.index', compact('tickets', 'clients', 'assignableEmployees', 'stats', 'client', 'isStaff', 'canAssign', 'activeTab', 'ticketCategories', 'ticketPriorities', 'ticketStatuses', 'activeCategories', 'activePriorities', 'activeStatuses', 'emailTemplates', 'reportStats', 'ticketsByStatus', 'ticketsByPriority', 'ticketsByCategory', 'topClientsByTickets', 'monthlyTrend', 'weeklyResolved', 'replyComparison', 'responseTimeDistribution', 'recentTickets', 'isClient'));
    }

    /**
     * Verify serial number belongs to client's project
     */
    public function verifySerialNumber(Request $request)
    {
        $serialNumber = $request->input('serial_number');
        $client = $this->getClientForUser();

        if (!$client) {
            return response()->json(['valid' => false, 'message' => 'Not a client user']);
        }

        // Find asset by serial number that belongs to client's projects
        $asset = Asset::where('serial_number', $serialNumber)
            ->whereHas('project', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->with(['project', 'category', 'brand'])
            ->first();

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
            ]
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        $canAssign = $this->canAssign();

        // Get default status (Open)
        $defaultStatus = TicketStatus::where('is_default', true)->first();
        $statusId = $defaultStatus ? $defaultStatus->id : null;

        // Validation rules differ for client vs staff
        if ($client) {
            // Client must provide asset_id (verified serial number)
            $validated = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            // Verify asset belongs to client's project
            $asset = Asset::where('id', $validated['asset_id'])
                ->whereHas('project', function($q) use ($client) {
                    $q->where('client_id', $client->id);
                })
                ->first();

            if (!$asset) {
                return back()->with('error', 'Invalid asset. Please verify serial number again.');
            }

            // Get priority and category names for legacy fields
            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'client_id' => $client->id,
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
            // Staff with assign can create ticket for any client
            $validated = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'asset_id' => 'nullable|exists:assets,id',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            // Get priority and category names for legacy fields
            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'client_id' => $validated['client_id'],
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
            // Staff without assign: create internal ticket (no client required)
            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'priority_id' => 'required|exists:ticket_priorities,id',
                'category_id' => 'nullable|exists:ticket_categories,id',
                'attachments.*' => 'nullable|file|max:10240',
            ]);

            // Get priority and category names for legacy fields
            $priority = TicketPriority::find($validated['priority_id']);
            $category = $validated['category_id'] ? TicketCategory::find($validated['category_id']) : null;

            $ticketData = [
                'ticket_number' => Ticket::generateTicketNumber(),
                'client_id' => null, // Internal ticket
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
            // Silent fail - don't interrupt main operation
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
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        $canAssign = $this->canAssign();
        $canSeeAllTickets = $this->canSeeAllTickets();

        // Check access
        if ($client && $ticket->client_id !== $client->id) {
            abort(403);
        }

        if (!$client && !$isStaff) {
            abort(403);
        }

        // Staff (non-Administrator) can only see their own tickets or tickets assigned to them
        if (!$client && !$canSeeAllTickets) {
            $isAssigned = $ticket->assignees()->where('user_id', $user->id)->exists();
            if ($ticket->created_by !== $user->id && $ticket->assigned_to !== $user->id && !$isAssigned) {
                abort(403, 'You can only view tickets you created or assigned to you.');
            }
        }

        $ticket->load(['client', 'creator', 'assignee', 'assignees.employee', 'asset.project', 'asset.category', 'asset.brand', 'replies.user', 'replies.attachments', 'attachments', 'ticketCategory', 'ticketPriority', 'ticketStatus']);
        
        try {
            ActivityLogService::logView($ticket, 'helpdesk', "Viewed ticket: #{$ticket->ticket_number}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        // Get assignable employees (those with helpdesk.assign permission)
        $assignableEmployees = $canAssign ? $this->getAssignableEmployees() : collect();
        
        // Get active statuses for the status dropdown
        $activeStatuses = TicketStatus::active()->ordered()->get();
        $activeCategories = TicketCategory::active()->ordered()->get();
        $activePriorities = TicketPriority::active()->ordered()->get();

        return view('helpdesk.show', compact('ticket', 'assignableEmployees', 'client', 'isStaff', 'canAssign', 'activeStatuses', 'activeCategories', 'activePriorities'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();

        // Check access
        if ($client && $ticket->client_id !== $client->id) {
            abort(403);
        }

        if (!$client && !$isStaff) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal_note' => 'nullable|boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $isInternalNote = $client ? false : ($validated['is_internal_note'] ?? false);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal_note' => $isInternalNote,
        ]);

        try {
            ActivityLogService::logReply($ticket, "Added reply to ticket: #{$ticket->ticket_number}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
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
        // Only staff can update status
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

        // Send status change notification
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
        // Only staff with assign permission can assign
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to assign tickets.');
        }

        $validated = $request->validate([
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        // Get current assignees before sync
        $currentAssigneeIds = $ticket->assignees()->pluck('user_id')->toArray();
        
        // Sync assignees (many-to-many)
        $assigneeIds = $validated['assignee_ids'] ?? [];
        $ticket->assignees()->sync($assigneeIds);

        // Clear legacy assigned_to field (we now use ticket_assignees table)
        $ticket->update(['assigned_to' => null]);

        // Update status if ticket is open and has assignees
        if ($ticket->status === 'open' && count($assigneeIds) > 0) {
            $ticket->update(['status' => 'in_progress']);
        }

        // Send notification to newly assigned users (not already assigned)
        $newAssigneeIds = array_diff($assigneeIds, $currentAssigneeIds);
        if (!empty($newAssigneeIds)) {
            $emailService = new HelpdeskEmailService();
            $notificationService = new TicketNotificationService($emailService);
            $notificationService->sendAssignmentNotifications($ticket, $newAssigneeIds);
        }

        return redirect()->route('helpdesk.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }

    public function downloadAttachment(TicketAttachment $attachment)
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();

        // Check access
        if ($client && $attachment->ticket->client_id !== $client->id) {
            abort(403);
        }

        if (!$client && !$isStaff) {
            abort(403);
        }

        $path = 'ticket-attachments/' . $attachment->ticket_id . '/' . $attachment->filename;
        
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, $attachment->original_filename);
    }

    public function destroy(Ticket $ticket)
    {
        // Only staff can delete
        if (!$this->isStaff()) {
            abort(403);
        }

        $ticketNumber = $ticket->ticket_number;
        $ticketSubject = $ticket->subject;

        try {
            ActivityLogService::logDelete($ticket, 'helpdesk', "Deleted ticket: #{$ticketNumber} - {$ticketSubject}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        // Delete attachments
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
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('helpdesk.index', ['tab' => 'categories'])
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(TicketCategory $category)
    {
        if (!$this->canAssign()) {
            abort(403, 'You do not have permission to manage categories.');
        }

        // Check if category has tickets
        if ($category->tickets()->count() > 0) {
            return redirect()->route('helpdesk.index', ['tab' => 'categories'])
                ->with('error', 'Cannot delete category with existing tickets.');
        }

        $categoryName = $category->name;

        try {
            ActivityLogService::logDelete($category, 'helpdesk', "Deleted ticket category: {$categoryName}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            TicketPriority::where('is_default', true)->update(['is_default' => false]);
        }

        $priority = TicketPriority::create($validated);

        try {
            ActivityLogService::logCreate($priority, 'helpdesk', "Created ticket priority: {$priority->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            TicketPriority::where('is_default', true)->where('id', '!=', $priority->id)->update(['is_default' => false]);
        }

        $priority->update($validated);

        try {
            ActivityLogService::logUpdate($priority, 'helpdesk', $oldValues, "Updated ticket priority: {$priority->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            TicketStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $status = TicketStatus::create($validated);

        try {
            ActivityLogService::logCreate($status, 'helpdesk', "Created ticket status: {$status->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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

        // If this is set as default, unset other defaults
        if ($validated['is_default']) {
            TicketStatus::where('is_default', true)->where('id', '!=', $status->id)->update(['is_default' => false]);
        }

        $status->update($validated);

        try {
            ActivityLogService::logUpdate($status, 'helpdesk', $oldValues, "Updated ticket status: {$status->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

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
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('helpdesk.index', ['tab' => 'templates'])
            ->with('success', 'Email template updated successfully.');
    }
}
