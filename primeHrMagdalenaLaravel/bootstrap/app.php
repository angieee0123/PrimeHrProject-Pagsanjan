<?php

use App\Http\Middleware\EnsureRoleForArea;
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

        // Area-based authorization: blocks authenticated users from entering an
        // admin/ mayor/ employee/ URL area their roles don't permit. Runs on
        // every web route (including ones added later) after the session loads.
        $middleware->web(append: [
            EnsureRoleForArea::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
