@extends('layouts.app')

@section('title', 'View Credential')

@section('page-title', 'View Credential')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">{{ $credential->name }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                @php
                    $typeColors = [
                        'ssh' => 'bg-green-100 text-green-800',
                        'windows' => 'bg-blue-100 text-blue-800',
                        'license_key' => 'bg-purple-100 text-purple-800',
                        'database' => 'bg-orange-100 text-orange-800',
                        'api_key' => 'bg-pink-100 text-pink-800',
                        'other' => 'bg-gray-100 text-gray-800',
                    ];
                @endphp
                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $typeColors[$credential->type] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $credential->type_name }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            @permission('internal_credentials', 'update')
            <a href="{{ route('internal.credentials.edit', $credential) }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">edit</span>
                EDIT
            </a>
            @endpermission
            @permission('internal_credentials', 'delete')
            <button type="button" 
                    onclick="openDeleteModal('{{ $credential->name }}', '{{ route('internal.credentials.destroy', $credential) }}')"
                    class="inline-flex items-center gap-2 px-3 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition"
                    style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                DELETE
            </button>
            @endpermission
            <a href="{{ route('internal.credentials.index') }}" 
               class="inline-flex items-center gap-2 px-3 text-gray-700 text-xs font-medium rounded hover:bg-gray-100 transition border border-gray-300"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">arrow_back</span>
                BACK
            </a>
        </div>
    </div>

    <div class="px-6 py-4">
        <!-- Vault Status -->
        <div id="vault-locked" class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs text-yellow-800" style="font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined align-middle" style="font-size: 14px;">lock</span>
                Vault is locked. 
                <button type="button" onclick="showUnlockModal()" class="text-blue-600 underline">Unlock</button> to view credential details.
            </p>
        </div>

        <div id="vault-unlocked" style="display: none;">
            <!-- Credential Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1" style="font-family: Poppins, sans-serif;">Name</label>
                    <p class="text-sm text-gray-900" style="font-family: Poppins, sans-serif;">{{ $credential->name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1" style="font-family: Poppins, sans-serif;">Created</label>
                    <p class="text-sm text-gray-900" style="font-family: Poppins, sans-serif;">{{ $credential->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            @if($credential->notes)
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 mb-1" style="font-family: Poppins, sans-serif;">Notes</label>
                <p class="text-sm text-gray-900" style="font-family: Poppins, sans-serif;">{{ $credential->notes }}</p>
            </div>
            @endif

            <hr class="my-4">

            <h3 class="text-sm font-semibold text-gray-900 mb-3" style="font-family: Poppins, sans-serif;">Sensitive Data</h3>
            <div id="decrypted-data" class="space-y-3">
                <p class="text-xs text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
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

@include('components.modals.delete-confirmation')

@push('scripts')
<script src="{{ asset('js/vault-crypto.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const credentialId = {{ $credential->id }};

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
    document.getElementById('vault-unlocked').style.display = 'block';

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
            
            displayDecryptedData(decrypted);
        }
    } catch (error) {
        console.error('Error loading credential:', error);
        document.getElementById('decrypted-data').innerHTML = '<p class="text-xs text-red-600">Failed to decrypt credential data</p>';
    }
}

function displayDecryptedData(data) {
    const container = document.getElementById('decrypted-data');
    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

    for (const [key, value] of Object.entries(data)) {
        if (value) {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const isSecret = ['password', 'private_key', 'api_key', 'api_secret', 'license_key'].includes(key);
            
            html += `
                <div class="col-span-${key === 'private_key' || key === 'custom_data' ? '2' : '1'}">
                    <label class="block text-xs font-medium text-gray-500 mb-1" style="font-family: Poppins, sans-serif;">${label}</label>
                    <div class="flex items-center gap-2">
                        ${key === 'private_key' || key === 'custom_data' ? 
                            `<pre class="flex-1 text-xs text-gray-900 bg-gray-50 p-2 rounded border overflow-x-auto" style="font-family: 'Courier New', monospace;">${escapeHtml(value)}</pre>` :
                            `<input type="${isSecret ? 'password' : 'text'}" value="${escapeHtml(value)}" readonly 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs bg-gray-50" 
                                style="font-family: ${isSecret ? "'Courier New', monospace" : 'Poppins, sans-serif'};"
                                id="field-${key}">`
                        }
                        <button type="button" onclick="copyToClipboard('${escapeHtml(value)}')" 
                                class="inline-flex items-center justify-center p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                title="Copy">
                            <span class="material-symbols-outlined" style="font-size: 16px;">content_copy</span>
                        </button>
                        ${isSecret && key !== 'private_key' ? `
                        <button type="button" onclick="toggleVisibility('field-${key}')" 
                                class="inline-flex items-center justify-center p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded transition"
                                title="Toggle visibility">
                            <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                        </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }
    }

    html += '</div>';
    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

function toggleVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.type = field.type === 'password' ? 'text' : 'password';
    }
}

// Check vault status on load
document.addEventListener('DOMContentLoaded', async function() {
    // Try to restore MEK from session
    const restored = await window.vaultCrypto.restoreMekFromSession();
    if (restored) {
        loadCredentialData();
    }
});

document.getElementById('pin-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        unlockVault();
    }
});
</script>
@endpush
@endsection
