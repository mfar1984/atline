<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Client self-registration with administrator approval.
 *
 * ── Why a new column instead of reusing is_active ──
 *
 * is_active already means "an administrator switched this account off", and
 * LoginController tells the user exactly that: "Your account has been
 * deactivated. Please contact administrator." A registrant waiting to be approved
 * has never been active, so that message is wrong, and an administrator looking at
 * the user list could not tell a pending signup from a suspended employee. Two
 * different states need two different columns.
 *
 * ── Why the default is 'approved' ──
 *
 * Every row that already exists belongs to somebody who can log in today. A
 * default of 'pending' would lock out every user in the system the moment this
 * migration ran, including the administrator who would need to approve them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('approved')
                ->after('is_active');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            // Who decided. nullOnDelete rather than cascade: losing the approver's
            // account must not delete the approved user along with it.
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
            // Shown back to the applicant on their next login attempt, so a
            // rejection is not a silent dead end.
            $table->text('rejection_reason')->nullable()->after('approved_by');
        });

        /*
         * Make sure the Client role exists.
         *
         * Nothing in this repository creates it — no RoleSeeder, no migration —
         * yet three places depend on the exact string 'Client':
         * User::isClientUser(), CheckPermission::isClientUserWithImplicitAccess()
         * and the ticket notification routing. Without the row, approving a
         * registrant would leave them with role_id = null, and CheckPermission
         * would then refuse every screen: they would log in successfully to an
         * application with nothing in it.
         *
         * Created with an empty permissions array on purpose. CheckPermission
         * grants the Client role implicit access to helpdesk view/create
         * regardless of what its permissions list holds, so an empty array is the
         * least privilege that still works.
         */
        $exists = DB::table('roles')->where('name', 'Client')->exists();
        if (! $exists) {
            DB::table('roles')->insert([
                'name' => 'Client',
                'description' => 'External client. Implicit access to the helpdesk; project visibility comes from the project_user pivot.',
                'permissions' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_at', 'approved_by', 'rejection_reason']);
        });

        // The Client role is deliberately left in place. By the time this is
        // rolled back, users may be pointing at it.
    }
};
