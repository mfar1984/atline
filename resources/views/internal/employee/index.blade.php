@extends('layouts.app')

@section('title', 'Employees')

@section('page-title', 'Employees')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Employee List</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage company employees</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('internal_employee.create')
            <a href="{{ route('internal.employee.create') }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                EMPLOYEE
            </a>
            @endpermission
        </div>
    </div>

    <div class="px-6 py-3">
        <form action="{{ route('internal.employee.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search name, IC, position..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('internal.employee.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded">
            <p class="text-xs text-green-800" style="font-family: Poppins, sans-serif;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div class="px-6">
        <x-ui.data-table
            :headers="[
                ['label' => 'Employee', 'align' => 'text-left'],
                ['label' => 'IC / Service', 'align' => 'text-left'],
                ['label' => 'Position', 'align' => 'text-left'],
                ['label' => 'Contact', 'align' => 'text-left'],
                ['label' => 'Status', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No employees found."
        >
            @forelse($employees as $employee)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-xs font-semibold text-blue-600">{{ strtoupper(substr($employee->full_name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-900">{{ $employee->full_name }}</div>
                            @if($employee->user)
                            <div class="text-xs text-gray-500">{{ $employee->user->email }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs text-gray-600">{{ $employee->ic_number }}</div>
                    <div class="text-xs text-blue-600 font-medium">{{ $employee->service_duration }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs text-gray-900">{{ $employee->position ?? '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs text-gray-600">{{ $employee->telephone ?? '-' }}</div>
                    @if($employee->email)
                    <div class="text-xs text-gray-500">{{ $employee->email }}</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-gray-100 text-gray-800',
                            'resigned' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$employee->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <x-ui.action-buttons
                        :edit-url="auth()->user()->hasPermission('internal_employee.update') ? route('internal.employee.edit', $employee) : null"
                        :show-url="route('internal.employee.show', $employee)"
                        :delete-onclick="auth()->user()->hasPermission('internal_employee.delete') ? 'deleteEmployee(' . $employee->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$employees" record-label="employees" />
    </div>
</div>

@push('scripts')
<script>
function deleteEmployee(id) {
    window.showDeleteModal('{{ route("internal.employee.index") }}/' + id);
}
</script>
@endpush
@endsection
