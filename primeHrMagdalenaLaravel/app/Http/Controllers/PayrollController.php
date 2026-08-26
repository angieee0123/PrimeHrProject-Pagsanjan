<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\SalaryComputation;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\Department;
use App\Models\EmploymentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Services\PayrollRegisterService;

class PayrollController extends Controller
{
    public function index(Request $request, PayrollRegisterService $registerService)
    {
        $activeTab = $request->input('tab', 'register');

        // Handle Payslip Management Tab
        if ($activeTab === 'payslips') {
            $salaryComputations = SalaryComputation::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation'
            ])
            ->orderBy('period_end', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

            // Set empty payrollRecords for stats calculation
            $payrollRecords = collect();
            $viewMode = 'employee';
            $deductionTypes = collect();
            $departments = collect();
            $employmentStatuses = collect();
            $employees = collect();

            return view('admin.payroll.adminPayroll', compact('salaryComputations', 'payrollRecords', 'viewMode', 'deductionTypes', 'departments', 'employmentStatuses', 'employees'));
        }

        // Handle Payroll Register Tab.
        //
        // The rows are `PayrollRegisterService`'s, not this method's. They
        // used to be ~230 lines inline here, which is precisely why the
        // register's Export button sat wired to nothing: reproducing what the
        // screen shows meant re-running the action that renders it. Both now
        // read the same object, so the exported file and the table above it
        // cannot state different figures for the same period.
        $register = $registerService->build([
            'start_date'        => $request->input('start_date', now()->startOfMonth()->format('Y-m-d')),
            'end_date'          => $request->input('end_date', now()->endOfMonth()->format('Y-m-d')),
            'department'        => $request->input('department'),
            'employment_status' => $request->input('employment_status'),
            'employee_name'     => $request->input('employee_name'),
            'status'            => $request->input('status'),
            'view_mode'         => $request->input('view_mode', 'daily'),
        ]);

        $payrollRecords = $register['records'];
        $deductionTypes = $register['deductionTypes'];
        $viewMode       = $register['view_mode'];

        // Get unique departments for filter
        $departments = Department::where('status', 'Active')->pluck('name');

        // Employment types for filter: always the six we employ, plus anything
        // else the rows actually hold, so a legacy value stays filterable
        $employmentStatuses = collect(EmploymentDetail::EMPLOYMENT_TYPES)
            ->merge(
                EmploymentDetail::whereNotNull('employment_status')
                    ->where('employment_status', '!=', '')
                    ->distinct()
                    ->orderBy('employment_status')
                    ->pluck('employment_status')
            )
            ->unique()
            ->values();

