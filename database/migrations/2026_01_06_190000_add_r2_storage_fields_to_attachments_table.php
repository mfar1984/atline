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
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('storage_type')->default('local')->after('file_path');
            $table->string('storage_path', 500)->nullable()->after('storage_type');
            $table->text('storage_url')->nullable()->after('storage_path');
        });

        // Make file_path nullable for R2 storage
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn(['storage_type', 'storage_path', 'storage_url']);
        });

        // Restore file_path to non-nullable
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable(false)->change();
        });
    }
};
