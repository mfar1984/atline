# Design Document: Recycle Bin

## Overview

Recycle Bin adalah feature soft delete untuk ATLINE System yang membolehkan semua deleted items dipindahkan ke "tong sampah" sebelum di-hard delete secara kekal. Feature ini diakses melalui tab baru dalam System Settings > Integrations, diletakkan sebelum Email tab.

Sistem ini menggunakan Laravel's SoftDeletes trait dengan tambahan `deleted_by` field untuk audit trail. Semua entities yang di-delete akan masuk ke Recycle Bin dan boleh di-restore atau di-permanent delete.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    Integrations Page                             │
├─────────────────────────────────────────────────────────────────┤
│  [Recycle Bin] [Email] [Payment] [Storage] [Weather] [Webhooks] │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Header: Recycle Bin                                      │   │
│  │ Stats: Total Items: XX                                   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Filters: [Search] [Type ▼] [Date From] [Date To]        │   │
│  │          [SEARCH] [DELETE ▼ 30/60/90 Days]              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Table                                                    │   │
│  │ Type | Name/Title | Original ID | Deleted By | Deleted  │   │
│  │      |            |             |            | At       │   │
│  │ ─────┼────────────┼─────────────┼────────────┼──────────│   │
│  │ 🏢   | Project A  | #123        | John Doe   | 01/01/26 │   │
│  │ 📦   | Asset XYZ  | #456        | Jane Doe   | 02/01/26 │   │
│  │ 👤   | Employee B | #789        | Admin      | 03/01/26 │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [Pagination]                                                    │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Migration

Add soft delete columns to all recyclable models:

```php
// Migration: add_soft_deletes_to_recyclable_tables.php
Schema::table('projects', function (Blueprint $table) {
    $table->softDeletes();
    $table->unsignedBigInteger('deleted_by')->nullable();
    $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
});

// Repeat for: assets, clients, vendors, categories, brands, locations,
// employees, internal_assets, credentials, downloads, tickets
```

### 2. RecycleBin Trait

```php
// app/Traits/RecycleBin.php
trait RecycleBin
{
    use SoftDeletes;

    public static function bootRecycleBin()
    {
        static::deleting(function ($model) {
            if (!$model->isForceDeleting()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getRecycleBinName(): string
    {
        return $this->name ?? $this->title ?? $this->subject ?? "#{$this->id}";
    }

    public function getRecycleBinType(): string
    {
        return class_basename($this);
    }
}
```

### 3. RecycleBinService

```php
// app/Services/RecycleBinService.php
class RecycleBinService
{
    protected array $recyclableModels = [
        'project' => Project::class,
        'asset' => Asset::class,
        'client' => Client::class,
        'vendor' => Vendor::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'location' => Location::class,
        'employee' => Employee::class,
        'internal_asset' => InternalAsset::class,
        'credential' => Credential::class,
        'download' => Download::class,
        'ticket' => Ticket::class,
    ];

    public function getAllTrashedItems(array $filters = []): LengthAwarePaginator
    {
        // Collect all trashed items from all models
        // Apply filters: type, search, date_from, date_to
        // Return paginated collection
    }

    public function restore(string $type, int $id): bool
    {
        // Find and restore the item
        // Log activity
    }

    public function forceDelete(string $type, int $id): bool
    {
        // Find and permanently delete
        // Delete related attachments
        // Log activity
    }

    public function bulkDeleteByAge(int $days): int
    {
        // Delete all items older than X days
        // Return count of deleted items
        // Log activity
    }

    public function getStatistics(): array
    {
        // Return total count and breakdown by type
    }
}
```

### 4. Controller

```php
// app/Http/Controllers/Settings/IntegrationController.php
// Add recycle bin methods

public function recycleBin(Request $request)
{
    $filters = $request->only(['search', 'type', 'date_from', 'date_to']);
    $items = $this->recycleBinService->getAllTrashedItems($filters);
    $stats = $this->recycleBinService->getStatistics();
    
    return view with $items, $stats, $activeTab = 'recycle-bin';
}

public function restoreItem(Request $request, string $type, int $id)
{
    // Check permission: settings_integrations_recycle_bin.restore
    // Restore item
    // Return success/error
}

public function forceDeleteItem(Request $request, string $type, int $id)
{
    // Check permission: settings_integrations_recycle_bin.delete
    // Force delete item
    // Return success/error
}

public function bulkDelete(Request $request)
{
    // Check permission: settings_integrations_recycle_bin.delete
    // Validate days (30, 60, 90)
    // Bulk delete
    // Return count
}
```

### 5. View Structure

