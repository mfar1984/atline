<div x-data="{ 
    showForm: false, 
    showViewModal: false, 
    editId: null, 
    editData: {},
    banDetails: ''
}" 
@open-view-modal.window="showViewModal = true; banDetails = $event.detail">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Banned IP Addresses</h3>
            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700">
                <span class="material-symbols-outlined" style="font-size: 12px;">block</span>
                {{ $bannedIps->total() }} Banned
            </span>
        </div>
        <button @click="showForm = true; editId = null; editData = { is_permanent: '0', duration_minutes: 60 }" 
                class="inline-flex items-center gap-1 px-3 py-2 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
            <span class="material-symbols-outlined" style="font-size: 16px;">add_circle</span>
            BAN IP ADDRESS
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold text-red-600">{{ $bannedStats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Banned</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold text-orange-600">{{ $bannedStats['permanent'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Permanent</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold text-yellow-600">{{ $bannedStats['temporary'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Temporary</p>
        </div>
        <div class="p-3 bg-white border border-gray-200 rounded text-center">
            <p class="text-xl font-bold text-blue-600">{{ $bannedStats['today'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Banned Today</p>
        </div>
    </div>

    @if($bannedIps->isEmpty())
        <!-- No Banned IPs -->
        <div class="border border-green-200 rounded bg-green-50 p-8 text-center">
            <span class="material-symbols-outlined text-green-400" style="font-size: 64px;">verified_user</span>
            <h4 class="mt-4 text-sm font-medium text-green-700" style="font-family: Poppins, sans-serif;">No Banned IP Addresses</h4>
            <p class="mt-2 text-xs text-green-600" style="font-family: Poppins, sans-serif;">
                There are currently no banned IP addresses in the system.
            </p>
        </div>
    @else
        <!-- Banned IPs Table -->
        <div class="overflow-x-auto border border-gray-200 rounded">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 140px;">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 100px;">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px;">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 100px;">Failed Attempts</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 140px;">Banned At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 140px;">Expires At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 120px;">Banned By</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($bannedIps as $ban)
                    <tr class="{{ $ban->is_permanent ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 text-xs font-mono text-gray-900">
                            {{ $ban->ip_address }}
                        </td>
                        <td class="px-4 py-3">
                            @if($ban->is_permanent)
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700" style="font-size: 10px;">
                                    <span class="material-symbols-outlined" style="font-size: 12px;">block</span>
                                    Permanent
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-700" style="font-size: 10px;">
                                    <span class="material-symbols-outlined" style="font-size: 12px;">schedule</span>
                                    Temporary
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                            {{ $ban->reason }}
                            @if($ban->notes)
                                <p class="text-xs text-gray-400 mt-1">{{ \Illuminate\Support\Str::limit($ban->notes, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded {{ $ban->failed_attempts >= 10 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $ban->failed_attempts }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap" style="font-family: Poppins, sans-serif;">
                            {{ $ban->banned_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap" style="font-family: Poppins, sans-serif;">
                            @if($ban->is_permanent)
                                <span class="text-red-600 font-medium">Never</span>
                            @elseif($ban->expires_at)
                                {{ $ban->expires_at->format('d/m/Y H:i') }}
                                @if($ban->expires_at->isPast())
                                    <span class="text-xs text-green-600">(Expired)</span>
                                @else
                                    <span class="text-xs text-orange-600">({{ $ban->expires_at->diffForHumans() }})</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                            {{ $ban->bannedByUser?->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-ui.action-buttons
                                :show-onclick="'viewBanDetails(' . $ban->id . ')'"
                                :delete-onclick="'unbanIp(' . $ban->id . ')'"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $bannedIps->links() }}
        </div>
    @endif

    <!-- Info Box -->
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
        <p class="text-xs font-medium text-blue-700 mb-2">About IP Banning:</p>
        <div class="text-xs text-blue-600 space-y-1">
            <p>• <strong>Automatic Banning:</strong> IPs are automatically banned after exceeding rate limits (configurable in System Configuration)</p>
            <p>• <strong>Temporary Bans:</strong> Expire after a set duration (default: 60 minutes)</p>
            <p>• <strong>Permanent Bans:</strong> Require manual removal by administrators</p>
            <p>• <strong>Expired Bans:</strong> Are automatically cleaned up and removed from the system</p>
        </div>
    </div>

    <!-- Add Ban Modal -->
    <div x-show="showForm" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 480px !important; margin: 16px !important; overflow: hidden !important;" @click.away="showForm = false">
            <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
                <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #dc2626 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">block</span>
                    </div>
                    <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Ban IP Address</h3>
                </div>
                <button type="button" @click="showForm = false" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
                </button>
            </div>

            <form action="{{ route('settings.activity-logs.ban-ip') }}" method="POST">
                @csrf
                <div style="padding: 20px !important; max-height: 60vh !important; overflow-y: auto !important;">
                    <div style="display: flex !important; flex-direction: column !important; gap: 16px !important;">
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                IP Address <span style="color: #ef4444 !important;">*</span>
                            </label>
                            <input type="text" name="ip_address" required pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" x-model="editData.ip_address"
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   placeholder="192.168.1.1"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                Reason <span style="color: #ef4444 !important;">*</span>
                            </label>
                            <input type="text" name="reason" required x-model="editData.reason"
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   placeholder="e.g., Suspicious activity"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">
                                Ban Type <span style="color: #ef4444 !important;">*</span>
                            </label>
                            <select name="is_permanent" x-model="editData.is_permanent"
                                    style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                                <option value="0">Temporary</option>
                                <option value="1">Permanent</option>
                            </select>
                        </div>
                        <div x-show="editData.is_permanent === '0'">
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" x-model="editData.duration_minutes" min="1"
                                   style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important;"
                                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display: block !important; font-size: 11px !important; font-weight: 500 !important; color: #374151 !important; margin-bottom: 6px !important; font-family: Poppins, sans-serif !important;">Notes (optional)</label>
                            <textarea name="notes" rows="3" x-model="editData.notes"
                                      style="width: 100% !important; padding: 10px 12px !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; font-family: Poppins, sans-serif !important; font-size: 12px !important; color: #1f2937 !important; outline: none !important; resize: vertical !important;"
                                      placeholder="Additional information..."
                                      onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
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
                            style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #ffffff !important; background-color: #dc2626 !important; border: none !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important; display: flex !important; align-items: center !important; gap: 6px !important;"
                            onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                        <span class="material-symbols-outlined" style="font-size: 16px !important;">block</span>
                        Ban IP Address
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Details Modal -->
    <div x-show="showViewModal" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div style="background-color: #ffffff !important; border-radius: 12px !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; width: 100% !important; max-width: 420px !important; margin: 16px !important; overflow: hidden !important;" @click.away="showViewModal = false">
            <div style="padding: 16px 20px !important; border-bottom: 1px solid #e5e7eb !important; display: flex !important; align-items: center !important; justify-content: space-between !important; background-color: #f9fafb !important;">
                <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
                    <div style="width: 36px !important; height: 36px !important; border-radius: 8px !important; background-color: #3b82f6 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                        <span class="material-symbols-outlined" style="font-size: 20px !important; color: #ffffff !important;">info</span>
                    </div>
                    <h3 style="font-size: 14px !important; font-weight: 600 !important; color: #111827 !important; font-family: Poppins, sans-serif !important; margin: 0 !important;">Ban Details</h3>
                </div>
                <button type="button" @click="showViewModal = false" style="width: 32px !important; height: 32px !important; border-radius: 6px !important; border: none !important; background-color: transparent !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important;" onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined" style="font-size: 20px !important; color: #6b7280 !important;">close</span>
                </button>
            </div>
            <div style="padding: 20px !important;">
                <div x-html="banDetails" class="space-y-3 text-xs">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div style="padding: 16px 20px !important; border-top: 1px solid #e5e7eb !important; display: flex !important; justify-content: flex-end !important; background-color: #f9fafb !important;">
                <button type="button" @click="showViewModal = false"
                        style="padding: 10px 20px !important; font-size: 12px !important; font-weight: 500 !important; color: #374151 !important; background-color: #ffffff !important; border: 1px solid #d1d5db !important; border-radius: 6px !important; cursor: pointer !important; font-family: Poppins, sans-serif !important;"
                        onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='#ffffff'">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
<!-- End Alpine.js scope -->

<script>
function viewBanDetails(banId) {
    fetch(`/settings/activity-logs/banned/${banId}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="border-b pb-2">
                    <p class="text-gray-500">IP Address</p>
                    <p class="font-mono font-medium">${data.ip_address}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Type</p>
                    <p class="font-medium">${data.is_permanent ? 'Permanent' : 'Temporary'}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Reason</p>
                    <p>${data.reason}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Failed Attempts</p>
                    <p class="font-medium">${data.failed_attempts}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Banned At</p>
                    <p>${data.banned_at}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Expires At</p>
                    <p>${data.expires_at || 'Never'}</p>
                </div>
                <div class="border-b pb-2">
                    <p class="text-gray-500">Banned By</p>
                    <p>${data.banned_by || 'System'}</p>
                </div>
                ${data.notes ? `
                <div>
                    <p class="text-gray-500">Notes</p>
                    <p>${data.notes}</p>
                </div>
                ` : ''}
            `;
            
            // Dispatch Alpine.js event to show modal
            window.dispatchEvent(new CustomEvent('open-view-modal', { 
                detail: content 
            }));
        })
        .catch(error => {
            console.error('Error fetching ban details:', error);
            alert('Failed to load ban details. Please try again.');
        });
}

function unbanIp(banId) {
    // Use the global delete modal if available
    if (typeof window.showDeleteModal === 'function') {
        window.showDeleteModal(`/settings/activity-logs/banned/${banId}`, 'Are you sure you want to unban this IP address?');
    } else {
        // Fallback to confirm dialog
        if (confirm('Are you sure you want to unban this IP address?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/settings/activity-logs/banned/${banId}`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
