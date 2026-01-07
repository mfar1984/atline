# Design: Role-Based Permission Enforcement System

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           Request Flow                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  User Request                                                            │
│       │                                                                  │
│       ▼                                                                  │
│  ┌─────────────────┐                                                    │
│  │  Auth Middleware │                                                    │
│  └────────┬────────┘                                                    │
│           │                                                              │
│           ▼                                                              │
│  ┌─────────────────────────┐                                            │
│  │  CheckPermission        │  ◄── New Middleware                        │
│  │  Middleware             │      - Check module.action permission       │
│  │                         │      - Return 403 if denied                 │
│  └────────┬────────────────┘                                            │
│           │                                                              │
│           ▼                                                              │
│  ┌─────────────────┐                                                    │
│  │   Controller    │                                                    │
│  └────────┬────────┘                                                    │
│           │                                                              │
│           ▼                                                              │
│  ┌─────────────────────────┐                                            │
│  │   Blade View            │  ◄── @can directive checks                 │
│  │   - Sidebar             │      - Hide/show menu items                │
│  │   - Action buttons      │      - Hide/show buttons                   │
│  │   - Table actions       │      - Hide/show icons                     │
│  └─────────────────────────┘                                            │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

## Component Design

### 1. Permission Configuration (`config/permissions.php`)

```php
return [
    'labels' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'export' => 'Export',
        'assign' => 'Assign',
    ],
    
    'modules' => [
        // Internal - NEW
        'internal_credentials' => 'Internal > Credentials',
        'internal_download' => 'Internal > Download',
        'internal_employee' => 'Internal > Employee',
        'internal_inventory_assets' => 'Internal > Inventory > Assets',
        'internal_inventory_movements' => 'Internal > Inventory > Movements',
        'internal_inventory_checkout' => 'Internal > Inventory > Checkout',
        'internal_inventory_locations' => 'Internal > Inventory > Locations',
        'internal_inventory_brands' => 'Internal > Inventory > Brands',
        'internal_inventory_categories' => 'Internal > Inventory > Categories',
        
        // Settings - existing + verify
        'settings_configuration' => 'System Settings > Configuration',
        'settings_roles' => 'System Settings > Group Roles',
        'settings_users' => 'System Settings > User Management',
        'settings_integrations' => 'System Settings > Integrations',
        'settings_activity_logs' => 'System Settings > Activity Logs',
        
        // External - existing
        // ... (already defined)
    ],
    
    'matrix' => [
        'internal_credentials' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_download' => [
            'view' => true,
            'create' => true,
            'update' => false,
            'delete' => true,
            'export' => false,
        ],
        'internal_inventory_assets' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => true,
        ],
        'internal_inventory_movements' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => true,
        ],
        'internal_inventory_checkout' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => true,
        ],
        'internal_inventory_locations' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_inventory_brands' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'internal_inventory_categories' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_tickets' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => true,
            'assign' => true,
        ],
        'helpdesk_templates' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_priorities' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_categories' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        'helpdesk_statuses' => [
            'view' => true,
            'create' => true,
            'update' => true,
            'delete' => true,
            'export' => false,
        ],
        // ... etc
    ],
    
    // Route to module mapping
    'route_mapping' => [
        'internal.credentials' => 'internal_credentials',
        'internal.download' => 'internal_download',
        'internal.employee' => 'internal_employee',
        'internal.inventory' => 'internal_inventory', // Will be further mapped by tab parameter
        'settings.configuration' => 'settings_configuration',
        'settings.roles' => 'settings_roles',
        'settings.users' => 'settings_users',
        'settings.integrations' => 'settings_integrations',
        'settings.activity-logs' => 'settings_activity_logs',
        'helpdesk' => 'helpdesk', // Will be further mapped by tab parameter
        // ... etc
    ],
    
    // Tab to sub-module mapping for multi-tab modules
    'tab_mapping' => [
        'internal.inventory' => [
            'assets' => 'internal_inventory_assets',
            'movements' => 'internal_inventory_movements',
            'checkout' => 'internal_inventory_checkout',
            'locations' => 'internal_inventory_locations',
            'brands' => 'internal_inventory_brands',
            'categories' => 'internal_inventory_categories',
        ],
        'helpdesk' => [
            'tickets' => 'helpdesk_tickets',
            'templates' => 'helpdesk_templates',
            'priorities' => 'helpdesk_priorities',
            'categories' => 'helpdesk_categories',
            'statuses' => 'helpdesk_statuses',
        ],
    ],
    
    // Action to route method mapping
    'action_mapping' => [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'destroy' => 'delete',
        'export' => 'export',
        'assign' => 'assign',
    ],
];
```

### 2. CheckPermission Middleware

```php
namespace App\Http\Middleware;

class CheckPermission
{
    public function handle($request, Closure $next, $module = null, $action = null)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Auto-detect module and action from route if not provided
        if (!$module || !$action) {
            [$module, $action] = $this->detectFromRoute($request);
        }
        
        if (!$module || !$action) {
            return $next($request);
        }
        
        $permission = "{$module}.{$action}";
        
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }
        
        return $next($request);
    }
    
    protected function detectFromRoute($request)
    {
        $routeName = $request->route()->getName();
        $routeMapping = config('permissions.route_mapping');
        $actionMapping = config('permissions.action_mapping');
        $tabMapping = config('permissions.tab_mapping');
        
        foreach ($routeMapping as $prefix => $module) {
            if (str_starts_with($routeName, $prefix)) {
                // Check if this module has tab-based sub-modules
                if (isset($tabMapping[$prefix])) {
                    $tab = $request->query('tab', array_key_first($tabMapping[$prefix]));
                    $module = $tabMapping[$prefix][$tab] ?? $module;
                }
                
                $routeAction = str_replace($prefix . '.', '', $routeName);
                $action = $actionMapping[$routeAction] ?? null;
                return [$module, $action];
            }
        }
        
        return [null, null];
    }
}
```

