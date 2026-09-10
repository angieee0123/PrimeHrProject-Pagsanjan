<?php

namespace App\Http\Middleware;

use App\Services\AttendanceKioskService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate on the attendance kiosk's bearer token.
 *
 * This is the only thing standing where `auth` stands on every other write path
 * in the application, so it answers 404 rather than 403 on a bad token. A 403
 * would confirm that a kiosk exists at this address and that the token is the
 * only thing missing, which is a useful fact for someone probing the host; 404
 * says only that there is nothing here. It also matches how a wrong token
 * behaves everywhere else — the route simply is not reachable.
 *
 * Registered as an alias rather than appended to the web group: the group
 * cannot know which routes are the kiosk's, and a gate that applies to
 * everything is a gate somebody will one day add a route behind by accident.
 */
class EnsureKioskToken
{
    public function __construct(
        private readonly AttendanceKioskService $kiosk,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->kiosk->matches($request->route('token'))) {
            abort(404);
        }

        return $next($request);
    }
}
