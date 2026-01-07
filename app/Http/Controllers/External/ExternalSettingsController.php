<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ActivityLogService;
use App\Traits\ClientIsolation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ExternalSettingsController extends Controller
{
    use ClientIsolation;

    public function index(Request $request)
    {
        // Client users should not access settings - only staff/admin
        $client = $this->getClientForUser();
        if ($client) {
            abort(403, 'You do not have permission to access settings.');
        }
        
        $activeTab = $request->get('tab', 'clients');
        $perPage = \App\Models\SystemSetting::paginationSize();
        $search = $request->get('search');
        $status = $request->get('status');
        
        $data = match($activeTab) {
            'clients' => [
                'clients' => $this->getClients($search, $status, $perPage),
                'roles' => Role::active()->orderBy('name')->get(),
            ],
            'vendors' => ['vendors' => $this->getVendors($search, $status, $perPage)],
            'locations' => ['locations' => $this->getLocations($search, $status, $perPage), 'allLocations' => Location::orderBy('name')->get()],
            'brands' => ['brands' => $this->getBrands($search, $status, $perPage)],
            'categories' => ['categories' => $this->getCategories($search, $status, $perPage)],
            default => [
                'clients' => $this->getClients($search, $status, $perPage),
                'roles' => Role::active()->orderBy('name')->get(),
            ],
        };
        
        return view('external.settings.index', array_merge($data, ['activeTab' => $activeTab]));
    }

    private function getClients($search, $status, $perPage)
    {
        $query = Client::with('user');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }
        
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    private function getVendors($search, $status, $perPage)
    {
        $query = Vendor::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('incharge_name', 'like', "%{$search}%");
            });
        }
        
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    private function getLocations($search, $status, $perPage)
    {
        $query = Location::with('parent');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }
        
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    private function getBrands($search, $status, $perPage)
    {
        $query = Brand::query();
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    private function getCategories($search, $status, $perPage)
    {
        $query = Category::withCount('assets');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
    }

    // Client CRUD
    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_type' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'create_account' => 'nullable|boolean',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $clientData = collect($validated)->except(['create_account', 'email', 'password', 'role_id', 'client_email'])->toArray();
        $clientData['is_active'] = true;
        
        // Set client email (separate from user account email)
        if ($request->filled('client_email')) {
            $clientData['email'] = $validated['client_email'];
        }

        // Create user account if requested
        if ($request->boolean('create_account') && $request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'] ?? null,
                'is_active' => true,
            ]);
            $clientData['user_id'] = $user->id;
        }

        Client::create($clientData);

        // Log client creation
        try {
            $createdClient = Client::latest()->first();
            ActivityLogService::logCreate($createdClient, 'external_settings', "Created client {$validated['name']}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'clients'])
            ->with('success', 'Client created successfully.');
    }

    public function updateClient(Request $request, Client $client)
    {
        \Log::info('updateClient called', [
            'client_id' => $client->id,
            'request_data' => $request->all(),
        ]);
        
        // Build email validation rule - exclude existing user's email if client has account
        $emailRule = 'nullable|email|max:255';
        if ($client->user_id) {
            $emailRule .= '|unique:users,email,' . $client->user_id;
        } else {
            $emailRule .= '|unique:users,email';
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_type' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'create_account' => 'nullable|boolean',
            'email' => $emailRule,
            'password' => 'nullable|string|min:6',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $clientData = collect($validated)->except(['create_account', 'email', 'password', 'role_id', 'client_email'])->toArray();
        
        // Set client email (separate from user account email)
        if ($request->filled('client_email')) {
            $clientData['email'] = $validated['client_email'];
        } elseif ($request->has('client_email')) {
            // If field is present but empty, clear the email
            $clientData['email'] = null;
        }

        // Create user account if requested and client doesn't have one
        if (!$client->user_id && $request->boolean('create_account') && $request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'] ?? null,
                'is_active' => true,
            ]);
            $clientData['user_id'] = $user->id;
        }

        $oldValues = $client->only(['name', 'organization_type', 'state', 'is_active']);
        $client->update($clientData);
        
        // Log client update
        try {
            ActivityLogService::logUpdate($client, 'external_settings', $oldValues, "Updated client {$client->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'clients'])
            ->with('success', 'Client updated successfully.');
    }

    public function destroyClient(Client $client)
    {
        if (!$client->canBeDeleted()) {
            return redirect()->route('external.settings.index', ['tab' => 'clients'])
                ->with('error', 'Cannot delete client. It is linked to projects.');
        }

        // Log client deletion before deleting
        try {
            ActivityLogService::logDelete($client, 'external_settings', "Deleted client {$client->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $client->delete();

        return redirect()->route('external.settings.index', ['tab' => 'clients'])
            ->with('success', 'Client deleted successfully.');
    }

    // Vendor CRUD
    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_type' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'incharge_name' => 'nullable|string|max:255',
            'incharge_phone' => 'nullable|string|max:50',
            'incharge_whatsapp' => 'nullable|string|max:50',
            'incharge_email' => 'nullable|email|max:255',
        ]);

        $validated['is_active'] = true;
        $vendor = Vendor::create($validated);
        
        // Log vendor creation
        try {
            ActivityLogService::logCreate($vendor, 'external_settings', "Created vendor {$vendor->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'vendors'])
            ->with('success', 'Vendor created successfully.');
    }

    public function updateVendor(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_type' => 'nullable|string|max:50',
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'district' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:50',
            'incharge_name' => 'nullable|string|max:255',
            'incharge_phone' => 'nullable|string|max:50',
            'incharge_whatsapp' => 'nullable|string|max:50',
            'incharge_email' => 'nullable|email|max:255',
        ]);

        $oldValues = $vendor->only(['name', 'organization_type', 'state']);
        $vendor->update($validated);
        
        // Log vendor update
        try {
            ActivityLogService::logUpdate($vendor, 'external_settings', $oldValues, "Updated vendor {$vendor->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'vendors'])
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroyVendor(Vendor $vendor)
    {
        if (!$vendor->canBeDeleted()) {
            return redirect()->route('external.settings.index', ['tab' => 'vendors'])
                ->with('error', 'Cannot delete vendor. It is linked to assets.');
        }

        // Log vendor deletion before deleting
        try {
            ActivityLogService::logDelete($vendor, 'external_settings', "Deleted vendor {$vendor->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $vendor->delete();

        return redirect()->route('external.settings.index', ['tab' => 'vendors'])
            ->with('success', 'Vendor deleted successfully.');
    }

    // Location CRUD
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:site,building,floor,room',
            'parent_id' => 'nullable|exists:locations,id',
        ]);

        // Set default type if not provided
        if (empty($validated['type'])) {
            $validated['type'] = 'site';
        }
        
        $validated['is_active'] = true;
        $location = Location::create($validated);
        
        // Log location creation
        try {
            ActivityLogService::logCreate($location, 'external_settings', "Created location {$location->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'locations'])
            ->with('success', 'Location created successfully.');
    }

    public function updateLocation(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:site,building,floor,room',
            'parent_id' => 'nullable|exists:locations,id',
        ]);

        // Build update data - keep existing type if not provided
        $updateData = ['name' => $validated['name']];
        
        if ($request->has('type') && !empty($validated['type'])) {
            $updateData['type'] = $validated['type'];
        }
        
        if ($request->has('parent_id')) {
            $updateData['parent_id'] = $validated['parent_id'];
        }

        $oldValues = $location->only(['name', 'type']);
        $location->update($updateData);
        
        // Log location update
        try {
            ActivityLogService::logUpdate($location, 'external_settings', $oldValues, "Updated location {$location->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'locations'])
            ->with('success', 'Location updated successfully.');
    }

    public function destroyLocation(Location $location)
    {
        if (!$location->canBeDeleted()) {
            return redirect()->route('external.settings.index', ['tab' => 'locations'])
                ->with('error', 'Cannot delete location. It has child locations or linked assets.');
        }

        // Log location deletion before deleting
        try {
            ActivityLogService::logDelete($location, 'external_settings', "Deleted location {$location->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $location->delete();

        return redirect()->route('external.settings.index', ['tab' => 'locations'])
            ->with('success', 'Location deleted successfully.');
    }

    // Brand CRUD
    public function storeBrand(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['is_active'] = true;
        $brand = Brand::create($validated);
        
        // Log brand creation
        try {
            ActivityLogService::logCreate($brand, 'external_settings', "Created brand {$brand->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'brands'])
            ->with('success', 'Brand created successfully.');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $oldValues = $brand->only(['name']);
        $brand->update($validated);
        
        // Log brand update
        try {
            ActivityLogService::logUpdate($brand, 'external_settings', $oldValues, "Updated brand {$brand->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'brands'])
            ->with('success', 'Brand updated successfully.');
    }

    public function destroyBrand(Brand $brand)
    {
        if (!$brand->canBeDeleted()) {
            return redirect()->route('external.settings.index', ['tab' => 'brands'])
                ->with('error', 'Cannot delete brand. It is linked to assets.');
        }

        // Log brand deletion before deleting
        try {
            ActivityLogService::logDelete($brand, 'external_settings', "Deleted brand {$brand->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $brand->delete();

        return redirect()->route('external.settings.index', ['tab' => 'brands'])
            ->with('success', 'Brand deleted successfully.');
    }

    // Category CRUD
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:categories,code',
            'fields_config' => 'nullable|array',
        ]);

        $validated['is_active'] = true;
        $category = Category::create($validated);
        
        // Log category creation
        try {
            ActivityLogService::logCreate($category, 'external_settings', "Created category {$category->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'categories'])
            ->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:categories,code,' . $category->id,
            'fields_config' => 'nullable|array',
        ]);

        $oldValues = $category->only(['name', 'code']);
        $category->update($validated);
        
        // Log category update
        try {
            ActivityLogService::logUpdate($category, 'external_settings', $oldValues, "Updated category {$category->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        return redirect()->route('external.settings.index', ['tab' => 'categories'])
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(Category $category)
    {
        if (!$category->canBeDeleted()) {
            return redirect()->route('external.settings.index', ['tab' => 'categories'])
                ->with('error', 'Cannot delete category. It is linked to assets.');
        }

        // Log category deletion before deleting
        try {
            ActivityLogService::logDelete($category, 'external_settings', "Deleted category {$category->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        $category->delete();

        return redirect()->route('external.settings.index', ['tab' => 'categories'])
            ->with('success', 'Category deleted successfully.');
    }

    // Toggle Status
    public function toggleStatus(Request $request, string $type, int $id)
    {
        $model = match($type) {
            'clients' => Client::findOrFail($id),
            'vendors' => Vendor::findOrFail($id),
            'locations' => Location::findOrFail($id),
            'brands' => Brand::findOrFail($id),
            'categories' => Category::findOrFail($id),
            default => abort(404),
        };

        $model->update(['is_active' => !$model->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $model->is_active,
        ]);
    }
}
