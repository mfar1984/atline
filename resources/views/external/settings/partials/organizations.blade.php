<!-- Leaflet + OpenStreetMap: no API key, no billing account, no per-load quota.
     A Google Maps key would have to be provisioned, restricted and rotated for a
     field that is entered a handful of times a year. -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div x-data="{ showForm: false, editId: null, editData: {} }" 
     x-on:edit-organization.window="editId = $event.detail.id; editData = $event.detail.data; showForm = true; $nextTick(() => orgMap.open(editData.latitude, editData.longitude))">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Organization List</h3>
        @permission('external_settings_organization.create')
        <button @click="showForm = true; editId = null; editData = { country: 'Malaysia' }; $nextTick(() => orgMap.open(null, null))" 
                class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
            ORGANIZATION
        </button>
        @endpermission
    </div>

    <!-- Search/Filter Form -->
    <div class="mb-4">
        <form action="{{ route('external.settings.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="organizations">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search name, phone, state..." 
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
            <button type="button" onclick="window.location.href='{{ route('external.settings.index', ['tab' => 'organizations']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    <!-- Add/Edit Form Modal -->
    <div x-show="showForm" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 520px !important; margin: 16px !important; overflow: hidden !important;" @click.away="showForm = false">
            <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
                <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #8b5cf6 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">corporate_fare</span>
                    </div>
                    <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;" x-text="editId ? 'Edit Organization' : 'Add New Organization'"></h3>
                </div>
                <button type="button" @click="showForm = false" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
                </button>
            </div>

            <form :action="editId ? '{{ url('external/settings/organizations') }}/' + editId : '{{ route('external.settings.organizations.store') }}'" method="POST">
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
                                       placeholder="e.g. MRSM Gerik Perak"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Type</label>
                                <select name="organization_type" x-model="editData.organization_type"
                                        style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                    <option value="">Select Type</option>
                                    <option value="government">Government</option>
                                    <option value="ngo">NGO</option>
                                    <option value="company">Company</option>
                                    <option value="education">Education</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Address 1</label>
                            <input type="text" name="address_1" x-model="editData.address_1"
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   placeholder="Enter address line 1"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Address 2</label>
                            <input type="text" name="address_2" x-model="editData.address_2"
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   placeholder="Enter address line 2"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Postcode</label>
                                <input type="text" name="postcode" x-model="editData.postcode" maxlength="10"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="e.g. 50000"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">District</label>
                                <input type="text" name="district" x-model="editData.district"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Enter district"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">State</label>
                                <select name="state" x-model="editData.state"
                                        style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                    <option value="">Select State</option>
                                    <option value="Johor">Johor</option>
                                    <option value="Kedah">Kedah</option>
                                    <option value="Kelantan">Kelantan</option>
                                    <option value="Melaka">Melaka</option>
                                    <option value="Negeri Sembilan">Negeri Sembilan</option>
                                    <option value="Pahang">Pahang</option>
                                    <option value="Perak">Perak</option>
                                    <option value="Perlis">Perlis</option>
                                    <option value="Pulau Pinang">Pulau Pinang</option>
                                    <option value="Sabah">Sabah</option>
                                    <option value="Sarawak">Sarawak</option>
                                    <option value="Selangor">Selangor</option>
                                    <option value="Terengganu">Terengganu</option>
                                    <option value="W.P. Kuala Lumpur">W.P. Kuala Lumpur</option>
                                    <option value="W.P. Labuan">W.P. Labuan</option>
                                    <option value="W.P. Putrajaya">W.P. Putrajaya</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Country</label>
                                <input type="text" name="country" x-model="editData.country"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Malaysia"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Website</label>
                                <input type="url" name="website" x-model="editData.website"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="https://example.com"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Phone Number</label>
                                <input type="text" name="phone" x-model="editData.phone"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Enter phone number"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                        <!-- Site location: the geofence anchor for the field app -->
                        <div style="border: 1px solid #e5e7eb !important; border-radius: 8px !important; padding: 14px !important; background-color: #f9fafb !important;">
                            <div style="display: flex !important; align-items: flex-start !important; justify-content: space-between !important; gap: 10px !important; margin-bottom: 10px !important;">
                                <div>
                                    <div style="display: flex !important; align-items: center !important; gap: 6px !important;">
                                        <span class="material-symbols-outlined" style="font-size: 16px !important; color: #3b82f6 !important;">my_location</span>
                                        <span style="font-size: 12px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important;">Site Location</span>
                                    </div>
                                    <p style="font-size: 10px !important; color: #6b7280 !important; margin: 4px 0 0 0 !important; font-family: Poppins, sans-serif !important; line-height: 1.5 !important;">
                                        Used by the field app to check a technician was on site when the customer signed. Search, click the map, or drag the pin.
                                    </p>
                                </div>
                                <button type="button" onclick="orgMap.locateMe()"
                                        style="flex-shrink: 0 !important; padding: 6px 10px !important; font-size: 10px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 4px !important;"
                                        title="Use this device's current position">
                                    <span class="material-symbols-outlined" style="font-size: 13px !important;">gps_fixed</span>
                                    HERE
                                </button>
                            </div>

                            <div style="display: flex !important; gap: 8px !important; margin-bottom: 10px !important;">
                                <input type="text" id="org_map_search" placeholder="Search a place, e.g. MRSM Gerik Perak"
                                       onkeydown="if(event.key==='Enter'){event.preventDefault();orgMap.search();}"
                                       style="flex: 1 !important; padding: 8px 10px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 11px !important; color: #1f2937 !important; outline: none !important;"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                <button type="button" onclick="orgMap.search()"
                                        style="padding: 8px 12px !important; font-size: 11px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #3b82f6 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;">
                                    <span class="material-symbols-outlined" style="font-size: 14px !important; vertical-align: middle !important;">search</span>
                                </button>
                            </div>

                            <div id="org_map" style="height: 220px !important; width: 100% !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; background-color: #e5e7eb !important; z-index: 1 !important;"></div>

                            <div style="display: grid !important; grid-template-columns: 1fr 1fr auto !important; gap: 8px !important; align-items: end !important; margin-top: 10px !important;">
                                <div>
                                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 4px !important; font-family: Poppins, sans-serif !important;">Latitude</label>
                                    <input type="text" name="latitude" id="org_latitude" inputmode="decimal"
                                           oninput="orgMap.fromInputs()"
                                           style="width: 100% !important; padding: 8px 10px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: 'Roboto Mono', monospace !important; font-size: 11px !important; color: #1f2937 !important; outline: none !important;"
                                           placeholder="5.4213000"
                                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                </div>
                                <div>
                                    <label style="display: block !important; font-size: 10px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 4px !important; font-family: Poppins, sans-serif !important;">Longitude</label>
                                    <input type="text" name="longitude" id="org_longitude" inputmode="decimal"
                                           oninput="orgMap.fromInputs()"
                                           style="width: 100% !important; padding: 8px 10px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: 'Roboto Mono', monospace !important; font-size: 11px !important; color: #1f2937 !important; outline: none !important;"
                                           placeholder="101.1275000"
                                           onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                </div>
                                <button type="button" onclick="orgMap.clear()"
                                        style="padding: 8px 10px !important; font-size: 10px !important; font-weight: 500 !important; color: #b91c1c !important; background-color: #ffffff !important; border: 1px solid #fecaca !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                                        title="Remove the coordinates from this organization">
                                    CLEAR
                                </button>
                            </div>
                            <p id="org_map_hint" style="font-size: 10px !important; color: #6b7280 !important; margin: 8px 0 0 0 !important; font-family: Poppins, sans-serif !important;"></p>
                        </div>

                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Email</label>
                                <input type="email" name="email" x-model="editData.email"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="organization@example.com"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Contact Person</label>
                                <input type="text" name="contact_person" x-model="editData.contact_person"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Contact person name"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">State</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;" title="Coordinates are required before geofencing can judge a signature at this site">Geofence</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Phone</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Projects</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($organizations as $organization)
                <tr class="{{ !$organization->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 text-xs text-gray-900" style="font-family: Poppins, sans-serif; max-width: 200px;">
                        <span class="block truncate" title="{{ $organization->name }}">{{ Str::limit($organization->name, 30) }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                        @if($organization->organization_type)
                            {{ ucfirst($organization->organization_type) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $organization->state ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($organization->hasCoordinates())
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-700" style="font-size: 10px;"
                                  title="{{ $organization->latitude }}, {{ $organization->longitude }}">
                                <span class="material-symbols-outlined" style="font-size: 12px;">place</span>
                                Pinned
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-amber-100 text-amber-700" style="font-size: 10px;"
                                  title="Without coordinates, a signature at this site is recorded but its distance cannot be judged">
                                <span class="material-symbols-outlined" style="font-size: 12px;">location_off</span>
                                Not set
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $organization->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-600" style="font-size: 10px;">
                            {{ $organization->projects_count ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="status-badge inline-flex px-2 py-1 text-xs font-medium rounded {{ $organization->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                            {{ $organization->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-ui.action-buttons
                            :edit-onclick="auth()->user()->hasPermission('external_settings_organization.update') ? 'editOrganization(' . $organization->id . ', ' . json_encode([
                                'name' => $organization->name,
                                'organization_type' => $organization->organization_type ?? '',
                                'address_1' => $organization->address_1 ?? '',
                                'address_2' => $organization->address_2 ?? '',
                                'postcode' => $organization->postcode ?? '',
                                'district' => $organization->district ?? '',
                                'state' => $organization->state ?? '',
                                'country' => $organization->country ?? 'Malaysia',
                                // Passed as raw values, not formatted strings: the
                                // map picker validates and formats them itself.
                                'latitude' => $organization->latitude,
                                'longitude' => $organization->longitude,
                                'website' => $organization->website ?? '',
                                'phone' => $organization->phone ?? '',
                                'email' => $organization->email ?? '',
                                'contact_person' => $organization->contact_person ?? ''
                            ]) . ')' : null"
                            :delete-onclick="auth()->user()->hasPermission('external_settings_organization.delete') ? 'deleteOrganization(' . $organization->id . ')' : null"
                            :more-actions="auth()->user()->hasPermission('external_settings_organization.update') ? [
                                ['label' => $organization->is_active ? 'Deactivate' : 'Activate', 'icon' => $organization->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleStatus(\'organizations\', ' . $organization->id . ', this)']
                            ] : []"
                        />
                        <form id="delete-organization-{{ $organization->id }}" action="{{ route('external.settings.organizations.destroy', $organization) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 text-xs">No organizations found. Click "ORGANIZATION" to create one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <x-ui.custom-pagination :paginator="$organizations" record-label="organizations" />
    </div>
</div>

<script>
/**
 * The site-location picker inside the organization modal.
 *
 * The map is created once and reused. Leaflet cannot lay out inside a hidden
 * container — it measures the element on creation — so creation is deferred until
 * the modal is actually visible, and invalidateSize() is called each time it
 * reopens. Skipping that gives the classic half-rendered map with grey tiles.
 */
window.orgMap = {
    map: null,
    marker: null,
    // Roughly the centre of Peninsular Malaysia, used only when no coordinate is
    // set yet so the admin starts somewhere recognisable rather than at 0,0.
    fallback: [4.2105, 101.9758],
    fallbackZoom: 6,

    open(lat, lng) {
        const hasFix = this.valid(lat, lng);
        this.setInputs(hasFix ? Number(lat) : null, hasFix ? Number(lng) : null);

        if (!this.map) {
            if (typeof L === 'undefined') {
                this.hint('The map library could not be loaded. You can still type coordinates by hand.', true);
                return;
            }
            this.map = L.map('org_map', { scrollWheelZoom: false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(this.map);
            // Clicking is the primary way to place a pin; dragging refines it.
            this.map.on('click', (e) => this.place(e.latlng.lat, e.latlng.lng));
        }

        if (hasFix) {
            this.place(Number(lat), Number(lng), 17);
        } else {
            this.clearMarker();
            this.map.setView(this.fallback, this.fallbackZoom);
            this.hint('No coordinates set. Geofencing cannot judge this site until one is.');
        }

        // The modal has only just become visible, so the container's size is
        // still stale at this point.
        setTimeout(() => this.map && this.map.invalidateSize(), 60);
    },

    valid(lat, lng) {
        const a = Number(lat), b = Number(lng);
        if (!Number.isFinite(a) || !Number.isFinite(b)) return false;
        if (lat === '' || lng === '' || lat === null || lng === null) return false;
        return Math.abs(a) <= 90 && Math.abs(b) <= 180;
    },

    place(lat, lng, zoom) {
        if (!this.map) return;
        const point = [lat, lng];
        if (!this.marker) {
            this.marker = L.marker(point, { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const p = this.marker.getLatLng();
                this.setInputs(p.lat, p.lng);
                this.hint('Pin moved. Save to keep this position.');
            });
        } else {
            this.marker.setLatLng(point);
        }
        this.map.setView(point, zoom || Math.max(this.map.getZoom(), 15));
        this.setInputs(lat, lng);
        this.hint('Pin set. Save to keep this position.');
    },

    clearMarker() {
        if (this.marker && this.map) {
            this.map.removeLayer(this.marker);
            this.marker = null;
        }
    },

    /** Empties the fields so the organization is saved with no coordinates. */
    clear() {
        this.setInputs(null, null);
        this.clearMarker();
        this.hint('Coordinates removed. Signatures for this site will record a position but no distance.');
    },

    setInputs(lat, lng) {
        const latEl = document.getElementById('org_latitude');
        const lngEl = document.getElementById('org_longitude');
        if (!latEl || !lngEl) return;
        // Seven decimals matches the DECIMAL(10,7) column, so what is shown is
        // exactly what gets stored.
        latEl.value = lat === null ? '' : Number(lat).toFixed(7);
        lngEl.value = lng === null ? '' : Number(lng).toFixed(7);
    },

    /** Typed coordinates move the pin, so a pasted pair can be eyeballed. */
    fromInputs() {
        const lat = document.getElementById('org_latitude').value.trim();
        const lng = document.getElementById('org_longitude').value.trim();
        if (!this.valid(lat, lng)) {
            if (lat !== '' && lng !== '') {
                this.hint('That is not a valid position. Latitude is -90 to 90, longitude -180 to 180.', true);
            }
            return;
        }
        // Not setInputs() afterwards: reformatting while someone is mid-type
        // would fight the caret.
        if (!this.map) return;
        const point = [Number(lat), Number(lng)];
        if (!this.marker) {
            this.marker = L.marker(point, { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const p = this.marker.getLatLng();
                this.setInputs(p.lat, p.lng);
            });
        } else {
            this.marker.setLatLng(point);
        }
        this.map.setView(point, Math.max(this.map.getZoom(), 15));
        this.hint('Pin moved to the coordinates you typed.');
    },

    locateMe() {
        if (!navigator.geolocation) {
            this.hint('This browser cannot report a location.', true);
            return;
        }
        this.hint('Getting this device\'s position…');
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.place(pos.coords.latitude, pos.coords.longitude, 17);
                this.hint('Set to this device\'s position (accurate to about '
                    + Math.round(pos.coords.accuracy) + 'm). Only correct if you are at the site.');
            },
            (err) => this.hint('Could not get a position: ' + err.message, true),
            { enableHighAccuracy: true, timeout: 15000 },
        );
    },

    /**
     * Nominatim geocoding. Rate-limited to one request per second by usage
     * policy, which a manual search button cannot realistically exceed.
     */
    async search() {
        const q = document.getElementById('org_map_search').value.trim();
        if (!q) return;
        this.hint('Searching…');
        try {
            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q='
                + encodeURIComponent(q);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const hits = await res.json();
            if (!Array.isArray(hits) || hits.length === 0) {
                this.hint('Nothing found for that. Try a nearby town, or place the pin by hand.', true);
                return;
            }
            this.place(Number(hits[0].lat), Number(hits[0].lon), 16);
            this.hint('Found: ' + hits[0].display_name + ' — check the pin before saving.');
        } catch (e) {
            this.hint('Search failed: ' + e.message, true);
        }
    },

    hint(text, isError) {
        const el = document.getElementById('org_map_hint');
        if (!el) return;
        el.textContent = text || '';
        el.style.color = isError ? '#b91c1c' : '#6b7280';
    },
};
</script>
