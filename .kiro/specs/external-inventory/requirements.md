# Requirements Document

## Introduction

External Inventory Module adalah sistem pengurusan aset IT yang komprehensif untuk menguruskan project-based inventory. Sistem ini membolehkan organisasi menjejak aset IT (PC, Laptop, Server, Network Switch, Software License) yang disewakan atau digunakan dalam pelbagai project, dengan sokongan untuk dynamic fields berdasarkan kategori aset, file attachments, dan master data management.

## Glossary

- **Project**: Entiti utama yang mewakili projek sewaan atau penggunaan aset (contoh: Sewaan Komputer ABC Sdn Bhd)
- **Asset**: Item inventori yang didaftarkan dalam sistem (PC, Laptop, Server, Switch, Software)
- **Category**: Jenis klasifikasi aset yang menentukan dynamic fields yang dipaparkan
- **Asset_Management_System**: Sistem keseluruhan untuk menguruskan External Inventory
- **Dynamic_Fields**: Fields tambahan yang muncul berdasarkan kategori aset yang dipilih
- **Master_Data**: Data rujukan seperti Categories, Locations, Brands, Measurements, Vendors, Departments
- **Attachment**: Fail yang dilampirkan kepada Project atau Asset

## Requirements

### Requirement 1: Project Management

**User Story:** As a user, I want to create and manage projects, so that I can organize assets under specific projects and attach relevant documentation.

#### Acceptance Criteria

1. WHEN a user creates a new project, THE Asset_Management_System SHALL require project name as mandatory field
2. WHEN a user creates a project, THE Asset_Management_System SHALL allow input of project description, client name, start date, end date, and status
3. WHEN a user views a project, THE Asset_Management_System SHALL display all associated file attachments
4. WHEN a user uploads a file attachment to a project, THE Asset_Management_System SHALL store the file with metadata including file name, file type, upload date, and uploaded by
5. WHEN a user deletes a project, THE Asset_Management_System SHALL prevent deletion if assets are still linked to the project
6. WHEN a user searches for projects, THE Asset_Management_System SHALL allow filtering by project name, status, and date range

### Requirement 2: Asset/Inventory Management - Basic Information

**User Story:** As a user, I want to register and manage IT assets with basic information, so that I can track all assets across projects.

#### Acceptance Criteria

1. WHEN a user creates a new asset, THE Asset_Management_System SHALL require Asset ID/Tag No, Category, and Project as mandatory fields
2. WHEN a user creates an asset, THE Asset_Management_System SHALL allow input of Brand, Model, Serial Number, and Status
3. WHEN a user selects a category, THE Asset_Management_System SHALL display dynamic fields specific to that category
4. THE Asset_Management_System SHALL generate unique Asset ID automatically if not provided manually
5. WHEN a user views asset list, THE Asset_Management_System SHALL display assets with filtering by Project, Category, Status, and Location
6. WHEN a user updates asset status, THE Asset_Management_System SHALL log the status change with timestamp and user

### Requirement 3: Asset/Inventory Management - Dynamic Technical Specifications

**User Story:** As a user, I want to input technical specifications based on asset category, so that I can capture relevant details for different types of IT equipment.

#### Acceptance Criteria

1. WHEN a user selects PC, Laptop, or Server category, THE Asset_Management_System SHALL display fields for CPU, RAM, Storage, Operating System, and Hostname
2. WHEN a user selects Network Switch category, THE Asset_Management_System SHALL display fields for Port Count, Speed, Type (Managed/Unmanaged), IP Address, and Firmware Version
3. WHEN a user selects Software License category, THE Asset_Management_System SHALL display fields for Software Name, License Key, License Type, Start Date, End Date, and Number of Seats
4. WHEN dynamic fields are displayed, THE Asset_Management_System SHALL validate required fields based on category configuration
5. THE Asset_Management_System SHALL store dynamic specifications in a flexible data structure

### Requirement 4: Asset/Inventory Management - Procurement & Financial Information

**User Story:** As a user, I want to track procurement and financial details of assets, so that I can manage warranties and vendor relationships.

#### Acceptance Criteria

