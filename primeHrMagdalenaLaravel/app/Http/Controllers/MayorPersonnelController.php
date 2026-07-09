<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;

class MayorPersonnelController extends Controller
{
    public function index()
    {
        $departments = Department::where('status', 'Active')->orderBy('name')->get();

        $employees = Employee::with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation', 'user'])
            ->orderBy('last_name')
            ->get();

        $stats = [
            'total'     => $employees->count(),
            'active'    => $employees->filter(fn ($e) => $e->user && $e->user->status === 'Active')->count(),
            'inactive'  => $employees->filter(fn ($e) => !$e->user || $e->user->status === 'Inactive')->count(),
            'permanent' => $employees->filter(fn ($e) => $e->employmentDetail && $e->employmentDetail->employment_status === 'Permanent')->count(),
        ];

        $departmentRows = Department::withCount(['employmentDetails as headcount'])
            ->orderBy('name')
            ->get();

        return view('mayor.personnel.mayorPersonnel', compact('departments', 'employees', 'stats', 'departmentRows'));
    }
}
