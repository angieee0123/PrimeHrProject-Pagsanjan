<?php

use App\Http\Middleware\EnsureRoleForArea;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render (and most PaaS hosts) terminate TLS at their edge proxy
        // and forward plain HTTP internally, so trust its X-Forwarded-*
        // headers or Laravel generates http:// URLs on an https:// site.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO,
        );

        // Account activation gate: ends access for a user who is deactivated
        // while holding a live session or API token. Runs before the role check
        // so an inactive admin is turned away rather than let into admin/.
        $middleware->web(append: [
            EnsureUserIsActive::class,
            EnsureRoleForArea::class,
        ]);

        $middleware->api(append: [
            EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
