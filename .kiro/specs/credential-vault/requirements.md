# Requirements Document

## Introduction

Credential Vault adalah modul untuk menyimpan dan mengurus credentials sensitif seperti SSH credentials, Windows credentials, dan license keys dengan End-to-End Encryption (E2EE). Setiap user akan mempunyai Daily PIN yang auto-rotate setiap hari untuk unlock credentials mereka.

## Glossary

- **Credential_Vault**: Sistem penyimpanan credentials yang encrypted
- **Credential**: Data sensitif seperti username, password, IP address, atau license key
- **MEK (Master_Encryption_Key)**: Symmetric key yang digunakan untuk encrypt/decrypt credentials
- **Daily_PIN**: 8-digit PIN yang auto-rotate setiap 24 jam untuk unlock MEK
- **E2EE (End_to_End_Encryption)**: Encryption di mana data di-encrypt di client dan hanya boleh di-decrypt di client
- **Credential_Type**: Jenis credential (SSH, Windows, License Key, Database, API Key, Other)
- **PIN_Rotation_Job**: Scheduled job yang rotate PIN setiap hari

## Requirements

### Requirement 1: Credential Types Management

**User Story:** As a system administrator, I want to store different types of credentials, so that I can manage all sensitive access information in one secure place.

#### Acceptance Criteria

1. THE Credential_Vault SHALL support the following credential types: SSH, Windows, License_Key, Database, API_Key, and Other
2. WHEN storing SSH credentials, THE Credential_Vault SHALL capture hostname/IP, port, username, password, and optional private key
3. WHEN storing Windows credentials, THE Credential_Vault SHALL capture hostname/IP, domain, username, password, and optional license key
4. WHEN storing License_Key credentials, THE Credential_Vault SHALL capture software name, license key, expiry date, and notes
5. WHEN storing Database credentials, THE Credential_Vault SHALL capture host, port, database name, username, and password
6. WHEN storing API_Key credentials, THE Credential_Vault SHALL capture service name, API key, API secret, and endpoint URL

### Requirement 2: End-to-End Encryption

**User Story:** As a security-conscious user, I want my credentials encrypted end-to-end, so that even the server cannot read my sensitive data.

#### Acceptance Criteria

1. THE Credential_Vault SHALL encrypt all sensitive fields using AES-256-GCM before sending to server
2. THE Credential_Vault SHALL perform all encryption and decryption operations in the browser (client-side)
3. THE Server SHALL only store encrypted credential data and SHALL NOT have access to plaintext credentials
4. WHEN a user creates a credential, THE Browser SHALL encrypt the data with MEK before transmission
5. WHEN a user views a credential, THE Browser SHALL decrypt the data with MEK after receiving from server

### Requirement 3: Master Encryption Key (MEK) Management

**User Story:** As a user, I want a secure master key to protect my credentials, so that my data remains encrypted even if the database is compromised.

#### Acceptance Criteria

1. WHEN a user first accesses Credential_Vault, THE System SHALL generate a unique MEK for that user
2. THE MEK SHALL be a 256-bit cryptographically secure random key
3. THE MEK SHALL be encrypted with the user's Daily_PIN before storage
4. THE Server SHALL only store the encrypted MEK, never the plaintext MEK
5. WHEN Daily_PIN rotates, THE System SHALL re-encrypt MEK with the new PIN

### Requirement 4: Daily PIN Auto-Rotation

**User Story:** As a security administrator, I want PINs to automatically rotate daily, so that compromised PINs have limited exposure time.

#### Acceptance Criteria

1. THE System SHALL generate a random 8-digit PIN for each user
2. THE PIN_Rotation_Job SHALL execute daily at midnight (00:00) server time
3. WHEN PIN rotates, THE System SHALL decrypt MEK with old PIN and re-encrypt with new PIN
4. WHEN PIN rotates, THE System SHALL notify the user via email with their new PIN
5. THE System SHALL store PIN hash (not plaintext) for verification purposes
6. IF a user has not set up Credential_Vault, THEN THE System SHALL skip PIN rotation for that user

### Requirement 5: PIN Unlock Interface

**User Story:** As a user, I want to unlock my credentials with my daily PIN, so that I can securely access my stored credentials.

#### Acceptance Criteria

1. WHEN a user accesses Credential_Vault, THE System SHALL display a PIN entry modal
2. THE PIN entry modal SHALL accept exactly 8 numeric digits
3. WHEN correct PIN is entered, THE System SHALL unlock and display credentials (decrypted in browser)
4. WHEN incorrect PIN is entered, THE System SHALL display an error message and allow retry
5. AFTER 5 failed PIN attempts, THE System SHALL lock the user out for 15 minutes
6. THE System SHALL maintain unlock state in browser session (sessionStorage) until browser is closed

### Requirement 6: Credential CRUD Operations

**User Story:** As a user, I want to create, view, edit, and delete credentials, so that I can manage my stored credentials effectively.

#### Acceptance Criteria

1. WHEN creating a credential, THE System SHALL encrypt and store the credential data
2. WHEN viewing credentials list, THE System SHALL display non-sensitive metadata (name, type, created date) without requiring PIN
3. WHEN viewing credential details, THE System SHALL require PIN unlock to decrypt and display sensitive fields
4. WHEN editing a credential, THE System SHALL decrypt existing data, allow modification, and re-encrypt before saving
5. WHEN deleting a credential, THE System SHALL permanently remove the encrypted data after confirmation
6. THE Credential list SHALL follow the same table design as External Inventory page

### Requirement 7: User Interface Design

**User Story:** As a user, I want a clean and familiar interface, so that I can easily navigate and manage my credentials.

#### Acceptance Criteria

1. THE Credential list page SHALL follow the same design pattern as /external/inventory
2. THE System SHALL display credentials in a data table with columns: Name, Type, Host/Service, Created, Actions
3. THE System SHALL provide search functionality to filter credentials by name or type
4. THE System SHALL provide filter dropdown for credential type
5. WHEN sensitive data is locked, THE System SHALL display masked values (e.g., ••••••••)
6. WHEN sensitive data is unlocked, THE System SHALL display actual values with copy-to-clipboard button

### Requirement 8: Security Audit Trail

**User Story:** As a security administrator, I want to track credential access, so that I can audit who accessed what and when.

#### Acceptance Criteria

1. WHEN a user unlocks credentials, THE System SHALL log the unlock event with timestamp
2. WHEN a user views a specific credential, THE System SHALL log the view event
3. WHEN a user creates, edits, or deletes a credential, THE System SHALL log the action
4. THE Audit log SHALL NOT contain any plaintext credential data
5. THE System SHALL retain audit logs for 90 days
