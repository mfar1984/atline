# Requirements: Role-Based Permission Enforcement System

## Overview

Sistem ini akan menguatkuasakan permission matrix dari `/settings/roles` ke seluruh modul internal dan settings. Setiap checkbox dalam permission matrix akan mengawal akses UI (hide/show) dan backend (allow/block).

## User Stories

### US-1: Sidebar Menu Visibility
**Sebagai** pengguna dengan role tertentu,
**Saya mahu** sidebar menu hanya menunjukkan modul yang saya ada akses,
**Supaya** saya tidak nampak menu yang tidak boleh saya akses.

**Acceptance Criteria:**
- AC-1.1: Menu item tersembunyi jika SEMUA permission untuk modul itu unchecked
- AC-1.2: Menu item visible jika SEKURANG-KURANGNYA satu permission checked
- AC-1.3: Submenu item tersembunyi berdasarkan permission modul masing-masing
- AC-1.4: Parent menu tersembunyi jika SEMUA children tersembunyi

### US-2: Action Button Visibility
**Sebagai** pengguna dengan role tertentu,
**Saya mahu** butang action (Create, Edit, Delete, Export) hanya visible jika saya ada permission,
**Supaya** saya tidak nampak butang yang tidak boleh saya guna.

**Acceptance Criteria:**
- AC-2.1: Butang "Create/Add" tersembunyi jika `create` permission unchecked
- AC-2.2: Butang "Edit" tersembunyi jika `update` permission unchecked
- AC-2.3: Butang "Delete" tersembunyi jika `delete` permission unchecked
- AC-2.4: Butang "Export" tersembunyi jika `export` permission unchecked
- AC-2.5: Butang "Assign" tersembunyi jika `assign` permission unchecked
- AC-2.6: Icon action dalam table row juga ikut permission yang sama

### US-3: Backend Permission Enforcement
**Sebagai** sistem,
**Saya mahu** block semua request ke route yang user tidak ada permission,
**Supaya** keselamatan data terjamin walaupun user cuba bypass UI.

**Acceptance Criteria:**
- AC-3.1: Request ke `index/show` route blocked jika `view` permission unchecked
- AC-3.2: Request ke `create/store` route blocked jika `create` permission unchecked
- AC-3.3: Request ke `edit/update` route blocked jika `update` permission unchecked
- AC-3.4: Request ke `destroy` route blocked jika `delete` permission unchecked
- AC-3.5: Request ke `export` route blocked jika `export` permission unchecked
- AC-3.6: Blocked request return 403 Forbidden response
- AC-3.7: Blocked request redirect dengan error message untuk web requests

### US-4: Tab-Level Permission Control
**Sebagai** pengguna dengan role tertentu,
**Saya mahu** tabs dalam modul multi-tab hanya visible jika saya ada akses,
**Supaya** saya tidak nampak tab yang tidak boleh saya akses.

**Acceptance Criteria:**
- AC-4.1: Internal Inventory tabs (assets, movements, checkout, locations, brands, categories) controlled by respective `internal_inventory_*` permissions
- AC-4.2: Helpdesk tabs (tickets, templates, priorities, categories, statuses) controlled by respective `helpdesk_*` permissions
- AC-4.3: Settings Integrations tabs (email, payment, storage, weather, webhooks) controlled by `settings_integrations` permission
- AC-4.4: External Settings tabs (clients, vendors, locations, brands, categories) controlled by respective `external_settings_*` permissions

## Modules to Protect

