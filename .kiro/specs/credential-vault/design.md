# Design Document: Credential Vault

## Overview

Credential Vault adalah secure password manager untuk menyimpan SSH credentials, Windows credentials, license keys, dan lain-lain dengan End-to-End Encryption (E2EE). Data di-encrypt di browser menggunakan AES-256-GCM sebelum dihantar ke server. Setiap user mempunyai Master Encryption Key (MEK) yang di-wrap dengan Daily PIN yang auto-rotate setiap hari.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         BROWSER (Client)                         │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────────┐  │
│  │ PIN Entry   │───▶│ Key Derive  │───▶│ Decrypt MEK         │  │
│  │ Modal       │    │ (PBKDF2)    │    │ (AES-256-GCM)       │  │
│  └─────────────┘    └─────────────┘    └─────────────────────┘  │
│                                                │                 │
│                                                ▼                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    MEK (in memory)                          ││
│  │              Used for encrypt/decrypt credentials           ││
│  └─────────────────────────────────────────────────────────────┘│
│                     │                    │                       │
│                     ▼                    ▼                       │
│  ┌─────────────────────┐    ┌─────────────────────────────────┐ │
│  │ Encrypt Credential  │    │ Decrypt Credential              │ │
│  │ (before save)       │    │ (after fetch)                   │ │
│  └─────────────────────┘    └─────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTPS (encrypted data only)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         SERVER (Laravel)                         │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────┐    ┌─────────────────────────────────┐ │
│  │ CredentialController│    │ PIN Rotation Job                │ │
│  │ (CRUD operations)   │    │ (Daily at midnight)             │ │
│  └─────────────────────┘    └─────────────────────────────────┘ │
│                              │                                   │
│                              ▼                                   │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                      DATABASE                               ││
│  │  - user_vault_keys (encrypted_mek, pin_hash, pin_expires)  ││
│  │  - credentials (encrypted_data, metadata)                  ││
│  │  - credential_audit_logs (action, timestamp)               ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Models

#### UserVaultKey Model
```php
class UserVaultKey extends Model
{
    protected $fillable = [
        'user_id',
        'encrypted_mek',      // MEK encrypted with PIN-derived key
        'mek_iv',             // IV for MEK encryption
        'pin_hash',           // Hash of current PIN for verification
        'pin_salt',           // Salt for PBKDF2
        'pin_expires_at',     // When current PIN expires
        'failed_attempts',    // Count of failed PIN attempts
        'locked_until',       // Lockout timestamp
        'is_initialized',     // Whether vault is set up
    ];
}
```

#### Credential Model
```php
class Credential extends Model
{
    protected $fillable = [
        'user_id',
        'name',               // Plaintext - for listing
        'type',               // ssh, windows, license_key, database, api_key, other
        'encrypted_data',     // JSON encrypted with MEK
        'data_iv',            // IV for data encryption
        'notes',              // Optional plaintext notes
    ];
}
```

#### CredentialAuditLog Model
```php
class CredentialAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'credential_id',
        'action',             // unlock, view, create, update, delete
        'ip_address',
        'user_agent',
    ];
}
```

### 2. Controller Interface

```php
class CredentialController extends Controller
{
    // List credentials (metadata only, no decryption needed)
    public function index();
    
    // Show create form
    public function create();
    
    // Store new credential (receives encrypted data from browser)
    public function store(Request $request);
    
    // Show credential detail (returns encrypted data for browser to decrypt)
    public function show(Credential $credential);
    
    // Show edit form
    public function edit(Credential $credential);
    
    // Update credential (receives encrypted data from browser)
    public function update(Request $request, Credential $credential);
    
    // Delete credential
    public function destroy(Credential $credential);
    
    // Verify PIN and return encrypted MEK
    public function verifyPin(Request $request);
    
    // Initialize vault for new user
    public function initializeVault(Request $request);
    
    // Get current PIN (for display in user profile)
    public function getCurrentPin();
}
```

### 3. JavaScript Encryption Service

```javascript
class VaultCrypto {
    // Derive key from PIN using PBKDF2
    async deriveKeyFromPin(pin, salt);
    
    // Decrypt MEK using PIN-derived key
    async decryptMek(encryptedMek, iv, pinDerivedKey);
    
    // Encrypt credential data with MEK
    async encryptCredential(data, mek);
    
    // Decrypt credential data with MEK
    async decryptCredential(encryptedData, iv, mek);
    
    // Generate random MEK
    async generateMek();
    
    // Encrypt MEK with PIN-derived key
    async encryptMek(mek, pinDerivedKey);
}
```

## Data Models

### Credential Data Structure (before encryption)

```javascript
// SSH Credential
{
    hostname: "192.168.1.100",
    port: 22,
    username: "admin",
    password: "secret123",
    private_key: "-----BEGIN RSA PRIVATE KEY-----..."
}

// Windows Credential
{
    hostname: "192.168.1.50",
    domain: "COMPANY",
    username: "administrator",
    password: "secret123",
    license_key: "XXXXX-XXXXX-XXXXX-XXXXX"
}

// License Key
{
    software_name: "Microsoft Office 365",
    license_key: "XXXXX-XXXXX-XXXXX-XXXXX",
    expiry_date: "2025-12-31",
    seats: 10
}

// Database Credential
{
    host: "db.example.com",
    port: 3306,
    database: "production",
    username: "dbuser",
    password: "dbpass123"
}

// API Key
{
    service_name: "Stripe",
    api_key: "sk_live_xxxxx",
    api_secret: "whsec_xxxxx",
    endpoint: "https://api.stripe.com"
}
```

