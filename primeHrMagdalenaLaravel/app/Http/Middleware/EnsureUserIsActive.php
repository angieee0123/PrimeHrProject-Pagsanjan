<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account activation gate.
 *
 * The login routes already refuse an inactive account, so this covers the other
 * half: a user who was signed in (web session or mobile token) at the moment an
 * admin deactivated them. Without it their access would survive until the
 * session or token expired. Guests fall through to the route-level `auth`
 * middleware, which handles the login redirect.
 *
 * Applied on the web and api groups, so it also covers routes added later.
 */
class EnsureUserIsActive
{
    private const MESSAGE = 'Your account is inactive. Please contact your administrator to activate it.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isActive()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => self::MESSAGE,
            ], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', self::MESSAGE);
    }
}
