# Implementation Plan: Credential Vault

## Overview

Implementation plan untuk Credential Vault module dengan E2EE encryption. Tasks disusun secara incremental - setiap task build on previous tasks.

## Tasks

- [x] 1. Database Setup
  - [x] 1.1 Create migration for user_vault_keys table
    - Fields: user_id, encrypted_mek, mek_iv, pin_hash, pin_salt, pin_expires_at, failed_attempts, locked_until, is_initialized
    - _Requirements: 3.1, 3.3, 4.1, 4.5_
  - [x] 1.2 Create migration for credentials table
    - Fields: user_id, name, type (enum), encrypted_data, data_iv, notes
    - _Requirements: 1.1, 6.1_
  - [x] 1.3 Create migration for credential_audit_logs table
    - Fields: user_id, credential_id, action (enum), ip_address, user_agent
    - _Requirements: 8.1, 8.2, 8.3_

- [x] 2. Models and Relationships
  - [x] 2.1 Create UserVaultKey model with user relationship
    - _Requirements: 3.1, 3.3_
  - [x] 2.2 Create Credential model with user relationship and type enum
    - _Requirements: 1.1, 6.1_
  - [x] 2.3 Create CredentialAuditLog model
    - _Requirements: 8.1_
  - [x] 2.4 Write property test for credential type validation
    - **Property 1: Credential Type Validation**
    - **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6**

- [x] 3. Checkpoint - Database and Models
  - Ensure migrations run successfully and models are properly configured

- [x] 4. Encryption Service (Backend)
  - [x] 4.1 Create CredentialEncryptionService class
    - Methods: generatePin(), hashPin(), verifyPin(), generateSalt()
    - _Requirements: 4.1, 4.5, 5.3, 5.4_
  - [x] 4.2 Write property test for PIN format validation
    - **Property 6: PIN Format Validation**
    - **Validates: Requirements 4.1, 5.2**
  - [x] 4.3 Write property test for PIN hash storage
    - **Property 7: PIN Hash Storage**
    - **Validates: Requirements 4.5**

- [x] 5. JavaScript Encryption (Frontend)
  - [x] 5.1 Create VaultCrypto JavaScript class
    - Methods: deriveKeyFromPin(), generateMek(), encryptMek(), decryptMek(), encryptCredential(), decryptCredential()
    - Use Web Crypto API with AES-256-GCM and PBKDF2
    - _Requirements: 2.1, 2.2, 2.4, 2.5_
  - [x] 5.2 Write property test for encryption round-trip
    - **Property 2: Encryption Round-Trip**
    - **Validates: Requirements 2.1, 2.4, 2.5**

- [x] 6. Checkpoint - Encryption Services
  - Ensure encryption/decryption works correctly in both backend and frontend

- [x] 7. Vault Initialization
  - [x] 7.1 Create CredentialController with initializeVault method
    - Generate MEK, encrypt with PIN, store encrypted MEK
    - _Requirements: 3.1, 3.2, 3.3_
  - [x] 7.2 Create vault initialization view with PIN display
    - Show generated PIN to user, explain daily rotation
    - _Requirements: 4.1_
  - [x] 7.3 Write property test for MEK uniqueness
    - **Property 4: MEK Generation Uniqueness**
    - **Validates: Requirements 3.1, 3.2**

- [x] 8. PIN Unlock Flow
  - [x] 8.1 Create PIN entry modal component
    - 8-digit numeric input, submit button
    - _Requirements: 5.1, 5.2_
  - [x] 8.2 Implement verifyPin API endpoint
    - Verify PIN hash, return encrypted MEK, handle lockout
    - _Requirements: 5.3, 5.4, 5.5_
  - [x] 8.3 Implement client-side unlock logic
    - Decrypt MEK with PIN, store in sessionStorage
    - _Requirements: 5.3, 5.6_
  - [x] 8.4 Write property test for correct PIN unlocks
    - **Property 8: Correct PIN Unlocks Vault**
    - **Validates: Requirements 5.3**
  - [x] 8.5 Write property test for incorrect PIN rejected
    - **Property 9: Incorrect PIN Rejected**
    - **Validates: Requirements 5.4**

- [x] 9. Checkpoint - Vault Unlock
  - Ensure PIN unlock flow works end-to-end

