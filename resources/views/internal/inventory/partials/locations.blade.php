<!-- Header with Add Button -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Location List</h3>
    @permission('internal_inventory_locations.create')
    <button type="button" onclick="openLocationModal()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
        LOCATION
    </button>
    @endpermission
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('internal.inventory.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="locations">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search name, description..." 
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
        <button type="button" onclick="window.location.href='{{ route('internal.inventory.index', ['tab' => 'locations']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Locations Table -->
<x-ui.data-table
    :headers="[
        ['label' => 'Name', 'align' => 'text-left'],
        ['label' => 'Description', 'align' => 'text-left'],
        ['label' => 'Assets', 'align' => 'text-center'],
        ['label' => 'Status', 'align' => 'text-center'],
        ['label' => 'Actions', 'align' => 'text-center']
    ]"
    :actions="false"
    empty-message="No locations found."
>
    @forelse($locations as $location)
    <tr class="hover:bg-gray-50 {{ !$location->is_active ? 'opacity-50' : '' }}">
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-xs font-medium text-gray-900">{{ $location->name }}</span>
        </td>
        <td class="px-6 py-4">
            <span class="text-xs text-gray-600">{{ $location->description ?? '-' }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ $location->assets_count }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $location->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                {{ $location->is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
            <x-ui.action-buttons
                :edit-onclick="auth()->user()->hasPermission('internal_inventory_locations.update') ? 'editLocation(' . $location->id . ', ' . json_encode(['name' => $location->name, 'description' => $location->description]) . ')' : null"
                :delete-onclick="auth()->user()->hasPermission('internal_inventory_locations.delete') ? 'deleteLocation(' . $location->id . ')' : null"
                :more-actions="auth()->user()->hasPermission('internal_inventory_locations.update') ? [
                    ['label' => $location->is_active ? 'Deactivate' : 'Activate', 'icon' => $location->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleStatus(\'locations\', ' . $location->id . ', this)']
                ] : []"
            />
        </td>
    </tr>
    @empty
    @endforelse
</x-ui.data-table>

<!-- Pagination -->
<div class="mt-4">
    <x-ui.custom-pagination :paginator="$locations" record-label="locations" />
</div>

<!-- Location Modal -->
<div id="locationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 480px !important; overflow: hidden !important; z-index: 10000;">
        <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #10b981 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">location_on</span>
                </div>
                <h3 id="locationModalTitle" style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Add Location</h3>
            </div>
            <button type="button" onclick="closeLocationModal()" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
            </button>
        </div>
        <form id="locationForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="locationMethod" value="POST">
            <div style="padding: 20px !important;">
                <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            Name <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <input type="text" name="name" id="location_name" required
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="Enter location name"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Description</label>
                        <textarea name="description" id="location_description" rows="3"
                                  style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important; resize: vertical !important;"
                                  placeholder="Enter description (optional)"
                                  onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; gap: 10px !important; background-color: #f9fafb !important;">
                <button type="button" onclick="closeLocationModal()"
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

@push('scripts')
<script>
function openLocationModal() {
    document.getElementById('locationModalTitle').textContent = 'Add Location';
    document.getElementById('locationForm').action = '{{ route("internal.inventory.locations.store") }}';
    document.getElementById('locationMethod').value = 'POST';
    document.getElementById('locationForm').reset();
    document.getElementById('locationModal').style.display = 'block';
}

function editLocation(id, data) {
    document.getElementById('locationModalTitle').textContent = 'Edit Location';
    document.getElementById('locationForm').action = '/internal/inventory/locations/' + id;
    document.getElementById('locationMethod').value = 'PUT';
    document.getElementById('location_name').value = data.name || '';
    document.getElementById('location_description').value = data.description || '';
    document.getElementById('locationModal').style.display = 'block';
}

function closeLocationModal() {
    document.getElementById('locationModal').style.display = 'none';
}

function deleteLocation(id) {
    window.showDeleteModal('/internal/inventory/locations/' + id);
}

document.getElementById('locationModal').addEventListener('click', function(e) {
    if (e.target === this) closeLocationModal();
});
</script>
@endpush
