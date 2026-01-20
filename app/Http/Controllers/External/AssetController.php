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

    /**
     * Bulk store assets from CSV
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        // Verify user can access this project
        $project = Project::find($request->project_id);
        if (!$project || !$this->canAccessProject($project)) {
            return back()->with('error', 'You do not have permission to add assets to this project.');
        }

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            // Remove header row
            $headers = array_shift($csvData);
            
            // Validate headers
            $expectedHeaders = ['category_name', 'brand_name', 'model', 'serial_number', 'vendor_name', 'location_name', 'status', 'assigned_to', 'department', 'unit_price', 'notes'];
            if ($headers !== $expectedHeaders) {
                return back()->with('error', 'Invalid CSV format. Please use the provided template.');
            }

            $createdCount = 0;
            $errors = [];
            $rowNumber = 2; // Start from 2 (after header)

            foreach ($csvData as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    $rowNumber++;
                    continue;
                }

                try {
                    // Parse CSV row
                    $categoryName = trim($row[0] ?? '');
                    $brandName = trim($row[1] ?? '');
                    $model = trim($row[2] ?? '');
                    $serialNumber = trim($row[3] ?? '');
                    $vendorName = trim($row[4] ?? '');
                    $locationName = trim($row[5] ?? '');
                    $status = trim($row[6] ?? 'active');
                    $assignedTo = trim($row[7] ?? '');
                    $department = trim($row[8] ?? '');
                    $unitPrice = trim($row[9] ?? '');
                    $notes = trim($row[10] ?? '');

                    // Validate status
                    if (!in_array($status, ['active', 'spare', 'damaged', 'maintenance', 'disposed'])) {
                        $errors[] = "Row {$rowNumber}: Invalid status '{$status}'. Must be: active, spare, damaged, maintenance, or disposed.";
                        $rowNumber++;
                        continue;
                    }

                    // Find or skip if not found
                    $categoryId = null;
                    if ($categoryName) {
                        $category = Category::where('name', $categoryName)->first();
                        if (!$category) {
                            $errors[] = "Row {$rowNumber}: Category '{$categoryName}' not found. Skipping row.";
                            $rowNumber++;
                            continue;
                        }
                        $categoryId = $category->id;
                    }

                    $brandId = null;
                    if ($brandName) {
                        $brand = Brand::where('name', $brandName)->first();
                        $brandId = $brand ? $brand->id : null;
                    }

                    $vendorId = null;
                    if ($vendorName) {
                        $vendor = Vendor::where('name', $vendorName)->first();
                        $vendorId = $vendor ? $vendor->id : null;
                    }

                    $locationId = null;
                    if ($locationName) {
                        $location = Location::where('name', $locationName)->first();
                        $locationId = $location ? $location->id : null;
                    }

                    // Generate asset tag
                    $assetTag = $this->assetService->generateAssetTag();

                    // Create asset
                    $asset = Asset::create([
                        'project_id' => $request->project_id,
                        'category_id' => $categoryId,
                        'brand_id' => $brandId,
                        'model' => $model ?: null,
                        'serial_number' => $serialNumber ?: null,
                        'vendor_id' => $vendorId,
                        'location_id' => $locationId,
                        'status' => $status,
                        'assigned_to' => $assignedTo ?: null,
                        'department' => $department ?: null,
                        'unit_price' => $unitPrice ? floatval($unitPrice) : null,
                        'notes' => $notes ?: null,
                        'asset_tag' => $assetTag,
                    ]);

                    // Log asset creation
                    try {
                        ActivityLogService::logCreate($asset, 'external_inventory', "Bulk created asset {$asset->asset_tag}");
                    } catch (\Exception $e) {
                        \Log::error('Activity logging failed: ' . $e->getMessage());
                    }

                    $createdCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }

                $rowNumber++;
            }

            // Prepare success message
            $message = "{$createdCount} asset(s) created successfully from CSV.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " row(s) had errors.";
            }

            if ($createdCount > 0) {
                return redirect()->route('external.inventory.index')
                    ->with('success', $message)
                    ->with('errors', $errors);
            } else {
                return back()->with('error', 'No assets were created. Please check your CSV file.')
                    ->with('errors', $errors);
            }

        } catch (\Exception $e) {
            \Log::error('Bulk asset upload failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to process CSV file: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'bulk_asset_template_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'category_name',
                'brand_name',
                'model',
                'serial_number',
                'vendor_name',
                'location_name',
                'status',
                'assigned_to',
                'department',
                'unit_price',
                'notes'
            ]);
            
            // Example rows
            fputcsv($file, [
                'Laptop',
                'Dell',
                'Latitude 5420',
                'SN123456789',
                'Dell Malaysia',
                'Office HQ',
                'active',
                'John Doe',
                'IT Department',
                '3500.00',
                'New laptop for IT staff'
            ]);
            
            fputcsv($file, [
                'Desktop',
                'HP',
                'EliteDesk 800 G6',
                'SN987654321',
                'HP Store',
                'Office HQ',
                'spare',
                '',
                'Finance',
                '2800.00',
                'Spare desktop for finance team'
            ]);
            
            fputcsv($file, [
                'Monitor',
                'Samsung',
                '27" LED',
                'MON2024001',
                'Samsung Malaysia',
                'Meeting Room A',
                'active',
                'Meeting Room',
                'General',
                '850.00',
                ''
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
