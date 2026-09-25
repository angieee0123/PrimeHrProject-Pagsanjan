<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leave-and-benefits entitlement gate.
 *
 * Leave credits, leave filings and monetization are a plantilla entitlement: a
 * Job Order is paid by the day and earns none, which is why the employee rail
 * has never offered "Leave & Benefits" to one. The rail was the *only* thing
 * keeping them out — the page, the leave POST behind it and the monetization
 * endpoints the page's own tab calls were all reachable by URL, so a Job Order
 * who typed `/employee/leave` read a page of credits they cannot hold and
 * could file against them.
 *
 * Hiding a nav item is tidiness, not a permission. This is the permission, and
 * it reads the same rule the hidden link was built from
 * ({@see \App\Models\EmploymentDetail::hasLeaveAndBenefits()}), so the door and
 * the link cannot disagree about who is entitled.
 *
 * Applied per route rather than by prefix — the rest of the employee area
 * (attendance, payslips, travel orders, pass slips) is theirs to use, and only
 * these routes are the entitlement's.
 */
class EnsureLeaveAndBenefitsEligible
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guests: let the route's `auth` middleware issue the login redirect.
        if (! $user) {
            return $next($request);
        }

        if (! $user->employee?->hasLeaveAndBenefits()) {
            abort(403, 'Leave and benefits are available to plantilla personnel only.');
        }

        return $next($request);
    }
}
