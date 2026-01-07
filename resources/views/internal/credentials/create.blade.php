@extends('layouts.app')

@section('title', 'Add Credential')

@section('page-title', 'Add Credential')

@section('content')
<div class="bg-white border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
        <div>
            <h2 class="text-sm font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Add New Credential</h2>
            <p class="text-xs text-gray-500 mt-0.5" style="font-size: 11px;">Store a new credential securely</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('internal.credentials.index') }}" class="inline-flex items-center px-3 bg-gray-500 text-white text-xs font-medium rounded hover:bg-gray-600 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
            <button type="submit" form="credential-form" id="submit-btn" class="inline-flex items-center px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px; font-family: Poppins, sans-serif; font-size: 11px;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                SAVE
            </button>
        </div>
    </div>

    <!-- Form -->
    <form id="credential-form">
        @csrf
        <div class="p-6">
            <!-- Basic Information Section -->
            <div class="border border-gray-200 rounded">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Basic Information</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                                   style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;"
                                   placeholder="e.g., Production Server SSH">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="type" required onchange="updateFormFields()"
                                    class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                                    style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
                                <option value="">Select Type</option>
                                @foreach($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Notes</label>
                            <textarea name="notes" id="notes" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                                      style="font-family: Poppins, sans-serif; font-size: 11px;"
                                      placeholder="Optional notes about this credential"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Fields Section -->
            <div id="dynamic-section" class="mt-6 border border-gray-200 rounded" style="display: none;">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Credential Details</h3>
                </div>
                <div class="p-4">
                    <div id="dynamic-fields"></div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('js/vault-crypto.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';

const fieldTemplates = {
    ssh: `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Hostname/IP <span class="text-red-500">*</span></label>
                <input type="text" name="hostname" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;" placeholder="192.168.1.100">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Port</label>
                <input type="number" name="port" value="22" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Password</label>
                <input type="password" name="password" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Private Key</label>
            <textarea name="private_key" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 font-mono" style="font-size: 11px;" placeholder="-----BEGIN RSA PRIVATE KEY-----"></textarea>
        </div>
    `,
    windows: `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Hostname/IP <span class="text-red-500">*</span></label>
                <input type="text" name="hostname" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Domain</label>
                <input type="text" name="domain" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;" placeholder="WORKGROUP">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">License Key</label>
            <input type="text" name="license_key" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;" placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
        </div>
    `,
    license_key: `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Software Name <span class="text-red-500">*</span></label>
                <input type="text" name="software_name" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Expiry Date</label>
                <input type="date" name="expiry_date" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">License Key <span class="text-red-500">*</span></label>
            <input type="text" name="license_key" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;" placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Number of Seats</label>
            <input type="number" name="seats" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
    `,
    database: `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Host <span class="text-red-500">*</span></label>
                <input type="text" name="host" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Port</label>
                <input type="number" name="port" value="3306" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Database Name <span class="text-red-500">*</span></label>
                <input type="text" name="database" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Username <span class="text-red-500">*</span></label>
                <input type="text" name="username" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Password <span class="text-red-500">*</span></label>
            <input type="password" name="password" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
    `,
    api_key: `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Service Name <span class="text-red-500">*</span></label>
                <input type="text" name="service_name" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
            <div>
                <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Endpoint URL</label>
                <input type="url" name="endpoint" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">API Key <span class="text-red-500">*</span></label>
            <input type="password" name="api_key" required class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
        <div class="mt-4">
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">API Secret</label>
            <input type="password" name="api_secret" class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500" style="font-family: Poppins, sans-serif; min-height: 32px; font-size: 11px;">
        </div>
    `,
    other: `
        <div>
            <label class="block text-gray-700 mb-1" style="font-size: 11px; font-family: Poppins, sans-serif;">Custom Data (JSON format)</label>
            <textarea name="custom_data" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 font-mono" style="font-size: 11px;" placeholder='{"key": "value"}'></textarea>
        </div>
    `
};

function updateFormFields() {
    const type = document.getElementById('type').value;
    const container = document.getElementById('dynamic-fields');
    const section = document.getElementById('dynamic-section');
    
    if (type && fieldTemplates[type]) {
        container.innerHTML = fieldTemplates[type];
        section.style.display = 'block';
    } else {
        container.innerHTML = '';
        section.style.display = 'none';
    }
}

document.getElementById('credential-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Check if vault is unlocked
    if (!window.vaultCrypto.isVaultUnlocked()) {
        alert('Please unlock your vault first before adding credentials.');
        window.location.href = '{{ route("internal.credentials.index") }}';
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined mr-1 animate-spin" style="font-size: 14px;">sync</span> SAVING...';

    try {
        const formData = new FormData(this);
        const type = formData.get('type');
        const name = formData.get('name');
        const notes = formData.get('notes');

        // Collect sensitive data based on type
        let sensitiveData = {};
        const dynamicFields = document.getElementById('dynamic-fields');
        const inputs = dynamicFields.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            if (input.name && input.value) {
                sensitiveData[input.name] = input.value;
            }
        });

        // Encrypt sensitive data
        const mek = window.vaultCrypto.getMek();
        const { encryptedData, iv } = await window.vaultCrypto.encryptCredential(sensitiveData, mek);

        // Send to server
        const response = await fetch('{{ route("internal.credentials.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: name,
                type: type,
                encrypted_data: encryptedData,
                data_iv: iv,
                notes: notes
            })
        });

        if (response.redirected) {
            window.location.href = response.url;
        } else {
            const data = await response.json();
            if (data.error) {
                alert(data.error);
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span> SAVE';
            } else {
                window.location.href = '{{ route("internal.credentials.index") }}';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save credential: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span> SAVE';
    }
});

// Check vault status on load and restore MEK from session
document.addEventListener('DOMContentLoaded', async function() {
    if (!window.vaultCrypto.isVaultUnlocked()) {
        // Try to restore MEK from session
        const restored = await window.vaultCrypto.restoreMekFromSession();
        
        if (!restored) {
            alert('Please unlock your vault first before adding credentials.');
            window.location.href = '{{ route("internal.credentials.index") }}';
            return;
        }
    }
});
</script>
@endpush
@endsection
