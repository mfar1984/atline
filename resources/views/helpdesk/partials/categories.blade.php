<!-- Header with Add Button -->
<div class="flex items-center justify-between mb-4">
    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Category List</h3>
    @permission('helpdesk_categories.create')
    <button type="button" onclick="openCategoryModal()" 
            class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition"
            style="min-height: 32px; background-color: #2563eb;">
        <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
        ADD CATEGORY
    </button>
    @endpermission
</div>

<!-- Search/Filter Form -->
<div class="mb-4">
    <form action="{{ route('helpdesk.index') }}" method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="categories">
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
        <button type="button" onclick="window.location.href='{{ route('helpdesk.index', ['tab' => 'categories']) }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
            <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
            RESET
        </button>
    </form>
</div>

<!-- Categories Table -->
<div class="overflow-x-auto border border-gray-200 rounded">
    <table class="w-full divide-y divide-gray-200" style="table-layout: fixed;">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 20%;">Category</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 35%;">Description</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 15%;">Color</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Tickets</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Status</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="font-size: 10px; width: 10%;">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($ticketCategories as $category)
            <tr class="hover:bg-gray-50 {{ !$category->is_active ? 'opacity-50' : '' }}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center flex-shrink-0" style="width: 28px; height: 28px; border-radius: 6px; background-color: {{ $category->color }}20;">
                            <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $category->color }};">{{ $category->icon }}</span>
                        </div>
                        <span class="text-xs font-medium text-gray-900 truncate" style="font-family: Poppins, sans-serif;">{{ $category->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs text-gray-600 line-clamp-2" style="font-family: Poppins, sans-serif;">{{ $category->description ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <div style="width: 20px; height: 20px; border-radius: 4px; background-color: {{ $category->color }};"></div>
                        <span class="text-xs text-gray-500" style="font-family: monospace;">{{ $category->color }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ $category->tickets_count }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $category->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-ui.action-buttons
                        :edit-onclick="auth()->user()->hasPermission('helpdesk_categories.update') ? 'editCategory(' . $category->id . ', ' . json_encode(['name' => $category->name, 'description' => $category->description, 'color' => $category->color, 'icon' => $category->icon]) . ')' : null"
                        :delete-onclick="auth()->user()->hasPermission('helpdesk_categories.delete') ? 'deleteCategory(' . $category->id . ')' : null"
                        :more-actions="auth()->user()->hasPermission('helpdesk_categories.update') ? [
                            ['label' => $category->is_active ? 'Deactivate' : 'Activate', 'icon' => $category->is_active ? 'toggle_off' : 'toggle_on', 'onclick' => 'toggleCategoryStatus(' . $category->id . ')']
                        ] : []"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-xs">
                    <span class="material-symbols-outlined text-gray-300" style="font-size: 40px;">category</span>
                    <p class="mt-2">No categories found. Click "ADD CATEGORY" to create one.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($ticketCategories->hasPages())
<div class="mt-4">
    <x-ui.custom-pagination :paginator="$ticketCategories" record-label="categories" />
</div>
@endif


<!-- Category Modal -->
<div id="categoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; border-radius: 8px; width: 480px; max-height: 90vh; overflow-y: auto; z-index: 10000;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 id="categoryModalTitle" style="font-size: 14px; font-weight: 600; color: #111827; font-family: Poppins, sans-serif;">Add Category</h3>
        </div>
        <form id="categoryForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="categoryMethod" value="POST">
            <div style="padding: 20px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Name *</label>
                    <input type="text" name="name" id="category_name" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; min-height: 32px; font-family: Poppins, sans-serif;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 4px; font-family: Poppins, sans-serif;">Description</label>
                    <textarea name="description" id="category_description" rows="2"
                              style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; font-family: Poppins, sans-serif;"></textarea>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Color *</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @php
                            $colors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#64748b', '#374151', '#1f2937'];
                        @endphp
                        @foreach($colors as $color)
                        <button type="button" onclick="selectColor('{{ $color }}')" class="color-option" style="width: 32px; height: 32px; border-radius: 6px; background-color: {{ $color }}; border: 2px solid transparent; cursor: pointer;" data-color="{{ $color }}"></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="color" id="category_color" value="#6366f1" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedColorPreview" style="width: 24px; height: 24px; border-radius: 4px; background-color: #6366f1; border: 1px solid #e5e7eb;"></div>
                        <span id="selectedColorCode" style="font-size: 10px; color: #374151; font-family: monospace;">#6366f1</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 500; color: #374151; margin-bottom: 8px; font-family: Poppins, sans-serif;">Icon *</label>
                    <div style="display: grid !important; grid-template-columns: repeat(10, 1fr) !important; gap: 6px !important; max-height: 150px; overflow-y: auto; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                        @php
                            $icons = ['category', 'label', 'bookmark', 'star', 'favorite', 'bug_report', 'build', 'code', 'computer', 'devices', 'dns', 'extension', 'help', 'info', 'lightbulb', 'memory', 'monitor', 'mouse', 'phone_android', 'print', 'router', 'security', 'settings', 'storage', 'support', 'sync', 'terminal', 'update', 'verified', 'warning', 'wifi', 'work', 'folder', 'description', 'article', 'assignment', 'chat', 'email', 'forum', 'message', 'notifications', 'person', 'group', 'business', 'shopping_cart'];
                        @endphp
                        @foreach($icons as $icon)
                        <button type="button" onclick="selectIcon('{{ $icon }}')" class="icon-option" style="width: 100% !important; aspect-ratio: 1 !important; border-radius: 6px; background-color: #f3f4f6; border: 2px solid transparent; cursor: pointer; display: flex !important; align-items: center !important; justify-content: center !important;" data-icon="{{ $icon }}">
                            <span class="material-symbols-outlined" style="font-size: 18px !important; color: #374151;">{{ $icon }}</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="icon" id="category_icon" value="category" required>
                    <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 10px; color: #6b7280;">Selected:</span>
                        <div id="selectedIconPreview" style="width: 28px; height: 28px; border-radius: 4px; background-color: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">category</span>
                        </div>
                        <span id="selectedIconName" style="font-size: 10px; color: #374151; font-family: monospace;">category</span>
                    </div>
                </div>
                
                <div style="padding: 12px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                    <label style="display: block; font-size: 10px; font-weight: 500; color: #6b7280; margin-bottom: 8px; text-transform: uppercase;">Preview</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="previewBadge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 6px; background-color: #6366f120;">
                            <span id="previewIcon" class="material-symbols-outlined" style="font-size: 16px; color: #6366f1;">category</span>
                            <span id="previewName" style="font-size: 11px; font-weight: 500; color: #6366f1;">Category Name</span>
                        </div>
                    </div>
                </div>
            </div>
            <div style="padding: 16px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" onclick="closeCategoryModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px; background: white; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">Save</button>
            </div>
        </form>
    </div>
</div>

<x-modals.delete-confirmation />

@push('scripts')
<script>
let selectedColor = '#6366f1';
let selectedIcon = 'category';

function openCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').action = '{{ route("helpdesk.categories.store") }}';
    document.getElementById('categoryMethod').value = 'POST';
    document.getElementById('categoryForm').reset();
    selectColor('#6366f1');
    selectIcon('category');
    updatePreview();
    document.getElementById('categoryModal').style.display = 'block';
}

