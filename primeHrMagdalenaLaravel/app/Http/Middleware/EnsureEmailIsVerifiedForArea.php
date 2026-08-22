<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Email verification gate, applied by area rather than per route.
 *
 * Registration mails every new account a verification link, whatever roles it
 * holds. Only the employee dashboard routes were behind the `verified`
 * middleware, though, so the link was advisory for everybody else: an admin,
 * HR officer or the mayor could ignore it forever and still reach their whole
 * area. An address nobody proved they own is also the address password resets
 * go to, so leaving those three roles ungated put the least-verified accounts
 * in the system on the most privileged pages.
 *
 * Gating by prefix instead of by route is what makes that hold. `routes/web.php`
 * declares 170-odd routes each carrying its own `->middleware('auth')`, with no
 * grouping to hang a second name on; adding `verified` to every one of them
 * would leave the gate one forgotten route away from a hole, forever. The areas
 * come from {@see EnsureRoleForArea::AREA_ROLES} rather than a second list here,
 * so a fourth area is gated by both middlewares or by neither.
 *
 * What is deliberately *not* gated is everything outside those prefixes — the
 * welcome page, login, `/select-role`, logout, and `/email/verify*` itself.
 * Gating the verification screens would redirect them to themselves.
 *
 * Existing accounts are unaffected: `2026_08_23_000000_restore_email_verified_at_on_users`
 * backfilled every row that predates verification working at all, so this
 * tightens the rule for accounts created from here on rather than locking out
 * staff who are using the system today.
 */
class EnsureEmailIsVerifiedForArea
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guests: let the route's `auth` middleware issue the login redirect.
        if (! $user instanceof MustVerifyEmail) {
            return $next($request);
        }

        if (! array_key_exists((string) $request->segment(1), EnsureRoleForArea::AREA_ROLES)) {
            return $next($request);
        }

        // Delegate rather than re-implement: the framework middleware decides
        // between a 403 for an API/fetch caller and a redirect to
        // `verification.notice` for a browser, and the admin pages are full of
        // fetch calls that would otherwise be handed a login page as JSON.
        return app(EnsureEmailIsVerified::class)->handle($request, $next);
    }
}
