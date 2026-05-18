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

        return view('permanent.payslip.permanentPayslip', compact('payslips', 'latestPayslip', 'stats'));
    }
}
