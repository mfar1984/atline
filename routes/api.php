<?php

use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\PingController;
use App\Http\Controllers\Api\V1\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| External Read-Only API — v1
|--------------------------------------------------------------------------
|
| Lets approved systems (currently the ATLINE backend) read External Asset
| Management data. Configured under Settings › Integrations › API.
|
| GET ONLY, deliberately. There is no POST, PUT, PATCH or DELETE anywhere in
| this file, so a consumer cannot modify atlinehelp records even if its token
| leaks. atlinehelp stays the master record.
|
| Auth: Authorization: Bearer <token>   (see App\Http\Middleware\ExternalApiAuth)
|
*/

Route::prefix('v1')->name('api.v1.')->middleware('external.api')->group(function () {

    Route::get('ping', PingController::class)->name('ping');

    // Customers
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/{id}', [OrganizationController::class, 'show'])
        ->whereNumber('id')->name('organizations.show');

    // Projects
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{id}', [ProjectController::class, 'show'])
        ->whereNumber('id')->name('projects.show');

    // Assets — the external inventory. `lookup` must be declared before `{id}`
    // or "lookup" would be captured as an id.
    Route::get('assets/lookup', [AssetController::class, 'lookup'])->name('assets.lookup');
    Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('assets/{id}', [AssetController::class, 'show'])
        ->whereNumber('id')->name('assets.show');

    // Reference lists (categories, brands, locations, vendors)
    Route::get('lookups', [LookupController::class, 'index'])->name('lookups.index');
});

// Anything else under /api gets a JSON 404 rather than an HTML error page.
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Unknown API endpoint.',
    ], 404);
});
