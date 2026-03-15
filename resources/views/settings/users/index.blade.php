@extends('layouts.app')

@section('title', 'User Management')

@section('page-title', 'User Management')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">User Management</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage system users and access</p>
        </div>
        <div class="flex items-center gap-2">
            @permission('settings_users.create')
            <a href="{{ route('settings.users.create') }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">person_add</span>
                USER
            </a>
            @endpermission
        </div>
    </div>

    <div class="px-6 py-3">
        <form action="{{ route('settings.users.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search name or email" 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <select name="role" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('settings.users.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded">
            <p class="text-xs text-green-800" style="font-family: Poppins, sans-serif;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded">
            <p class="text-xs text-red-800" style="font-family: Poppins, sans-serif;">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="px-6">
        <x-ui.data-table
            :headers="[
                ['label' => '', 'align' => 'text-center', 'width' => 'w-16'],
                ['label' => 'Name', 'align' => 'text-left'],
                ['label' => 'Email', 'align' => 'text-left'],
                ['label' => 'Role', 'align' => 'text-center'],
                ['label' => 'Status', 'align' => 'text-center'],
                ['label' => 'Last Login', 'align' => 'text-center'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No users found."
        >
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-medium mx-auto">
                        {{ $user->initials }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900" style="font-family: Poppins, sans-serif; font-size: 12px;">
                        {{ $user->name }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                        {{ $user->email }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($user->role)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800" style="font-size: 10px;">
                            {{ $user->role->name }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $user->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-xs text-gray-500" style="font-family: Poppins, sans-serif;">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    @php
                        $canEdit = auth()->user()->hasPermission('settings_users.update');
                        $canDelete = auth()->user()->hasPermission('settings_users.delete') && $user->id !== auth()->id();
                    @endphp
                    <x-ui.action-buttons
                        :edit-url="$canEdit ? route('settings.users.edit', $user) : null"
                        :show-url="route('settings.users.show', $user)"
                        :delete-onclick="$canDelete ? 'deleteUser(' . $user->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$users" record-label="users" />
    </div>
</div>

@push('scripts')
<script>
function deleteUser(id) {
    window.showDeleteModal('{{ route("settings.users.index") }}/' + id);
}
</script>
@endpush
@endsection
