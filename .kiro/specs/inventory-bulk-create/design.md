# Design Document: Inventory Bulk Create

## Overview

Redesign inventory create page untuk membolehkan pengguna menambah multiple assets sekaligus. Form dibahagikan kepada dua bahagian utama:
1. Common Information - maklumat yang dikongsi oleh semua assets
2. Asset Items Table - table untuk menambah multiple asset items

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Create Asset Page                         │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────┐   │
│  │           Common Information Section                 │   │
│  │  ┌─────────────────┐  ┌─────────────────┐          │   │
│  │  │ Project *       │  │ Category *      │          │   │
│  │  └─────────────────┘  └─────────────────┘          │   │
│  │  ┌─────────────────┐  ┌─────────────────┐          │   │
│  │  │ Status          │  │ Location        │          │   │
│  │  └─────────────────┘  └─────────────────┘          │   │
│  │  ┌─────────────────┐  ┌─────────────────┐          │   │
│  │  │ Vendor          │  │ Assigned To     │          │   │
│  │  └─────────────────┘  └─────────────────┘          │   │
│  │  ┌─────────────────┐  ┌─────────────────┐          │   │
│  │  │ Department      │  │ Notes           │          │   │
│  │  └─────────────────┘  └─────────────────┘          │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │        Dynamic Technical Specifications              │   │
│  │        (appears when Category selected)              │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              Asset Items Section                     │   │
│  │  ┌─────────────────────────────────────────────┐    │   │
│  │  │ Asset Tag  │ Brand │ Model │ Serial │ Action│    │   │
│  │  ├─────────────────────────────────────────────┤    │   │
│  │  │ [____][AUTO]│ [▼]  │ [___] │ [____] │  🗑   │    │   │
│  │  │ [____][AUTO]│ [▼]  │ [___] │ [____] │  🗑   │    │   │
│  │  └─────────────────────────────────────────────┘    │   │
│  │                              [+ ADD ITEM]            │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              File Attachments Section                │   │
│  │  (same as current implementation)                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. AssetController Updates

```php
// app/Http/Controllers/External/AssetController.php

public function store(Request $request)
{
    // Validate common fields
    $validated = $request->validate([
        'project_id' => 'required|exists:projects,id',
        'category_id' => 'required|exists:categories,id',
        'status' => 'required|in:active,spare,damaged,maintenance,disposed',
        'location_id' => 'nullable|exists:locations,id',
        'vendor_id' => 'nullable|exists:vendors,id',
        'assigned_to' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
        'specs' => 'nullable|array',
        // Asset items validation
        'items' => 'required|array|min:1',
        'items.*.asset_tag' => 'required|string|max:50|unique:assets,asset_tag',
        'items.*.brand_id' => 'nullable|exists:brands,id',
        'items.*.model' => 'nullable|string|max:255',
        'items.*.serial_number' => 'nullable|string|max:255',
    ]);

    $createdAssets = [];
    
    foreach ($validated['items'] as $item) {
        $asset = Asset::create([
            'project_id' => $validated['project_id'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'location_id' => $validated['location_id'],
            'vendor_id' => $validated['vendor_id'],
            'assigned_to' => $validated['assigned_to'],
            'department' => $validated['department'],
            'notes' => $validated['notes'],
            'specs' => $validated['specs'] ?? null,
            'asset_tag' => $item['asset_tag'],
            'brand_id' => $item['brand_id'],
            'model' => $item['model'],
            'serial_number' => $item['serial_number'],
        ]);
        
        $createdAssets[] = $asset;
    }

    // Handle file attachments - attach to all created assets
    if ($request->hasFile('attachment_files')) {
        foreach ($createdAssets as $asset) {
            foreach ($request->file('attachment_files') as $index => $file) {
                $displayName = $request->attachment_names[$index] ?? $file->getClientOriginalName();
                $this->attachmentService->store($file, $asset, $displayName);
            }
        }
    }

    $count = count($createdAssets);
    return redirect()->route('external.inventory.index')
        ->with('success', "{$count} asset(s) created successfully.");
}
```

### 2. View Structure (create.blade.php)

