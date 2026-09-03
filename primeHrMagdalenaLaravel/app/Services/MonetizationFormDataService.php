<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\MonetizationRequest;
use App\Models\User;

/**
 * Everything the printed Monetization sheet prints, read off one
 * `monetization_requests` row.
 *
 * This service computes nothing. The peso figure on the form is
 * `monetization_requests.computed_amount` — the value
 * MonetizationRequest::computeAmount() wrote when the request was filed, and
 * the value both detail modals and the Monetization Requests table already
 * show — so the sheet cannot disagree with the screen the button was pressed
 * from. The two multiplication lines the template prints are the *same*
 * arithmetic spelled out, not a re-derivation: the middle line is S × D, which
 * exists only because the office's sheet shows its working.
 *
 * Balances are equally not recomputed. `vl_balance` / `sl_balance` are the
 * credits as they stood when the employee filed, which is what the sheet's
 * "No. of Leave Credits as of <date>" line means; reading today's balance
 * would print a figure the approval had already deducted the monetized days
 * out of.
 *
 * The employee and the preparer are resolved independently — the employee from
 * the request's own row, the preparer from the account that pressed the
 * button. They are not the same person, and on the admin surface they never
 * are.
 */
class MonetizationFormDataService
{
    /**
     * Values a name field carries when nobody has been named. They must not
     * reach the "Prepared by" block: a form is read as prepared once a name
     * appears over that line, and "admin" is not a person who prepared
     * anything. Same list as DtrFormDataService.
     */
    private const PLACEHOLDER_NAMES = ['n/a', 'na', 'n.a.', '-', '--', 'none', 'tbd', 'not applicable', 'admin', 'administrator'];

    /**
     * @param  int  $requestId  the monetization request being printed
     * @param  User|null  $generatedBy  the account that pressed the button
     * @param  int|null  $scopeEmployeeId  when set, the request must belong to
     *         this employee — the employee surface passes its own id so one
     *         employee cannot print another's sheet by editing the URL
     */
    public function build(int $requestId, ?User $generatedBy = null, ?int $scopeEmployeeId = null): array
    {
        $query = MonetizationRequest::with([
            'employee.employmentDetail.designationRelation',
            'employee.employmentDetail.departmentRelation',
            'approvedBy.employee',
        ])->where('id', $requestId);

        if ($scopeEmployeeId !== null) {
            $query->where('employee_id', $scopeEmployeeId);
        }

        $monetization = $query->firstOrFail();

        $employee = $monetization->employee;
        $employment = $employee?->employmentDetail;

        $salary = (float) $monetization->monthly_salary;
        $days = $monetization->totalDays();
        $factor = MonetizationRequest::CONSTANT_FACTOR;

        $vlBalance = (float) $monetization->vl_balance;
        $slBalance = (float) $monetization->sl_balance;

        return [
            'request' => $monetization,
            'requestNumber' => (string) $monetization->request_number,

            // ── The employee the monetization belongs to ──────────────────
            'employeeName' => mb_strtoupper($this->fullName($employee)),
            'employeeIdNo' => (string) ($employee?->employee_id ?? ''),
            'position' => (string) ($employment?->designationRelation?->title ?? ''),
            'department' => (string) ($employment?->departmentRelation?->name ?? ''),

            // ── The figures, exactly as the request recorded them ─────────
            'salary' => $this->money($salary),
            'creditsAsOf' => $monetization->created_at?->format('F j, Y') ?? '',
            'vacationCredits' => $this->credits($vlBalance),
            'sickCredits' => $this->credits($slBalance),
            'totalCredits' => $this->credits($vlBalance + $slBalance),
            'monetizedDays' => $this->days($days),
            'constantFactor' => $this->factor($factor),

            // The template shows its working: S × D on one line, then that
            // product × CF. Printed from the same three values the stored
            // amount was computed from, so the lines lead to the total rather
            // than to a figure derived a second way.
            'salaryTimesDays' => $this->money($salary * $days),
            'amount' => $this->money((float) $monetization->computed_amount),

            // ── Who prepared this copy — never the employee above ─────────
            'preparedBy' => $this->preparedBy($generatedBy),

            'heading' => (array) config('monetization_form.heading', []),
            'formTitle' => (string) config('monetization_form.title', 'Monetization'),
            'labels' => (array) config('monetization_form.labels', []),
            'legend' => (array) config('monetization_form.legend', []),
            'currencySymbol' => (string) config('monetization_form.currency_symbol', 'P'),
            'amountSuffix' => (string) config('monetization_form.amount_suffix', 'Php'),

            'filename' => $this->filename($monetization, $employee),
        ];
    }

