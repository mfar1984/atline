# Implementation Plan: External Settings

## Overview

Implementasi External Settings page dengan TAB navigation untuk manage master data (Client, Vendor, Location, Brand, Category). Menggunakan pattern yang sama dengan POS Item Inventory page.

## Tasks

- [x] 1. Database Setup
  - [x] 1.1 Create clients table migration
    - Create migration file with fields: id, name, contact_person, phone, email, is_active, timestamps
    - _Requirements: 8.1_
  - [x] 1.2 Create Client model
    - Add fillable, casts, relationships (projects), canBeDeleted(), scopeActive()
    - _Requirements: 8.4_
  - [x] 1.3 Modify projects table - add client_id
    - Create migration to add client_id foreign key
    - Migrate existing client_name data to clients table
    - Update projects with corresponding client_id
    - _Requirements: 8.2, 8.3_
  - [x] 1.4 Add vendor_id to assets table
    - Create migration to add vendor_id foreign key to assets
    - _Requirements: 9.1_
  - [x] 1.5 Update Asset model
    - Add vendor_id to fillable
    - Add vendor() relationship
    - _Requirements: 9.2_
  - [x] 1.6 Update Project model
    - Add client_id to fillable
    - Add client() relationship
    - Remove client_name from fillable (optional, can keep for backward compatibility)
    - _Requirements: 8.4_

- [x] 2. Controller and Routes Setup
  - [x] 2.1 Create ExternalSettingsController
    - Create controller with index method
    - Add tab switching logic based on URL parameter
    - _Requirements: 1.1, 1.2, 1.4_
  - [x] 2.2 Add routes for External Settings
    - Add main settings route
    - Add CRUD routes for clients, vendors, locations, brands, categories
    - Add toggle-status route
    - _Requirements: 1.1_

- [x] 3. Main Settings Page with TAB Navigation
  - [x] 3.1 Create settings index.blade.php
    - Create main page layout following POS Item Inventory pattern
    - Add TAB navigation with 5 tabs: Client, Vendor, Location, Brand, Category
    - Add tab content area with @include for partials
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 4. Client TAB Implementation
  - [x] 4.1 Create clients partial view
    - Create table with columns: Name, Contact Person, Phone, Email, Status, Actions
    - Add "Add Client" button
    - Add inline edit/delete actions
    - _Requirements: 2.1, 2.2, 2.3, 2.4_
  - [x] 4.2 Implement Client CRUD in controller
    - Add storeClient, updateClient, destroyClient methods
    - Add validation rules
    - Implement canBeDeleted check before delete
    - _Requirements: 2.2, 2.3, 2.4, 2.5_
  - [x] 4.3 Update Projects forms to use Client dropdown
    - Update create.blade.php - replace client_name input with Client dropdown
    - Update edit.blade.php - replace client_name input with Client dropdown
    - Update ProjectController to pass clients to views
    - _Requirements: 2.6_

- [x] 5. Vendor TAB Implementation
  - [x] 5.1 Create vendors partial view
    - Create table with columns: Name, Contact Person, Phone, Email, Status, Actions
    - Add "Add Vendor" button
    - Add inline edit/delete actions
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  - [x] 5.2 Implement Vendor CRUD in controller
    - Add storeVendor, updateVendor, destroyVendor methods
    - Add validation rules
    - Implement canBeDeleted check (check both projects and assets)
    - _Requirements: 3.2, 3.3, 3.4, 3.5_
  - [x] 5.3 Update Inventory forms to include Vendor dropdown
    - Update create.blade.php - add Vendor dropdown
    - Update edit.blade.php - add Vendor dropdown
    - Update AssetController to pass vendors to views
    - Update AssetRequest validation to include vendor_id
    - _Requirements: 3.7, 9.3_

- [x] 6. Location TAB Implementation
  - [x] 6.1 Create locations partial view
    - Create table with columns: Name, Type, Parent Location, Status, Actions
    - Add "Add Location" button
    - Show hierarchical structure with indentation
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  - [x] 6.2 Implement Location CRUD in controller
    - Add storeLocation, updateLocation, destroyLocation methods
    - Add validation rules including parent_id
    - Implement canBeDeleted check (check children and assets)
    - _Requirements: 4.3, 4.4, 4.5, 4.6_

- [x] 7. Brand TAB Implementation
  - [x] 7.1 Create brands partial view
    - Create table with columns: Name, Status, Actions
    - Add "Add Brand" button
    - Add inline edit/delete actions
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  - [x] 7.2 Implement Brand CRUD in controller
    - Add storeBrand, updateBrand, destroyBrand methods
    - Add validation rules
    - Implement canBeDeleted check
    - _Requirements: 5.2, 5.3, 5.4, 5.5_

- [x] 8. Category TAB Implementation
  - [x] 8.1 Create categories partial view
    - Create table with columns: Name, Code, Custom Fields Count, Status, Actions
    - Add "Add Category" button
    - Add modal for category form with dynamic fields configuration
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_
  - [x] 8.2 Implement Category CRUD in controller
    - Add storeCategory, updateCategory, destroyCategory methods
    - Add validation rules including fields_config JSON validation
    - Implement canBeDeleted check
    - _Requirements: 6.2, 6.3, 6.4, 6.5, 6.6_

- [x] 9. Status Toggle Implementation
  - [x] 9.1 Implement toggleStatus method in controller
    - Handle toggle for all entity types (clients, vendors, locations, brands, categories)
    - Return JSON response for AJAX
    - _Requirements: 7.1_
  - [x] 9.2 Add status toggle UI in all partials
    - Add toggle switch or button for each row
    - Show visual indicator for inactive items
    - _Requirements: 7.1, 7.2_

- [x] 10. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Update Show Pages
  - [x] 11.1 Update Projects show page
    - Display Client name instead of client_name
    - _Requirements: 2.6_
  - [x] 11.2 Update Inventory show page
    - Display Vendor name for asset
    - _Requirements: 3.7_

- [x] 12. Final Integration and Cleanup
  - [x] 12.1 Verify all dropdowns only show active items
    - Check Projects forms (Client, Vendor dropdowns)
    - Check Inventory forms (Vendor, Location, Brand, Category dropdowns)
    - _Requirements: 7.3_
  - [x] 12.2 Run migrations
    - Execute all new migrations
    - Verify data integrity
    - _Requirements: 8.1, 8.2, 8.3, 9.1_

- [x] 13. Final Checkpoint
  - All tasks completed successfully.

## Notes

- Tasks follow the POS Item Inventory TAB pattern exactly
- All CRUD operations use AJAX for better UX
- Deletion is prevented if entity has linked records
- Status toggle affects dropdown visibility in other forms
- Client migration preserves existing project-client relationships
