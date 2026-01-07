# Requirements Document

## Introduction

External Settings adalah halaman konfigurasi untuk modul External yang menggunakan design TAB navigation. Halaman ini membolehkan pengguna mengurus master data yang digunakan dalam Projects dan Inventory seperti Client, Vendor, Location, Brand, dan Category. Setiap TAB akan mempunyai fungsi CRUD (Create, Read, Update, Delete) dengan integrasi ke modul berkaitan.

## Glossary

- **External_Settings**: Halaman utama settings untuk modul External dengan TAB navigation
- **Client**: Entiti pelanggan yang digunakan dalam Projects (menggantikan client_name field)
- **Vendor**: Entiti pembekal yang digunakan dalam Projects dan Inventory
- **Location**: Entiti lokasi hierarki (Site > Building > Floor > Room) untuk Assets
- **Brand**: Entiti jenama untuk Assets
- **Category**: Entiti kategori dengan dynamic fields configuration untuk Assets
- **TAB_Navigation**: Komponen navigasi berbentuk tab untuk switch antara settings

## Requirements

### Requirement 1: TAB Navigation

**User Story:** As a user, I want to navigate between different settings using tabs, so that I can easily manage different master data in one page.

#### Acceptance Criteria

1. WHEN a user visits the External Settings page, THE External_Settings SHALL display a TAB navigation with tabs: Client, Vendor, Location, Brand, Category
2. WHEN a user clicks on a TAB, THE External_Settings SHALL display the corresponding content without page reload (using URL parameter)
3. THE External_Settings SHALL highlight the active TAB with blue border-bottom and blue text color
4. THE External_Settings SHALL persist the active TAB in URL parameter for bookmarking and sharing

### Requirement 2: Client Management

**User Story:** As a user, I want to manage clients, so that I can select them when creating projects instead of typing manually.

#### Acceptance Criteria

1. THE Client TAB SHALL display a table with columns: Name, Contact Person, Phone, Email, Status, Actions
2. WHEN a user clicks "Add Client" button, THE External_Settings SHALL show an inline form or modal to create new client
3. WHEN a user clicks edit action, THE External_Settings SHALL allow editing client details inline or via modal
4. WHEN a user clicks delete action, THE External_Settings SHALL show confirmation modal before deleting
5. IF a client is linked to projects, THEN THE External_Settings SHALL prevent deletion and show warning message
6. THE Client SHALL integrate with Projects create/edit forms as dropdown selection replacing client_name text field

### Requirement 3: Vendor Management

**User Story:** As a user, I want to manage vendors, so that I can select them when creating projects and assets.

#### Acceptance Criteria

1. THE Vendor TAB SHALL display a table with columns: Name, Contact Person, Phone, Email, Status, Actions
2. WHEN a user clicks "Add Vendor" button, THE External_Settings SHALL show an inline form or modal to create new vendor
3. WHEN a user clicks edit action, THE External_Settings SHALL allow editing vendor details inline or via modal
4. WHEN a user clicks delete action, THE External_Settings SHALL show confirmation modal before deleting
5. IF a vendor is linked to projects or assets, THEN THE External_Settings SHALL prevent deletion and show warning message
6. THE Vendor SHALL integrate with Projects create/edit forms as dropdown selection
7. THE Vendor SHALL integrate with Inventory create/edit forms as dropdown selection (move from Project level to Asset level)

### Requirement 4: Location Management

**User Story:** As a user, I want to manage locations hierarchically, so that I can organize asset locations properly.

#### Acceptance Criteria

1. THE Location TAB SHALL display a table with columns: Name, Type, Parent Location, Status, Actions
2. THE Location SHALL support hierarchical structure (Site > Building > Floor > Room)
3. WHEN a user clicks "Add Location" button, THE External_Settings SHALL show form with parent location dropdown
4. WHEN a user clicks edit action, THE External_Settings SHALL allow editing location details
5. WHEN a user clicks delete action, THE External_Settings SHALL show confirmation modal before deleting
6. IF a location has child locations or linked assets, THEN THE External_Settings SHALL prevent deletion and show warning message
7. THE Location SHALL integrate with Inventory create/edit forms as dropdown selection

### Requirement 5: Brand Management

**User Story:** As a user, I want to manage brands, so that I can select them when creating assets.

#### Acceptance Criteria

1. THE Brand TAB SHALL display a table with columns: Name, Status, Actions
2. WHEN a user clicks "Add Brand" button, THE External_Settings SHALL show an inline form or modal to create new brand
3. WHEN a user clicks edit action, THE External_Settings SHALL allow editing brand details inline or via modal
4. WHEN a user clicks delete action, THE External_Settings SHALL show confirmation modal before deleting
5. IF a brand is linked to assets, THEN THE External_Settings SHALL prevent deletion and show warning message
6. THE Brand SHALL integrate with Inventory create/edit forms as dropdown selection

### Requirement 6: Category Management

**User Story:** As a user, I want to manage categories with custom fields, so that I can define different specifications for different asset types.

#### Acceptance Criteria

1. THE Category TAB SHALL display a table with columns: Name, Code, Custom Fields Count, Status, Actions
2. WHEN a user clicks "Add Category" button, THE External_Settings SHALL show form with dynamic fields configuration
3. THE Category form SHALL allow adding custom fields with: Field Name, Field Type (text/number/date/select), Required flag, Options (for select type)
4. WHEN a user clicks edit action, THE External_Settings SHALL allow editing category and its custom fields
5. WHEN a user clicks delete action, THE External_Settings SHALL show confirmation modal before deleting
6. IF a category is linked to assets, THEN THE External_Settings SHALL prevent deletion and show warning message
7. THE Category SHALL integrate with Inventory create/edit forms showing dynamic fields based on selected category

### Requirement 7: Status Toggle

**User Story:** As a user, I want to toggle active/inactive status for master data, so that I can hide unused items without deleting them.

#### Acceptance Criteria

1. WHEN a user toggles status, THE External_Settings SHALL update the is_active field immediately
2. THE External_Settings SHALL show visual indicator for inactive items (grayed out or badge)
3. WHEN filtering in Projects/Inventory forms, THE System SHALL only show active items in dropdowns

### Requirement 8: Database Migration for Client

**User Story:** As a developer, I want to create Client model and migrate existing client_name data, so that clients can be managed as separate entities.

#### Acceptance Criteria

1. THE System SHALL create clients table with fields: id, name, contact_person, phone, email, is_active, timestamps
2. THE System SHALL migrate existing unique client_name values from projects table to clients table
3. THE System SHALL add client_id foreign key to projects table
4. THE System SHALL update Project model to use belongsTo Client relationship
5. THE System SHALL update Projects create/edit forms to use Client dropdown instead of client_name text field

### Requirement 9: Vendor Integration with Inventory

**User Story:** As a user, I want to assign vendor per asset instead of per project, so that I can track which vendor supplied each asset.

#### Acceptance Criteria

1. THE System SHALL add vendor_id foreign key to assets table
2. THE System SHALL update Asset model to use belongsTo Vendor relationship
3. THE System SHALL update Inventory create/edit forms to include Vendor dropdown
4. THE System SHALL keep vendor_id in projects table for project-level vendor (optional)
