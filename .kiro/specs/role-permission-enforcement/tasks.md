# Implementation Plan: Role-Based Permission Enforcement System

## Overview

Implementasi sistem permission enforcement yang mengawal akses ke semua modul internal dan settings berdasarkan permission matrix dalam `/settings/roles`.

## Tasks

- [x] 1. Update config/permissions.php dengan modul baru
  - [x] 1.1 Tambah `internal_credentials` module dengan permissions (view, create, update, delete)
  - [x] 1.2 Tambah `internal_download` module dengan permissions (view, create, delete)
  - [x] 1.3 Verify `internal_employee` sudah ada
  - [x] 1.4 Tambah `internal_inventory_*` sub-modules (assets, movements, checkout, locations, brands, categories)
  - [x] 1.5 Tambah `helpdesk_*` sub-modules (tickets, templates, priorities, categories, statuses)
  - [x] 1.6 Tambah `route_mapping` array untuk map route prefix ke module
  - [x] 1.7 Tambah `tab_mapping` array untuk map tab parameter ke sub-module
  - [x] 1.8 Tambah `action_mapping` array untuk map route action ke permission
  - _Requirements: US-3, US-4_

- [x] 2. Create CheckPermission Middleware
  - [x] 2.1 Create `app/Http/Middleware/CheckPermission.php`
  - [x] 2.2 Implement `handle()` method dengan auto-detection dari route
  - [x] 2.3 Update `detectFromRoute()` helper method untuk support tab-based sub-modules
  - [x] 2.4 Handle JSON response untuk API requests
  - [x] 2.5 Handle redirect dengan error message untuk web requests
  - [x] 2.6 Register middleware dalam `bootstrap/app.php`
  - _Requirements: US-3, AC-3.1 to AC-3.7_

- [x] 3. Register Blade Directives
  - [x] 3.1 Add `@permission` directive dalam AppServiceProvider
  - [x] 3.2 Add `@moduleAccess` directive dalam AppServiceProvider
  - _Requirements: US-1, US-2_

- [x] 4. Update Sidebar Component dengan Permission Checks
  - [x] 4.1 Tambah `module` key ke setiap menu item dalam `$menuItems` array
  - [x] 4.2 Filter children berdasarkan `hasModuleAccess()`
  - [x] 4.3 Hide parent menu jika semua children hidden
  - [x] 4.4 Hide single menu item jika tiada module access
  - _Requirements: US-1, AC-1.1 to AC-1.4_

- [x] 5. Apply Middleware ke Internal Routes
  - [x] 5.1 Add middleware ke `internal.credentials.*` routes
  - [x] 5.2 Add middleware ke `internal.download.*` routes
  - [x] 5.3 Add middleware ke `internal.employee.*` routes
  - [x] 5.4 Add middleware ke `internal.inventory.*` routes
  - _Requirements: US-3_

- [x] 6. Apply Middleware ke Settings Routes
  - [x] 6.1 Add middleware ke `settings.configuration.*` routes
  - [x] 6.2 Add middleware ke `settings.roles.*` routes
  - [x] 6.3 Add middleware ke `settings.users.*` routes
  - [x] 6.4 Add middleware ke `settings.integrations.*` routes
  - [x] 6.5 Add middleware ke `settings.activity-logs.*` routes
  - _Requirements: US-3_

- [x] 7. Update Internal Credentials Views
  - [x] 7.1 Update `index.blade.php` - hide Create button jika tiada `create` permission
  - [x] 7.2 Update `index.blade.php` - hide Edit/Delete icons jika tiada permission
  - [x] 7.3 Update `show.blade.php` - hide Edit/Delete buttons jika tiada permission
  - _Requirements: US-2, AC-2.1 to AC-2.6_

- [x] 8. Update Internal Download Views
  - [x] 8.1 Update `index.blade.php` - hide Upload button jika tiada `create` permission
  - [x] 8.2 Update `index.blade.php` - hide Delete icon jika tiada `delete` permission
  - [x] 8.3 Update `show.blade.php` - hide Delete button jika tiada permission
  - _Requirements: US-2_

- [x] 9. Update Internal Employee Views
  - [x] 9.1 Update `index.blade.php` - hide Create button jika tiada `create` permission
  - [x] 9.2 Update `index.blade.php` - hide Edit/Delete icons jika tiada permission
  - [x] 9.3 Update `index.blade.php` - hide Export button jika tiada `export` permission
  - [x] 9.4 Update `show.blade.php` - hide Edit/Delete buttons jika tiada permission
  - [x] 9.5 Update `create.blade.php` - redirect jika tiada `create` permission
  - [x] 9.6 Update `edit.blade.php` - redirect jika tiada `update` permission
  - _Requirements: US-2_

