<div x-data="{ showForm: false, editId: null, editData: {}, fields: [] }"
     x-on:edit-category.window="editId = $event.detail.id; editData = $event.detail.data; fields = $event.detail.fields || []; showForm = true">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Category List</h3>
        @permission('external_settings_category.create')
        <button @click="showForm = true; editId = null; editData = {}; fields = []" 
                class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
            CATEGORY
        </button>
        @endpermission
    </div>

    <!-- Search/Filter Form -->
    <div class="mb-4">
        <form action="{{ route('external.settings.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="categories">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search name, code..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('external.settings.index', ['tab' => 'categories']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    <!-- Add/Edit Form Modal -->
    <div x-show="showForm" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 480px !important; margin: 16px !important; overflow: hidden !important;" @click.away="showForm = false">
            <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
                <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #8b5cf6 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">category</span>
                    </div>
                    <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;" x-text="editId ? 'Edit Category' : 'Add New Category'"></h3>
                </div>
                <button type="button" @click="showForm = false" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
                </button>
            </div>
            <form :action="editId ? '/external/settings/categories/' + editId : '{{ route('external.settings.categories.store') }}'" method="POST">
                @csrf
                <template x-if="editId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div style="padding: 20px !important; max-height: 60vh !important; overflow-y: auto !important;">
                    <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Name <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="text" name="name" required x-model="editData.name"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Enter category name"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Code <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="text" name="code" required x-model="editData.code" maxlength="10"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important; text-transform: uppercase !important;"
                                       placeholder="e.g. LAP, SRV"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                        
                        <!-- Dynamic Fields Configuration -->
                        <div style="border-top: 1px solid #e5e7eb !important; padding-top: 16px !important;">
                            <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 12px !important;">
                                <label style="font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; font-family: Poppins, sans-serif !important;">Custom Fields</label>
                                <button type="button" @click="fields.push({ name: '', type: 'text', required: false })"
                                        style="font-size: 11px !important; color: #3b82f6 !important; background: none !important; border: none !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 4px !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important;">add</span>
                                    Add Field
                                </button>
                            </div>
                            
                            <template x-for="(field, index) in fields" :key="index">
                                <div style="display: flex !important; align-items: center !important; gap: 8px !important; margin-bottom: 8px !important;">
                                    <input type="text" :name="'fields_config[' + index + '][name]'" x-model="field.name"
                                           style="flex: 1 !important; padding: 8px 10px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 11px !important; color: #1f2937 !important; outline: none !important;"
                                           placeholder="Field name">
                                    <select :name="'fields_config[' + index + '][type]'" x-model="field.type"
                                            style="padding: 8px 10px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 11px !important; color: #1f2937 !important; width: 90px !important; outline: none !important;">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="textarea">Textarea</option>
                                    </select>
                                    <label style="display: flex !important; align-items: center !important; gap: 4px !important; font-size: 10px !important; color: #6b7280 !important; cursor: pointer !important; padding: 0 8px !important;">
                                        <input type="checkbox" :name="'fields_config[' + index + '][required]'" x-model="field.required" 
                                               style="width: 14px !important; height: 14px !important; accent-color: #3b82f6 !important;">
                                        Req
                                    </label>
                                    <button type="button" @click="fields.splice(index, 1)" 
                                            style="width: 28px !important; height: 28px !important; border: none !important; background-color: #fef2f2 !important; border-radius: 6px !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                                        <span class="material-symbols-outlined" style="font-size: 16px !important; color: #ef4444 !important;">close</span>
                                    </button>
                                </div>
                            </template>
                            
                            <p x-show="fields.length === 0" style="font-size: 11px !important; color: #9ca3af !important; font-style: italic !important; font-family: Poppins, sans-serif !important;">No custom fields defined</p>
                        </div>
                    </div>
                </div>
                <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; gap: 10px !important; background-color: #f9fafb !important;">
                    <button type="button" @click="showForm = false" 
                            style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                            onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #3b82f6 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 6px !important;"
                            onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'">
                        <span class="material-symbols-outlined" style="font-size: 16px !important;">save</span>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Code</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Fields</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Assets</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Updated</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                <tr class="{{ !$category->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 text-xs text-gray-900" style="font-family: Poppins, sans-serif;">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $category->code }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ count($category->fields_config ?? []) }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $category->assets_count }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $category->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $category->updated_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="status-badge inline-flex px-2 py-1 text-xs font-medium rounded {{ $category->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-ui.action-buttons
                            :edit-onclick="auth()->user()->hasPermission('external_settings_category.update') ? 'editCategory(' . $category->id . ', ' . json_encode(['name' => $category->name, 'code' => $category->code]) . ', ' . json_encode($category->fields_config ?? []) . ')' : null"
                            :delete-onclick="auth()->user()->hasPermission('external_settings_category.delete') ? 'deleteCategory(' . $category->id . ')' : null"
                            :more-actions="auth()->user()->hasPermission('external_settings_category.update') ? [
                                ['label' => $category->is_active ? 'Deactivate' : 'Activate', 'icon' => $category->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleStatus(\'categories\', ' . $category->id . ', this)']
                            ] : []"
                        />
                        <form id="delete-category-{{ $category->id }}" action="{{ route('external.settings.categories.destroy', $category) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 text-xs">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <x-ui.custom-pagination :paginator="$categories" record-label="categories" />
    </div>
</div>
