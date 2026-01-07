/**
 * VaultCrypto - Client-side encryption for Credential Vault
 * Uses Web Crypto API with AES-256-GCM and PBKDF2
 */
class VaultCrypto {
    constructor() {
        this.mek = null;
        this.isUnlocked = false;
    }

    /**
     * Convert hex string to ArrayBuffer
     */
    hexToBuffer(hex) {
        const bytes = new Uint8Array(hex.length / 2);
        for (let i = 0; i < hex.length; i += 2) {
            bytes[i / 2] = parseInt(hex.substr(i, 2), 16);
        }
        return bytes.buffer;
    }

    /**
     * Convert ArrayBuffer to hex string
     */
    bufferToHex(buffer) {
        const bytes = new Uint8Array(buffer);
        return Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
    }

    /**
     * Convert string to ArrayBuffer
     */
    stringToBuffer(str) {
        return new TextEncoder().encode(str);
    }

    /**
     * Convert ArrayBuffer to string
     */
    bufferToString(buffer) {
        return new TextDecoder().decode(buffer);
    }

    /**
     * Derive key from PIN using PBKDF2
     */
    async deriveKeyFromPin(pin, saltHex) {
        const salt = this.hexToBuffer(saltHex);
        const pinBuffer = this.stringToBuffer(pin);
        
        const keyMaterial = await crypto.subtle.importKey(
            'raw',
            pinBuffer,
            'PBKDF2',
            false,
            ['deriveKey']
        );

        return await crypto.subtle.deriveKey(
            {
                name: 'PBKDF2',
                salt: salt,
                iterations: 100000,
                hash: 'SHA-256'
            },
            keyMaterial,
            { name: 'AES-GCM', length: 256 },
            false,
            ['encrypt', 'decrypt']
        );
    }

    /**
     * Generate a new MEK (Master Encryption Key)
     */
    async generateMek() {
        return await crypto.subtle.generateKey(
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );
    }

    /**
     * Export MEK to raw bytes
     */
    async exportMek(mek) {
        return await crypto.subtle.exportKey('raw', mek);
    }

    /**
     * Import MEK from raw bytes
     */
    async importMek(rawKey) {
        return await crypto.subtle.importKey(
            'raw',
            rawKey,
            { name: 'AES-GCM', length: 256 },
            true,
            ['encrypt', 'decrypt']
        );
    }

    /**
     * Encrypt MEK with PIN-derived key
     */
    async encryptMek(mek, pinDerivedKey) {
        const iv = crypto.getRandomValues(new Uint8Array(12));
        const mekRaw = await this.exportMek(mek);
        
        const encrypted = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            pinDerivedKey,
            mekRaw
        );

        return {
            encryptedMek: this.bufferToHex(encrypted),
            iv: this.bufferToHex(iv)
        };
    }

    /**
     * Decrypt MEK with PIN-derived key
     */
    async decryptMek(encryptedMekHex, ivHex, pinDerivedKey) {
        const encryptedMek = this.hexToBuffer(encryptedMekHex);
        const iv = this.hexToBuffer(ivHex);

        const decrypted = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: iv },
            pinDerivedKey,
            encryptedMek
        );

        return await this.importMek(decrypted);
    }

    /**
     * Encrypt credential data with MEK
     */
    async encryptCredential(data, mek) {
        const iv = crypto.getRandomValues(new Uint8Array(12));
        const dataString = JSON.stringify(data);
        const dataBuffer = this.stringToBuffer(dataString);

        const encrypted = await crypto.subtle.encrypt(
            { name: 'AES-GCM', iv: iv },
            mek,
            dataBuffer
        );

        return {
            encryptedData: this.bufferToHex(encrypted),
            iv: this.bufferToHex(iv)
        };
    }

    /**
     * Decrypt credential data with MEK
     */
    async decryptCredential(encryptedDataHex, ivHex, mek) {
        const encryptedData = this.hexToBuffer(encryptedDataHex);
        const iv = this.hexToBuffer(ivHex);

        const decrypted = await crypto.subtle.decrypt(
            { name: 'AES-GCM', iv: iv },
            mek,
            encryptedData
        );

        const dataString = this.bufferToString(decrypted);
        return JSON.parse(dataString);
    }

    /**
     * Store MEK in session storage (raw form for easy restore)
     */
    async storeMekInSession(mek) {
        const rawMek = await this.exportMek(mek);
        const mekHex = this.bufferToHex(rawMek);
        sessionStorage.setItem('vault_mek_raw', mekHex);
        sessionStorage.setItem('vault_unlocked', 'true');
    }

    /**
     * Get stored MEK from session
     */
    getMekFromSession() {
        return {
            mekHex: sessionStorage.getItem('vault_mek_raw'),
            isUnlocked: sessionStorage.getItem('vault_unlocked') === 'true'
        };
    }

    /**
     * Restore MEK from session
     */
    async restoreMekFromSession() {
        const session = this.getMekFromSession();
        if (session.isUnlocked && session.mekHex) {
            const rawMek = this.hexToBuffer(session.mekHex);
            this.mek = await this.importMek(rawMek);
            this.isUnlocked = true;
            return true;
        }
        return false;
    }

    /**
     * Clear MEK from session (lock vault)
     */
    clearSession() {
        sessionStorage.removeItem('vault_mek_raw');
        sessionStorage.removeItem('vault_unlocked');
        this.mek = null;
        this.isUnlocked = false;
    }

    /**
     * Set the current MEK (after unlock)
     */
    setMek(mek) {
        this.mek = mek;
        this.isUnlocked = true;
    }

    /**
     * Get current MEK
     */
    getMek() {
        return this.mek;
    }

    /**
     * Check if vault is unlocked
     */
    isVaultUnlocked() {
        return this.isUnlocked && this.mek !== null;
    }
}

// Global instance
window.vaultCrypto = new VaultCrypto();
