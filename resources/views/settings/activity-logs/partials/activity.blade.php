<div x-data="{ showDeleteConfirm: false, deleteDays: 30, showDeleteDropdown: false }">
    <!-- Header with Storage Info -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Activity Log</h3>
            <!-- Storage Info -->
            <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded">
                <span class="material-symbols-outlined text-gray-400" style="font-size: 14px;">storage</span>
                <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                    {{ $storageUsed ?? '0 KB' }} / 5 MB
                </span>
                @if(isset($storagePercent) && $storagePercent > 80)
                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-red-100 text-red-600" style="font-size: 9px;">
                        {{ $storagePercent }}%
                    </span>
                @elseif(isset($storagePercent) && $storagePercent > 50)
                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-600" style="font-size: 9px;">
                        {{ $storagePercent }}%
                    </span>
                @elseif(isset($storagePercent))
                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-green-100 text-green-600" style="font-size: 9px;">
                        {{ $storagePercent }}%
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Search/Filter Form -->
    <div class="mb-4">
        <form action="{{ route('settings.activity-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="activity">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search description, action, module..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <!-- Delete Dropdown Button -->
            <div class="relative">
                <button type="button" @click="showDeleteDropdown = !showDeleteDropdown" 
                        class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition hover:bg-red-700" 
                        style="min-height: 32px; background-color: #dc2626;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                    DELETE
                    <span class="material-symbols-outlined" style="font-size: 14px;">expand_more</span>
                </button>
                <!-- Dropdown Menu -->
                <div x-show="showDeleteDropdown" @click.away="showDeleteDropdown = false" x-cloak
                     class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded shadow-lg z-50">
                    <button type="button" @click="deleteDays = 30; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        30 Days
                    </button>
                    <button type="button" @click="deleteDays = 60; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        60 Days
                    </button>
                    <button type="button" @click="deleteDays = 90; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        90 Days
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" @click.away="showDeleteConfirm = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600" style="font-size: 20px;">warning</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Delete Activity Logs</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6" style="font-family: Poppins, sans-serif;">
                    Are you sure you want to delete all activity logs older than <span class="font-semibold" x-text="deleteDays + ' days'"></span>? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showDeleteConfirm = false"
                            class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                            style="font-family: Poppins, sans-serif;">
                        Cancel
                    </button>
                    <form action="{{ route('settings.activity-logs.delete') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="days" x-bind:value="deleteDays">
                        <button type="submit"
                                class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                                style="font-family: Poppins, sans-serif;">
                            Delete Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Date/Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Module</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">IP Address</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs ?? [] as $log)
                <tr>
                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap" style="font-family: Poppins, sans-serif;">
                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                        {{ $log->user?->name ?? 'System' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($log->client_id)
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-600" style="font-size: 10px;" title="{{ $log->client?->name }}">
                                <span class="material-symbols-outlined" style="font-size: 12px;">business</span>
                                Client
                            </span>
                        @elseif($log->employee_id)
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-600" style="font-size: 10px;" title="{{ $log->employee?->name }}">
                                <span class="material-symbols-outlined" style="font-size: 12px;">badge</span>
                                Staff
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600" style="font-size: 10px;">
                                <span class="material-symbols-outlined" style="font-size: 12px;">computer</span>
                                System
                            </span>
                        @endif
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
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">
                        <span class="material-symbols-outlined text-gray-300" style="font-size: 48px;">history</span>
                        <p class="mt-2">No activity logs found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($logs) && $logs->hasPages())
    <div class="mt-4">
        <x-ui.custom-pagination :paginator="$logs" record-label="logs" tab-param="activity" />
    </div>
    @elseif(isset($logs))
    <div class="mt-4">
        <p class="text-xs text-gray-400" style="font-family: Poppins, sans-serif;">
            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs
        </p>
    </div>
    @endif
</div>
