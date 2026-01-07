# Requirements Document

## Introduction

Sistem untuk menambah multiple assets (inventory) sekaligus dalam satu form submission. Form dibahagikan kepada dua bahagian:
1. Maklumat common/group (Project, Category, Location, Vendor, Assigned To, Department, Notes)
2. Table untuk menambah multiple asset items (Asset Tag, Brand, Model, Serial Number)

Ini memudahkan pengguna menambah banyak assets yang berkongsi maklumat yang sama (contoh: 10 unit PC untuk project yang sama, lokasi yang sama, vendor yang sama).

## Glossary

- **Asset**: Item inventori IT seperti PC, laptop, printer, dll
- **Asset_Group**: Maklumat common yang dikongsi oleh multiple assets
- **Asset_Item**: Satu unit asset dengan Asset Tag, Brand, Model, Serial Number yang unik
- **Bulk_Create**: Proses menambah multiple assets sekaligus

## Requirements

### Requirement 1: Common Information Section

**User Story:** As a user, I want to enter common information once for multiple assets, so that I can save time when adding similar assets.

#### Acceptance Criteria

1. THE Create_Asset_Form SHALL display a "Common Information" section at the top
2. WHEN the form loads, THE System SHALL display fields for: Project (required), Category (required), Status, Location, Vendor, Assigned To, Department, Notes
3. THE Common_Information SHALL apply to all asset items added in the table below

### Requirement 2: Asset Items Table

**User Story:** As a user, I want to add multiple asset items in a table format, so that I can create many assets at once.

#### Acceptance Criteria

1. THE Create_Asset_Form SHALL display an "Asset Items" section with a table format similar to File Attachments
2. THE Asset_Items_Table SHALL have columns: Asset Tag/ID, Brand (dropdown), Model, Serial Number, and Delete button
3. WHEN user clicks "ADD ITEM" button, THE System SHALL add a new row to the table
4. THE System SHALL allow adding unlimited asset item rows
5. WHEN user clicks delete button on a row, THE System SHALL remove that row from the table

### Requirement 3: Auto-Generate Asset Tag

**User Story:** As a user, I want to auto-generate asset tags, so that I can ensure unique and consistent naming.

#### Acceptance Criteria

1. WHEN a new row is added, THE System SHALL display an "AUTO" button next to Asset Tag field
2. WHEN user clicks "AUTO" button, THE System SHALL generate a unique asset tag based on selected Category
3. IF Category is not selected, THEN THE System SHALL disable the AUTO button
4. THE Generated_Asset_Tag SHALL follow the format: {CATEGORY_PREFIX}-{YEAR}-{SEQUENCE}

### Requirement 4: Form Submission

**User Story:** As a user, I want to submit the form and create all assets at once, so that I can efficiently add inventory.

#### Acceptance Criteria

1. WHEN user submits the form, THE System SHALL validate all required fields
2. WHEN user submits the form with at least one asset item, THE System SHALL create all assets with the common information
3. IF no asset items are added, THEN THE System SHALL show an error message
4. WHEN assets are created successfully, THE System SHALL redirect to inventory index with success message showing count of created assets

### Requirement 5: Dynamic Technical Specifications

**User Story:** As a user, I want to see category-specific fields, so that I can enter relevant technical specifications.

#### Acceptance Criteria

1. WHEN Category is selected, THE System SHALL load dynamic specification fields
2. THE Dynamic_Fields SHALL appear in the Common Information section
3. THE Dynamic_Fields SHALL apply to all asset items created

### Requirement 6: File Attachments

**User Story:** As a user, I want to attach files to the assets, so that I can store related documents.

#### Acceptance Criteria

1. THE Create_Asset_Form SHALL display a "File Attachments" section
2. THE File_Attachments SHALL be associated with all assets created in the submission
3. THE System SHALL support multiple file uploads with display names
