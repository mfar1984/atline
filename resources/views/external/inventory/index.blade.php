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
            <button type="button" onclick="openBulkAssetModal()"
               class="inline-flex items-center gap-2 px-3 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">upload_file</span>
                BULK ASSET
            </button>
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

    @if(session('errors') && is_array(session('errors')) && count(session('errors')) > 0)
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs font-semibold text-yellow-900 mb-2" style="font-family: Poppins, sans-serif;">CSV Upload Errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('errors') as $error)
                    <li class="text-xs text-yellow-800" style="font-family: Poppins, sans-serif;">{{ $error }}</li>
                @endforeach
            </ul>
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

<!-- Bulk Asset Upload Modal -->
<div id="bulkAssetModal" class="hidden fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
    <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 520px !important; margin: 16px !important; overflow: hidden !important;" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #10b981 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">upload_file</span>
                </div>
                <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Bulk Asset Upload</h3>
            </div>
            <button type="button" onclick="closeBulkAssetModal()" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="bulkAssetForm" action="{{ route('external.inventory.bulk-store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="padding: 20px !important;">
                <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                    <!-- Project Selection -->
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            Project <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <select name="project_id" id="bulkProjectId" required
                                style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- CSV File Upload -->
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            CSV File <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <div style="border: 2px dashed #d1d5db !important; border-radius: 8px !important; padding: 20px !important; text-align: center !important; cursor: pointer !important; transition: all 0.2s !important;" 
                             onclick="document.getElementById('csvFileInput').click()"
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="handleDrop(event)">
                            <span class="material-symbols-outlined" style="font-size: 40px !important; color: #9ca3af !important;">cloud_upload</span>
                            <p style="font-size: 12px !important; color: #6b7280 !important; margin: 8px 0 4px 0 !important;">Drag and drop CSV file here or click to browse</p>
                            <p style="font-size: 10px !important; color: #9ca3af !important; margin: 0 !important;">Only CSV files are accepted</p>
                        </div>
                        <input type="file" name="csv_file" id="csvFileInput" accept=".csv" required style="display: none !important;" onchange="handleFileSelect(this)">
                        
                        <!-- Selected File Info -->
                        <div id="selectedFileInfo" style="display: none !important; margin-top: 12px !important; padding: 12px !important; background-color: #f3f4f6 !important; border-radius: 6px !important;">
                            <div style="display: flex !important; align-items: center !important; gap: 12px !important;">
                                <div style="width: 40px !important; height: 40px !important; background-color: #dbeafe !important; border-radius: 6px !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #2563eb !important;">description</span>
                                </div>
                                <div style="flex: 1 !important; min-width: 0 !important;">
                                    <p style="font-size: 11px !important; font-weight: 500 !important; color: #111827 !important; margin: 0 !important; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important;" id="selectedFileName"></p>
                                    <p style="font-size: 10px !important; color: #6b7280 !important; margin: 2px 0 0 0 !important;" id="selectedFileSize"></p>
                                </div>
                                <button type="button" onclick="clearFile()" style="color: #ef4444 !important; background: none !important; border: none !important; cursor: pointer !important;">
                                    <span class="material-symbols-outlined" style="font-size: 18px !important;">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Download Example CSV -->
                    <div style="padding: 12px !important; background-color: #eff6ff !important; border-radius: 6px !important; border: 1px solid #bfdbfe !important;">
                        <div style="display: flex !important; align-items: start !important; gap: 10px !important;">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #3b82f6 !important;">info</span>
                            <div style="flex: 1 !important;">
                                <p style="font-size: 11px !important; color: #1e40af !important; margin: 0 0 6px 0 !important; font-weight: 500 !important;">Need a template?</p>
                                <a href="{{ route('external.inventory.download-template') }}" class="inline-flex items-center gap-1" style="font-size: 11px !important; color: #2563eb !important; text-decoration: none !important; font-weight: 500 !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important;">download</span>
                                    Download Example CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; gap: 10px !important; background-color: #f9fafb !important;">
                <button type="button" onclick="closeBulkAssetModal()" 
                        style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                        onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                    Cancel
                </button>
                <button type="submit"
                        style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #10b981 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 6px !important;"
                        onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10b981'">
                    <span class="material-symbols-outlined" style="font-size: 16px !important;">upload</span>
                    Upload & Create Assets
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function deleteAsset(id) {
    window.showDeleteModal('{{ route("external.inventory.index") }}/' + id);
}

function openBulkAssetModal() {
    document.getElementById('bulkAssetModal').classList.remove('hidden');
}

function closeBulkAssetModal() {
    document.getElementById('bulkAssetModal').classList.add('hidden');
    document.getElementById('bulkAssetForm').reset();
    document.getElementById('selectedFileInfo').style.display = 'none';
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#3b82f6';
    e.currentTarget.style.backgroundColor = '#eff6ff';
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#d1d5db';
    e.currentTarget.style.backgroundColor = 'transparent';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#d1d5db';
    e.currentTarget.style.backgroundColor = 'transparent';
    
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].name.endsWith('.csv')) {
        document.getElementById('csvFileInput').files = files;
        handleFileSelect(document.getElementById('csvFileInput'));
    } else {
        alert('Please upload a CSV file only.');
    }
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        if (!file.name.endsWith('.csv')) {
            alert('Please upload a CSV file only.');
            input.value = '';
            return;
        }
        
        document.getElementById('selectedFileName').textContent = file.name;
        document.getElementById('selectedFileSize').textContent = formatFileSize(file.size);
        document.getElementById('selectedFileInfo').style.display = 'block';
    }
}

function clearFile() {
    document.getElementById('csvFileInput').value = '';
    document.getElementById('selectedFileInfo').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' bytes';
}

// Close modal when clicking outside
document.getElementById('bulkAssetModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBulkAssetModal();
    }
});
</script>
@endpush
@endsection
