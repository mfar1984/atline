<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Site coordinates for an organization.
 *
 * These are the anchor point for geofencing in the field app: a signature
 * captured on site records its own GPS position, and the distance between the
 * two is what decides whether the technician was actually there.
 *
 * DECIMAL(10,7) rather than a float. Seven decimal places is roughly 11mm at the
 * equator — far more than needed — but the point is that decimal is exact.
 * Storing a coordinate as a float introduces rounding that would shift a
 * boundary by a few metres, and a geofence decision that a customer may later
 * dispute must not depend on binary floating-point representation.
 *
 * Nullable on purpose. Most organizations were created long before geofencing
 * existed, and forcing a coordinate would have meant inventing one. The backend
 * records `geofence_status = 'no_site_coords'` for those rather than silently
 * reporting the technician as on site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)
                ->nullable()
                ->after('country')
                ->comment('Site latitude, anchor for field-app geofencing');

            $table->decimal('longitude', 10, 7)
                ->nullable()
                ->after('latitude')
                ->comment('Site longitude, anchor for field-app geofencing');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