- [x] 10. Credential CRUD - List
  - [x] 10.1 Implement index method in CredentialController
    - Return metadata only (name, type, created_at), no sensitive data
    - _Requirements: 6.2_
  - [x] 10.2 Create credentials index view (follow external/inventory design)
    - Data table with columns: Name, Type, Host/Service, Created, Actions
    - Search and filter by type
    - _Requirements: 6.6, 7.1, 7.2, 7.3, 7.4_
  - [x] 10.3 Write property test for list shows metadata only
    - **Property 10: Credential List Shows Metadata Only**
    - **Validates: Requirements 6.2**

- [x] 11. Credential CRUD - Create
  - [x] 11.1 Implement create and store methods
    - Receive encrypted data from browser, store directly
    - _Requirements: 6.1_
  - [x] 11.2 Create credential create view with type-specific forms
    - Dynamic form fields based on credential type
    - Client-side encryption before submit
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6_
  - [x] 11.3 Write property test for server never sees plaintext
    - **Property 3: Server Never Sees Plaintext**
    - **Validates: Requirements 2.3, 3.4**

- [x] 12. Credential CRUD - View/Edit
  - [x] 12.1 Implement show method
    - Return encrypted data for browser to decrypt
    - Require vault unlock
    - _Requirements: 6.3_
  - [x] 12.2 Create credential show view with decryption
    - Decrypt in browser, display with copy buttons
    - Masked values when locked
    - _Requirements: 7.5, 7.6_
  - [x] 12.3 Implement edit and update methods
    - _Requirements: 6.4_
  - [x] 12.4 Create credential edit view
    - Pre-fill decrypted values, re-encrypt on save
    - _Requirements: 6.4_
  - [x] 12.5 Write property test for edit preserves unmodified fields
    - **Property 11: Edit Preserves Unmodified Fields**
    - **Validates: Requirements 6.4**

- [x] 13. Credential CRUD - Delete
  - [x] 13.1 Implement destroy method
    - Soft delete or hard delete with confirmation
    - _Requirements: 6.5_
  - [x] 13.2 Write property test for delete removes data
    - **Property 12: Delete Removes Data**
    - **Validates: Requirements 6.5**

- [x] 14. Checkpoint - CRUD Operations
  - Ensure all CRUD operations work with encryption

- [x] 15. Audit Logging
  - [x] 15.1 Create AuditLogService
    - Methods: logUnlock(), logView(), logCreate(), logUpdate(), logDelete()
    - _Requirements: 8.1, 8.2, 8.3_
  - [x] 15.2 Integrate audit logging into all credential operations
    - _Requirements: 8.1, 8.2, 8.3_
  - [x] 15.3 Write property test for audit log completeness
    - **Property 13: Audit Log Completeness**
    - **Validates: Requirements 8.1, 8.2, 8.3**
  - [x] 15.4 Write property test for audit log contains no sensitive data
    - **Property 14: Audit Log Contains No Sensitive Data**
    - **Validates: Requirements 8.4**

- [x] 16. Daily PIN Rotation
  - [x] 16.1 Create RotatePinJob scheduled command
    - Generate new PIN, re-encrypt MEK, update database
    - _Requirements: 4.2, 4.3_
  - [x] 16.2 Create PIN rotation email notification
    - Send new PIN to user via email
    - _Requirements: 4.4_
  - [x] 16.3 Register job in scheduler (daily at midnight)
    - _Requirements: 4.2_
  - [x] 16.4 Write property test for PIN rotation preserves access
    - **Property 5: PIN Rotation Preserves Access**
    - **Validates: Requirements 3.5, 4.3**

- [x] 17. Routes and Navigation
  - [x] 17.1 Add credential routes to web.php
    - Resource routes + verifyPin, initializeVault, getCurrentPin
    - _Requirements: 6.1_
  - [x] 17.2 Update sidebar navigation
    - Add Credentials link under Internal menu
    - _Requirements: 7.1_

- [x] 18. Final Checkpoint
  - Ensure all tests pass, ask the user if questions arise

## Notes

- All tasks are required for comprehensive implementation
- Each task references specific requirements for traceability
- JavaScript encryption uses Web Crypto API (native browser support)
- All sensitive data encrypted client-side before transmission
- Server never has access to plaintext credentials or MEK
- Compatible with cPanel hosting (no special composer packages needed)
