<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Models\Asset;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Project;
use App\Models\Vendor;
use App\Services\ActivityLogService;
use App\Services\AssetService;
use App\Services\AttachmentService;
use App\Traits\ProjectAccess;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    use ProjectAccess;

    protected AssetService $assetService;
    protected AttachmentService $attachmentService;

    public function __construct(AssetService $assetService, AttachmentService $attachmentService)
    {
        $this->assetService = $assetService;
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request)
    {
        $isStaff = $this->isStaff();
        
        $query = Asset::with(['project', 'category', 'brand', 'location', 'vendor']);

        // Apply project access filter for client users
        $projectIds = $this->getAccessibleProjectIds();
        if ($projectIds !== null) {
            $query->whereIn('project_id', $projectIds);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Project filter
        if ($request->filled('project_id')) {
            // Verify user can access this project
            $projectId = $request->project_id;
            if ($projectIds !== null && !in_array($projectId, $projectIds)) {
                $projectId = null; // Invalid project, ignore filter
            }
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $assets = $query->latest()->paginate(\App\Models\SystemSetting::paginationSize());
        
        // Get accessible projects for dropdown
        $projects = $this->getAccessibleProjects();
        
        $categories = Category::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        // For backward compatibility
        $client = $this->getClientForUser();

        return view('external.inventory.index', compact('assets', 'projects', 'categories', 'locations', 'client', 'isStaff'));
    }

    public function create()
    {
        $isStaff = $this->isStaff();
        
        // Get accessible projects for dropdown
        $projects = $this->getAccessibleProjects();
        
        // If no projects available, show error
        if ($projects->isEmpty()) {
            return redirect()->route('external.inventory.index')
                ->with('error', 'No projects available. Please create a project first or contact administrator for access.');
        }
        
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();

        // For backward compatibility
        $client = $this->getClientForUser();

        return view('external.inventory.create', compact('projects', 'categories', 'brands', 'locations', 'vendors', 'client', 'isStaff'));
    }

    public function store(Request $request)
    {
        // Validate common fields and items array
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'status' => 'required|in:active,spare,damaged,maintenance,disposed',
            'location_id' => 'nullable|exists:locations,id',
            'assigned_to' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            // Asset items validation
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'nullable|exists:categories,id',
            'items.*.vendor_id' => 'nullable|exists:vendors,id',
            'items.*.brand_id' => 'nullable|exists:brands,id',
            'items.*.model' => 'nullable|string|max:255',
            'items.*.serial_number' => 'nullable|string|max:255',
        ], [
            'items.required' => 'At least one asset item is required.',
            'items.min' => 'At least one asset item is required.',
        ]);

        // Verify user can access this project
        $project = Project::find($validated['project_id']);
        if (!$project || !$this->canAccessProject($project)) {
            abort(403, 'You do not have permission to add assets to this project.');
        }

        $createdAssets = [];
        
        foreach ($validated['items'] as $item) {
            // Auto-generate unique asset tag
            $assetTag = $this->assetService->generateAssetTag();
            
            $asset = Asset::create([
                'project_id' => $validated['project_id'],
                'category_id' => $item['category_id'] ?? null,
                'status' => $validated['status'],
                'location_id' => $validated['location_id'] ?? null,
                'vendor_id' => $item['vendor_id'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'department' => $validated['department'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'asset_tag' => $assetTag,
                'brand_id' => $item['brand_id'] ?? null,
                'model' => $item['model'] ?? null,
                'serial_number' => $item['serial_number'] ?? null,
            ]);
            
            // Log asset creation
            try {
                ActivityLogService::logCreate($asset, 'external_inventory', "Created asset {$asset->asset_tag}");
            } catch (\Exception $e) {
                \Log::error('Activity logging failed: ' . $e->getMessage());
            }
            
            $createdAssets[] = $asset;
        }

        $count = count($createdAssets);
        return redirect()->route('external.inventory.index')
            ->with('success', "{$count} asset(s) created successfully.");
    }

    public function show(Asset $inventory)
    {
        $isClient = !$this->isStaff();
        
        // Load project to check access
        $inventory->load('project');
        
        // Verify user can access this asset's project
        if ($inventory->project && !$this->canAccessProject($inventory->project)) {
            abort(403, 'You do not have permission to view this asset.');
        }
        
        $inventory->load(['project', 'category', 'brand', 'location', 'vendor', 'attachments', 'logs.user']);
        
        // Log view activity
        try {
            ActivityLogService::logView($inventory, 'external_inventory', "Viewed asset {$inventory->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // For backward compatibility
        $client = $this->getClientForUser();
        
        return view('external.inventory.show', ['asset' => $inventory, 'client' => $client, 'isClient' => $isClient]);
    }

    public function edit(Asset $inventory)
    {
        $isStaff = $this->isStaff();
        
        // Load project to check access
        $inventory->load('project');
        
        // Verify user can access this asset's project
        if ($inventory->project && !$this->canAccessProject($inventory->project)) {
            abort(403, 'You do not have permission to edit this asset.');
        }
        
        $inventory->load(['attachments']);
        
        // Get accessible projects for dropdown
        $projects = $this->getAccessibleProjects();
        
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();
        $vendors = Vendor::active()->orderBy('name')->get();

        // For backward compatibility
        $client = $this->getClientForUser();

        return view('external.inventory.edit', [
            'asset' => $inventory,
            'projects' => $projects,
            'categories' => $categories,
            'brands' => $brands,
            'locations' => $locations,
            'vendors' => $vendors,
            'client' => $client,
            'isStaff' => $isStaff,
        ]);
    }

    public function update(AssetRequest $request, Asset $inventory)
    {
        // Load project to check access
        $inventory->load('project');
        
        // Verify user can access this asset's project
        if ($inventory->project && !$this->canAccessProject($inventory->project)) {
            abort(403, 'You do not have permission to update this asset.');
        }
        
        $validated = $request->validated();
        
        // Verify user can access the new project if changing
        if (isset($validated['project_id'])) {
            $newProject = Project::find($validated['project_id']);
            if (!$newProject || !$this->canAccessProject($newProject)) {
                abort(403, 'You do not have permission to move asset to this project.');
            }
        }
        
        $oldValues = $inventory->only(['status', 'location_id', 'assigned_to', 'department']);
        
        // Handle specs (dynamic fields)
        if ($request->has('specs')) {
            $validated['specs'] = $request->specs;
        }

        $inventory->update($validated);

        // Log changes
        $this->assetService->logChanges($inventory, $oldValues, $validated);
        
        // Log update activity
        try {
            ActivityLogService::logUpdate($inventory, 'external_inventory', $oldValues, "Updated asset {$inventory->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // Handle new file attachments
        if ($request->hasFile('attachment_files')) {
            foreach ($request->file('attachment_files') as $index => $file) {
                $displayName = $request->attachment_names[$index] ?? $file->getClientOriginalName();
                $this->attachmentService->store($file, $inventory, $displayName);
            }
        }

        return redirect()->route('external.inventory.index')
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $inventory)
    {
        // Load project to check access
        $inventory->load('project');
        
        // Verify user can access this asset's project
        if ($inventory->project && !$this->canAccessProject($inventory->project)) {
            abort(403, 'You do not have permission to delete this asset.');
        }
        
        // Log delete activity before deletion
        try {
            ActivityLogService::logDelete($inventory, 'external_inventory', "Deleted asset {$inventory->asset_tag}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        // Delete all attachments first
        foreach ($inventory->attachments as $attachment) {
            $this->attachmentService->delete($attachment);
        }

        $inventory->delete();

        return redirect()->route('external.inventory.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Get dynamic fields for a category (API endpoint)
     */
    public function getDynamicFields(Category $category)
    {
        return response()->json([
            'fields' => $category->getDynamicFields(),
        ]);
    }

    /**
     * Generate Asset ID for a category (API endpoint)
     */
    public function generateAssetId(Category $category)
    {
        return response()->json([
            'asset_tag' => $this->assetService->generateAssetId($category),
        ]);
    }
}
