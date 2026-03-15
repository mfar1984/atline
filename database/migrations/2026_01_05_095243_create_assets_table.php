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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('asset_tag', 50)->unique(); // Asset ID/Tag No
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('status', ['active', 'spare', 'damaged', 'maintenance', 'disposed'])->default('active');
            $table->json('specs')->nullable(); // Dynamic fields based on category
            $table->date('purchase_date')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->string('warranty_period', 50)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');
            $table->string('assigned_to')->nullable();
            $table->string('department')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
