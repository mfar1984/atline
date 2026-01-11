<?php

namespace App\Traits;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

trait ProjectAccess
{
    /**
     * Check if current user is staff (not a client user)
     * Staff includes employees with user accounts and administrators
     */
    protected function isStaff(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // If user has Client role, they are NOT staff
        if ($user->role && $user->role->name === 'Client') {
            return false;
        }
        
        return true;
    }

    /**
     * Check if current user is Administrator
     */
    protected function isAdmin(): bool
    {
        $user = Auth::user();
        if (!$user || !$user->role) return false;
        
        return $user->role->name === 'Administrator';
    }

    /**
     * Check if current user is a client user
     */
    protected function isClientUser(): bool
    {
        return !$this->isStaff();
    }

    /**
     * Get project IDs that the current user can access
     * - Client user: only projects assigned to them via project_user
     * - Staff/Admin: all projects (returns null to indicate no filter)
     */
    protected function getAccessibleProjectIds(): ?array
    {
        $user = Auth::user();
        if (!$user) return [];
        
        // Staff/Admin can see all data
        if ($this->isStaff()) {
            return null;
        }
        
        // Client user can only see their assigned projects
        return $user->getAccessibleProjectIds();
    }

    /**
     * Check if current user can access a specific project
     */
    protected function canAccessProject(Project $project): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Staff/Admin can access all projects
        if ($this->isStaff()) {
            return true;
        }
        
        // Client user must have explicit access
        return $user->hasProjectAccess($project->id);
    }

    /**
     * Apply project access filter to a query
     * Use this for Project queries
     */
    protected function applyProjectAccessFilter($query)
    {
        $projectIds = $this->getAccessibleProjectIds();
        
        // null means no filter (staff/admin)
        if ($projectIds === null) {
            return $query;
        }
        
        return $query->whereIn('id', $projectIds);
    }

    /**
     * Apply project access filter for related models (e.g., Assets)
     * Use this for queries on models that belong to Project
     */
    protected function applyProjectRelationFilter($query, string $projectIdColumn = 'project_id')
    {
        $projectIds = $this->getAccessibleProjectIds();
        
        // null means no filter (staff/admin)
        if ($projectIds === null) {
            return $query;
        }
        
        return $query->whereIn($projectIdColumn, $projectIds);
    }

    /**
     * Get accessible projects for dropdown/selection
     */
    protected function getAccessibleProjects()
    {
        $projectIds = $this->getAccessibleProjectIds();
        
        if ($projectIds === null) {
            // Staff/Admin: return all projects
            return Project::orderBy('name')->get();
        }
        
        // Client: return only assigned projects
        return Project::whereIn('id', $projectIds)->orderBy('name')->get();
    }

    // ============================================
    // LEGACY METHODS - For backward compatibility
    // These map to the old ClientIsolation methods
    // ============================================

    /**
     * @deprecated Use isClientUser() instead
     */
    protected function getClientForUser()
    {
        // Return a fake "client" object if user is a client user
        // This maintains backward compatibility with views that check if ($client)
        $user = Auth::user();
        if (!$user) return null;
        
        if ($this->isClientUser()) {
            // Return the user's client record if exists, otherwise return user as pseudo-client
            return $user->client ?? (object)[
                'id' => $user->id,
                'name' => $user->name,
                'user_id' => $user->id,
            ];
        }
        
        return null;
    }

    /**
     * @deprecated Use getAccessibleProjectIds() instead
     */
    protected function getAccessibleClientIds(): ?array
    {
        // This method is deprecated but kept for backward compatibility
        // In the new system, we filter by project access, not client
        return null;
    }
}