```
resources/views/settings/integrations/
├── index.blade.php (update tabs)
└── partials/
    └── recycle-bin.blade.php (new)
```

## Data Models

### RecycleBinItem (Virtual/Collection Item)

```php
// Used for unified display in table
class RecycleBinItem
{
    public string $type;           // 'Project', 'Asset', etc.
    public string $typeIcon;       // Material icon name
    public int $originalId;        // Original record ID
    public string $name;           // Display name
    public ?User $deletedBy;       // User who deleted
    public Carbon $deletedAt;      // Deletion timestamp
    public Model $model;           // Original model instance
}
```

### Entity Type Icons Mapping

| Type | Icon | Color |
|------|------|-------|
| Project | folder | blue |
| Asset | inventory_2 | green |
| Client | business | purple |
| Vendor | local_shipping | orange |
| Category | category | gray |
| Brand | branding_watermark | indigo |
| Location | location_on | red |
| Employee | person | teal |
| Internal Asset | devices | cyan |
| Credential | key | amber |
| Download | download | lime |
| Ticket | confirmation_number | pink |

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Soft Delete Preserves Data

*For any* recyclable entity that is deleted, the record SHALL still exist in the database with `deleted_at` timestamp set and `deleted_by` user ID recorded.

**Validates: Requirements 3.1, 3.2, 3.3**

### Property 2: Soft Deleted Items Excluded from Normal Queries

*For any* soft-deleted entity, it SHALL NOT appear in normal model queries (without `withTrashed()`).

**Validates: Requirements 3.4**

### Property 3: Restore Removes Soft Delete Markers

*For any* restored item from Recycle Bin, the `deleted_at` SHALL be null and the item SHALL be queryable through normal queries.

**Validates: Requirements 4.1, 4.2**

### Property 4: Hard Delete Removes Record Completely

*For any* item that is permanently deleted from Recycle Bin, the record SHALL NOT exist in the database (even with `withTrashed()`).

**Validates: Requirements 5.3, 5.4**

### Property 5: Bulk Delete by Age

*For any* bulk delete operation with X days, ALL recycled items with `deleted_at` older than X days SHALL be permanently deleted, and items newer than X days SHALL remain.

**Validates: Requirements 6.4**

### Property 6: Permission Enforcement

*For any* user without the required permission, attempting to access Recycle Bin view/restore/delete SHALL be denied.

**Validates: Requirements 7.3, 7.4, 7.5**

### Property 7: Activity Logging

*For any* recycle bin operation (soft delete, restore, hard delete, bulk delete), an activity log entry SHALL be created with correct action type, entity details, and user information.

**Validates: Requirements 9.1, 9.2, 9.3, 9.4**

## Error Handling

| Scenario | Error Response |
|----------|----------------|
| Item not found in trash | 404 - "Item not found in Recycle Bin" |
| No permission to view | 403 - Redirect with "Access denied" |
| No permission to restore | 403 - "You don't have permission to restore items" |
| No permission to delete | 403 - "You don't have permission to permanently delete items" |
| Restore conflict (duplicate) | 422 - "Cannot restore: item with same identifier already exists" |
| Invalid bulk delete days | 422 - "Invalid period selected" |

## Testing Strategy

### Unit Tests

1. **RecycleBinService Tests**
   - Test `getAllTrashedItems()` returns correct items
   - Test `restore()` removes soft delete markers
   - Test `forceDelete()` removes record completely
   - Test `bulkDeleteByAge()` deletes correct items
   - Test `getStatistics()` returns accurate counts

2. **RecycleBin Trait Tests**
   - Test `deleted_by` is set on soft delete
   - Test `getRecycleBinName()` returns correct name
   - Test `getRecycleBinType()` returns correct type

### Property-Based Tests

1. **Soft Delete Property Test**
   - Generate random entities
   - Delete them
   - Verify `deleted_at` and `deleted_by` are set
   - Verify record still exists with `withTrashed()`

2. **Exclusion Property Test**
   - Generate random entities, soft delete some
   - Query without `withTrashed()`
   - Verify soft-deleted items not in results

3. **Restore Property Test**
   - Generate random soft-deleted entities
   - Restore them
   - Verify `deleted_at` is null
   - Verify items appear in normal queries

4. **Bulk Delete Property Test**
   - Generate items with various `deleted_at` dates
   - Run bulk delete with specific days
   - Verify only items older than threshold are deleted

### Integration Tests

1. Test full flow: Create → Delete → View in Recycle Bin → Restore
2. Test full flow: Create → Delete → View in Recycle Bin → Permanent Delete
3. Test bulk delete with confirmation modal
4. Test permission enforcement for all actions
