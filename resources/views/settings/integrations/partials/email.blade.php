@php
    $credentials = $emailSetting->getDecryptedCredentials();
    $provider = $emailSetting->provider ?: 'smtp';
    $isDisabled = !auth()->user()->hasPermission('settings_integrations_email.update');
@endphp

<div class="space-y-6">
    <!-- Status Card -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #dbeafe;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #2563eb;">mail</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Email Service</h3>
                    <p class="text-xs text-gray-500">SMTP or Google OAuth</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $emailSetting->isConnected() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $emailSetting->getStatusLabel() }}
            </span>
        </div>
        @if($emailSetting->last_tested_at)
            <p class="text-xs text-gray-400 mt-2">Last tested: {{ $emailSetting->last_tested_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('settings.integrations.email.update') }}" id="emailForm">
        @csrf
        
        <!-- Provider Selection -->
        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Provider</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}">
                    <input type="radio" name="provider" value="smtp" {{ $provider === 'smtp' ? 'checked' : '' }} 
                           class="w-4 h-4 text-blue-600" onchange="toggleEmailProvider()" {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">SMTP</span>
                </label>
                <label class="flex items-center gap-2 {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}">
                    <input type="radio" name="provider" value="google" {{ $provider === 'google' ? 'checked' : '' }} 
                           class="w-4 h-4 text-blue-600" onchange="toggleEmailProvider()" {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">Google OAuth</span>
                </label>
            </div>
        </div>

        <!-- SMTP Fields -->
        <div id="smtpFields" class="{{ $provider !== 'smtp' ? 'hidden' : '' }}">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 mb-1" style="font-size: 11px;">SMTP Host <span class="text-red-500">*</span></label>
                    <input type="text" name="host" value="{{ $credentials['host'] ?? '' }}" 
                           class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                           style="min-height: 32px; font-size: 11px;" placeholder="smtp.gmail.com" {{ $isDisabled ? 'disabled' : '' }}>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" style="font-size: 11px;">Port <span class="text-red-500">*</span></label>
                    <input type="number" name="port" value="{{ $credentials['port'] ?? '587' }}" 
                           class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                           style="min-height: 32px; font-size: 11px;" placeholder="587" {{ $isDisabled ? 'disabled' : '' }}>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 mb-1" style="font-size: 11px;">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" value="{{ $credentials['username'] ?? '' }}" 
                           class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                           style="min-height: 32px; font-size: 11px;" placeholder="your@email.com" {{ $isDisabled ? 'disabled' : '' }}>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" style="font-size: 11px;">Password</label>
                    <input type="password" name="password" 
                           class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                           style="min-height: 32px; font-size: 11px;" placeholder="{{ isset($credentials['password']) ? '••••••••' : 'Enter password' }}" {{ $isDisabled ? 'disabled' : '' }}>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Encryption <span class="text-red-500">*</span></label>
                <select name="encryption" 
                        class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                        style="min-height: 32px; font-size: 11px;" {{ $isDisabled ? 'disabled' : '' }}>
                    <option value="tls" {{ ($credentials['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ ($credentials['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ ($credentials['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        <!-- Google OAuth Fields -->
        <div id="googleFields" class="{{ $provider !== 'google' ? 'hidden' : '' }}">
            <div class="mb-4">
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Google Client ID <span class="text-red-500">*</span></label>
                <input type="text" name="google_client_id" value="{{ $credentials['google_client_id'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="your-client-id.apps.googleusercontent.com" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Google Client Secret</label>
                <input type="password" name="google_client_secret" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="{{ isset($credentials['google_client_secret']) ? '••••••••' : 'Enter client secret' }}" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
        </div>

        <!-- Common Fields -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">From Address <span class="text-red-500">*</span></label>
                <input type="email" name="from_address" value="{{ $credentials['from_address'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="noreply@company.com" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">From Name <span class="text-red-500">*</span></label>
                <input type="text" name="from_name" value="{{ $credentials['from_name'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="ATLINE System" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center border-t border-gray-200" style="gap: 8px; padding-top: 20px; margin-top: 16px;">
            @permission('settings_integrations_email.update')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 text-white rounded hover:opacity-90 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif; background-color: #2563eb;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                Save Settings
            </button>
            @endpermission
            <button type="button" onclick="testEmailConnection()"
                    class="inline-flex items-center gap-2 px-4 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined" style="font-size: 14px;">wifi_tethering</span>
                Test Connection
            </button>
        </div>
    </form>
</div>

<!-- Test Email Input Modal -->
<div id="testEmailInputModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5);" onclick="closeTestEmailInputModal()"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 24px; width: 400px; z-index: 10000;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 40px; height: 40px; background-color: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: #2563eb;">mail</span>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Test Email Connection</h3>
                <p style="font-size: 11px; color: #6b7280; margin: 0;">Enter email address to receive test email</p>
            </div>
        </div>
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 11px; color: #374151; margin-bottom: 4px;">Email Address <span style="color: #ef4444;">*</span></label>
            <input type="email" id="testEmailAddress" 
                   style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 11px; box-sizing: border-box;"
                   placeholder="Enter your email address">
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button onclick="closeTestEmailInputModal()" 
                    style="flex: 1; padding: 8px 16px; background-color: #f3f4f6; color: #374151; border-radius: 6px; font-size: 11px; border: none; cursor: pointer;">
                Cancel
            </button>
            <button onclick="sendTestEmail()" id="sendTestEmailBtn"
                    style="flex: 1; padding: 8px 16px; background-color: #2563eb; color: white; border-radius: 6px; font-size: 11px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">send</span>
                Send Test Email
            </button>
        </div>
    </div>
</div>

<!-- Test Result Modal -->
<div id="testResultModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5);" onclick="closeTestModal()"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 24px; min-width: 320px; z-index: 10000;">
        <div id="testResultContent"></div>
        <button onclick="closeTestModal()" style="margin-top: 16px; width: 100%; padding: 8px 16px; background-color: #f3f4f6; color: #374151; border-radius: 6px; font-size: 11px; border: none; cursor: pointer;">
            Close
        </button>
    </div>
</div>

@push('scripts')
<script>
function toggleEmailProvider() {
    const provider = document.querySelector('input[name="provider"]:checked').value;
    document.getElementById('smtpFields').classList.toggle('hidden', provider !== 'smtp');
    document.getElementById('googleFields').classList.toggle('hidden', provider !== 'google');
}

function testEmailConnection() {
    // Show email input modal
    document.getElementById('testEmailInputModal').classList.remove('hidden');
    document.getElementById('testEmailAddress').focus();
}

function closeTestEmailInputModal() {
    document.getElementById('testEmailInputModal').classList.add('hidden');
    document.getElementById('testEmailAddress').value = '';
}

function sendTestEmail() {
    const email = document.getElementById('testEmailAddress').value;
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    
    const btn = document.getElementById('sendTestEmailBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px; animation: spin 1s linear infinite;">progress_activity</span> Sending...';
    
    fetch('{{ route("settings.integrations.email.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        closeTestEmailInputModal();
        showTestResult(data.success, data.message);
    })
    .catch(error => {
        closeTestEmailInputModal();
        showTestResult(false, 'Connection failed: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">send</span> Send Test Email';
    });
}

function showTestResult(success, message) {
    const content = document.getElementById('testResultContent');
    content.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 40px; height: 40px; background-color: ${success ? '#dcfce7' : '#fee2e2'}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: ${success ? '#16a34a' : '#dc2626'};">${success ? 'check_circle' : 'error'}</span>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">${success ? 'Email Sent Successfully' : 'Failed to Send Email'}</h3>
            </div>
        </div>
        <p style="font-size: 12px; color: #4b5563; margin: 0;">${message}</p>
        ${success ? '<p style="font-size: 11px; color: #6b7280; margin-top: 8px;">Please check your inbox (and spam folder) for the test email.</p>' : ''}
    `;
    document.getElementById('testResultModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testResultModal').classList.add('hidden');
}
</script>
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
