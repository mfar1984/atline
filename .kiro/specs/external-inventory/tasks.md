# Implementation Plan: External Inventory Module

## Overview

Implementation plan untuk External Inventory Module menggunakan Laravel 11.x dengan pendekatan incremental - bermula dengan database migrations dan models, kemudian controllers dan views, dan akhirnya testing.

## Tasks

- [x] 1. Setup Database Migrations dan Models
  - [x] 1.1 Create migration untuk `projects` table (already exists)
  - [x] 1.2 Create migration untuk `categories` table
  - [x] 1.3 Create migration untuk `brands` table
  - [x] 1.4 Create migration untuk `locations` table dengan hierarchical structure
  - [x] 1.5 Create migration untuk `measurements` table
  - [x] 1.6 Create migration untuk `vendors` table
  - [x] 1.7 Create migration untuk `assets` table
  - [x] 1.8 Create migration untuk `attachments` table (polymorphic)
  - [x] 1.9 Create migration untuk `asset_logs` table

- [x] 2. Create Eloquent Models dengan Relationships
  - [x] 2.1 Create Project model dengan relationships dan validation rules
  - [x] 2.2 Create Category model dengan fields_config accessor
  - [x] 2.3 Create Asset model dengan relationships dan JSON casting untuk specs
  - [x] 2.4 Create Attachment model (polymorphic)
  - [x] 2.5 Create AssetLog model
  - [x] 2.6 Create master data models (Brand, Location, Measurement, Vendor)
  - [ ]* 2.7 Write property test for Project name validation
  - [ ]* 2.8 Write property test for Asset mandatory fields validation

- [x] 3. Checkpoint - Run migrations dan verify models

- [x] 4. Create Services dan Business Logic
  - [x] 4.1 Create AssetService dengan generateAssetId method
  - [x] 4.2 Create AssetService method untuk validate dynamic fields
  - [x] 4.3 Create AssetService method untuk log status/location/assignment changes
  - [x] 4.4 Create AssetService method untuk get assets with expiring warranty
  - [x] 4.5 Create AttachmentService untuk file upload handling
  - [ ]* 4.6 Write property test for unique Asset ID generation
  - [ ]* 4.7 Write property test for warranty expiry detection

- [x] 5. Checkpoint - Verify services

- [x] 6. Create Form Requests untuk Validation
  - [x] 6.1 Create ProjectRequest dengan validation rules (already exists)
  - [x] 6.2 Create AssetRequest dengan validation rules
  - [ ] 6.3 Create AttachmentRequest dengan file validation
  - [ ] 6.4 Create master data requests (CategoryRequest, LocationRequest, BrandRequest, MeasurementRequest)
  - [ ]* 6.5 Write property test for file type validation

- [ ] 7. Create Controllers - Projects (already exists)
  - [x] 7.1 Create ProjectController dengan CRUD methods
  - [ ] 7.2 Implement project deletion protection (check for linked assets)
  - [ ] 7.3 Implement project search/filter functionality
  - [ ]* 7.4 Write property test for project deletion protection

- [x] 8. Create Controllers - Assets/Inventory
  - [x] 8.1 Create AssetController dengan CRUD methods
  - [x] 8.2 Implement getDynamicFields API endpoint
  - [x] 8.3 Implement asset filtering functionality
  - [x] 8.4 Implement status change logging in update method
  - [ ]* 8.5 Write property test for dynamic fields by category
  - [ ]* 8.6 Write property test for audit log completeness

- [x] 9. Create Controllers - Attachments
  - [x] 9.1 Create AttachmentController dengan download, destroy
  - [ ] 9.2 Implement attachment filtering
  - [x] 9.3 Implement file download functionality
  - [ ]* 9.4 Write property test for attachment metadata completeness
  - [ ]* 9.5 Write property test for attachment display completeness

- [x] 10. Checkpoint - Verify controllers

- [ ] 11. Create Controllers - Settings (Master Data)
  - [ ] 11.1 Create CategoryController dengan CRUD dan fields config management
  - [ ] 11.2 Create LocationController dengan hierarchical CRUD
  - [ ] 11.3 Create BrandController dengan CRUD
  - [ ] 11.4 Create MeasurementController dengan CRUD
  - [ ] 11.5 Implement deletion protection for master data in use
  - [ ]* 11.6 Write property test for master data deletion protection
  - [ ]* 11.7 Write property test for master data CRUD operations

- [ ] 12. Create Controllers - Reports
  - [ ] 12.1 Create ReportController dengan summary statistics
  - [ ] 12.2 Implement report filtering
  - [ ] 12.3 Implement warranty expiry alerts in reports
  - [ ] 12.4 Implement asset value summary
  - [ ] 12.5 Implement export to Excel and PDF
  - [ ]* 12.6 Write property test for report statistics accuracy
  - [ ]* 12.7 Write property test for filter results accuracy

- [ ] 13. Checkpoint - Verify all controllers

- [x] 14. Create Routes
  - [x] 14.1 Define routes untuk External module dalam routes/web.php
    - Inventory: resource routes + getDynamicFields + generateAssetId
    - Attachments: download, destroy

- [ ] 15. Create Blade Views - Projects (already exists)
  - [x] 15.1 Create projects/index.blade.php dengan DataTable dan filters
  - [x] 15.2 Create projects/create.blade.php dan edit.blade.php forms
  - [ ] 15.3 Create projects/show.blade.php dengan attachments section

- [x] 16. Create Blade Views - Inventory
  - [x] 16.1 Create inventory/index.blade.php dengan DataTable dan filters
  - [x] 16.2 Create inventory/create.blade.php dengan dynamic fields
  - [x] 16.3 Create inventory/edit.blade.php dengan dynamic fields
  - [x] 16.4 Create inventory/show.blade.php dengan all sections

- [ ] 17. Create Blade Views - Attachments
  - [ ] 17.1 Create attachments/index.blade.php dengan stat cards view
  - [ ] 17.2 Implement attachment filters UI

- [ ] 18. Create Blade Views - Reports
  - [ ] 18.1 Create reports/index.blade.php dengan summary statistics
  - [ ] 18.2 Implement report filters UI
  - [ ] 18.3 Add warranty expiry alerts section
  - [ ] 18.4 Add export buttons (Excel, PDF)

- [ ] 19. Create Blade Views - Settings
  - [ ] 19.1 Create settings/index.blade.php dengan tabs
  - [ ] 19.2 Create CRUD modals/forms untuk each tab

- [ ] 20. Update Sidebar Navigation
  - [ ] 20.1 Add External menu dengan submenus

- [x] 21. Create Database Seeders
  - [x] 21.1 Create CategorySeeder dengan default categories dan fields config
  - [x] 21.2 Create AssetMasterDataSeeder (Brands, Locations, Vendors)

- [ ] 22. Final Checkpoint - Full Integration Testing

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Laravel conventions followed throughout (naming, structure)
