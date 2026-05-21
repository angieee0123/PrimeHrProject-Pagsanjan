<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailySalaryComputation;
use App\Models\SalaryComputation;
use App\Models\Attendance;
use App\Models\AccreditedHoursLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
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
}
