@extends('layouts.app')

@section('title', 'View Role')

@section('page-title', 'View Role')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">{{ $role->name }}</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Role details and permissions</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.roles.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            @permission('settings_roles.update')
            <a href="{{ route('settings.roles.edit', $role) }}" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                EDIT
            </a>
            @endpermission
        </div>
    </div>

    <div class="p-6">
        <!-- Role Information -->
        <div class="border border-gray-200 rounded">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Role Information</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Role Name</label>
                        <p class="text-sm font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $role->name }}</p>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Status</label>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $role->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Users Assigned</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $role->users_count ?? 0 }} users
                        </span>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Description</label>
                        <p class="text-xs text-gray-700" style="font-family: Poppins, sans-serif;">{{ $role->description ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permission Matrix -->
        <div class="mt-6">
            <x-ui.permission-matrix-view :permissions="$role->permissions ?? []" />
        </div>
    </div>
</div>
@endsection
