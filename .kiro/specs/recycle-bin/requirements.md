# Requirements Document

## Introduction

Recycle Bin adalah feature untuk ATLINE System yang membolehkan soft delete untuk semua entities. Apabila user delete sesuatu item, ia akan dipindahkan ke Recycle Bin terlebih dahulu sebelum boleh di-hard delete secara kekal. Ini memberikan safety net untuk data recovery dan audit trail yang lengkap.

## Glossary

- **Recycle_Bin**: Tempat penyimpanan sementara untuk items yang telah di-delete (soft delete)
- **Soft_Delete**: Proses menandakan item sebagai deleted tanpa membuang data dari database
- **Hard_Delete**: Proses membuang data secara kekal dari database
- **Restore**: Proses memulihkan item dari Recycle Bin kembali ke lokasi asal
- **Recyclable_Entity**: Model/entity yang boleh masuk Recycle Bin (Projects, Assets, Employees, etc.)

## Requirements

### Requirement 1: Recycle Bin Tab dalam Integrations

**User Story:** As an administrator, I want to access Recycle Bin through the Integrations page, so that I can manage deleted items in a centralized location.

#### Acceptance Criteria

1. THE System SHALL display a "Recycle Bin" tab in System Settings > Integrations page before the Email tab
2. WHEN user clicks on Recycle Bin tab, THE System SHALL display recycle bin data table with filters
3. THE Recycle Bin tab SHALL display storage statistics showing total recycled items count

---

### Requirement 2: Recycle Bin Data Table

**User Story:** As an administrator, I want to view all recycled items in a table format, so that I can manage deleted items efficiently.

#### Acceptance Criteria

1. THE System SHALL display recycled items in a table with columns: Type, Name/Title, Original ID, Deleted By, Deleted At, Actions
2. WHEN displaying recycled items, THE System SHALL show the entity type with appropriate icon (Project, Asset, Employee, etc.)
3. THE System SHALL display the original name/title of the recycled item
4. THE System SHALL show the user who performed the delete action
5. THE System SHALL display deletion timestamp
6. THE System SHALL provide Restore and Permanent Delete action buttons for each item
7. THE System SHALL support pagination for recycled items list
8. THE System SHALL support filtering by entity type (dropdown)
9. THE System SHALL support search by name/title
10. THE System SHALL support date range filtering (date_from, date_to)

---

### Requirement 3: Soft Delete Implementation

**User Story:** As a system user, I want deleted items to be moved to Recycle Bin instead of being permanently deleted, so that I can recover accidentally deleted data.

#### Acceptance Criteria

1. WHEN user deletes any recyclable entity, THE System SHALL perform soft delete instead of hard delete
2. THE System SHALL add deleted_at timestamp to the record
3. THE System SHALL record the deleted_by user_id who performed the deletion
4. THE System SHALL exclude soft-deleted items from normal queries automatically
5. THE System SHALL support soft delete for the following entities:
   - External: Projects, Assets, Clients, Vendors, Categories, Brands, Locations
   - Internal: Employees, Internal Assets, Credentials, Downloads
   - Helpdesk: Tickets
   - Settings: Users (deactivate instead of delete)

---

### Requirement 4: Restore Functionality

**User Story:** As an administrator, I want to restore recycled items back to their original location, so that I can recover accidentally deleted data.

#### Acceptance Criteria

1. WHEN user clicks Restore button, THE System SHALL remove the deleted_at timestamp
2. THE System SHALL restore the item to its original location/module
3. THE System SHALL log the restore action in activity logs
4. THE System SHALL display success message after successful restore
5. THE System SHALL validate that restoring the item won't cause conflicts (e.g., duplicate asset tags)

---

### Requirement 5: Permanent Delete (Hard Delete)

**User Story:** As an administrator, I want to permanently delete items from Recycle Bin, so that I can free up storage and remove unwanted data completely.

#### Acceptance Criteria

1. WHEN user clicks Permanent Delete button, THE System SHALL show confirmation modal
2. THE confirmation modal SHALL clearly state that this action cannot be undone
3. WHEN confirmed, THE System SHALL permanently remove the record from database
4. THE System SHALL also delete any related attachments and files
5. THE System SHALL log the permanent deletion in activity logs
6. THE System SHALL display success message after successful deletion

---

### Requirement 6: Bulk Delete by Age (Dropdown)

**User Story:** As an administrator, I want to bulk delete recycled items older than a specified period, so that I can clean up old deleted data efficiently.

#### Acceptance Criteria

1. THE System SHALL provide a DELETE dropdown button with options: 30 Days, 60 Days, 90 Days
2. WHEN user selects a period, THE System SHALL show confirmation modal with count of items to be deleted
3. THE confirmation modal SHALL clearly state that this action cannot be undone
4. WHEN confirmed, THE System SHALL permanently delete all recycled items older than the selected period
5. THE System SHALL log the bulk deletion in activity logs
6. THE System SHALL display success message with count of deleted items

---

### Requirement 7: Permission Matrix for Recycle Bin

**User Story:** As a system administrator, I want to control who can access and manage Recycle Bin, so that sensitive deleted data is protected.

#### Acceptance Criteria

1. THE System SHALL add Recycle Bin permissions to the role permission matrix
2. THE Recycle Bin permissions SHALL include: View, Restore, Delete (permanent)
3. WHEN user without View permission accesses Recycle Bin, THE System SHALL deny access
4. WHEN user without Restore permission tries to restore, THE System SHALL deny the action
5. WHEN user without Delete permission tries to permanently delete, THE System SHALL deny the action
6. THE permission module SHALL be named "settings_integrations_recycle_bin"

---

### Requirement 8: Recycle Bin Statistics Header

**User Story:** As an administrator, I want to see recycle bin statistics, so that I can monitor deleted items.

#### Acceptance Criteria

1. THE System SHALL display total count of recycled items in header
2. THE System SHALL display count breakdown by entity type (optional tooltip or info)

---

### Requirement 9: Recycle Bin Activity Logging

**User Story:** As an administrator, I want all recycle bin operations to be logged, so that I have complete audit trail of deleted data management.

#### Acceptance Criteria

1. WHEN an item is soft deleted, THE System SHALL log the action with module "recycle_bin"
2. WHEN an item is restored, THE System SHALL log the action with details
3. WHEN an item is permanently deleted, THE System SHALL log the action with item details
4. WHEN bulk delete is performed, THE System SHALL log the action with count
5. THE activity log SHALL include: action type, entity type, entity name, user, timestamp
