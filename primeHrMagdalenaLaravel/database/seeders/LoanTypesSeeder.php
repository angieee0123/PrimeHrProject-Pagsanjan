<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeductionType;
use App\Models\LoanType;

class LoanTypesSeeder extends Seeder
{
    public function run(): void
    {
        $loanTypes = [
            [
                'provider' => 'GSIS',
                'code' => 'GSIS_SALARY',
                'name' => 'GSIS Salary Loan',
                'max_loanable_amount' => 100000.00,
                'interest_rate' => 6.00,
                'max_terms_months' => 36,
            ],
            [
                'provider' => 'GSIS',
                'code' => 'GSIS_POLICY',
                'name' => 'GSIS Policy Loan',
                'max_loanable_amount' => 50000.00,
                'interest_rate' => 6.00,
                'max_terms_months' => 24,
            ],
            [
                'provider' => 'GSIS',
                'code' => 'GSIS_EMERGENCY',
                'name' => 'GSIS Emergency Loan',
                'max_loanable_amount' => 20000.00,
                'interest_rate' => 6.00,
                'max_terms_months' => 12,
            ],
            [
                'provider' => 'PAGIBIG',
                'code' => 'PAGIBIG_MPL',
                'name' => 'Pag-IBIG Multi-Purpose Loan',
                'max_loanable_amount' => 80000.00,
                'interest_rate' => 10.50,
                'max_terms_months' => 24,
            ],
            [
                'provider' => 'PAGIBIG',
                'code' => 'PAGIBIG_CALAMITY',
                'name' => 'Pag-IBIG Calamity Loan',
                'max_loanable_amount' => 40000.00,
                'interest_rate' => 5.95,
                'max_terms_months' => 24,
            ],
        ];

        foreach ($loanTypes as $loanData) {
            // Create deduction type first
            $deductionType = DeductionType::create([
                'code' => 'LOAN_' . $loanData['code'],
                'name' => $loanData['name'],
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => $loanData['interest_rate'],
                'max_amount' => $loanData['max_loanable_amount'],
                'is_active' => true,
            ]);

            // Create loan type linked to deduction type
            LoanType::create([
                'code' => $loanData['code'],
                'name' => $loanData['name'],
                'deduction_type_id' => $deductionType->id,
                'max_loanable_amount' => $loanData['max_loanable_amount'],
                'interest_rate' => $loanData['interest_rate'],
                'max_terms_months' => $loanData['max_terms_months'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ Seeded ' . count($loanTypes) . ' default loan types');
    }
}
