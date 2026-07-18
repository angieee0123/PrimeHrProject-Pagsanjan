<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeductionType;
use App\Models\LoanType;
use App\Models\EmployeeDeduction;

class LoanTypeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'provider' => 'required|string',
            'code' => 'required|string|max:50|unique:deduction_types,code',
            'name' => 'required|string|max:100',
            'max_loanable_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'max_terms_months' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        // Create the deduction type for this loan
        $deductionType = DeductionType::create([
            'code' => 'LOAN_' . $data['code'],
            'name' => $data['name'],
            'category' => 'LOAN',
            'computation_type' => 'FIXED',
            'percentage_rate' => $data['interest_rate'] ?? null,
            'max_amount' => $data['max_loanable_amount'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        // Create the loan type record (optional, for additional metadata)
        LoanType::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'deduction_type_id' => $deductionType->id,
            'max_loanable_amount' => $data['max_loanable_amount'] ?? null,
            'interest_rate' => $data['interest_rate'] ?? null,
            'max_terms_months' => $data['max_terms_months'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        return redirect()->route('admin.deductions')
            ->with('success', "Loan type \"{$data['name']}\" registered successfully! It's now available for assignment to employees.");
    }

    public function update(Request $request, $id)
    {
        $deductionType = DeductionType::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'max_loanable_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'max_terms_months' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        // Update deduction type
        $deductionType->update([
            'name' => $data['name'],
            'percentage_rate' => $data['interest_rate'] ?? null,
            'max_amount' => $data['max_loanable_amount'] ?? null,
            'is_active' => $data['is_active'],
        ]);

        // Update loan type record if exists
        $loanType = LoanType::where('deduction_type_id', $id)->first();
        if ($loanType) {
            $loanType->update([
                'name' => $data['name'],
                'max_loanable_amount' => $data['max_loanable_amount'] ?? null,
                'interest_rate' => $data['interest_rate'] ?? null,
                'max_terms_months' => $data['max_terms_months'] ?? null,
                'is_active' => $data['is_active'],
            ]);
        }

        return redirect()->route('admin.deductions')
            ->with('success', "Loan type \"{$data['name']}\" updated successfully!");
    }

    public function destroy($id)
    {
        $deductionType = DeductionType::findOrFail($id);

        // Check if any employees are using this loan type
        $employeesCount = EmployeeDeduction::where('deduction_type_id', $id)
            ->where('status', 'ACTIVE')
            ->count();

        if ($employeesCount > 0) {
            return redirect()->route('admin.deductions')
                ->with('error', "Cannot delete loan type \"{$deductionType->name}\" because it's currently assigned to {$employeesCount} employee(s).");
        }

        // Delete the loan type record first
        LoanType::where('deduction_type_id', $id)->delete();

        // Then delete the deduction type
        $deductionType->delete();

        return redirect()->route('admin.deductions')
            ->with('success', "Loan type \"{$deductionType->name}\" deleted successfully.");
    }
}
