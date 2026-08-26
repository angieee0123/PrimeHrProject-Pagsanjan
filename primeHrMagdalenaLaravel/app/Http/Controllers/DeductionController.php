<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeductionType;
use App\Models\EmployeeDeduction;
use App\Models\Employee;

class DeductionController extends Controller
{
    public function index()
    {
        // Get all employee deductions with relationships
        $employeeDeductions = EmployeeDeduction::with([
            'employee.employmentDetail.departmentRelation',
            'deductionType'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        // Get only loans (category = LOAN)
        $loans = EmployeeDeduction::with([
            'employee.employmentDetail.departmentRelation',
            'deductionType.schedules'
        ])
        ->whereHas('deductionType', function($q) {
            $q->where('category', 'LOAN');
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // Get employees with active deductions for schedules tab
        $employeesWithDeductions = Employee::with([
            'employmentDetail.departmentRelation',
            'deductions' => function($q) {
                $q->where('status', 'ACTIVE')->with('deductionType');
            }
        ])
        ->whereHas('deductions', function($q) {
            $q->where('status', 'ACTIVE');
        })
        ->orderBy('last_name')
        ->get()
        ->map(function($employee) {
            $deductions = $employee->deductions;
            $loansCount = $deductions->filter(function($d) {
                return $d->deductionType->category === 'LOAN';
            })->count();
            $deductionsCount = $deductions->count();

            return [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'photo' => $employee->photo,
                'department' => $employee->employmentDetail->departmentRelation->name ?? 'N/A',
                'deductions_count' => $deductionsCount,
                'loans_count' => $loansCount,
                'updated_at' => $deductions->max('updated_at'),
            ];
        });

        // Get statistics
        $stats = [
            'total_types' => DeductionType::where('is_active', true)->count(),
            'mandatory_count' => DeductionType::where('category', 'MANDATORY')->where('is_active', true)->count(),
            'loan_count' => DeductionType::where('category', 'LOAN')->where('is_active', true)->count(),
            'active_loans' => EmployeeDeduction::whereHas('deductionType', function($q) {
                $q->where('category', 'LOAN');
            })->where('status', 'ACTIVE')->count(),
            'total_outstanding' => EmployeeDeduction::whereHas('deductionType', function($q) {
                $q->where('category', 'LOAN');
            })->where('status', 'ACTIVE')->sum('remaining_balance'),
            'transactions_this_month' => 0, // PayrollDeduction table is empty
        ];

        return view('admin.deductions.adminDeductions', compact('employeeDeductions', 'loans', 'employeesWithDeductions', 'stats'));
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:deduction_types,code',
            'name' => 'required|string|max:100',
            'category' => 'required|in:MANDATORY,LOAN,OTHER',
            'computation_type' => 'required|in:PERCENTAGE,FIXED,CUSTOM',
            'rate' => 'nullable|numeric|min:0',
            'base_salary' => 'nullable|in:BASIC,GROSS,MONTHLY,CUSTOM',
            'max_amount' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'deducted_from_employee' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        // Map form fields to database fields
        $deductionData = [
            'code' => $data['code'],
            'name' => $data['name'],
            'category' => $data['category'],
            'computation_type' => $data['computation_type'],
            'percentage_rate' => $data['rate'] ?? null,
            'base_salary_type' => $data['base_salary'] ?? null,
            'max_amount' => $data['max_amount'] ?? null,
            'is_active' => $data['is_active'],
            'deducted_from_employee' => $data['deducted_from_employee'],
        ];

        DeductionType::create($deductionData);

        return redirect()->route('admin.deductions')
            ->with('success', 'Deduction type "' . $data['name'] . '" added successfully!');
    }

    public function updateType(Request $request, $code)
    {
        $deductionType = DeductionType::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:MANDATORY,LOAN,OTHER',
            'computation_type' => 'required|in:PERCENTAGE,FIXED,CUSTOM',
            'rate' => 'nullable|numeric|min:0',
            'base_salary' => 'nullable|in:BASIC,GROSS,MONTHLY,CUSTOM',
            'max_amount' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'deducted_from_employee' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        $deductionType->update([
            'name'                   => $data['name'],
            'category'               => $data['category'],
            'computation_type'       => $data['computation_type'],
            'percentage_rate'        => $data['rate'] ?? null,
            'base_salary_type'       => $data['base_salary'] ?? null,
            'max_amount'             => $data['max_amount'] ?? null,
            'is_active'              => $data['is_active'],
            'deducted_from_employee' => $data['deducted_from_employee'],
        ]);

        return redirect()->route('admin.deductions')->with('success', 'Deduction type updated successfully.');
    }

    /**
     * One deduction type, looked up by code *or* id.
     *
     * Two tabs call this endpoint with different identifiers: Deduction Types
     * passes the code (`editDeductionType('GSIS PS')`) and Loan Types passes
     * the numeric id (`viewLoanTypeDetails(25)`). It only ever resolved codes,
     * so every Loan Types row asked for a type whose *code* was "25", got a
     * 404, and both "View details" and "Edit loan type" died in the fetch's
     * catch with "Failed to load loan type details."
     *
     * Codes are non-numeric throughout (GSIS PS, LOAN_MPL, PAG-IBIG GS), so a
     * numeric identifier is unambiguously an id.
     */
    public function showType($identifier)
    {
        $deductionType = is_numeric($identifier)
            ? DeductionType::findOrFail($identifier)
            : DeductionType::where('code', $identifier)->firstOrFail();

        // Get employee count
        $employeesCount = EmployeeDeduction::where('deduction_type_id', $deductionType->id)
            ->where('status', 'ACTIVE')
            ->distinct('employee_id')
            ->count();

        return response()->json([
            'id' => $deductionType->id,
            'code' => $deductionType->code,
            'name' => $deductionType->name,
            'category' => $deductionType->category,
            'computation_type' => $deductionType->computation_type,
            'percentage_rate' => $deductionType->percentage_rate,
            'max_amount' => $deductionType->max_amount,
            'is_active' => $deductionType->is_active,
            'employees_count' => $employeesCount,
        ]);
    }

    public function storeEmployeeDeduction(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deduction_type_id' => 'required',
            'other_provider_name' => 'nullable|string',
            'other_loan_type' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'installment_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
            'remarks' => 'nullable|string',
        ]);

        // Handle "Other" provider - create a custom deduction type
        if ($data['deduction_type_id'] === 'OTHER') {
            $providerName = $data['other_provider_name'] ?? 'External Provider';
            $loanDescription = $data['other_loan_type'] ?? 'Custom Loan';

            // Create unique code from provider name
            $code = 'LOAN_' . strtoupper(str_replace([' ', '-', '.'], '_', $providerName));

            // Create or find the custom deduction type
            $customDeduction = DeductionType::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $providerName . ' - ' . $loanDescription,
                    'category' => 'LOAN',
                    'computation_type' => 'FIXED',
                    'is_active' => true,
                ]
            );

            $data['deduction_type_id'] = $customDeduction->id;
            $data['remarks'] = trim(($data['remarks'] ?? '') . " [Provider: {$providerName}, Type: {$loanDescription}]");
        }

        // Set remaining balance equal to total amount for loans
        if ($data['total_amount'] ?? null) {
            $data['remaining_balance'] = $data['total_amount'];
        }

        // Remove non-database fields
        unset($data['other_provider_name'], $data['other_loan_type']);

        EmployeeDeduction::create($data);

        return redirect()->route('admin.deductions')->with('success', 'Loan assigned successfully.');
    }

