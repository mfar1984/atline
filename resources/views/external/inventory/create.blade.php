@extends('layouts.app')

@section('title', 'Create Asset')

@section('page-title', 'Create Asset')

@section('content')
<div class="bg-white border border-gray-200" x-data="assetForm()">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Create New Assets</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Add multiple assets to the inventory</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('external.inventory.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="asset-form" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                SAVE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="asset-form" action="{{ route('external.inventory.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6">
            <!-- Common Information Section -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Common Information</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Project <span class="text-red-500">*</span>
                            </label>
                            <select name="project_id" required
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('project_id') border-red-500 @enderror" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="spare" {{ old('status') == 'spare' ? 'selected' : '' }}>Spare</option>
                                <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Location</label>
                            <select name="location_id"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Assigned To</label>
                            <input type="text" name="assigned_to" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('assigned_to') }}"
                                   placeholder="Enter person name">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Department</label>
                            <input type="text" name="department" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   value="{{ old('department') }}"
                                   placeholder="Enter department">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Notes</label>
                            <textarea name="notes" rows="1" 
                                      class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                      style="font-family: Poppins, sans-serif; font-size: 11px; min-height: 32px; resize: none; line-height: 32px; padding-top: 0; padding-bottom: 0;"
                                      placeholder="Enter any notes">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Items Section -->
            <div class="mt-6 border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Asset Items</h3>
                    <button type="button" @click="addItem()" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                        <span class="material-symbols-outlined mr-1" style="font-size: 14px;">add</span>
                        ADD ITEM
                    </button>
                </div>
                <div class="p-4">
                    @error('items')
                        <p class="text-red-500 text-xs mb-3">{{ $message }}</p>
                    @enderror
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 22%;">Serial Number</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 18%;">Category</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 18%;">Brand</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 20%;">Model</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 18%;">Vendor</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 4%;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'items['+index+'][serial_number]'" x-model="item.serial_number"
                                                   class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                                   placeholder="Enter serial number">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select :name="'items['+index+'][category_id]'" x-model="item.category_id"
                                                    class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                                <option value="">Select</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <select :name="'items['+index+'][brand_id]'" x-model="item.brand_id"
                                                    class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                                <option value="">Select</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'items['+index+'][model]'" x-model="item.model"
                                                   class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                                   placeholder="Enter model">
                                        </td>
                                        <td class="px-3 py-2">
                                            <select :name="'items['+index+'][vendor_id]'" x-model="item.vendor_id"
                                                    class="w-full px-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" 
                                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                                <option value="">Select</option>
                                                @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeItem(index)" :disabled="items.length <= 1"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <p class="text-gray-500 text-xs mt-3" style="font-size: 10px;">Add multiple asset items. All items will share the common information above.</p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function assetForm() {
    return {
        items: [{ category_id: '', vendor_id: '', brand_id: '', model: '', serial_number: '' }],
        
        addItem() {
            this.items.push({ 
                category_id: '',
                vendor_id: '',
                brand_id: '', 
                model: '', 
                serial_number: '' 
            });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
