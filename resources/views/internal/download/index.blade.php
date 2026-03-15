@extends('layouts.app')

@section('title', 'Download Center')

@section('page-title', 'Download')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Download Center</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage and share files with Cloudflare R2 integration</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('internal_download.create')
            <button type="button" onclick="openUploadModal()"
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">cloud_upload</span>
                UPLOAD
            </button>
            @endpermission
        </div>
    </div>

    <div class="px-6 py-3">
        <form id="filter-form" action="{{ route('internal.download.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search file name..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="file_type" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Types</option>
                @foreach($fileTypes as $type)
                    <option value="{{ $type }}" {{ request('file_type') == $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="uploading" {{ request('status') == 'uploading' ? 'selected' : '' }}>Uploading</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('internal.download.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
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
                ['label' => 'File Name', 'align' => 'text-left'],
                ['label' => 'File Type', 'align' => 'text-center'],
                ['label' => 'Created', 'align' => 'text-center'],
                ['label' => 'Download', 'align' => 'text-center'],
                ['label' => 'Status', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No files found."
        >
            @forelse($downloads as $download)
            <tr class="hover:bg-gray-50" id="row-{{ $download->id }}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: #dbeafe;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: #2563eb;">
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
                            <div class="text-xs font-medium text-gray-900" style="font-family: Poppins, sans-serif;">
                                {{ $download->name }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $download->formatted_file_size }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ strtoupper($download->file_extension) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-xs text-gray-600">{{ $download->created_at->format('d/m/Y') }}</span>
                    <div class="text-xs text-gray-400">{{ $download->created_at->format('H:i') }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-xs font-medium text-gray-900">{{ $download->download_count }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div id="status-{{ $download->id }}">
                        @if($download->status === 'uploading')
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600 rounded-full transition-all duration-300" style="width: {{ $download->upload_progress }}%"></div>
                            </div>
                            <span class="text-xs text-blue-600">{{ $download->upload_progress }}%</span>
                        </div>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $download->status_color }}">
                            {{ ucfirst($download->status) }}
                        </span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="inline-flex items-center bg-white border border-gray-300 rounded-full shadow-sm" style="border-radius: 9999px !important; padding: 1px;">
                        @if($download->status === 'completed')
                        <!-- Download Button -->
                        <a href="{{ route('internal.download.file', $download) }}" 
                           class="inline-flex items-center justify-center text-green-600 hover:text-green-700 hover:bg-green-50 transition-colors duration-150"
                           title="Download"
                           style="border-radius: 9999px 0 0 9999px !important; padding: 4px 6px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                        </a>
                        <!-- Divider -->
                        <div class="h-4 w-px bg-gray-300"></div>
                        @endif
                        
                        <!-- View Button -->
                        <a href="{{ route('internal.download.show', $download) }}" 
                           class="inline-flex items-center justify-center text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors duration-150"
                           title="View"
                           style="border-radius: {{ $download->status === 'completed' ? '0' : '9999px 0 0 9999px' }} !important; padding: 4px 6px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">open_in_new</span>
                        </a>
                        
                        <!-- Divider -->
                        <div class="h-4 w-px bg-gray-300"></div>
                        
                        <!-- More Actions Dropdown -->
                        @permission('internal_download.delete')
                        <div class="dropdown-container" style="position: relative; display: inline-block;">
                            <button type="button" 
                                    class="inline-flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-150 dropdown-trigger"
                                    title="More actions"
                                    style="border-radius: 0 9999px 9999px 0 !important; padding: 4px 6px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">more_vert</span>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu hidden" style="position: fixed !important; z-index: 99999 !important; border-radius: 3px !important; background: white !important; border: 1px solid #e5e7eb !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important; min-width: 8rem !important;">
                                <div style="padding: 0.25rem 0;">
                                    <button type="button" 
                                            onclick="deleteDownload({{ $download->id }})"
                                            class="w-full text-left flex items-center transition-colors duration-150"
                                            style="font-family: Poppins, sans-serif !important; font-size: 11px !important; padding: 0.35rem 0.65rem !important; color: #dc2626 !important;"
                                            onmouseover="this.style.backgroundColor='#fef2f2'"
                                            onmouseout="this.style.backgroundColor='transparent'">
                                        <span class="material-symbols-outlined" style="font-size: 14px !important; margin-right: 0.4rem;">delete</span>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endpermission
                    </div>
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$downloads" record-label="files" />
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5);" onclick="closeUploadModal()"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 480px; z-index: 10000;">
        <!-- Modal Header -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0; font-family: Poppins, sans-serif;">Upload File</h3>
            <button type="button" onclick="closeUploadModal()" style="color: #6b7280; background: none; border: none; cursor: pointer;">
                <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="uploadForm" enctype="multipart/form-data" style="padding: 20px;">
            @csrf
            
            <!-- File Name -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">File Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" id="fileName" required
                       style="width: 100%; padding: 0 12px; min-height: 32px; font-size: 11px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box;"
                       placeholder="Enter display name for the file">
            </div>

            <!-- File Upload -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px;">Select File <span style="color: #ef4444;">*</span></label>
                <div id="dropZone" style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 24px; text-align: center; cursor: pointer; transition: all 0.2s;"
                     onclick="document.getElementById('fileInput').click()"
                     ondragover="handleDragOver(event)"
                     ondragleave="handleDragLeave(event)"
                     ondrop="handleDrop(event)">
                    <span class="material-symbols-outlined" style="font-size: 40px; color: #9ca3af;">cloud_upload</span>
                    <p style="font-size: 12px; color: #6b7280; margin: 8px 0 4px 0;">Drag and drop file here or click to browse</p>
                    <p style="font-size: 10px; color: #9ca3af;">Maximum file size: 100MB</p>
                </div>
                <input type="file" name="file" id="fileInput" required style="display: none;" onchange="handleFileSelect(this)">
                
                <!-- Selected File Info -->
                <div id="selectedFile" style="display: none; margin-top: 12px; padding: 12px; background-color: #f3f4f6; border-radius: 6px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background-color: #dbeafe; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 20px; color: #2563eb;" id="fileIcon">insert_drive_file</span>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <p style="font-size: 11px; font-weight: 500; color: #111827; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" id="selectedFileName"></p>
                            <p style="font-size: 10px; color: #6b7280; margin: 2px 0 0 0;" id="selectedFileSize"></p>
                        </div>
                        <button type="button" onclick="clearFile()" style="color: #ef4444; background: none; border: none; cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" style="display: none; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <span style="font-size: 11px; color: #374151;">Uploading...</span>
                    <span style="font-size: 11px; color: #3b82f6;" id="progressPercent">0%</span>
                </div>
                <div style="height: 6px; background-color: #e5e7eb; border-radius: 3px; overflow: hidden;">
                    <div id="progressBar" style="height: 100%; background-color: #3b82f6; border-radius: 3px; transition: width 0.3s; width: 0%;"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 8px; border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 16px;">
                <button type="submit" id="uploadBtn"
                        style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 32px; background-color: #3b82f6; color: white; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">cloud_upload</span>
                    UPLOAD
                </button>
                <button type="button" onclick="closeUploadModal()"
                        style="padding: 0 16px; min-height: 32px; background-color: #f3f4f6; color: #374151; font-size: 11px; font-weight: 500; border-radius: 4px; border: none; cursor: pointer;">
                    CANCEL
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadForm').reset();
    document.getElementById('selectedFile').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('uploadBtn').disabled = false;
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('dropZone').style.borderColor = '#3b82f6';
    document.getElementById('dropZone').style.backgroundColor = '#eff6ff';
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('dropZone').style.borderColor = '#d1d5db';
    document.getElementById('dropZone').style.backgroundColor = 'transparent';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('dropZone').style.borderColor = '#d1d5db';
    document.getElementById('dropZone').style.backgroundColor = 'transparent';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('fileInput').files = files;
        handleFileSelect(document.getElementById('fileInput'));
    }
}

