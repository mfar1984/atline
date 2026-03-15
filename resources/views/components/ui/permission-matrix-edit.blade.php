@props([
    'permissions' => [],
])

@php
    $permissionLabels = config('permissions.labels', []);
    $permissionMatrix = config('permissions.matrix', []);
    $moduleLabels = config('permissions.modules', []);
@endphp

<div>
    <!-- Header -->
    <div class="my-6">
        <div class="border-t border-gray-200"></div>
        <h3 class="text-sm font-semibold text-gray-900 mt-6 mb-2" style="font-family: Poppins, sans-serif;">
            Permission Matrix
        </h3>
        <p class="text-xs text-gray-500 mb-4" style="font-family: Poppins, sans-serif;">
            Set access permissions for each module in the system
        </p>
    </div>

    <!-- Permission Matrix Table -->
    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" style="font-size: 10px; min-width: 250px;">
                        Module
                    </th>
                    @foreach($permissionLabels as $action => $label)
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" style="font-size: 10px; min-width: 70px;">
                        {{ $label }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($permissionMatrix as $module => $modulePermissions)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-gray-900 border-r border-gray-200" style="font-family: Poppins, sans-serif;">
                        {{ $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module)) }}
                    </td>
                    @foreach($permissionLabels as $action => $label)
                    <td class="px-3 py-3 text-center border-r border-gray-200">
                        @if(isset($modulePermissions[$action]) && $modulePermissions[$action])
                        @php
                            $permissionKey = "{$module}.{$action}";
                            $isChecked = is_array($permissions) && in_array($permissionKey, $permissions);
                        @endphp
                        <input
                            type="checkbox"
                            name="permission_matrix[{{ $module }}][{{ $action }}]"
                            value="1"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            {{ old("permission_matrix.{$module}.{$action}", $isChecked) ? 'checked' : '' }}
                        >
                        @else
                        <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div class="flex justify-between items-center mt-4 p-3 bg-gray-50 rounded border border-gray-200">
        <div class="text-xs text-gray-600" style="font-family: Poppins, sans-serif;">
            Quick Actions:
        </div>
        <div class="flex gap-4">
            <button type="button" onclick="selectAllPermissions()" class="text-xs text-blue-600 hover:text-blue-800" style="font-family: Poppins, sans-serif;">
                Select All
            </button>
            <button type="button" onclick="clearAllPermissions()" class="text-xs text-red-600 hover:text-red-800" style="font-family: Poppins, sans-serif;">
                Clear All
            </button>
            <button type="button" onclick="selectViewOnly()" class="text-xs text-green-600 hover:text-green-800" style="font-family: Poppins, sans-serif;">
                View Only
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectAllPermissions() {
    document.querySelectorAll('input[name^="permission_matrix"]').forEach(function(checkbox) {
        checkbox.checked = true;
    });
}

function clearAllPermissions() {
    document.querySelectorAll('input[name^="permission_matrix"]').forEach(function(checkbox) {
        checkbox.checked = false;
    });
}

function selectViewOnly() {
    document.querySelectorAll('input[name^="permission_matrix"]').forEach(function(checkbox) {
        if (checkbox.name.includes('[view]')) {
            checkbox.checked = true;
        } else {
            checkbox.checked = false;
        }
    });
}
</script>
@endpush