### Database Schema

```sql
-- User vault keys table
CREATE TABLE user_vault_keys (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL UNIQUE,
    encrypted_mek TEXT NOT NULL,
    mek_iv VARCHAR(32) NOT NULL,
    pin_hash VARCHAR(255) NOT NULL,
    pin_salt VARCHAR(32) NOT NULL,
    pin_expires_at TIMESTAMP NOT NULL,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    is_initialized BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Credentials table
CREATE TABLE credentials (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('ssh', 'windows', 'license_key', 'database', 'api_key', 'other') NOT NULL,
    encrypted_data TEXT NOT NULL,
    data_iv VARCHAR(32) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type)
);

-- Audit logs table
CREATE TABLE credential_audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    credential_id BIGINT NULL,
    action ENUM('unlock', 'view', 'create', 'update', 'delete', 'pin_rotate') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (credential_id) REFERENCES credentials(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action, created_at)
);
```



## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Credential Type Validation

*For any* credential type (SSH, Windows, License_Key, Database, API_Key, Other), when a credential of that type is created with all required fields, the system should successfully store it and return the same type when retrieved.

**Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6**

### Property 2: Encryption Round-Trip

*For any* valid credential data, encrypting with MEK then decrypting with the same MEK should produce the original plaintext data. This validates E2EE integrity.

**Validates: Requirements 2.1, 2.4, 2.5**

### Property 3: Server Never Sees Plaintext

*For any* credential stored in the database, the `encrypted_data` field should not contain any plaintext values from the original credential (hostname, username, password, etc.).

**Validates: Requirements 2.3, 3.4**

### Property 4: MEK Generation Uniqueness

*For any* two different users, their generated MEKs should be different. Additionally, each MEK should be exactly 256 bits (32 bytes).

**Validates: Requirements 3.1, 3.2**

### Property 5: PIN Rotation Preserves Access

*For any* user with initialized vault and stored credentials, after PIN rotation, the user should still be able to decrypt all their credentials using the new PIN.

**Validates: Requirements 3.5, 4.3**

### Property 6: PIN Format Validation

*For any* PIN input, the system should only accept exactly 8 numeric digits. Any other format (less than 8, more than 8, non-numeric) should be rejected.

**Validates: Requirements 4.1, 5.2**

### Property 7: PIN Hash Storage

*For any* stored PIN, the database should contain only the hash (not plaintext). Verifying the original PIN against the hash should return true.

**Validates: Requirements 4.5**

### Property 8: Correct PIN Unlocks Vault

*For any* user with initialized vault, entering the correct PIN should successfully decrypt the MEK and allow access to credentials.

**Validates: Requirements 5.3**

### Property 9: Incorrect PIN Rejected

*For any* user with initialized vault, entering an incorrect PIN should be rejected and not decrypt the MEK.

**Validates: Requirements 5.4**

### Property 10: Credential List Shows Metadata Only

*For any* credential in the list view, only non-sensitive metadata (name, type, created_at) should be returned. Sensitive fields (password, keys) should not be included.

**Validates: Requirements 6.2**

### Property 11: Edit Preserves Unmodified Fields

*For any* credential edit operation where only some fields are modified, the unmodified fields should retain their original values after save.

**Validates: Requirements 6.4**

### Property 12: Delete Removes Data

*For any* deleted credential, querying the database for that credential ID should return no results.

**Validates: Requirements 6.5**

### Property 13: Audit Log Completeness

*For any* credential operation (unlock, view, create, update, delete), an audit log entry should be created with the correct action type and timestamp.

**Validates: Requirements 8.1, 8.2, 8.3**

### Property 14: Audit Log Contains No Sensitive Data

*For any* audit log entry, the log should not contain any plaintext credential data (passwords, keys, etc.).

**Validates: Requirements 8.4**

## Error Handling

### PIN Errors
- Invalid PIN format: Return validation error with message "PIN must be exactly 8 digits"
- Incorrect PIN: Return error "Invalid PIN" and increment failed_attempts
- Account locked: Return error "Account locked. Try again in X minutes"

### Encryption Errors
- MEK decryption failure: Return error "Unable to unlock vault. Please try again"
- Credential decryption failure: Return error "Unable to decrypt credential"

### Validation Errors
- Missing required fields: Return validation errors for each missing field
- Invalid credential type: Return error "Invalid credential type"

### Authorization Errors
- Accessing other user's credential: Return 403 Forbidden
- Vault not initialized: Redirect to initialization flow

## Testing Strategy

### Unit Tests
- Test credential type validation for each type
- Test PIN format validation (8 digits, numeric only)
- Test lockout logic (5 attempts, 15 minute lockout)
- Test audit log creation

### Property-Based Tests (using Pest with Faker)
- Property 2: Encryption round-trip with random credential data
- Property 4: MEK uniqueness across multiple generated keys
- Property 5: PIN rotation with random credentials
- Property 6: PIN validation with random inputs
- Property 7: PIN hash verification
- Property 13: Audit log completeness

### Integration Tests
- Full flow: Initialize vault → Create credential → View credential → Edit → Delete
- PIN rotation job execution
- Lockout and unlock flow

### JavaScript Tests (using Jest)
- VaultCrypto.deriveKeyFromPin with various inputs
- VaultCrypto.encryptCredential / decryptCredential round-trip
- VaultCrypto.generateMek uniqueness
