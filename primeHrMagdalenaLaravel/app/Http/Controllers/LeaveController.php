<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveAccrualRate;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        // Get query parameters
        $sortBy = request('sort_by', 'leave_code');
        $sortOrder = request('sort_order', 'asc');
        $perPage = request('per_page', 10);

        // Validate sort column
        $allowedSortColumns = ['leave_code', 'leave_name', 'annual_limit', 'is_accrued', 'is_active'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'leave_code';
        }

        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Validate per page
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        // Fetch leave types from database with pagination and sorting
        $leaveTypes = LeaveType::orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends([
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage
            ]);

        // Fetch accrual rates with leave type relationship
        $accrualRates = LeaveAccrualRate::with('leaveType')
            ->orderBy('is_active', 'desc')
            ->orderBy('leave_type_id', 'asc')
            ->orderBy('effective_date', 'desc')
            ->paginate(10);

        // Get only accrued leave types for the modal dropdown
        $accruedLeaveTypes = LeaveType::where('is_accrued', true)
            ->where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        // Fetch all leave applications with relationships
        $leaveApplications = LeaveApplication::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'leaveType'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        // Get unique departments for filter
        $departments = $leaveApplications
            ->pluck('employee.employmentDetail.departmentRelation.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Get all employees for manual credit modal
        $employees = \App\Models\Employee::with('employmentDetail.departmentRelation')
            ->orderBy('employee_id')
            ->get();

        // Sample benefits data (you can create a table for this later)
        $benefitsData = [
            ['empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'gsis' => '₱3,794', 'philhealth' => '₱1,050', 'pagibig' => '₱100', 'vlBalance' => 15, 'slBalance' => 15],
            ['empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'gsis' => '₱3,428', 'philhealth' => '₱950', 'pagibig' => '₱100', 'vlBalance' => 12, 'slBalance' => 13],
            ['empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'gsis' => '₱3,046', 'philhealth' => '₱850', 'pagibig' => '₱100', 'vlBalance' => 13, 'slBalance' => 11],
            ['empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'gsis' => '₱4,253', 'philhealth' => '₱1,150', 'pagibig' => '₱100', 'vlBalance' => 10, 'slBalance' => 9],
            ['empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'gsis' => '₱3,159', 'philhealth' => '₱875', 'pagibig' => '₱100', 'vlBalance' => 14, 'slBalance' => 14],
            ['empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'gsis' => '₱2,748', 'philhealth' => '₱775', 'pagibig' => '₱100', 'vlBalance' => 8, 'slBalance' => 10],
        ];

        return view('admin.leaveAndBenefits.adminLeaveAndBenefits', compact('leaveTypes', 'leaveApplications', 'benefitsData', 'accrualRates', 'accruedLeaveTypes', 'departments', 'employees'));
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

    public function show($code)
    {
        $leaveType = LeaveType::where('leave_code', $code)->firstOrFail();
        return response()->json($leaveType);
    }

    public function update(Request $request, $code)
    {
        $leaveType = LeaveType::where('leave_code', $code)->firstOrFail();

        $validated = $request->validate([
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

        $documentPath = $leaveType->document_path;
        if ($request->hasFile('document')) {
            // Delete old document if exists
            if ($documentPath && \Storage::disk('public')->exists($documentPath)) {
                \Storage::disk('public')->delete($documentPath);
            }
            $documentPath = $request->file('document')->store('leave_types_documents', 'public');
        }

        $leaveType->update([
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

        return redirect()->route('admin.leave')->with('success', 'Leave type updated successfully!');
    }

    public function storeAccrualRate(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types_config,id',
            'accrual_frequency' => 'required|in:daily,monthly,yearly',
            'days_of_service_required' => 'required|numeric|min:0.01',
            'credits_earned_per_period' => 'required|numeric|min:0.0001',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        LeaveAccrualRate::create($validated);

        return redirect()->route('admin.leave')->with('success', 'Accrual rate added successfully!');
    }

    public function showAccrualRate($id)
    {
        $accrualRate = LeaveAccrualRate::with('leaveType')->findOrFail($id);
        return response()->json($accrualRate);
    }

    public function updateAccrualRate(Request $request, $id)
    {
        $accrualRate = LeaveAccrualRate::findOrFail($id);

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types_config,id',
            'accrual_frequency' => 'required|in:daily,monthly,yearly',
            'days_of_service_required' => 'required|numeric|min:0.01',
            'credits_earned_per_period' => 'required|numeric|min:0.0001',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $accrualRate->update($validated);

        return redirect()->route('admin.leave')->with('success', 'Accrual rate updated successfully!');
    }

    public function destroyAccrualRate($id)
    {
        $accrualRate = LeaveAccrualRate::findOrFail($id);
        $accrualRate->delete();

        return redirect()->route('admin.leave')->with('success', 'Accrual rate deleted successfully!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_code' => 'required|exists:leave_types_config,leave_code',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'number_of_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $employee = auth()->user()->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $leaveType = LeaveType::where('leave_code', $validated['leave_code'])->first();
            
            // Check if attachment is required
            if ($leaveType->requires_attachment && !$request->hasFile('attachment')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment is required for this leave type'
                ], 422);
            }

            // Check for overlapping leave requests (pending or approved)
            $hasOverlap = LeaveApplication::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                          });
                })
                ->exists();

            if ($hasOverlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a leave request for the selected dates. Please choose different dates or cancel your existing leave request.'
                ], 422);
            }

            // Check leave balance
            $year = Carbon::parse($validated['start_date'])->year;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $validated['leave_code'])
                ->where('year', $year)
                ->first();

            if (!$leaveBalance || $leaveBalance->available_credits < $validated['number_of_days']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient leave balance'
                ], 422);
            }

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
            }

            // Create leave application
            $leaveApplication = LeaveApplication::create([
                'employee_id' => $employee->id,
                'leave_code' => $validated['leave_code'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'number_of_days' => $validated['number_of_days'],
                'reason' => $validated['reason'],
                'status' => 'pending',
                'attachment_path' => $attachmentPath,
                'filed_by' => auth()->id(),
            ]);

            // Create pending transaction
            $balanceBefore = $leaveBalance->available_credits;
            $leaveBalance->pending_credits += $validated['number_of_days'];
            $leaveBalance->available_credits -= $validated['number_of_days'];
            $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $employee->id,
                    'leave_code' => $validated['leave_code'],
                    'year' => $year,
                    'transaction_type' => 'pending',
                    'amount' => -$validated['number_of_days'],
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Pending leave application {$leaveApplication->application_number}",
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully',
                'application_number' => $leaveApplication->application_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();

        try {
            $employee = auth()->user()->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $leaveApplication = LeaveApplication::where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$leaveApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application not found'
                ], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be cancelled'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->available_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $employee->id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year,
                    'transaction_type' => 'credit',
                    'amount' => $leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Cancelled leave application {$leaveApplication->application_number}",
                ]);
            }

            $leaveApplication->status = 'cancelled';
            $leaveApplication->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $leaveApplication = LeaveApplication::findOrFail($id);

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be approved'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->used_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $leaveApplication->employee_id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year,
                    'transaction_type' => 'debit',
                    'amount' => -$leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Approved leave application {$leaveApplication->application_number}",
                ]);
            }

            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = auth()->id();
            $leaveApplication->approved_at = now();
            $leaveApplication->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'remarks' => 'required|string|max:500'
            ]);

            $leaveApplication = LeaveApplication::findOrFail($id);

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be rejected'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->available_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $leaveApplication->employee_id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year,
                    'transaction_type' => 'credit',
                    'amount' => $leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Rejected leave application {$leaveApplication->application_number}: {$validated['remarks']}",
                ]);
            }

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = auth()->id();
            $leaveApplication->approved_at = now();
            $leaveApplication->approver_remarks = $validated['remarks'];
            $leaveApplication->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeBalances($employeeId)
    {
        try {
            $year = now()->year;
            
            // Get all leave balances for the employee
            $balances = LeaveBalance::where('employee_id', $employeeId)
                ->where('year', $year)
                ->pluck('available_credits', 'leave_code');

            // Get all active leave types
            $leaveTypes = LeaveType::where('is_active', true)
                ->orderBy('leave_name')
                ->get(['leave_code', 'leave_name']);

            return response()->json([
                'success' => true,
                'balances' => $balances,
                'leaveTypes' => $leaveTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load employee balances: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeManualCredit(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_code' => 'required|exists:leave_types_config,leave_code',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:add,deduct',
            'remarks' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $year = Carbon::parse($validated['transaction_date'])->year;
            $isDeduction = $validated['transaction_type'] === 'deduct';
            
            // Get or create leave balance for the employee
            $leaveBalance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'leave_code' => $validated['leave_code'],
                    'year' => $year,
                ],
                [
                    'total_credits' => 0,
                    'used_credits' => 0,
                    'pending_credits' => 0,
                    'available_credits' => 0,
                    'carried_over' => 0,
                ]
            );

            $balanceBefore = $leaveBalance->available_credits;
            
            // Check if deduction would result in negative balance (warning only, still allow)
            if ($isDeduction && $balanceBefore < $validated['amount']) {
                // Log warning but proceed
                \Log::warning('Manual deduction results in negative balance', [
                    'employee_id' => $validated['employee_id'],
                    'leave_code' => $validated['leave_code'],
                    'current_balance' => $balanceBefore,
                    'deduction_amount' => $validated['amount'],
                    'processed_by' => auth()->id()
                ]);
            }
            
            // Apply adjustment
            if ($isDeduction) {
                $leaveBalance->total_credits -= $validated['amount'];
                $leaveBalance->available_credits -= $validated['amount'];
                $transactionAmount = -$validated['amount'];
                $transactionType = 'debit';
            } else {
                $leaveBalance->total_credits += $validated['amount'];
                $leaveBalance->available_credits += $validated['amount'];
                $transactionAmount = $validated['amount'];
                $transactionType = 'credit';
            }
            
            $leaveBalance->save();

            // Create transaction record
            LeaveTransaction::create([
                'employee_id' => $validated['employee_id'],
                'leave_code' => $validated['leave_code'],
                'year' => $year,
                'transaction_type' => 'adjustment',
                'amount' => $transactionAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $leaveBalance->available_credits,
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'transaction_date' => $validated['transaction_date'],
                'processed_by' => auth()->id(),
                'remarks' => ($isDeduction ? '[DEDUCTION] ' : '[ADDITION] ') . $validated['remarks'],
            ]);

            DB::commit();

            $message = $isDeduction 
                ? 'Leave credits deducted successfully!' 
                : 'Leave credits added successfully!';

            return redirect()->route('admin.leave')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('admin.leave')
                ->with('error', 'Failed to adjust leave credits: ' . $e->getMessage());
        }
    }
}