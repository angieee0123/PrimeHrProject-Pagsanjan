<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeductionTypesSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Deduction Types
        |--------------------------------------------------------------------------
        */

        $deductionTypes = [
            [
                'code' => 'PhilHeath PS',
                'name' => 'PhilHealth Personal Share',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.50,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'PhilHeath GS',
                'name' => 'PhilHealth Government Share',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.50,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => false,
            ],

            [
                'code' => 'GSIS PS',
                'name' => 'GSIS Personal Share',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 9.00,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'GSIS GS',
                'name' => 'GSIS Government Share',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 12.00,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => false,
            ],

            [
                'code' => 'GSIS-SI',
                'name' => 'GSIS State Insurance',
                'category' => 'MANDATORY',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => 100.00,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'PAG-IBIG PS',
                'name' => 'PAG-IBIG PERSONAL SHARE',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.00,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'PAG-IBIG GS',
                'name' => 'PAG-IBIG GOVERNMENT SHARE',
                'category' => 'MANDATORY',
                'computation_type' => 'PERCENTAGE',
                'percentage_rate' => 2.00,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => false,
            ],

            [
                'code' => 'LOAN_GSIS_EMERGENCY_LOAN',
                'name' => 'GSIS EMERGENCY LOAN - Emergency Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'LOAN_gsis EL',
                'name' => 'Emergency Loan',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],

            [
                'code' => 'LOAN_MPL',
                'name' => 'MP LOAN',
                'category' => 'LOAN',
                'computation_type' => 'FIXED',
                'percentage_rate' => null,
                'base_salary_type' => null,
                'max_amount' => null,
                'is_active' => true,
                'deducted_from_employee' => true,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Insert / Update Deduction Types
        |--------------------------------------------------------------------------
        */

        foreach ($deductionTypes as $type) {
            DB::table('deduction_types')->updateOrInsert(
                [
                    'code' => $type['code'],
                ],
                array_merge($type, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Default Deduction Schedules
        |--------------------------------------------------------------------------
        |
        | We look up the deduction type ID by its code instead of
        | hardcoding IDs.
        |
        */

        $schedules = [
            [
                'code' => 'GSIS PS',
                'cutoff_schedule' => '1ST_ONLY',
                'priority_order' => 1,
            ],

            [
                'code' => 'PhilHeath PS',
                'cutoff_schedule' => '1ST_ONLY',
                'priority_order' => 2,
            ],

            [
                'code' => 'PAG-IBIG PS',
                'cutoff_schedule' => '2ND_ONLY',
                'priority_order' => 3,
            ],
        ];

        foreach ($schedules as $schedule) {
            $deductionTypeId = DB::table('deduction_types')
                ->where('code', $schedule['code'])
                ->value('id');

            if (!$deductionTypeId) {
                continue;
            }

            DB::table('deduction_schedules')->updateOrInsert(
                [
                    'deduction_type_id' => $deductionTypeId,
                ],
                [
                    'cutoff_schedule' => $schedule['cutoff_schedule'],
                    'priority_order' => $schedule['priority_order'],
                    'is_active' => true,
                    'effective_date' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}