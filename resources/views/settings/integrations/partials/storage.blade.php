@php
    $credentials = $storageSetting->getDecryptedCredentials();
    $isDisabled = !auth()->user()->hasPermission('settings_integrations_storage.update');
@endphp

<div class="space-y-6">
    <!-- Storage Usage Card -->
    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #f3f4f6;">
                    <span class="material-symbols-outlined" style="font-size: 22px; color: #6b7280;">cloud</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Cloudflare R2 Storage</h3>
                    <p class="text-xs text-gray-500">{{ $storageUsage['total_files'] ?? 0 }} files uploaded</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium {{ $storageSetting->isConnected() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $storageSetting->isConnected() ? '● Connected' : '○ Not Connected' }}
            </span>
        </div>
        
        <!-- Storage Bar -->
        <div class="mb-4">
            <div class="flex justify-between text-xs text-gray-600 mb-1.5">
                <span>Used: {{ $storageUsage['used_formatted'] ?? '0 B' }}</span>
                <span>{{ $storageUsage['usage_percent'] ?? 0 }}% of 10 GB</span>
            </div>
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 bg-blue-500" 
                     style="width: {{ max($storageUsage['usage_percent'] ?? 0, 1) }}%;"></div>
            </div>
        </div>
        
        <!-- Storage Stats -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white rounded-lg p-3 border border-gray-200 text-center">
                <p class="text-lg font-bold text-gray-900">{{ $storageUsage['used_formatted'] ?? '0 B' }}</p>
                <p class="text-xs text-gray-500">Used</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-green-200 text-center" style="background-color: #f0fdf4;">
                <p class="text-lg font-bold text-green-600">{{ $storageUsage['free_formatted'] ?? '10 GB' }}</p>
                <p class="text-xs text-gray-500">Free</p>
            </div>
            <div class="bg-white rounded-lg p-3 border border-gray-200 text-center">
                <p class="text-lg font-bold text-gray-900">{{ $storageUsage['total_formatted'] ?? '10 GB' }}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
        </div>
        
        @if($storageSetting->last_tested_at)
            <p class="text-xs text-gray-400 mt-3 text-right">Last tested: {{ $storageSetting->last_tested_at->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-lg" style="background-color: #fff7ed; border: 1px solid #fed7aa;">
        <div class="flex items-start gap-2">
            <span class="material-symbols-outlined" style="font-size: 18px; color: #f97316;">info</span>
            <div class="text-xs" style="color: #9a3412;">
                <p class="font-medium">Cloudflare R2 Setup (FREE - 10GB Storage)</p>
                <p class="mt-1">1. Go to <a href="https://dash.cloudflare.com/" target="_blank" class="underline">Cloudflare Dashboard</a> → R2</p>
                <p>2. Create a bucket (e.g., "atline-downloads")</p>
                <p>3. Go to R2 → Manage R2 API Tokens → Create API Token</p>
                <p>4. Copy Account ID, Access Key ID, and Secret Access Key</p>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <form method="POST" action="{{ route('settings.integrations.storage.update') }}">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Account ID <span class="text-red-500">*</span></label>
            <input type="text" name="account_id" value="{{ $credentials['account_id'] ?? '' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="Your Cloudflare Account ID" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">Found in Cloudflare Dashboard → R2 → Account ID</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Access Key ID <span class="text-red-500">*</span></label>
            <input type="text" name="access_key_id" value="{{ $credentials['access_key_id'] ?? '' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="R2 Access Key ID" {{ $isDisabled ? 'disabled' : '' }}>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Secret Access Key <span class="text-red-500">*</span></label>
            <input type="password" name="secret_access_key" value="" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="{{ !empty($credentials['secret_access_key']) ? '••••••••••••••••' : 'R2 Secret Access Key' }}" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">Leave empty to keep existing key</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Bucket Name <span class="text-red-500">*</span></label>
            <input type="text" name="bucket_name" value="{{ $credentials['bucket_name'] ?? '' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="your-bucket-name" {{ $isDisabled ? 'disabled' : '' }}>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Folder Path (Optional)</label>
            <input type="text" name="folder_path" value="{{ $credentials['folder_path'] ?? 'downloads' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="downloads" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">Files will be uploaded to this folder in the bucket</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px;">Public URL (Optional)</label>
            <input type="text" name="public_url" value="{{ $credentials['public_url'] ?? '' }}" 
                   class="w-full px-3 border border-gray-300 rounded text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 {{ $isDisabled ? 'bg-gray-100' : '' }}"
                   style="min-height: 32px; font-size: 11px;" placeholder="https://your-domain.r2.dev" {{ $isDisabled ? 'disabled' : '' }}>
            <p class="text-xs text-gray-400 mt-1">Custom domain or R2.dev subdomain for public access</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center border-t border-gray-200" style="gap: 8px; padding-top: 20px; margin-top: 16px;">
            @permission('settings_integrations_storage.update')
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-4 text-white rounded hover:opacity-90 transition"
                    style="min-height: 32px; font-size: 11px; font-family: Poppins, sans-serif; background-color: #f97316;">
                <span class="material-symbols-outlined" style="font-size: 14px;">save</span>
                Save Settings
            </button>
            @endpermission
            <button type="button" onclick="testStorageConnection()"
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
function testStorageConnection() {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size: 14px;">progress_activity</span> Testing...';
    
    fetch('{{ route("settings.integrations.storage.test") }}', {
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
