<?php

namespace App\Services;

use App\Models\PassSlip;
use Carbon\Carbon;

class PassSlipFormDataService
{
    /**
     * Values `departments.head` and `pass_slips.recommended_by_name` carry when
     * nobody has been named. They must not reach a signature line — printing
     * "n/a" over "Department Head" is worse than leaving the line blank for a
     * wet signature, which is what the paper form expects anyway.
     */
    private const PLACEHOLDER_NAMES = ['n/a', 'na', 'n.a.', '-', '--', 'none', 'tbd', 'not applicable'];

    /**
     * Characters that fit one ruled line of the Purpose/s block at 10pt across
     * the printable width. Used to break the purpose across the form's two
     * ruled lines instead of letting it wrap under a single underline.
     */
    private const PURPOSE_LINE_CHARS = 88;

    public function build(int $passSlipId): array
    {
        $slip = PassSlip::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'approver.employee',
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

        $approverName = $this->approverName($slip);
        $recommendedByName = $this->realName($slip->recommended_by_name)
            ?: $this->realName($employment?->departmentRelation?->head);

        // The paper form's "Department Head" rule is the approval signature.
        // Once the slip is approved in the system, the person who approved it
        // is who signed it — the department's recorded head only stands in
        // while the slip is still unsigned.
        $departmentHeadName = ($isApproved && $approverName) ? $approverName : $recommendedByName;

        $employeeName = $this->formalName($employee);
        $purpose = $this->purposeText($slip);
        $date = $slip->date;

        return [
            'slip' => $slip,
            'employee' => $employee,
            'employeeName' => $employeeName,
            'employeeNameNatural' => $this->naturalName($employee),
            'department' => $employment?->departmentRelation?->name ?? '',
            'designation' => $employment?->designationRelation?->title ?? '',
            'slipNumber' => $slip->slip_number,
            'date' => $date?->format('F j, Y'),
            'isOfficialActivity' => $slip->type === 'official_activity',
            'isPersonalReason' => $slip->type === 'personal_reason',
            'purposeCategory' => $slip->purpose_category,
            'purposeLabel' => $slip->purpose_label,
            'purposeDetail' => $slip->reason,
            'purposeText' => $purpose,
            // The Purpose/s block on the form is two ruled lines, so the text
            // is broken here rather than left to wrap above a single rule.
            'purposeLines' => $this->splitToLines($purpose, self::PURPOSE_LINE_CHARS, 2),
            'destination' => $slip->destination,
            'timeOut' => $slip->time_out ? Carbon::parse($slip->time_out)->format('g:i A') : '',
            'timeOutPeriod' => $slip->time_out ? Carbon::parse($slip->time_out)->format('A') : '',
            'timeIn' => $slip->time_in ? Carbon::parse($slip->time_in)->format('g:i A') : '',
            'timeInPeriod' => $slip->time_in ? Carbon::parse($slip->time_in)->format('A') : '',
            'hasReturned' => (bool) $slip->time_in,
            'recommendedByName' => $recommendedByName,
            'departmentHeadName' => $departmentHeadName,
            'approverName' => $approverName,
            'isApproved' => $isApproved,
            'isRejected' => $isRejected,
            'isPending' => $isPending,
            'remarks' => $slip->remarks,
            'logoBase64' => $logoBase64,
            'letterhead' => $this->letterhead(),
            'agencyName' => config('cs_form.agency_name'),
            'agencyAddress' => config('cs_form.agency_address'),
            // The Certificate of Appearance is issued for the day appeared, so
            // it reads the same on every reprint rather than moving with the
            // clock the way a "generated on" stamp does.
            'issuedDay' => $date?->format('jS'),
            'issuedMonth' => $date?->format('F'),
            'issuedYear' => $date?->format('Y'),
            'generatedDate' => now()->format('F d, Y'),
        ];
    }

