<?php

namespace App\Services;

use App\Models\PassSlip;

class PassSlipFormDataService
{
    public function build(int $passSlipId): array
    {
        $slip = PassSlip::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'approver',
        ])->findOrFail($passSlipId);

        $employee = $slip->employee;
        $employment = $employee?->employmentDetail;

        // dompdf cannot fetch a URL, so the seal is embedded. Via the service
        // so an uploaded logo reaches the printed forms too, and so the MIME
        // type is derived — this used to hard-code image/jpeg, which would
        // have produced a broken image the moment somebody uploaded a PNG.
        $logoBase64 = \App\Services\SiteContentService::logoDataUri();

        $isApproved = $slip->status === 'approved';
        $isRejected = $slip->status === 'rejected';
        $isPending = $slip->status === 'pending';

        $approverName = $slip->approver?->name ?? '';
        $recommendedByName = $slip->recommended_by_name ?: ($employment?->departmentRelation?->head ?? '');

        return [
            'slip' => $slip,
            'employee' => $employee,
            'employeeName' => $employee
                ? strtoupper(trim("{$employee->last_name}, {$employee->first_name}" . ($employee->middle_name ? ' ' . substr($employee->middle_name, 0, 1) . '.' : '')))
                : '',
            'department' => $employment?->departmentRelation?->name ?? '',
            'designation' => $employment?->designationRelation?->title ?? '',
            'slipNumber' => $slip->slip_number,
            'date' => $slip->date?->format('F j, Y'),
            'isOfficialActivity' => $slip->type === 'official_activity',
            'isPersonalReason' => $slip->type === 'personal_reason',
            'purposeCategory' => $slip->purpose_category,
            'purposeLabel' => $slip->purpose_label,
            'purposeDetail' => $slip->reason,
            'destination' => $slip->destination,
            'timeOut' => $slip->time_out ? \Carbon\Carbon::parse($slip->time_out)->format('g:i A') : '',
            'timeOutPeriod' => $slip->time_out ? \Carbon\Carbon::parse($slip->time_out)->format('A') : '',
            'timeIn' => $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '',
            'timeInPeriod' => $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('A') : '',
            'hasReturned' => (bool) $slip->time_in,
            'recommendedByName' => $recommendedByName,
            'approverName' => $approverName,
            'isApproved' => $isApproved,
            'isRejected' => $isRejected,
            'isPending' => $isPending,
            'remarks' => $slip->remarks,
            'logoBase64' => $logoBase64,
            'agencyName' => config('cs_form.agency_name'),
            'agencyAddress' => config('cs_form.agency_address'),
            'generatedDate' => now()->format('F d, Y'),
        ];
    }
}
