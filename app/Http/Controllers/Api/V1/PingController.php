<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiToken;
use App\Models\Asset;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Health / handshake endpoint.
 *
 * Used by the "Test Connection" button on the consumer side to confirm the
 * token works and to see how much data it can expect, before running a sync.
 */
class PingController extends BaseApiController
{
    /** GET /api/v1/ping */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');

        return response()->json([
            'success' => true,
            'message' => 'External API reachable.',
            'app'     => config('app.name'),
            'api_version' => 'v1',
            'token'   => $token ? [
                'name'      => $token->name,
                'abilities' => $token->abilities,
                'expires_at' => $this->iso($token->expires_at),
            ] : null,
            'counts' => [
                'organizations' => Organization::count(),
                'projects'      => Project::count(),
                'assets'        => Asset::count(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
