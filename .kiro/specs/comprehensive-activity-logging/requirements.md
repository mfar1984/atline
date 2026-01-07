# Requirements Document

## Introduction

Sistem ini memerlukan comprehensive activity logging untuk merekod semua aktiviti pengguna termasuk operasi CRUD, authentication events, dan data exports. Activity log akan dipaparkan di halaman `/settings/activity-logs` untuk audit dan monitoring purposes.

## Glossary

- **Activity_Log_Service**: Service yang bertanggungjawab untuk merekod semua aktiviti pengguna ke dalam database
- **Activity_Log**: Model yang menyimpan rekod aktiviti termasuk user, action, module, subject, dan metadata
- **User**: Pengguna sistem yang melakukan aktiviti
- **Module**: Bahagian sistem di mana aktiviti berlaku (contoh: external_inventory, helpdesk, settings)
- **Action**: Jenis operasi yang dilakukan (login, logout, create, update, delete, view, export, download, password_change)

## Requirements

### Requirement 1: Authentication Activity Logging

**User Story:** As a system administrator, I want to track all authentication events, so that I can monitor user access patterns and detect suspicious activities.

#### Acceptance Criteria

1. WHEN a user successfully logs in, THE Activity_Log_Service SHALL record the login event with user details, IP address, and user agent
2. WHEN a user logs out, THE Activity_Log_Service SHALL record the logout event with user details
3. WHEN a user changes their password, THE Activity_Log_Service SHALL record the password change event without storing the actual password
4. WHEN a user enables or disables 2FA, THE Activity_Log_Service SHALL record the 2FA status change event
5. WHEN a login attempt fails, THE Activity_Log_Service SHALL record the failed login attempt with the attempted email

### Requirement 2: External Inventory Activity Logging

**User Story:** As a system administrator, I want to track all external inventory operations, so that I can audit asset management activities.

#### Acceptance Criteria

1. WHEN a user creates a new asset, THE Activity_Log_Service SHALL record the create event with asset details and module 'external_inventory'
2. WHEN a user updates an asset, THE Activity_Log_Service SHALL record the update event with old and new values
3. WHEN a user deletes an asset, THE Activity_Log_Service SHALL record the delete event with asset details before deletion
4. WHEN a user views an asset detail page, THE Activity_Log_Service SHALL record the view event with asset identifier
5. WHEN a user exports asset data, THE Activity_Log_Service SHALL record the export event with filter criteria used

### Requirement 3: Project Activity Logging

**User Story:** As a system administrator, I want to track all project operations, so that I can audit project management activities.

#### Acceptance Criteria

1. WHEN a user creates a new project, THE Activity_Log_Service SHALL record the create event with project details and module 'external_projects'
2. WHEN a user updates a project, THE Activity_Log_Service SHALL record the update event with old and new values
3. WHEN a user deletes a project, THE Activity_Log_Service SHALL record the delete event with project details before deletion
4. WHEN a user views a project detail page, THE Activity_Log_Service SHALL record the view event with project identifier

### Requirement 4: User Management Activity Logging

**User Story:** As a system administrator, I want to track all user management operations, so that I can audit user administration activities.

#### Acceptance Criteria

1. WHEN an administrator creates a new user, THE Activity_Log_Service SHALL record the create event with user details (excluding password) and module 'settings_users'
2. WHEN an administrator updates a user, THE Activity_Log_Service SHALL record the update event with old and new values (excluding password)
3. WHEN an administrator deletes a user, THE Activity_Log_Service SHALL record the delete event with user details before deletion
4. WHEN an administrator views a user detail page, THE Activity_Log_Service SHALL record the view event with user identifier

### Requirement 5: Role Management Activity Logging

**User Story:** As a system administrator, I want to track all role management operations, so that I can audit permission changes.

#### Acceptance Criteria

1. WHEN an administrator creates a new role, THE Activity_Log_Service SHALL record the create event with role details including permissions and module 'settings_roles'
2. WHEN an administrator updates a role, THE Activity_Log_Service SHALL record the update event with old and new permission values
3. WHEN an administrator deletes a role, THE Activity_Log_Service SHALL record the delete event with role details before deletion
4. WHEN an administrator views a role detail page, THE Activity_Log_Service SHALL record the view event with role identifier

### Requirement 6: Employee Management Activity Logging

**User Story:** As a system administrator, I want to track all employee management operations, so that I can audit HR-related activities.

#### Acceptance Criteria

1. WHEN a user creates a new employee record, THE Activity_Log_Service SHALL record the create event with employee details (excluding sensitive data) and module 'internal_employee'
2. WHEN a user updates an employee record, THE Activity_Log_Service SHALL record the update event with changed fields
3. WHEN a user deletes an employee record, THE Activity_Log_Service SHALL record the delete event with employee identifier
4. WHEN a user views an employee detail page, THE Activity_Log_Service SHALL record the view event with employee identifier
5. WHEN a user downloads an employee attachment, THE Activity_Log_Service SHALL record the download event with attachment details

