<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TravelOrder;
use App\Models\TravelOrderCompanion;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;

class EmployeeTravelOrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            $travelOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
            $companionOptions = collect();
            $companionInvitations = collect();
            return view('employee.travelOrder.employeeTravelOrder', compact('travelOrders', 'companionOptions', 'companionInvitations'));
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $perPage = request('per_page', 10);
        $travelOrders = TravelOrder::where('employee_id', $employee->id)
            ->with('companions.employee')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Employees selectable as travel companions (must have a user account to respond)
        $companionOptions = Employee::where('id', '!=', $employee->id)
            ->whereHas('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'employee_id', 'first_name', 'last_name', 'photo']);

        // Travel orders where this employee is invited as a companion
        $companionInvitations = TravelOrderCompanion::where('employee_id', $employee->id)
            ->with(['travelOrder.employee', 'travelOrder.companions.employee'])
            ->whereHas('travelOrder', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('employee.travelOrder.employeeTravelOrder', compact('employee', 'travelOrders', 'companionOptions', 'companionInvitations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return back()->with('error', 'No employee record found.');
        }

        $data = $request->validate([
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:300',
            'travel_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:travel_date',
            'duration' => 'required|integer|min:1',
            'transportation_mode' => 'nullable|string|max:100',
            'estimated_budget' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'companions' => 'nullable|array',
            'companions.*' => 'integer|exists:employees,id',
        ]);

        // Companions must be other employees with user accounts (so they can respond)
        $companionIds = collect($data['companions'] ?? [])
            ->unique()
            ->reject(fn ($id) => (int) $id === $employee->id)
            ->filter(fn ($id) => Employee::where('id', $id)->whereHas('user')->exists())
            ->values();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('travel_orders', 'public');
        }

        $travelOrder = DB::transaction(function () use ($data, $employee, $attachmentPath, $companionIds) {
            // With companions the order waits for their responses before it can be
            // forwarded to HR; without companions it goes straight to HR as pending.
            $travelOrder = TravelOrder::create([
                'employee_id' => $employee->id,
                'destination' => $data['destination'],
                'purpose' => $data['purpose'],
                'travel_date' => $data['travel_date'],
                'return_date' => $data['return_date'],
                'duration' => $data['duration'],
                'transportation_mode' => $data['transportation_mode'] ?? null,
                'estimated_budget' => $data['estimated_budget'] ?? null,
                'attachment' => $attachmentPath,
                'status' => $companionIds->isNotEmpty() ? 'awaiting_companions' : 'pending',
                'filed_by' => Auth::id(),
            ]);

            $travelOrder->logHistory('filed', 'Travel order filed by ' . $employee->first_name . ' ' . $employee->last_name . '.');

            foreach ($companionIds as $companionId) {
                $companion = $travelOrder->companions()->create([
                    'employee_id' => $companionId,
                    'status' => 'pending',
                ]);
                $companionEmployee = $companion->employee;
                $travelOrder->logHistory('companion_invited', $companionEmployee->first_name . ' ' . $companionEmployee->last_name . ' was invited as a companion.');
            }

            return $travelOrder;
        });

        foreach ($travelOrder->companions as $companion) {
            NotificationService::travelOrderCompanionInvited($travelOrder, $companion);
        }

        if ($companionIds->isEmpty()) {
            // No companions to wait for — it is already with HR
            $travelOrder->logHistory('forwarded_to_hr', 'Travel order submitted to HR/Admin for approval.');
            NotificationService::travelOrderForwarded($travelOrder);
            $message = 'Travel order submitted successfully.';
        } else {
            $message = 'Travel order filed. Your companions have been notified — once all of them respond, you can forward it to HR.';
        }

        return redirect()->route('employee.travelorder')->with('success', $message);
    }

    public function companionResponse(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.travelorder')->with('error', 'No employee record found.');
        }

        $data = $request->validate([
            'response' => 'required|in:accepted,rejected',
            'response_note' => 'nullable|string|max:300',
        ]);

        $companion = TravelOrderCompanion::where('travel_order_id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if ($companion->status !== 'pending') {
            return redirect()->route('employee.travelorder')->with('error', 'You have already responded to this companion request.');
        }

        $travelOrder = $companion->travelOrder;

        if ($travelOrder->status !== 'awaiting_companions') {
            return redirect()->route('employee.travelorder')->with('error', 'This travel order is no longer accepting companion responses.');
        }

        $companion->update([
            'status' => $data['response'],
            'response_note' => $data['response_note'] ?? null,
            'responded_at' => now(),
        ]);

        $travelOrder->logHistory(
            'companion_' . $data['response'],
            $employee->first_name . ' ' . $employee->last_name . ' ' . $data['response'] . ' the companion request.'
                . (!empty($data['response_note']) ? ' Note: ' . $data['response_note'] : '')
        );

        NotificationService::travelOrderCompanionResponded($travelOrder, $companion->fresh('employee'));

        return redirect()->route('employee.travelorder')->with('success', 'You have ' . $data['response'] . ' the companion request.');
    }

    public function forward($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.travelorder')->with('error', 'No employee record found.');
        }

        $travelOrder = TravelOrder::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if ($travelOrder->status !== 'awaiting_companions') {
            return redirect()->route('employee.travelorder')->with('error', 'Only travel orders awaiting companion responses can be forwarded.');
        }

        if (!$travelOrder->allCompanionsResponded()) {
            return redirect()->route('employee.travelorder')->with('error', 'All companions must accept or reject the request before you can forward it to HR.');
        }

        $travelOrder->update(['status' => 'pending']);
        $travelOrder->logHistory('forwarded_to_hr', 'Travel order forwarded to HR/Admin for approval by ' . $employee->first_name . ' ' . $employee->last_name . '.');

        NotificationService::travelOrderForwarded($travelOrder);

        return redirect()->route('employee.travelorder')->with('success', 'Travel order forwarded to HR for approval.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            abort(403, 'No employee record found.');
        }

        // The filer or an invited companion may view the travel order
        $travelOrder = TravelOrder::where('id', $id)
            ->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                  ->orWhereHas('companions', fn ($c) => $c->where('employee_id', $employee->id));
            })
            ->with(['approver', 'employee', 'companions.employee', 'histories.performer.employee'])
            ->firstOrFail();

        return response()->json($travelOrder);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.travelorder')->with('error', 'No employee record found.');
        }

        $travelOrder = TravelOrder::where('id', $id)
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'awaiting_companions'])
            ->firstOrFail();

        // Let invited companions know the trip was called off before deleting
        foreach ($travelOrder->companions()->with('employee.user')->get() as $companion) {
            if ($companion->employee && $companion->employee->user) {
                $notificationData = [
                    'user_id' => $companion->employee->user->id,
                    'type' => 'travel_order',
                    'title' => 'Travel Order Cancelled',
                    'message' => "Travel order {$travelOrder->order_number} to {$travelOrder->destination}, where you were invited as a companion, has been cancelled by the filer.",
                    'link' => route('employee.travelorder'),
                ];

                if (Notification::hasAudienceColumn()) {
                    $notificationData['audience'] = 'employee';
                }

                Notification::create($notificationData);
            }
        }

        if ($travelOrder->attachment) {
            Storage::disk('public')->delete($travelOrder->attachment);
        }

        $travelOrder->delete();

        return redirect()->route('employee.travelorder')->with('success', 'Travel order cancelled successfully.');
    }
}
