<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SalaryComputation;
use App\Models\User;

class PermanentPayslipController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return view('permanent.payslip.permanentPayslip', [
                'payslips' => collect(),
                'latestPayslip' => null,
                'stats' => [
                    'latest_net_pay' => 0,
                    'basic_pay' => 0,
                    'total_deductions' => 0,
                    'total_payslips' => 0
                ]
            ]);
        }

        // Load employee relationships for topbar
        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $payslips = SalaryComputation::where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc')
            ->paginate(5);

        $latestPayslip = SalaryComputation::where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc')
            ->first();

        $stats = [
            'latest_net_pay' => $latestPayslip->net_pay ?? 0,
            'basic_pay' => $latestPayslip->basic_pay ?? 0,
            'total_deductions' => ($latestPayslip->late_deduction ?? 0) + ($latestPayslip->undertime_deduction ?? 0) + ($latestPayslip->other_deductions ?? 0),
            'total_payslips' => SalaryComputation::where('employee_id', $employee->id)->count()
        ];

        return view('permanent.payslip.permanentPayslip', compact('employee', 'payslips', 'latestPayslip', 'stats'));
    }

    public function getPayslipDetails($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $payslip = SalaryComputation::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with(['employee.employmentDetail.departmentRelation', 'employee.employmentDetail.designationRelation'])
            ->first();

        if (!$payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found'
            ], 404);
        }

        // Parse deduction_breakdown if it's a string
        $deductionBreakdown = $payslip->deduction_breakdown;
        if (is_string($deductionBreakdown)) {
            $deductionBreakdown = json_decode($deductionBreakdown, true) ?? [];
        } elseif (!is_array($deductionBreakdown)) {
            $deductionBreakdown = [];
        }

        return response()->json([
            'success' => true,
            'payslip' => [
                'employee_name' => $payslip->employee->first_name . ' ' . $payslip->employee->last_name,
                'employee_id' => $payslip->employee->employee_id,
                'department' => $payslip->employee->employmentDetail->departmentRelation->name ?? 'N/A',
                'position' => $payslip->employee->employmentDetail->designationRelation->title ?? 'N/A',
                'period' => $payslip->period_start->format('M d, Y') . ' - ' . $payslip->period_end->format('M d, Y'),
                'pay_date' => $payslip->period_end->format('M d, Y'),
                'monthly_rate' => $payslip->monthly_rate ?? ($payslip->employee->employmentDetail->designationRelation->monthly_rate ?? 0),
                'daily_rate' => $payslip->daily_rate ?? 0,
                'total_days_present' => $payslip->total_days_present ?? 0,
                'basic_pay' => $payslip->basic_pay,
                'ot_pay' => $payslip->ot_pay ?? 0,
                'gross_pay' => $payslip->gross_pay ?? ($payslip->basic_pay + ($payslip->ot_pay ?? 0)),
                'late_deduction' => $payslip->late_deduction ?? 0,
                'undertime_deduction' => $payslip->undertime_deduction ?? 0,
                'other_deductions' => $payslip->other_deductions ?? 0,
                'deduction_breakdown' => $deductionBreakdown,
                'total_deductions' => ($payslip->late_deduction ?? 0) + ($payslip->undertime_deduction ?? 0) + ($payslip->other_deductions ?? 0),
                'net_pay' => $payslip->net_pay,
                'status' => $payslip->status,
                'notes' => $payslip->notes ?? null
            ]
        ]);
    }
}
