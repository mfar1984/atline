<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission'   => \App\Http\Middleware\CheckPermission::class,
            // Gate for the External read-only API: enabled toggle, IP allowlist,
            // bearer token and per-minute rate limit.
            'external.api' => \App\Http\Middleware\ExternalApiAuth::class,
        ]);
        
        // Trust all proxies for ngrok
        $middleware->trustProxies(at: '*');
        
        // Exclude webhook endpoint from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
