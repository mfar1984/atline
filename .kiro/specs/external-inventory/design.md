# Design Document: External Inventory Module

## Overview

External Inventory Module adalah sistem pengurusan aset IT berasaskan Laravel yang membolehkan organisasi menguruskan project-based inventory dengan sokongan dynamic fields berdasarkan kategori aset. Sistem ini menggunakan arsitektur MVC Laravel dengan Blade templates, Eloquent ORM, dan MySQL/SQLite database.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Presentation Layer                      │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌────────┐│
│  │Projects │ │Inventory│ │ Reports │ │Attachmt │ │Settings││
│  │  View   │ │  View   │ │  View   │ │  View   │ │  View  ││
│  └────┬────┘ └────┬────┘ └────┬────┘ └────┬────┘ └───┬────┘│
└───────┼──────────┼──────────┼──────────┼───────────┼───────┘
        │          │          │          │           │
┌───────┴──────────┴──────────┴──────────┴───────────┴───────┐
│                      Controller Layer                       │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────┐│
│  │  Project    │ │   Asset     │ │  Settings Controllers   ││
│  │ Controller  │ │ Controller  │ │ (Category,Location,etc) ││
│  └──────┬──────┘ └──────┬──────┘ └───────────┬─────────────┘│
└─────────┼───────────────┼────────────────────┼──────────────┘
          │               │                    │
┌─────────┴───────────────┴────────────────────┴──────────────┐
│                       Service Layer                          │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────────┐│
│  │  Project    │ │   Asset     │ │   Master Data Service   ││
│  │  Service    │ │  Service    │ │                         ││
│  └──────┬──────┘ └──────┬──────┘ └───────────┬─────────────┘│
└─────────┼───────────────┼────────────────────┼──────────────┘
          │               │                    │
┌─────────┴───────────────┴────────────────────┴──────────────┐
│                        Model Layer                           │
│  ┌────────┐ ┌─────┐ ┌────────┐ ┌──────────┐ ┌─────────────┐ │
│  │Project │ │Asset│ │Category│ │Attachment│ │ MasterData  │ │
│  └────────┘ └─────┘ └────────┘ └──────────┘ └─────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Database**: MySQL 8.0 / SQLite
- **Frontend**: Blade Templates, Alpine.js, Tailwind CSS
- **File Storage**: Laravel Storage (local/S3)
- **Testing**: PHPUnit, Pest PHP

## Components and Interfaces

### Controllers

#### ProjectController
```php
class ProjectController extends Controller
{
    public function index(): View                    // List all projects
    public function create(): View                   // Show create form
    public function store(ProjectRequest $request): RedirectResponse
    public function show(Project $project): View     // View project details
    public function edit(Project $project): View     // Show edit form
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    public function destroy(Project $project): RedirectResponse
}
```

#### AssetController
```php
class AssetController extends Controller
{
    public function index(): View                    // List all assets with filters
    public function create(): View                   // Show create form
    public function store(AssetRequest $request): RedirectResponse
    public function show(Asset $asset): View         // View asset details
    public function edit(Asset $asset): View         // Show edit form
    public function update(AssetRequest $request, Asset $asset): RedirectResponse
    public function destroy(Asset $asset): RedirectResponse
    public function getDynamicFields(Category $category): JsonResponse
}
```

#### AttachmentController
```php
class AttachmentController extends Controller
{
    public function index(): View                    // Centralized attachments view
    public function store(AttachmentRequest $request): RedirectResponse
    public function download(Attachment $attachment): BinaryFileResponse
    public function destroy(Attachment $attachment): RedirectResponse
}
```

#### Settings Controllers
```php
class CategoryController extends Controller { /* CRUD operations */ }
class LocationController extends Controller { /* CRUD with hierarchy */ }
class BrandController extends Controller { /* CRUD operations */ }
class MeasurementController extends Controller { /* CRUD operations */ }
```

### Services

#### AssetService
```php
class AssetService
{
    public function generateAssetId(Category $category): string
    public function validateDynamicFields(array $data, Category $category): bool
    public function logStatusChange(Asset $asset, string $oldStatus, string $newStatus): void
    public function getAssetsExpiringWarranty(int $days = 30): Collection
}
```

## Data Models

### Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Project   │       │    Asset    │       │  Category   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │◄──────│ project_id  │       │ id          │
│ name        │       │ category_id │──────►│ name        │
│ description │       │ asset_tag   │       │ code        │
│ client_name │       │ brand_id    │       │ fields_config│
│ start_date  │       │ model       │       │ is_active   │
│ end_date    │       │ serial_no   │       └─────────────┘
│ status      │       │ status      │
│ created_at  │       │ specs (JSON)│       ┌─────────────┐
│ updated_at  │       │ purchase_date│      │   Brand     │
└─────────────┘       │ unit_price  │       ├─────────────┤
                      │ vendor_id   │──────►│ id          │
