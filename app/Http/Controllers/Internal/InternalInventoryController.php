<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\InternalAsset;
use App\Models\InternalCategory;
use App\Models\InternalBrand;
use App\Models\InternalLocation;
use App\Models\AssetMovement;
use App\Models\Employee;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class InternalInventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $status = $request->get('status');
        
        // Define tabs with their permissions in order
        $tabPermissions = [
            'assets' => 'internal_inventory_assets.view',
            'movements' => 'internal_inventory_movements.view',
            'checkout' => 'internal_inventory_checkout.view',
            'locations' => 'internal_inventory_locations.view',
            'brands' => 'internal_inventory_brands.view',
            'categories' => 'internal_inventory_categories.view',
        ];

        // Get requested tab or find first accessible tab
        $requestedTab = $request->get('tab');
        $activeTab = null;

        if ($requestedTab && isset($tabPermissions[$requestedTab])) {
            // Check if user has permission for requested tab
            if ($user->hasPermission($tabPermissions[$requestedTab])) {
                $activeTab = $requestedTab;
            }
        }

        // If no valid tab yet, find first accessible tab
        if (!$activeTab) {
            foreach ($tabPermissions as $tab => $permission) {
                if ($user->hasPermission($permission)) {
                    $activeTab = $tab;
                    break;
                }
            }
        }

        // If user has no permission for any tab, deny access
        if (!$activeTab) {
            abort(403, 'You do not have permission to access any inventory tabs.');
        }

        // If requested tab differs from active tab (no permission), redirect
        if ($requestedTab && $requestedTab !== $activeTab) {
            return redirect()->route('internal.inventory.index', ['tab' => $activeTab]);
        }
        
        $data = [
            'activeTab' => $activeTab,
        ];

        switch ($activeTab) {
            case 'assets':
                $query = InternalAsset::with(['category', 'brand', 'location', 'currentMovement.employee']);
                
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('asset_tag', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('serial_number', 'like', "%{$search}%")
                          ->orWhere('model', 'like', "%{$search}%");
                    });
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                if ($request->get('category')) {
                    $query->where('category_id', $request->get('category'));
                }
                
                $data['assets'] = $query->orderByDesc('created_at')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
                $data['categories'] = InternalCategory::where('is_active', true)->get();
                $data['brands'] = InternalBrand::where('is_active', true)->get();
                $data['locations'] = InternalLocation::where('is_active', true)->get();
                break;
                
            case 'movements':
                $query = AssetMovement::with(['asset', 'employee', 'approver']);
                
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('purpose', 'like', "%{$search}%")
                          ->orWhereHas('asset', function($q2) use ($search) {
                              $q2->where('asset_tag', 'like', "%{$search}%")
                                 ->orWhere('name', 'like', "%{$search}%");
                          })
                          ->orWhereHas('employee', function($q2) use ($search) {
                              $q2->where('full_name', 'like', "%{$search}%");
                          });
                    });
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
                
                $data['movements'] = $query->orderByDesc('created_at')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
                break;
                
            case 'checkout':
                $data['availableAssets'] = InternalAsset::where('status', 'available')
                    ->with(['category', 'brand'])
                    ->get();
                $data['employees'] = Employee::where('status', 'active')->orderBy('full_name')->get();
                $data['checkedOutAssets'] = AssetMovement::where('status', 'checked_out')
                    ->with(['asset', 'employee'])
                    ->get();
                break;
                
            case 'locations':
                $query = InternalLocation::withCount('assets');
                
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
                
                $data['locations'] = $query->orderBy('name')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
                break;
                
            case 'brands':
                $query = InternalBrand::withCount('assets');
                
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
                
                $data['brands'] = $query->orderBy('name')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
                break;
                
            case 'categories':
                $query = InternalCategory::withCount('assets');
                
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
                
                $data['categories'] = $query->orderBy('name')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();
                break;
        }

        return view('internal.inventory.index', $data);
    }

    // Asset CRUD
    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:internal_assets,asset_tag',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:internal_categories,id',
            'brand_id' => 'nullable|exists:internal_brands,id',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:internal_locations,id',
            'condition' => 'required|in:excellent,good,fair,poor',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'available';
        $asset = InternalAsset::create($validated);
        
        // Log asset creation
        try {
            ActivityLogService::logCreate($asset, 'internal_inventory', "Created internal asset {$asset->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.inventory.index', ['tab' => 'assets'])
            ->with('success', 'Asset created successfully.');
    }

    public function updateAsset(Request $request, InternalAsset $asset)
    {
        $validated = $request->validate([
            'asset_tag' => 'required|unique:internal_assets,asset_tag,' . $asset->id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:internal_categories,id',
            'brand_id' => 'nullable|exists:internal_brands,id',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:internal_locations,id',
            'condition' => 'required|in:excellent,good,fair,poor',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $asset->only(['name', 'status', 'condition']);
        $asset->update($validated);
        
        // Log asset update
        try {
            ActivityLogService::logUpdate($asset, 'internal_inventory', $oldValues, "Updated internal asset {$asset->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.inventory.index', ['tab' => 'assets'])
            ->with('success', 'Asset updated successfully.');
    }

    public function destroyAsset(InternalAsset $asset)
    {
        // Log asset deletion before deleting
        try {
            ActivityLogService::logDelete($asset, 'internal_inventory', "Deleted internal asset {$asset->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        $asset->delete();
        return redirect()->route('internal.inventory.index', ['tab' => 'assets'])
            ->with('success', 'Asset deleted successfully.');
    }

    // Checkout/Checkin
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'internal_asset_id' => 'required|exists:internal_assets,id',
            'employee_id' => 'required|exists:employees,id',
            'expected_return_date' => 'required|date|after:today',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $asset = InternalAsset::findOrFail($validated['internal_asset_id']);
        
        if ($asset->status !== 'available') {
            return back()->with('error', 'Asset is not available for checkout.');
        }

        AssetMovement::create([
            'internal_asset_id' => $validated['internal_asset_id'],
            'employee_id' => $validated['employee_id'],
            'checkout_date' => now(),
            'expected_return_date' => $validated['expected_return_date'],
            'checkout_condition' => $asset->condition,
            'purpose' => $validated['purpose'],
            'status' => 'checked_out',
            'approved_by' => auth()->id(),
            'notes' => $validated['notes'],
        ]);

        $asset->update(['status' => 'checked_out']);
        
        // Log checkout activity
        try {
            $employee = Employee::find($validated['employee_id']);
            ActivityLogService::logMovement($asset, $employee, 'checkout');
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.inventory.index', ['tab' => 'checkout'])
            ->with('success', 'Asset checked out successfully.');
    }

    public function checkin(Request $request, AssetMovement $movement)
    {
        // Check if current user's employee is the one who checked out the asset
        $currentEmployee = Employee::where('user_id', auth()->id())->first();
        
        if (!$currentEmployee) {
            return redirect()->route('internal.inventory.index', ['tab' => 'checkout'])
                ->with('error', 'Your account is not linked to an employee profile.');
        }
        
        if ($movement->employee_id !== $currentEmployee->id) {
            return redirect()->route('internal.inventory.index', ['tab' => 'checkout'])
                ->with('error', 'You can only check in assets that you have checked out.');
        }
        
        $validated = $request->validate([
            'return_condition' => 'required|in:excellent,good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $movement->update([
            'actual_return_date' => now(),
            'return_condition' => $validated['return_condition'],
            'status' => 'returned',
            'notes' => $movement->notes . ($validated['notes'] ? "\nReturn: " . $validated['notes'] : ''),
        ]);

        $movement->asset->update([
            'status' => 'available',
            'condition' => $validated['return_condition'],
        ]);
        
        // Log checkin activity
        try {
            ActivityLogService::logMovement($movement->asset, $movement->employee, 'checkin');
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('internal.inventory.index', ['tab' => 'checkout'])
            ->with('success', 'Asset checked in successfully.');
    }

    public function printMovement(AssetMovement $movement)
    {
        $movement->load(['asset.category', 'asset.brand', 'asset.location', 'employee', 'approver']);
        
        return view('internal.inventory.print-movement', compact('movement'));
    }

    // Location CRUD
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        InternalLocation::create($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'locations'])
            ->with('success', 'Location created successfully.');
    }

    public function updateLocation(Request $request, InternalLocation $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'locations'])
            ->with('success', 'Location updated successfully.');
    }

    public function destroyLocation(InternalLocation $location)
    {
        $location->delete();
        return redirect()->route('internal.inventory.index', ['tab' => 'locations'])
            ->with('success', 'Location deleted successfully.');
    }

    // Brand CRUD
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        InternalBrand::create($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'brands'])
            ->with('success', 'Brand created successfully.');
    }

    public function updateBrand(Request $request, InternalBrand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $brand->update($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'brands'])
            ->with('success', 'Brand updated successfully.');
    }

    public function destroyBrand(InternalBrand $brand)
    {
        $brand->delete();
        return redirect()->route('internal.inventory.index', ['tab' => 'brands'])
            ->with('success', 'Brand deleted successfully.');
    }

    // Category CRUD
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        InternalCategory::create($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'categories'])
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, InternalCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('internal.inventory.index', ['tab' => 'categories'])
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(InternalCategory $category)
    {
        $category->delete();
        return redirect()->route('internal.inventory.index', ['tab' => 'categories'])
            ->with('success', 'Category deleted successfully.');
    }

    // Toggle Status
    public function toggleStatus($type, $id)
    {
        $model = match($type) {
            'locations' => InternalLocation::findOrFail($id),
            'brands' => InternalBrand::findOrFail($id),
            'categories' => InternalCategory::findOrFail($id),
            default => abort(404),
        };

        $model->update(['is_active' => !$model->is_active]);

        return response()->json(['success' => true, 'is_active' => $model->is_active]);
    }
}
