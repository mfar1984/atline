# Implementation Plan: Inventory Bulk Create

## Overview

Redesign inventory create page untuk membolehkan pengguna menambah multiple assets sekaligus dalam satu form submission.

## Tasks

- [x] 1. Update AssetController store method for bulk create
  - Modify validation rules to accept items array
  - Loop through items and create multiple assets
  - Handle file attachments for all created assets
  - Return success message with count
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 2. Update AssetRequest validation
  - [x] 2.1 Add validation for items array
    - Validate items.*.asset_tag is required and unique
    - Validate items.*.brand_id exists in brands table
    - Validate items.*.model is string
    - Validate items.*.serial_number is string
    - _Requirements: 4.1_

- [x] 3. Redesign create.blade.php view
  - [x] 3.1 Create Common Information section
    - Move Project, Category, Status to common section
    - Move Location, Vendor, Assigned To, Department, Notes to common section
    - Keep dynamic technical specifications
    - _Requirements: 1.1, 1.2, 1.3_

  - [x] 3.2 Create Asset Items table section
    - Add table with columns: Asset Tag/ID, Brand, Model, Serial Number, Action
    - Add "ADD ITEM" button in header
    - Style similar to File Attachments section
    - _Requirements: 2.1, 2.2_

  - [x] 3.3 Implement Alpine.js assetItems component
    - Create items array with default one row
    - Implement addItem() function
    - Implement removeItem(index) function
    - Implement generateAssetTag(index) function
    - _Requirements: 2.3, 2.4, 2.5, 3.1, 3.2_

  - [x] 3.4 Update form field names for bulk submission
    - Change asset_tag to items[index][asset_tag]
    - Change brand_id to items[index][brand_id]
    - Change model to items[index][model]
    - Change serial_number to items[index][serial_number]
    - _Requirements: 4.2_

- [x] 4. Update edit.blade.php view (single asset edit)
  - Keep edit page for single asset editing
  - Ensure edit page still works correctly
  - _Requirements: N/A (maintain existing functionality)_

- [x] 5. Checkpoint - Test bulk create functionality
  - All code verified with no syntax errors
  - Controller store method handles bulk items array
  - Create view has Asset Items table with ADD ITEM button
  - Edit view remains for single asset editing

- [x] 6. Final testing and cleanup
  - Test creating multiple assets
  - Test file attachments on bulk create
  - Test validation errors
  - Clean up any unused code
  - _Requirements: All_

## Notes

- Edit page remains for single asset editing
- Create page is redesigned for bulk creation
- File attachments are duplicated to all created assets
- Dynamic specs apply to all assets in bulk create