        // Get unique employee names for filter
        $employees = Employee::orderBy('first_name')
            ->get()
            ->map(function($emp) {
                return trim($emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '') . $emp->last_name);
            })
            ->unique()
            ->values();

        return view('admin.payroll.adminPayroll', compact('payrollRecords', 'departments', 'employmentStatuses', 'employees', 'viewMode', 'deductionTypes'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date',
            'payroll_type' => 'required|in:regular,13th_month,bonus,special',
            'department' => 'nullable|string',
            'employment_status' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Determine cutoff period (1st or 2nd half of month)
            $startDay = (int) date('d', strtotime($data['start_date']));
            $isCutoff1st = $startDay <= 15;

            // Get employees based on filters
            $employeesQuery = Employee::with([
                'employmentDetail.departmentRelation',
                'employmentDetail.designationRelation',
                'deductions' => function($q) use ($data) {
                    $q->where('status', 'ACTIVE')
                      ->where('start_date', '<=', $data['end_date'])
                      ->where(function($query) use ($data) {
                          $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', $data['end_date']);
                      })
                      ->with('deductionType.schedules');
                }
            ]);

            if ($data['department']) {
                $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($data) {
                    $q->where('name', $data['department']);
                });
            }

            if ($data['employment_status']) {
                $employeesQuery->whereHas('employmentDetail', function($q) use ($data) {
                    $q->where('employment_status', $data['employment_status']);
                });
            }

            $employees = $employeesQuery->get();
            
            if ($employees->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No employees found matching the selected criteria.',
                    'errors' => ['Please adjust your filters and try again.']
                ], 422);
            }
            
            $processedCount = 0;
            $periodComputationsCreated = 0;
            $errors = [];
            // Who to tell their payslip is ready. Collected from the rows that
            // actually saved, not from the employee filter: an employee skipped
            // for having no daily computations gets no payslip and must not be
            // told one is waiting.
            $payslipEmployeeIds = [];
            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;

            foreach ($employees as $employee) {
                // Get all attendance records for the period
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$data['start_date'], $data['end_date']])
                    ->get();

                // First, ensure all daily computations exist
                foreach ($attendances as $attendance) {
                    $accreditedLog = AccreditedHoursLog::where('attendance_id', $attendance->id)->first();
                    
                    if (!$accreditedLog) {
                        $errors[] = "No accredited hours log for {$employee->first_name} {$employee->last_name} on {$attendance->date}";
                        continue;
                    }

                    // Check if salary computation already exists
                    $existingComputation = DailySalaryComputation::where('accredited_hours_log_id', $accreditedLog->id)->first();
                    
                    if (!$existingComputation) {
                        DailySalaryComputation::computeFromAccreditedLog($accreditedLog);
                        $processedCount++;
                    }
                }

                // Get salary computations for the period
                $computations = DailySalaryComputation::where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$data['start_date'], $data['end_date']])
                    ->get();

                if ($computations->isEmpty()) {
                    $errors[] = "No daily computations found for {$employee->first_name} {$employee->last_name}. Please ensure attendance has been processed.";
                    continue;
                }

                \Log::info("Processing payroll for {$employee->first_name} {$employee->last_name}: {$computations->count()} daily computations found");

                // Calculate totals
                $monthlyRate = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                $dailyRate = $monthlyRate > 0 ? $monthlyRate / 22 : 0;
                $hourlyRate = $dailyRate > 0 ? $dailyRate / 8 : 0;
                
                // Load accredited hours logs for calculations
                $computations->load('accreditedHoursLog');
                
                $totalDaysPresent = 0;
                $totalDaysAbsent = 0;
                $totalAccreditedHours = 0;
                $totalLateMinutes = 0;
                $totalUndertimeMinutes = 0;
                $totalOtMinutes = 0;
                
                foreach ($computations as $comp) {
                    if ($comp->accreditedHoursLog) {
                        $accreditedMinutes = $comp->accreditedHoursLog->total_accredited_minutes ?? 0;
                        if ($accreditedMinutes > 0) {
                            $totalDaysPresent++;
                        } else {
                            $totalDaysAbsent++;
                        }
                        $totalAccreditedHours += $accreditedMinutes / 60;
                        $totalLateMinutes += $comp->accreditedHoursLog->late_minutes ?? 0;
                        $totalUndertimeMinutes += $comp->accreditedHoursLog->undertime_minutes ?? 0;
                        $totalOtMinutes += $comp->accreditedHoursLog->ot_minutes ?? 0;
                    }
                }
                
                $basicPay = $computations->sum('daily_basic_pay');
                $otPay = $computations->sum('ot_pay');
                $lateDeduction = $computations->sum('late_deduction');
                $undertimeDeduction = $computations->sum('undertime_deduction');

                // Calculate deductions by type with cutoff schedule
                $totalOtherDeductions = 0;
                $deductionBreakdown = []; // Store individual deduction amounts
                
                foreach ($employee->deductions as $deduction) {
                    $deductionType = $deduction->deductionType;
                    
                    if (!$deductionType->deducted_from_employee) {
                        continue;
                    }
                    
                    // Get schedule - prioritize custom over default
                    $cutoffSchedule = $deduction->custom_cutoff_schedule 
                        ?? ($deductionType->schedules->first()->cutoff_schedule ?? 'BOTH_SPLIT');
                    
                    // Calculate base amount
                    $deductionAmount = 0;
                    if ($deductionType->category === 'MANDATORY') {
                        if ($deductionType->computation_type === 'PERCENTAGE') {
                            $baseAmount = $deductionType->base_salary_type === 'BASIC' ? $basicPay 
                                : ($deductionType->base_salary_type === 'GROSS' ? $basicPay + $otPay 
                                : ($deductionType->base_salary_type === 'MONTHLY' ? $monthlyRate 
                                : $basicPay));
                            $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                        } elseif ($deductionType->computation_type === 'FIXED') {
                            $deductionAmount = $deductionType->percentage_rate ?? $deduction->amount ?? 0;
                        } else {
                            $deductionAmount = $deduction->amount ?? 0;
                        }
                    } elseif ($deductionType->category === 'LOAN') {
                        $deductionAmount = $deduction->installment_amount ?? 0;
                    }
                    
                    // Apply cutoff schedule
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $deductionAmount = $isCutoff1st ? $deductionAmount : 0;
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $deductionAmount = $isCutoff1st ? 0 : $deductionAmount;
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        // Keep full amount
                    } else { // BOTH_SPLIT
                        $deductionAmount = $deductionAmount / 2;
                    }
                    
                    // Store individual deduction
                    if ($deductionAmount > 0) {
                        $deductionBreakdown[$deductionType->code] = [
                            'name' => $deductionType->name,
                            'amount' => round($deductionAmount, 2),
                            'category' => $deductionType->category,
                        ];
                    }
                    
                    $totalOtherDeductions += $deductionAmount;
                }

                $grossPay = $basicPay + $otPay;
                $netPay = $grossPay - $lateDeduction - $undertimeDeduction - $totalOtherDeductions;
                
                // Accumulate totals
                $totalGross += $grossPay;
                $totalDeductions += ($lateDeduction + $undertimeDeduction + $totalOtherDeductions);
                $totalNet += $netPay;

                // Create/update the period salary computation
                try {
                    \Log::info("Creating salary computation for employee {$employee->id}: Basic={$basicPay}, Deductions={$totalOtherDeductions}, Net={$netPay}");
                    
                    $periodComputation = SalaryComputation::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'period_start' => $data['start_date'],
                            'period_end' => $data['end_date'],
                        ],
                        [
                            'pay_date' => $data['pay_date'],
                            'payroll_type' => $data['payroll_type'],
                            'monthly_rate' => $monthlyRate,
                            'daily_rate' => $dailyRate,
                            'hourly_rate' => $hourlyRate,
                            'total_days_present' => $totalDaysPresent,
                            'total_days_absent' => $totalDaysAbsent,
                            'total_hours_worked' => $computations->count() * 8,
                            'total_accredited_hours' => $totalAccreditedHours,
                            'total_late_minutes' => $totalLateMinutes,
                            'total_undertime_minutes' => $totalUndertimeMinutes,
                            'total_ot_minutes' => $totalOtMinutes,
                            'basic_pay' => $basicPay,
                            'ot_pay' => $otPay,
                            'late_deduction' => $lateDeduction,
                            'undertime_deduction' => $undertimeDeduction,
                            'other_deductions' => $totalOtherDeductions,
                            'deduction_breakdown' => json_encode($deductionBreakdown),
                            'gross_pay' => $grossPay,
                            'net_pay' => $netPay,
                            'status' => 'approved',
                            'computed_by' => Auth::id(),
                        ]
                    );
                    
                    // Always increment counter when record is saved (created or updated)
                    $periodComputationsCreated++;
                    $payslipEmployeeIds[] = $employee->id;
                    \Log::info("Successfully saved salary computation ID: {$periodComputation->id} (wasRecentlyCreated: " . ($periodComputation->wasRecentlyCreated ? 'yes' : 'no') . ")");
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to create period computation for {$employee->first_name} {$employee->last_name}: {$e->getMessage()}";
                    \Log::error("Salary computation error for employee {$employee->id}: " . $e->getMessage());
                    \Log::error($e->getTraceAsString());
                }
            }

            DB::commit();

            // After the commit: a notification announcing a payslip that got
            // rolled back would be a false statement about somebody's pay.
            // Guarded on non-empty because payrollGenerated() treats an empty
            // list as "notify every employee".
            if (!empty($payslipEmployeeIds)) {
                NotificationService::payrollGenerated(
                    $data['start_date'],
                    $data['end_date'],
                    $payslipEmployeeIds
                );
            }

            $message = "Payroll generated successfully! Created {$periodComputationsCreated} payslip(s) for period " . 
                       date('M d, Y', strtotime($data['start_date'])) . ' to ' . 
                       date('M d, Y', strtotime($data['end_date']));

            if ($processedCount > 0) {
                $message .= " (Processed {$processedCount} daily computation(s))";
            }

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'employees_processed' => $periodComputationsCreated,
                    'total_gross' => $totalGross,
                    'total_deductions' => $totalDeductions,
                    'total_net' => $totalNet,
                    'errors' => count($errors) > 0 ? $errors : null
                ]);
            }

            // Traditional redirect for form submissions
            if (count($errors) > 0) {
                $errorDetails = implode('; ', array_slice($errors, 0, 5)); // Show first 5 errors
                return redirect()->route('admin.payroll', [
                    'tab' => 'payslips'
                ])->with('warning', $message . ' Errors: ' . $errorDetails);
            }

            return redirect()->route('admin.payroll', [
                'tab' => 'payslips'
            ])->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payroll Generation Error: ' . $e->getMessage());
            \Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate payroll',
                    'error' => $e->getMessage(),
                    'errors' => [$e->getMessage()]
                ], 500);
            }
            
            return redirect()->route('admin.payroll', ['tab' => 'generate'])
                ->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    public function approvePayslip($id)
    {
        try {
            $computation = SalaryComputation::findOrFail($id);
            $computation->update(['status' => 'approved']);

            return response()->json(['success' => true, 'message' => 'Payslip approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payslipDetails($id)
    {
        try {
            $computation = SalaryComputation::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation'
            ])->findOrFail($id);

            // Parse deduction_breakdown if it's a JSON string
            $deductionBreakdown = $computation->deduction_breakdown;
            if (is_string($deductionBreakdown)) {
                $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
            } elseif (!is_array($deductionBreakdown)) {
                $deductionBreakdown = [];
            }

            $payslip = [
                'id' => $computation->id,
                'employee_name' => ($computation->employee->first_name ?? '') . ' ' . ($computation->employee->last_name ?? ''),
                'employee_id' => $computation->employee->employee_id ?? 'N/A',
                'department' => $computation->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                'position' => $computation->employee->employmentDetail->designationRelation->title ?? 'N/A',
                'period' => $computation->period_start->format('M d, Y') . ' - ' . $computation->period_end->format('M d, Y'),
                'pay_date' => $computation->pay_date ? $computation->pay_date->format('M d, Y') : null,
                'monthly_rate' => $computation->monthly_rate,
                'daily_rate' => $computation->daily_rate,
                'total_days_present' => $computation->total_days_present,
                'basic_pay' => $computation->basic_pay,
                'ot_pay' => $computation->ot_pay,
                'gross_pay' => $computation->gross_pay,
                'late_deduction' => $computation->late_deduction,
                'undertime_deduction' => $computation->undertime_deduction,
                'other_deductions' => $computation->other_deductions,
                'deduction_breakdown' => $deductionBreakdown,
                'total_deductions' => $computation->late_deduction + $computation->undertime_deduction + $computation->other_deductions,
                'net_pay' => $computation->net_pay,
                'status' => $computation->status,
                'notes' => $computation->notes,
            ];

            return response()->json(['success' => true, 'payslip' => $payslip]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rejectPayslip(Request $request, $id)
    {
        try {
            $request->validate(['reason' => 'required|string']);

            $computation = SalaryComputation::findOrFail($id);
            $computation->update([
                'status' => 'rejected',
                'notes' => $request->reason
            ]);

            return response()->json(['success' => true, 'message' => 'Payslip rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /*
        `exportPayslips()` and `export()` used to live here. Both now sit on
        `PayrollExportController` beside the Payroll Register export, which had
        no handler at all — one controller per page, one method per tab, the
        same rule the Departments and Leave & Benefits exports follow. They
        also wear `CsvReportWriter`'s letterhead now; the run export printed a
        hard-coded "MUNICIPAL GOVERNMENT OF PAGSANJAN" into a Magdalena
        deployment.
    */

    public function preview(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $department = $request->input('department');
        $employmentStatus = $request->input('employment_status');

        // Get employees based on filters
        $employeesQuery = Employee::with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation'
        ]);

        if ($department) {
            $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($department) {
                $q->where('name', $department);
            });
        }

        if ($employmentStatus) {
            $employeesQuery->whereHas('employmentDetail', function($q) use ($employmentStatus) {
                $q->where('employment_status', $employmentStatus);
            });
        }

        $employees = $employeesQuery->get();
        $employeeIds = $employees->pluck('id');

        // Get existing salary computations for the period
        $computations = DailySalaryComputation::whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $estimatedGross = $computations->sum('daily_basic_pay') + $computations->sum('ot_pay');
        $estimatedDeductions = $computations->sum('late_deduction') + $computations->sum('undertime_deduction');
        $estimatedNet = $estimatedGross - $estimatedDeductions;

        return response()->json([
            'employee_count' => $employees->count(),
            'estimated_gross' => number_format($estimatedGross, 2, '.', ''),
            'estimated_deductions' => number_format($estimatedDeductions, 2, '.', ''),
            'estimated_net' => number_format($estimatedNet, 2, '.', ''),
        ]);
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date',
            'payroll_type' => 'required|in:regular,13th_month,bonus,special',
            'department' => 'nullable|string',
            'employment_status' => 'nullable|string',
        ]);

        try {
            // Determine cutoff period
            $startDay = (int) date('d', strtotime($data['start_date']));
            $isCutoff1st = $startDay <= 15;

            // Get employees based on filters
            $employeesQuery = Employee::with([
                'employmentDetail.departmentRelation',
                'employmentDetail.designationRelation',
                'deductions' => function($q) use ($data) {
                    $q->where('status', 'ACTIVE')
                      ->where('start_date', '<=', $data['end_date'])
                      ->where(function($query) use ($data) {
                          $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', $data['end_date']);
                      })
                      ->with('deductionType.schedules');
                }
            ]);

            if ($data['department']) {
                $employeesQuery->whereHas('employmentDetail.departmentRelation', function($q) use ($data) {
                    $q->where('name', $data['department']);
                });
            }

            if ($data['employment_status']) {
                $employeesQuery->whereHas('employmentDetail', function($q) use ($data) {
                    $q->where('employment_status', $data['employment_status']);
                });
            }

            $employees = $employeesQuery->get();
            $payrollData = [];
            $allDeductionTypes = collect();

            foreach ($employees as $employee) {
                // Get salary computations for the period
                $computations = DailySalaryComputation::where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$data['start_date'], $data['end_date']])
                    ->get();

                if ($computations->isEmpty()) {
                    continue;
                }

                $basicPay = $computations->sum('daily_basic_pay');
                $otPay = $computations->sum('ot_pay');
                $lateDeduction = $computations->sum('late_deduction');
                $undertimeDeduction = $computations->sum('undertime_deduction');
                $daysWorked = $computations->count();
                $dailyRate = $computations->first()->daily_rate ?? 0;

                // Calculate deductions by type with cutoff schedule
                $deductions = [];
                foreach ($employee->deductions as $deduction) {
                    $deductionType = $deduction->deductionType;
                    $code = $deductionType->code;

                    if (!$deductionType->deducted_from_employee) {
                        continue;
                    }

                    // Get schedule - prioritize custom over default
                    $cutoffSchedule = $deduction->custom_cutoff_schedule
                        ?? ($deductionType->schedules->first()->cutoff_schedule ?? 'BOTH_SPLIT');

                    // Calculate base amount
                    $deductionAmount = 0;
                    if ($deductionType->category === 'MANDATORY') {
                        if ($deductionType->computation_type === 'PERCENTAGE') {
                            $baseAmount = $deductionType->base_salary_type === 'BASIC' ? $basicPay
                                : ($deductionType->base_salary_type === 'GROSS' ? $basicPay + $otPay
                                : ($deductionType->base_salary_type === 'MONTHLY' ? ($employee->employmentDetail?->designationRelation?->monthly_rate ?? 0)
                                : $basicPay));
                            $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                        } elseif ($deductionType->computation_type === 'FIXED') {
                            $deductionAmount = $deductionType->percentage_rate ?? $deduction->amount ?? 0;
                        } else {
                            $deductionAmount = $deduction->amount ?? 0;
                        }
                    } elseif ($deductionType->category === 'LOAN') {
                        $deductionAmount = $deduction->installment_amount ?? 0;
                    }

                    // Apply cutoff schedule
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        $deductions[$code] = $deductionAmount;
                    } else {
                        $deductions[$code] = $deductionAmount / 2;
                    }

                    // Collect unique deduction types
                    if (!$allDeductionTypes->contains($code)) {
                        $allDeductionTypes->push($code);
                    }
                }

                $payrollData[] = [
                    'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                    'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                    'department' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                    'days_worked' => $daysWorked,
                    'daily_rate' => $dailyRate,
                    'basic_pay' => $basicPay,
                    'ot_pay' => $otPay,
                    'late' => $lateDeduction,
                    'undertime' => $undertimeDeduction,
                    'deductions' => $deductions,
                ];
            }

            // Get deduction type names
            $deductionTypeNames = [];
            if ($allDeductionTypes->isNotEmpty()) {
                $deductionTypeModels = \App\Models\DeductionType::whereIn('code', $allDeductionTypes)->get();
                foreach ($deductionTypeModels as $dt) {
                    $deductionTypeNames[$dt->code] = $dt->name;
                }
            }

            $payrollTypeLabels = [
                'regular' => 'Regular Payroll',
                '13th_month' => '13th Month Pay',
                'bonus' => 'Bonus',
                'special' => 'Special Payroll'
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => date('M d, Y', strtotime($data['start_date'])) . ' - ' . date('M d, Y', strtotime($data['end_date'])),
                    'pay_date' => date('M d, Y', strtotime($data['pay_date'])),
                    'payroll_type' => $payrollTypeLabels[$data['payroll_type']],
                    'employees' => $payrollData,
                    'deduction_types' => $allDeductionTypes->toArray(),
                    'deduction_names' => $deductionTypeNames,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
