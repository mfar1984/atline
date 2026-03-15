@php
    $credentials = $weatherSetting->getDecryptedCredentials();
    $isDisabled = !auth()->user()->hasPermission('settings_integrations_weather.update');
@endphp

<div class="space-y-6">
    <!-- Status Card -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #fef9c3;">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #ca8a04;">wb_sunny</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Weather Service</h3>
                    <p class="text-xs text-gray-500">OpenWeatherMap API</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $weatherSetting->isConnected() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $weatherSetting->getStatusLabel() }}
            </span>
        </div>
        @if($weatherSetting->last_tested_at)
            <p class="text-xs text-gray-400 mt-2">Last tested: {{ $weatherSetting->last_tested_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-lg" style="background-color: #fefce8; border: 1px solid #fef08a;">
        <div class="flex items-start gap-2">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #ca8a04;">info</span>
            <div class="text-xs" style="color: #854d0e;">
                <p class="font-medium">OpenWeatherMap API</p>
                <p class="mt-1">Get your free API key from <a href="https://openweathermap.org/api" target="_blank" class="underline">OpenWeatherMap</a></p>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('settings.integrations.weather.update') }}">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">API Key <span class="text-red-500">*</span></label>
            <input type="password" name="api_key" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="{{ isset($credentials['api_key']) ? '••••••••' : 'Enter your API Key' }}" {{ $isDisabled ? 'disabled' : '' }}>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Default City</label>
                <input type="text" name="default_city" value="{{ $credentials['default_city'] ?? 'Kuala Lumpur' }}" 
                       class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                       style="min-height: 32px; font-size: 11px;" placeholder="Kuala Lumpur" {{ $isDisabled ? 'disabled' : '' }}>
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px;">Units <span class="text-red-500">*</span></label>
                <select name="units" 
                        class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                        style="min-height: 32px; font-size: 11px;" {{ $isDisabled ? 'disabled' : '' }}>
                    <option value="metric" {{ ($credentials['units'] ?? 'metric') === 'metric' ? 'selected' : '' }}>Metric (°C)</option>
                    <option value="imperial" {{ ($credentials['units'] ?? '') === 'imperial' ? 'selected' : '' }}>Imperial (°F)</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center border-t border-gray-200" style="gap: 8px; padding-top: 20px; margin-top: 16px;">
            @permission('settings_integrations_weather.update')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 text-white rounded hover:opacity-90 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif; background-color: #ca8a04;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                Save Settings
            </button>
            @endpermission
            <button type="button" onclick="testWeatherConnection()"
                    class="inline-flex items-center gap-2 px-4 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined" style="font-size: 14px;">wifi_tethering</span>
                Test Connection
            </button>
        </div>
    </form>
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
function testWeatherConnection() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size: 14px;">progress_activity</span> Testing...';
    
    fetch('{{ route("settings.integrations.weather.test") }}', {
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
