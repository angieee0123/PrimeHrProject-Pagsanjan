<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveAccrualRatesSeeder extends Seeder
{
    public function run(): void
    {
        $accrualRates = [
            [
                'id' => 1,
                'leave_type_id' => 19,
                'days_of_service_required' => 30.00,
                'credits_earned_per_period' => 1.2500,
                'accrual_frequency' => 'monthly',
                'effective_date' => '2026-05-11',
                'end_date' => null,
                'is_active' => 1,
                'notes' => null,
                'created_at' => '2026-05-11 13:44:17',
                'updated_at' => '2026-05-11 13:44:17',
            ],
            [
                'id' => 2,
                'leave_type_id' => 11,
                'days_of_service_required' => 30.00,
                'credits_earned_per_period' => 1.2500,
                'accrual_frequency' => 'monthly',
                'effective_date' => '2026-05-11',
                'end_date' => null,
                'is_active' => 1,
                'notes' => null,
                'created_at' => '2026-05-11 13:44:17',
                'updated_at' => '2026-05-11 13:44:17',
            ],
        ];

        foreach ($accrualRates as $rate) {
            DB::table('leave_accrual_rates')->updateOrInsert(
                ['id' => $rate['id']],
                $rate
            );
        }
    }
}