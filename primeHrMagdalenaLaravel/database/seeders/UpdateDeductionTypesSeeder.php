<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DeductionType;

class UpdateDeductionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Updates existing deduction types to correctly set the deducted_from_employee flag.
     * Government/Employer shares should NOT be deducted from employee salary.
     */
    public function run(): void
    {
        $this->command->info('Updating deduction types...');

        // Government shares - NOT deducted from employee (employer pays)
        $governmentShares = [
            'PhilHeath GS',
            'GSIS GS',
            'PAG-IBIG GS',
        ];

        $updated = DeductionType::whereIn('code', $governmentShares)
            ->update(['deducted_from_employee' => false]);
        
        $this->command->info("✓ Updated {$updated} government share(s) to NOT be deducted from employee");

        // Employee shares - SHOULD be deducted from employee
        $employeeShares = [
            'PhilHeath PS',
            'GSIS PS',
            'GSIS-SI',
            'PAG-IBIG PS',
        ];

        $updated = DeductionType::whereIn('code', $employeeShares)
            ->update(['deducted_from_employee' => true]);
        
        $this->command->info("✓ Updated {$updated} employee share(s) to be deducted from employee");

        // All loans should be deducted from employee
        $updated = DeductionType::where('category', 'LOAN')
            ->update(['deducted_from_employee' => true]);
        
        $this->command->info("✓ Updated {$updated} loan type(s) to be deducted from employee");

        $this->command->info('✅ Deduction types updated successfully!');
    }
}
