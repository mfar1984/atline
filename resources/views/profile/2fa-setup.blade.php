@extends('layouts.app')

@section('title', 'Setup Two-Factor Authentication')

@section('page-title', 'Setup Two-Factor Authentication')

@section('content')
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Setup Two-Factor Authentication</h2>
            <p class="text-gray-500 mt-0.5" style="font-size: 11px;">Scan the QR code with your authenticator app</p>
        </div>
        <a href="{{ route('profile.index') }}" class="inline-flex items-center gap-2 px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-size: 11px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
            BACK
        </a>
    </div>

    <div class="p-6">
        <div class="space-y-6">
            <!-- Step 1: Install App -->
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-semibold" style="font-size: 11px;">1</span>
                </div>
                <div>
                    <h3 class="text-gray-900" style="font-size: 11px; font-weight: 500;">Install an authenticator app</h3>
                    <p class="text-gray-500 mt-1" style="font-size: 11px;">Download and install an authenticator app on your phone:</p>
                    <ul class="text-gray-500 mt-2 space-y-1" style="font-size: 11px;">
                        <li>• Google Authenticator (iOS / Android)</li>
                        <li>• Microsoft Authenticator (iOS / Android)</li>
                        <li>• Authy (iOS / Android / Desktop)</li>
                    </ul>
                </div>
            </div>

            <!-- Step 2: Scan QR Code -->
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-semibold" style="font-size: 11px;">2</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-gray-900" style="font-size: 11px; font-weight: 500;">Scan the QR code</h3>
                    <p class="text-gray-500 mt-1" style="font-size: 11px;">Open your authenticator app and scan this QR code:</p>
                    
                    <div class="mt-4 flex items-start gap-6">
                        <!-- QR Code - Left aligned -->
                        <div class="p-3 bg-white border border-gray-200 rounded-lg">
                            {!! $qrCodeSvg !!}
                        </div>
                        
                        <!-- Manual Code - Right side -->
                        <div class="flex-1">
                            <p class="text-gray-500" style="font-size: 11px;">Or enter this code manually:</p>
                            <div class="mt-2 p-3 bg-gray-100 rounded font-mono tracking-wider select-all" style="font-size: 11px;">
                                {{ $secret }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Verify -->
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-semibold" style="font-size: 11px;">3</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-gray-900" style="font-size: 11px; font-weight: 500;">Verify the code</h3>
                    <p class="text-gray-500 mt-1" style="font-size: 11px;">Enter the 6-digit code from your authenticator app:</p>
                    
                    <form action="{{ route('profile.2fa.enable') }}" method="POST" class="mt-4">
                        @csrf
                        <div class="flex gap-3">
                            <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" required
                                   class="w-32 px-3 border border-gray-300 rounded text-center tracking-widest focus:outline-none focus:border-blue-500 @error('code') border-red-500 @enderror"
                                   style="min-height: 32px; font-size: 11px; font-family: monospace;"
                                   placeholder="000000"
                                   autocomplete="off">
                            <button type="submit" class="inline-flex items-center px-4 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-size: 11px;">
                                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">verified</span>
                                VERIFY & ENABLE
                            </button>
                        </div>
                        @error('code')
                            <p class="text-red-500 mt-2" style="font-size: 11px;">{{ $message }}</p>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
