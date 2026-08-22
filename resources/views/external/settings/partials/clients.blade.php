<div x-data="{ showForm: false, editId: null, editData: {}, step: 1, createAccount: false, hasAccount: false }" 
     x-on:edit-client.window="editId = $event.detail.id; editData = $event.detail.data; step = 1; createAccount = $event.detail.data.has_account || false; hasAccount = $event.detail.data.has_account || false; showForm = true">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Client List</h3>
        @permission('external_settings_client.create')
        <button @click="showForm = true; editId = null; editData = { country: 'Malaysia' }; step = 1; createAccount = false" 
                class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
            CLIENT
        </button>
        @endpermission
    </div>

    <!-- Search/Filter Form -->
    <div class="mb-4">
        <form action="{{ route('external.settings.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="clients">
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
                {{-- Filters on the linked user's approval_status, not on
                     clients.is_active — see getClients(). --}}
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending approval</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('external.settings.index', ['tab' => 'clients']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
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
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #0ea5e9 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">business</span>
                    </div>
                    <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;" x-text="editId ? 'Edit Client' : 'Add New Client'"></h3>
                </div>
                <button type="button" @click="showForm = false" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
                </button>
            </div>
            
            <!-- Timeline Steps -->
            <div class="py-3 px-5 border-b border-gray-100 bg-white">
                <div class="flex items-center justify-center gap-3">
                    <!-- Step 1 -->
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full text-xs font-medium flex items-center justify-center flex-shrink-0"
                             :class="step >= 1 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500'">1</div>
                        <span class="text-xs" :class="step >= 1 ? 'text-blue-500 font-medium' : 'text-gray-400'">Client Info</span>
                    </div>
                    <!-- Line -->
                    <div class="w-12 h-0.5 flex-shrink-0" :class="step >= 2 ? 'bg-blue-500' : 'bg-gray-200'"></div>
                    <!-- Step 2 -->
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full text-xs font-medium flex items-center justify-center flex-shrink-0"
                             :class="step >= 2 ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500'">2</div>
                        <span class="text-xs" :class="step >= 2 ? 'text-blue-500 font-medium' : 'text-gray-400'">Account Info</span>
                    </div>
                </div>
            </div>

            <form :action="editId ? '{{ url('external/settings/clients') }}/' + editId : '{{ route('external.settings.clients.store') }}'" method="POST">
                @csrf
                <template x-if="editId">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Step 1: Client Info -->
                <div x-show="step === 1" style="padding: 20px !important; max-height: 50vh !important; overflow-y: auto !important;">
                    <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Name <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="text" name="name" required x-model="editData.name"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Enter client name"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Type</label>
                                <select name="organization_type" x-model="editData.organization_type"
                                        style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                    <option value="">Select Type</option>
                                    <option value="gov">Government</option>
                                    <option value="ngo">NGO</option>
                                    <option value="company">Company</option>
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
                                <input type="email" name="client_email" x-model="editData.client_email"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="client@example.com"
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

                <!-- Step 2: Account Info -->
                <div x-show="step === 2" style="padding: 20px !important;">
                    <!-- For new client -->
                    <template x-if="!editId">
                        <div style="margin-bottom: 16px !important;">
                            <label style="display: flex !important; align-items: center !important; gap: 8px !important; cursor: pointer !important;">
                                <input type="checkbox" name="create_account" value="1" x-model="createAccount" 
                                       style="width: 16px !important; height: 16px !important; border-radius: 4px !important; accent-color: #3b82f6 !important;">
                                <span style="font-size: 12px !important; font-weight: 500 !important; color: #374151 !important;">Create user account for this client</span>
                            </label>
                            <p style="font-size: 11px !important; color: #6b7280 !important; margin-top: 4px !important; margin-left: 24px !important;">This will allow the client to login to the helpdesk</p>
                        </div>
                    </template>

                    <!-- For edit client with existing account -->
                    <template x-if="editId && hasAccount">
                        <div style="margin-bottom: 16px !important; padding: 12px !important; background-color: #f0fdf4 !important; border: 1px solid #bbf7d0 !important; border-radius: 6px !important;">
                            <div style="display: flex !important; align-items: center !important; gap: 8px !important;">
                                <span class="material-symbols-outlined" style="font-size: 16px !important; color: #16a34a !important;">check_circle</span>
                                <span style="font-size: 12px !important; font-weight: 500 !important; color: #166534 !important;">This client has an account</span>
                            </div>
                            <p style="font-size: 11px !important; color: #166534 !important; margin-top: 4px !important;" x-text="'Email: ' + (editData.email || '-')"></p>
                        </div>
                    </template>

                    <!-- For edit client without account -->
                    <template x-if="editId && !hasAccount">
                        <div style="margin-bottom: 16px !important;">
                            <label style="display: flex !important; align-items: center !important; gap: 8px !important; cursor: pointer !important;">
                                <input type="checkbox" name="create_account" value="1" x-model="createAccount" 
                                       style="width: 16px !important; height: 16px !important; border-radius: 4px !important; accent-color: #3b82f6 !important;">
                                <span style="font-size: 12px !important; font-weight: 500 !important; color: #374151 !important;">Create user account for this client</span>
                            </label>
                            <p style="font-size: 11px !important; color: #6b7280 !important; margin-top: 4px !important; margin-left: 24px !important;">This will allow the client to login to the helpdesk</p>
                        </div>
                    </template>

                    <div x-show="createAccount && !hasAccount" style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                        <div style="display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important;">
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Email <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="email" name="email" x-model="editData.email"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="client@example.com"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                            <div>
                                <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                    Password <span style="color: #ef4444 !important;">*</span>
                                </label>
                                <input type="password" name="password"
                                       style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                       placeholder="Minimum 6 characters"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                            </div>
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Role</label>
                            <select name="role_id"
                                    style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                <option value="">Select Role</option>
                                @foreach($roles ?? [] as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div x-show="!createAccount && !hasAccount" style="text-align: center !important; padding: 32px 0 !important; color: #9ca3af !important;">
                        <span class="material-symbols-outlined" style="font-size: 48px !important; opacity: 0.5 !important;">no_accounts</span>
                        <p style="font-size: 12px !important; margin-top: 8px !important;">No user account will be created</p>
                        <p style="font-size: 11px !important;">Check the box above to create a login account</p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: space-between !important; background-color: #f9fafb !important;">
                    <div>
                        <button type="button" x-show="step > 1" @click="step--" 
                                style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                                onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                            Back
                        </button>
                    </div>
                    <div style="display: flex !important; gap: 10px !important;">
                        <button type="button" @click="showForm = false" 
                                style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                                onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                            Cancel
                        </button>
                        <button type="button" x-show="step < 2" @click="step++"
                                style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #3b82f6 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                                onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'">
                            Next
                        </button>
                        <button type="submit" x-show="step === 2"
                                style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #3b82f6 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 6px !important;"
                                onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'">
                            <span class="material-symbols-outlined" style="font-size: 16px !important;">save</span>
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Only when there is something waiting, and only counted across the whole
         table rather than the current page. --}}
    @if(($pendingCount ?? 0) > 0)
    <div class="mb-4 px-4 py-3 rounded border border-amber-200 bg-amber-50 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-amber-600" style="font-size: 18px;">schedule</span>
            <p class="text-xs text-amber-900 m-0" style="font-family: Poppins, sans-serif;">
                <strong>{{ $pendingCount }}</strong>
                {{ $pendingCount === 1 ? 'registration is' : 'registrations are' }} waiting for approval.
                @unless(auth()->user()->canApproveRegistrations())
                    Only an Administrator can approve them.
                @endunless
            </p>
        </div>
        @if(request('status') !== 'pending')
        <a href="{{ route('external.settings.index', ['tab' => 'clients', 'status' => 'pending']) }}"
           class="inline-flex items-center gap-1 px-3 text-amber-900 rounded transition"
           style="min-height: 28px; font-size: 11px; font-weight: 600; background-color: #fde68a; text-decoration: none; font-family: Poppins, sans-serif;">
            <span class="material-symbols-outlined" style="font-size: 14px;">filter_alt</span>
            SHOW ONLY THESE
        </a>
        @endif
    </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Account</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Created</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                @php
                    $pending  = $client->user && $client->user->isPendingApproval();
                    $rejected = $client->user && $client->user->isRejected();
                    $canApprove = auth()->user()->canApproveRegistrations();
                @endphp
                {{-- A pending row keeps full opacity. The dimming is meant to say
                     "switched off, ignore me", which is the opposite of what a row
                     awaiting a decision needs to say. --}}
                <tr class="{{ !$client->is_active && !$pending ? 'opacity-50' : '' }} {{ $pending ? 'bg-amber-50' : '' }}">
                    <td class="px-4 py-3 text-xs text-gray-900" style="font-family: Poppins, sans-serif;">{{ $client->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                        @if($client->organization_type)
                            {{ $client->organization_type == 'gov' ? 'Government' : ($client->organization_type == 'ngo' ? 'NGO' : 'Company') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $client->email ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $client->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                        @if($client->user_id)
                            <span class="inline-flex items-center gap-1 text-green-600">
                                <span class="material-symbols-outlined" style="font-size: 12px;">check_circle</span>
                                {{ $client->user?->email ?? 'Yes' }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">{{ $client->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        {{-- Three states, not two. A self-registration is inactive
                             AND waiting on somebody, which "Inactive" alone does not
                             say — it reads identically to a client an administrator
                             switched off on purpose. --}}
                        @if($pending)
                            <span class="status-badge inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-amber-100 text-amber-700" style="font-size: 10px;">
                                <span class="material-symbols-outlined" style="font-size: 11px;">schedule</span>
                                Pending
                            </span>
                        @elseif($rejected)
                            <span class="status-badge inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700" style="font-size: 10px;">
                                <span class="material-symbols-outlined" style="font-size: 11px;">block</span>
                                Rejected
                            </span>
                        @else
                            <span class="status-badge inline-flex px-2 py-1 text-xs font-medium rounded {{ $client->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                                {{ $client->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        {{-- Approve and Reject sit in front of the dropdown, not
                             inside it. They are the only reason this row is at the
                             top of the list, and burying a decision somebody is
                             waiting on behind a menu costs an extra tap for the one
                             action that matters. --}}
                        @if($pending && $canApprove)
                        <div class="inline-flex items-center gap-1 mr-1 align-middle">
                            <button type="button"
                                    onclick="openApprove({{ $client->id }}, {{ Illuminate\Support\Js::from($client->name) }}, {{ Illuminate\Support\Js::from($client->user?->email ?? '') }})"
                                    class="inline-flex items-center gap-1 px-2 text-white rounded transition"
                                    style="min-height: 26px; font-size: 10px; font-weight: 600; background-color: #16a34a; font-family: Poppins, sans-serif;"
                                    onmouseover="this.style.backgroundColor='#15803d'" onmouseout="this.style.backgroundColor='#16a34a'">
                                <span class="material-symbols-outlined" style="font-size: 13px;">check</span>
                                APPROVE
                            </button>
                            <button type="button"
                                    onclick="openReject({{ $client->id }}, {{ Illuminate\Support\Js::from($client->name) }})"
                                    class="inline-flex items-center gap-1 px-2 text-white rounded transition"
                                    style="min-height: 26px; font-size: 10px; font-weight: 600; background-color: #dc2626; font-family: Poppins, sans-serif;"
                                    onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                                <span class="material-symbols-outlined" style="font-size: 13px;">close</span>
                                REJECT
                            </button>
                        </div>
                        @elseif($pending)
                            {{-- Visible to non-administrators so the row still
                                 explains itself, rather than looking like a client
                                 that is simply switched off for no reason. --}}
                            <span class="text-gray-400 mr-1 align-middle" style="font-size: 10px; font-family: Poppins, sans-serif;">Administrator only</span>
                        @endif
                        <x-ui.action-buttons
                            :edit-onclick="auth()->user()->hasPermission('external_settings_client.update') ? 'editClient(' . $client->id . ', ' . json_encode([
                                'name' => $client->name,
                                'organization_type' => $client->organization_type ?? '',
                                'address_1' => $client->address_1 ?? '',
                                'address_2' => $client->address_2 ?? '',
                                'postcode' => $client->postcode ?? '',
                                'district' => $client->district ?? '',
                                'state' => $client->state ?? '',
                                'country' => $client->country ?? 'Malaysia',
                                'website' => $client->website ?? '',
                                'phone' => $client->phone ?? '',
                                'client_email' => $client->email ?? '',
                                'contact_person' => $client->contact_person ?? '',
                                'has_account' => $client->user_id ? true : false,
                                'email' => $client->user?->email ?? ''
                            ]) . ')' : null"
                            :delete-onclick="auth()->user()->hasPermission('external_settings_client.delete') ? 'deleteClient(' . $client->id . ')' : null"
                            :more-actions="auth()->user()->hasPermission('external_settings_client.update') ? [
                                ['label' => $client->is_active ? 'Deactivate' : 'Activate', 'icon' => $client->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleStatus(\'clients\', ' . $client->id . ', this)']
                            ] : []"
                        />
                        <form id="delete-client-{{ $client->id }}" action="{{ route('external.settings.clients.destroy', $client) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 text-xs">No clients found. Click "CLIENT" to create one.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <x-ui.custom-pagination :paginator="$clients" record-label="clients" />
    </div>
