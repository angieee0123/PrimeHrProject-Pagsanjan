<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveAccrualRatesSeeder extends Seeder
{
    public function run(): void
    {
        $accrualRates = [
            // Vacation Leave - Official CSC Standard (0.042 per day)
            [
                'leave_code' => 'VL',
                'days_of_service_required' => 1.00,
                'credits_earned_per_period' => 0.0420, // Official CSC rate
                'accrual_frequency' => 'daily',
                'effective_date' => '2024-01-01',
                'end_date' => null,
                'is_active' => true,
                'notes' => 'Official CSC Accrual Table: 0.042 credits per day of service. 30 days = 1.25 credits (15 days annually).',
            ],
            // Sick Leave - Official CSC Standard (0.042 per day)
            [
                'leave_code' => 'SL',
                'days_of_service_required' => 1.00,
                'credits_earned_per_period' => 0.0420, // Official CSC rate
                'accrual_frequency' => 'daily',
                'effective_date' => '2024-01-01',
                'end_date' => null,
                'is_active' => true,
                'notes' => 'Official CSC Accrual Table: 0.042 credits per day of service. 30 days = 1.25 credits (15 days annually).',
            ],
            // Alternative Monthly Calculation for VL
            [
                'leave_code' => 'VL',
                'days_of_service_required' => 30.00,
                'credits_earned_per_period' => 1.2500,
                'accrual_frequency' => 'monthly',
                'effective_date' => '2024-01-01',
                'end_date' => null,
                'is_active' => false,
                'notes' => 'Alternative calculation: 1.25 days earned per month of service (30 days).',
            ],
            // Alternative Monthly Calculation for SL
            [
                'leave_code' => 'SL',
                'days_of_service_required' => 30.00,
                'credits_earned_per_period' => 1.2500,
                'accrual_frequency' => 'monthly',
                'effective_date' => '2024-01-01',
                'end_date' => null,
                'is_active' => false,
                'notes' => 'Alternative calculation: 1.25 days earned per month of service (30 days).',
            ],
        ];

        foreach ($accrualRates as $rate) {
            DB::table('leave_accrual_rates')->insert(array_merge($rate, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
