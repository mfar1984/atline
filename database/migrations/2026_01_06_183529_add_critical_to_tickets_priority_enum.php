<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'critical' to the priority enum
        DB::statement("ALTER TABLE tickets MODIFY COLUMN priority ENUM('low','medium','high','urgent','critical') DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'critical' from the priority enum (only if no tickets use it)
        DB::statement("ALTER TABLE tickets MODIFY COLUMN priority ENUM('low','medium','high','urgent') DEFAULT 'medium'");
    }
};
