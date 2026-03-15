@extends('layouts.app')

@section('title', 'Projects')

@section('page-title', 'Projects')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project List</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage your external projects</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('external_projects.create')
            <a href="{{ route('external.projects.create') }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                PROJECT
            </a>
            @endpermission
        </div>
    </div>

    <div class="px-6 py-3">
        <form id="filter-form" action="{{ route('external.projects.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search project name or client" 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('external.projects.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
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

    @if(session('error'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded">
            <p class="text-xs text-red-800" style="font-family: Poppins, sans-serif;">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="px-6">
        <x-ui.data-table
            :headers="[
                ['label' => 'Project Name', 'align' => 'text-left'],
                ['label' => 'Client', 'align' => 'text-left'],
                ['label' => 'Assets', 'align' => 'text-center'],
                ['label' => 'Status', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No projects found."
        >
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3" style="max-width: 300px;">
                    <div class="text-xs font-medium text-gray-900 truncate" title="{{ $project->name }}">{{ Str::limit($project->name, 50) }}</div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-xs text-gray-500">{{ $project->client->name ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $project->assets_count ?? 0 }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'on_hold' => 'bg-yellow-100 text-yellow-800',
                        ];
                    @endphp
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-ui.action-buttons
                        :edit-url="auth()->user()->hasPermission('external_projects.update') ? route('external.projects.edit', $project) : null"
                        :show-url="route('external.projects.show', $project)"
                        :delete-onclick="auth()->user()->hasPermission('external_projects.delete') ? 'deleteProject(' . $project->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$projects" record-label="projects" />
    </div>
</div>

@push('scripts')
<script>
function deleteProject(id) {
    window.showDeleteModal('{{ route("external.projects.index") }}/' + id);
}
</script>
@endpush
@endsection
