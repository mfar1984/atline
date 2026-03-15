@extends('layouts.app')

@section('title', 'View Asset')

@section('page-title', 'View Asset')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Asset Details</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">{{ $asset->asset_tag }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.inventory.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <a href="{{ route('external.inventory.edit', $asset) }}" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">edit</span>
                EDIT
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Basic Information</h3>
                </div>
                <div class="p-4">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Project</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->project->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Category</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->category->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Asset Tag/ID</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->asset_tag }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Brand</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->brand->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Model</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->model ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Serial Number</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->serial_number ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Status</dt>
                            <dd>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'spare' => 'bg-blue-100 text-blue-800',
                                        'damaged' => 'bg-red-100 text-red-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        'disposed' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }}" style="font-size: 10px;">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Location & Assignment -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Location & Assignment</h3>
                </div>
                <div class="p-4">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Location</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->location->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Assigned To</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->assigned_to ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500" style="font-size: 11px;">Department</dt>
                            <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->department ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Notes</dt>
                            <dd class="text-xs text-gray-900 bg-gray-50 p-2 rounded" style="font-size: 11px;">{{ $asset->notes ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Technical Specifications -->
        @if($asset->specs && count($asset->specs) > 0)
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Technical Specifications</h3>
            </div>
            <div class="p-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($asset->specs as $key => $value)
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-xs text-gray-500" style="font-size: 11px;">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $value ?? '-' }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>
        @endif

        <!-- Procurement & Warranty -->
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Procurement & Warranty</h3>
            </div>
            <div class="p-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Purchase Date</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">@formatDate($asset->purchase_date)</dd>
                    </div>
                    @if(!($isClient ?? false))
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Unit Price</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">@formatCurrency($asset->unit_price)</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">PO/Invoice Number</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->po_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Vendor</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->vendor->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Warranty Period</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">{{ $asset->warranty_period ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 mb-1" style="font-size: 11px;">Warranty Expiry</dt>
                        <dd class="text-xs font-medium text-gray-900" style="font-size: 11px;">
                            @if($asset->warranty_expiry)
                                @formatDate($asset->warranty_expiry)
                                @if($asset->warranty_expiry->isPast())
                                    <span class="text-red-500 text-xs">(Expired)</span>
                                @elseif($asset->warranty_expiry->diffInDays(now()) <= 30)
                                    <span class="text-yellow-500 text-xs">(Expiring Soon)</span>
                                @endif
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- File Attachments -->
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">File Attachments ({{ $asset->attachments->count() }})</h3>
            </div>
            <div class="p-4">
                @if($asset->attachments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">File Name</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Type</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Size</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Uploaded</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($asset->attachments as $attachment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $ext = strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION));
                                            $iconClass = match($ext) {
                                                'pdf' => 'text-red-600',
                                                'doc', 'docx' => 'text-blue-600',
                                                'xls', 'xlsx' => 'text-green-600',
                                                'jpg', 'jpeg', 'png' => 'text-purple-600',
                                                default => 'text-gray-600'
                                            };
                                            $iconName = match($ext) {
                                                'pdf' => 'description',
                                                'doc', 'docx' => 'article',
                                                'xls', 'xlsx' => 'table_chart',
                                                'jpg', 'jpeg', 'png' => 'image',
                                                default => 'insert_drive_file'
                                            };
                                        @endphp
                                        <span class="material-symbols-outlined {{ $iconClass }}" style="font-size: 20px;">{{ $iconName }}</span>
                                        <div>
                                            <p class="text-xs font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $attachment->display_name ?? $attachment->file_name }}</p>
                                            <p class="text-xs text-gray-500" style="font-size: 10px;">{{ $attachment->file_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs text-gray-600 uppercase" style="font-size: 11px;">{{ $ext }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs text-gray-600" style="font-size: 11px;">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs text-gray-600" style="font-size: 11px;">@formatDate($attachment->created_at)</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded hover:bg-blue-100 transition" style="min-height: 28px; font-size: 10px;">
                                        <span class="material-symbols-outlined mr-1" style="font-size: 14px;">download</span>
                                        Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-gray-400">
                    <span class="material-symbols-outlined" style="font-size: 48px;">folder_off</span>
                    <p class="mt-2 text-xs" style="font-size: 11px;">No attachments found.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Activity Log -->
        @if($asset->logs->count() > 0)
        <div class="mt-6 border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Activity Log</h3>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @foreach($asset->logs->take(10) as $log)
                    <div class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-blue-600" style="font-size: 16px;">history</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-900" style="font-size: 11px;">
                                <span class="font-medium">{{ ucwords(str_replace('_', ' ', $log->field_name)) }}</span> changed from 
                                <span class="text-red-600">{{ $log->old_value ?? 'empty' }}</span> to 
                                <span class="text-green-600">{{ $log->new_value ?? 'empty' }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 10px;">
                                by {{ $log->user->name ?? 'System' }} • {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Metadata -->
        <div class="mt-6 flex items-center justify-between text-xs text-gray-400" style="font-size: 10px;">
            <span>Created: @formatDateTime($asset->created_at)</span>
            <span>Last Updated: @formatDateTime($asset->updated_at)</span>
        </div>
    </div>
</div>
@endsection
