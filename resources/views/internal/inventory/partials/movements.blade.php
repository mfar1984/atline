<!-- Header -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Movement History</h3>
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('internal.inventory.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="movements">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search asset tag, employee name, purpose..." 
                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
        <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Status</option>
            <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
            SEARCH
        </button>
        <button type="button" onclick="window.location.href='{{ route('internal.inventory.index', ['tab' => 'movements']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Movement History Table -->
<x-ui.data-table
    :headers="[
        ['label' => 'Asset', 'align' => 'text-left'],
        ['label' => 'Employee', 'align' => 'text-left'],
        ['label' => 'Checkout / Return', 'align' => 'text-left'],
        ['label' => 'Purpose', 'align' => 'text-left'],
        ['label' => 'Status', 'align' => 'text-center']
    ]"
    :actions="false"
    empty-message="No movement history found."
>
    @forelse($movements as $movement)
    @php
        $isOverdue = $movement->status === 'checked_out' && $movement->expected_return_date < now()->startOfDay();
    @endphp
    <tr class="hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-xs font-medium text-blue-600">{{ $movement->asset->asset_tag ?? '-' }}</div>
            <div class="text-xs text-gray-500">{{ $movement->asset->name ?? '-' }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-xs text-gray-900">{{ $movement->employee->full_name ?? '-' }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-xs text-gray-600">Out: {{ $movement->checkout_date->format('d/m/Y') }}</div>
            <div class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                Due: {{ $movement->expected_return_date->format('d/m/Y') }}
            </div>
            @if($movement->actual_return_date)
                <div class="text-xs text-green-600">In: {{ $movement->actual_return_date->format('d/m/Y') }}</div>
            @endif
        </td>
        <td class="px-6 py-4">
            <span class="text-xs text-gray-600">{{ Str::limit($movement->purpose, 40) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            @php
                $statusColors = [
                    'checked_out' => $isOverdue ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800',
                    'returned' => 'bg-green-100 text-green-800',
                    'overdue' => 'bg-red-100 text-red-800',
                ];
                $statusLabel = $isOverdue ? 'Overdue' : ucfirst(str_replace('_', ' ', $movement->status));
            @endphp
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$movement->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $statusLabel }}
            </span>
        </td>
    </tr>
    @empty
    @endforelse
</x-ui.data-table>

<div class="mt-4">
    <x-ui.custom-pagination :paginator="$movements" record-label="movements" />
</div>
