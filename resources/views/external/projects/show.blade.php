@extends('layouts.app')

@section('title', 'View Project')

@section('page-title', 'View Project')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project Details</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">{{ $project->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.projects.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <a href="{{ route('external.projects.edit', $project) }}" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                EDIT
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Project Information -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project Information</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Project Name</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->name }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Client</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->client?->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Description</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 64px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->description ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Details -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project Details</h3>
                </div>
                <div class="p-4 space-y-4">
                    @if(!($isClient ?? false))
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Project Value (RM)</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->project_value ? number_format($project->project_value, 2) : '-' }}</span>
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Start Date</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">@formatDate($project->start_date)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">End Date</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">@formatDate($project->end_date)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Status</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                    'on_hold' => 'bg-yellow-100 text-yellow-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-800' }}" style="font-size: 10px;">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procurement & Warranty -->
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Procurement & Warranty</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Purchase Date</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">@formatDate($project->purchase_date)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">PO/Invoice Number</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->po_number ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Period</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $project->warranty_period ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Expiry</label>
                        <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded" style="min-height: 32px;">
                            <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">@formatDate($project->warranty_expiry)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Inventory List ({{ $project->assets->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Asset Tag</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Brand</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Serial Number</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($project->assets as $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-900 font-medium" style="font-family: Poppins, sans-serif;">{{ $asset->asset_tag }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $asset->category->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $asset->brand->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $asset->model ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $asset->serial_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $assetStatusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'spare' => 'bg-blue-100 text-blue-800',
                                        'damaged' => 'bg-red-100 text-red-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        'disposed' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $assetStatusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }}" style="font-size: 10px;">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <x-ui.action-buttons
                                    :show-url="route('external.inventory.show', $asset)"
                                    :edit-url="route('external.inventory.edit', $asset)"
                                />
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">No inventory found for this project.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- File Attachments -->
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">File Attachments ({{ $project->attachments->count() }})</h3>
            </div>
            @if($project->attachments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">File Name</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Storage</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Size</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Uploaded</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($project->attachments as $attachment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @php
                                        $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                        $iconClass = match($ext) {
                                            'pdf' => 'text-red-500',
                                            'doc', 'docx' => 'text-blue-500',
                                            'xls', 'xlsx' => 'text-green-500',
                                            'jpg', 'jpeg', 'png' => 'text-purple-500',
                                            default => 'text-gray-500'
                                        };
                                        $iconName = match($ext) {
                                            'pdf' => 'picture_as_pdf',
                                            'doc', 'docx' => 'description',
                                            'xls', 'xlsx' => 'table_chart',
                                            'jpg', 'jpeg', 'png' => 'image',
                                            default => 'insert_drive_file'
                                        };
                                    @endphp
                                    <span class="material-symbols-outlined {{ $iconClass }}" style="font-size: 18px;">{{ $iconName }}</span>
                                    <span class="text-xs text-gray-900" style="font-size: 11px; font-family: Poppins, sans-serif;">{{ $attachment->display_name ?? $attachment->file_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600 uppercase" style="font-size: 10px;">{{ $ext }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($attachment->isR2Storage())
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-sky-100 text-sky-700" style="font-size: 10px;">
                                        <span class="material-symbols-outlined" style="font-size: 12px;">cloud</span>
                                        Cloud
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600" style="font-size: 10px;">
                                        <span class="material-symbols-outlined" style="font-size: 12px;">folder</span>
                                        Local
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-gray-600" style="font-size: 11px;">
                                    @if($attachment->file_size >= 1048576)
                                        {{ number_format($attachment->file_size / 1048576, 1) }} MB
                                    @else
                                        {{ number_format($attachment->file_size / 1024, 1) }} KB
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-gray-600" style="font-size: 11px;">@formatDate($attachment->created_at)</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-600 text-xs rounded hover:bg-blue-100 transition" style="font-size: 10px;">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                                    Download
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-4">
                <div class="text-center py-8 text-gray-400 border border-dashed border-gray-300 rounded">
                    <span class="material-symbols-outlined" style="font-size: 48px;">folder_off</span>
                    <p class="mt-2 text-xs" style="font-size: 11px;">No attachments found.</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Metadata -->
        <div class="mt-6 flex items-center justify-between text-xs text-gray-400" style="font-size: 10px;">
            <span>Created: @formatDateTime($project->created_at)</span>
            <span>Last Updated: @formatDateTime($project->updated_at)</span>
        </div>
    </div>
</div>
@endsection
