<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration handles data transformation from old structure to new structure:
     * 1. Create organizations from existing clients
     * 2. Link projects to organizations
     * 3. Link client users to their projects via project_user
     * 4. Update tickets with organization_id
     */
    public function up(): void
    {
        // Step 1: Create organizations from existing clients
        // Each existing client becomes an organization
        $clients = DB::table('clients')->get();
        
        foreach ($clients as $client) {
            // Check if organization with same name already exists
            $existingOrg = DB::table('organizations')->where('name', $client->name)->first();
            
            if (!$existingOrg) {
                $orgId = DB::table('organizations')->insertGetId([
                    'name' => $client->name,
                    'organization_type' => $client->organization_type ?? null,
                    'address_1' => $client->address_1 ?? null,
                    'address_2' => $client->address_2 ?? null,
                    'postcode' => $client->postcode ?? null,
                    'district' => $client->district ?? null,
                    'state' => $client->state ?? null,
                    'country' => $client->country ?? null,
                    'website' => $client->website ?? null,
                    'phone' => $client->phone ?? null,
                    'email' => $client->email ?? null,
                    'contact_person' => $client->contact_person ?? null,
                    'is_active' => $client->is_active ?? true,
                    'created_at' => $client->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            } else {
                $orgId = $existingOrg->id;
            }
            
            // Step 2: Update projects that belong to this client
            // Link them to the new organization
            DB::table('projects')
                ->where('client_id', $client->id)
                ->update(['organization_id' => $orgId]);
            
            // Step 3: If client has a user account, link user to all projects under this client
            if ($client->user_id) {
                $projectIds = DB::table('projects')
                    ->where('client_id', $client->id)
                    ->pluck('id');
                
                foreach ($projectIds as $projectId) {
                    // Check if relationship already exists
                    $exists = DB::table('project_user')
                        ->where('project_id', $projectId)
                        ->where('user_id', $client->user_id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('project_user')->insert([
                            'project_id' => $projectId,
                            'user_id' => $client->user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            // Step 4: Update tickets that belong to this client
            // Link them to the organization
            DB::table('tickets')
                ->where('client_id', $client->id)
                ->update(['organization_id' => $orgId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the project_user relationships
        DB::table('project_user')->truncate();
        
        // Clear organization_id from projects
        DB::table('projects')->update(['organization_id' => null]);
        
        // Clear organization_id from tickets
        DB::table('tickets')->update(['organization_id' => null]);
        
        // Note: We don't delete organizations as they might have been created manually
    }
};
