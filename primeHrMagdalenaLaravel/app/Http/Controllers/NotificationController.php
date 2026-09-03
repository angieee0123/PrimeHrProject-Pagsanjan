<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureRoleForArea;
use App\Models\EmployeeRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Every notification endpoint.
 *
 * The one rule that governs the whole class: **a notification is only ever
 * reachable by the account it was written for.** Every query below starts from
 * `where('user_id', Auth::id())`, and the `audience` a caller supplies can only
 * narrow that set further — it can never widen it, name another user, or select
 * a different person's bell. An id typed into the URL that belongs to somebody
 * else is a 404, not a permission error, because "no such notification" is the
 * truthful answer from where the caller stands.
 */
class NotificationController extends Controller
{
    /**
     * Which bell an area's pages show. The URL prefix decides, not the client:
     * a request cannot ask for a bell whose area it is not allowed into,
     * because EnsureRoleForArea has already refused it at the prefix.
     */
    public const AREA_AUDIENCE = [
        'admin'    => 'admin',
        'mayor'    => 'mayor',
        'employee' => 'employee',
    ];

    /** JSON list of the caller's notifications. Legacy endpoint, kept in use. */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->newestFirst()
            ->paginate(config('notifications.history_per_page', 20));

        return response()->json($notifications);
    }

    /**
     * The notification panel's live feed.
     *
     * Returns the same rows the panel was server-rendered from, re-rendered
     * through the same partial, so a polling refresh cannot drift from first
     * paint. Scoped to the caller's own rows; `audience` only chooses between
     * the three panels, it can never widen who the notifications belong to.
     */
    public function feed(Request $request)
    {
        $audience = $this->audience($request->query('audience'));
        $limit = (int) config('notifications.panel_limit', 8);

        $notifications = Notification::where('user_id', Auth::id())
            ->forAudience($audience)
            ->recent($limit)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->forAudience($audience)
            ->unread()
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'html' => view('partials.notificationItems', [
                'notifications' => $notifications,
            ])->render(),
        ]);
    }

    /** Badge count only — the cheap poll, for pages that show no list. */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', Auth::id())
            ->forAudience($this->audience($request->query('audience')))
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Full notification history for one area, paginated and filterable.
     *
     * `$area` comes from the route's own defaults, never from the request, so
     * the page cannot be asked to render another area's bell. The area's role
     * gate has already run as middleware by the time this method is reached.
     */
    public function history(Request $request, string $area = 'employee')
    {
        $audience = self::AREA_AUDIENCE[$area] ?? 'employee';

        $state = in_array($request->query('state'), ['unread', 'read'], true)
            ? $request->query('state')
            : 'all';

        $type = array_key_exists((string) $request->query('type'), Notification::CATEGORIES)
            ? $request->query('type')
            : null;

        $query = Notification::where('user_id', Auth::id())->forAudience($audience);

        if ($state === 'unread') {
            $query->unread();
        } elseif ($state === 'read') {
            $query->read();
        }

        if ($type) {
            $query->where('type', $type);
        }

        $notifications = $query->newestFirst()
            ->paginate(config('notifications.history_per_page', 20))
            ->withQueryString();

        // Counts describe the whole area, not the filtered page: a reader has
        // to be able to tell "no unread notifications" from "none matching
        // this filter".
        $base = fn () => Notification::where('user_id', Auth::id())->forAudience($audience);

        return view('notifications.index', [
            'area'          => $area,
            'audience'      => $audience,
            'layout'        => $this->layoutFor($area),
            'notifications' => $notifications,
            'state'         => $state,
            'activeType'    => $type,
            'totalCount'    => $base()->count(),
            'unreadCount'   => $base()->unread()->count(),
            'typeCounts'    => $base()->selectRaw('type, COUNT(*) as total')
                                ->groupBy('type')
                                ->pluck('total', 'type')
                                ->all(),
        ]);
    }

    /**
     * Open a notification: mark it read, then go where it points.
     *
     * This exists so the *server* decides where a click lands. The panel used
     * to mark the row read and then navigate to a URL it had read out of a DOM
     * attribute, which meant the destination was whatever the page said it was.
     * Here the link is re-read from the row, and {@see safeRedirect()} refuses
     * anything off-site or in an area this account may not enter — a
     * notification must never be a way around the area gates.
     */
    public function open($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return $this->safeRedirect($notification->link);
    }

    /** Mark one of the caller's own notifications read. */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true, 'is_read' => true]);
    }

    /** Put one back in the unread pile. */
    public function markAsUnread($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsUnread();

        return response()->json(['success' => true, 'is_read' => false]);
    }

    /**
     * Delete one of the caller's notifications.
     *
     * Safe to offer because the notification is not the record: the leave
     * application, the travel order and the audit log all outlive it.
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear one bell, not all of them.
     *
     * The audience the panel posts narrows within the caller's own rows,
     * exactly as feed() does. An unrecognised value falls to null (clear
     * everything) rather than to the narrower panel: narrowing an unknown value
     * shows a *reader* less, which is safe, but silently narrowing a *write*
     * would leave a badge stuck at a count nothing can reset.
     */
    public function markAllAsRead(Request $request)
    {
        $audience = in_array($request->input('audience'), ['admin', 'employee', 'mayor'], true)
            ? $request->input('audience')
            : null;

        $marked = NotificationService::markAllAsRead(Auth::id(), $audience);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'marked' => $marked]);
        }

        return back();
    }

    /* ===================================================================== */
    /*  Helpers                                                              */
    /* ===================================================================== */

    /**
     * Resolve a requested audience against what the caller may actually see.
     *
     * A client asking for the admin bell without an admin or HR role gets the
     * employee one — the request is not refused, it is answered with the bell
     * that account really has. Without this check the audience parameter would
     * be a way for any signed-in account to read the admin queue out of its own
     * rows; that only matters for an account whose roles have since been
     * narrowed, which is exactly the case worth closing.
     */
    protected function audience(?string $requested): string
    {
        $user = Auth::user();

        return match ($requested) {
            'admin' => $user && $user->hasAnyRole(['admin', 'hr']) ? 'admin' : 'employee',
            'mayor' => $user && $user->hasRole('mayor') ? 'mayor' : 'employee',
            default => 'employee',
        };
    }

    protected function layoutFor(string $area): string
    {
        return match ($area) {
            'admin' => 'layouts.app',
            'mayor' => 'layouts.mayor',
            default => 'layouts.employee',
        };
    }

    /**
     * Follow a stored link only if it is ours and this account may enter it.
     *
     * Three refusals, in order: a link that is not a same-origin path (nothing
     * writes one, and an absolute URL in a redirect is an open redirect waiting
     * for a writer that does); a link into an area whose roles this account
     * lacks, which would end at the area gate's 403 with the notification
     * already marked read; and no link at all. Each falls back to the account's
     * own dashboard.
     */
    protected function safeRedirect(?string $link)
    {
        $fallback = $this->homeUrl();

        // A notification with nothing to open — a broadcast announcement, say —
        // is still worth marking read, but there is nowhere to go. Sending the
        // reader to a dashboard they did not ask for loses the page they were
        // on, so stay where they were.
        if (! $link) {
            return redirect()->back(302, [], $fallback);
        }

        $parts = parse_url($link);

        if ($parts === false) {
            return redirect($fallback);
        }

        // Absolute links are accepted only when they point back at this app.
        if (isset($parts['host']) && $parts['host'] !== parse_url(config('app.url'), PHP_URL_HOST)
            && $parts['host'] !== request()->getHost()) {
            return redirect($fallback);
        }

        $path = trim($parts['path'] ?? '', '/');
        $segment = explode('/', $path)[0] ?? '';
        $user = Auth::user();

        if (isset(EnsureRoleForArea::AREA_ROLES[$segment])
            && (! $user || ! $user->hasAnyRole(EnsureRoleForArea::AREA_ROLES[$segment]))) {
            return redirect($fallback);
        }

        $target = '/' . $path;

        if (! empty($parts['query'])) {
            $target .= '?' . $parts['query'];
        }

        return redirect($target);
    }

    /** Where a click goes when its link cannot be followed. */
    protected function homeUrl(): string
    {
        $user = Auth::user();
        $route = $user ? ($user->dashboardRoutes()[0] ?? null) : null;

        return NotificationService::link($route ?? 'employee.dashboard') ?? '/';
    }

    /* ===================================================================== */
    /*  Employee requests                                                    */
    /* ===================================================================== */

    // Submit employee request (for permanent employees)
    public function submitRequest(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:payslip,deduction_inquiry,leave_balance,attendance_correction,certificate,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        $employeeRequest = EmployeeRequest::create([
            'employee_id' => $employee->id,
            'request_type' => $validated['request_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        // Send notification to admins. Two request types have a writer of their
        // own that names what was asked for ("requested a payslip", "has a
        // question about deductions"); the rest fall to the generic one. They
        // are alternatives, never both — a request must not arrive twice.
        match ($employeeRequest->request_type) {
            'payslip' => NotificationService::payslipRequested($employeeRequest),
            'deduction_inquiry' => NotificationService::deductionInquiry($employeeRequest),
            default => NotificationService::employeeRequestSubmitted($employeeRequest),
        };

        return response()->json([
            'success' => true,
            'message' => 'Request submitted successfully',
            'request' => $employeeRequest
        ]);
    }

    // Get employee's own requests
    public function myRequests()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['error' => 'Employee record not found'], 404);
        }

        $requests = EmployeeRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    // Admin: Get all requests
    public function allRequests()
    {
        $requests = EmployeeRequest::with(['employee', 'processedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    // Admin: Update request status
    public function updateRequestStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected',
            'admin_response' => 'nullable|string',
        ]);

        $employeeRequest = EmployeeRequest::findOrFail($id);

        $employeeRequest->update([
            'status' => $validated['status'],
            'admin_response' => $validated['admin_response'] ?? null,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        // Notify employee
        NotificationService::requestStatusChanged($employeeRequest, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully',
            'request' => $employeeRequest
        ]);
    }
}
