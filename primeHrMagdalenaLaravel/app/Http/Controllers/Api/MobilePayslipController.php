<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalaryComputation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobilePayslipController extends Controller
{
    /**
     * Payslip stats and history for the authenticated employee.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $payslips = SalaryComputation::where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc')
            ->get();

        $latestPayslip = $payslips->first();

        $totalDeductions = 0;
        if ($latestPayslip) {
            $totalDeductions = ($latestPayslip->late_deduction ?? 0)
                + ($latestPayslip->undertime_deduction ?? 0)
                + ($latestPayslip->other_deductions ?? 0);
        }

        $latestPeriodLabel = null;
        if ($latestPayslip) {
            $latestPeriodLabel = $latestPayslip->period_start->format('M d')
                . '-' . $latestPayslip->period_end->format('d, Y');
        }

        $stats = [
            'latest_net_pay' => (float) ($latestPayslip->net_pay ?? 0),
            'basic_pay' => (float) ($latestPayslip->basic_pay ?? 0),
            'total_deductions' => (float) $totalDeductions,
            'total_payslips' => $payslips->count(),
            'latest_period_label' => $latestPeriodLabel,
        ];

        $items = $payslips->map(function (SalaryComputation $payslip) {
            $deductions = ($payslip->late_deduction ?? 0)
                + ($payslip->undertime_deduction ?? 0)
                + ($payslip->other_deductions ?? 0);

            return [
                'id' => $payslip->id,
                'period_label' => $payslip->period_start->format('M d')
                    . '-' . $payslip->period_end->format('d, Y'),
                'period_start' => $payslip->period_start->format('Y-m-d'),
                'period_end' => $payslip->period_end->format('Y-m-d'),
                'pay_date' => $payslip->pay_date
                    ? $payslip->pay_date->format('Y-m-d')
                    : $payslip->period_end->format('Y-m-d'),
                'basic_pay' => (float) $payslip->basic_pay,
                'total_deductions' => (float) $deductions,
                'gross_pay' => (float) ($payslip->gross_pay ?? ($payslip->basic_pay + ($payslip->ot_pay ?? 0))),
                'net_pay' => (float) $payslip->net_pay,
                'status' => $payslip->status,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'payslips' => $items,
            ],
        ]);
    }

    /**
     * Full payslip details for modal / detail view.
     */
    public function show(int $id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $payslip = SalaryComputation::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation',
            ])
            ->first();

        if (!$payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found',
            ], 404);
        }

        $deductionBreakdown = $payslip->deduction_breakdown;
        if (is_string($deductionBreakdown)) {
            $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
        } elseif (!is_array($deductionBreakdown)) {
            $deductionBreakdown = [];
        }

        $totalDeductions = ($payslip->late_deduction ?? 0)
            + ($payslip->undertime_deduction ?? 0)
            + ($payslip->other_deductions ?? 0);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payslip->id,
                'employee_name' => trim(
                    $payslip->employee->first_name . ' ' . $payslip->employee->last_name
                ),
                'employee_id' => $payslip->employee->employee_id,
                'department' => $payslip->employee->employmentDetail?->departmentRelation?->name ?? 'N/A',
                'position' => $payslip->employee->employmentDetail?->designationRelation?->title ?? 'N/A',
                'period_label' => $payslip->period_start->format('M d, Y')
                    . ' - ' . $payslip->period_end->format('M d, Y'),
                'period_start' => $payslip->period_start->format('Y-m-d'),
                'period_end' => $payslip->period_end->format('Y-m-d'),
                'pay_date' => ($payslip->pay_date ?? $payslip->period_end)->format('Y-m-d'),
                'monthly_rate' => (float) (
                    $payslip->monthly_rate
                    ?? $payslip->employee->employmentDetail?->designationRelation?->monthly_rate
                    ?? 0
                ),
                'daily_rate' => (float) ($payslip->daily_rate ?? 0),
                'total_days_present' => (int) ($payslip->total_days_present ?? 0),
                'basic_pay' => (float) $payslip->basic_pay,
                'ot_pay' => (float) ($payslip->ot_pay ?? 0),
                'gross_pay' => (float) (
                    $payslip->gross_pay ?? ($payslip->basic_pay + ($payslip->ot_pay ?? 0))
                ),
                'late_deduction' => (float) ($payslip->late_deduction ?? 0),
                'undertime_deduction' => (float) ($payslip->undertime_deduction ?? 0),
                'other_deductions' => (float) ($payslip->other_deductions ?? 0),
                'deduction_breakdown' => $deductionBreakdown,
                'total_deductions' => (float) $totalDeductions,
                'net_pay' => (float) $payslip->net_pay,
                'status' => $payslip->status,
                'notes' => $payslip->notes,
            ],
        ]);
    }
}
