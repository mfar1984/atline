<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AttachmentService;
use App\Traits\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    use ProjectAccess;

    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request)
    {
        $isStaff = $this->isStaff();
        $isClient = !$isStaff;
        
        $query = Project::with(['organization', 'users']);

        // Apply project access filter for client users
        $this->applyProjectAccessFilter($query);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('organization', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Organization filter (staff only)
        if ($isStaff && $request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        $projects = $query->withCount('assets')->latest()->paginate(\App\Models\SystemSetting::paginationSize());

        // Get organizations for filter dropdown (staff only)
        $organizations = $isStaff ? Organization::active()->orderBy('name')->get() : collect();

        // For backward compatibility with views
        $client = $this->getClientForUser();

        return view('external.projects.index', compact('projects', 'organizations', 'client', 'isStaff', 'isClient'));
    }

    public function create()
    {
        $isStaff = $this->isStaff();
        
        // Only staff can create projects
        if (!$isStaff) {
            abort(403, 'You do not have permission to create projects.');
        }
        
        $organizations = Organization::active()->orderBy('name')->get();
        
        // Get client users for assignment
        $clientUsers = User::whereHas('role', function($q) {
            $q->where('name', 'Client');
        })->where('is_active', true)->orderBy('name')->get();

        // For backward compatibility
        $client = null;
        $clients = collect(); // Legacy - not used anymore

        return view('external.projects.create', compact('organizations', 'clientUsers', 'client', 'clients', 'isStaff'));
    }

    public function store(Request $request)
    {
        // Only staff can create projects
        if (!$this->isStaff()) {
            abort(403, 'You do not have permission to create projects.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|exists:organizations,id',
            'project_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,completed,on_hold',
            'purchase_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:50',
            'warranty_expiry' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'project_value' => $validated['project_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'purchase_date' => $validated['purchase_date'] ?? null,
            'po_number' => $validated['po_number'] ?? null,
            'warranty_period' => $validated['warranty_period'] ?? null,
            'warranty_expiry' => $validated['warranty_expiry'] ?? null,
        ]);

        // Assign users to project
        if (!empty($validated['user_ids'])) {
            $project->users()->sync($validated['user_ids']);
        }

        // Log project creation
        try {
            ActivityLogService::logCreate($project, 'external_projects', "Created project {$project->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // Handle file attachments
        if ($request->hasFile('attachment_files')) {
            foreach ($request->file('attachment_files') as $index => $file) {
                $displayName = $request->attachment_names[$index] ?? $file->getClientOriginalName();
                $this->attachmentService->store($file, $project, $displayName);
            }
        }

        return redirect()->route('external.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $isStaff = $this->isStaff();
        $isClient = !$isStaff;
        
        // Check access
        if (!$this->canAccessProject($project)) {
            abort(403, 'You do not have permission to view this project.');
        }
        
        $project->load(['assets.category', 'assets.brand', 'attachments', 'organization', 'users']);
        
        // Log view activity
        try {
            ActivityLogService::logView($project, 'external_projects', "Viewed project {$project->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // For backward compatibility
        $client = $this->getClientForUser();
        
        return view('external.projects.show', compact('project', 'client', 'isClient'));
    }

    public function edit(Project $project)
    {
        $isStaff = $this->isStaff();
        
        // Check access
        if (!$this->canAccessProject($project)) {
            abort(403, 'You do not have permission to edit this project.');
        }
        
        // Only staff can edit projects
        if (!$isStaff) {
            abort(403, 'You do not have permission to edit projects.');
        }
        
        $project->load(['attachments', 'users']);
        
        $organizations = Organization::active()->orderBy('name')->get();
        
        // Get client users for assignment
        $clientUsers = User::whereHas('role', function($q) {
            $q->where('name', 'Client');
        })->where('is_active', true)->orderBy('name')->get();

        // For backward compatibility
        $client = null;
        $clients = collect();
        
        return view('external.projects.edit', compact('project', 'organizations', 'clientUsers', 'client', 'clients', 'isStaff'));
    }

    public function update(Request $request, Project $project)
    {
        // Check access
        if (!$this->canAccessProject($project)) {
            abort(403, 'You do not have permission to update this project.');
        }
        
        // Only staff can update projects
        if (!$this->isStaff()) {
            abort(403, 'You do not have permission to update projects.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'nullable|exists:organizations,id',
            'project_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,completed,on_hold',
            'purchase_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:50',
            'warranty_expiry' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $oldValues = $project->only(['name', 'description', 'status', 'project_value']);
        
        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organization_id' => $validated['organization_id'] ?? null,
            'project_value' => $validated['project_value'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'purchase_date' => $validated['purchase_date'] ?? null,
            'po_number' => $validated['po_number'] ?? null,
            'warranty_period' => $validated['warranty_period'] ?? null,
            'warranty_expiry' => $validated['warranty_expiry'] ?? null,
        ]);

        // Sync users
        $project->users()->sync($validated['user_ids'] ?? []);
        
        // Log update activity
        try {
            ActivityLogService::logUpdate($project, 'external_projects', $oldValues, "Updated project {$project->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // Handle new file attachments
        if ($request->hasFile('attachment_files')) {
            foreach ($request->file('attachment_files') as $index => $file) {
                $displayName = $request->attachment_names[$index] ?? $file->getClientOriginalName();
                $this->attachmentService->store($file, $project, $displayName);
            }
        }

        return redirect()->route('external.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        // Check access
        if (!$this->canAccessProject($project)) {
            abort(403, 'You do not have permission to delete this project.');
        }
        
        // Only staff can delete projects
        if (!$this->isStaff()) {
            abort(403, 'You do not have permission to delete projects.');
        }
        
        // Check if project has linked assets
        if (!$project->canBeDeleted()) {
            return redirect()->route('external.projects.index')
                ->with('error', 'Cannot delete project with linked assets. Please remove all assets first.');
        }

        // Log delete activity before deletion
        try {
            ActivityLogService::logDelete($project, 'external_projects', "Deleted project {$project->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }

        // Delete all attachments first
        foreach ($project->attachments as $attachment) {
            $this->attachmentService->delete($attachment);
        }

        // Detach all users
        $project->users()->detach();

        $project->delete();

        return redirect()->route('external.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
