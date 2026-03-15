@extends('layouts.app')

@section('title', 'Edit Credential')

@section('page-title', 'Edit Credential')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Edit Credential</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update credential information</p>
        </div>
        <a href="{{ route('internal.credentials.show', $credential) }}" 
           class="inline-flex items-center gap-2 px-3 text-gray-700 text-xs font-medium rounded hover:bg-gray-100 transition border border-gray-300"
           style="min-height: 32px;">
            <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
            BACK
        </a>
    </div>

    <!-- Vault Status -->
    <div id="vault-locked" class="px-6 py-4">
        <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs text-yellow-800" style="font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined align-middle" style="font-size: 14px;">lock</span>
                Vault is locked. 
                <button type="button" onclick="showUnlockModal()" class="text-blue-600 underline">Unlock</button> to edit credential.
            </p>
        </div>
    </div>

    <form id="credential-form" class="px-6 py-4" style="display: none;">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1" style="font-family: Poppins, sans-serif;">Name *</label>
                <input type="text" name="name" id="name" required value="{{ $credential->name }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif;">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1" style="font-family: Poppins, sans-serif;">Type *</label>
                <select name="type" id="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 bg-gray-100"
                        style="font-family: Poppins, sans-serif;" disabled>
                    @foreach($types as $value => $label)
                    <option value="{{ $value }}" {{ $credential->type == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="type" value="{{ $credential->type }}">
            </div>
        </div>

        <!-- Dynamic Fields Container -->
        <div id="dynamic-fields" class="mb-4"></div>

        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-700 mb-1" style="font-family: Poppins, sans-serif;">Notes</label>
            <textarea name="notes" id="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                      style="font-family: Poppins, sans-serif;">{{ $credential->notes }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200">
            <a href="{{ route('internal.credentials.show', $credential) }}" 
               class="inline-flex items-center px-3 text-xs font-medium rounded transition border border-gray-300"
               style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #f3f4f6; color: #374151;">
                CANCEL
            </a>
            <button type="submit" id="submit-btn"
                    class="inline-flex items-center px-3 text-xs font-medium rounded transition"
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #2563eb; color: white;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span>
                UPDATE CREDENTIAL
            </button>
        </div>
    </form>
</div>

<!-- Unlock Vault Modal -->
<div id="unlock-modal" class="delete-modal" style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center;">
    <div class="delete-modal-backdrop" style="position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.5);" onclick="closeUnlockModal()"></div>
    <div class="delete-modal-content" style="position: relative; background: white; border-radius: 6px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 400px; width: 90%; margin: 1rem;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: #059669;">lock_open</span>
                <h3 style="font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Unlock Vault</h3>
            </div>
            <button type="button" onclick="closeUnlockModal()" style="padding: 0.25rem; color: #6b7280; cursor: pointer; border: none; background: none;">
                <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
            </button>
        </div>
        <div style="padding: 1.25rem;">
            <p style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; margin: 0 0 1rem 0; line-height: 1.5;">
                Enter your 8-digit PIN to unlock the vault:
            </p>
            <input type="password" id="pin-input" maxlength="8" pattern="[0-9]*" inputmode="numeric"
                   class="w-full px-3 py-2 border border-gray-300 rounded text-center text-lg tracking-widest focus:outline-none focus:border-blue-500"
                   style="font-family: 'Courier New', monospace; letter-spacing: 8px;"
                   placeholder="••••••••">
            <p id="pin-error" style="font-family: Poppins, sans-serif; font-size: 11px; color: #dc2626; margin: 0.5rem 0 0 0; display: none;"></p>
        </div>
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" onclick="closeUnlockModal()" 
                    class="inline-flex items-center px-3 text-xs font-medium rounded transition" 
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #f3f4f6; color: #374151;">
                CANCEL
            </button>
            <button type="button" onclick="unlockVault()"
                    class="inline-flex items-center px-3 text-xs font-medium rounded transition" 
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #059669; color: white;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">lock_open</span>
                UNLOCK
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/vault-crypto.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const credentialId = {{ $credential->id }};
const credentialType = '{{ $credential->type }}';

const fieldTemplates = {
    ssh: ['hostname', 'port', 'username', 'password', 'private_key'],
    windows: ['hostname', 'domain', 'username', 'password', 'license_key'],
    license_key: ['software_name', 'license_key', 'expiry_date', 'seats'],
    database: ['host', 'port', 'database', 'username', 'password'],
    api_key: ['service_name', 'endpoint', 'api_key', 'api_secret'],
    other: ['custom_data']
};

function showUnlockModal() {
    document.getElementById('unlock-modal').style.display = 'flex';
    document.getElementById('pin-input').value = '';
    document.getElementById('pin-error').style.display = 'none';
    document.getElementById('pin-input').focus();
}

function closeUnlockModal() {
    document.getElementById('unlock-modal').style.display = 'none';
}

async function unlockVault() {
    const pin = document.getElementById('pin-input').value;
    const errorEl = document.getElementById('pin-error');

    if (pin.length !== 8 || !/^\d+$/.test(pin)) {
        errorEl.textContent = 'PIN must be exactly 8 digits';
        errorEl.style.display = 'block';
        return;
    }

    try {
        const response = await fetch('{{ route("internal.credentials.verify-pin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ pin: pin })
        });

        const data = await response.json();

        if (data.success) {
            const pinKey = await window.vaultCrypto.deriveKeyFromPin(pin, data.pin_salt);
            const mek = await window.vaultCrypto.decryptMek(data.encrypted_mek, data.mek_iv, pinKey);
            
            window.vaultCrypto.setMek(mek);
            await window.vaultCrypto.storeMekInSession(mek);

            closeUnlockModal();
            loadCredentialData();
        } else {
            errorEl.textContent = data.error || 'Invalid PIN';
            errorEl.style.display = 'block';
        }
    } catch (error) {
        console.error('Unlock error:', error);
        errorEl.textContent = 'Failed to unlock vault';
        errorEl.style.display = 'block';
    }
}

async function loadCredentialData() {
    document.getElementById('vault-locked').style.display = 'none';
    document.getElementById('credential-form').style.display = 'block';

    try {
        const response = await fetch('{{ route("internal.credentials.encrypted-data", $credential) }}', {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (data.encrypted_data) {
            const mek = window.vaultCrypto.getMek();
            const decrypted = await window.vaultCrypto.decryptCredential(data.encrypted_data, data.data_iv, mek);
            
            buildFormFields(decrypted);
        }
    } catch (error) {
        console.error('Error loading credential:', error);
        alert('Failed to decrypt credential data');
    }
}

function buildFormFields(data) {
    const container = document.getElementById('dynamic-fields');
    const fields = fieldTemplates[credentialType] || [];
    
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
    
    fields.forEach(field => {
        const label = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const value = data[field] || '';
        const isSecret = ['password', 'private_key', 'api_key', 'api_secret', 'license_key'].includes(field);
        const isLarge = ['private_key', 'custom_data'].includes(field);
        
        if (isLarge) {
            html += `
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1" style="font-family: Poppins, sans-serif;">${label}</label>
                    <textarea name="${field}" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 font-mono">${escapeHtml(value)}</textarea>
                </div>
            `;
        } else {
            html += `
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1" style="font-family: Poppins, sans-serif;">${label}</label>
                    <input type="${isSecret ? 'password' : field === 'expiry_date' ? 'date' : field === 'port' || field === 'seats' ? 'number' : 'text'}" 
                           name="${field}" value="${escapeHtml(value)}"
                           class="w-full px-3 py-2 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                           style="font-family: Poppins, sans-serif;">
                </div>
            `;
        }
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('credential-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!window.vaultCrypto.isVaultUnlocked()) {
        alert('Please unlock your vault first.');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined mr-1 animate-spin" style="font-size: 14px;">sync</span> ENCRYPTING...';

    try {
        const formData = new FormData(this);
        const name = formData.get('name');
        const type = formData.get('type');
        const notes = formData.get('notes');

        // Collect sensitive data
        let sensitiveData = {};
        const fields = fieldTemplates[type] || [];
        fields.forEach(field => {
            const value = formData.get(field);
            if (value) {
                sensitiveData[field] = value;
            }
        });

        // Encrypt sensitive data
        const mek = window.vaultCrypto.getMek();
        const { encryptedData, iv } = await window.vaultCrypto.encryptCredential(sensitiveData, mek);

        // Send to server
        const response = await fetch('{{ route("internal.credentials.update", $credential) }}', {
            method: 'PUT',
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
                btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span> UPDATE CREDENTIAL';
            } else {
                window.location.href = '{{ route("internal.credentials.show", $credential) }}';
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update credential: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">save</span> UPDATE CREDENTIAL';
    }
});

document.getElementById('pin-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        unlockVault();
    }
});

// Check vault status on load
document.addEventListener('DOMContentLoaded', async function() {
    // Try to restore MEK from session
    const restored = await window.vaultCrypto.restoreMekFromSession();
    if (restored) {
        loadCredentialData();
    }
});
</script>
@endpush
@endsection
