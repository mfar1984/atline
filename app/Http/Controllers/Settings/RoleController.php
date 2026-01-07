<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $roles = $query->orderBy('name')->paginate(\App\Models\SystemSetting::paginationSize())->withQueryString();

        return view('settings.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('settings.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:500',
            'permission_matrix' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);

        // Convert permission matrix to flat array
        $permissions = [];
        if ($request->has('permission_matrix')) {
            foreach ($request->permission_matrix as $module => $actions) {
                foreach ($actions as $action => $value) {
                    if ($value) {
                        $permissions[] = "{$module}.{$action}";
                    }
                }
            }
        }

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'permissions' => $permissions,
            'is_active' => $validated['is_active'],
        ]);

        try {
            ActivityLogService::logCreate($role, 'settings_roles', "Created role: {$role->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->loadCount('users');

        try {
            ActivityLogService::logView($role, 'settings_roles', "Viewed role: {$role->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return view('settings.roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        return view('settings.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permission_matrix' => 'nullable|array',
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $role->only(['name', 'description', 'permissions', 'is_active']);

        // Convert permission matrix to flat array
        $permissions = [];
        if ($request->has('permission_matrix')) {
            foreach ($request->permission_matrix as $module => $actions) {
                foreach ($actions as $action => $value) {
                    if ($value) {
                        $permissions[] = "{$module}.{$action}";
                    }
                }
            }
        }

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'permissions' => $permissions,
            'is_active' => $validated['is_active'],
        ]);

        try {
            ActivityLogService::logUpdate($role, 'settings_roles', $oldValues, "Updated role: {$role->name}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        return redirect()->route('settings.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (!$role->canBeDeleted()) {
            return redirect()->route('settings.roles.index')
                ->with('error', 'Cannot delete role. It is assigned to users.');
        }

        $roleName = $role->name;

        try {
            ActivityLogService::logDelete($role, 'settings_roles', "Deleted role: {$roleName}");
        } catch (\Exception $e) {
            // Silent fail - don't interrupt main operation
        }

        $role->delete();

        return redirect()->route('settings.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