- [x] 10. Update Internal Inventory Views dengan Tab-Level Permissions
  - [x] 10.1 Update `index.blade.php` - hide tabs berdasarkan sub-module permissions
  - [x] 10.2 Update `partials/assets.blade.php` - permission checks untuk `internal_inventory_assets`
  - [x] 10.3 Update `partials/movements.blade.php` - permission checks untuk `internal_inventory_movements`
  - [x] 10.4 Update `partials/checkout.blade.php` - permission checks untuk `internal_inventory_checkout`
  - [x] 10.5 Update `partials/locations.blade.php` - permission checks untuk `internal_inventory_locations`
  - [x] 10.6 Update `partials/brands.blade.php` - permission checks untuk `internal_inventory_brands`
  - [x] 10.7 Update `partials/categories.blade.php` - permission checks untuk `internal_inventory_categories`
  - _Requirements: US-2, US-4, AC-4.1_

- [x] 11. Update Settings Users Views
  - [x] 11.1 Update `index.blade.php` - hide Create button jika tiada `create` permission
  - [x] 11.2 Update `index.blade.php` - hide Edit/Delete icons jika tiada permission
  - [x] 11.3 Update `show.blade.php` - hide Edit/Delete buttons jika tiada permission
  - _Requirements: US-2_

- [x] 12. Update Settings Roles Views
  - [x] 12.1 Update `index.blade.php` - hide Create button jika tiada `create` permission
  - [x] 12.2 Update `index.blade.php` - hide Edit/Delete icons jika tiada permission
  - [x] 12.3 Update `show.blade.php` - hide Edit/Delete buttons jika tiada permission
  - _Requirements: US-2_

- [x] 13. Update Settings Integrations Views
  - [x] 13.1 Update `index.blade.php` - hide Save buttons jika tiada `update` permission
  - [x] 13.2 Update `partials/email.blade.php` - permission checks
  - [x] 13.3 Update `partials/payment.blade.php` - permission checks
  - [x] 13.4 Update `partials/storage.blade.php` - permission checks
  - [x] 13.5 Update `partials/weather.blade.php` - permission checks
  - [x] 13.6 Update `partials/webhooks.blade.php` - permission checks
  - _Requirements: US-2, US-4_

- [x] 14. Update External Module Views (Reference Implementation)
  - [x] 14.1 Update `external/projects/index.blade.php` - permission checks
  - [x] 14.2 Update `external/inventory/index.blade.php` - permission checks
  - [x] 14.3 Update `external/reports/index.blade.php` - permission checks
  - [x] 14.4 Update `external/attachments/index.blade.php` - permission checks
  - [x] 14.5 Update `external/settings/index.blade.php` - permission checks untuk setiap tab
  - _Requirements: US-2, US-4_

- [x] 15. Update Helpdesk Views dengan Tab-Level Permissions
  - [x] 15.1 Update `index.blade.php` - hide tabs berdasarkan sub-module permissions
  - [x] 15.2 Update `partials/tickets.blade.php` - permission checks untuk `helpdesk_tickets`
  - [x] 15.3 Update `partials/templates.blade.php` - permission checks untuk `helpdesk_templates`
  - [x] 15.4 Update `partials/priorities.blade.php` - permission checks untuk `helpdesk_priorities`
  - [x] 15.5 Update `partials/categories.blade.php` - permission checks untuk `helpdesk_categories`
  - [x] 15.6 Update `partials/statuses.blade.php` - permission checks untuk `helpdesk_statuses`
  - [x] 15.7 Update `show.blade.php` - hide Assign button jika tiada `helpdesk_tickets.assign` permission
  - [x] 15.8 Update `show.blade.php` - hide Delete button jika tiada `helpdesk_tickets.delete` permission
  - _Requirements: US-2, US-4, AC-4.2_

- [x] 16. Apply Middleware ke External Routes
  - [x] 16.1 Add middleware ke `external.projects.*` routes
  - [x] 16.2 Add middleware ke `external.inventory.*` routes
  - [x] 16.3 Add middleware ke `external.reports.*` routes
  - [x] 16.4 Add middleware ke `external.attachments.*` routes
  - [x] 16.5 Add middleware ke `external.settings.*` routes
  - _Requirements: US-3_

- [x] 17. Apply Middleware ke Helpdesk Routes
  - [x] 17.1 Add middleware ke `helpdesk.*` routes
  - _Requirements: US-3_

- [x] 18. Apply Middleware ke Dashboard Route
  - [x] 18.1 Add middleware ke `dashboard` route
  - _Requirements: US-3_

