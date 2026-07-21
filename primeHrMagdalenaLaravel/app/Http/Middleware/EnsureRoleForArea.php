<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Area-based authorization.
 *
 * Routes are namespaced by URL prefix (admin/, mayor/, employee/). This
 * middleware maps each prefix to the roles allowed to enter that area and
 * blocks authenticated users who lack them. Guests are intentionally left to
 * the route-level `auth` middleware, which handles the login redirect — here
 * we only guard the authenticated-but-wrong-role case (e.g. a plain employee
 * navigating to /admin/payroll).
 *
 * Applied once on the web group, so it also covers any routes added later.
 */
class EnsureRoleForArea
{
    /**
     * Path prefix => roles permitted in that area.
     *
     * @var array<string, array<int, string>>
     */
    private const AREA_ROLES = [
        'admin'    => ['admin', 'hr'],
        'mayor'    => ['mayor'],
        'employee' => ['employee'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guests: let the route's `auth` middleware issue the login redirect.
        if (! $user) {
            return $next($request);
        }

        $segment = $request->segment(1); // first path segment, e.g. "admin"

        if (isset(self::AREA_ROLES[$segment]) && ! $user->hasAnyRole(self::AREA_ROLES[$segment])) {
            abort(403, 'You do not have access to this area.');
        }

        return $next($request);
    }
}