function editCategory(id, data) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('categoryForm').action = '/helpdesk/categories/' + id;
    document.getElementById('categoryMethod').value = 'PUT';
    document.getElementById('category_name').value = data.name || '';
    document.getElementById('category_description').value = data.description || '';
    selectColor(data.color || '#6366f1');
    selectIcon(data.icon || 'category');
    updatePreview();
    document.getElementById('categoryModal').style.display = 'block';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

function selectColor(color) {
    selectedColor = color;
    document.getElementById('category_color').value = color;
    document.getElementById('selectedColorPreview').style.backgroundColor = color;
    document.getElementById('selectedColorCode').textContent = color;
    document.querySelectorAll('.color-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.color === color ? '#1f2937' : 'transparent';
        btn.style.transform = btn.dataset.color === color ? 'scale(1.1)' : 'scale(1)';
    });
    updatePreview();
}

function selectIcon(icon) {
    selectedIcon = icon;
    document.getElementById('category_icon').value = icon;
    document.getElementById('selectedIconPreview').innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px; color: #374151;">' + icon + '</span>';
    document.getElementById('selectedIconName').textContent = icon;
    document.querySelectorAll('.icon-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.icon === icon ? '#2563eb' : 'transparent';
        btn.style.backgroundColor = btn.dataset.icon === icon ? '#dbeafe' : '#f3f4f6';
    });
    updatePreview();
}

function updatePreview() {
    const name = document.getElementById('category_name').value || 'Category Name';
    document.getElementById('previewBadge').style.backgroundColor = selectedColor + '20';
    document.getElementById('previewIcon').textContent = selectedIcon;
    document.getElementById('previewIcon').style.color = selectedColor;
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewName').style.color = selectedColor;
}

function deleteCategory(id) {
    window.showDeleteConfirmation({
        title: 'Delete Category',
        message: 'Are you sure you want to delete this category? This action cannot be undone.',
        formAction: '/helpdesk/categories/' + id,
        method: 'DELETE'
    });
}

function toggleCategoryStatus(id) {
    fetch('/helpdesk/categories/' + id + '/toggle-status', {
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

document.getElementById('category_name').addEventListener('input', updatePreview);
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeCategoryModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('categoryModal').style.display === 'block') {
        closeCategoryModal();
    }
});
</script>
@endpush