    public function updateEmployeeDeduction(Request $request, $id)
    {
        $employeeDeduction = EmployeeDeduction::findOrFail($id);

        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'remaining_balance' => 'nullable|numeric|min:0',
            'installment_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
            'remarks' => 'nullable|string',
        ]);

        $employeeDeduction->update($data);

        return redirect()->route('admin.deductions')->with('success', 'Employee deduction updated successfully.');
    }

    public function bulkAssignEmployeeDeduction(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'deduction_types' => 'required|array|min:1',
            'deduction_types.*' => 'exists:deduction_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:ACTIVE,SUSPENDED,COMPLETED',
            'remarks' => 'nullable|string',
        ]);

        $assignedCount = 0;
        $skippedTypes = [];
        $employee = Employee::findOrFail($data['employee_id']);
        $employeeName = $employee->first_name . ' ' . $employee->last_name;

        foreach ($data['deduction_types'] as $deductionTypeId) {
            // Check if employee already has this deduction type active
            $exists = EmployeeDeduction::where('employee_id', $data['employee_id'])
                ->where('deduction_type_id', $deductionTypeId)
                ->where('status', 'ACTIVE')
                ->exists();

            if ($exists) {
                $deductionType = DeductionType::find($deductionTypeId);
                $skippedTypes[] = $deductionType->name;
                continue;
            }

            // Create employee deduction
            EmployeeDeduction::create([
                'employee_id' => $data['employee_id'],
                'deduction_type_id' => $deductionTypeId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'remarks' => $data['remarks'],
            ]);
            $assignedCount++;
        }

        // Build success message
        if ($assignedCount > 0 && count($skippedTypes) > 0) {
            $skippedList = implode(', ', $skippedTypes);
            return redirect()->route('admin.deductions')
                ->with('success', "{$assignedCount} deduction(s) assigned to {$employeeName}. Skipped (already active): {$skippedList}");
        } elseif ($assignedCount > 0) {
            return redirect()->route('admin.deductions')
                ->with('success', "{$assignedCount} deduction(s) assigned to {$employeeName} successfully.");
        } else {
            $skippedList = implode(', ', $skippedTypes);
            return redirect()->route('admin.deductions')
                ->with('warning', "No deductions were assigned. All selected deductions are already active for {$employeeName}: {$skippedList}");
        }
    }

    /*
        `exportEmployeeDeductions()`, `exportLoans()` and `exportSchedules()`
        used to live here. All three now sit on `DeductionExportController`
        alongside the Deduction Types and Loan Types exports, which had no
        endpoint at all — one controller per page, one method per tab, the same
        rule the Departments and Leave & Benefits exports follow.

        They also wear `CsvReportWriter`'s letterhead now, and they read the
        toolbar's filters. Previously all three ignored the toolbar entirely,
        so a file exported from a filtered screen silently contained every row
        in the system.
    */

    public function showEmployeeDeduction($id)
    {
        $deduction = EmployeeDeduction::with(['employee', 'deductionType'])->findOrFail($id);
        return response()->json($deduction);
    }

    public function activeForEmployee($employeeId)
    {
        $deductions = EmployeeDeduction::where('employee_id', $employeeId)
            ->where('status', 'ACTIVE')
            ->with('deductionType')
            ->get()
            ->map(function($ed) {
                return [
                    'id' => $ed->deduction_type_id,
                    'name' => $ed->deductionType->name,
                    'code' => $ed->deductionType->code,
                ];
            });

        return response()->json(['deductions' => $deductions]);
    }

    public function deleteEmployeeDeduction($id)
    {
        $deduction = EmployeeDeduction::with(['employee', 'deductionType'])->findOrFail($id);
        $employeeName = $deduction->employee->first_name . ' ' . $deduction->employee->last_name;
        $deductionName = $deduction->deductionType->name;

        $deduction->delete();

        return redirect()->route('admin.deductions')
            ->with('success', "Deduction '{$deductionName}' removed from {$employeeName} successfully.");
    }

    public function deductionsForEmployee($employeeId)
    {
        $deductions = EmployeeDeduction::where('employee_id', $employeeId)
            ->where('status', 'ACTIVE')
            ->with('deductionType.schedules')
            ->get()
            ->map(function($ed) {
                $schedule = $ed->deductionType->schedules->first();
                $defaultSchedule = $schedule ? $schedule->cutoff_schedule : '1ST_ONLY';

                // Use custom schedule if set, otherwise use default
                $currentSchedule = $ed->custom_cutoff_schedule ?? $defaultSchedule;

                // Format amount display
                $amountDisplay = 'Auto';
                if ($ed->installment_amount) {
                    $amountDisplay = '₱' . number_format($ed->installment_amount, 2) . '/month';
                } elseif ($ed->amount) {
                    $amountDisplay = '₱' . number_format($ed->amount, 2);
                } elseif ($ed->deductionType->percentage_rate) {
                    $amountDisplay = $ed->deductionType->percentage_rate . '%';
                }

                return [
                    'id' => $ed->id, // This is the employee_deduction ID
                    'deduction_type_id' => $ed->deduction_type_id,
                    'name' => $ed->deductionType->name,
                    'code' => $ed->deductionType->code,
                    'category' => $ed->deductionType->category,
                    'computation_type' => $ed->deductionType->computation_type,
                    'amount' => $amountDisplay,
                    'current_schedule' => $currentSchedule,
                    'has_custom_schedule' => $ed->custom_cutoff_schedule !== null,
                    'default_schedule' => $defaultSchedule,
                ];
            });

        return response()->json(['deductions' => $deductions]);
    }

    public function updateSchedules(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'required|date_format:Y-m',
            'schedules' => 'required|array|min:1',
            'schedules.*.deduction_id' => 'required|integer',
            'schedules.*.cutoff' => 'required|in:1ST,2ND,BOTH',
        ]);

        $updatedCount = 0;
        $errors = [];

        foreach ($data['schedules'] as $schedule) {
            // Find the employee deduction by ID
            $employeeDeduction = EmployeeDeduction::where('id', $schedule['deduction_id'])
                ->where('employee_id', $data['employee_id'])
                ->first();

            if (!$employeeDeduction) {
                $errors[] = "Deduction ID {$schedule['deduction_id']} not found for this employee";
                continue;
            }

            // Map cutoff values to schedule enum
            $cutoffSchedule = match($schedule['cutoff']) {
                '1ST' => '1ST_ONLY',
                '2ND' => '2ND_ONLY',
                'BOTH' => 'BOTH_SPLIT',
                default => 'BOTH_SPLIT',
            };

            // Set custom schedule for this specific employee deduction
            $employeeDeduction->update(['custom_cutoff_schedule' => $cutoffSchedule]);
            $updatedCount++;
        }

        if (!empty($errors)) {
            return redirect()->route('admin.deductions')
                ->with('error', 'Some schedules could not be updated: ' . implode(', ', $errors));
        }

        $employee = Employee::findOrFail($data['employee_id']);
        $employeeName = $employee->first_name . ' ' . $employee->last_name;

        return redirect()->route('admin.deductions')
            ->with('success', "Deduction schedules updated for {$employeeName}. {$updatedCount} deduction(s) configured successfully.");
    }
}
