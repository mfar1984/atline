<?php

namespace App\Services;

class CredentialEncryptionService
{
    /**
     * Generate a random 8-digit PIN
     */
    public function generatePin(): string
    {
        return str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random salt for PBKDF2
     */
    public function generateSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Hash PIN using PBKDF2 with salt
     */
    public function hashPin(string $pin, string $salt): string
    {
        return hash_pbkdf2('sha256', $pin, $salt, 100000, 64);
    }

    /**
     * Verify PIN against stored hash
     */
    public function verifyPin(string $pin, string $salt, string $storedHash): bool
    {
        $computedHash = $this->hashPin($pin, $salt);
        return hash_equals($storedHash, $computedHash);
    }

    /**
     * Validate PIN format (8 numeric digits)
     */
    public function isValidPinFormat(string $pin): bool
    {
        return preg_match('/^\d{8}$/', $pin) === 1;
    }

    /**
     * Generate a random IV for AES-GCM
     */
    public function generateIv(): string
    {
        return bin2hex(random_bytes(12));
    }
}
