@extends('layouts.app')

@section('title', 'View File - ' . $download->name)

@section('page-title', 'Download')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
        <div class="flex items-center gap-4">
            <a href="{{ route('internal.download.index') }}" class="text-gray-500 hover:text-gray-700">
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
            </a>
            <div>
                <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">{{ $download->name }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">File Details</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($download->status === 'completed')
            <a href="{{ route('internal.download.file', $download) }}" 
               class="inline-flex items-center gap-2 px-3 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                DOWNLOAD
            </a>
            @endif
            @permission('internal_download.delete')
            <button type="button" onclick="deleteDownload({{ $download->id }})"
               class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
               style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                DELETE
            </button>
            @endpermission
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-2 gap-6">
            <!-- File Info Card -->
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-4" style="font-family: Poppins, sans-serif;">File Information</h3>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: #dbeafe;">
                        <span class="material-symbols-outlined" style="font-size: 32px; color: #2563eb;">
                            @switch($download->file_extension)
                                @case('pdf')
                                    picture_as_pdf
                                    @break
                                @case('doc')
                                @case('docx')
                                    description
                                    @break
                                @case('xls')
                                @case('xlsx')
                                    table_chart
                                    @break
                                @case('jpg')
                                @case('jpeg')
                                @case('png')
                                @case('gif')
                                    image
                                    @break
                                @case('zip')
                                @case('rar')
                                    folder_zip
                                    @break
                                @default
                                    insert_drive_file
                            @endswitch
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $download->original_filename }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $download->formatted_file_size }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Display Name</span>
                        <span class="text-xs font-medium text-gray-900">{{ $download->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">File Type</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ strtoupper($download->file_extension) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">MIME Type</span>
                        <span class="text-xs text-gray-900">{{ $download->file_type }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">File Size</span>
                        <span class="text-xs font-medium text-gray-900">{{ $download->formatted_file_size }}</span>
                    </div>
                </div>
            </div>

            <!-- Status & Stats Card -->
            <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-4" style="font-family: Poppins, sans-serif;">Status & Statistics</h3>
                
                <!-- Status -->
                <div class="mb-6">
                    <div class="flex items-center gap-3">
                        @if($download->status === 'completed')
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #dcfce7;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #16a34a;">check_circle</span>
                        </div>
                        @elseif($download->status === 'uploading')
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #dbeafe;">
                            <span class="material-symbols-outlined animate-spin" style="font-size: 20px; color: #2563eb;">progress_activity</span>
                        </div>
                        @elseif($download->status === 'failed')
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #fee2e2;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #dc2626;">error</span>
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #f3f4f6;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #6b7280;">schedule</span>
                        </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($download->status) }}</p>
                            @if($download->status === 'uploading')
                            <p class="text-xs text-gray-500">{{ $download->upload_progress }}% complete</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($download->status === 'uploading')
                    <div class="mt-3">
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-300" style="width: {{ $download->upload_progress }}%"></div>
                        </div>
                    </div>
                    @endif
                    
                    @if($download->status === 'failed' && $download->error_message)
                    <div class="mt-3 p-3 bg-red-50 rounded-lg">
                        <p class="text-xs text-red-700">{{ $download->error_message }}</p>
                    </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Download Count</span>
                        <span class="text-xs font-medium text-gray-900">{{ $download->download_count }} times</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Uploaded By</span>
                        <span class="text-xs text-gray-900">{{ $download->uploader?->name ?? 'System' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Created At</span>
                        <span class="text-xs text-gray-900">{{ $download->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-xs text-gray-500">Last Updated</span>
                        <span class="text-xs text-gray-900">{{ $download->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($download->status === 'completed' && $download->storage_url)
        <!-- Storage Info -->
        <div class="mt-6 bg-gray-50 rounded-lg p-5 border border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900 mb-4" style="font-family: Poppins, sans-serif;">Storage Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="text-xs text-gray-500">Storage Path</span>
                    <span class="text-xs text-gray-900 font-mono">{{ $download->storage_path }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function deleteDownload(id) {
    window.showDeleteModal('{{ route("internal.download.index") }}/' + id);
}
</script>
@endpush
@endsection
