<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

/**
 * Assembles the printed Daily Time Record — the office's "Time Master ·
 * Employee Attendance Logs" sheet — from the days the Detailed DTR modal is
 * showing.
 *
 * It does **not** compute a day. The day computation lives in
 * AttendanceController::generateDetailedRecords(), which is private to that
 * controller for the same reason the attendance CSV exports are: it is built
 * from the accreditation path payroll reads, and moving it would move the
 * computation. So the controller hands the records over and this service does
 * the *form* work — the part that would otherwise be a second copy of the
 * modal's filtering rules living in a Blade view:
 *
 *  - narrowing the days to the range and the View chip the modal has applied,
 *  - putting them in date order,
 *  - formatting each slot for a column 74pt wide,
 *  - cutting the result into sheets of `rows_per_page`,
 *  - naming the file.
 *
 * The range narrowing is deliberately re-applied here rather than trusted from
 * the caller. It is the export's one critical property — a DTR that carries a
 * day outside the period it prints is a false record — and re-applying it
 * costs one comparison per day.
 */
class DtrFormDataService
{
    /** The width the sheet gives the wordmark; its height follows the file. */
    private const WORDMARK_WIDTH = 171.6;

    /** Used only when the artwork is missing and getimagesize() cannot answer. */
    private const WORDMARK_FALLBACK_HEIGHT = 36.1;

    /**
     * Names that are not names. A `users.name` of "N/A" over a signature rule
     * is worse than an empty rule: it reads as a signed document.
     */
    private const PLACEHOLDER_NAMES = ['n/a', 'na', 'n.a.', '-', '--', 'none', 'tbd', 'not applicable', 'admin', 'administrator'];

    /** `applyDtrChip()`'s single-day views, by the chip's own key. */
    private const VIEW_DAYS = [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
    ];

    private const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    private const WEEKEND  = ['Saturday', 'Sunday'];

    /**
     * @param  array<int, array<string, mixed>>  $records  generateDetailedRecords() output
     * @return array<string, mixed>
     */
    public function build(
        Employee $employee,
        array $records,
        Carbon $startDate,
        Carbon $endDate,
        string $view = 'all',
        ?User $generatedBy = null
    ): array {
        $view = $this->normaliseView($view);
        $rows = $this->rows($records, $startDate, $endDate, $view);

        $perPage = max(1, (int) config('dtr_form.rows_per_page', 24));

        // An empty result still prints one sheet. A period with no logged days
        // is a real answer — the blank ruled form for that period, over the
        // employee's own name — and it is what the office would file. A
        // zero-page PDF is a download that will not open.
        $pages = $rows === [] ? [[]] : array_chunk($rows, $perPage);

        $wordmark = (string) config('dtr_form.brand.wordmark');

        return [
            'employee' => [
                'id_no'      => $this->text($employee->employee_id),
                'name'       => $this->fullName($employee),
                'department' => $this->text(
                    $employee->employmentDetail->departmentRelation->name ?? null
                ),
            ],

            'periodStart' => $startDate->copy()->startOfDay(),
            'periodEnd'   => $endDate->copy()->startOfDay(),
            'view'        => $view,

            'rows'        => $rows,
            'pages'       => $pages,
            'pageCount'   => count($pages),
            'rowsPerPage' => $perPage,

            'brand'       => (array) config('dtr_form.brand', []),
            'title'       => (string) config('dtr_form.title', ''),
            'labels'      => (array) config('dtr_form.labels', []),
            'columns'     => (array) config('dtr_form.columns', []),
            'signatories' => $this->signatories($generatedBy),

            'wordmarkSrc'    => $this->embed($wordmark),
            'wordmarkWidth'  => self::WORDMARK_WIDTH,
            'wordmarkHeight' => $this->scaledHeight($wordmark, self::WORDMARK_WIDTH),

            'filename' => $this->filename($employee, $startDate, $endDate),
        ];
    }

    /**
     * The days the sheet prints: inside the period, matching the View chip,
     * in date order.
     */
    private function rows(array $records, Carbon $startDate, Carbon $endDate, string $view): array
    {
        $from = $startDate->copy()->startOfDay();
        $to   = $endDate->copy()->startOfDay();

        $kept = [];

        foreach ($records as $record) {
            $key = $record['date_key'] ?? null;

            if (!is_string($key) || $key === '') {
                continue;
            }

            $day = Carbon::parse($key)->startOfDay();

            if ($day->lt($from) || $day->gt($to)) {
                continue;
            }

            if (!$this->matchesView($record, $view)) {
                continue;
            }

            $kept[$key] = $this->row($record, $day);
        }

        // Keyed by date, so a generator that emitted a day twice prints it
        // once. ksort on `Y-m-d` is chronological order.
        ksort($kept);

        return array_values($kept);
    }

