<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get existing data before modification
        $existingRates = DB::table('leave_accrual_rates')->get();
        
        // Store data temporarily
        $dataToRestore = [];
        foreach ($existingRates as $rate) {
            $leaveType = DB::table('leave_types_config')
                ->where('leave_code', $rate->leave_code)
                ->first();
            
            if ($leaveType) {
                $dataToRestore[] = [
                    'leave_type_id' => $leaveType->id,
                    'days_of_service_required' => $rate->days_of_service_required,
                    'credits_earned_per_period' => $rate->credits_earned_per_period,
                    'accrual_frequency' => $rate->accrual_frequency,
                    'effective_date' => $rate->effective_date,
                    'end_date' => $rate->end_date,
                    'is_active' => $rate->is_active,
                    'notes' => $rate->notes,
                    'created_at' => $rate->created_at,
                    'updated_at' => $rate->updated_at,
                ];
            }
        }
        
        // Clear the table
        DB::table('leave_accrual_rates')->truncate();
        
        // Insert data with new structure
        if (!empty($dataToRestore)) {
            DB::table('leave_accrual_rates')->insert($dataToRestore);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get existing data
        $existingRates = DB::table('leave_accrual_rates')->get();
        
        // Store data temporarily
        $dataToRestore = [];
        foreach ($existingRates as $rate) {
            $leaveType = DB::table('leave_types_config')
                ->where('id', $rate->leave_type_id)
                ->first();
            
            if ($leaveType) {
                $dataToRestore[] = [
                    'leave_code' => $leaveType->leave_code,
                    'days_of_service_required' => $rate->days_of_service_required,
                    'credits_earned_per_period' => $rate->credits_earned_per_period,
                    'accrual_frequency' => $rate->accrual_frequency,
                    'effective_date' => $rate->effective_date,
                    'end_date' => $rate->end_date,
                    'is_active' => $rate->is_active,
                    'notes' => $rate->notes,
                    'created_at' => $rate->created_at,
                    'updated_at' => $rate->updated_at,
                ];
            }
        }
        
        // Clear the table
        DB::table('leave_accrual_rates')->truncate();
        
        // Insert data with old structure
        if (!empty($dataToRestore)) {
            DB::table('leave_accrual_rates')->insert($dataToRestore);
        }
    }
};