function handleFileSelect(input) {
    const file = input.files[0];
    if (file) {
        document.getElementById('selectedFileName').textContent = file.name;
        document.getElementById('selectedFileSize').textContent = formatFileSize(file.size);
        document.getElementById('selectedFile').style.display = 'block';
        document.getElementById('dropZone').style.display = 'none';
        
        // Auto-fill name if empty
        if (!document.getElementById('fileName').value) {
            const nameWithoutExt = file.name.replace(/\.[^/.]+$/, '');
            document.getElementById('fileName').value = nameWithoutExt;
        }
        
        // Set file icon
        const ext = file.name.split('.').pop().toLowerCase();
        let icon = 'insert_drive_file';
        if (['pdf'].includes(ext)) icon = 'picture_as_pdf';
        else if (['doc', 'docx'].includes(ext)) icon = 'description';
        else if (['xls', 'xlsx'].includes(ext)) icon = 'table_chart';
        else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = 'image';
        else if (['zip', 'rar'].includes(ext)) icon = 'folder_zip';
        document.getElementById('fileIcon').textContent = icon;
    }
}

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('selectedFile').style.display = 'none';
    document.getElementById('dropZone').style.display = 'block';
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' bytes';
}

// Form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size: 14px;">progress_activity</span> UPLOADING...';
    
    const xhr = new XMLHttpRequest();
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Close modal and reload page to see upload progress in table
                closeUploadModal();
                window.location.reload();
            } else {
                alert(response.message || 'Upload failed. Please try again.');
                resetUploadBtn();
            }
        } else {
            try {
                const response = JSON.parse(xhr.responseText);
                alert(response.message || 'Upload failed. Please try again.');
            } catch(e) {
                alert('Upload failed. Please try again.');
            }
            resetUploadBtn();
        }
    });
    
    xhr.addEventListener('error', function() {
        alert('Upload failed. Please try again.');
        resetUploadBtn();
    });
    
    xhr.open('POST', '{{ route("internal.download.store") }}');
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.send(formData);
});

function resetUploadBtn() {
    const uploadBtn = document.getElementById('uploadBtn');
    const progressDiv = document.getElementById('uploadProgress');
    uploadBtn.disabled = false;
    uploadBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">cloud_upload</span> UPLOAD';
    if (progressDiv) progressDiv.style.display = 'none';
}

function deleteDownload(id) {
    window.showDeleteModal('{{ route("internal.download.index") }}/' + id);
}

// Auto-refresh for uploading items
@if($downloads->contains('status', 'uploading'))
setInterval(function() {
    @foreach($downloads->where('status', 'uploading') as $upload)
    fetch('/internal/download/{{ $upload->id }}/progress')
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById('status-{{ $upload->id }}');
            if (data.status === 'completed') {
                statusDiv.innerHTML = '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>';
            } else if (data.status === 'failed') {
                statusDiv.innerHTML = '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failed</span>';
            } else if (data.status === 'uploading') {
                statusDiv.innerHTML = `
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-300" style="width: ${data.progress}%"></div>
                        </div>
                        <span class="text-xs text-blue-600">${data.progress}%</span>
                    </div>
                `;
            }
        });
    @endforeach
}, 2000);
@endif
</script>
@endpush
@endsection