1. WHEN a user creates or edits an asset, THE Asset_Management_System SHALL allow input of Purchase Date, Unit Price, PO/Invoice Number, and Vendor
2. WHEN a user inputs warranty information, THE Asset_Management_System SHALL capture Warranty Period and Warranty Expiry Date
3. WHEN warranty is about to expire (30 days before), THE Asset_Management_System SHALL flag the asset for attention in reports
4. WHEN a user views asset details, THE Asset_Management_System SHALL display all procurement information in a dedicated section

### Requirement 5: Asset/Inventory Management - Location & Assignment Tracking

**User Story:** As a user, I want to track asset locations and assignments, so that I can know where each asset is and who is responsible for it.

#### Acceptance Criteria

1. WHEN a user creates or edits an asset, THE Asset_Management_System SHALL allow selection of Location from master data
2. WHEN a user assigns an asset to a person, THE Asset_Management_System SHALL capture Assigned To name and Department
3. WHEN an asset location or assignment changes, THE Asset_Management_System SHALL log the change with timestamp and previous values
4. WHEN a user searches assets, THE Asset_Management_System SHALL allow filtering by Location and Assigned To

### Requirement 6: Asset Documentation & Attachments

**User Story:** As a user, I want to attach documents and images to assets, so that I can maintain complete documentation for each asset.

#### Acceptance Criteria

1. WHEN a user uploads an attachment to an asset, THE Asset_Management_System SHALL accept images (JPG, PNG) and documents (PDF, DOC, DOCX)
2. WHEN a user uploads an attachment, THE Asset_Management_System SHALL store file metadata including file name, file type, upload date, and uploaded by
3. WHEN a user views the Attachments page, THE Asset_Management_System SHALL display all attachments as stat cards with file name, project name, file type, and actions
4. WHEN a user adds notes to an asset, THE Asset_Management_System SHALL save the notes with timestamp for audit trail

### Requirement 7: Centralized Attachments View

**User Story:** As a user, I want to view all attachments in one centralized page, so that I can quickly find and manage files across all projects and assets.

#### Acceptance Criteria

1. WHEN a user visits the Attachments page, THE Asset_Management_System SHALL display all files as stat cards
2. WHEN displaying attachment cards, THE Asset_Management_System SHALL show file name, associated project name, file type, upload date, and action buttons
3. WHEN a user filters attachments, THE Asset_Management_System SHALL allow filtering by project, file type, and date range
4. WHEN a user clicks on an attachment, THE Asset_Management_System SHALL allow preview or download based on file type
5. WHEN a user deletes an attachment, THE Asset_Management_System SHALL remove the file and update related records

### Requirement 8: Master Data Settings Management

**User Story:** As a user, I want to manage master data through settings tabs, so that I can customize dropdown options for the inventory system.

#### Acceptance Criteria

1. WHEN a user accesses External Settings, THE Asset_Management_System SHALL display tabs for Categories, Locations, Brand Names, and Measurements
2. WHEN a user manages Categories, THE Asset_Management_System SHALL allow CRUD operations and configuration of which dynamic fields appear for each category
3. WHEN a user manages Locations, THE Asset_Management_System SHALL allow CRUD operations with hierarchical structure (Site > Building > Floor > Room)
4. WHEN a user manages Brand Names, THE Asset_Management_System SHALL allow CRUD operations for brand master data
5. WHEN a user manages Measurements, THE Asset_Management_System SHALL allow CRUD operations for measurement units (RAM sizes, Storage sizes, etc.)
6. WHEN master data is deleted, THE Asset_Management_System SHALL prevent deletion if data is in use by existing assets

### Requirement 9: Reports & Analytics

**User Story:** As a user, I want to generate reports on inventory data, so that I can analyze asset distribution and status.

#### Acceptance Criteria

1. WHEN a user accesses Reports page, THE Asset_Management_System SHALL display summary statistics (Total Assets, By Category, By Status, By Location)
2. WHEN a user generates a report, THE Asset_Management_System SHALL allow filtering by Project, Category, Status, Location, and Date Range
3. WHEN a user exports a report, THE Asset_Management_System SHALL support export to Excel and PDF formats
4. WHEN displaying reports, THE Asset_Management_System SHALL show warranty expiry alerts for assets expiring within 30 days
5. WHEN a user views reports, THE Asset_Management_System SHALL display asset value summary by project and category