    /**
     * One ruled line of the sheet.
     *
     * A day the employee did not punch because it was covered — approved leave
     * or an approved travel order — is where generateDetailedRecords() writes
     * a marker such as "ON LEAVE" into the four time slots rather than a time.
     * The sheet prints that marker once, across the span those four columns
     * occupy, and leaves the slots themselves empty: repeating it four times
     * would not fit the columns, and blanking it outright would make an
     * approved absence read as an unexplained one on an official record.
     */
    private function row(array $record, Carbon $day): array
    {
        $marker = $this->marker($record);

        return [
            'date_key' => $day->format('Y-m-d'),
            'date'     => $day->format('M d, Y'),
            'day'      => (string) ($record['day'] ?? $day->format('l')),
            'state'    => $this->recordState($record),

            // The am/pm captions already name the half of the day, which is
            // how the template reads a bare clock time. Nothing names the half
            // an OT column falls in, so those keep the meridiem.
            'am_in'    => $marker ? '' : $this->time($record['am_in']  ?? null),
            'am_out'   => $marker ? '' : $this->time($record['am_out'] ?? null),
            'pm_in'    => $marker ? '' : $this->time($record['pm_in']  ?? null),
            'pm_out'   => $marker ? '' : $this->time($record['pm_out'] ?? null),
            'ot_in'    => $this->time($record['ot_in']  ?? null, true),
            'ot_out'   => $this->time($record['ot_out'] ?? null, true),

            'marker'   => $marker,
        ];
    }

    /**
     * The non-time value the generator wrote into the morning slot, if any —
     * "ON LEAVE", "ON TRAVEL". Read off the record rather than rebuilt from
     * `is_on_leave`, so a marker this service has never heard of still prints
     * instead of vanishing.
     */
    private function marker(array $record): string
    {
        $value = $record['am_in'] ?? null;

        if (!is_string($value) || trim($value) === '' || $this->isTime($value)) {
            return '';
        }

        return trim($value);
    }

    /**
     * Mirrors `renderDetailedDTR()`'s `chipState` in
     * resources/js/admin/attendance/detailedDtrModal.js.
     *
     * The modal decides in JavaScript and the sheet decides here. Both halves
     * keep working when they drift; only their agreement breaks, which is why
     * DtrFormDataTest pins the pair.
     */
    private function recordState(array $record): string
    {
        $day       = (string) ($record['day'] ?? '');
        $isWeekend = in_array($day, self::WEEKEND, true);
        $onLeave   = !empty($record['is_on_leave']);

        $slots = array_map(
            fn (string $slot) => $this->filled($record[$slot] ?? null),
            ['am_in', 'am_out', 'pm_in', 'pm_out']
        );

        $hasAnyLog  = in_array(true, $slots, true);
        $isComplete = !in_array(false, $slots, true);
        $isAbsent   = !$isWeekend && !$hasAnyLog && !$onLeave;

        return match (true) {
            !empty($record['is_abandoned'])          => 'absent',
            !empty($record['is_absent'])             => 'absent',
            $isAbsent                                => 'absent',
            $onLeave                                 => 'leave',
            $isWeekend                               => 'weekend',
            !$isComplete                             => 'incomplete',
            (int) ($record['late_minutes'] ?? 0) > 0 => 'late',
            default                                  => 'present',
        };
    }

    /** Mirrors `applyDtrChip()` in the same module. */
    private function matchesView(array $record, string $view): bool
    {
        $day = (string) ($record['day'] ?? '');

        if (isset(self::VIEW_DAYS[$view])) {
            return $day === self::VIEW_DAYS[$view];
        }

        return match ($view) {
            'all'      => true,
            'weekdays' => in_array($day, self::WEEKDAYS, true),
            'weekend'  => in_array($day, self::WEEKEND, true),
            default    => $this->recordState($record) === $view,
        };
    }

    /**
     * An unknown chip prints the whole period rather than nothing. A view this
     * service does not recognise is a bug in the pair above; answering it with
     * an empty sheet would report the period as having no records at all.
     */
    private function normaliseView(string $view): string
    {
        $view = strtolower(trim($view));

        $known = array_merge(
            array_keys(self::VIEW_DAYS),
            ['all', 'weekdays', 'weekend', 'present', 'absent', 'late', 'leave', 'incomplete']
        );

        return in_array($view, $known, true) ? $view : 'all';
    }

