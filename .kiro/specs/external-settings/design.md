# Design Document: External Settings

## Overview

External Settings adalah halaman konfigurasi master data untuk modul External yang menggunakan TAB navigation pattern seperti dalam POS Item Inventory. Halaman ini membolehkan pengguna mengurus Client, Vendor, Location, Brand, dan Category dalam satu halaman dengan navigasi tab yang mudah.

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    External Settings Page                        │
├─────────────────────────────────────────────────────────────────┤
│  [Client] [Vendor] [Location] [Brand] [Category]  ← TAB Nav     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    TAB Content Area                      │   │
│  │  - Table with data                                       │   │
│  │  - Add/Edit/Delete actions                               │   │
│  │  - Inline forms or modals                                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Component Structure

```
resources/views/external/settings/
├── index.blade.php              # Main page with TAB navigation
└── partials/
    ├── clients.blade.php        # Client TAB content
    ├── vendors.blade.php        # Vendor TAB content
    ├── locations.blade.php      # Location TAB content
    ├── brands.blade.php         # Brand TAB content
    └── categories.blade.php     # Category TAB content
```

## Components and Interfaces

### Controller: ExternalSettingsController

```php
class ExternalSettingsController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'clients');
        
        $data = match($activeTab) {
            'clients' => ['clients' => Client::orderBy('name')->get()],
            'vendors' => ['vendors' => Vendor::orderBy('name')->get()],
            'locations' => ['locations' => Location::with('parent')->orderBy('name')->get()],
            'brands' => ['brands' => Brand::orderBy('name')->get()],
            'categories' => ['categories' => Category::withCount('assets')->orderBy('name')->get()],
            default => ['clients' => Client::orderBy('name')->get()],
        };
        
        return view('external.settings.index', array_merge($data, ['activeTab' => $activeTab]));
    }
    
    // CRUD methods for each entity
    public function storeClient(Request $request) { ... }
    public function updateClient(Request $request, Client $client) { ... }
    public function destroyClient(Client $client) { ... }
    
    public function storeVendor(Request $request) { ... }
    public function updateVendor(Request $request, Vendor $vendor) { ... }
    public function destroyVendor(Vendor $vendor) { ... }
    
    public function storeLocation(Request $request) { ... }
    public function updateLocation(Request $request, Location $location) { ... }
    public function destroyLocation(Location $location) { ... }
    
    public function storeBrand(Request $request) { ... }
    public function updateBrand(Request $request, Brand $brand) { ... }
    public function destroyBrand(Brand $brand) { ... }
    
    public function storeCategory(Request $request) { ... }
    public function updateCategory(Request $request, Category $category) { ... }
    public function destroyCategory(Category $category) { ... }
    
    public function toggleStatus(Request $request, string $type, int $id) { ... }
}
```

### Routes

```php
Route::prefix('external')->name('external.')->group(function () {
    // Settings
    Route::get('settings', [ExternalSettingsController::class, 'index'])->name('settings.index');
    
    // Clients
    Route::post('settings/clients', [ExternalSettingsController::class, 'storeClient'])->name('settings.clients.store');
    Route::put('settings/clients/{client}', [ExternalSettingsController::class, 'updateClient'])->name('settings.clients.update');
    Route::delete('settings/clients/{client}', [ExternalSettingsController::class, 'destroyClient'])->name('settings.clients.destroy');
    
    // Vendors
    Route::post('settings/vendors', [ExternalSettingsController::class, 'storeVendor'])->name('settings.vendors.store');
    Route::put('settings/vendors/{vendor}', [ExternalSettingsController::class, 'updateVendor'])->name('settings.vendors.update');
    Route::delete('settings/vendors/{vendor}', [ExternalSettingsController::class, 'destroyVendor'])->name('settings.vendors.destroy');
    
    // Locations
    Route::post('settings/locations', [ExternalSettingsController::class, 'storeLocation'])->name('settings.locations.store');
    Route::put('settings/locations/{location}', [ExternalSettingsController::class, 'updateLocation'])->name('settings.locations.update');
    Route::delete('settings/locations/{location}', [ExternalSettingsController::class, 'destroyLocation'])->name('settings.locations.destroy');
    
    // Brands
    Route::post('settings/brands', [ExternalSettingsController::class, 'storeBrand'])->name('settings.brands.store');
    Route::put('settings/brands/{brand}', [ExternalSettingsController::class, 'updateBrand'])->name('settings.brands.update');
    Route::delete('settings/brands/{brand}', [ExternalSettingsController::class, 'destroyBrand'])->name('settings.brands.destroy');
    
    // Categories
    Route::post('settings/categories', [ExternalSettingsController::class, 'storeCategory'])->name('settings.categories.store');
    Route::put('settings/categories/{category}', [ExternalSettingsController::class, 'updateCategory'])->name('settings.categories.update');
    Route::delete('settings/categories/{category}', [ExternalSettingsController::class, 'destroyCategory'])->name('settings.categories.destroy');
    
    // Toggle Status
    Route::post('settings/{type}/{id}/toggle-status', [ExternalSettingsController::class, 'toggleStatus'])->name('settings.toggle-status');
});
```

