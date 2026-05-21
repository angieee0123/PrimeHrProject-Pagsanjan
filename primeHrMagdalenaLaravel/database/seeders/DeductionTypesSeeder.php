<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeductionTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Insert Deduction Types
        $deductionTypes = [
            [
                'code' => 'GSIS',
                'name' => 'GSIS Contribution',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 9.00,
                'base_salary_type' => 'BASIC',
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PHILHEALTH',
                'name' => 'PhilHealth Contribution',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.50,
                'base_salary_type' => 'BASIC',
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'PAGIBIG',
                'name' => 'Pag-IBIG Contribution',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.00,
                'base_salary_type' => 'BASIC',
                'max_amount' => 100.00,
                'is_active' => true,
            ],
            [
                'code' => 'WTAX',
                'name' => 'Withholding Tax',
                'category' => 'MANDATORY',
                'computation_type' => 'CUSTOM',
                'percentage_rate' => null,
                'base_salary_type' => 'CUSTOM',
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_GSIS_SALARY',
                'name' => 'GSIS Salary Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_GSIS_POLICY',
                'name' => 'GSIS Policy Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_PAGIBIG_MPL',
                'name' => 'Pag-IBIG Multi-Purpose Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_PAGIBIG_HOUSING',
                'name' => 'Pag-IBIG Housing Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
            ],
        ];

        foreach ($deductionTypes as $type) {
            DB::table('deduction_types')->insert(array_merge($type, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert Default Deduction Schedules
        $schedules = [
            ['deduction_type_id' => 1, 'cutoff_schedule' => '1ST_ONLY', 'priority_order' => 1], // GSIS
            ['deduction_type_id' => 2, 'cutoff_schedule' => '1ST_ONLY', 'priority_order' => 2], // PhilHealth
            ['deduction_type_id' => 3, 'cutoff_schedule' => '2ND_ONLY', 'priority_order' => 3], // Pag-IBIG
            ['deduction_type_id' => 4, 'cutoff_schedule' => 'BOTH_SPLIT', 'priority_order' => 4], // Withholding Tax
        ];

        foreach ($schedules as $schedule) {
            DB::table('deduction_schedules')->insert(array_merge($schedule, [
                'is_active' => true,
                'effective_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
