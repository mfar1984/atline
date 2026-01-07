<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the status enum to include 'on_hold'
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'pending', 'on_hold', 'resolved', 'closed') DEFAULT 'open'");
    }

    public function down(): void
    {
        // Revert back to original enum (convert any 'on_hold' to 'pending' first)
        DB::statement("UPDATE tickets SET status = 'pending' WHERE status = 'on_hold'");
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'pending', 'resolved', 'closed') DEFAULT 'open'");
    }
};
