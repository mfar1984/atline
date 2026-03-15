<!-- Header with Add Button -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Asset List</h3>
    @permission('internal_inventory_assets.create')
    <button type="button" onclick="openAssetModal()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
        ASSET
    </button>
    @endpermission
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('internal.inventory.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="assets">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search asset tag, name, serial number..." 
                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
        <select name="category" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Status</option>
            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
            <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
            SEARCH
        </button>
        <button type="button" onclick="window.location.href='{{ route('internal.inventory.index', ['tab' => 'assets']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Assets Table -->
<x-ui.data-table
    :headers="[
        ['label' => 'Asset', 'align' => 'text-left'],
        ['label' => 'Category / Brand', 'align' => 'text-left'],
        ['label' => 'Location', 'align' => 'text-left'],
        ['label' => 'Status / Assigned', 'align' => 'text-left'],
        ['label' => 'Actions', 'align' => 'text-center']
    ]"
    :actions="false"
    empty-message="No assets found."
>
    @forelse($assets as $asset)
    <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-xs font-medium text-blue-600">{{ $asset->asset_tag }}</div>
            <div class="text-xs text-gray-900">{{ $asset->name }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                {{ $asset->category->name ?? '-' }}
            </span>
            <div class="text-xs text-gray-600 mt-1">{{ $asset->brand->name ?? '-' }}{{ $asset->model ? ' / ' . $asset->model : '' }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-xs text-gray-600">{{ $asset->location->name ?? '-' }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            @php
                $statusColors = [
                    'available' => 'bg-green-100 text-green-800',
                    'checked_out' => 'bg-yellow-100 text-yellow-800',
                    'maintenance' => 'bg-orange-100 text-orange-800',
                    'disposed' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$asset->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ ucfirst(str_replace('_', ' ', $asset->status)) }}
            </span>
            @if($asset->currentMovement)
                <div class="text-xs text-gray-600 mt-1">{{ $asset->currentMovement->employee->full_name ?? '-' }}</div>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
            <x-ui.action-buttons
                :edit-onclick="auth()->user()->hasPermission('internal_inventory_assets.update') ? 'editAsset(' . $asset->id . ', ' . json_encode($asset) . ')' : null"
                :show-onclick="'viewAsset(' . $asset->id . ', ' . json_encode($asset) . ')'"
                :delete-onclick="auth()->user()->hasPermission('internal_inventory_assets.delete') ? 'deleteAsset(' . $asset->id . ')' : null"
            />
        </td>
    </tr>
    @empty
    @endforelse
</x-ui.data-table>

<div class="mt-4">
    <x-ui.custom-pagination :paginator="$assets" record-label="assets" />
</div>

<!-- Asset Modal -->
<div id="assetModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 600px !important; max-height: 90vh !important; overflow: hidden !important; z-index: 10000;">
        <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #3b82f6 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">inventory_2</span>
                </div>
                <h3 id="assetModalTitle" style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Add Asset</h3>
            </div>
            <button type="button" onclick="closeAssetModal()" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
            </button>
        </div>
        <form id="assetForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="assetMethod" value="POST">
            <div style="padding: 20px !important; max-height: 60vh !important; overflow-y: auto !important;">
                <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important;">
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            Asset Tag <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <input type="text" name="asset_tag" id="asset_tag" required
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="e.g. AST-001"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            Name <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <input type="text" name="name" id="asset_name" required
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="Enter asset name"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Category</label>
                        <select name="category_id" id="asset_category_id"
                                style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Brand</label>
                        <select name="brand_id" id="asset_brand_id"
                                style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Model</label>
                        <input type="text" name="model" id="asset_model"
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="Enter model"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Serial Number</label>
                        <input type="text" name="serial_number" id="asset_serial_number"
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="Enter serial number"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Location</label>
                        <select name="location_id" id="asset_location_id"
                                style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="">Select Location</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                            Condition <span style="color: #ef4444 !important;">*</span>
                        </label>
                        <select name="condition" id="asset_condition" required
                                style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            <option value="excellent">Excellent</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Purchase Price (RM)</label>
                        <input type="number" name="purchase_price" id="asset_purchase_price" step="0.01"
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               placeholder="0.00"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Purchase Date</label>
                        <input type="date" name="purchase_date" id="asset_purchase_date"
                               style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div style="grid-column: span 2 !important;">
                        <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Notes</label>
                        <textarea name="notes" id="asset_notes" rows="3"
                                  style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important; resize: vertical !important;"
                                  placeholder="Enter notes (optional)"
                                  onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; gap: 10px !important; background-color: #f9fafb !important;">
                <button type="button" onclick="closeAssetModal()"
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

<!-- Asset View Modal -->
<div id="assetViewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 550px !important; max-height: 90vh !important; overflow: hidden !important; z-index: 10000;">
        <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #6366f1 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">visibility</span>
                </div>
                <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Asset Details</h3>
            </div>
            <button type="button" onclick="closeAssetViewModal()" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
            </button>
        </div>
        <div style="padding: 20px !important; max-height: 60vh !important; overflow-y: auto !important;">
            <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 16px !important;">
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Asset Tag</label>
                    <p id="view_asset_tag" style="font-size: 12px !important; color: #111827 !important; font-weight: 500 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Name</label>
                    <p id="view_asset_name" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Category</label>
                    <p id="view_asset_category" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Brand</label>
                    <p id="view_asset_brand" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Model</label>
                    <p id="view_asset_model" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Serial Number</label>
                    <p id="view_asset_serial" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Location</label>
                    <p id="view_asset_location" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Condition</label>
                    <p id="view_asset_condition" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Status</label>
                    <p id="view_asset_status" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Purchase Price</label>
                    <p id="view_asset_price" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Purchase Date</label>
                    <p id="view_asset_purchase_date" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div>
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Created</label>
                    <p id="view_asset_created" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
                <div style="grid-column: span 2 !important;">
                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #6b7280 !important; margin-bottom: 4px !important; text-transform: uppercase !important; font-family: Poppins, sans-serif !important;">Notes</label>
                    <p id="view_asset_notes" style="font-size: 12px !important; color: #111827 !important; margin: 0 !important;">-</p>
                </div>
            </div>
        </div>
        <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; background-color: #f9fafb !important;">
            <button type="button" onclick="closeAssetViewModal()"
                    style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                    onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAssetModal() {
    document.getElementById('assetModalTitle').textContent = 'Add Asset';
    document.getElementById('assetForm').action = '{{ route("internal.inventory.assets.store") }}';
    document.getElementById('assetMethod').value = 'POST';
    document.getElementById('assetForm').reset();
    document.getElementById('assetModal').style.display = 'block';
}

function editAsset(id, data) {
    document.getElementById('assetModalTitle').textContent = 'Edit Asset';
    document.getElementById('assetForm').action = '/internal/inventory/assets/' + id;
    document.getElementById('assetMethod').value = 'PUT';
    
    document.getElementById('asset_tag').value = data.asset_tag || '';
    document.getElementById('asset_name').value = data.name || '';
    document.getElementById('asset_category_id').value = data.category_id || '';
    document.getElementById('asset_brand_id').value = data.brand_id || '';
    document.getElementById('asset_model').value = data.model || '';
    document.getElementById('asset_serial_number').value = data.serial_number || '';
    document.getElementById('asset_location_id').value = data.location_id || '';
    document.getElementById('asset_condition').value = data.condition || 'good';
    document.getElementById('asset_purchase_price').value = data.purchase_price || '';
    document.getElementById('asset_purchase_date').value = data.purchase_date ? data.purchase_date.split('T')[0] : '';
    document.getElementById('asset_notes').value = data.notes || '';
    
    document.getElementById('assetModal').style.display = 'block';
}

function viewAsset(id, data) {
    document.getElementById('view_asset_tag').textContent = data.asset_tag || '-';
    document.getElementById('view_asset_name').textContent = data.name || '-';
    document.getElementById('view_asset_category').textContent = data.category ? data.category.name : '-';
    document.getElementById('view_asset_brand').textContent = data.brand ? data.brand.name : '-';
    document.getElementById('view_asset_model').textContent = data.model || '-';
    document.getElementById('view_asset_serial').textContent = data.serial_number || '-';
    document.getElementById('view_asset_location').textContent = data.location ? data.location.name : '-';
    document.getElementById('view_asset_condition').textContent = data.condition ? data.condition.charAt(0).toUpperCase() + data.condition.slice(1) : '-';
    document.getElementById('view_asset_status').textContent = data.status ? data.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
    document.getElementById('view_asset_price').textContent = data.purchase_price ? 'RM ' + parseFloat(data.purchase_price).toFixed(2) : '-';
    document.getElementById('view_asset_purchase_date').textContent = data.purchase_date ? new Date(data.purchase_date).toLocaleDateString('en-GB') : '-';
    document.getElementById('view_asset_created').textContent = data.created_at ? new Date(data.created_at).toLocaleDateString('en-GB') : '-';
    document.getElementById('view_asset_notes').textContent = data.notes || '-';
    
    document.getElementById('assetViewModal').style.display = 'block';
}

function closeAssetModal() {
    document.getElementById('assetModal').style.display = 'none';
}

function closeAssetViewModal() {
    document.getElementById('assetViewModal').style.display = 'none';
}

function deleteAsset(id) {
    window.showDeleteModal('/internal/inventory/assets/' + id);
}

document.getElementById('assetModal').addEventListener('click', function(e) {
    if (e.target === this) closeAssetModal();
});

document.getElementById('assetViewModal').addEventListener('click', function(e) {
    if (e.target === this) closeAssetViewModal();
});
</script>
@endpush
