@extends('layouts.app')

@section('title', 'Edit Asset')

@section('page-title', 'Edit Asset')

@section('content')
<div class="bg-white border border-gray-200" x-data="assetForm()">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Edit Asset</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Update asset information - {{ $asset->asset_tag }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.inventory.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="asset-form" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                UPDATE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="asset-form" action="{{ route('external.inventory.update', $asset) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="border border-gray-200 rounded">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Basic Information</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Project <span class="text-red-500">*</span>
                            </label>
                            <select name="project_id" required
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('project_id') border-red-500 @enderror" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id', $asset->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" required x-model="categoryId" @change="loadDynamicFields()"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('category_id') border-red-500 @enderror" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Asset Tag/ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="asset_tag" required
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('asset_tag') border-red-500 @enderror" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('asset_tag', $asset->asset_tag) }}"
                                   placeholder="e.g., PC-2026-0001">
                            @error('asset_tag')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Brand</label>
                                <select name="brand_id"
                                        class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                        style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $asset->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Model</label>
                                <input type="text" name="model" 
                                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                       style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                       value="{{ old('model', $asset->model) }}"
                                       placeholder="Enter model">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Serial Number</label>
                            <input type="text" name="serial_number" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('serial_number', $asset->serial_number) }}"
                                   placeholder="Enter serial number">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="active" {{ old('status', $asset->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="spare" {{ old('status', $asset->status) == 'spare' ? 'selected' : '' }}>Spare</option>
                                <option value="damaged" {{ old('status', $asset->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="maintenance" {{ old('status', $asset->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="disposed" {{ old('status', $asset->status) == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location & Assignment -->
                <div class="border border-gray-200 rounded">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Location & Assignment</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Location</label>
                            <select name="location_id"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id', $asset->location_id) == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Vendor</label>
                            <select name="vendor_id"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id', $asset->vendor_id) == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Assigned To</label>
                            <input type="text" name="assigned_to" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('assigned_to', $asset->assigned_to) }}"
                                   placeholder="Enter person name">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Department</label>
                            <input type="text" name="department" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('department', $asset->department) }}"
                                   placeholder="Enter department">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Notes</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                      style="font-family: Poppins, sans-serif; font-size: 11px;"
                                      placeholder="Enter any notes">{{ old('notes', $asset->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Technical Specifications -->
            <div class="mt-6 border border-gray-200 rounded" x-show="dynamicFields.length > 0" x-cloak>
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Technical Specifications</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="field in dynamicFields" :key="field.name">
                            <div>
                                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                    <span x-text="field.label"></span>
                                    <span x-show="field.required" class="text-red-500">*</span>
                                </label>
                                <template x-if="field.type === 'text'">
                                    <input type="text" :name="'specs[' + field.name + ']'" :required="field.required"
                                           :value="existingSpecs[field.name] || ''"
                                           class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                           style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                </template>
                                <template x-if="field.type === 'number'">
                                    <input type="number" :name="'specs[' + field.name + ']'" :required="field.required"
                                           :value="existingSpecs[field.name] || ''"
                                           class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                           style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                </template>
                                <template x-if="field.type === 'date'">
                                    <input type="date" :name="'specs[' + field.name + ']'" :required="field.required"
                                           :value="existingSpecs[field.name] || ''"
                                           class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                           style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                </template>
                                <template x-if="field.type === 'select' && Array.isArray(field.options)">
                                    <select :name="'specs[' + field.name + ']'" :required="field.required"
                                            class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                            style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                        <option value="">Select...</option>
                                        <template x-for="opt in field.options" :key="opt">
                                            <option :value="opt" :selected="existingSpecs[field.name] === opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                        </template>
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
                    @if($asset->attachments->count() > 0)
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2 font-medium" style="font-size: 11px; font-family: Poppins, sans-serif;">Current Files ({{ $asset->attachments->count() }})</label>
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
                    <div class="{{ $asset->attachments->count() > 0 ? 'border-t border-gray-200 pt-4' : '' }}">
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
                                                           placeholder="e.g. Invoice, Warranty Card">
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
                                                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
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
                        
                        <p class="text-gray-500 text-xs mt-3" style="font-size: 10px;">Accepted formats: JPG, PNG, PDF, DOC, DOCX (Max 10MB per file)</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function assetForm() {
    return {
        categoryId: '{{ old('category_id', $asset->category_id) }}',
        dynamicFields: [],
        existingSpecs: @json($asset->specs ?? []),
        
        async loadDynamicFields() {
            if (!this.categoryId) {
                this.dynamicFields = [];
                return;
            }
            
            try {
                const response = await fetch(`/external/inventory/category/${this.categoryId}/fields`);
                const data = await response.json();
                this.dynamicFields = data.fields || [];
            } catch (error) {
                console.error('Error loading dynamic fields:', error);
                this.dynamicFields = [];
            }
        },
        
        init() {
            if (this.categoryId) {
                this.loadDynamicFields();
            }
        }
    }
}

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
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size exceeds 10MB limit');
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
