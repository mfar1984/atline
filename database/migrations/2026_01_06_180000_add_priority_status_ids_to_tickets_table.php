<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('priority_id')->nullable()->after('priority')->constrained('ticket_priorities')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->after('status')->constrained('ticket_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['priority_id']);
            $table->dropForeign(['status_id']);
            $table->dropColumn(['priority_id', 'status_id']);
        });
    }
};
