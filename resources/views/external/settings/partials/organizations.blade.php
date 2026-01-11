<div x-data="{ showForm: false, editId: null, editData: {} }" 
     x-on:edit-organization.window="editId = $event.detail.id; editData = $event.detail.data; showForm = true">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Organization List</h3>
        @permission('external_settings_organization.create')
        <button @click="showForm = true; editId = null; editData = { country: 'Malaysia' }" 
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
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">No organizations found. Click "ORGANIZATION" to create one.</td>
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
