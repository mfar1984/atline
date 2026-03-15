<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix: Vendor belongs to assets (inventory), not projects
     */
    public function up(): void
    {
        // Remove vendor_id from projects table if it exists
        if (Schema::hasColumn('projects', 'vendor_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            });
        }

        // Ensure vendor_id exists in assets table
        if (!Schema::hasColumn('assets', 'vendor_id')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->foreignId('vendor_id')->nullable()->after('location_id')->constrained()->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a fix migration, no need to reverse
    }
};
