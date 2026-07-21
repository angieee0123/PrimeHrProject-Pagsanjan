<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\SalaryComputation;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index(Request $request)
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
            $employees = collect();

            return view('admin.payroll.adminPayroll', compact('salaryComputations', 'payrollRecords', 'viewMode', 'deductionTypes', 'departments', 'employees'));
        }

        // Handle Payroll Register Tab (existing code)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->input('department');
        $employeeName = $request->input('employee_name');
        $status = $request->input('status');
        $viewMode = $request->input('view_mode', 'daily');

        // Determine cutoff period (1st or 2nd half of month)
        $startDay = (int) date('d', strtotime($startDate));
        $isCutoff1st = $startDay <= 15;

        // Get daily salary computations for the period
        $query = DailySalaryComputation::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'employee.deductions' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'ACTIVE')
                  ->where('start_date', '<=', $endDate)
                  ->where(function($query) use ($endDate) {
                      $query->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
                  })
                  ->with('deductionType.schedules');
            },
            'accreditedHoursLog'
        ])
        ->whereBetween('work_date', [$startDate, $endDate])
        ->orderBy('work_date', 'asc')
        ->orderBy('employee_id');

        if ($department) {
            $query->whereHas('employee.employmentDetail.departmentRelation', function($q) use ($department) {
                $q->where('name', $department);
            });
        }

        if ($employeeName) {
            $query->whereHas('employee', function($q) use ($employeeName) {
                $q->whereRaw("CONCAT(first_name, ' ', COALESCE(CONCAT(SUBSTRING(middle_name, 1, 1), '. '), ''), last_name) = ?", [$employeeName]);
            });
        }

        $dailyComputations = $query->get();

        // Process based on view mode
        if ($viewMode === 'employee' || $viewMode === 'monthly') {
            // Group by employee
            $payrollRecords = $dailyComputations->groupBy('employee_id')->map(function($records) use ($viewMode, $startDate, $endDate, $isCutoff1st) {
                $employee = $records->first()->employee;
                $totalBasicPay = $records->sum('daily_basic_pay');
                $totalOtPay = $records->sum('ot_pay');
                $totalLateDeduction = $records->sum('late_deduction');
                $totalUndertimeDeduction = $records->sum('undertime_deduction');
                $recordStatus = $records->every(fn($r) => $r->daily_gross_pay > 0) ? 'Processed' : 'Pending';

                // Calculate deductions by type with cutoff schedule
                $deductions = [];
                foreach ($employee->deductions as $deduction) {
                    $deductionType = $deduction->deductionType;
                    $code = $deductionType->code;

                    if (!$deductionType->deducted_from_employee) {
                        continue;
                    }

                    // Get schedule - prioritize employee's custom schedule over deduction type schedule
                    $cutoffSchedule = 'BOTH_SPLIT'; // Default
                    if ($deduction->custom_cutoff_schedule) {
                        // Use employee-specific custom schedule
                        $cutoffSchedule = $deduction->custom_cutoff_schedule;
                    } else {
                        // Use deduction type's default schedule
                        $schedule = $deductionType->schedules->first();
                        $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                    }

                    // Calculate base deduction amount
                    $deductionAmount = 0;

                    if ($deductionType->category === 'MANDATORY') {
                        if ($deductionType->computation_type === 'PERCENTAGE') {
                            $baseAmount = 0;
                            if ($deductionType->base_salary_type === 'BASIC') {
                                $baseAmount = $totalBasicPay;
                            } elseif ($deductionType->base_salary_type === 'GROSS') {
                                $baseAmount = $totalBasicPay + $totalOtPay;
                            } elseif ($deductionType->base_salary_type === 'MONTHLY') {
                                $baseAmount = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                            } else {
                                $baseAmount = $totalBasicPay;
                            }
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
                    } else { // BOTH_SPLIT
                        $deductions[$code] = $deductionAmount / 2;
                    }
                }

                return [
                    'id' => $employee->employee_id ?? 'N/A',
                    'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                    'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                    'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                    'photo' => $employee->photo,
                    'work_date' => null,
                    'daily_rate' => $records->first()->daily_rate ?? 0,
                    'basic' => $totalBasicPay,
                    'ot_pay' => $totalOtPay,
                    'late_deduction' => $totalLateDeduction,
                    'undertime_deduction' => $totalUndertimeDeduction,
                    'deductions' => $deductions,
                    'status' => $recordStatus,
                    'days_count' => $records->count(),
                ];
            })->values();
        } else {
            // Daily view - one row per day per employee
            $payrollRecords = $dailyComputations->map(function($record) use ($startDate, $endDate, $isCutoff1st) {
                $employee = $record->employee;
                $recordStatus = $record->daily_gross_pay > 0 ? 'Processed' : 'Pending';

                // Calculate deductions by type (prorated for daily) with cutoff schedule
                $deductions = [];
                foreach ($employee->deductions as $deduction) {
                    $deductionType = $deduction->deductionType;
                    $code = $deductionType->code;

                    if (!$deductionType->deducted_from_employee) {
                        continue;
                    }

                    // Get schedule - prioritize employee's custom schedule over deduction type schedule
                    $cutoffSchedule = 'BOTH_SPLIT'; // Default
                    if ($deduction->custom_cutoff_schedule) {
                        // Use employee-specific custom schedule
                        $cutoffSchedule = $deduction->custom_cutoff_schedule;
                    } else {
                        // Use deduction type's default schedule
                        $schedule = $deductionType->schedules->first();
                        $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                    }

                    // Calculate base deduction amount
                    $deductionAmount = 0;

                    if ($deductionType->category === 'MANDATORY') {
                        if ($deductionType->computation_type === 'PERCENTAGE') {
                            $baseAmount = 0;
                            if ($deductionType->base_salary_type === 'BASIC') {
                                $baseAmount = $record->daily_basic_pay;
                            } elseif ($deductionType->base_salary_type === 'GROSS') {
                                $baseAmount = $record->daily_basic_pay + $record->ot_pay;
                            } elseif ($deductionType->base_salary_type === 'MONTHLY') {
                                $monthlySalary = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                                $baseAmount = $monthlySalary / 22;
                            } else {
                                $baseAmount = $record->daily_basic_pay;
                            }
                            $deductionAmount = $baseAmount * ($deductionType->percentage_rate / 100);
                        } elseif ($deductionType->computation_type === 'FIXED') {
                            $deductionAmount = ($deductionType->percentage_rate ?? $deduction->amount ?? 0) / 22;
                        } else {
                            $deductionAmount = ($deduction->amount ?? 0) / 22;
                        }
                    } elseif ($deductionType->category === 'LOAN') {
                        $deductionAmount = ($deduction->installment_amount ?? 0) / 22;
                    }

                    // Apply cutoff schedule (for daily view, prorate based on cutoff)
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $deductions[$code] = $isCutoff1st ? $deductionAmount : 0;
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $deductions[$code] = $isCutoff1st ? 0 : $deductionAmount;
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        $deductions[$code] = $deductionAmount;
                    } else { // BOTH_SPLIT
                        $deductions[$code] = $deductionAmount / 2;
                    }
                }

                return [
                    'id' => $employee->employee_id ?? 'N/A',
                    'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                    'position' => $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                    'dept' => $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                    'photo' => $employee->photo,
                    'work_date' => $record->work_date,
                    'daily_rate' => $record->daily_rate,
                    'basic' => $record->daily_basic_pay,
                    'ot_pay' => $record->ot_pay,
                    'late_deduction' => $record->late_deduction,
                    'undertime_deduction' => $record->undertime_deduction,
                    'deductions' => $deductions,
                    'status' => $recordStatus,
                    'days_count' => null,
                ];
            });
        }

        // Filter by status if provided
        if ($status) {
            $payrollRecords = $payrollRecords->filter(fn($r) => $r['status'] === $status)->values();
        }

        // Get all unique deduction types from the records
        $deductionTypes = collect();
        foreach ($payrollRecords as $record) {
            if (isset($record['deductions'])) {
                foreach (array_keys($record['deductions']) as $code) {
                    if (!$deductionTypes->contains($code)) {
                        $deductionTypes->push($code);
                    }
                }
            }
        }

        // Get unique departments for filter
        $departments = Department::where('status', 'Active')->pluck('name');

        // Get unique employee names for filter
        $employees = Employee::orderBy('first_name')
            ->get()
            ->map(function($emp) {
                return trim($emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '') . $emp->last_name);
            })
            ->unique()
            ->values();

        return view('admin.payroll.adminPayroll', compact('payrollRecords', 'departments', 'employees', 'viewMode', 'deductionTypes'));
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
                    \Log::info("Successfully saved salary computation ID: {$periodComputation->id} (wasRecentlyCreated: " . ($periodComputation->wasRecentlyCreated ? 'yes' : 'no') . ")");
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to create period computation for {$employee->first_name} {$employee->last_name}: {$e->getMessage()}";
                    \Log::error("Salary computation error for employee {$employee->id}: " . $e->getMessage());
                    \Log::error($e->getTraceAsString());
                }
            }

            DB::commit();

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

    public function exportPayslips(Request $request)
    {
        $status = $request->input('status');

        $query = SalaryComputation::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation'
        ])->orderBy('period_end', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $computations = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=payslips_' . now()->format('Y-m-d') . '.csv',
        ];

        $callback = function () use ($computations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Employee ID',
                'Employee Name',
                'Department',
                'Position',
                'Period Start',
                'Period End',
                'Basic Pay',
                'OT Pay',
                'Late Deduction',
                'Undertime Deduction',
                'Other Deductions',
                'Gross Pay',
                'Net Pay',
                'Status'
            ]);

            foreach ($computations as $comp) {
                fputcsv($file, [
                    $comp->employee->employee_id ?? 'N/A',
                    ($comp->employee->first_name ?? '') . ' ' . ($comp->employee->last_name ?? ''),
                    $comp->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                    $comp->employee->employmentDetail->designationRelation->title ?? 'N/A',
                    $comp->period_start->format('Y-m-d'),
                    $comp->period_end->format('Y-m-d'),
                    number_format($comp->basic_pay, 2),
                    number_format($comp->ot_pay, 2),
                    number_format($comp->late_deduction, 2),
                    number_format($comp->undertime_deduction, 2),
                    number_format($comp->other_deductions, 2),
                    number_format($comp->gross_pay, 2),
                    number_format($comp->net_pay, 2),
                    ucfirst($comp->status)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

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

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $payDate = $request->input('pay_date');
        $department = $request->input('department');
        $employmentStatus = $request->input('employment_status');

        // Get employees based on filters
        $employeesQuery = Employee::with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
            'deductions' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'ACTIVE')
                  ->where('start_date', '<=', $endDate)
                  ->where(function($query) use ($endDate) {
                      $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $endDate);
                  })
                  ->with('deductionType');
            }
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

        // Get all unique deduction types (only employee shares)
        $deductionTypeCodes = [];
        $deductionTypeNames = [];
        foreach ($employees as $employee) {
            foreach ($employee->deductions as $deduction) {
                // Skip employer/government shares (only show employee shares in export)
                if (!$deduction->deductionType->deducted_from_employee) {
                    continue;
                }

                $code = $deduction->deductionType->code;
                if (!in_array($code, $deductionTypeCodes)) {
                    $deductionTypeCodes[] = $code;
                    $deductionTypeNames[$code] = $deduction->deductionType->name;
                }
            }
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=payroll_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '.csv',
        ];

        $callback = function () use ($employees, $startDate, $endDate, $payDate, $deductionTypeCodes, $deductionTypeNames) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Header rows
            fputcsv($file, ['MUNICIPAL GOVERNMENT OF PAGSANJAN']);
            fputcsv($file, ['PAYROLL REGISTER']);
            fputcsv($file, ['Period: ' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate))]);
            fputcsv($file, ['Pay Date: ' . date('M d, Y', strtotime($payDate))]);
            fputcsv($file, []); // Empty row

            // Column headers
            $columnHeaders = [
                'No.',
                'Employee Name',
                'Position',
                'Department',
                'Days Worked',
                'Daily Rate',
                'Basic Pay',
                'OT Pay',
                'Late Deduction',
                'Undertime Deduction',
            ];

            // Add deduction type columns
            foreach ($deductionTypeCodes as $code) {
                $columnHeaders[] = $deductionTypeNames[$code];
            }

            $columnHeaders[] = 'Total Deductions';
            $columnHeaders[] = 'Net Pay';

            fputcsv($file, $columnHeaders);

            $totals = [
                'basic_pay' => 0,
                'ot_pay' => 0,
                'late' => 0,
                'undertime' => 0,
                'deductions' => array_fill_keys($deductionTypeCodes, 0),
                'total_deductions' => 0,
                'net_pay' => 0
            ];

            $rowNum = 1;
            foreach ($employees as $employee) {
                $computations = DailySalaryComputation::where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$startDate, $endDate])
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

                // Calculate deductions by type
                $deductions = array_fill_keys($deductionTypeCodes, 0);
                foreach ($employee->deductions as $deduction) {
                    // Skip employer/government shares (only deduct employee shares)
                    if (!$deduction->deductionType->deducted_from_employee) {
                        continue;
                    }

                    $code = $deduction->deductionType->code;
                    if ($deduction->deductionType->category === 'MANDATORY') {
                        if ($deduction->deductionType->computation_type === 'PERCENTAGE') {
                            $baseAmount = 0;

                            // Determine base amount based on base_salary_type
                            if ($deduction->deductionType->base_salary_type === 'BASIC') {
                                $baseAmount = $basicPay;
                            } elseif ($deduction->deductionType->base_salary_type === 'GROSS') {
                                $baseAmount = $basicPay + $otPay;
                            } elseif ($deduction->deductionType->base_salary_type === 'MONTHLY') {
                                // Get monthly salary from designation
                                $baseAmount = $employee->employmentDetail?->designationRelation?->monthly_rate ?? 0;
                            } else {
                                $baseAmount = $basicPay; // Default to basic
                            }

                            $deductions[$code] = $baseAmount * ($deduction->deductionType->percentage_rate / 100);
                        } elseif ($deduction->deductionType->computation_type === 'FIXED') {
                            // For FIXED, use percentage_rate column (which stores the fixed amount)
                            $deductions[$code] = $deduction->deductionType->percentage_rate ?? $deduction->amount ?? 0;
                        } else {
                            $deductions[$code] = $deduction->amount ?? 0;
                        }
                    } elseif ($deduction->deductionType->category === 'LOAN') {
                        $deductions[$code] = $deduction->installment_amount ?? 0;
                    }
                }

                $totalDeductions = $lateDeduction + $undertimeDeduction + array_sum($deductions);
                $netPay = $basicPay + $otPay - $totalDeductions;

                $totals['basic_pay'] += $basicPay;
                $totals['ot_pay'] += $otPay;
                $totals['late'] += $lateDeduction;
                $totals['undertime'] += $undertimeDeduction;
                foreach ($deductionTypeCodes as $code) {
                    $totals['deductions'][$code] += $deductions[$code];
                }
                $totals['total_deductions'] += $totalDeductions;
                $totals['net_pay'] += $netPay;

                $rowData = [
                    $rowNum++,
                    trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
                    $employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                    $employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                    $daysWorked,
                    number_format($dailyRate, 2),
                    number_format($basicPay, 2),
                    number_format($otPay, 2),
                    number_format($lateDeduction, 2),
                    number_format($undertimeDeduction, 2),
                ];

                // Add deduction amounts
                foreach ($deductionTypeCodes as $code) {
                    $rowData[] = number_format($deductions[$code], 2);
                }

                $rowData[] = number_format($totalDeductions, 2);
                $rowData[] = number_format($netPay, 2);

                fputcsv($file, $rowData);
            }

            // Total row
            $totalRow = [
                '',
                '',
                '',
                '',
                '',
                'TOTAL:',
                number_format($totals['basic_pay'], 2),
                number_format($totals['ot_pay'], 2),
                number_format($totals['late'], 2),
                number_format($totals['undertime'], 2),
            ];

            foreach ($deductionTypeCodes as $code) {
                $totalRow[] = number_format($totals['deductions'][$code], 2);
            }

            $totalRow[] = number_format($totals['total_deductions'], 2);
            $totalRow[] = number_format($totals['net_pay'], 2);

            fputcsv($file, $totalRow);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
