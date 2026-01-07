# Implementation Plan: Recycle Bin

## Overview

Implementasi Recycle Bin feature untuk ATLINE System dengan soft delete functionality, UI seperti activity-logs, dan permission matrix dalam roles.

## Tasks

- [x] 1. Database Migration untuk Soft Deletes
  - [x] 1.1 Create migration untuk add soft delete columns ke semua recyclable tables
    - Add `deleted_at` timestamp column
    - Add `deleted_by` foreign key to users table
    - Tables: projects, assets, clients, vendors, categories, brands, locations, employees, internal_assets, credentials, downloads, tickets
    - _Requirements: 3.1, 3.2, 3.3_

- [x] 2. Create RecycleBin Trait
  - [x] 2.1 Create `app/Traits/RecycleBin.php`
    - Extend Laravel SoftDeletes trait
    - Add boot method to auto-set `deleted_by` on delete
    - Add `deletedByUser()` relationship
    - Add `getRecycleBinName()` method
    - Add `getRecycleBinType()` method
    - _Requirements: 3.1, 3.2, 3.3_

- [x] 3. Update Models dengan RecycleBin Trait
  - [x] 3.1 Add RecycleBin trait to all recyclable models
    - Project, Asset, Client, Vendor, Category, Brand, Location
    - Employee, InternalAsset, Credential, Download, Ticket
    - _Requirements: 3.4, 3.5_

- [x] 4. Create RecycleBinService
  - [x] 4.1 Create `app/Services/RecycleBinService.php`
    - Define recyclable models array with type mapping
    - Implement `getAllTrashedItems()` with filters (search, type, date_from, date_to)
    - Implement `restore()` method
    - Implement `forceDelete()` method
    - Implement `bulkDeleteByAge()` method for 30/60/90 days
    - Implement `getStatistics()` method
    - _Requirements: 2.1, 2.7, 2.8, 2.9, 2.10, 4.1, 4.2, 5.3, 5.4, 6.4_

- [x] 5. Update Permission Matrix (config/permissions.php)
  - [x] 5.1 Add Recycle Bin module to permissions config
    - Add `'settings_integrations_recycle_bin'` to modules array with label "System Settings > Integrations > Recycle Bin"
    - Add matrix entry with view: true, create: false, update: true (restore), delete: true
    - Add to tab_mapping under 'settings.integrations' => 'recycle-bin' => 'settings_integrations_recycle_bin'
    - _Requirements: 7.1, 7.2, 7.6_

- [x] 6. Update IntegrationController
  - [x] 6.1 Add Recycle Bin methods to `app/Http/Controllers/Settings/IntegrationController.php`
    - Inject RecycleBinService
    - Add `recycleBin()` method for displaying tab with filters
    - Add `restoreItem()` method with permission check
    - Add `forceDeleteItem()` method with permission check
    - Add `bulkDelete()` method with permission check for 30/60/90 days
    - _Requirements: 1.2, 4.1, 4.3, 5.1, 5.5, 6.1, 6.5_

- [x] 7. Update Routes (routes/web.php)
  - [x] 7.1 Add Recycle Bin routes under settings.integrations
    - GET route for recycle-bin tab (handled by existing index with tab param)
    - POST route for restore: `settings/integrations/recycle-bin/{type}/{id}/restore`
    - DELETE route for force delete: `settings/integrations/recycle-bin/{type}/{id}`
    - DELETE route for bulk delete: `settings/integrations/recycle-bin/bulk-delete`
    - _Requirements: 1.1, 4.1, 5.1, 6.1_

- [x] 8. Create Recycle Bin View Partial
  - [x] 8.1 Create `resources/views/settings/integrations/partials/recycle-bin.blade.php`
    - Header with title "Recycle Bin" and total items count stat
    - Filter form: search input, type dropdown, date_from, date_to, SEARCH button
    - DELETE dropdown button with 30 Days, 60 Days, 90 Days options (like activity-logs)
    - Delete confirmation modal with Alpine.js
    - _Requirements: 1.3, 2.8, 2.9, 2.10, 6.1, 6.2_

  - [x] 8.2 Create data table in recycle-bin.blade.php
    - Columns: Type (with icon), Name/Title, Original ID, Deleted By, Deleted At, Actions
    - Restore button (check update permission)
    - Permanent Delete button (check delete permission)
    - Empty state when no items
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

  - [x] 8.3 Add pagination to recycle-bin.blade.php
    - Use custom-pagination component
    - _Requirements: 2.7_

- [x] 9. Update Integrations Index View
  - [x] 9.1 Update `resources/views/settings/integrations/index.blade.php`
    - Add "Recycle Bin" tab BEFORE Email tab
    - Add tab content include for recycle-bin partial
    - _Requirements: 1.1_

- [x] 10. Activity Logging Integration
  - [x] 10.1 Add activity logging to RecycleBinService
    - Log soft delete with module "recycle_bin", action "soft_delete"
    - Log restore with module "recycle_bin", action "restore"
    - Log permanent delete with module "recycle_bin", action "permanent_delete"
    - Log bulk delete with module "recycle_bin", action "bulk_delete"
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ] 11. Update Existing Delete Methods
  - [ ] 11.1 Update all controller destroy methods to use soft delete
    - ProjectController, AssetController, ClientController, VendorController
    - CategoryController, BrandController, LocationController
    - EmployeeController, InternalInventoryController, CredentialController
    - DownloadController, HelpdeskController (tickets)
    - Ensure they call model delete() which triggers soft delete via trait
    - _Requirements: 3.1_

- [x] 12. Checkpoint - Test Basic Functionality
  - Ensure migration runs successfully ✓
  - Ensure soft delete works on all models ✓
  - Ensure Recycle Bin tab displays correctly ✓
  - Ensure all tests pass, ask the user if questions arise

- [x] 13. Permission Enforcement
  - [x] 13.1 Add permission checks in controller methods
    - Check `settings_integrations_recycle_bin.view` for accessing tab
    - Check `settings_integrations_recycle_bin.update` for restore action
    - Check `settings_integrations_recycle_bin.delete` for permanent delete and bulk delete
    - _Requirements: 7.3, 7.4, 7.5_

- [ ] 14. Final Checkpoint
  - Ensure all tests pass
  - Ensure permission matrix shows in roles create/edit pages
  - Ensure Recycle Bin tab works with all filters
  - Ask the user if questions arise

## Notes

- UI style follows existing activity-logs pattern with DELETE dropdown for bulk operations
- Permission matrix will appear in roles checkbox matrix automatically via config/permissions.php
- Soft delete uses Laravel's built-in SoftDeletes with custom `deleted_by` tracking
- All existing delete operations will become soft deletes after trait is added to models
