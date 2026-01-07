<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Internal Categories (separate from external)
        Schema::create('internal_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Internal Brands (separate from external)
        Schema::create('internal_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Internal Locations (office locations)
        Schema::create('internal_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Internal Assets (office assets)
        Schema::create('internal_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('internal_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('internal_brands')->nullOnDelete();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('internal_locations')->nullOnDelete();
            $table->enum('status', ['available', 'checked_out', 'maintenance', 'disposed'])->default('available');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Asset Movements (checkout/checkin history)
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_asset_id')->constrained('internal_assets')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('checkout_date');
            $table->date('expected_return_date');
            $table->dateTime('actual_return_date')->nullable();
            $table->enum('checkout_condition', ['excellent', 'good', 'fair', 'poor']);
            $table->enum('return_condition', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->string('purpose');
            $table->enum('status', ['checked_out', 'returned', 'overdue'])->default('checked_out');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('internal_assets');
        Schema::dropIfExists('internal_locations');
        Schema::dropIfExists('internal_brands');
        Schema::dropIfExists('internal_categories');
    }
};
