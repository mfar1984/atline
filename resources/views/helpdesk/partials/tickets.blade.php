<!-- Stats Cards -->
<div class="mb-4">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <div class="bg-blue-50 border border-blue-200 rounded p-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600" style="font-size: 20px;">inbox</span>
                <div>
                    <p class="text-xs text-blue-600" style="font-family: Poppins, sans-serif;">Open</p>
                    <p class="text-lg font-semibold text-blue-800">{{ $stats['open'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-600" style="font-size: 20px;">pending</span>
                <div>
                    <p class="text-xs text-yellow-600" style="font-family: Poppins, sans-serif;">In Progress</p>
                    <p class="text-lg font-semibold text-yellow-800">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded p-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-600" style="font-size: 20px;">schedule</span>
                <div>
                    <p class="text-xs text-orange-600" style="font-family: Poppins, sans-serif;">Pending</p>
                    <p class="text-lg font-semibold text-orange-800">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded p-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600" style="font-size: 20px;">check_circle</span>
                <div>
                    <p class="text-xs text-green-600" style="font-family: Poppins, sans-serif;">Resolved</p>
                    <p class="text-lg font-semibold text-green-800">{{ $stats['resolved'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="mb-4">
    <form action="{{ route('helpdesk.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="tickets">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search ticket number, subject, serial number..." 
                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
        <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Status</option>
            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <select name="priority" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Priority</option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
            SEARCH
        </button>
        <button type="button" onclick="window.location.href='{{ route('helpdesk.index', ['tab' => 'tickets']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Tickets Table -->
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Ticket & Asset</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">{{ $client ? 'Subject' : 'Client & Subject' }}</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Priority</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Status</th>
                @if(!$client)
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Assigned To</th>
                @endif
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Created</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div>
                        <span class="text-xs font-medium text-blue-600" style="font-family: Poppins, sans-serif;">{{ $ticket->ticket_number }}</span>
                    </div>
                    <div class="mt-0.5">
                        <span class="text-xs text-gray-500" style="font-family: Poppins, sans-serif;">{{ $ticket->asset->serial_number ?? '-' }}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    @if(!$client)
                    <div>
                        <span class="text-xs font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $ticket->client->name ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="{{ !$client ? 'mt-0.5' : '' }}">
                        <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ Str::limit($ticket->subject, 50) }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $ticket->priority_color }}" style="font-size: 10px;">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $ticket->status_color }}" style="font-size: 10px;">
                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                    </span>
                </td>
                @if(!$client)
                <td class="px-4 py-3">
                    @if($ticket->assignees->count() > 0)
                        @if($canAssign)
                        <div class="space-y-0.5">
                            @foreach($ticket->assignees as $assignee)
                            <div class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                                {{ $assignee->employee->full_name ?? $assignee->name }}
                            </div>
                            @endforeach
                        </div>
                        @else
                        @php
                            $myAssignment = $ticket->assignees->where('id', auth()->id())->first();
                        @endphp
                        @if($myAssignment)
                        <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                            {{ $myAssignment->employee->full_name ?? $myAssignment->name }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400" style="font-family: Poppins, sans-serif;">-</span>
                        @endif
                        @endif
                    @else
                    <span class="text-xs text-gray-400" style="font-family: Poppins, sans-serif;">-</span>
                    @endif
                </td>
                @endif
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <x-ui.action-buttons
                        :show-url="route('helpdesk.show', $ticket)"
                        :delete-onclick="$client ? null : (auth()->user()->hasPermission('helpdesk_tickets.delete') ? 'deleteTicket(' . $ticket->id . ')' : null)"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $client ? 6 : 7 }}" class="px-4 py-12 text-center">
                    <div class="inline-flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-gray-400" style="font-size: 32px;">confirmation_number</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500" style="font-family: Poppins, sans-serif;">No tickets found</p>
                        <p class="text-xs text-gray-400 mt-1" style="font-family: Poppins, sans-serif;">Click "NEW TICKET" to create one.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    <x-ui.custom-pagination :paginator="$tickets" record-label="tickets" />
</div>
