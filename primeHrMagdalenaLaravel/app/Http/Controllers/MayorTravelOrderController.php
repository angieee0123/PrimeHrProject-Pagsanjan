<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use App\Models\Department;

class MayorTravelOrderController extends Controller
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

        $disapprovedOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation', 'approver.employee', 'disapprover.employee', 'companions.employee'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'disapproved_page');

        $departments = Department::where('status', 'Active')->orderBy('name')->get();

        return view('mayor.travelOrder.mayorTravelOrder', compact('pendingOrders', 'approvedOrders', 'disapprovedOrders', 'departments'));
    }

    public function show($id)
    {
        $travelOrder = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee', 'histories.performer.employee'])
            ->findOrFail($id);

        return response()->json($travelOrder);
    }
}