</div>

{{--
    Approval dialogs.

    Outside the Alpine root above on purpose: that component's state is the
    add/edit wizard, and reusing it would mean a half-filled client form could be
    open behind an approval decision. Plain DOM here, driven by the two functions
    pushed at the bottom of the settings index.

    Rendered only for an Administrator. A dialog nobody can submit is a dialog that
    should not exist.
--}}
@if(auth()->user()->canApproveRegistrations())
<div id="approve-modal" class="fixed inset-0 hidden items-center justify-center" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="background-color:#fff; border-radius:12px; width:100%; max-width:420px; margin:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,.25);">
        <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#f0fdf4; display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:8px; background:#16a34a; display:flex; align-items:center; justify-content:center;">
                <span class="material-symbols-outlined" style="font-size:20px; color:#fff;">how_to_reg</span>
            </div>
            <h3 style="font-size:14px; font-weight:600; color:#111827; margin:0; font-family:Poppins,sans-serif;">Approve registration</h3>
        </div>

        <form id="approve-form" method="POST">
            @csrf
            <div style="padding:20px;">
                <p style="font-size:12px; color:#374151; margin:0 0 4px; font-family:Poppins,sans-serif;" id="approve-name"></p>
                <p style="font-size:11px; color:#6b7280; margin:0 0 16px; font-family:Poppins,sans-serif;" id="approve-email"></p>

                <label style="display:block; font-size:11px; font-weight:500; color:#374151; margin-bottom:6px; font-family:Poppins,sans-serif;">
                    Role <span style="color:#ef4444;">*</span>
                </label>
                <select name="role_id" required
                        style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:Poppins,sans-serif; font-size:12px; color:#1f2937; outline:none;">
                    <option value="">Select role</option>
                    @foreach($roles as $role)
                        {{-- Pre-selected to Client when that row exists. The
                             administrator still chooses, matching the Add Client
                             wizard, so a registrant can never influence their own
                             permissions. --}}
                        <option value="{{ $role->id }}" @selected(($defaultClientRoleId ?? null) === $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <p style="font-size:10.5px; color:#6b7280; margin:6px 0 0; font-family:Poppins,sans-serif;">
                    Approving activates the login and the client record. Project access is separate —
                    add them to a project afterwards.
                </p>
            </div>
            <div style="padding:14px 20px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeApprove()"
                        style="padding:8px 16px; font-size:12px; color:#374151; background:#fff; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-family:Poppins,sans-serif;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:8px 16px; font-size:12px; font-weight:500; color:#fff; background:#16a34a; border:none; border-radius:6px; cursor:pointer; font-family:Poppins,sans-serif;">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>

<div id="reject-modal" class="fixed inset-0 hidden items-center justify-center" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="background-color:#fff; border-radius:12px; width:100%; max-width:420px; margin:16px; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,.25);">
        <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#fef2f2; display:flex; align-items:center; gap:10px;">
            <div style="width:36px; height:36px; border-radius:8px; background:#dc2626; display:flex; align-items:center; justify-content:center;">
                <span class="material-symbols-outlined" style="font-size:20px; color:#fff;">block</span>
            </div>
            <h3 style="font-size:14px; font-weight:600; color:#111827; margin:0; font-family:Poppins,sans-serif;">Reject registration</h3>
        </div>

        <form id="reject-form" method="POST">
            @csrf
            <div style="padding:20px;">
                <p style="font-size:12px; color:#374151; margin:0 0 16px; font-family:Poppins,sans-serif;" id="reject-name"></p>

                <label style="display:block; font-size:11px; font-weight:500; color:#374151; margin-bottom:6px; font-family:Poppins,sans-serif;">
                    Reason (optional)
                </label>
                <textarea name="rejection_reason" rows="3" maxlength="500"
                          placeholder="Shown to the applicant if they try to sign in."
                          style="width:100%; padding:10px 12px; border:1px solid #d1d5db; border-radius:6px; font-family:Poppins,sans-serif; font-size:12px; color:#1f2937; outline:none; resize:vertical;"></textarea>
                <p style="font-size:10.5px; color:#6b7280; margin:6px 0 0; font-family:Poppins,sans-serif;">
                    The account is kept and marked rejected, not deleted — so the email cannot be
                    used to register again straight away, and the record of the application survives.
                </p>
            </div>
            <div style="padding:14px 20px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeReject()"
                        style="padding:8px 16px; font-size:12px; color:#374151; background:#fff; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-family:Poppins,sans-serif;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:8px 16px; font-size:12px; font-weight:500; color:#fff; background:#dc2626; border:none; border-radius:6px; cursor:pointer; font-family:Poppins,sans-serif;">
                    Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Built from a template rather than a data attribute so the client id cannot be
// tampered with in the DOM ahead of submitting.
const APPROVE_URL = @json(route('external.settings.clients.approve', ['client' => '__ID__']));
const REJECT_URL  = @json(route('external.settings.clients.reject',  ['client' => '__ID__']));

function openApprove(id, name, email) {
    document.getElementById('approve-form').action = APPROVE_URL.replace('__ID__', id);
    document.getElementById('approve-name').textContent = name;
    document.getElementById('approve-email').textContent = email || 'No login email on record';
    const m = document.getElementById('approve-modal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}

function closeApprove() {
    const m = document.getElementById('approve-modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

function openReject(id, name) {
    document.getElementById('reject-form').action = REJECT_URL.replace('__ID__', id);
    document.getElementById('reject-name').textContent = 'Reject the registration from ' + name + '?';
    const m = document.getElementById('reject-modal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}

function closeReject() {
    const m = document.getElementById('reject-modal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// Escape closes whichever is open. A modal that can only be dismissed by its own
// Cancel button is a trap when the page behind it scrolled.
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeApprove(); closeReject(); }
});
</script>
@endpush
@endif
