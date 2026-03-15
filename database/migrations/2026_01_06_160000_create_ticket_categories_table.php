<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // Hex color
            $table->string('icon', 50)->default('category'); // Material icon name
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Update tickets table to use category_id instead of category string
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained('ticket_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        
        Schema::dropIfExists('ticket_categories');
    }
};
