@extends('layouts.app')

@section('title', 'System Configuration')

@section('page-title', 'System Configuration')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">System Configuration</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Configure system-wide settings</p>
        </div>
        @if($canUpdate)
        <button type="submit" form="config-form" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
            SAVE CHANGES
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded text-xs">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded text-xs">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Branding Form (separate for file uploads) -->
    <form id="branding-form" action="{{ route('settings.configuration.update-branding') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 pb-0">
            <!-- Branding Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">palette</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Branding</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Icon/Favicon -->
                        <div>
                            <label class="block text-gray-700 mb-2" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">tab</span>
                                    Favicon / Icon
                                </span>
                            </label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <div class="flex items-center justify-center mb-3">
                                    <div class="w-16 h-16 border border-gray-300 rounded bg-white flex items-center justify-center overflow-hidden">
                                        <img src="{{ \App\Models\SystemSetting::iconPath() }}" alt="Current Icon" class="max-w-full max-h-full object-contain" id="icon-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="material-symbols-outlined text-gray-300" style="font-size: 32px; display: none;">image</span>
                                    </div>
                                </div>
                                @if($canUpdate)
                                <input type="file" name="icon" id="icon-input" accept=".ico,.png,.jpg,.jpeg,.svg" class="hidden" onchange="previewImage(this, 'icon-preview')">
                                <label for="icon-input" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded text-xs text-center cursor-pointer hover:bg-gray-50 transition">
                                    <span class="material-symbols-outlined align-middle mr-1" style="font-size: 14px;">upload</span>
                                    Choose File
                                </label>
                                @endif
                                <p class="text-xs text-gray-400 mt-2 text-center">Browser tab icon (ICO, PNG, 32x32px)</p>
                            </div>
                        </div>
                        
                        <!-- Logo -->
                        <div>
                            <label class="block text-gray-700 mb-2" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">image</span>
                                    Logo
                                </span>
                            </label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <div class="flex items-center justify-center mb-3">
                                    <div class="w-32 h-16 border border-gray-300 rounded bg-white flex items-center justify-center overflow-hidden">
                                        <img src="{{ \App\Models\SystemSetting::logoPath() }}" alt="Current Logo" class="max-w-full max-h-full object-contain" id="logo-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="material-symbols-outlined text-gray-300" style="font-size: 32px; display: none;">image</span>
                                    </div>
                                </div>
                                @if($canUpdate)
                                <input type="file" name="logo" id="logo-input" accept=".png,.jpg,.jpeg,.svg" class="hidden" onchange="previewImage(this, 'logo-preview')">
                                <label for="logo-input" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded text-xs text-center cursor-pointer hover:bg-gray-50 transition">
                                    <span class="material-symbols-outlined align-middle mr-1" style="font-size: 14px;">upload</span>
                                    Choose File
                                </label>
                                @endif
                                <p class="text-xs text-gray-400 mt-2 text-center">Sidebar & Login (PNG, 200x80px)</p>
                            </div>
                        </div>
                        
                        <!-- Hero Image -->
                        <div>
                            <label class="block text-gray-700 mb-2" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">wallpaper</span>
                                    Hero Image
                                </span>
                            </label>
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <div class="flex items-center justify-center mb-3">
                                    <div class="w-32 h-16 border border-gray-300 rounded bg-white flex items-center justify-center overflow-hidden">
                                        <img src="{{ \App\Models\SystemSetting::heroImagePath() }}" alt="Current Hero" class="max-w-full max-h-full object-cover" id="hero-preview" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <span class="material-symbols-outlined text-gray-300" style="font-size: 32px; display: none;">image</span>
                                    </div>
                                </div>
                                @if($canUpdate)
                                <input type="file" name="hero_image" id="hero-input" accept=".png,.jpg,.jpeg" class="hidden" onchange="previewImage(this, 'hero-preview')">
                                <label for="hero-input" class="block w-full px-3 py-2 bg-white border border-gray-300 rounded text-xs text-center cursor-pointer hover:bg-gray-50 transition">
                                    <span class="material-symbols-outlined align-middle mr-1" style="font-size: 14px;">upload</span>
                                    Choose File
                                </label>
                                @endif
                                <p class="text-xs text-gray-400 mt-2 text-center">Login background (JPG/PNG, 1920x1080px)</p>
                            </div>
                        </div>
                    </div>
                    @if($canUpdate)
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                            <span class="material-symbols-outlined mr-1" style="font-size: 14px;">upload</span>
                            UPLOAD BRANDING
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <!-- Settings Form -->
    <form id="config-form" action="{{ route('settings.configuration.update') }}" method="POST">
        @csrf
        <div class="p-6 space-y-6">
            
            <!-- Company Information -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">business</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Company Information</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">System Name <span class="text-red-500">*</span></label>
                            <input type="text" name="system_name" value="{{ $settings['company']['system_name'] ?? 'Atline Administration System' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required
                                   placeholder="e.g. Atline Administration System">
                            <p class="text-xs text-gray-400 mt-1">Displayed in the system topbar</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Company Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_name" value="{{ $settings['company']['name'] ?? 'Atline Sdn Bhd' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Short Name <span class="text-red-500">*</span></label>
                            <input type="text" name="company_short_name" value="{{ $settings['company']['short_name'] ?? 'ATLINE' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">SSM Number</label>
                            <input type="text" name="company_ssm_number" value="{{ $settings['company']['ssm_number'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" placeholder="e.g. 123456-X" {{ !$canUpdate ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Email</label>
                            <input type="email" name="company_email" value="{{ $settings['company']['email'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Phone</label>
                            <input type="text" name="company_phone" value="{{ $settings['company']['phone'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }}>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Website</label>
                            <input type="url" name="company_website" value="{{ $settings['company']['website'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }}>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Address</label>
                            <textarea name="company_address" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                      style="font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }}>{{ $settings['company']['address'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Regional Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">language</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Regional Settings</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Timezone <span class="text-red-500">*</span></label>
                            <select name="timezone" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach($timezones as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['regional']['timezone'] ?? 'Asia/Kuala_Lumpur') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Date Format <span class="text-red-500">*</span></label>
                            <select name="date_format" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach($dateFormats as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['regional']['date_format'] ?? 'd/m/Y') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Time Format <span class="text-red-500">*</span></label>
                            <select name="time_format" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach($timeFormats as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['regional']['time_format'] ?? 'H:i') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Currency <span class="text-red-500">*</span></label>
                            <select name="currency" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach($currencies as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['regional']['currency'] ?? 'MYR') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Currency Symbol <span class="text-red-500">*</span></label>
                            <input type="text" name="currency_symbol" value="{{ $settings['regional']['currency_symbol'] ?? 'RM' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Language <span class="text-red-500">*</span></label>
                            <select name="language" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach($languages as $value => $label)
                                    <option value="{{ $value }}" {{ ($settings['regional']['language'] ?? 'en') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">security</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Security Settings</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Session Timeout (minutes) <span class="text-red-500">*</span></label>
                            <input type="number" name="session_timeout" value="{{ $settings['security']['session_timeout'] ?? 120 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="15" max="480" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">15 - 480 minutes</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Min Password Length <span class="text-red-500">*</span></label>
                            <input type="number" name="password_min_length" value="{{ $settings['security']['password_min_length'] ?? 8 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="6" max="32" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">6 - 32 characters</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Max Login Attempts <span class="text-red-500">*</span></label>
                            <input type="number" name="max_login_attempts" value="{{ $settings['security']['max_login_attempts'] ?? 5 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="3" max="10" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Before lockout</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Lockout Duration (min) <span class="text-red-500">*</span></label>
                            <input type="number" name="lockout_duration" value="{{ $settings['security']['lockout_duration'] ?? 15 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="5" max="60" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">5 - 60 minutes</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="require_2fa" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['security']['require_2fa'] ?? false) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Require Two-Factor Authentication</span>
                                <p class="text-xs text-gray-400">All users must enable 2FA to access the system</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>


            <!-- Notification Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">notifications</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Email Notification Settings</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="email_ticket_created" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['notification']['email_ticket_created'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Ticket Created</span>
                                <p class="text-xs text-gray-400">When new ticket is created</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="email_ticket_replied" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['notification']['email_ticket_replied'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Ticket Replied</span>
                                <p class="text-xs text-gray-400">When ticket receives reply</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="email_ticket_status_changed" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['notification']['email_ticket_status_changed'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Status Changed</span>
                                <p class="text-xs text-gray-400">When ticket status changes</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="email_ticket_assigned" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['notification']['email_ticket_assigned'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Ticket Assigned</span>
                                <p class="text-xs text-gray-400">When ticket is assigned</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="email_user_created" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['notification']['email_user_created'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">User Created</span>
                                <p class="text-xs text-gray-400">When new user is created</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Rate Limiter Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">shield</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Rate Limiter Settings</h3>
                    <span class="ml-auto px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">Redis</span>
                </div>
                <div class="p-4">
                    <div class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-amber-600" style="font-size: 16px;">info</span>
                            <p class="text-xs text-amber-700">Rate limiter melindungi login page daripada serangan brute force. Hanya terpakai untuk guest (pelawat yang belum login).</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Login Page Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="rate_limit_login_page" value="{{ $settings['rate_limiter']['login_page_limit'] ?? 10 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="5" max="60" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Request/minit (GET /login)</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Login Attempt Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="rate_limit_login_attempt" value="{{ $settings['rate_limiter']['login_attempt_limit'] ?? 5 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="3" max="20" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Cubaan/minit (POST /login)</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Guest Protection Limit <span class="text-red-500">*</span></label>
                            <input type="number" name="rate_limit_guest_protection" value="{{ $settings['rate_limiter']['guest_protection_limit'] ?? 20 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="10" max="100" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Request/minit (semua guest)</p>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 w-full {{ !$canUpdate ? 'opacity-60' : '' }}">
                                <input type="checkbox" name="rate_limiter_enabled" value="1" 
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       {{ ($settings['rate_limiter']['enabled'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                                <div>
                                    <span class="text-gray-700" style="font-size: 11px;">Aktifkan Rate Limiter</span>
                                    <p class="text-xs text-gray-400">On/Off protection</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Defaults -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">tune</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">System Defaults</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Pagination Size <span class="text-red-500">*</span></label>
                            <select name="pagination_size" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                    style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                                @foreach([10, 15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ ($settings['defaults']['pagination_size'] ?? 15) == $size ? 'selected' : '' }}>{{ $size }} items per page</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Auto-Close Tickets (days) <span class="text-red-500">*</span></label>
                            <input type="number" name="ticket_auto_close_days" value="{{ $settings['defaults']['ticket_auto_close_days'] ?? 7 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="1" max="30" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">After resolved status</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Max Attachment Size (MB) <span class="text-red-500">*</span></label>
                            <input type="number" name="attachment_max_size" value="{{ $settings['defaults']['attachment_max_size'] ?? 10 }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" min="1" max="50" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Per file upload</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Allowed File Types <span class="text-red-500">*</span></label>
                            <input type="text" name="allowed_file_types" value="{{ $settings['defaults']['allowed_file_types'] ?? 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,zip' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" {{ !$canUpdate ? 'disabled' : '' }} required>
                            <p class="text-xs text-gray-400 mt-1">Comma separated</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Line Settings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-500" style="font-size: 18px;">support_agent</span>
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Help Line</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- WhatsApp -->
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    WhatsApp Number
                                </span>
                            </label>
                            <input type="text" name="helpline_whatsapp" value="{{ $settings['helpline']['whatsapp'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" placeholder="60166337231" {{ !$canUpdate ? 'disabled' : '' }}>
                            <p class="text-xs text-gray-400 mt-1">Format: 60166337231 (with country code)</p>
                        </div>
                        
                        <!-- Telegram -->
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                    </svg>
                                    Telegram ID
                                </span>
                            </label>
                            <input type="text" name="helpline_telegram" value="{{ $settings['helpline']['telegram'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" placeholder="@Fahmmie85" {{ !$canUpdate ? 'disabled' : '' }}>
                            <p class="text-xs text-gray-400 mt-1">Format: @username</p>
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px;">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">mail</span>
                                    Support Email
                                </span>
                            </label>
                            <input type="email" name="helpline_email" value="{{ $settings['helpline']['email'] ?? '' }}" 
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 {{ !$canUpdate ? 'bg-gray-100' : '' }}"
                                   style="min-height: 32px; font-size: 11px;" placeholder="help@atline.com.my" {{ !$canUpdate ? 'disabled' : '' }}>
                            <p class="text-xs text-gray-400 mt-1">Support email address</p>
                        </div>
                    </div>
                    
                    <!-- Widget Settings -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 {{ !$canUpdate ? 'opacity-60' : '' }}">
                            <input type="checkbox" name="helpline_widget_enabled" value="1" 
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                   {{ ($settings['helpline']['widget_enabled'] ?? true) ? 'checked' : '' }} {{ !$canUpdate ? 'disabled' : '' }}>
                            <div>
                                <span class="text-gray-700" style="font-size: 11px;">Show Floating Contact Widget on Login Page</span>
                                <p class="text-xs text-gray-400">Display help line widget at bottom-right corner of login page</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const placeholder = preview.nextElementSibling;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