```blade
<!-- Common Information Section -->
<div class="border border-gray-200 rounded">
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
        <h3>Common Information</h3>
    </div>
    <div class="p-4">
        <!-- Project, Category, Status, Location, Vendor, Assigned To, Department, Notes -->
    </div>
</div>

<!-- Dynamic Technical Specifications -->
<div x-show="dynamicFields.length > 0">
    <!-- Category-specific fields -->
</div>

<!-- Asset Items Section -->
<div class="border border-gray-200 rounded" x-data="assetItems()">
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between">
        <h3>Asset Items</h3>
        <button @click="addItem()">ADD ITEM</button>
    </div>
    <div class="p-4">
        <table>
            <thead>
                <tr>
                    <th>Asset Tag/ID</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Serial Number</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items">
                    <tr>
                        <td>
                            <input :name="'items['+index+'][asset_tag]'" x-model="item.asset_tag">
                            <button @click="generateAssetTag(index)">AUTO</button>
                        </td>
                        <td>
                            <select :name="'items['+index+'][brand_id]'" x-model="item.brand_id">
                                <!-- Brand options -->
                            </select>
                        </td>
                        <td>
                            <input :name="'items['+index+'][model]'" x-model="item.model">
                        </td>
                        <td>
                            <input :name="'items['+index+'][serial_number]'" x-model="item.serial_number">
                        </td>
                        <td>
                            <button @click="removeItem(index)">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<!-- File Attachments Section -->
<div class="border border-gray-200 rounded">
    <!-- Same as current implementation -->
</div>
```

### 3. Alpine.js Component

```javascript
function assetItems() {
    return {
        items: [{ asset_tag: '', brand_id: '', model: '', serial_number: '' }],
        categoryId: '',
        
        addItem() {
            this.items.push({ 
                asset_tag: '', 
                brand_id: '', 
                model: '', 
                serial_number: '' 
            });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        
        async generateAssetTag(index) {
            if (!this.categoryId) return;
            
            const response = await fetch(`/external/inventory/category/${this.categoryId}/generate-id`);
            const data = await response.json();
            this.items[index].asset_tag = data.asset_tag;
        }
    }
}
```

## Data Models

No changes to existing Asset model. The bulk create uses the same Asset model but creates multiple records.

### Request Data Structure

```json
{
    "project_id": 1,
    "category_id": 2,
    "status": "active",
    "location_id": 3,
    "vendor_id": 4,
    "assigned_to": "John Doe",
    "department": "IT",
    "notes": "Bulk purchase",
    "specs": {
        "ram": "16GB",
        "storage": "512GB SSD"
    },
    "items": [
        {
            "asset_tag": "PC-2026-0001",
            "brand_id": 1,
            "model": "ThinkPad X1",
            "serial_number": "SN001"
        },
        {
            "asset_tag": "PC-2026-0002",
            "brand_id": 1,
            "model": "ThinkPad X1",
            "serial_number": "SN002"
        }
    ],
    "attachment_files": [...],
    "attachment_names": [...]
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Asset Count Matches Items Count

*For any* valid form submission with N items in the items array, the system SHALL create exactly N assets in the database.

**Validates: Requirements 4.2**

### Property 2: Common Information Applied to All Assets

*For any* bulk create submission, all created assets SHALL have identical values for: project_id, category_id, status, location_id, vendor_id, assigned_to, department, notes, and specs.

**Validates: Requirements 1.3, 3.1**

### Property 3: Unique Asset Tags

*For any* set of asset items submitted, all asset_tag values SHALL be unique both within the submission and against existing assets in the database.

**Validates: Requirements 3.3**

### Property 4: Minimum One Item Required

*For any* form submission, IF the items array is empty or not provided, THEN the system SHALL reject the submission with a validation error.

**Validates: Requirements 4.3**

## Error Handling

| Error Condition | Response |
|----------------|----------|
| No items added | Show validation error: "At least one asset item is required" |
| Duplicate asset tag | Show validation error on specific row |
| Invalid category | Show validation error |
| Invalid project | Show validation error |
| File upload failure | Continue with asset creation, show warning |

## Testing Strategy

### Unit Tests
- Test validation rules for bulk create
- Test asset creation with multiple items
- Test attachment association with multiple assets

### Property-Based Tests
- Property 1: Asset count matches items count
- Property 2: Common information consistency
- Property 3: Asset tag uniqueness
- Property 4: Minimum item validation
