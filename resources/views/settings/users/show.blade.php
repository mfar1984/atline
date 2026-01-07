@extends('layouts.app')

@section('title', 'View User')

@section('page-title', 'View User')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">{{ $user->name }}</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">User details and information</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('settings.users.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            @permission('settings_users.update')
            <a href="{{ route('settings.users.edit', $user) }}" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                EDIT
            </a>
            @endpermission
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Information -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">User Information</h3>
                </div>
                <div class="p-4">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-medium">
                            {{ $user->initials }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900" style="font-family: Poppins, sans-serif;">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Role</label>
                            @if($user->role)
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800" style="font-size: 10px;">
                                    {{ $user->role->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">No role assigned</span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Status</label>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded {{ $user->is_active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}" style="font-size: 10px;">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Information -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Activity Information</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Last Login</label>
                            <p class="text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Never logged in' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Account Created</label>
                            <p class="text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Last Updated</label>
                            <p class="text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                                {{ $user->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credential Vault -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Credential Vault</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Vault Status</label>
                            @if($vaultKey && $vaultKey->is_initialized)
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-600" style="font-size: 10px;">
                                    Initialized
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600" style="font-size: 10px;">
                                    Not Initialized
                                </span>
                            @endif
                        </div>
                        @if($vaultKey && $vaultKey->is_initialized)
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">Current PIN</label>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex px-3 py-1.5 text-sm font-mono font-semibold rounded bg-blue-50 text-blue-700 tracking-wider" style="letter-spacing: 2px;">
                                    {{ $vaultKey->current_pin ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-500 mb-1" style="font-size: 10px; font-family: Poppins, sans-serif;">PIN Expires At</label>
                            <p class="text-xs text-gray-900" style="font-family: Poppins, sans-serif;">
                                {{ $vaultKey->pin_expires_at ? $vaultKey->pin_expires_at->format('d/m/Y H:i') : 'N/A' }}
                                @if($vaultKey->isPinExpired())
                                    <span class="ml-2 inline-flex px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-600" style="font-size: 9px;">Expired</span>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Permissions -->
        @if($user->role)
        <div class="mt-6">
            <x-ui.permission-matrix-view :permissions="$user->role->permissions ?? []" />
        </div>
        @endif
    </div>
</div>
@endsection
