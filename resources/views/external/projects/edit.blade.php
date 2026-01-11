@extends('layouts.app')

@section('title', 'Edit Project')

@section('page-title', 'Edit Project')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Edit Project</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Update project information</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.projects.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="project-form" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                UPDATE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="project-form" action="{{ route('external.projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Project Information -->
                <div class="border border-gray-200 rounded">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project Information</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Project Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" required 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('name', $project->name) }}"
                                   placeholder="Enter project name">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Organization</label>
                            <select name="organization_id"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Organization</option>
                                @foreach($organizations as $organization)
                                    <option value="{{ $organization->id }}" {{ old('organization_id', $project->organization_id) == $organization->id ? 'selected' : '' }}>{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Description</label>
                            <textarea name="description" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                      style="font-family: Poppins, sans-serif; font-size: 11px;"
                                      placeholder="Enter project description">{{ old('description', $project->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Project Details -->
                <div class="border border-gray-200 rounded">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Project Details</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Project Value (RM)</label>
                            <input type="number" name="project_value" step="0.01" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('project_value', $project->project_value) }}"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Start Date</label>
                            <input type="date" name="start_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">End Date</label>
                            <input type="date" name="end_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Status</label>
                            <select name="status" 
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Users Section -->
            @if(isset($clientUsers) && $clientUsers->count() > 0)
            <div class="mt-6 border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Assign Client Users</h3>
                    <p class="text-xs text-gray-500 mt-0.5" style="font-size: 10px;">Select users who can access this project</p>
                </div>
                <div class="p-4">
                    @php
                        $assignedUserIds = $project->users->pluck('id')->toArray();
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($clientUsers as $user)
                        <label class="flex items-center p-3 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer {{ in_array($user->id, $assignedUserIds) ? 'bg-blue-50 border-blue-200' : '' }}">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ in_array($user->id, old('user_ids', $assignedUserIds)) ? 'checked' : '' }}>
                            <div class="ml-3">
                                <span class="text-xs font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $user->name }}</span>
                                <span class="block text-xs text-gray-500" style="font-size: 10px;">{{ $user->email }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Procurement & Warranty -->
            <div class="mt-6 border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Procurement & Warranty</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Purchase Date</label>
                            <input type="date" name="purchase_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('purchase_date', $project->purchase_date?->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">PO/Invoice Number</label>
                            <input type="text" name="po_number" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('po_number', $project->po_number) }}"
                                   placeholder="Enter PO number">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Period</label>
                            <input type="text" name="warranty_period" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('warranty_period', $project->warranty_period) }}"
                                   placeholder="e.g., 1 Year, 3 Years">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Expiry</label>
                            <input type="date" name="warranty_expiry" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('warranty_expiry', $project->warranty_expiry?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Attachments Section -->
            <div class="mt-6 border border-gray-200 rounded" x-data="fileAttachments()">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">File Attachments</h3>
                    <button type="button" @click="addFile()" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                        <span class="material-symbols-outlined mr-1" style="font-size: 14px;">add</span>
                        ADD FILE
                    </button>
                </div>
                <div class="p-4">
                    <!-- Existing Attachments -->
                    @if($project->attachments->count() > 0)
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2 font-medium" style="font-size: 11px; font-family: Poppins, sans-serif;">Current Files ({{ $project->attachments->count() }})</label>
                        <div class="overflow-x-auto border border-gray-200 rounded">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 35%;">File Name/Type</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Size</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 20%;">Date</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 25%;">Actions</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($project->attachments as $attachment)
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
                                                    <p class="text-xs font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $attachment->file_name }}</p>
                                                    <p class="text-xs text-gray-500" style="font-size: 10px;">{{ strtoupper($ext) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-xs text-gray-600" style="font-size: 11px;">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="text-xs text-gray-600" style="font-size: 11px;">{{ $attachment->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded hover:bg-blue-100 transition" style="min-height: 28px; font-size: 10px;">
                                                    <span class="material-symbols-outlined mr-1" style="font-size: 14px;">download</span>
                                                    Download
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button type="button" onclick="deleteAttachment({{ $attachment->id }})" class="inline-flex items-center justify-center w-8 h-8 bg-red-500 text-white rounded hover:bg-red-600 transition">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Add New Files -->
                    <div class="{{ $project->attachments->count() > 0 ? 'border-t border-gray-200 pt-4' : '' }}">
                        <label class="block text-gray-700 mb-2 font-medium" style="font-size: 11px; font-family: Poppins, sans-serif;">Add New Files</label>
                        <template x-if="files.length > 0">
                            <div class="overflow-x-auto border border-gray-200 rounded">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="bg-gray-50 border-b border-gray-200">
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 30%;">File Name/Type</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Size</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Date</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 35%;">Select File</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(file, index) in files" :key="index">
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <input type="text" :name="'attachment_names[]'" 
                                                           class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                           style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                                           placeholder="e.g. UAT Document, Meeting Minutes">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <span class="text-xs text-gray-500" style="font-size: 11px;" x-text="file.size || '-'"></span>
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <span class="text-xs text-gray-500" style="font-size: 11px;" x-text="file.date || new Date().toLocaleDateString()"></span>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="file" :name="'attachment_files[]'" 
                                                           @change="updateFileInfo($event, index)"
                                                           class="w-full text-xs" 
                                                           style="font-family: Poppins, sans-serif; font-size: 11px;"
                                                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    <button type="button" @click="removeFile(index)" class="inline-flex items-center justify-center w-8 h-8 bg-red-500 text-white rounded hover:bg-red-600 transition">
                                                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        
                        <div x-show="files.length === 0" class="text-center py-8 text-gray-400 border border-dashed border-gray-300 rounded">
                            <span class="material-symbols-outlined" style="font-size: 36px;">upload_file</span>
                            <p class="mt-2 text-xs" style="font-size: 11px;">Click "ADD FILE" button above to upload new files.</p>
                        </div>
                        
                        <p class="text-gray-500 text-xs mt-3" style="font-size: 10px;">Accepted formats: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX (Max 15MB per file)</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function fileAttachments() {
    return {
        files: [],
        addFile() {
            this.files.push({ 
                id: Date.now(),
                size: null,
                date: new Date().toLocaleDateString()
            });
        },
        removeFile(index) {
            this.files.splice(index, 1);
        },
        updateFileInfo(event, index) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 15 * 1024 * 1024) {
                    alert('File size exceeds 15MB limit');
                    event.target.value = '';
                    return;
                }
                
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                if (file.size === 0) {
                    this.files[index].size = '0 Byte';
                } else {
                    const i = parseInt(Math.floor(Math.log(file.size) / Math.log(1024)));
                    this.files[index].size = Math.round(file.size / Math.pow(1024, i), 2) + ' ' + sizes[i];
                }
            }
        }
    }
}

function deleteAttachment(id) {
    if (confirm('Are you sure you want to delete this attachment?')) {
        fetch(`/attachments/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Failed to delete attachment');
            }
        });
    }
}
</script>
@endsection
