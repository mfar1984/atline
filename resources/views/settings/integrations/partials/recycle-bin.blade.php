<div x-data="{ showDeleteConfirm: false, deleteDays: 30, showDeleteDropdown: false, showItemDeleteConfirm: false, deleteItemType: '', deleteItemId: 0, deleteItemName: '', showViewModal: false, viewItem: null }">
    <!-- Header with Stats -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Recycle Bin</h3>
            <!-- Total Items -->
            <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded">
                <span class="material-symbols-outlined text-gray-400" style="font-size: 14px;">delete</span>
                <span class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                    {{ $recycleBinStats['total'] ?? 0 }} items
                </span>
            </div>
        </div>
    </div>

    <!-- Search/Filter Form -->
    <div class="mb-4">
        <form action="{{ route('settings.integrations.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="recycle-bin">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, ID..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <select name="type" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                <option value="">All Types</option>
                @foreach($recycleBinTypes ?? [] as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            @permission('settings_integrations_recycle_bin.delete')
            <!-- Delete Dropdown Button -->
            <div class="relative">
                <button type="button" @click="showDeleteDropdown = !showDeleteDropdown" 
                        class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition hover:bg-red-700" 
                        style="min-height: 32px; background-color: #dc2626;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                    DELETE
                    <span class="material-symbols-outlined" style="font-size: 14px;">expand_more</span>
                </button>
                <!-- Dropdown Menu -->
                <div x-show="showDeleteDropdown" @click.away="showDeleteDropdown = false" x-cloak
                     class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded shadow-lg z-50">
                    <button type="button" @click="deleteDays = 30; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        30 Days
                    </button>
                    <button type="button" @click="deleteDays = 60; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        60 Days
                    </button>
                    <button type="button" @click="deleteDays = 90; showDeleteConfirm = true; showDeleteDropdown = false"
                            class="w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100" style="font-family: Poppins, sans-serif;">
                        90 Days
                    </button>
                </div>
            </div>
            @endpermission
        </form>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" @click.away="showDeleteConfirm = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600" style="font-size: 20px;">warning</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Delete Items</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6" style="font-family: Poppins, sans-serif;">
                    Are you sure you want to permanently delete all items older than <span class="font-semibold" x-text="deleteDays + ' days'"></span>? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showDeleteConfirm = false"
                            class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                            style="font-family: Poppins, sans-serif;">
                        Cancel
                    </button>
                    <form action="{{ route('settings.integrations.recycle-bin.bulk-delete') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="days" x-bind:value="deleteDays">
                        <button type="submit"
                                class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700"
                                style="font-family: Poppins, sans-serif;">
                            Delete Items
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Item Delete Confirmation Modal -->
    <div x-data="{ verifyCode: '', expectedCode: '', codeValid: false }" x-show="showItemDeleteConfirm" x-cloak 
         x-init="$watch('showItemDeleteConfirm', value => { if(value) { expectedCode = generateDeleteCode(); verifyCode = ''; codeValid = false; } })"
         class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" @click.away="showItemDeleteConfirm = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-600" style="font-size: 20px;">warning</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Permanent Delete</h3>
                </div>
                <p class="text-sm text-gray-600 mb-4" style="font-family: Poppins, sans-serif;">
                    Are you sure you want to permanently delete "<span class="font-semibold" x-text="deleteItemName"></span>"? This action cannot be undone.
                </p>
                
                <!-- Verification Code Section -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-xs text-red-700 mb-2" style="font-family: Poppins, sans-serif;">
                        To confirm permanent deletion, enter this code:
                    </p>
                    <div class="text-center py-2 px-3 bg-white rounded border border-dashed border-red-300">
                        <span class="font-mono text-2xl font-bold text-red-600 tracking-widest" x-text="expectedCode"></span>
                    </div>
                </div>
                
                <!-- Input Field -->
                <div class="mb-4">
                    <label class="block text-xs text-gray-600 mb-1" style="font-family: Poppins, sans-serif;">Enter verification code</label>
                    <input type="text" 
                           x-model="verifyCode" 
                           @input="verifyCode = verifyCode.toUpperCase(); codeValid = (verifyCode === expectedCode && verifyCode.length === 6)"
                           maxlength="6"
                           placeholder="Enter 6-character code"
                           class="w-full px-3 py-2 text-center font-mono text-lg tracking-widest border rounded focus:outline-none focus:ring-2"
                           :class="verifyCode.length === 6 ? (codeValid ? 'border-green-500 focus:ring-green-200' : 'border-red-500 focus:ring-red-200') : 'border-gray-300 focus:ring-blue-200'"
                           style="font-family: 'Courier New', monospace; text-transform: uppercase;">
                    <p x-show="verifyCode.length === 6 && !codeValid" class="text-xs text-red-600 mt-1" style="font-family: Poppins, sans-serif;">
                        Code does not match. Please try again.
                    </p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showItemDeleteConfirm = false; verifyCode = ''; codeValid = false;"
                            class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                            style="font-family: Poppins, sans-serif;">
                        Cancel
                    </button>
                    <form :action="'/settings/integrations/recycle-bin/' + deleteItemType + '/' + deleteItemId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                :disabled="!codeValid"
                                class="px-4 py-2 text-xs font-medium text-white rounded transition"
                                :class="codeValid ? 'bg-red-600 hover:bg-red-700 cursor-pointer' : 'bg-red-300 cursor-not-allowed'"
                                style="font-family: Poppins, sans-serif;">
                            Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div x-show="showViewModal" x-cloak class="fixed inset-0 flex items-center justify-center" style="background-color: rgba(0,0,0,0.5) !important; z-index: 9999 !important;">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4" @click.away="showViewModal = false">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50 rounded-t-lg">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white" style="font-size: 18px;">visibility</span>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Deleted Item Details</h3>
                </div>
                <button type="button" @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                </button>
            </div>
            <div class="p-5" x-show="viewItem">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1" style="font-size: 10px;">Type</label>
                        <p class="text-xs text-gray-900" x-text="viewItem?.type"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1" style="font-size: 10px;">Original ID</label>
                        <p class="text-xs text-gray-900" x-text="'#' + viewItem?.id"></p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1" style="font-size: 10px;">Name/Title</label>
                        <p class="text-xs text-gray-900" x-text="viewItem?.name"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1" style="font-size: 10px;">Deleted By</label>
                        <p class="text-xs text-gray-900" x-text="viewItem?.deletedBy"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1" style="font-size: 10px;">Deleted At</label>
                        <p class="text-xs text-gray-900" x-text="viewItem?.deletedAt"></p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end bg-gray-50 rounded-b-lg">
                <button type="button" @click="showViewModal = false"
                        class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                        style="font-family: Poppins, sans-serif;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Table using data-table component -->
    <x-ui.data-table
        :headers="[
            ['label' => 'Type', 'align' => 'text-left'],
            ['label' => 'Name/Title', 'align' => 'text-left'],
            ['label' => 'Original ID', 'align' => 'text-left'],
            ['label' => 'Deleted By', 'align' => 'text-left'],
            ['label' => 'Deleted At', 'align' => 'text-left']
        ]"
        :actions="true"
        empty-message="Recycle bin is empty."
    >
        @forelse($recycleBinItems ?? [] as $item)
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded {{ $item->getRecycleBinColor() }}" style="font-size: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 12px;">{{ $item->getRecycleBinIcon() }}</span>
                    {{ $item->getRecycleBinType() }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                {{ $item->getRecycleBinName() }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                #{{ $item->id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                {{ $item->deletedByUser?->name ?? 'System' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
                {{ $item->deleted_at->format('d/m/Y H:i:s') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <x-ui.action-buttons
                    :show-onclick="'viewRecycleItem(' . $item->id . ', \'' . addslashes($item->getRecycleBinType()) . '\', \'' . addslashes($item->getRecycleBinName()) . '\', \'' . ($item->deletedByUser?->name ?? 'System') . '\', \'' . $item->deleted_at->format('d/m/Y H:i:s') . '\')'"
                    :restore-onclick="auth()->user()->hasPermission('settings_integrations_recycle_bin.update') ? 'restoreItem(\'' . $item->recycle_type . '\', ' . $item->id . ')' : null"
                    :delete-onclick="auth()->user()->hasPermission('settings_integrations_recycle_bin.delete') ? 'confirmDeleteItem(\'' . $item->recycle_type . '\', ' . $item->id . ', \'' . addslashes($item->getRecycleBinName()) . '\')' : null"
                />
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-xs">
                <span class="material-symbols-outlined text-gray-300" style="font-size: 48px;">delete_sweep</span>
                <p class="mt-2">Recycle bin is empty.</p>
            </td>
        </tr>
        @endforelse
    </x-ui.data-table>

    <!-- Pagination -->
    @if(isset($recycleBinItems) && $recycleBinItems->hasPages())
    <div class="mt-4">
        <x-ui.custom-pagination :paginator="$recycleBinItems" record-label="items" tab-param="recycle-bin" />
    </div>
    @elseif(isset($recycleBinItems) && $recycleBinItems->total() > 0)
    <div class="mt-4">
        <p class="text-xs text-gray-400" style="font-family: Poppins, sans-serif;">
            Showing {{ $recycleBinItems->firstItem() ?? 0 }} to {{ $recycleBinItems->lastItem() ?? 0 }} of {{ $recycleBinItems->total() }} items
        </p>
    </div>
    @endif

    <!-- Hidden Restore Form -->
    <form id="restoreForm" method="POST" style="display: none;">
        @csrf
    </form>
</div>

@push('scripts')
<script>
function generateDeleteCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}

function viewRecycleItem(id, type, name, deletedBy, deletedAt) {
    const el = document.querySelector('[x-data*="showViewModal"]');
    if (el && el._x_dataStack) {
        const data = el._x_dataStack[0];
        data.viewItem = {
            id: id,
            type: type,
            name: name,
            deletedBy: deletedBy,
            deletedAt: deletedAt
        };
        data.showViewModal = true;
    }
}

function restoreItem(type, id) {
    const form = document.getElementById('restoreForm');
    form.action = '/settings/integrations/recycle-bin/' + type + '/' + id + '/restore';
    form.submit();
}

function confirmDeleteItem(type, id, name) {
    const el = document.querySelector('[x-data*="showItemDeleteConfirm"]');
    if (el && el._x_dataStack) {
        const data = el._x_dataStack[0];
        data.deleteItemType = type;
        data.deleteItemId = id;
        data.deleteItemName = name;
        data.showItemDeleteConfirm = true;
    }
}
</script>
@endpush
