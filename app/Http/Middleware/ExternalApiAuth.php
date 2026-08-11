<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\IntegrationSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the External read-only API.
 *
 * Four checks, in this order, cheapest first:
 *   1. The integration is switched on in Settings › Integrations › API
 *   2. The caller IP is on the allowlist (blank allowlist = any IP)
 *   3. A valid, unrevoked, unexpired bearer token with the required ability
 *   4. The caller is within its per-minute request budget
 *
 * The resolved token is attached to the request as `api_token` so controllers
 * and logging can see which integration made the call.
 */
class ExternalApiAuth
{
    public function handle(Request $request, Closure $next, string $ability = ApiToken::ABILITY_READ): Response
    {
        $setting  = IntegrationSetting::getOrCreateByType(IntegrationSetting::TYPE_API);
        $settings = $setting->settings ?? [];

        // ── 1. Switched on? ──
        if (!$setting->is_active) {
            return $this->deny('The external API is disabled.', 503);
        }

        // ── 2. IP allowlist ──
        $allowlist = $this->parseList($settings['ip_allowlist'] ?? '');
        if (!empty($allowlist) && !in_array($request->ip(), $allowlist, true)) {
            return $this->deny('Your IP address is not allowed to use this API.', 403);
        }

        // ── 3. Bearer token ──
        $plain = $request->bearerToken();
        if (!$plain) {
            return $this->deny('Missing bearer token.', 401);
        }

        $token = ApiToken::findValid($plain);
        if (!$token) {
            return $this->deny('Invalid, expired or revoked token.', 401);
        }

        if (!$token->hasAbility($ability)) {
            return $this->deny("This token does not have the '{$ability}' ability.", 403);
        }

        // ── 4. Rate limit, per token per minute ──
        $limit = (int) ($settings['rate_limit'] ?? 120);
        if ($limit > 0) {
            $key = "extapi:rl:{$token->id}:" . now()->format('YmdHi');
            $hits = (int) Cache::get($key, 0) + 1;
            // Keep the counter slightly longer than the window it belongs to.
            Cache::put($key, $hits, now()->addSeconds(90));

            if ($hits > $limit) {
                return $this->deny("Rate limit exceeded ({$limit} requests/minute).", 429)
                    ->header('Retry-After', (string) (60 - (int) now()->format('s')));
            }
        }

        $token->touchUsage($request->ip());
        $request->attributes->set('api_token', $token);

        return $next($request);
    }

    /** @return array<int,string> */
    private function parseList(string $raw): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw) ?: [])));
    }

    private function deny(string $message, int $status): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
