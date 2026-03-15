<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert system_name setting if not exists
        DB::table('system_settings')->insertOrIgnore([
            'group' => 'company',
            'key' => 'system_name',
            'value' => 'Atline Administration System',
            'type' => 'string',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('group', 'company')
            ->where('key', 'system_name')
            ->delete();
    }
};
