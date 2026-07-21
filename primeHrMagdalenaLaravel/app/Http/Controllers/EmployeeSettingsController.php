<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeSettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee record not found.');
        }

        $employee->load(['employmentDetail.designationRelation', 'employmentDetail.departmentRelation', 'contacts']);

        $fullName = trim($employee->first_name . ' ' . $employee->last_name);
        $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
        $contactNumber = optional($employee->contacts->firstWhere('type', 'mobile'))->number;
        $isPermanent = $employee->employmentDetail?->employment_status === 'Permanent';

        return view('employee.settings.employeeSettings', [
            'employee' => $employee,
            'fullName' => $fullName,
            'initials' => $initials,
            'contactNumber' => $contactNumber,
            'isPermanent' => $isPermanent,
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record is linked to this account.'], 404);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $path = $request->file('photo')->storeAs('employees/photos', $filename, 'public');
        $employee->update(['photo' => '/storage/' . $path]);

        return response()->json(['success' => true, 'message' => 'Profile photo updated.', 'photo' => $employee->photo]);
    }
}
