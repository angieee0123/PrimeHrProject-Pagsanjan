<?php

namespace App\Http\Controllers;

use App\Models\PassSlip;

class MayorPassSlipController extends Controller
{
    public function index()
    {
        $passSlips = PassSlip::with(['employee.employmentDetail.departmentRelation'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total'    => $passSlips->count(),
            'approved' => $passSlips->where('status', 'approved')->count(),
            'pending'  => $passSlips->where('status', 'pending')->count(),
            'rejected' => $passSlips->where('status', 'rejected')->count(),
        ];

        return view('mayor.passSlip.mayorPassSlip', compact('passSlips', 'stats'));
    }
}
