@extends('layouts.app')

@section('title', 'My Profile')

@section('page-title', 'My Profile')

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
        {{ session('error') }}
    </div>
    @endif

    @if(session('info'))
    <div class="px-4 py-3 bg-blue-50 border border-blue-200 text-blue-700 rounded text-xs">
        {{ session('info') }}
    </div>
    @endif

    <!-- 2FA Required Warning -->
    @if($require2FA && !$has2FAEnabled)
    <div class="px-4 py-3 bg-amber-50 border border-amber-200 text-amber-700 rounded text-xs flex items-center gap-2">
        <span class="material-symbols-outlined" style="font-size: 18px;">warning</span>
        <span>Two-factor authentication is required by system policy. Please enable it to continue using the system.</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">person</span>
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Profile Information</h3>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('name') border-red-500 @enderror"
                               style="min-height: 32px; font-size: 11px;">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('email') border-red-500 @enderror"
                               style="min-height: 32px; font-size: 11px;">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Role</label>
                        <input type="text" value="{{ $user->role->name ?? 'No Role' }}" disabled
                               class="w-full px-3 border border-gray-300 rounded text-xs bg-gray-100"
                               style="min-height: 32px; font-size: 11px;">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-size: 11px;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                            UPDATE PROFILE
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">lock</span>
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Change Password</h3>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                               style="min-height: 32px; font-size: 11px;">
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 @error('password') border-red-500 @enderror"
                               style="min-height: 32px; font-size: 11px;">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                               style="min-height: 32px; font-size: 11px;">
                    </div>
                    <p class="text-xs text-gray-400">Minimum {{ $passwordMinLength }} characters</p>
                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-size: 11px;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">lock_reset</span>
                            CHANGE PASSWORD
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Two-Factor Authentication -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">security</span>
            <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Two-Factor Authentication</h3>
        </div>
        <div class="p-4">
            @if($has2FAEnabled)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600" style="font-size: 20px;">verified_user</span>
                        </div>
                        <div>
                            <p class="text-gray-900" style="font-size: 11px; font-weight: 500;">Two-factor authentication is enabled</p>
                            <p class="text-gray-500" style="font-size: 11px;">Your account is protected with an authenticator app</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.2fa.recovery') }}" class="inline-flex items-center px-3 bg-gray-100 text-gray-700 text-xs font-medium rounded hover:bg-gray-200 transition" style="min-height: 32px; font-size: 11px;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">key</span>
                            VIEW RECOVERY CODES
                        </a>
                        @if(!$require2FA)
                        <button type="button" onclick="document.getElementById('disable-2fa-modal').classList.remove('hidden')" class="inline-flex items-center px-3 bg-red-50 text-red-600 text-xs font-medium rounded hover:bg-red-100 transition" style="min-height: 32px; font-size: 11px;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">remove_moderator</span>
                            DISABLE 2FA
                        </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-400" style="font-size: 20px;">shield</span>
                        </div>
                        <div>
                            <p class="text-gray-900" style="font-size: 11px; font-weight: 500;">Two-factor authentication is not enabled</p>
                            <p class="text-gray-500" style="font-size: 11px;">Add an extra layer of security to your account</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.2fa.setup') }}" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-size: 11px;">
                        <span class="material-symbols-outlined mr-1" style="font-size: 14px;">add_moderator</span>
                        ENABLE 2FA
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
            <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">info</span>
            <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Account Information</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-gray-500" style="font-size: 11px;">Account Created</p>
                    <p class="text-gray-900" style="font-size: 11px;">@formatDateTime($user->created_at)</p>
                </div>
                <div>
                    <p class="text-gray-500" style="font-size: 11px;">Last Login</p>
                    <p class="text-gray-900" style="font-size: 11px;">{{ $user->last_login_at ? \App\Helpers\FormatHelper::datetime($user->last_login_at) : 'Never' }}</p>
                </div>
                <div>
                    <p class="text-gray-500" style="font-size: 11px;">Account Status</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}" style="font-size: 11px;">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div id="disable-2fa-modal" class="fixed inset-0 hidden" style="background-color: rgba(0,0,0,0.5); z-index: 99999;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full" style="z-index: 100000;">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Disable Two-Factor Authentication</h3>
            </div>
            <form action="{{ route('profile.2fa.disable') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="p-4 space-y-4">
                    <p class="text-gray-600" style="font-size: 11px;">Enter your password to confirm disabling two-factor authentication.</p>
                    <div>
                        <label class="block text-gray-700 mb-1" style="font-size: 11px;">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required
                               class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                               style="min-height: 32px; font-size: 11px;">
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('disable-2fa-modal').classList.add('hidden')" class="px-3 bg-gray-200 text-gray-700 text-xs font-medium rounded hover:bg-gray-300 transition" style="min-height: 32px; font-size: 11px;">
                        CANCEL
                    </button>
                    <button type="submit" class="px-3 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition" style="min-height: 32px; font-size: 11px;">
                        DISABLE 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
