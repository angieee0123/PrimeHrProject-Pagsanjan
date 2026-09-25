<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoanTypesSeeder extends Seeder
{
    public function run(): void
    {
        $loanTypes = [
            [
                'id' => 8,
                'code' => 'gsis EL',
                'name' => 'Emergency Loan',
                'deduction_type_id' => 26,
                'max_loanable_amount' => null,
                'interest_rate' => null,
                'max_terms_months' => null,
                'is_active' => 1,
                'created_at' => '2026-05-17 08:15:22',
                'updated_at' => '2026-05-17 08:15:22',
            ],
            [
                'id' => 9,
                'code' => 'MPL',
                'name' => 'MP LOAN',
                'deduction_type_id' => 27,
                'max_loanable_amount' => null,
                'interest_rate' => null,
                'max_terms_months' => null,
                'is_active' => 1,
                'created_at' => '2026-05-17 08:17:03',
                'updated_at' => '2026-05-17 08:17:03',
            ],
        ];

        foreach ($loanTypes as $loanData) {
            DB::table('loan_types')->updateOrInsert(
                ['id' => $loanData['id']],
                $loanData
            );
        }

        $this->command->info('✓ Seeded ' . count($loanTypes) . ' loan types');
    }
}