<!-- Header -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Email Templates</h3>
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('helpdesk.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="templates">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search title, description..." 
                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
        <select name="recipient_type" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <option value="">All Recipients</option>
            <option value="client" {{ request('recipient_type') == 'client' ? 'selected' : '' }}>Client</option>
            <option value="staff" {{ request('recipient_type') == 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="admin" {{ request('recipient_type') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
            SEARCH
        </button>
        <button type="button" onclick="window.location.href='{{ route('helpdesk.index', ['tab' => 'templates']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Templates Table -->
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="w-full divide-y divide-gray-200" style="table-layout: fixed;">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 25%;">Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 30%;">Description</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Recipient</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Updated By</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Updated</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($emailTemplates as $template)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center flex-shrink-0" style="width: 28px; height: 28px; border-radius: 6px; background-color: {{ $template->recipient_type == 'client' ? '#dbeafe' : ($template->recipient_type == 'staff' ? '#dcfce7' : '#f3e8ff') }};">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $template->recipient_type == 'client' ? '#2563eb' : ($template->recipient_type == 'staff' ? '#16a34a' : '#9333ea') }};">mail</span>
                        </div>
                        <span class="text-xs font-medium text-gray-900" style="font-family: monospace;">{{ $template->title }}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-600 line-clamp-2" style="font-family: Poppins, sans-serif;">{{ $template->description ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $template->recipient_badge_color }}" style="font-size: 10px;">
                        {{ $template->recipient_label }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                        {{ $template->updatedByUser?->name ?? '-' }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                        {{ $template->updated_at->format('d/m/Y H:i') }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-ui.action-buttons
                        :edit-onclick="auth()->user()->hasPermission('helpdesk_templates.update') ? 'editTemplate(' . $template->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-xs">
                    <span class="material-symbols-outlined text-gray-300" style="font-size: 40px;">mail</span>
                    <p class="mt-2">No email templates found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($emailTemplates->hasPages())
<div class="mt-4">
    <x-ui.custom-pagination :paginator="$emailTemplates" record-label="templates" />
</div>
@endif

<!-- Edit Template Modal -->
<div id="templateModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border-radius: 8px; width: 700px; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="font-size: 14px; font-weight: 600; color: #111827; font-family: Poppins, sans-serif;">Edit Email Template</h3>
            <button type="button" onclick="closeTemplateModal()" style="background: none; border: none; cursor: pointer; padding: 4px;">
                <span class="material-symbols-outlined text-gray-400 hover:text-gray-600" style="font-size: 20px;">close</span>
            </button>
        </div>
        <form id="templateForm" method="POST">
            @csrf
            @method('PUT')
            <div style="padding: 20px;">
                <!-- Template Info -->
                <div style="padding: 12px; background-color: #f9fafb; border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 4px 12px; font-size: 11px;">
                        <span style="color: #6b7280;">Template:</span>
                        <span id="modal-template-title" style="font-weight: 500; color: #1f2937; font-family: monospace;"></span>
                        <span style="color: #6b7280;">Description:</span>
                        <span id="modal-template-desc" style="color: #374151;"></span>
                        <span style="color: #6b7280;">Recipient:</span>
                        <span id="modal-template-recipient"></span>
                    </div>
                </div>

                <!-- Subject -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Email Subject *</label>
                    <input type="text" name="subject" id="template_subject" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px; font-family: Poppins, sans-serif;">
                </div>

                <!-- Content -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Email Content *</label>
                    <textarea name="content" id="template_content" required rows="12"
                              style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; font-family: monospace; line-height: 1.5; resize: vertical;"></textarea>
                </div>

                <!-- Available Tags -->
                <div style="padding: 12px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">
                    <label style="display: block; font-size: 10px; font-weight: 600; color: #1e40af; margin-bottom: 8px; text-transform: uppercase;">
                        <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; margin-right: 4px;">info</span>
                        Insert special tag (will be replaced with actual info)
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <button type="button" onclick="insertTag('name')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{name}}</button>
                        <button type="button" onclick="insertTag('first_name')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{first_name}}</button>
                        <button type="button" onclick="insertTag('ticket_number')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{ticket_number}}</button>
                        <button type="button" onclick="insertTag('ticket_subject')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{ticket_subject}}</button>
                        <button type="button" onclick="insertTag('ticket_status')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{ticket_status}}</button>
                        <button type="button" onclick="insertTag('ticket_priority')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{ticket_priority}}</button>
                        <button type="button" onclick="insertTag('ticket_url')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{ticket_url}}</button>
                        <button type="button" onclick="insertTag('num_tickets')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{num_tickets}}</button>
                        <button type="button" onclick="insertTag('list_tickets')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{list_tickets}}</button>
                        <button type="button" onclick="insertTag('reply_content')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{reply_content}}</button>
                        <button type="button" onclick="insertTag('site_title')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{site_title}}</button>
                        <button type="button" onclick="insertTag('site_url')" class="tag-btn" style="padding: 4px 8px; font-size: 10px; background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; border-radius: 4px; cursor: pointer; font-family: monospace;">@{{site_url}}</button>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeTemplateModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: white; cursor: pointer; font-family: Poppins, sans-serif;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer; font-family: Poppins, sans-serif;">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentTemplateId = null;

function editTemplate(id) {
    currentTemplateId = id;
    
    // Fetch template data
    fetch('/helpdesk/templates/' + id + '/edit', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const template = data.template;
            document.getElementById('templateForm').action = '/helpdesk/templates/' + id;
            document.getElementById('modal-template-title').textContent = template.title;
            document.getElementById('modal-template-desc').textContent = template.description;
            
            // Set recipient badge
            const recipientColors = {
                'client': 'background-color: #dbeafe; color: #1e40af;',
                'staff': 'background-color: #dcfce7; color: #166534;',
                'admin': 'background-color: #f3e8ff; color: #6b21a8;'
            };
            const recipientLabels = { 'client': 'Client', 'staff': 'Staff', 'admin': 'Admin' };
            document.getElementById('modal-template-recipient').innerHTML = 
                '<span style="padding: 2px 8px; border-radius: 9999px; font-size: 10px; ' + recipientColors[template.recipient_type] + '">' + 
                recipientLabels[template.recipient_type] + '</span>';
            
            document.getElementById('template_subject').value = template.subject;
            document.getElementById('template_content').value = template.content;
            
            document.getElementById('templateModal').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load template');
    });
}

function closeTemplateModal() {
    document.getElementById('templateModal').style.display = 'none';
    currentTemplateId = null;
}

function insertTag(tag) {
    const textarea = document.getElementById('template_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const tagText = '{{' + tag + '}}';
    
    textarea.value = text.substring(0, start) + tagText + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + tagText.length;
    textarea.focus();
}

// Close modal on outside click
document.getElementById('templateModal').addEventListener('click', function(e) {
    if (e.target === this) closeTemplateModal();
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('templateModal').style.display === 'block') {
        closeTemplateModal();
    }
});
</script>
@endpush
