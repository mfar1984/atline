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
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->after('client_id')->constrained()->nullOnDelete();

            $table->index(['client_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['employee_id']);
            $table->dropIndex(['client_id', 'created_at']);
            $table->dropIndex(['employee_id', 'created_at']);
            $table->dropColumn(['client_id', 'employee_id']);
        });
    }
};