### Requirement 7: Helpdesk Activity Logging

**User Story:** As a system administrator, I want to track all helpdesk operations, so that I can audit support activities.

#### Acceptance Criteria

1. WHEN a user creates a new ticket, THE Activity_Log_Service SHALL record the create event with ticket details and module 'helpdesk'
2. WHEN a user updates a ticket (status change, assignment, etc.), THE Activity_Log_Service SHALL record the update event with old and new values
3. WHEN a user adds a reply to a ticket, THE Activity_Log_Service SHALL record the reply event with ticket identifier
4. WHEN a user views a ticket detail page, THE Activity_Log_Service SHALL record the view event with ticket identifier
5. WHEN a user deletes a ticket, THE Activity_Log_Service SHALL record the delete event with ticket details

### Requirement 8: Internal Inventory Activity Logging

**User Story:** As a system administrator, I want to track all internal inventory operations, so that I can audit internal asset management.

#### Acceptance Criteria

1. WHEN a user creates a new internal asset, THE Activity_Log_Service SHALL record the create event with asset details and module 'internal_inventory'
2. WHEN a user updates an internal asset, THE Activity_Log_Service SHALL record the update event with old and new values
3. WHEN a user deletes an internal asset, THE Activity_Log_Service SHALL record the delete event with asset details
4. WHEN a user performs asset checkout/checkin, THE Activity_Log_Service SHALL record the movement event with asset and employee details

### Requirement 9: Credential Vault Activity Logging

**User Story:** As a system administrator, I want to track all credential vault operations, so that I can audit sensitive credential access.

#### Acceptance Criteria

1. WHEN a user creates a new credential, THE Activity_Log_Service SHALL record the create event with credential name (not the actual credential) and module 'internal_credentials'
2. WHEN a user updates a credential, THE Activity_Log_Service SHALL record the update event with credential identifier
3. WHEN a user deletes a credential, THE Activity_Log_Service SHALL record the delete event with credential identifier
4. WHEN a user views/decrypts a credential, THE Activity_Log_Service SHALL record the view event with credential identifier

### Requirement 10: Settings and Configuration Activity Logging

**User Story:** As a system administrator, I want to track all system configuration changes, so that I can audit system modifications.

#### Acceptance Criteria

1. WHEN an administrator updates system settings, THE Activity_Log_Service SHALL record the update event with setting category and changed values
2. WHEN an administrator updates integration settings, THE Activity_Log_Service SHALL record the update event with integration type and changed values (excluding sensitive keys)
3. WHEN an administrator creates/updates/deletes master data (categories, brands, locations, vendors, clients), THE Activity_Log_Service SHALL record the event with entity details and module 'external_settings' or 'internal_settings'

### Requirement 11: Download and Export Activity Logging

**User Story:** As a system administrator, I want to track all download and export operations, so that I can monitor data extraction activities.

#### Acceptance Criteria

1. WHEN a user downloads a file attachment, THE Activity_Log_Service SHALL record the download event with file details and source module
2. WHEN a user exports data to CSV/Excel, THE Activity_Log_Service SHALL record the export event with export type and filter criteria
3. WHEN a user downloads a report, THE Activity_Log_Service SHALL record the download event with report type and parameters

### Requirement 12: Activity Log Data Integrity

**User Story:** As a system administrator, I want activity logs to maintain data integrity, so that audit records are reliable.

#### Acceptance Criteria

1. THE Activity_Log_Service SHALL capture the user's IP address for every logged activity
2. THE Activity_Log_Service SHALL capture the user's browser user agent for every logged activity
3. THE Activity_Log_Service SHALL store timestamps in UTC format for consistency
4. IF a user is deleted, THE Activity_Log model SHALL retain the activity log records with null user reference
5. THE Activity_Log_Service SHALL NOT store sensitive data such as passwords, API keys, or encrypted credentials in the properties field

### Requirement 13: Client and Staff Identification

**User Story:** As a system administrator, I want to identify whether an activity was performed by a client or staff member, so that I can filter and analyze activities by user type.

#### Acceptance Criteria

1. THE Activity_Log table SHALL include a client_id column to store the client identifier when the activity is performed by a client user
2. THE Activity_Log table SHALL include an employee_id column to store the employee identifier when the activity is performed by a staff user
3. WHEN a user performs an activity, THE Activity_Log_Service SHALL automatically detect if the user is linked to a client and set the client_id accordingly
4. WHEN a user performs an activity, THE Activity_Log_Service SHALL automatically detect if the user is linked to an employee and set the employee_id accordingly
5. THE Activity_Log model SHALL provide relationships to Client and Employee models for easy querying
6. THE Activity_Log view SHALL allow filtering by client or staff/employee
7. THE Activity_Log view SHALL display whether the activity was performed by a client or staff member