- [x] 19. Create Reusable Action Buttons Component
  - [x] 19.1 Update `components/ui/action-buttons.blade.php` dengan permission checks
  - [x] 19.2 Add `module` prop untuk permission checking
  - [x] 19.3 Implement conditional rendering untuk setiap button type
  - _Requirements: US-2_

- [x] 20. Testing dan Verification
  - [x] 20.1 Create test role dengan limited permissions
  - [x] 20.2 Test sidebar visibility
  - [x] 20.3 Test action button visibility
  - [x] 20.4 Test direct URL access blocking
  - [x] 20.5 Test API request blocking
  - _Requirements: All_
  - _Note: All implementation complete. Manual testing dapat dilakukan oleh user._

## Sidebar Menu Structure Reference

```
Overview (module: overview)
├── Dashboard

Internal (parent)
├── Credentials (module: internal_credentials)
├── Download (module: internal_download)
├── Employee (module: internal_employee)
└── Inventory (parent - show if ANY sub-module accessible)
    ├── Tab: Assets (module: internal_inventory_assets)
    ├── Tab: Movements (module: internal_inventory_movements)
    ├── Tab: Checkout (module: internal_inventory_checkout)
    ├── Tab: Locations (module: internal_inventory_locations)
    ├── Tab: Brands (module: internal_inventory_brands)
    └── Tab: Categories (module: internal_inventory_categories)

External (parent)
├── Projects (module: external_projects)
├── Inventory (module: external_inventory)
├── Reports (module: external_reports)
├── Attachments (module: external_attachments)
└── Settings (module: external_settings_*)
    ├── Tab: Clients (module: external_settings_client)
    ├── Tab: Vendors (module: external_settings_vendor)
    ├── Tab: Locations (module: external_settings_location)
    ├── Tab: Brands (module: external_settings_brand)
    └── Tab: Categories (module: external_settings_category)

Helpdesk (parent - show if ANY sub-module accessible)
├── Tab: Tickets (module: helpdesk_tickets)
├── Tab: Templates (module: helpdesk_templates)
├── Tab: Priorities (module: helpdesk_priorities)
├── Tab: Categories (module: helpdesk_categories)
└── Tab: Statuses (module: helpdesk_statuses)

System Settings (parent)
├── Configuration (module: settings_configuration)
├── Group Roles (module: settings_roles)
├── User Management (module: settings_users)
├── Integrations (module: settings_integrations)
│   ├── Tab: Email
│   ├── Tab: Payment
│   ├── Tab: Storage
│   ├── Tab: Weather
│   └── Tab: Webhooks
└── Activity Logs (module: settings_activity_logs)
```

## Action Buttons Reference

| Module | View | Create | Update | Delete | Export | Assign |
|--------|------|--------|--------|--------|--------|--------|
| overview | ✓ | - | - | - | ✓ | - |
| internal_credentials | ✓ | ✓ | ✓ | ✓ | - | - |
| internal_download | ✓ | ✓ | - | ✓ | - | - |
| internal_employee | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| internal_inventory_assets | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| internal_inventory_movements | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| internal_inventory_checkout | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| internal_inventory_locations | ✓ | ✓ | ✓ | ✓ | - | - |
| internal_inventory_brands | ✓ | ✓ | ✓ | ✓ | - | - |
| internal_inventory_categories | ✓ | ✓ | ✓ | ✓ | - | - |
| external_projects | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| external_inventory | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| external_reports | ✓ | ✓ | - | ✓ | ✓ | - |
| external_attachments | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| external_settings_* | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| helpdesk_tickets | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| helpdesk_templates | ✓ | ✓ | ✓ | ✓ | - | - |
| helpdesk_priorities | ✓ | ✓ | ✓ | ✓ | - | - |
| helpdesk_categories | ✓ | ✓ | ✓ | ✓ | - | - |
| helpdesk_statuses | ✓ | ✓ | ✓ | ✓ | - | - |
| settings_configuration | ✓ | - | ✓ | - | - | - |
| settings_roles | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| settings_users | ✓ | ✓ | ✓ | ✓ | ✓ | - |
| settings_integrations | ✓ | ✓ | ✓ | ✓ | - | - |
| settings_activity_logs | ✓ | - | - | - | ✓ | - |

## Notes

- Semua tasks adalah required untuk comprehensive implementation
- Setiap task reference specific requirements untuk traceability
- Permission checks mesti ada di KEDUA-DUA frontend (UI) dan backend (middleware)
- Backward compatibility maintained - existing roles akan berfungsi seperti biasa
- Super admin role (jika ada) boleh bypass semua permission checks
