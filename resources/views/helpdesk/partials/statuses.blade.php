<!-- Header with Add Button -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Status List</h3>
    @permission('helpdesk_statuses.create')
    <button type="button" onclick="openStatusModal()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
        ADD STATUS
    </button>
    @endpermission
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('helpdesk.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="statuses">
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
        <button type="button" onclick="window.location.href='{{ route('helpdesk.index', ['tab' => 'statuses']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Statuses Table -->
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="w-full divide-y divide-gray-200" style="table-layout: fixed;">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 20%;">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 25%;">Description</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Color</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Tickets</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Closed?</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Active</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($ticketStatuses as $status)
            <tr class="hover:bg-gray-50 {{ !$status->is_active ? 'opacity-50' : '' }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center flex-shrink-0" style="width: 28px; height: 28px; border-radius: 6px; background-color: {{ $status->color }}20;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $status->color }};">{{ $status->icon }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-900 truncate" style="font-family: Poppins, sans-serif;">{{ $status->name }}</span>
                            @if($status->is_default)
                            <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-700" style="font-size: 9px;">Default</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-600 line-clamp-2" style="font-family: Poppins, sans-serif;">{{ $status->description ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <div style="width: 20px; height: 20px; border-radius: 4px; background-color: {{ $status->color }};"></div>
                        <span class="text-xs text-gray-500" style="font-family: monospace;">{{ $status->color }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $status->tickets_count }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    @if($status->is_closed)
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600" style="font-size: 10px;">Yes</span>
                    @else
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600" style="font-size: 10px;">No</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $status->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                        {{ $status->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-ui.action-buttons
                        :edit-onclick="auth()->user()->hasPermission('helpdesk_statuses.update') ? 'editStatus(' . $status->id . ', ' . json_encode(['name' => $status->name, 'description' => $status->description, 'color' => $status->color, 'icon' => $status->icon, 'is_default' => $status->is_default, 'is_closed' => $status->is_closed]) . ')' : null"
                        :delete-onclick="auth()->user()->hasPermission('helpdesk_statuses.delete') ? 'deleteStatus(' . $status->id . ')' : null"
                        :more-actions="auth()->user()->hasPermission('helpdesk_statuses.update') ? [
                            ['label' => $status->is_active ? 'Deactivate' : 'Activate', 'icon' => $status->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleStatusActive(' . $status->id . ')'],
                            ['label' => 'Set as Default', 'icon' => 'star', 'onclick' => 'setDefaultStatus(' . $status->id . ')']
                        ] : []"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">
                    <span class="material-symbols-outlined text-gray-300" style="font-size: 40px;">pending_actions</span>
                    <p class="mt-2">No statuses found. Click "ADD STATUS" to create one.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($ticketStatuses->hasPages())
<div class="mt-4">
    <x-ui.custom-pagination :paginator="$ticketStatuses" record-label="statuses" />
</div>
@endif


<!-- Status Modal -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border-radius: 8px; width: 480px; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 id="statusModalTitle" style="font-size: 14px; font-weight: 600; color: #111827; font-family: Poppins, sans-serif;">Add Status</h3>
        </div>
        <form id="statusForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="statusMethod" value="POST">
            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Name *</label>
                    <input type="text" name="name" id="status_name" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px; font-family: Poppins, sans-serif;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Description</label>
                    <textarea name="description" id="status_description" rows="2"
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; font-family: Poppins, sans-serif;"></textarea>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Color *</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @php
                            $colors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#64748b', '#374151', '#1f2937'];
                        @endphp
                        @foreach($colors as $color)
                        <button type="button" onclick="selectStatusColor('{{ $color }}')" class="status-color-option" style="width: 32px; height: 32px; border-radius: 6px; background-color: {{ $color }}; border: 2px solid transparent; cursor: pointer;" data-color="{{ $color }}"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" id="status_color" value="#3b82f6" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedStatusColorPreview" style="width: 24px; height: 24px; border-radius: 4px; background-color: #3b82f6; border: 1px solid #e5e7eb;"></div>
                        <span id="selectedStatusColorCode" style="font-size: 10px; color: #374151; font-family: monospace;">#3b82f6</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Icon *</label>
                    <div style="display: grid !important; grid-template-columns: repeat(10, 1fr) !important; gap: 6px !important; max-height: 150px; overflow-y: auto; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        @php
                            $icons = ['pending', 'hourglass_empty', 'schedule', 'autorenew', 'sync', 'loop', 'play_arrow', 'pause', 'stop', 'check_circle', 'task_alt', 'done', 'done_all', 'verified', 'cancel', 'block', 'remove_circle', 'error', 'warning', 'info'];
                        @endphp
                        @foreach($icons as $icon)
                        <button type="button" onclick="selectStatusIcon('{{ $icon }}')" class="status-icon-option" style="width: 100% !important; aspect-ratio: 1 !important; border-radius: 6px; background-color: #f3f4f6; border: 2px solid transparent; cursor: pointer; display: flex !important; align-items: center !important; justify-content: center !important;" data-icon="{{ $icon }}">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #374151;">{{ $icon }}</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="icon" id="status_icon" value="pending" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedStatusIconPreview" style="width: 28px; height: 28px; border-radius: 4px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">pending</span>
                        </div>
                        <span id="selectedStatusIconName" style="font-size: 10px; color: #374151; font-family: monospace;">pending</span>
                    </div>
                </div>

                <div style="margin-bottom: 16px; display: flex; gap: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_default" id="status_is_default" value="1" style="width: 16px; height: 16px;">
                        <span style="font-size: 11px; color: #374151; font-family: Poppins, sans-serif;">Set as default status</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_closed" id="status_is_closed" value="1" style="width: 16px; height: 16px;">
                        <span style="font-size: 11px; color: #374151; font-family: Poppins, sans-serif;">Marks ticket as closed</span>
                    </label>
                </div>
                
                <div style="padding: 12px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                    <label style="display: block; font-size: 10px; font-weight: 500; color: #6b7280; margin-bottom: 8px; text-transform: uppercase;">Preview</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="statusPreviewBadge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; background-color: #3b82f620;">
                            <span id="statusPreviewIcon" class="material-symbols-outlined" style="font-size: 16px; color: #3b82f6;">pending</span>
                            <span id="statusPreviewName" style="font-size: 11px; font-weight: 500; color: #3b82f6;">Status Name</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeStatusModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: white; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<x-modals.delete-confirmation />

@push('scripts')
<script>
let selectedStatusColor = '#3b82f6';
let selectedStatusIcon = 'pending';

function openStatusModal() {
    document.getElementById('statusModalTitle').textContent = 'Add Status';
    document.getElementById('statusForm').action = '{{ route("helpdesk.statuses.store") }}';
    document.getElementById('statusMethod').value = 'POST';
    document.getElementById('statusForm').reset();
    selectStatusColor('#3b82f6');
    selectStatusIcon('pending');
    updateStatusPreview();
    document.getElementById('statusModal').style.display = 'block';
}

function editStatus(id, data) {
    document.getElementById('statusModalTitle').textContent = 'Edit Status';
    document.getElementById('statusForm').action = '/helpdesk/statuses/' + id;
    document.getElementById('statusMethod').value = 'PUT';
    document.getElementById('status_name').value = data.name || '';
    document.getElementById('status_description').value = data.description || '';
    document.getElementById('status_is_default').checked = data.is_default || false;
    document.getElementById('status_is_closed').checked = data.is_closed || false;
    selectStatusColor(data.color || '#3b82f6');
    selectStatusIcon(data.icon || 'pending');
    updateStatusPreview();
    document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function selectStatusColor(color) {
    selectedStatusColor = color;
    document.getElementById('status_color').value = color;
    document.getElementById('selectedStatusColorPreview').style.backgroundColor = color;
    document.getElementById('selectedStatusColorCode').textContent = color;
    document.querySelectorAll('.status-color-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.color === color ? '#1f2937' : 'transparent';
        btn.style.transform = btn.dataset.color === color ? 'scale(1.1)' : 'scale(1)';
    });
    updateStatusPreview();
}

function selectStatusIcon(icon) {
    selectedStatusIcon = icon;
    document.getElementById('status_icon').value = icon;
    document.getElementById('selectedStatusIconPreview').innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">' + icon + '</span>';
    document.getElementById('selectedStatusIconName').textContent = icon;
    document.querySelectorAll('.status-icon-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.icon === icon ? '#2563eb' : 'transparent';
        btn.style.backgroundColor = btn.dataset.icon === icon ? '#dbeafe' : '#f3f4f6';
    });
    updateStatusPreview();
}

function updateStatusPreview() {
    const name = document.getElementById('status_name').value || 'Status Name';
    document.getElementById('statusPreviewBadge').style.backgroundColor = selectedStatusColor + '20';
    document.getElementById('statusPreviewIcon').textContent = selectedStatusIcon;
    document.getElementById('statusPreviewIcon').style.color = selectedStatusColor;
    document.getElementById('statusPreviewName').textContent = name;
    document.getElementById('statusPreviewName').style.color = selectedStatusColor;
}

function deleteStatus(id) {
    window.showDeleteConfirmation({
        title: 'Delete Status',
        message: 'Are you sure you want to delete this status? This action cannot be undone.',
        formAction: '/helpdesk/statuses/' + id,
        method: 'DELETE'
    });
}

function toggleStatusActive(id) {
    fetch('/helpdesk/statuses/' + id + '/toggle-active', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to toggle status');
    });
}

function setDefaultStatus(id) {
    fetch('/helpdesk/statuses/' + id + '/set-default', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to set default');
    });
}

document.getElementById('status_name').addEventListener('input', updateStatusPreview);
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('statusModal').style.display === 'block') {
        closeStatusModal();
    }
});
</script>
@endpush
