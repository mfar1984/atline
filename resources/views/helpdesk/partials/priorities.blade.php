<!-- Header with Add Button -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Priority List</h3>
    @permission('helpdesk_priorities.create')
    <button type="button" onclick="openPriorityModal()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
        ADD PRIORITY
    </button>
    @endpermission
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('helpdesk.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="priorities">
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
        <button type="button" onclick="window.location.href='{{ route('helpdesk.index', ['tab' => 'priorities']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Priorities Table -->
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="w-full divide-y divide-gray-200" style="table-layout: fixed;">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 20%;">Priority</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 30%;">Description</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Color</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Level</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Tickets</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Status</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($ticketPriorities as $priority)
            <tr class="hover:bg-gray-50 {{ !$priority->is_active ? 'opacity-50' : '' }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center flex-shrink-0" style="width: 28px; height: 28px; border-radius: 6px; background-color: {{ $priority->color }}20;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $priority->color }};">{{ $priority->icon }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-900 truncate" style="font-family: Poppins, sans-serif;">{{ $priority->name }}</span>
                            @if($priority->is_default)
                            <span class="ml-1 inline-flex px-1.5 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-700" style="font-size: 9px;">Default</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-600 line-clamp-2" style="font-family: Poppins, sans-serif;">{{ $priority->description ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <div style="width: 20px; height: 20px; border-radius: 4px; background-color: {{ $priority->color }};"></div>
                        <span class="text-xs text-gray-500" style="font-family: monospace;">{{ $priority->color }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $priority->color }}20; color: {{ $priority->color }};">
                        {{ $priority->level }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $priority->tickets_count }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $priority->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                        {{ $priority->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-ui.action-buttons
                        :edit-onclick="auth()->user()->hasPermission('helpdesk_priorities.update') ? 'editPriority(' . $priority->id . ', ' . json_encode(['name' => $priority->name, 'description' => $priority->description, 'color' => $priority->color, 'icon' => $priority->icon, 'level' => $priority->level, 'is_default' => $priority->is_default]) . ')' : null"
                        :delete-onclick="auth()->user()->hasPermission('helpdesk_priorities.delete') ? 'deletePriority(' . $priority->id . ')' : null"
                        :more-actions="auth()->user()->hasPermission('helpdesk_priorities.update') ? [
                            ['label' => $priority->is_active ? 'Deactivate' : 'Activate', 'icon' => $priority->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'togglePriorityStatus(' . $priority->id . ')'],
                            ['label' => 'Set as Default', 'icon' => 'star', 'onclick' => 'setDefaultPriority(' . $priority->id . ')']
                        ] : []"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 text-xs">
                    <span class="material-symbols-outlined text-gray-300" style="font-size: 40px;">flag</span>
                    <p class="mt-2">No priorities found. Click "ADD PRIORITY" to create one.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($ticketPriorities->hasPages())
<div class="mt-4">
    <x-ui.custom-pagination :paginator="$ticketPriorities" record-label="priorities" />
</div>
@endif


<!-- Priority Modal -->
<div id="priorityModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border-radius: 8px; width: 480px; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 id="priorityModalTitle" style="font-size: 14px; font-weight: 600; color: #111827; font-family: Poppins, sans-serif;">Add Priority</h3>
        </div>
        <form id="priorityForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="priorityMethod" value="POST">
            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Name *</label>
                    <input type="text" name="name" id="priority_name" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px; font-family: Poppins, sans-serif;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Description</label>
                    <textarea name="description" id="priority_description" rows="2"
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; font-family: Poppins, sans-serif;"></textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Level * (1=Lowest, 10=Highest)</label>
                    <input type="number" name="level" id="priority_level" min="1" max="10" value="1" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px; font-family: Poppins, sans-serif;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Color *</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @php
                            $colors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#64748b', '#374151', '#1f2937'];
                        @endphp
                        @foreach($colors as $color)
                        <button type="button" onclick="selectPriorityColor('{{ $color }}')" class="priority-color-option" style="width: 32px; height: 32px; border-radius: 6px; background-color: {{ $color }}; border: 2px solid transparent; cursor: pointer;" data-color="{{ $color }}"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" id="priority_color" value="#ef4444" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedPriorityColorPreview" style="width: 24px; height: 24px; border-radius: 4px; background-color: #ef4444; border: 1px solid #e5e7eb;"></div>
                        <span id="selectedPriorityColorCode" style="font-size: 10px; color: #374151; font-family: monospace;">#ef4444</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Icon *</label>
                    <div style="display: grid !important; grid-template-columns: repeat(10, 1fr) !important; gap: 6px !important; max-height: 150px; overflow-y: auto; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        @php
                            $icons = ['flag', 'priority_high', 'warning', 'error', 'info', 'bolt', 'local_fire_department', 'whatshot', 'speed', 'timer', 'schedule', 'hourglass_empty', 'trending_up', 'arrow_upward', 'arrow_downward', 'low_priority', 'notification_important', 'report', 'crisis_alert', 'emergency'];
                        @endphp
                        @foreach($icons as $icon)
                        <button type="button" onclick="selectPriorityIcon('{{ $icon }}')" class="priority-icon-option" style="width: 100% !important; aspect-ratio: 1 !important; border-radius: 6px; background-color: #f3f4f6; border: 2px solid transparent; cursor: pointer; display: flex !important; align-items: center !important; justify-content: center !important;" data-icon="{{ $icon }}">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #374151;">{{ $icon }}</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="icon" id="priority_icon" value="flag" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedPriorityIconPreview" style="width: 28px; height: 28px; border-radius: 4px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">flag</span>
                        </div>
                        <span id="selectedPriorityIconName" style="font-size: 10px; color: #374151; font-family: monospace;">flag</span>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_default" id="priority_is_default" value="1" style="width: 16px; height: 16px;">
                        <span style="font-size: 11px; color: #374151; font-family: Poppins, sans-serif;">Set as default priority</span>
                    </label>
                </div>
                
                <div style="padding: 12px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                    <label style="display: block; font-size: 10px; font-weight: 500; color: #6b7280; margin-bottom: 8px; text-transform: uppercase;">Preview</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="priorityPreviewBadge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; background-color: #ef444420;">
                            <span id="priorityPreviewIcon" class="material-symbols-outlined" style="font-size: 16px; color: #ef4444;">flag</span>
                            <span id="priorityPreviewName" style="font-size: 11px; font-weight: 500; color: #ef4444;">Priority Name</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closePriorityModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: white; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<x-modals.delete-confirmation />

@push('scripts')
<script>
let selectedPriorityColor = '#ef4444';
let selectedPriorityIcon = 'flag';

function openPriorityModal() {
    document.getElementById('priorityModalTitle').textContent = 'Add Priority';
    document.getElementById('priorityForm').action = '{{ route("helpdesk.priorities.store") }}';
    document.getElementById('priorityMethod').value = 'POST';
    document.getElementById('priorityForm').reset();
    selectPriorityColor('#ef4444');
    selectPriorityIcon('flag');
    document.getElementById('priority_level').value = 1;
    updatePriorityPreview();
    document.getElementById('priorityModal').style.display = 'block';
}

function editPriority(id, data) {
    document.getElementById('priorityModalTitle').textContent = 'Edit Priority';
    document.getElementById('priorityForm').action = '/helpdesk/priorities/' + id;
    document.getElementById('priorityMethod').value = 'PUT';
    document.getElementById('priority_name').value = data.name || '';
    document.getElementById('priority_description').value = data.description || '';
    document.getElementById('priority_level').value = data.level || 1;
    document.getElementById('priority_is_default').checked = data.is_default || false;
    selectPriorityColor(data.color || '#ef4444');
    selectPriorityIcon(data.icon || 'flag');
    updatePriorityPreview();
    document.getElementById('priorityModal').style.display = 'block';
}

function closePriorityModal() {
    document.getElementById('priorityModal').style.display = 'none';
}

function selectPriorityColor(color) {
    selectedPriorityColor = color;
    document.getElementById('priority_color').value = color;
    document.getElementById('selectedPriorityColorPreview').style.backgroundColor = color;
    document.getElementById('selectedPriorityColorCode').textContent = color;
    document.querySelectorAll('.priority-color-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.color === color ? '#1f2937' : 'transparent';
        btn.style.transform = btn.dataset.color === color ? 'scale(1.1)' : 'scale(1)';
    });
    updatePriorityPreview();
}

function selectPriorityIcon(icon) {
    selectedPriorityIcon = icon;
    document.getElementById('priority_icon').value = icon;
    document.getElementById('selectedPriorityIconPreview').innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">' + icon + '</span>';
    document.getElementById('selectedPriorityIconName').textContent = icon;
    document.querySelectorAll('.priority-icon-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.icon === icon ? '#2563eb' : 'transparent';
        btn.style.backgroundColor = btn.dataset.icon === icon ? '#dbeafe' : '#f3f4f6';
    });
    updatePriorityPreview();
}

