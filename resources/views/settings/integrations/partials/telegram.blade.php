@php
    $credentials = $telegramSetting->getDecryptedCredentials();
    $isDisabled = !auth()->user()->hasPermission('settings_integrations_telegram.update');
@endphp

<div class="space-y-6">
    <!-- Status Card -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #dbeafe;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #0088cc;">send</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Telegram Bot</h3>
                    <p class="text-xs text-gray-500">Send notifications to Telegram channel</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $telegramSetting->isConnected() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $telegramSetting->getStatusLabel() }}
            </span>
        </div>
        @if($telegramSetting->last_tested_at)
            <p class="text-xs text-gray-400 mt-2">Last tested: {{ $telegramSetting->last_tested_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('settings.integrations.telegram.update') }}" id="telegramForm">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Bot API Token <span class="text-red-500">*</span></label>
            <input type="password" name="bot_token" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="{{ isset($credentials['bot_token']) && !empty($credentials['bot_token']) ? '••••••••' : 'Enter Bot API Token' }}" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">Get from @BotFather on Telegram</p>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Channel ID <span class="text-red-500">*</span></label>
                <input type="text" name="channel_id" value="{{ $credentials['channel_id'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="-1001234567890" {{ $isDisabled ? 'disabled' : '' }}>
                <p class="text-xs text-gray-400 mt-1">Channel/Group ID (starts with -100)</p>
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Bot Username</label>
                <input type="text" name="bot_username" value="{{ $credentials['bot_username'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="@yourbotname" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Owner Username</label>
                <input type="text" name="owner_username" value="{{ $credentials['owner_username'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="@username" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Owner User ID</label>
                <input type="text" name="owner_user_id" value="{{ $credentials['owner_user_id'] ?? '' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="123456789" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center border-t border-gray-200" style="gap: 8px; padding-top: 20px; margin-top: 16px;">
            @permission('settings_integrations_telegram.update')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 text-white rounded hover:opacity-90 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif; background-color: #2563eb;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                Save Settings
            </button>
            @endpermission
            <button type="button" onclick="testTelegramConnection()"
                    class="inline-flex items-center gap-2 px-4 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined" style="font-size: 14px;">wifi_tethering</span>
                Test Connection
            </button>
        </div>
    </form>
</div>

<!-- Test Message Input Modal -->
<div id="testTelegramInputModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5);" onclick="closeTestTelegramInputModal()"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 24px; width: 400px; z-index: 10000;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 40px; height: 40px; background-color: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: #0088cc;">send</span>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Test Telegram Connection</h3>
                <p style="font-size: 11px; color: #6b7280; margin: 0;">Send a test message to your channel</p>
            </div>
        </div>
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-size: 11px; color: #374151; margin-bottom: 4px;">Test Message <span style="color: #ef4444;">*</span></label>
            <textarea id="testTelegramMessage" rows="3"
                   style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 11px; box-sizing: border-box; resize: none;"
                   placeholder="Enter your test message...">🔔 Test notification from ATLINE System

This is a test message to verify Telegram integration is working correctly.

Timestamp: {{ now()->format('d/m/Y H:i:s') }}</textarea>
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button onclick="closeTestTelegramInputModal()" 
                    style="flex: 1; padding: 8px 16px; background-color: #f3f4f6; color: #374151; border-radius: 6px; font-size: 11px; border: none; cursor: pointer;">
                Cancel
            </button>
            <button onclick="sendTestTelegram()" id="sendTestTelegramBtn"
                    style="flex: 1; padding: 8px 16px; background-color: #0088cc; color: white; border-radius: 6px; font-size: 11px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">send</span>
                Send Test Message
            </button>
        </div>
    </div>
</div>

<!-- Test Result Modal -->
<div id="testTelegramResultModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;">
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5);" onclick="closeTelegramTestModal()"></div>
    <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 8px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); padding: 24px; min-width: 320px; z-index: 10000;">
        <div id="testTelegramResultContent"></div>
        <button onclick="closeTelegramTestModal()" style="margin-top: 16px; width: 100%; padding: 8px 16px; background-color: #f3f4f6; color: #374151; border-radius: 6px; font-size: 11px; border: none; cursor: pointer;">
            Close
        </button>
    </div>
</div>

@push('scripts')
<script>
function testTelegramConnection() {
    document.getElementById('testTelegramInputModal').classList.remove('hidden');
}

function closeTestTelegramInputModal() {
    document.getElementById('testTelegramInputModal').classList.add('hidden');
}

function sendTestTelegram() {
    const message = document.getElementById('testTelegramMessage').value;
    if (!message.trim()) {
        alert('Please enter a test message');
        return;
    }
    
    const btn = document.getElementById('sendTestTelegramBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px; animation: spin 1s linear infinite;">progress_activity</span> Sending...';
    
    fetch('{{ route("settings.integrations.telegram.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        closeTestTelegramInputModal();
        showTelegramTestResult(data.success, data.message);
    })
    .catch(error => {
        closeTestTelegramInputModal();
        showTelegramTestResult(false, 'Connection failed: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">send</span> Send Test Message';
    });
}

function showTelegramTestResult(success, message) {
    const content = document.getElementById('testTelegramResultContent');
    content.innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 40px; height: 40px; background-color: ${success ? '#dcfce7' : '#fee2e2'}; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: ${success ? '#16a34a' : '#dc2626'};">${success ? 'check_circle' : 'error'}</span>
            </div>
            <div>
                <h3 style="font-size: 14px; font-weight: 600; color: #111827; margin: 0;">${success ? 'Message Sent Successfully' : 'Failed to Send Message'}</h3>
            </div>
        </div>
        <p style="font-size: 12px; color: #4b5563; margin: 0;">${message}</p>
        ${success ? '<p style="font-size: 11px; color: #6b7280; margin-top: 8px;">Check your Telegram channel for the test message.</p>' : ''}
    `;
    document.getElementById('testTelegramResultModal').classList.remove('hidden');
}

function closeTelegramTestModal() {
    document.getElementById('testTelegramResultModal').classList.add('hidden');
}
</script>
<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
