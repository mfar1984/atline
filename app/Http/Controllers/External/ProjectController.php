<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use App\Services\ActivityLogService;
use App\Services\AttachmentService;
use App\Traits\ClientIsolation;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ClientIsolation;

    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function index(Request $request)
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        $isClient = !$isStaff;
        
        $query = Project::with('client');

        // Client isolation - clients only see their own projects
        if ($client) {
            $query->where('client_id', $client->id);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->withCount('assets')->latest()->paginate(\App\Models\SystemSetting::paginationSize());

        return view('external.projects.index', compact('projects', 'client', 'isStaff', 'isClient'));
    }

    public function create()
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        
        // If client user, they can only create projects for themselves
        if ($client) {
            $clients = collect([$client]);
        } else {
            $clients = Client::active()->orderBy('name')->get();
        }
        
        return view('external.projects.create', compact('clients', 'client', 'isStaff'));
    }

    public function store(Request $request)
    {
        $client = $this->getClientForUser();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => $client ? 'nullable' : 'nullable|exists:clients,id',
            'project_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,completed,on_hold',
            'purchase_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:50',
            'warranty_expiry' => 'nullable|date',
        ]);

        // Force client_id for client users
        if ($client) {
            $validated['client_id'] = $client->id;
        }

        $project = Project::create($validated);

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
        $client = $this->getClientForUser();
        $isClient = !$this->isStaff();
        
        // Client isolation - clients can only view their own projects
        if ($client && $project->client_id !== $client->id) {
            abort(403, 'You do not have permission to view this project.');
        }
        
        $project->load(['assets.category', 'assets.brand', 'attachments', 'client']);
        
        // Log view activity
        try {
            ActivityLogService::logView($project, 'external_projects', "Viewed project {$project->name}");
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
        
        return view('external.projects.show', compact('project', 'client', 'isClient'));
    }

    public function edit(Project $project)
    {
        $client = $this->getClientForUser();
        $isStaff = $this->isStaff();
        
        // Client isolation - clients can only edit their own projects
        if ($client && $project->client_id !== $client->id) {
            abort(403, 'You do not have permission to edit this project.');
        }
        
        $project->load('attachments');
        
        // If client user, they can only see themselves in the dropdown
        if ($client) {
            $clients = collect([$client]);
        } else {
            $clients = Client::active()->orderBy('name')->get();
        }
        
        return view('external.projects.edit', compact('project', 'clients', 'client', 'isStaff'));
    }

    public function update(Request $request, Project $project)
    {
        $client = $this->getClientForUser();
        
        // Client isolation - clients can only update their own projects
        if ($client && $project->client_id !== $client->id) {
            abort(403, 'You do not have permission to update this project.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => $client ? 'nullable' : 'nullable|exists:clients,id',
            'project_value' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:active,completed,on_hold',
            'purchase_date' => 'nullable|date',
            'po_number' => 'nullable|string|max:100',
            'warranty_period' => 'nullable|string|max:50',
            'warranty_expiry' => 'nullable|date',
        ]);

        // Force client_id for client users (prevent changing to another client)
        if ($client) {
            $validated['client_id'] = $client->id;
        }

        $oldValues = $project->only(['name', 'description', 'status', 'project_value']);
        $project->update($validated);
        
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
        $client = $this->getClientForUser();
        
        // Client isolation - clients can only delete their own projects
        if ($client && $project->client_id !== $client->id) {
            abort(403, 'You do not have permission to delete this project.');
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

        $project->delete();

        return redirect()->route('external.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