function updatePriorityPreview() {
    const name = document.getElementById('priority_name').value || 'Priority Name';
    document.getElementById('priorityPreviewBadge').style.backgroundColor = selectedPriorityColor + '20';
    document.getElementById('priorityPreviewIcon').textContent = selectedPriorityIcon;
    document.getElementById('priorityPreviewIcon').style.color = selectedPriorityColor;
    document.getElementById('priorityPreviewName').textContent = name;
    document.getElementById('priorityPreviewName').style.color = selectedPriorityColor;
}

function deletePriority(id) {
    window.showDeleteConfirmation({
        title: 'Delete Priority',
        message: 'Are you sure you want to delete this priority? This action cannot be undone.',
        formAction: '/helpdesk/priorities/' + id,
        method: 'DELETE'
    });
}

function togglePriorityStatus(id) {
    fetch('/helpdesk/priorities/' + id + '/toggle-status', {
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

function setDefaultPriority(id) {
    fetch('/helpdesk/priorities/' + id + '/set-default', {
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

document.getElementById('priority_name').addEventListener('input', updatePriorityPreview);
document.getElementById('priorityModal').addEventListener('click', function(e) {
    if (e.target === this) closePriorityModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('priorityModal').style.display === 'block') {
        closePriorityModal();
    }
});
</script>
@endpush