    /**
     * The masthead, with each emblem that actually exists embedded as bytes.
     *
     * dompdf cannot fetch a URL, and a missing emblem must leave a gap rather
     * than a broken-image box on an official form.
     */
    private function letterhead(): array
    {
        $head = config('cs_form.letterhead', []);

        $emblems = [];
        foreach ($head['emblems'] ?? [] as $relative) {
            if ($uri = $this->embed($relative)) {
                $emblems[] = $uri;
            }
        }

        $masthead = $this->embed($head['masthead'] ?? null);
        $mark = $this->embed($head['mark'] ?? null);

        return [
            // Both halves of the office's own artwork have to be present for
            // the exact letterhead; with either missing the view draws the
            // masthead from the wording below instead of printing half of one.
            'masthead' => $masthead && $mark ? $masthead : '',
            'mark' => $masthead && $mark ? $mark : '',
            'republic' => $head['republic'] ?? '',
            'municipality' => $head['municipality'] ?? config('cs_form.agency_name'),
            'tagline' => $head['tagline'] ?? '',
            'office' => $head['office'] ?? '',
            'telephone' => $head['telephone'] ?? '',
            'emblems' => $emblems,
        ];
    }

    /**
     * A file under public/ as a data: URI, or '' when it is not there.
     *
     * dompdf cannot fetch a URL, and a missing image must leave a gap rather
     * than a broken-image box on an official form.
     */
    private function embed(?string $relative): string
    {
        if (!$relative) {
            return '';
        }

        $file = public_path($relative);

        if (!is_file($file)) {
            return '';
        }

        $mime = @mime_content_type($file) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    /**
     * Who signed the approval.
     *
     * `users` has no `name` column — only `username` — so the old
     * `$slip->approver?->name` was always null and an approved slip printed an
     * empty approver. Resolved through the approver's employee record, the
     * same way PassSlipController::show() does it for the modal.
     */
    private function approverName(PassSlip $slip): string
    {
        $approver = $slip->approver;

        if (!$approver) {
            return '';
        }

        $name = $this->naturalName($approver->employee);

        return $name !== '' ? $name : (string) ($approver->username ?? '');
    }

    /** "LAST, First M." — the form's printed-name convention. */
    private function formalName($employee): string
    {
        if (!$employee) {
            return '';
        }

        $initial = $employee->middle_name ? ' ' . substr($employee->middle_name, 0, 1) . '.' : '';

        return strtoupper(trim("{$employee->last_name}, {$employee->first_name}{$initial}"));
    }

    /** "First M. Last Jr." — reads as a signature, for the NAME line. */
    private function naturalName($employee): string
    {
        if (!$employee) {
            return '';
        }

        $initial = $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '';

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $employee->first_name,
            $initial,
            $employee->last_name,
            $employee->suffix ?? null,
        ]))));
    }

    /**
     * The purpose as one sentence: the checkbox wording from the paper form
     * plus whatever the employee typed, never one without the other.
     */
    private function purposeText(PassSlip $slip): string
    {
        $label = trim((string) $slip->purpose_label);
        $detail = trim((string) $slip->reason);

        if ($label === '') {
            return $detail;
        }

        if ($detail === '' || strcasecmp($label, $detail) === 0) {
            return ucfirst($label);
        }

        return ucfirst($label) . ' — ' . $detail;
    }

    /**
     * Break text onto the ruled lines the form provides, always returning
     * exactly `$lines` entries so the rules print whether or not they are used.
     * Any overflow stays on the last line rather than being cut — losing the
     * end of a stated purpose would be worse than a slightly cramped line.
     */
    private function splitToLines(string $text, int $width, int $lines): array
    {
        $wrapped = explode("\n", wordwrap(trim($text), $width, "\n", true));

        if (count($wrapped) > $lines) {
            $tail = array_splice($wrapped, $lines - 1);
            $wrapped[] = implode(' ', $tail);
        }

        return array_pad($wrapped, $lines, '');
    }

    /** Null out the placeholders sitting in the head/recommender columns. */
    private function realName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '' || in_array(mb_strtolower($name), self::PLACEHOLDER_NAMES, true)) {
            return '';
        }

        return $name;
    }
}
