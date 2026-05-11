<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;

class LeaveController extends Controller
{
    public function index()
    {
        // Fetch leave types from database with pagination
        $leaveTypes = LeaveType::where('is_active', true)
            ->orderBy('leave_code')
            ->paginate(10); // 10 items per page

        // Sample leave requests data (you can create a table for this later)
        $leaveRequests = [
            ['id' => 'LV-2025-001', 'empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'type' => 'Vacation Leave', 'from' => 'Jun 10, 2025', 'to' => 'Jun 12, 2025', 'days' => 3, 'reason' => 'Family vacation', 'status' => 'Approved'],
            ['id' => 'LV-2025-002', 'empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'type' => 'Sick Leave', 'from' => 'Jun 15, 2025', 'to' => 'Jun 16, 2025', 'days' => 2, 'reason' => 'Medical consultation', 'status' => 'Approved'],
            ['id' => 'LV-2025-003', 'empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'type' => 'Sick Leave', 'from' => 'Jun 20, 2025', 'to' => 'Jun 22, 2025', 'days' => 3, 'reason' => 'Flu and fever', 'status' => 'Pending'],
            ['id' => 'LV-2025-004', 'empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'type' => 'Emergency Leave', 'from' => 'Jun 18, 2025', 'to' => 'Jun 18, 2025', 'days' => 1, 'reason' => 'Family emergency', 'status' => 'Approved'],
            ['id' => 'LV-2025-005', 'empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'type' => 'Vacation Leave', 'from' => 'Jul 1, 2025', 'to' => 'Jul 3, 2025', 'days' => 3, 'reason' => 'Rest and recreation', 'status' => 'Pending'],
            ['id' => 'LV-2025-006', 'empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'type' => 'Vacation Leave', 'from' => 'Jun 25, 2025', 'to' => 'Jun 25, 2025', 'days' => 1, 'reason' => 'Personal errand', 'status' => 'Rejected'],
        ];

        // Sample benefits data (you can create a table for this later)
        $benefitsData = [
            ['empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'gsis' => '₱3,794', 'philhealth' => '₱1,050', 'pagibig' => '₱100', 'vlBalance' => 15, 'slBalance' => 15],
            ['empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'gsis' => '₱3,428', 'philhealth' => '₱950', 'pagibig' => '₱100', 'vlBalance' => 12, 'slBalance' => 13],
            ['empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'gsis' => '₱3,046', 'philhealth' => '₱850', 'pagibig' => '₱100', 'vlBalance' => 13, 'slBalance' => 11],
            ['empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'gsis' => '₱4,253', 'philhealth' => '₱1,150', 'pagibig' => '₱100', 'vlBalance' => 10, 'slBalance' => 9],
            ['empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'gsis' => '₱3,159', 'philhealth' => '₱875', 'pagibig' => '₱100', 'vlBalance' => 14, 'slBalance' => 14],
            ['empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'gsis' => '₱2,748', 'philhealth' => '₱775', 'pagibig' => '₱100', 'vlBalance' => 8, 'slBalance' => 10],
        ];

        return view('admin.leaveAndBenefits.adminLeaveAndBenefits', compact('leaveTypes', 'leaveRequests', 'benefitsData'));
    }

    public function storeLeaveType(Request $request)
    {
        $validated = $request->validate([
            'leave_code' => 'required|string|max:10|unique:leave_types_config,leave_code',
            'leave_name' => 'required|string|max:100',
            'annual_limit' => 'required|numeric|min:0',
            'is_accrued' => 'boolean',
            'is_cumulative' => 'boolean',
            'requires_6_months' => 'boolean',
            'is_monetizable' => 'boolean',
            'requires_attachment' => 'boolean',
            'attachment_info' => 'nullable|string',
            'is_active' => 'required|boolean',
            'document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leave_types_documents', 'public');
        }

        LeaveType::create([
            'leave_code' => strtoupper($validated['leave_code']),
            'leave_name' => $validated['leave_name'],
            'annual_limit' => $validated['annual_limit'],
            'is_accrued' => $request->has('is_accrued'),
            'is_cumulative' => $request->has('is_cumulative'),
            'requires_6_months' => $request->has('requires_6_months'),
            'is_monetizable' => $request->has('is_monetizable'),
            'requires_attachment' => $request->has('requires_attachment'),
            'attachment_info' => $validated['attachment_info'],
            'document_path' => $documentPath,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('admin.leave')->with('success', 'Leave type added successfully!');
    }
}
