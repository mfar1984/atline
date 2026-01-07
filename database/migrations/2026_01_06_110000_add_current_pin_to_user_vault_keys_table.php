<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_vault_keys', function (Blueprint $table) {
            $table->string('current_pin', 8)->nullable()->after('pin_salt');
        });
    }

    public function down(): void
    {
        Schema::table('user_vault_keys', function (Blueprint $table) {
            $table->dropColumn('current_pin');
        });
    }
};
