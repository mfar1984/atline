@extends('layouts.app')

@section('title', 'External Inventory')

@section('page-title', 'External Inventory')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Asset List</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage your IT assets across projects</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('external_inventory.create')
            <a href="{{ route('external.inventory.create') }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                ASSET
            </a>
            @endpermission
        </div>
    </div>

    <div class="px-6 py-3">
        <form id="filter-form" action="{{ route('external.inventory.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search asset tag, model, serial..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="project_id" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; max-width: 200px;">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ Str::limit($project->name, 30) }}</option>
                @endforeach
            </select>
            <select name="category_id" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[100px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="spare" {{ request('status') == 'spare' ? 'selected' : '' }}>Spare</option>
                <option value="damaged" {{ request('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('external.inventory.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
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
                ['label' => 'Asset', 'align' => 'text-left'],
                ['label' => 'Project', 'align' => 'text-left'],
                ['label' => 'Category / Brand', 'align' => 'text-left'],
                ['label' => 'Location', 'align' => 'text-left'],
                ['label' => 'Status', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No assets found."
        >
            @forelse($assets as $asset)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs font-medium text-blue-600" style="font-family: Poppins, sans-serif;">
                        {{ $asset->asset_tag }}
                    </div>
                    @if($asset->serial_number)
                    <div class="text-xs text-gray-500">S/N: {{ $asset->serial_number }}</div>
                    @endif
                </td>
                <td class="px-6 py-4" style="max-width: 200px;">
                    <span class="text-xs text-gray-900 block truncate" title="{{ $asset->project->name ?? '-' }}">{{ Str::limit($asset->project->name ?? '-', 35) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        {{ $asset->category->name ?? '-' }}
                    </span>
                    <div class="text-xs text-gray-600 mt-1">{{ $asset->brand->name ?? '-' }}{{ $asset->model ? ' / ' . $asset->model : '' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs text-gray-600">{{ $asset->location->name ?? '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @php
                        $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'spare' => 'bg-blue-100 text-blue-800',
                            'damaged' => 'bg-red-100 text-red-800',
                            'maintenance' => 'bg-yellow-100 text-yellow-800',
                            'disposed' => 'bg-gray-100 text-gray-800',
                        ];
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($asset->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <x-ui.action-buttons
                        :edit-url="auth()->user()->hasPermission('external_inventory.update') ? route('external.inventory.edit', $asset) : null"
                        :show-url="route('external.inventory.show', $asset)"
                        :delete-onclick="auth()->user()->hasPermission('external_inventory.delete') ? 'deleteAsset(' . $asset->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$assets" record-label="assets" />
    </div>
</div>

@push('scripts')
<script>
function deleteAsset(id) {
    window.showDeleteModal('{{ route("external.inventory.index") }}/' + id);
}
</script>
@endpush
@endsection
