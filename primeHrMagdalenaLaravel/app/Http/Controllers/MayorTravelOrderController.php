<?php

namespace App\Http\Controllers;

use App\Models\TravelOrder;

class MayorTravelOrderController extends Controller
{
    public function index()
    {
        $travelOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'    => $travelOrders->count(),
            'approved' => $travelOrders->where('status', 'approved')->count(),
            'pending'  => $travelOrders->where('status', 'pending')->count(),
            'rejected' => $travelOrders->whereIn('status', ['rejected', 'disapproved'])->count(),
        ];

        return view('mayor.travelOrder.mayorTravelOrder', compact('travelOrders', 'stats'));
    }

    public function show($id)
    {
        $travelOrder = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee', 'histories.performer.employee'])
            ->findOrFail($id);

        return response()->json($travelOrder);
    }
}
