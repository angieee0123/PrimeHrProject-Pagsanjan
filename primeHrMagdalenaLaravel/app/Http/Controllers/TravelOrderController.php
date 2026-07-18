<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TravelOrder;
use App\Models\Department;
use App\Services\NotificationService;

class TravelOrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $pendingOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'companions.employee'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'pending_page');

        $approvedOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->paginate($perPage, ['*'], 'approved_page');

        $disapprovedOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'disapproved_page');

        $departments = Department::where('status', 'Active')->orderBy('name')->get();

        return view('admin.travelOrder.travelOrder', compact('pendingOrders', 'approvedOrders', 'disapprovedOrders', 'departments'));
    }

    public function approve($id)
    {
        $travelOrder = TravelOrder::findOrFail($id);

        $travelOrder->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => null,
        ]);

        $travelOrder->logHistory('approved', 'Travel order approved by HR/Admin.');
        NotificationService::travelOrderStatusChanged($travelOrder, 'approved');

        return redirect()->route('admin.travelorder', ['tab' => 'approved'])
            ->with('success', 'Travel order approved successfully.');
    }

    public function disapprove(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $travelOrder = TravelOrder::findOrFail($id);

        $travelOrder->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $request->reason,
        ]);

        $travelOrder->logHistory('disapproved', 'Travel order disapproved by HR/Admin. Reason: ' . $request->reason);
        NotificationService::travelOrderStatusChanged($travelOrder, 'disapproved');

        return redirect()->route('admin.travelorder', ['tab' => 'disapproved'])
            ->with('success', 'Travel order disapproved.');
    }

    public function show($id)
    {
        $travelOrder = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee', 'histories.performer.employee'])
            ->findOrFail($id);

        return response()->json($travelOrder);
    }
}
