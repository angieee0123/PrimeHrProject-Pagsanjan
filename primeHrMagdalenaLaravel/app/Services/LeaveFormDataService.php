<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;

class LeaveFormDataService
{
    /**
     * Official CS Form No. 6 (Revised 2020) leave types in PDF order.
     * Keys are leave_code values stored in the database.
     */
    private const OFFICIAL_LEAVE_TYPES = [
        'VL' => 'Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'FL' => 'Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'SL' => 'Sick Leave (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'ML' => 'Maternity Leave (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)',
        'PL' => 'Paternity Leave (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)',
        'SPL' => 'Special Privilege Leave (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'SOPL' => 'Solo Parent Leave (RA No. 8972 / CSC MC No. 8, s. 2004)',
        'STL' => 'Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'VAWC' => '10-Day VAWC Leave (RA No. 9262 / CSC MC No. 15, s. 2005)',
        'RL' => 'Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'SEL' => 'Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)',
        'AL' => 'Adoption Leave (R.A. No. 8552)',
        'SLBW' => 'Special Leave Benefits for Women (RA No. 9710 / CSC MC No. 25, s. 2010)',
        'MLC' => 'Monetization of Leave Credits',
        'TL' => 'Terminal Leave',
    ];

    public function build(int $leaveApplicationId): array
    {
        $application = LeaveApplication::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'leaveType',
            'approvedBy.employee',
        ])->findOrFail($leaveApplicationId);

        $employee = $application->employee;
        $employment = $employee->employmentDetail;
        $year = (int) $application->start_date->format('Y');
        $leaveCode = $application->leave_code;

        $vlBalance = $this->getBalance($employee->id, 'VL', $year);
        $slBalance = $this->getBalance($employee->id, 'SL', $year);

        // dompdf cannot fetch a URL, so the seal is embedded. Via the service
        // so an uploaded logo reaches the printed forms too, and so the MIME
        // type is derived — this used to hard-code image/jpeg, which would
        // have produced a broken image the moment somebody uploaded a PNG.
        $logoBase64 = \App\Services\SiteContentService::logoDataUri();

        $monthlyRate = $employment?->designationRelation?->monthly_rate;
        $salaryDisplay = $monthlyRate
            ? '₱ ' . number_format((float) $monthlyRate, 2)
            : ($employment?->salary_grade
                ? 'SG ' . $employment->salary_grade . ($employment->step_increment ? ' / Step ' . $employment->step_increment : '')
                : '');

        $isApproved = $application->status === 'approved';
        $isRejected = $application->status === 'rejected';

        $approverEmployee = $application->approvedBy?->employee;
        $approverName = $approverEmployee
            ? strtoupper(trim("{$approverEmployee->last_name}, {$approverEmployee->first_name}" . ($approverEmployee->middle_name ? ' ' . substr($approverEmployee->middle_name, 0, 1) . '.' : '')))
            : ($application->approvedBy?->name ?? '');

        $creditsAsOf = $isApproved && $application->approved_at
            ? $application->approved_at->format('m/d/Y')
            : $application->created_at->format('m/d/Y');

        $isOfficialType = array_key_exists($leaveCode, self::OFFICIAL_LEAVE_TYPES);
        $otherLeaveLabel = $isOfficialType ? '' : ($application->leaveType->leave_name ?? $leaveCode);

        return [
            'application' => $application,
            'employee' => $employee,
            'department' => $employment?->departmentRelation?->name ?? '',
            'designation' => $employment?->designationRelation?->title ?? '',
            'salary' => $salaryDisplay,
            'leaveType' => $application->leaveType,
            'officialLeaveTypes' => self::OFFICIAL_LEAVE_TYPES,
            'selectedLeaveCode' => $leaveCode,
            'isOtherLeave' => !$isOfficialType,
            'otherLeaveLabel' => $otherLeaveLabel,
            'vlCredits' => $this->formatCredits($vlBalance, $leaveCode === 'VL' ? (float) $application->number_of_days : 0),
            'slCredits' => $this->formatCredits($slBalance, $leaveCode === 'SL' ? (float) $application->number_of_days : 0),
            'isApproved' => $isApproved,
            'isRejected' => $isRejected,
            'isPending' => $application->status === 'pending',
            'approverName' => $approverName,
            'creditsAsOf' => $creditsAsOf,
            'generatedDate' => now()->format('F d, Y'),
            'logoBase64' => $logoBase64,
            'agencyName' => config('cs_form.agency_name'),
            'agencyAddress' => config('cs_form.agency_address'),
            'receiptDate' => $application->created_at->format('m/d/Y'),
            'inclusiveDates' => $this->formatInclusiveDates($application),
            'daysWithPay' => $isApproved
                ? ($application->approved_days_with_pay ?? $application->number_of_days)
                : '',
            'daysWithoutPay' => $isApproved ? ($application->approved_days_without_pay ?? '') : '',
            'approvedOtherSpecify' => $isApproved ? ($application->approved_other_specify ?? '') : '',
        ];
    }

    private function formatInclusiveDates(LeaveApplication $application): string
    {
        $start = $application->start_date;
        $end = $application->end_date;

        if ($start->equalTo($end)) {
            return $start->format('F j, Y');
        }

        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('F j') . '-' . $end->format('j, Y');
        }

        return $start->format('m/d/Y') . ' – ' . $end->format('m/d/Y');
    }

    private function getBalance(int $employeeId, string $leaveCode, int $year): ?LeaveBalance
    {
        return LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_code', $leaveCode)
            ->where('year', $year)
            ->first();
    }

    private function formatCredits(?LeaveBalance $balance, float $daysApplied): array
    {
        $totalEarned = $balance ? (float) $balance->total_credits : 0;
        $less = $daysApplied > 0 ? $daysApplied : 0;
        $balanceAfter = $balance ? max(0, (float) $balance->available_credits) : 0;

        return [
            'total_earned' => $totalEarned > 0 ? number_format($totalEarned, 3) : '',
            'less' => $less > 0 ? number_format($less, 3) : '',
            'balance' => $balance ? number_format($balanceAfter, 3) : '',
        ];
    }
}
