@extends('layouts.app')

@section('title', 'Credentials')

@section('page-title', 'Credentials')

@section('content')
<div class="bg-white border border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900" style="font-family: Poppins, sans-serif;">Credential Vault</h2>
            <p class="text-xs text-gray-500 mt-0.5">Securely store and manage your credentials</p>
        </div>
        <div class="flex items-center gap-2">
            @if($vaultKey && $vaultKey->is_initialized)
            <button type="button" onclick="showUnlockModal()"
               class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded hover:opacity-90 transition"
               style="min-height: 32px; background-color: #059669;"
               id="unlock-btn">
                <span class="material-symbols-outlined" style="font-size: 14px;">lock_open</span>
                UNLOCK VAULT
            </button>
            @permission('internal_credentials.create')
            <a href="{{ route('internal.credentials.create') }}" 
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">add_circle</span>
                CREDENTIAL
            </a>
            @endpermission
            @else
            <button type="button" onclick="showInitializeModal()"
               class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
               style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">key</span>
                INITIALIZE VAULT
            </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-3">
        <form action="{{ route('internal.credentials.index') }}" method="GET" class="flex items-center gap-2">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search credential name..." 
                       class="w-full px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500"
                       style="font-family: Poppins, sans-serif; min-height: 32px;">
            </div>
            <select name="type" class="px-3 border border-gray-300 rounded text-xs focus:outline-none focus:border-blue-500 min-w-[120px]" style="font-family: Poppins, sans-serif; min-height: 32px;">
                <option value="">All Types</option>
                @foreach($types as $value => $label)
                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center gap-2 px-3 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" style="min-height: 32px;">
                <span class="material-symbols-outlined" style="font-size: 14px;">search</span>
                SEARCH
            </button>
            <button type="button" onclick="window.location.href='{{ route('internal.credentials.index') }}'" class="inline-flex items-center gap-2 px-3 text-white text-xs font-medium rounded transition" style="min-height: 32px; background-color: #dc2626;">
                <span class="material-symbols-outlined" style="font-size: 14px;">refresh</span>
                RESET
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-green-50 border border-green-200 rounded">
            <p class="text-xs text-green-800" style="font-family: Poppins, sans-serif;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded">
            <p class="text-xs text-red-800" style="font-family: Poppins, sans-serif;">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if(!$vaultKey || !$vaultKey->is_initialized)
    <div class="px-6 pb-3">
        <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs text-yellow-800" style="font-family: Poppins, sans-serif;">
                <span class="material-symbols-outlined align-middle" style="font-size: 14px;">info</span>
                Your vault is not initialized. Click "Initialize Vault" to set up your secure credential storage.
            </p>
        </div>
    </div>
    @endif

    <div class="px-6">
        <x-ui.data-table
            :headers="[
                ['label' => 'Name', 'align' => 'text-left'],
                ['label' => 'Type', 'align' => 'text-left'],
                ['label' => 'Created By', 'align' => 'text-left'],
                ['label' => 'Created', 'align' => 'text-left'],
                ['label' => 'Updated', 'align' => 'text-left'],
                ['label' => 'Actions', 'align' => 'text-center']
            ]"
            :actions="false"
            empty-message="No credentials found."
        >
            @forelse($credentials as $credential)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            @php
                                $icons = [
                                    'ssh' => 'terminal',
                                    'windows' => 'computer',
                                    'license_key' => 'vpn_key',
                                    'database' => 'database',
                                    'api_key' => 'api',
                                    'other' => 'key',
                                ];
                            @endphp
                            <span class="material-symbols-outlined text-blue-600" style="font-size: 16px;">{{ $icons[$credential->type] ?? 'key' }}</span>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-900">{{ $credential->name }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
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
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $typeColors[$credential->type] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $credential->type_name }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs text-gray-600">{{ $credential->creator->name ?? '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs text-gray-600">{{ $credential->created_at->format('d/m/Y H:i') }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-xs text-gray-600">{{ $credential->updated_at->format('d/m/Y H:i') }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <x-ui.action-buttons
                        :edit-url="auth()->user()->hasPermission('internal_credentials.update') ? route('internal.credentials.edit', $credential) : null"
                        :show-url="route('internal.credentials.show', $credential)"
                        :delete-onclick="auth()->user()->hasPermission('internal_credentials.delete') ? 'deleteCredential(' . $credential->id . ')' : null"
                    />
                </td>
            </tr>
            @empty
            @endforelse
        </x-ui.data-table>
    </div>

    <div class="px-6 py-3">
        <x-ui.custom-pagination :paginator="$credentials" record-label="credentials" />
    </div>
</div>

<!-- Initialize Vault Modal -->
<div id="initialize-modal" class="delete-modal" style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center;">
    <div class="delete-modal-backdrop" style="position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.5);" onclick="closeInitializeModal()"></div>
    <div class="delete-modal-content" style="position: relative; background: white; border-radius: 6px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 450px; width: 90%; margin: 1rem;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 20px; color: #2563eb;">key</span>
                <h3 style="font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">Initialize Vault</h3>
            </div>
            <button type="button" onclick="closeInitializeModal()" style="padding: 0.25rem; color: #6b7280; cursor: pointer; border: none; background: none;">
                <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
            </button>
        </div>
        <div style="padding: 1.25rem;">
            <div id="init-step-1">
                <p style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; margin: 0 0 1rem 0; line-height: 1.5;">
                    Your vault will be secured with End-to-End Encryption. A unique PIN will be generated for you to unlock your credentials.
                </p>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-3 mb-4">
                    <p style="font-family: Poppins, sans-serif; font-size: 11px; color: #92400e; margin: 0;">
                        <span class="material-symbols-outlined align-middle" style="font-size: 14px;">warning</span>
                        Your PIN will rotate daily at midnight. Make sure to check your email for the new PIN.
                    </p>
                </div>
            </div>
            <div id="init-step-2" style="display: none;">
                <p style="font-family: Poppins, sans-serif; font-size: 12px; color: #4b5563; margin: 0 0 1rem 0; line-height: 1.5;">
                    Your vault has been initialized! Here is your PIN:
                </p>
                <div class="bg-green-50 border border-green-200 rounded p-4 mb-4 text-center">
                    <p style="font-family: 'Courier New', monospace; font-size: 28px; font-weight: bold; color: #059669; margin: 0; letter-spacing: 4px;" id="generated-pin">--------</p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded p-3">
                    <p style="font-family: Poppins, sans-serif; font-size: 11px; color: #dc2626; margin: 0;">
                        <span class="material-symbols-outlined align-middle" style="font-size: 14px;">error</span>
                        Save this PIN securely! You will need it to unlock your credentials.
                    </p>
                </div>
            </div>
        </div>
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" onclick="closeInitializeModal()" 
                    class="inline-flex items-center px-3 text-xs font-medium rounded transition" 
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #f3f4f6; color: #374151;">
                CANCEL
            </button>
            <button type="button" id="init-btn" onclick="initializeVault()"
                    class="inline-flex items-center px-3 text-xs font-medium rounded transition" 
                    style="min-height: 32px; font-family: Poppins, sans-serif; background-color: #2563eb; color: white;">
                <span class="material-symbols-outlined mr-1" style="font-size: 14px;">key</span>
                INITIALIZE
            </button>
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

<x-modals.delete-confirmation />

@push('scripts')
<script src="{{ asset('js/vault-crypto.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';

function deleteCredential(id) {
    window.showDeleteModal('{{ route("internal.credentials.index") }}/' + id);
}

function showInitializeModal() {
    document.getElementById('initialize-modal').style.display = 'flex';
    document.getElementById('init-step-1').style.display = 'block';
    document.getElementById('init-step-2').style.display = 'none';
    document.getElementById('init-btn').style.display = 'inline-flex';
}

function closeInitializeModal() {
    document.getElementById('initialize-modal').style.display = 'none';
}

function showUnlockModal() {
    document.getElementById('unlock-modal').style.display = 'flex';
    document.getElementById('pin-input').value = '';
    document.getElementById('pin-error').style.display = 'none';
    document.getElementById('pin-input').focus();
}

function closeUnlockModal() {
    document.getElementById('unlock-modal').style.display = 'none';
}

async function initializeVault() {
    const btn = document.getElementById('init-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-symbols-outlined mr-1 animate-spin" style="font-size: 14px;">sync</span> INITIALIZING...';

    try {
        // Step 1: Get PIN and salt from server
        const step1Response = await fetch('{{ route("internal.credentials.initialize") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({})
        });

        const step1Data = await step1Response.json();

        if (!step1Data.success || step1Data.step !== 1) {
            throw new Error(step1Data.error || 'Failed to get PIN from server');
        }

        const pin = step1Data.pin;
        const salt = step1Data.salt;

        // Generate MEK in browser
        const mek = await window.vaultCrypto.generateMek();
        
        // Derive key from the actual PIN
        const pinKey = await window.vaultCrypto.deriveKeyFromPin(pin, salt);
        
        // Encrypt MEK with PIN-derived key
        const { encryptedMek, iv } = await window.vaultCrypto.encryptMek(mek, pinKey);

        // Step 2: Send encrypted MEK to server
        const step2Response = await fetch('{{ route("internal.credentials.initialize") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                encrypted_mek: encryptedMek,
                mek_iv: iv
            })
        });

        const step2Data = await step2Response.json();

        if (step2Data.success) {
            // Show PIN to user
            document.getElementById('init-step-1').style.display = 'none';
            document.getElementById('init-step-2').style.display = 'block';
            document.getElementById('generated-pin').textContent = pin;
            btn.style.display = 'none';
        } else {
            alert(step2Data.error || 'Failed to initialize vault');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">key</span> INITIALIZE';
        }
    } catch (error) {
        console.error('Initialize error:', error);
        alert('Failed to initialize vault: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined mr-1" style="font-size: 14px;">key</span> INITIALIZE';
    }
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
            // Decrypt MEK with PIN
            const pinKey = await window.vaultCrypto.deriveKeyFromPin(pin, data.pin_salt);
            const mek = await window.vaultCrypto.decryptMek(data.encrypted_mek, data.mek_iv, pinKey);
            
            // Store MEK in memory and session
            window.vaultCrypto.setMek(mek);
            await window.vaultCrypto.storeMekInSession(mek);

            closeUnlockModal();
            
            // Update UI
            const unlockBtn = document.getElementById('unlock-btn');
            if (unlockBtn) {
                unlockBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">lock</span> LOCK VAULT';
                unlockBtn.onclick = lockVault;
                unlockBtn.style.backgroundColor = '#dc2626';
            }

            // Show success message
            alert('Vault unlocked successfully!');
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

function lockVault() {
    window.vaultCrypto.clearSession();
    
    const unlockBtn = document.getElementById('unlock-btn');
    if (unlockBtn) {
        unlockBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">lock_open</span> UNLOCK VAULT';
        unlockBtn.onclick = showUnlockModal;
        unlockBtn.style.backgroundColor = '#059669';
    }
}

// Check if vault was previously unlocked in this session
document.addEventListener('DOMContentLoaded', async function() {
    const session = window.vaultCrypto.getMekFromSession();
    if (session.isUnlocked && session.mekHex) {
        // Restore MEK from session
        await window.vaultCrypto.restoreMekFromSession();
        
        const unlockBtn = document.getElementById('unlock-btn');
        if (unlockBtn) {
            unlockBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px;">lock</span> LOCK VAULT';
            unlockBtn.onclick = lockVault;
            unlockBtn.style.backgroundColor = '#dc2626';
        }
    }
});

// Handle Enter key in PIN input
document.getElementById('pin-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        unlockVault();
    }
});
</script>
@endpush
@endsection
