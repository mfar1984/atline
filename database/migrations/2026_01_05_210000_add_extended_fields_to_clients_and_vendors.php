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
        // Add extended fields to clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('organization_type', ['gov', 'ngo', 'company'])->nullable()->after('name');
            $table->string('address_1')->nullable()->after('organization_type');
            $table->string('address_2')->nullable()->after('address_1');
            $table->string('postcode', 10)->nullable()->after('address_2');
            $table->string('district')->nullable()->after('postcode');
            $table->string('state')->nullable()->after('district');
            $table->string('country')->nullable()->default('Malaysia')->after('state');
            $table->string('website')->nullable()->after('country');
        });

        // Add extended fields to vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('organization_type', ['ngo', 'company'])->nullable()->after('name');
            $table->string('address_1')->nullable()->after('organization_type');
            $table->string('address_2')->nullable()->after('address_1');
            $table->string('postcode', 10)->nullable()->after('address_2');
            $table->string('district')->nullable()->after('postcode');
            $table->string('state')->nullable()->after('district');
            $table->string('country')->nullable()->default('Malaysia')->after('state');
            $table->string('website')->nullable()->after('country');
            // Incharge Info
            $table->string('incharge_name')->nullable()->after('website');
            $table->string('incharge_phone')->nullable()->after('incharge_name');
            $table->string('incharge_whatsapp')->nullable()->after('incharge_phone');
            $table->string('incharge_email')->nullable()->after('incharge_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'organization_type',
                'address_1',
                'address_2',
                'postcode',
                'district',
                'state',
                'country',
                'website',
            ]);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'organization_type',
                'address_1',
                'address_2',
                'postcode',
                'district',
                'state',
                'country',
                'website',
                'incharge_name',
                'incharge_phone',
                'incharge_whatsapp',
                'incharge_email',
            ]);
        });
    }
};
