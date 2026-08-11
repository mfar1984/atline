<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contract / Reference number for a project, e.g. QT240000000024647.
 *
 * This is the number the customer knows the engagement by, and it is printed on
 * every preventive-maintenance service form. It is deliberately separate from
 * `po_number`: a PO is a purchasing document raised per order, while the
 * contract reference identifies the agreement the work is carried out under, and
 * the two do not always match.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('contract_reference', 120)
                ->nullable()
                ->after('organization_id')
                ->comment('Contract / Reference No. printed on service forms');

            // Consumers look projects up by this reference, and the external API
            // exposes it, so it is worth indexing. Not unique: older projects
            // share a reference until the data is cleaned up.
            $table->index('contract_reference', 'projects_contract_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_contract_reference_index');
            $table->dropColumn('contract_reference');
        });
    }
};
