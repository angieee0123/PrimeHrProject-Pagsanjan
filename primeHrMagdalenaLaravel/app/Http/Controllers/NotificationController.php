<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\EmployeeRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Get notifications for current user
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($notifications);
    }

    /**
     * The notification panel's live feed. Returns the same rows the panel was
     * server-rendered from, re-rendered through the same partial, so a polling
     * refresh cannot drift from first paint. Scoped to the caller's own rows;
     * `audience` only chooses between the admin and employee panels, it can
     * never widen who the notifications belong to.
     */
    public function feed(Request $request)
    {
        $audience = $request->query('audience') === 'admin' ? 'admin' : 'employee';

        $notifications = Notification::where('user_id', Auth::id())
            ->forAudience($audience)
            ->recent(10)
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

    // Get unread count
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Clear one panel's bell, not both. The audience the panel posts narrows
     * within the caller's own rows, exactly as feed() does — it can never widen
     * them. An unrecognised value falls to null (clear everything) rather than to
     * the narrower panel the way feed() does: narrowing an unknown value shows a
     * reader less, which is safe, but silently narrowing a *write* would leave a
     * bell stuck at a count nothing can reset.
     */
    public function markAllAsRead(Request $request)
    {
        $audience = in_array($request->input('audience'), ['admin', 'employee'], true)
            ? $request->input('audience')
            : null;

        NotificationService::markAllAsRead(Auth::id(), $audience);

        return response()->json(['success' => true]);
    }

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