### 3. Blade Directives

```php
// AppServiceProvider boot()
Blade::if('permission', function ($permission) {
    return auth()->check() && auth()->user()->hasPermission($permission);
});

Blade::if('moduleAccess', function ($module) {
    return auth()->check() && auth()->user()->hasModuleAccess($module);
});
```

### 4. Sidebar Component Update

```php
// sidebar.blade.php
@php
$menuItems = [
    [
        'label' => 'Overview',
        'icon' => 'dashboard',
        'route' => 'dashboard',
        'module' => 'overview',
    ],
    [
        'label' => 'Internal',
        'icon' => 'home_storage',
        'children' => [
            ['label' => 'Credentials', 'route' => 'internal.credentials.index', 'module' => 'internal_credentials'],
            ['label' => 'Download', 'route' => 'internal.download.index', 'module' => 'internal_download'],
            ['label' => 'Employee', 'route' => 'internal.employee.index', 'module' => 'internal_employee'],
            ['label' => 'Inventory', 'route' => 'internal.inventory.index', 'module' => 'internal_inventory'],
        ]
    ],
    // ... etc
];
@endphp

@foreach($menuItems as $item)
    @if(isset($item['children']))
        @php
            $visibleChildren = collect($item['children'])->filter(function($child) {
                return auth()->user()->hasModuleAccess($child['module']);
            });
        @endphp
        @if($visibleChildren->isNotEmpty())
            <!-- Render parent with visible children -->
        @endif
    @else
        @if(auth()->user()->hasModuleAccess($item['module']))
            <!-- Render single menu item -->
        @endif
    @endif
@endforeach
```

### 5. Action Buttons Component

```blade
{{-- components/ui/action-buttons.blade.php --}}
@props([
    'module',
    'showView' => true,
    'showEdit' => true,
    'showDelete' => true,
    'showExport' => false,
    'viewUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'exportUrl' => null,
])

<div class="flex items-center gap-2">
    @if($showView && auth()->user()->hasPermission($module . '.view'))
        <a href="{{ $viewUrl }}" class="...">View</a>
    @endif
    
    @if($showEdit && auth()->user()->hasPermission($module . '.update'))
        <a href="{{ $editUrl }}" class="...">Edit</a>
    @endif
    
    @if($showDelete && auth()->user()->hasPermission($module . '.delete'))
        <button onclick="showDeleteModal('{{ $deleteUrl }}')" class="...">Delete</button>
    @endif
    
    @if($showExport && auth()->user()->hasPermission($module . '.export'))
        <a href="{{ $exportUrl }}" class="...">Export</a>
    @endif
</div>
```

## Data Flow

### Permission Check Flow

```
1. User logs in
   └── Role loaded with permissions array
       └── Permissions cached in session

2. User navigates to /internal/employee
   └── CheckPermission middleware triggered
       └── Extract module: internal_employee
       └── Extract action: view (from index route)
       └── Check: user.hasPermission('internal_employee.view')
           ├── TRUE: Continue to controller
           └── FALSE: Return 403 / redirect with error

3. View renders
   └── Sidebar checks hasModuleAccess() for each menu
   └── Action buttons check hasPermission() for each action
   └── Table row icons check hasPermission() for each action
```

### Permission Storage Format

```json
// Role.permissions (JSON column)
[
    "overview.view",
    "overview.export",
    "internal_credentials.view",
    "internal_credentials.create",
    "internal_credentials.update",
    "internal_credentials.delete",
    "internal_employee.view",
    "internal_employee.create",
    "internal_employee.update",
    "internal_employee.delete",
    "internal_employee.export",
    "internal_inventory_assets.view",
    "internal_inventory_assets.create",
    "internal_inventory_assets.update",
    "internal_inventory_assets.delete",
    "internal_inventory_assets.export",
    "internal_inventory_movements.view",
    "internal_inventory_movements.create",
    "internal_inventory_locations.view",
    "internal_inventory_locations.create",
    "internal_inventory_locations.update",
    "internal_inventory_locations.delete",
    "helpdesk_tickets.view",
    "helpdesk_tickets.create",
    "helpdesk_tickets.update",
    "helpdesk_tickets.delete",
    "helpdesk_tickets.assign",
    "helpdesk_templates.view",
    "helpdesk_templates.create",
    // ... etc
]
```

## Error Handling

### 403 Forbidden Response

```php
// For AJAX requests
{
    "error": "Unauthorized",
    "message": "You do not have permission to perform this action."
}

// For web requests
redirect()->back()->with('error', 'You do not have permission to perform this action.');
```

## Testing Strategy

### Unit Tests
- Test `hasPermission()` method with various permission strings
- Test `hasModuleAccess()` method with various modules
- Test middleware permission detection from route names

### Integration Tests
- Test full request flow with different role permissions
- Test sidebar visibility based on permissions
- Test action button visibility based on permissions

### Manual Testing Checklist
- [ ] Create role with limited permissions
- [ ] Assign role to user
- [ ] Login as user
- [ ] Verify sidebar shows only permitted modules
- [ ] Verify action buttons show only permitted actions
- [ ] Verify direct URL access blocked for unpermitted routes
- [ ] Verify API requests blocked for unpermitted actions