┌─────────────┐       │ warranty_exp│       │ name        │
│ Attachment  │       │ location_id │       │ is_active   │
├─────────────┤       │ assigned_to │       └─────────────┘
│ id          │       │ department  │
│ attachable_id│      │ notes       │       ┌─────────────┐
│ attachable_type│    │ created_at  │       │  Location   │
│ file_name   │       │ updated_at  │       ├─────────────┤
│ file_path   │       └─────────────┘       │ id          │
│ file_type   │              │              │ parent_id   │
│ file_size   │              │              │ name        │
│ uploaded_by │              ▼              │ type        │
│ created_at  │       ┌─────────────┐       │ is_active   │
└─────────────┘       │ AssetLog    │       └─────────────┘
                      ├─────────────┤
                      │ id          │       ┌─────────────┐
                      │ asset_id    │       │ Measurement │
                      │ field_name  │       ├─────────────┤
                      │ old_value   │       │ id          │
                      │ new_value   │       │ name        │
                      │ changed_by  │       │ unit        │
                      │ created_at  │       │ type        │
                      └─────────────┘       │ is_active   │
                                            └─────────────┘
```

### Database Schema

#### projects
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| name | varchar(255) | NOT NULL |
| description | text | NULLABLE |
| client_name | varchar(255) | NULLABLE |
| start_date | date | NULLABLE |
| end_date | date | NULLABLE |
| status | enum('active','completed','on_hold') | DEFAULT 'active' |
| created_at | timestamp | |
| updated_at | timestamp | |

#### assets
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| project_id | bigint | FK -> projects.id |
| category_id | bigint | FK -> categories.id |
| asset_tag | varchar(50) | UNIQUE, NOT NULL |
| brand_id | bigint | FK -> brands.id, NULLABLE |
| model | varchar(255) | NULLABLE |
| serial_number | varchar(255) | NULLABLE |
| status | enum('active','spare','damaged','maintenance','disposed') | DEFAULT 'active' |
| specs | json | NULLABLE (dynamic fields) |
| purchase_date | date | NULLABLE |
| unit_price | decimal(12,2) | NULLABLE |
| po_number | varchar(100) | NULLABLE |
| vendor_id | bigint | FK -> vendors.id, NULLABLE |
| warranty_period | varchar(50) | NULLABLE |
| warranty_expiry | date | NULLABLE |
| location_id | bigint | FK -> locations.id, NULLABLE |
| assigned_to | varchar(255) | NULLABLE |
| department | varchar(255) | NULLABLE |
| notes | text | NULLABLE |
| created_at | timestamp | |
| updated_at | timestamp | |

#### categories
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| name | varchar(255) | NOT NULL |
| code | varchar(50) | UNIQUE |
| fields_config | json | Dynamic fields configuration |
| is_active | boolean | DEFAULT true |
| created_at | timestamp | |
| updated_at | timestamp | |

#### attachments (Polymorphic)
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| attachable_id | bigint | NOT NULL |
| attachable_type | varchar(255) | NOT NULL |
| file_name | varchar(255) | NOT NULL |
| file_path | varchar(500) | NOT NULL |
| file_type | varchar(50) | NOT NULL |
| file_size | bigint | NOT NULL |
| uploaded_by | bigint | FK -> users.id |
| created_at | timestamp | |
| updated_at | timestamp | |

#### asset_logs
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, auto-increment |
| asset_id | bigint | FK -> assets.id |
| field_name | varchar(100) | NOT NULL |
| old_value | text | NULLABLE |
| new_value | text | NULLABLE |
| changed_by | bigint | FK -> users.id |
| created_at | timestamp | |

### Dynamic Fields Configuration (JSON)

```json
{
  "PC": {
    "fields": [
      {"name": "cpu", "label": "Processor (CPU)", "type": "text", "required": false},
      {"name": "ram", "label": "RAM", "type": "select", "options": "measurements:ram", "required": false},
      {"name": "storage", "label": "Storage", "type": "select", "options": "measurements:storage", "required": false},
      {"name": "os", "label": "Operating System", "type": "text", "required": false},
      {"name": "hostname", "label": "Hostname", "type": "text", "required": false}
    ]
  },
  "Network Switch": {
    "fields": [
      {"name": "port_count", "label": "Port Count", "type": "select", "options": ["8","24","48"], "required": false},
      {"name": "speed", "label": "Speed", "type": "text", "required": false},
      {"name": "switch_type", "label": "Type", "type": "select", "options": ["Managed","Unmanaged"], "required": false},
      {"name": "ip_address", "label": "IP Address", "type": "text", "required": false},
      {"name": "firmware", "label": "Firmware Version", "type": "text", "required": false}
    ]
  },
  "Software License": {
    "fields": [
      {"name": "software_name", "label": "Software Name", "type": "text", "required": true},
      {"name": "license_key", "label": "License Key", "type": "text", "required": false},
      {"name": "license_type", "label": "License Type", "type": "select", "options": ["Perpetual","Subscription"], "required": false},
      {"name": "license_start", "label": "License Start Date", "type": "date", "required": false},
      {"name": "license_end", "label": "License End Date", "type": "date", "required": false},
      {"name": "seats", "label": "Number of Seats", "type": "number", "required": false}
    ]
  }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Project Name Validation
*For any* project creation attempt, the system shall reject the creation if project name is empty or null, and accept it if project name is provided.
**Validates: Requirements 1.1**

### Property 2: Project Deletion Protection
*For any* project with one or more linked assets, attempting to delete the project shall fail and return an error, preserving the project and all linked assets.
**Validates: Requirements 1.5**

### Property 3: Asset Mandatory Fields Validation
*For any* asset creation attempt, the system shall reject the creation if Asset ID, Category, or Project is missing, and accept it if all mandatory fields are provided.
**Validates: Requirements 2.1**

### Property 4: Unique Asset ID Generation
*For any* set of auto-generated Asset IDs, all IDs shall be unique - no two assets shall have the same Asset ID.
**Validates: Requirements 2.4**

### Property 5: Dynamic Fields by Category
*For any* category selection, the system shall return the correct set of dynamic fields as configured for that category - PC/Laptop/Server returns CPU, RAM, Storage, OS, Hostname; Network Switch returns Port Count, Speed, Type, IP Address, Firmware; Software License returns Software Name, License Key, License Type, Start/End Date, Seats.
**Validates: Requirements 3.1, 3.2, 3.3**

### Property 6: Attachment Metadata Completeness
*For any* successfully uploaded attachment (to project or asset), the stored record shall contain non-null values for file_name, file_type, upload_date, and uploaded_by.
**Validates: Requirements 1.4, 6.2**

### Property 7: Attachment File Type Validation
*For any* file upload attempt, the system shall accept files with extensions JPG, PNG, PDF, DOC, DOCX and reject files with other extensions.
**Validates: Requirements 6.1**

### Property 8: Audit Log Completeness
*For any* change to asset status, location, or assignment, the system shall create a log entry containing the asset_id, field_name, old_value, new_value, changed_by, and timestamp.
**Validates: Requirements 2.6, 5.3, 6.4**

### Property 9: Warranty Expiry Alert
*For any* asset with warranty_expiry date within 30 days from current date, the asset shall be flagged in warranty expiry reports.
**Validates: Requirements 4.3, 9.4**

### Property 10: Master Data Deletion Protection
*For any* master data item (Category, Location, Brand, Measurement) that is referenced by one or more assets, attempting to delete the item shall fail and return an error.
**Validates: Requirements 8.6**

### Property 11: Filter Results Accuracy
*For any* filter applied to assets, projects, or attachments, all returned results shall match the filter criteria, and no matching items shall be excluded from results.
**Validates: Requirements 1.6, 2.5, 5.4, 7.3, 9.2**

### Property 12: Master Data CRUD Operations
*For any* master data type (Category, Brand, Location, Measurement), creating an item then reading it shall return the same data, updating an item shall persist the changes, and deleting an unreferenced item shall remove it from the system.
**Validates: Requirements 8.2, 8.3, 8.4, 8.5**

### Property 13: Attachment Display Completeness
*For any* attachment displayed in the centralized attachments view, the display shall include file_name, project_name, file_type, upload_date, and action buttons.
**Validates: Requirements 6.3, 7.2**

### Property 14: Report Statistics Accuracy
*For any* report generated, the total asset count shall equal the sum of assets by category, and the sum of assets by status, and the sum of assets by location.
**Validates: Requirements 9.1, 9.5**

## Error Handling

### Validation Errors
- Return 422 Unprocessable Entity with field-specific error messages
- Display errors inline on forms using Laravel validation

### Business Rule Violations
- Project deletion with linked assets: Return error message "Cannot delete project with linked assets"
- Master data deletion in use: Return error message "Cannot delete [item] as it is in use by [count] assets"

### File Upload Errors
- Invalid file type: Return "File type not allowed. Accepted types: JPG, PNG, PDF, DOC, DOCX"
- File too large: Return "File size exceeds maximum limit of 10MB"
- Storage failure: Log error and return "Failed to upload file. Please try again."

### Database Errors
- Unique constraint violation: Return user-friendly message about duplicate entry
- Foreign key violation: Return message about related data dependency

## Testing Strategy

### Unit Tests
- Model validation rules
- Service method logic (AssetService, etc.)
- Helper functions

### Property-Based Tests (using Pest PHP with faker)
- Property 1: Project name validation across random inputs
- Property 2: Project deletion protection with various asset counts
- Property 3: Asset mandatory fields validation
- Property 4: Unique Asset ID generation across multiple creations
- Property 5: Dynamic fields configuration by category
- Property 6: Attachment metadata completeness
- Property 7: File type validation
- Property 8: Audit log creation
- Property 9: Warranty expiry detection
- Property 10: Master data deletion protection
- Property 11: Filter accuracy
- Property 12: Master data CRUD round-trip
- Property 13: Attachment display fields
- Property 14: Report statistics consistency

### Integration Tests
- Full CRUD workflows for Projects, Assets, Attachments
- File upload and download flows
- Report generation and export

### Configuration
- Minimum 100 iterations per property test
- Tag format: **Feature: external-inventory, Property {number}: {property_text}**
