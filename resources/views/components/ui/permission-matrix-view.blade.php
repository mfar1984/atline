@props([
    'permissions' => [],
])

@php
    $permissionLabels = config('permissions.labels', []);
    $permissionMatrix = config('permissions.matrix', []);
    $moduleLabels = config('permissions.modules', []);
@endphp

<div>
    <h3 class="text-sm font-semibold text-gray-900 mb-4" style="font-family: Poppins, sans-serif;">
        Permission Matrix
    </h3>

    <div class="overflow-x-auto border border-gray-200 rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200" style="font-size: 10px; min-width: 250px;">
                        Module
                    </th>
                    @foreach($permissionLabels as $key => $label)
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
                    @foreach($permissionLabels as $permission => $permLabel)
                    <td class="px-3 py-3 text-center border-r border-gray-200">
                        @if(isset($modulePermissions[$permission]) && $modulePermissions[$permission])
                            @php
                                $permissionKey = "{$module}.{$permission}";
                                $hasPermission = is_array($permissions) && in_array($permissionKey, $permissions);
                            @endphp
                            @if($hasPermission)
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs font-medium" style="background-color: #dcfce7; color: #166534;">
                                    ✓
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded text-xs font-medium" style="background-color: #fee2e2; color: #991b1b;">
                                    ✗
                                </span>
                            @endif
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
</div>