    /**
     * The "Prepared by" block: the signed-in account's name and designation.
     *
     * Their employee record first — that is the name the office knows them by,
     * and the only place a designation is recorded — then `users.name`, then
     * the username, because an HR account without a linked employee row still
     * has a person behind it. Upper-cased, the way the template sets it.
     *
     * With nothing real to print the configured fallback stands; with that
     * empty too the line prints blank for a wet signature rather than naming
     * somebody who did not prepare the sheet.
     */
    private function preparedBy(?User $generatedBy): array
    {
        $configured = (array) config('monetization_form.prepared_by', []);

        $name = (string) ($configured['name'] ?? '');
        $title = (string) ($configured['title'] ?? '');

        if (($configured['name_from'] ?? null) === 'generator' && $generatedBy) {
            $resolved = $this->generatorName($generatedBy);

            if ($resolved !== '') {
                $name = $resolved;
                // The designation follows the person, not the fallback: a
                // resolved preparer printed over somebody else's configured
                // title would misstate who prepared the sheet in what capacity.
                $title = (string) ($generatedBy->employee?->employmentDetail?->designationRelation?->title ?? '');
            }
        }

        return [
            'caption' => (string) ($configured['caption'] ?? 'Prepared by:'),
            'name' => mb_strtoupper($this->realName($name)),
            'title' => $this->realName($title),
        ];
    }

    private function generatorName(?User $generatedBy): string
    {
        if (! $generatedBy) {
            return '';
        }

        $name = $this->realName($this->fullName($generatedBy->employee));

        if ($name === '') {
            $name = $this->realName($generatedBy->name ?? null);
        }

        if ($name === '') {
            $name = $this->realName($generatedBy->username ?? null);
        }

        return $name;
    }

    private function realName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '' || in_array(mb_strtolower($name), self::PLACEHOLDER_NAMES, true)) {
            return '';
        }

        return $name;
    }

    /** Empty for an account with no linked employee row — an HR user may have none. */
    private function fullName(?Employee $employee): string
    {
        if (! $employee) {
            return '';
        }

        $initial = $employee->middle_name ? mb_substr($employee->middle_name, 0, 1) . '.' : '';

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $employee->first_name,
            $initial,
            $employee->last_name,
            $employee->suffix ?? null,
        ]))));
    }

    /**
     * Pesos with separators and centavos — "12,087.00" — matching every other
     * money figure this system prints. The peso mark is drawn by the sheet, so
     * the total line can carry its "Php" suffix instead.
     */
    private function money(float $value): string
    {
        return number_format($value, 2);
    }

    /**
     * Leave credits at the precision they are stored with (3 decimals), minus
     * a trailing zero in the third place: 111.969 prints as "111.969",
     * 129.000 as "129.00" — exactly as the office's sheet writes them.
     *
     * The value is never rounded to fit. A credit balance is what an employee
     * is owed, and 111.969 days is not 112.
     */
    private function credits(float $value): string
    {
        $formatted = number_format($value, 3, '.', ',');

        return str_ends_with($formatted, '0') ? substr($formatted, 0, -1) : $formatted;
    }

    /** Monetized days, whole where they are whole: 75, 12.5, 7.25. */
    private function days(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 3, '.', ','), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /** The constant factor as written, not as a rounded decimal: 0.0481927. */
    private function factor(float $value): string
    {
        return rtrim(rtrim(number_format($value, 7, '.', ''), '0'), '.');
    }

    /**
     * Monetization-MON-2026-0001-Juan-Dela-Cruz.pdf
     *
     * The name is squeezed to ASCII word characters: it reaches a
     * Content-Disposition header and a Windows file system, and a "Ñ" or a "#"
     * in a surname is enough to truncate one or be rejected by the other.
     * Same rule as DtrFormDataService::filename().
     */
    private function filename(MonetizationRequest $monetization, ?Employee $employee): string
    {
        $name = preg_replace('/[^A-Za-z0-9]+/', '-', $this->asciiName($this->fullName($employee)));
        $name = trim((string) $name, '-');

        $parts = array_filter([
            'Monetization',
            preg_replace('/[^A-Za-z0-9\-]+/', '', (string) $monetization->request_number),
            $name,
        ]);

        return implode('-', $parts) . '.pdf';
    }

    private function asciiName(string $name): string
    {
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        return $transliterated === false ? $name : $transliterated;
    }
}
