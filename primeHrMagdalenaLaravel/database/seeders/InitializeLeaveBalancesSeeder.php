<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Carbon\Carbon;

class InitializeLeaveBalancesSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = Carbon::now()->year;
        $employees = Employee::with('employmentDetail')->get();

        foreach ($employees as $employee) {
            if (!$employee->employmentDetail) {
                continue;
            }

            $employmentStatus = strtolower($employee->employmentDetail->employment_status ?? '');
            $appointmentDate = $employee->employmentDetail->appointment_date 
                ? Carbon::parse($employee->employmentDetail->appointment_date) 
                : null;

            // Get all active leave types
            $leaveTypes = LeaveType::where('is_active', true)->get();

            foreach ($leaveTypes as $leaveType) {
                $credits = $this->calculateCredits(
                    $leaveType, 
                    $employmentStatus, 
                    $appointmentDate, 
                    $currentYear
                );

                if ($credits !== null) {
                    LeaveBalance::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'leave_code' => $leaveType->leave_code,
                            'year' => $currentYear,
                        ],
                        [
                            'total_credits' => $credits,
                            'used_credits' => 0,
                            'pending_credits' => 0,
                            'available_credits' => $credits,
                            'carried_over' => 0,
                        ]
                    );
                }
            }
        }
    }

    private function calculateCredits($leaveType, $employmentStatus, $appointmentDate, $year)
    {
        // Permanent employees get full benefits
        if (str_contains($employmentStatus, 'permanent')) {
            return $this->getCreditsForPermanent($leaveType, $appointmentDate, $year);
        }
        
        // Contractual employees (limited benefits)
        if (str_contains($employmentStatus, 'contractual') || str_contains($employmentStatus, 'contract')) {
            return $this->getCreditsForContractual($leaveType);
        }
        
        // Casual employees (limited benefits)
        if (str_contains($employmentStatus, 'casual')) {
            return $this->getCreditsForCasual($leaveType);
        }

        return null;
    }

    private function getCreditsForPermanent($leaveType, $appointmentDate, $year)
    {
        $code = $leaveType->leave_code;
        
        // Check if employee has served 6 months for leaves that require it
        $hasServed6Months = $this->hasServedSixMonths($appointmentDate);
        
        // Accrued leaves (VL, SL) - calculate based on months served
        if ($leaveType->is_accrued) {
            if ($appointmentDate) {
                $monthsServed = $this->getMonthsServedInYear($appointmentDate, $year);
                $accruedCredits = round($monthsServed * 1.25, 2); // 1.25 days per month
                
                // VL requires 6 months before it can be USED (but still accrues)
                // SL can be used immediately as earned
                return $accruedCredits;
            }
            return $leaveType->annual_limit; // Full 15 days if no appointment date
        }

        // Fixed allocation leaves - check 6-month requirement
        switch ($code) {
            case 'SPL': // Special Privilege Leave - REQUIRES 6 MONTHS
                return $hasServed6Months ? 3.00 : 0.00;
            case 'SOPL': // Solo Parent Leave - REQUIRES 6 MONTHS (RA 11861)
                return $hasServed6Months ? 7.00 : 0.00;
            case 'ML': // Maternity Leave - IMMEDIATE
            case 'MLE': // Maternity Leave Extension - IMMEDIATE
            case 'PL': // Paternity Leave - IMMEDIATE
            case 'VAWC': // VAWC Leave - IMMEDIATE (Emergency)
            case 'SLBW': // Special Leave Benefits for Women
            case 'MCL': // Magna Carta Leave
            case 'BL': // Bereavement Leave
            case 'FL': // Forced Leave
            case 'WL': // Wellness Leave
            case 'AL': // Adoption Leave
                return $leaveType->annual_limit;
            case 'STL': // Study Leave
            case 'RL': // Rehabilitation Leave
            case 'SEL': // Special Emergency Leave
            case 'TL': // Terminal Leave
            case 'MLC': // Monetization
                return 0.00; // Granted on request basis
            default:
                return $leaveType->annual_limit;
        }
    }

    private function getCreditsForContractual($leaveType)
    {
        $code = $leaveType->leave_code;
        
        // Contractual employees typically get limited benefits
        switch ($code) {
            case 'VL': // Vacation Leave - prorated
            case 'SL': // Sick Leave - prorated
                return 5.00; // Limited credits
            case 'ML': // Maternity Leave - IMMEDIATE (Statutory)
            case 'PL': // Paternity Leave - IMMEDIATE (Statutory)
            case 'VAWC': // VAWC Leave - IMMEDIATE (Emergency)
                return $leaveType->annual_limit; // Mandated by law
            case 'SOPL': // Solo Parent Leave - REQUIRES 6 MONTHS
            case 'SPL': // Special Privilege Leave - REQUIRES 6 MONTHS
            case 'BL': // Bereavement Leave
                return 0.00; // Usually not granted to contractual
            default:
                return null; // Not eligible
        }
    }

    private function getCreditsForCasual($leaveType)
    {
        $code = $leaveType->leave_code;
        
        // Casual employees get minimal benefits
        switch ($code) {
            case 'SL': // Sick Leave only
                return 5.00; // Limited sick leave
            case 'ML': // Maternity Leave - IMMEDIATE (Statutory)
            case 'PL': // Paternity Leave - IMMEDIATE (Statutory)
            case 'VAWC': // VAWC Leave - IMMEDIATE (Emergency)
                return $leaveType->annual_limit; // Mandated by law
            case 'SOPL': // Solo Parent Leave - REQUIRES 6 MONTHS
                return 0.00; // Usually not granted to casual
            default:
                return null; // Not eligible for other leaves
        }
    }

    private function getMonthsServedInYear($appointmentDate, $year)
    {
        $startOfYear = Carbon::create($year, 1, 1);
        $endOfYear = Carbon::create($year, 12, 31);
        
        $serviceStart = $appointmentDate->year == $year 
            ? $appointmentDate 
            : $startOfYear;
        
        $serviceEnd = Carbon::now()->year == $year 
            ? Carbon::now() 
            : $endOfYear;

        if ($serviceStart->year > $year) {
            return 0;
        }

        return $serviceStart->diffInMonths($serviceEnd) + 1;
    }

    private function hasServedSixMonths($appointmentDate)
    {
        if (!$appointmentDate) {
            return true; // Assume eligible if no appointment date
        }
        
        $monthsServed = Carbon::parse($appointmentDate)->diffInMonths(Carbon::now());
        return $monthsServed >= 6;
    }
}
