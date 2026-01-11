<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            // Drop the incorrect foreign key
            $table->dropForeign(['employee_id']);
            
            // Add the correct foreign key to employees table
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            // Drop the correct foreign key
            $table->dropForeign(['employee_id']);
            
            // Restore the original (incorrect) foreign key to users table
            $table->foreign('employee_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