## Data Models

### Client Model (New)

```php
class Client extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

### Database Migrations

#### Create Clients Table
```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('contact_person')->nullable();
    $table->string('phone')->nullable();
    $table->string('email')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Modify Projects Table
```php
// Add client_id, keep client_name temporarily for migration
Schema::table('projects', function (Blueprint $table) {
    $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
});

// Migrate data: create clients from unique client_names, update projects with client_id
// Then optionally drop client_name column
```

#### Add Vendor to Assets Table
```php
Schema::table('assets', function (Blueprint $table) {
    $table->foreignId('vendor_id')->nullable()->after('location_id')->constrained()->nullOnDelete();
});
```

### Updated Project Model

```php
class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'client_id',        // Changed from client_name
        'project_value',
        'start_date',
        'end_date',
        'status',
        'purchase_date',
        'po_number',
        'vendor_id',
        'warranty_period',
        'warranty_expiry',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    // ... other relationships
}
```

### Updated Asset Model

```php
class Asset extends Model
{
    protected $fillable = [
        // ... existing fields
        'vendor_id',  // Add this
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    
    // ... other relationships
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: TAB State Persistence
*For any* tab selection, the URL parameter should reflect the active tab, and visiting that URL should display the correct tab content.
**Validates: Requirements 1.2, 1.4**

### Property 2: Deletion Prevention for Linked Records
*For any* master data entity (Client, Vendor, Location, Brand, Category) that has linked records (projects, assets, child locations), attempting to delete should fail and return an error message.
**Validates: Requirements 2.5, 3.5, 4.6, 5.5, 6.6**

### Property 3: Location Hierarchy Integrity
*For any* location with a parent, the parent must exist and the hierarchy depth should not exceed 4 levels (Site > Building > Floor > Room).
**Validates: Requirements 4.2**

### Property 4: Status Toggle Affects Dropdown Visibility
*For any* master data entity, toggling is_active to false should exclude it from dropdown selections in Projects/Inventory forms, and toggling to true should include it.
**Validates: Requirements 7.1, 7.3**

### Property 5: Client Migration Data Integrity
*For any* existing project with client_name, after migration, the project should have a valid client_id pointing to a Client record with matching name.
**Validates: Requirements 8.2**

### Property 6: Category Dynamic Fields Configuration
*For any* category with fields_config, the configuration should be valid JSON array where each field has name, type, and required properties.
**Validates: Requirements 6.3**

## Error Handling

1. **Deletion Errors**: When attempting to delete a record with linked data, return JSON response with error message and HTTP 422 status.

2. **Validation Errors**: Use Laravel's built-in validation with custom error messages in Bahasa Malaysia/English.

3. **Database Errors**: Wrap operations in try-catch and return user-friendly error messages.

4. **AJAX Errors**: All CRUD operations use AJAX, handle errors gracefully with toast notifications.

## Testing Strategy

### Unit Tests
- Test model relationships (Client->projects, Vendor->assets, etc.)
- Test canBeDeleted() methods for each model
- Test scopeActive() queries

### Property-Based Tests
- Test deletion prevention with randomly generated linked records
- Test status toggle affects dropdown filtering
- Test location hierarchy constraints

### Integration Tests
- Test full CRUD flow for each entity type
- Test tab navigation and URL state
- Test form submissions and validations

### Test Configuration
- Minimum 100 iterations per property test
- Use Laravel's RefreshDatabase trait
- Tag format: **Feature: external-settings, Property {number}: {property_text}**
