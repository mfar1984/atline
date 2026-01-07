<?php

namespace App\Traits;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;

trait ClientIsolation
{
    /**
     * Get the client associated with the current user (if any)
     * Returns null if user is staff/admin (not a client)
     */
    protected function getClientForUser(): ?Client
    {
        return Client::where('user_id', Auth::id())->first();
    }

    /**
     * Check if current user is staff (not a client)
     * Staff includes employees with user accounts and administrators
     */
    protected function isStaff(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // If user is linked to a client, they are NOT staff
        if (Client::where('user_id', $user->id)->exists()) {
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
     * Get client IDs that the current user can access
     * - Client user: only their own client ID
     * - Staff/Admin: all client IDs (null means no filter)
     */
    protected function getAccessibleClientIds(): ?array
    {
        $client = $this->getClientForUser();
        
        if ($client) {
            // Client user can only see their own data
            return [$client->id];
        }
        
        // Staff/Admin can see all data
        return null;
    }
}