    /**
     * The officers printed over the sheet's two signature rules.
     *
     * A signatory marked `name_from => 'generator'` is signed by whoever
     * produced this copy — the HRMO staffer who chose the period and pressed
     * the button is the one certifying it, so a fixed name there would put
     * somebody else's name over a record they never saw. Its configured
     * `name` survives as the fallback for an account carrying no usable name.
     *
     * Every other signatory prints exactly what the office typed: nothing in
     * this schema records the Municipal Administrator's decision, so that name
     * stands over a blank rule for a wet signature.
     */
    private function signatories(?User $generatedBy): array
    {
        $configured = (array) config('dtr_form.signatories', []);

        return array_map(function ($signatory) use ($generatedBy) {
            $signatory = (array) $signatory;

            if (($signatory['name_from'] ?? null) !== 'generator') {
                return $signatory;
            }

            $signatory['name'] = $this->generatorName($generatedBy)
                ?: $this->realName($signatory['name'] ?? null);

            return $signatory;
        }, $configured);
    }

    /**
     * Who to print for the account that generated this DTR.
     *
     * Their employee record first — that is the name the rest of this system
     * knows them by, and the one an office would recognise on a form. Then
     * `users.name`, then the username, because an HR account without a linked
     * employee row still has a person behind it. Upper-cased to sit level with
     * the sheet's other signature block.
     *
     * Empty when there is nothing real to print, which hands the decision back
     * to the configured fallback rather than writing "admin" over a signature.
     */
    private function generatorName(?User $generatedBy): string
    {
        if (!$generatedBy) {
            return '';
        }

        $name = $this->realName($this->fullName($generatedBy->employee));

        if ($name === '') {
            $name = $this->realName($generatedBy->name ?? null);
        }

        if ($name === '') {
            $name = $this->realName($generatedBy->username ?? null);
        }

        return $name === '' ? '' : mb_strtoupper($name);
    }

    private function realName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '' || in_array(mb_strtolower($name), self::PLACEHOLDER_NAMES, true)) {
            return '';
        }

        return $name;
    }

    private function isTime(?string $value): bool
    {
        return is_string($value) && preg_match('/^\d{1,2}:\d{2}/', trim($value)) === 1;
    }

    /** JavaScript truthiness for the slot values, which are `H:i` or null. */
    private function filled(mixed $value): bool
    {
        return is_string($value) ? trim($value) !== '' : !empty($value);
    }

    /** `H:i` -> `h:mm`, optionally with the meridiem. Anything else is blank. */
    private function time(?string $value, bool $meridiem = false): string
    {
        if (!$this->isTime($value)) {
            return '';
        }

        [$hours, $minutes] = array_map('intval', explode(':', trim($value)));

        $suffix = $meridiem ? ' ' . ($hours >= 12 ? 'PM' : 'AM') : '';

        return sprintf('%d:%02d%s', $hours % 12 === 0 ? 12 : $hours % 12, $minutes, $suffix);
    }

    /** Null for an account with no linked employee row — an HR user may have none. */
    private function fullName(?Employee $employee): string
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
     * DTR_Juan_Dela_Cruz_2026-01-01_to_2026-01-31.pdf
     *
     * The name is squeezed to ASCII word characters: it reaches a
     * Content-Disposition header and a Windows file system, and a "Ñ" or a "#"
     * in a surname is enough to truncate one or be rejected by the other.
     */
    private function filename(Employee $employee, Carbon $startDate, Carbon $endDate): string
    {
        $name = $this->fullName($employee);

        if (function_exists('iconv')) {
            $name = @iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name;
        }

        $name = trim(preg_replace('/_+/', '_', preg_replace('/[^A-Za-z0-9]+/', '_', $name)), '_');

        if ($name === '') {
            $name = $this->text($employee->employee_id) ?: 'Employee';
        }

        return sprintf(
            'DTR_%s_%s_to_%s.pdf',
            $name,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }

    private function text(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    /** dompdf reads no URLs; the artwork has to travel as bytes. */
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

    /** A re-cut wordmark prints taller, never squashed. */
    private function scaledHeight(?string $relative, float $width): float
    {
        $file = $relative ? public_path($relative) : null;
        $size = ($file && is_file($file)) ? @getimagesize($file) : false;

        if (!$size || empty($size[0])) {
            return self::WORDMARK_FALLBACK_HEIGHT;
        }

        return round($width * $size[1] / $size[0], 2);
    }
}
