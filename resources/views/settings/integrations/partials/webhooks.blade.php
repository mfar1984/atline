@php
    $credentials = $webhookSetting->getDecryptedCredentials();
    $events = $webhookSetting->settings['events'] ?? [];
    $isDisabled = !auth()->user()->hasPermission('settings_integrations_webhook.update');
@endphp

<div class="space-y-6">
    <!-- Status Card -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #e0e7ff;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #4f46e5;">webhook</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Webhooks</h3>
                    <p class="text-xs text-gray-500">Event Notifications</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $webhookSetting->isConnected() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $webhookSetting->getStatusLabel() }}
            </span>
        </div>
        @if($webhookSetting->last_tested_at)
            <p class="text-xs text-gray-400 mt-2">Last tested: {{ $webhookSetting->last_tested_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('settings.integrations.webhook.update') }}">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Webhook URL</label>
            <input type="url" name="webhook_url" value="{{ $credentials['webhook_url'] ?? '' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="https://your-domain.com/webhook" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">URL that will receive webhook events</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Webhook Secret</label>
            <div class="flex items-center gap-2">
                <input type="text" name="webhook_secret" id="webhookSecret" value="{{ $credentials['webhook_secret'] ?? '' }}" 
                       class="flex-1 px-3 border border-gray-300 rounded text-gray-700 font-mono focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="Click generate to create secret" {{ $isDisabled ? 'disabled' : '' }}>
                @permission('settings_integrations_webhook.update')
                <button type="button" onclick="generateWebhookSecret()"
                        class="inline-flex items-center px-3 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
                        style="min-height: 32px;" title="Generate new secret">
                    <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                </button>
                @endpermission
            </div>
            <p class="text-xs text-gray-400 mt-1">Use this secret to verify webhook signatures</p>
        </div>

        <!-- Webhook Events -->
        <div style="margin-bottom: 24px;">
            <label class="block text-gray-700" style="font-size: 11px; margin-bottom: 8px;">Events to Send</label>
            <div class="bg-gray-50 rounded-lg" style="padding: 16px;">
                <label class="flex items-center {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}" style="gap: 12px; margin-bottom: 12px;">
                    <input type="checkbox" name="events[]" value="project.created" style="width: 16px; height: 16px;" {{ in_array('project.created', $events) ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">project.created - When a new project is created</span>
                </label>
                <label class="flex items-center {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}" style="gap: 12px; margin-bottom: 12px;">
                    <input type="checkbox" name="events[]" value="project.updated" style="width: 16px; height: 16px;" {{ in_array('project.updated', $events) ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">project.updated - When a project is updated</span>
                </label>
                <label class="flex items-center {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}" style="gap: 12px; margin-bottom: 12px;">
                    <input type="checkbox" name="events[]" value="asset.created" style="width: 16px; height: 16px;" {{ in_array('asset.created', $events) ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">asset.created - When a new asset is created</span>
                </label>
                <label class="flex items-center {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}" style="gap: 12px; margin-bottom: 12px;">
                    <input type="checkbox" name="events[]" value="asset.updated" style="width: 16px; height: 16px;" {{ in_array('asset.updated', $events) ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">asset.updated - When an asset is updated</span>
                </label>
                <label class="flex items-center {{ $isDisabled ? 'opacity-60' : 'cursor-pointer' }}" style="gap: 12px;">
                    <input type="checkbox" name="events[]" value="user.created" style="width: 16px; height: 16px;" {{ in_array('user.created', $events) ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}>
                    <span style="font-size: 11px;">user.created - When a new user is created</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center border-t border-gray-200" style="gap: 8px; padding-top: 20px; margin-top: 8px;">
            @permission('settings_integrations_webhook.update')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 text-white rounded hover:opacity-90 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif; background-color: #4f46e5;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                Save Settings
            </button>
            @endpermission
            <button type="button" onclick="testWebhookConnection()"
                    class="inline-flex items-center gap-2 px-4 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined" style="font-size: 14px;">wifi_tethering</span>
                Test Connection
            </button>
        </div>
    </form>

    <!-- Payload Example -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-3" style="font-family: Poppins, sans-serif;">Payload Example</h3>
        <p class="text-xs text-gray-500 mb-4">Example payload that will be sent to your webhook URL:</p>
        
        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
            <pre class="text-xs font-mono" style="color: #4ade80;">{
  "event": "project.created",
  "timestamp": "{{ now()->toIso8601String() }}",
  "data": {
    "project_id": "PRJ-{{ now()->format('Ymd') }}001",
    "name": "Sample Project",
    "client": "Client Name"
  },
  "signature": "sha256=..."
}</pre>
        </div>
        
        <div class="mt-4 p-3 rounded-lg" style="background-color: #eff6ff; border: 1px solid #bfdbfe;">
            <div class="flex items-start gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px; color: #2563eb;">info</span>
                <div class="text-xs" style="color: #1e40af;">
                    <p class="font-medium">Verify Webhook Signature</p>
                    <p class="mt-1">Use the webhook secret to verify the signature in the <code class="px-1 rounded" style="background-color: #dbeafe;">X-Webhook-Signature</code> header</p>
                </div>
            </div>
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
function generateWebhookSecret() {
    const secret = 'whsec_' + Array.from(crypto.getRandomValues(new Uint8Array(24)))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');
    document.getElementById('webhookSecret').value = secret;
}

function testWebhookConnection() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size: 14px;">progress_activity</span> Testing...';
    
    fetch('{{ route("settings.integrations.webhook.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        showTestResult(data.success, data.message);
    })
    .catch(error => {
        showTestResult(false, 'Connection failed: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">wifi_tethering</span> Test Connection';
    });
}

function showTestResult(success, message) {
    const content = document.getElementById('testResultContent');
    content.innerHTML = `
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: ${success ? '#dcfce7' : '#fee2e2'};">
                <span class="material-symbols-outlined" style="font-size: 20px; color: ${success ? '#16a34a' : '#dc2626'};">${success ? 'check_circle' : 'error'}</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">${success ? 'Connection Successful' : 'Connection Failed'}</h3>
            </div>
        </div>
        <p class="text-xs text-gray-600">${message}</p>
    `;
    document.getElementById('testResultModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testResultModal').classList.add('hidden');
}
</script>
@endpush
