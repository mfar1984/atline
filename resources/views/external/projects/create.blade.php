@extends('layouts.app')

@section('title', 'Create Project')

@section('page-title', 'Create Project')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Create New Project</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Add a new project to the system</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.projects.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="project-form" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                SAVE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="project-form" action="{{ route('external.projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
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
                                   value="{{ old('name') }}"
                                   placeholder="Enter project name">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Client</label>
                            <select name="client_id"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Description</label>
                            <textarea name="description" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                      style="font-family: Poppins, sans-serif; font-size: 11px;"
                                      placeholder="Enter project description">{{ old('description') }}</textarea>
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
                                   value="{{ old('project_value') }}"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Start Date</label>
                            <input type="date" name="start_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('start_date') }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">End Date</label>
                            <input type="date" name="end_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('end_date') }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Status</label>
                            <select name="status" 
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Purchase Date</label>
                            <input type="date" name="purchase_date" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('purchase_date') }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">PO/Invoice Number</label>
                            <input type="text" name="po_number" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('po_number') }}"
                                   placeholder="Enter PO number">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Period</label>
                            <input type="text" name="warranty_period" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('warranty_period') }}"
                                   placeholder="e.g., 1 Year, 3 Years">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Warranty Expiry</label>
                            <input type="date" name="warranty_expiry" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('warranty_expiry') }}">
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
                    <template x-if="files.length > 0">
                        <div class="overflow-x-auto">
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
                                                       placeholder="e.g. Kick Off Meeting, Jadual Kerja">
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
                    
                    <div x-show="files.length === 0" class="text-center py-12 text-gray-400 border border-dashed border-gray-300 rounded">
                        <span class="material-symbols-outlined" style="font-size: 48px;">upload_file</span>
                        <p class="mt-2 text-xs" style="font-size: 11px;">No files added yet. Click "ADD FILE" to upload.</p>
                    </div>
                    
                    <p class="text-gray-500 text-xs mt-3" style="font-size: 10px;">Accepted formats: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX (Max 15MB per file)</p>
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
                // Check file size (15MB = 15 * 1024 * 1024 bytes)
                if (file.size > 15 * 1024 * 1024) {
                    alert('File size exceeds 15MB limit');
                    event.target.value = '';
                    return;
                }
                
                // Format file size
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
</script>
@endsection
