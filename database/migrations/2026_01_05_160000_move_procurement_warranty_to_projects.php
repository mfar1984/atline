<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add procurement & warranty fields to projects table (without vendor_id - vendor stays with assets)
        Schema::table('projects', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('status');
            $table->string('po_number', 100)->nullable()->after('purchase_date');
            $table->string('warranty_period', 50)->nullable()->after('po_number');
            $table->date('warranty_expiry')->nullable()->after('warranty_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fields from projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'po_number', 'warranty_period', 'warranty_expiry']);
        });
    }
};
