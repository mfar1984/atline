<!-- Reports Container -->
<div style="width: 100%; max-width: 100%; overflow: hidden; box-sizing: border-box;">
<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Helpdesk Reports & Analytics</h3>
        <p class="text-xs text-gray-500 mt-1">Comprehensive overview of support ticket performance</p>
    </div>
    @permission('helpdesk_reports.export')
    <button type="button" onclick="window.print()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">print</span>
        PRINT REPORT
    </button>
    @endpermission
</div>

<!-- Summary Stats Cards - Row 1: Ticket Status Counts -->
<div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 16px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Total Tickets</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['total_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">confirmation_number</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Open</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['open_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">pending</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">In Progress</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['in_progress_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">autorenew</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Pending</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['pending_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">hourglass_empty</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Resolved</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['resolved_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">check_circle</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Closed</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['closed_tickets'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">task_alt</span>
        </div>
    </div>
</div>

<!-- Summary Stats Cards - Row 2: Performance Metrics (Staff Only) -->
@if(!($isClient ?? false))
<div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 24px;">
    <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Avg Response</p>
                <p style="font-size: 18px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['avg_response_time'] ?? '0h' }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">schedule</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Avg Resolution</p>
                <p style="font-size: 18px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['avg_resolution_time'] ?? '0h' }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">timer</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Total Replies</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['total_replies'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">forum</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Staff Replies</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['staff_replies'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">support_agent</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Client Replies</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['client_replies'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">person</span>
        </div>
    </div>
    <div style="background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%); border-radius: 8px; padding: 14px; color: white;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between;">
            <div>
                <p style="font-size: 10px; opacity: 0.8; margin: 0; text-transform: uppercase;">Total Clients</p>
                <p style="font-size: 22px; font-weight: 700; margin: 2px 0 0 0;">{{ $reportStats['total_clients'] ?? 0 }}</p>
            </div>
            <span class="material-symbols-outlined" style="font-size: 28px; opacity: 0.5;">groups</span>
        </div>
    </div>
</div>
@else
<div style="margin-bottom: 24px;"></div>
@endif

<!-- Row 1: Tickets by Status + Tickets by Priority (Half-Half) -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; width: 100%;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">pie_chart</span>
            Tickets by Status
        </h3>
        <div style="height: 160px; position: relative;"><canvas id="ticketStatusChart"></canvas></div>
    </div>
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #ef4444;">priority_high</span>
            Tickets by Priority
        </h3>
        <div style="height: 160px; position: relative;"><canvas id="ticketPriorityChart"></canvas></div>
    </div>
</div>

<!-- Row 2: Monthly Ticket Trend (Full Width) -->
<div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; margin-bottom: 16px; width: 100%; min-width: 0; overflow: hidden; box-sizing: border-box;">
    <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #10b981;">trending_up</span>
        Monthly Ticket Trend (Last 6 Months)
    </h3>
    <div style="height: 180px; position: relative;"><canvas id="monthlyTrendChart"></canvas></div>
</div>

<!-- Row 3: Reply Comparison (Full Width - Staff Only) -->
@if(!($isClient ?? false))
<div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; margin-bottom: 16px; width: 100%; min-width: 0; overflow: hidden; box-sizing: border-box;">
    <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #6366f1;">compare_arrows</span>
        Reply Comparison: Staff vs Client (Monthly)
    </h3>
    <div style="height: 180px; position: relative;"><canvas id="replyComparisonChart"></canvas></div>
</div>
@endif

<!-- Row 4: Weekly Resolved + Response Time Distribution (Half-Half - Staff Only) -->
@if(!($isClient ?? false))
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; width: 100%;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #8b5cf6;">calendar_month</span>
            Weekly Resolved (Last 4 Weeks)
        </h3>
        <div style="display: flex; flex-direction: column; gap: 6px;">
            @foreach($weeklyResolved ?? [] as $week)
            @php $maxWeek = collect($weeklyResolved)->max('count') ?: 1; $pctWeek = ($week['count'] / max($maxWeek, 1)) * 100; @endphp
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="font-size: 10px; color: #6b7280;">{{ $week['week'] }}</span>
                    <span style="font-size: 10px; font-weight: 600; color: #111827;">{{ $week['count'] }}</span>
                </div>
                <div style="height: 6px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ max($pctWeek, 3) }}%; background: linear-gradient(90deg, #10b981, #34d399); border-radius: 3px;"></div>
                </div>
            </div>
            @endforeach
            @if(empty($weeklyResolved) || count($weeklyResolved) === 0)
            <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 16px 0;">No data</p>
            @endif
        </div>
    </div>
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #f59e0b;">speed</span>
            Response Time Distribution
        </h3>
        <div style="display: flex; flex-direction: column; gap: 6px;">
            @php 
                $responseColors = ['#22c55e', '#84cc16', '#f59e0b', '#ef4444', '#dc2626'];
                $maxResponse = collect($responseTimeDistribution ?? [])->max('count') ?: 1;
            @endphp
            @foreach($responseTimeDistribution ?? [] as $index => $resp)
            @php $pctResp = ($resp['count'] / max($maxResponse, 1)) * 100; @endphp
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="font-size: 10px; color: #6b7280;">{{ $resp['range'] }}</span>
                    <span style="font-size: 10px; font-weight: 600; color: #111827;">{{ $resp['count'] }}</span>
                </div>
                <div style="height: 6px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ max($pctResp, 3) }}%; background-color: {{ $responseColors[$index % count($responseColors)] }}; border-radius: 3px;"></div>
                </div>
            </div>
            @endforeach
            @if(empty($responseTimeDistribution) || count($responseTimeDistribution) === 0)
            <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 16px 0;">No data</p>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Row 5: Tickets by Category + Top Clients (Half-Half) -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; width: 100%;">
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #ec4899;">category</span>
            Tickets by Category
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="height: 140px; position: relative;"><canvas id="ticketCategoryChart"></canvas></div>
            <div style="max-height: 140px; overflow-y: auto;">
                @php $categoryColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16']; @endphp
                @foreach($ticketsByCategory ?? [] as $index => $cat)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f3f4f6;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div style="width: 8px; height: 8px; border-radius: 2px; background-color: {{ $categoryColors[$index % count($categoryColors)] }};"></div>
                        <span style="font-size: 10px; color: #4b5563;">{{ $cat['name'] }}</span>
                    </div>
                    <span style="font-size: 10px; font-weight: 600; color: #111827;">{{ $cat['count'] }}</span>
                </div>
                @endforeach
                @if(empty($ticketsByCategory) || count($ticketsByCategory) === 0)
                <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 30px 0;">No data</p>
                @endif
            </div>
        </div>
    </div>
    @if(!($isClient ?? false))
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #14b8a6;">leaderboard</span>
            Top Clients by Tickets
        </h3>
        @php $maxClient = collect($topClientsByTickets ?? [])->max('count') ?: 1; @endphp
        @foreach($topClientsByTickets ?? [] as $clientData)
        @php $pct = ($clientData['count'] / $maxClient) * 100; @endphp
        <div style="margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                <span style="font-size: 10px; color: #4b5563;">{{ $clientData['name'] }}</span>
                <span style="font-size: 10px; font-weight: 600; color: #111827;">{{ $clientData['count'] }}</span>
            </div>
            <div style="height: 5px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden;">
                <div style="height: 100%; width: {{ max($pct, 3) }}%; background: linear-gradient(90deg, #3b82f6, #8b5cf6); border-radius: 3px;"></div>
            </div>
        </div>
        @endforeach
        @if(empty($topClientsByTickets) || count($topClientsByTickets) === 0)
        <p style="font-size: 11px; color: #9ca3af; text-align: center; padding: 30px 0;">No data</p>
        @endif
    </div>
    @else
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; min-width: 0; overflow: hidden;">
        <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #14b8a6;">history</span>
            Your Ticket History
        </h3>
        <div style="height: 140px; position: relative;"><canvas id="clientHistoryChart"></canvas></div>
    </div>
    @endif
</div>

<!-- Row 6: Recent Tickets Table -->
<div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; border: 1px solid #e5e7eb; width: 100%; min-width: 0; overflow: hidden; box-sizing: border-box;">
    <h3 style="font-size: 12px; font-weight: 600; color: #111827; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px;">
        <span class="material-symbols-outlined" style="font-size: 18px; color: #3b82f6;">list_alt</span>
        Recent Tickets
    </h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 500px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb;">
                    <th style="text-align: left; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Ticket #</th>
                    <th style="text-align: left; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Subject</th>
                    @if(!($isClient ?? false))
                    <th style="text-align: left; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Client</th>
                    @endif
                    <th style="text-align: center; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Priority</th>
                    <th style="text-align: center; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Status</th>
                    <th style="text-align: center; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Replies</th>
                    <th style="text-align: center; padding: 10px 8px; font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase;">Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTickets ?? [] as $ticket)
                <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.15s;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 12px 8px;">
                        <a href="{{ route('helpdesk.show', $ticket) }}" style="font-size: 11px; font-weight: 600; color: #3b82f6; text-decoration: none;">{{ $ticket->ticket_number }}</a>
                    </td>
                    <td style="padding: 12px 8px;">
                        <span style="font-size: 11px; color: #111827; display: block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $ticket->subject }}</span>
                    </td>
                    @if(!($isClient ?? false))
                    <td style="padding: 12px 8px;"><span style="font-size: 11px; color: #6b7280;">{{ $ticket->client?->name ?? '-' }}</span></td>
                    @endif
                    <td style="padding: 12px 8px; text-align: center;">
                        @if($ticket->ticketPriority)
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: {{ $ticket->ticketPriority->color }}15; color: {{ $ticket->ticketPriority->color }}; border: 1px solid {{ $ticket->ticketPriority->color }}30;">
                            <span class="material-symbols-outlined" style="font-size: 12px;">{{ $ticket->ticketPriority->icon }}</span>
                            {{ $ticket->ticketPriority->name }}
                        </span>
                        @else
                        <span style="font-size: 10px; color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px 8px; text-align: center;">
                        @if($ticket->ticketStatus)
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; font-size: 10px; font-weight: 500; border-radius: 9999px; background-color: {{ $ticket->ticketStatus->color }}15; color: {{ $ticket->ticketStatus->color }}; border: 1px solid {{ $ticket->ticketStatus->color }}30;">
                            {{ $ticket->ticketStatus->name }}
                        </span>
                        @else
                        <span style="font-size: 10px; color: #9ca3af;">{{ ucfirst($ticket->status) }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px 8px; text-align: center;">
                        <span style="font-size: 11px; font-weight: 500; color: #374151;">{{ $ticket->replies_count ?? 0 }}</span>
                    </td>
                    <td style="padding: 12px 8px; text-align: center;"><span style="font-size: 10px; color: #9ca3af;">{{ $ticket->created_at->format('d M Y') }}</span></td>
                </tr>
                @endforeach
                @if(empty($recentTickets) || count($recentTickets) === 0)
                <tr>
                    <td colspan="{{ ($isClient ?? false) ? '6' : '7' }}" style="padding: 40px 0; text-align: center;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #d1d5db;">inbox</span>
                        <p style="font-size: 11px; color: #9ca3af; margin-top: 8px;">No tickets found</p>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
</div>
<!-- End Reports Container -->

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 9 }, padding: 8, usePointStyle: true, boxWidth: 8 } }
        }
    };

    // Tickets by Status Doughnut Chart
    new Chart(document.getElementById('ticketStatusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($ticketsByStatus ?? [])->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode(collect($ticketsByStatus ?? [])->pluck('count')) !!},
                backgroundColor: {!! json_encode(collect($ticketsByStatus ?? [])->pluck('color')) !!},
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { ...chartOptions, cutout: '60%' }
    });

    // Tickets by Priority Doughnut Chart
    new Chart(document.getElementById('ticketPriorityChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($ticketsByPriority ?? [])->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode(collect($ticketsByPriority ?? [])->pluck('count')) !!},
                backgroundColor: {!! json_encode(collect($ticketsByPriority ?? [])->pluck('color')) !!},
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { ...chartOptions, cutout: '60%' }
    });

    // Monthly Trend Line Chart (Full Width)
    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($monthlyTrend ?? [])->pluck('month')) !!},
            datasets: [
                {
                    label: 'Created',
                    data: {!! json_encode(collect($monthlyTrend ?? [])->pluck('created')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: '#3b82f620',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6'
                },
                {
                    label: 'Resolved',
                    data: {!! json_encode(collect($monthlyTrend ?? [])->pluck('resolved')) !!},
                    borderColor: '#10b981',
                    backgroundColor: '#10b98120',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 10 }, usePointStyle: true, boxWidth: 8 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, precision: 0 }, beginAtZero: true, suggestedMax: 10 }
            }
        }
    });

    // Tickets by Category Doughnut Chart
    new Chart(document.getElementById('ticketCategoryChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(collect($ticketsByCategory ?? [])->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode(collect($ticketsByCategory ?? [])->pluck('count')) !!},
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '50%' }
    });

    @if(!($isClient ?? false))
    // Reply Comparison Chart (Staff vs Client) - Full Width
    new Chart(document.getElementById('replyComparisonChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($replyComparison ?? [])->pluck('month')) !!},
            datasets: [
                {
                    label: 'Staff Replies',
                    data: {!! json_encode(collect($replyComparison ?? [])->pluck('staff')) !!},
                    backgroundColor: '#6366f1',
                    borderRadius: 3,
                    maxBarThickness: 30
                },
                {
                    label: 'Client Replies',
                    data: {!! json_encode(collect($replyComparison ?? [])->pluck('client')) !!},
                    backgroundColor: '#f97316',
                    borderRadius: 3,
                    maxBarThickness: 30
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 10 }, usePointStyle: true, boxWidth: 8 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, precision: 0 }, beginAtZero: true, suggestedMax: 15 }
            }
        }
    });
    @else
    // Client History Chart
    new Chart(document.getElementById('clientHistoryChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($monthlyTrend ?? [])->pluck('month')) !!},
            datasets: [{
                label: 'Your Tickets',
                data: {!! json_encode(collect($monthlyTrend ?? [])->pluck('created')) !!},
                backgroundColor: '#3b82f6',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
            }
        }
    });
    @endif
});
</script>
@endpush