### Internal Modules
| Module | Route Prefix | Permission Key | Actions |
|--------|--------------|----------------|---------|
| Credentials | `internal.credentials.*` | `internal_credentials` | view, create, update, delete |
| Download | `internal.download.*` | `internal_download` | view, create, delete |
| Employee | `internal.employee.*` | `internal_employee` | view, create, update, delete, export |
| Inventory > Assets | `internal.inventory.*` (tab=assets) | `internal_inventory_assets` | view, create, update, delete, export |
| Inventory > Movements | `internal.inventory.*` (tab=movements) | `internal_inventory_movements` | view, create, update, delete, export |
| Inventory > Checkout | `internal.inventory.*` (tab=checkout) | `internal_inventory_checkout` | view, create, update, delete, export |
| Inventory > Locations | `internal.inventory.*` (tab=locations) | `internal_inventory_locations` | view, create, update, delete, export |
| Inventory > Brands | `internal.inventory.*` (tab=brands) | `internal_inventory_brands` | view, create, update, delete, export |
| Inventory > Categories | `internal.inventory.*` (tab=categories) | `internal_inventory_categories` | view, create, update, delete, export |

### Settings Modules
| Module | Route Prefix | Permission Key | Actions |
|--------|--------------|----------------|---------|
| Configuration | `settings.configuration.*` | `settings_configuration` | view, update |
| Roles | `settings.roles.*` | `settings_roles` | view, create, update, delete, export |
| Users | `settings.users.*` | `settings_users` | view, create, update, delete, export |
| Integrations | `settings.integrations.*` | `settings_integrations` | view, create, update, delete |
| Activity Logs | `settings.activity-logs.*` | `settings_activity_logs` | view, export |

### External Modules (Reference)
| Module | Route Prefix | Permission Key | Actions |
|--------|--------------|----------------|---------|
| Projects | `external.projects.*` | `external_projects` | view, create, update, delete, export |
| Inventory | `external.inventory.*` | `external_inventory` | view, create, update, delete, export |
| Reports | `external.reports.*` | `external_reports` | view, create, delete, export |
| Attachments | `external.attachments.*` | `external_attachments` | view, create, update, delete, export |
| Settings > Client | `external.settings.clients.*` | `external_settings_client` | view, create, update, delete, export |
| Settings > Vendor | `external.settings.vendors.*` | `external_settings_vendor` | view, create, update, delete, export |
| Settings > Location | `external.settings.locations.*` | `external_settings_location` | view, create, update, delete, export |
| Settings > Brand | `external.settings.brands.*` | `external_settings_brand` | view, create, update, delete, export |
| Settings > Category | `external.settings.categories.*` | `external_settings_category` | view, create, update, delete, export |

### Other Modules
| Module | Route Prefix | Permission Key | Actions |
|--------|--------------|----------------|---------|
| Overview/Dashboard | `dashboard.*` | `overview` | view, export |
| Helpdesk > Tickets | `helpdesk.*` (tab=tickets) | `helpdesk_tickets` | view, create, update, delete, export, assign |
| Helpdesk > Templates | `helpdesk.*` (tab=templates) | `helpdesk_templates` | view, create, update, delete |
| Helpdesk > Priorities | `helpdesk.*` (tab=priorities) | `helpdesk_priorities` | view, create, update, delete |
| Helpdesk > Categories | `helpdesk.*` (tab=categories) | `helpdesk_categories` | view, create, update, delete |
| Helpdesk > Statuses | `helpdesk.*` (tab=statuses) | `helpdesk_statuses` | view, create, update, delete |

## Non-Functional Requirements

### NFR-1: Performance
- Permission check tidak boleh menambah lebih dari 5ms latency per request
- Permission data di-cache dalam session untuk mengelak repeated database queries

### NFR-2: Security
- Backend permission check WAJIB ada walaupun UI element tersembunyi
- Permission check berlaku untuk semua HTTP methods (GET, POST, PUT, DELETE)

### NFR-3: Maintainability
- Permission keys defined centrally dalam `config/permissions.php`
- Blade directive untuk permission check dalam views
- Middleware untuk permission check dalam routes

## Out of Scope
- Permission inheritance (child inherits parent permissions)
- Time-based permissions
- Resource-level permissions (e.g., can only edit own records)
- API token permissions
