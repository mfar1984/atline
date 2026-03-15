<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing client_name values to clients table
        $existingClientNames = DB::table('projects')
            ->whereNotNull('client_name')
            ->where('client_name', '!=', '')
            ->distinct()
            ->pluck('client_name');

        foreach ($existingClientNames as $clientName) {
            DB::table('clients')->insertOrIgnore([
                'name' => $clientName,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add client_id column to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        // Update projects with corresponding client_id
        $clients = DB::table('clients')->get();
        foreach ($clients as $client) {
            DB::table('projects')
                ->where('client_name', $client->name)
                ->update(['client_id' => $client->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
