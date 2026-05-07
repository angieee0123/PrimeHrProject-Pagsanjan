<?php

namespace App\Http\Controllers;

use App\Models\SalaryComputation;
use App\Models\DailySalaryComputation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryComputationController extends Controller
{
    /**
     * Display daily salary computations
     */
    public function dailyIndex(Request $request)
    {
        $query = DailySalaryComputation::with(['employee', 'attendance']);
        
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('work_date', [$request->date_from, $request->date_to]);
        }
        
        $dailyComputations = $query->orderBy('work_date', 'desc')->paginate(20);
        
        return view('admin.salary.daily', compact('dailyComputations'));
    }
    
    /**
     * Display period salary computations
     */
    public function periodIndex(Request $request)
    {
        $query = SalaryComputation::with(['employee', 'computedBy', 'approvedBy']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $salaryComputations = $query->orderBy('period_start', 'desc')->paginate(20);
        
        return view('admin.salary.period', compact('salaryComputations'));
    }
    
    /**
     * Compute period salary
     */
    public function computePeriod(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'payroll_type' => 'required|in:monthly,semi-monthly,weekly',
        ]);
        
        $computation = SalaryComputation::computePeriod(
            $request->employee_id,
            $request->period_start,
            $request->period_end,
            $request->payroll_type,
            Auth::id()
        );
        
        return redirect()->route('admin.salary.period')
            ->with('success', 'Salary computed successfully');
    }
    
    /**
     * Compute salary for all employees in a period
     */
    public function computeAllPeriod(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'payroll_type' => 'required|in:monthly,semi-monthly,weekly',
        ]);
        
        $employees = Employee::whereHas('employmentDetail')->get();
        
        foreach ($employees as $employee) {
            SalaryComputation::computePeriod(
                $employee->id,
                $request->period_start,
                $request->period_end,
                $request->payroll_type,
                Auth::id()
            );
        }
        
        return redirect()->route('admin.salary.period')
            ->with('success', "Salary computed for {$employees->count()} employees");
    }
    
    /**
     * Approve salary computation
     */
    public function approve($id)
    {
        $computation = SalaryComputation::findOrFail($id);
        $computation->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);
        
        return back()->with('success', 'Salary approved successfully');
    }
    
    /**
     * Mark salary as paid
     */
    public function markPaid($id)
    {
        $computation = SalaryComputation::findOrFail($id);
        $computation->update(['status' => 'paid']);
        
        return back()->with('success', 'Salary marked as paid');
    }
}
