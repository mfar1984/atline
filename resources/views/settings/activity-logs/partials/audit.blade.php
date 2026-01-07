<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">User Activity Audit</h3>
    </div>

    <!-- Filter Form - Full Width -->
    <div class="mb-4">
        <form action="{{ route('settings.activity-logs.index') }}" method="GET">
            <input type="hidden" name="tab" value="audit">
            <div class="flex flex-wrap items-end gap-3">
                <!-- User Selection -->
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-xs text-gray-500 mb-1">User Account</label>
                    <select name="user_id" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 36px; font-size: 11px;">
                        <option value="">-- Select User Account --</option>
                        @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- DateTime From -->
                <div class="min-w-[180px]">
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="datetime-local" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                           style="font-family: Poppins, sans-serif; min-height: 36px; font-size: 11px;">
                </div>

                <!-- DateTime To -->
                <div class="min-w-[180px]">
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="datetime-local" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                           style="font-family: Poppins, sans-serif; min-height: 36px; font-size: 11px;">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 36px;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">filter_alt</span>
                        FILTER
                    </button>
                    @if(request('user_id'))
                    <a href="{{ route('settings.activity-logs.index', ['tab' => 'audit']) }}" 
                       class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 36px;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                        RESET
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if($selectedUser ?? false)
        <!-- User Info Card -->
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-blue-900" style="font-family: Poppins, sans-serif;">
                        {{ $selectedUser->name }}
                    </h4>
                    <p class="text-xs text-blue-700 mt-1">{{ $selectedUser->email }}</p>
                    <p class="text-xs text-blue-600 mt-1">
                        Role: {{ $selectedUser->role?->name ?? 'No Role' }} |
                        Status: {{ $selectedUser->is_active ? 'Active' : 'Inactive' }}
                    </p>
                </div>
                @if($auditStats ?? false)
                <div class="text-right">
                    <p class="text-xs text-blue-600">
                        Period: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y H:i') : 'All time' }}
                        - {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y H:i') : 'Now' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        @if($auditStats ?? false)
        <!-- Stats Cards - 4 per row -->
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-gray-900">{{ $auditStats['total'] }}</p>
                <p class="text-xs text-gray-500">Total Activities</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-green-600">{{ $auditStats['logins'] }}</p>
                <p class="text-xs text-gray-500">Logins</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold {{ $auditStats['failed_logins'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $auditStats['failed_logins'] }}</p>
                <p class="text-xs text-gray-500">Failed Logins</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-blue-600">{{ $auditStats['creates'] }}</p>
                <p class="text-xs text-gray-500">Creates</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-yellow-600">{{ $auditStats['updates'] }}</p>
                <p class="text-xs text-gray-500">Updates</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-red-600">{{ $auditStats['deletes'] }}</p>
                <p class="text-xs text-gray-500">Deletes</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-purple-600">{{ $auditStats['views'] }}</p>
                <p class="text-xs text-gray-500">Views</p>
            </div>
            <div class="p-3 bg-white border border-gray-200 rounded text-center">
                <p class="text-xl font-bold text-indigo-600">{{ $auditStats['downloads'] }}</p>
                <p class="text-xs text-gray-500">Downloads</p>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                <p class="text-xs font-medium text-gray-700 mb-1">IP Addresses Used:</p>
                <div class="flex flex-wrap gap-1">
                    @forelse($auditStats['ip_addresses'] as $ip)
                        <span class="px-2 py-0.5 bg-white border border-gray-300 rounded text-xs text-gray-600">{{ $ip }}</span>
                    @empty
                        <span class="text-xs text-gray-400">No IP recorded</span>
                    @endforelse
                </div>
            </div>
            <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                <p class="text-xs font-medium text-gray-700 mb-1">Modules Accessed:</p>
                <div class="flex flex-wrap gap-1">
                    @forelse($auditStats['modules'] as $module)
                        <span class="px-2 py-0.5 bg-white border border-gray-300 rounded text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                    @empty
                        <span class="text-xs text-gray-400">No modules</span>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Activity Timeline -->
        <div class="overflow-x-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Date/Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Module</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($auditLogs ?? [] as $log)
                    <tr class="{{ $log->action === 'login_failed' ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap" style="font-family: Poppins, sans-serif;">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded {{ $log->action_color }}" style="font-size: 10px;">
                                <span class="material-symbols-outlined" style="font-size: 12px;">{{ $log->action_icon }}</span>
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                            {{ $log->module ? ucfirst(str_replace('_', ' ', $log->module)) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif; max-width: 300px;">
                            <span class="truncate block" title="{{ $log->description }}">{{ $log->description }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-xs">
                            <span class="material-symbols-outlined text-gray-300" style="font-size: 48px;">history</span>
                            <p class="mt-2">No activity logs found for this user.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($auditLogs) && $auditLogs->hasPages())
        <div class="mt-4">
            <x-ui.custom-pagination :paginator="$auditLogs" record-label="logs" tab-param="audit" />
        </div>
        @endif

    @else
        <!-- No User Selected -->
        <div class="border border-gray-200 rounded bg-gray-50 p-8 text-center">
            <span class="material-symbols-outlined text-gray-300" style="font-size: 64px;">person_search</span>
            <h4 class="mt-4 text-sm font-medium text-gray-600" style="font-family: Poppins, sans-serif;">Select a User to Audit</h4>
            <p class="mt-2 text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                Choose a user account from the dropdown above and optionally set a date range to view their activity history.
            </p>
        </div>
    @endif
</div>
