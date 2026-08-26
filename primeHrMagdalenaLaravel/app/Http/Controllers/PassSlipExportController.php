<?php

namespace App\Http\Controllers;

use App\Models\PassSlip;
use App\Models\Schedule;
use App\Services\CscTimeConversionService;
use App\Services\CsvReportWriter;
use App\Services\PassSlipComplianceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * "Export" on the Admin → Pass Slip toolbar.
 *
 * The button was rendered, styled and clickable with no handler on it at all,
 * exactly like the Travel Order one beside it. It sits above three tabs, so it
 * exports the tab you are looking at — a Pending file has no approver to name,
 * and Disapproved exists to carry the reason a slip was refused.
 *
 * A pass slip is an attendance document, not a list of errands: an approved
 * Personal Reason slip is charged as undertime and an Official Activity one is
 * time rendered (CSC MC No. 21, s. 1991). So the file states, per slip, how many
 * paid office minutes the absence covers and whether they are chargeable —
 * `PassSlipComplianceService` decides both, the same service the DTR computes
 * from, so the report and the time record cannot disagree.
 *
 * Every export re-runs the query server-side: all three tabs paginate and every
 * filter on the page is applied in the browser, so scraping the rendered table
 * would return page one with the filters silently baked in.
 */
class PassSlipExportController extends Controller
{
    /** The two values `pass_slips.type` holds, spelled the way the form spells them. */
    private const TYPE_LABELS = [
        'official_activity' => 'Official Activity',
        'personal_reason'   => 'Personal Reason',
    ];

    /** `pass_slips.status` values, spelled the way the tabs spell them. */
    private const STATUS_LABELS = [
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'rejected'  => 'Disapproved',
        'cancelled' => 'Cancelled',
    ];

    public function __construct(private readonly PassSlipComplianceService $compliance)
    {
    }

    /**
     * Every pass slip on file, whatever its status — the register.
     *
     * The three tab exports answer "what is in front of me"; this one answers
     * "what has this office recorded". It carries a Status column and the
     * decision columns filled only where they apply.
     */
    public function all(Request $request)
    {
        $slips = $this->slips(null, $request);

        return CsvReportWriter::download($this->fileName('Pass_Slips_All_Records'), function (CsvReportWriter $csv) use ($slips, $request) {
            $csv->letterhead(
                'Pass Slips — Complete Register',
                'Human Resource Management Office · PRIME HRIS',
                'All requests to leave the workplace on file as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'All Statuses', $slips->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Status', 'Filed By', 'Acted On By', 'Date Acted On', 'Reason for Disapproval',
            ]));

            foreach ($slips as $index => $slip) {
                $csv->row(array_merge($this->baseRow($slip, $index), [
                    $this->statusLabel($slip),
                    $this->userName($slip->filer),
                    // Blank on a pending slip rather than filled with a dash:
                    // nobody has acted on it, and an empty cell is the honest
                    // way to say so.
                    $slip->status === 'pending' ? '' : $this->userName($slip->approver),
                    $slip->status === 'pending' ? '' : CsvReportWriter::dateTime($slip->approved_at, ''),
                    $slip->status === 'rejected' ? ($slip->remarks ?: 'No reason recorded') : '',
                ]));
            }

            $this->writeTail($csv, $slips, 'No pass slips matched the filters above.', [
                'Acted On By and Date Acted On are blank for a pending slip — no decision has been made on it yet.',
                'Only an approved slip reaches the Daily Time Record; Charged as Undertime says so per row.',
            ], includeStatusBreakdown: true);
        });
    }

    /**
     * Pending Pass Slips — what still needs a decision.
     */
    public function pending(Request $request)
    {
        $slips = $this->slips('pending', $request);

        return CsvReportWriter::download($this->fileName('Pass_Slips_Pending'), function (CsvReportWriter $csv) use ($slips, $request) {
            $csv->letterhead(
                'Pass Slips — Pending Approval',
                'Human Resource Management Office · PRIME HRIS',
                'Requests to leave the workplace awaiting action as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Pending Approval', $slips->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Days Pending',
            ]));

            foreach ($slips as $index => $slip) {
                $csv->row(array_merge($this->baseRow($slip, $index), [
                    $this->userName($slip->filer),
                    $slip->created_at ? (int) $slip->created_at->startOfDay()->diffInDays(now()->startOfDay()) : '',
                ]));
            }

            $this->writeTail($csv, $slips, 'No pending pass slips matched the filters above.', [
                'Days Pending counts from the date the slip was filed to the date this report was generated.',
                'A pending slip changes nothing on the Daily Time Record — only an approved one is applied.',
            ]);
        });
    }

    /**
     * Approved Pass Slips — the ones that actually reach the time record.
     */
    public function approved(Request $request)
    {
        $slips = $this->slips('approved', $request);

        return CsvReportWriter::download($this->fileName('Pass_Slips_Approved'), function (CsvReportWriter $csv) use ($slips, $request) {
            $csv->letterhead(
                'Pass Slips — Approved',
                'Human Resource Management Office · PRIME HRIS',
                'Authorised absences from the workplace as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Approved', $slips->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Approved By', 'Date Approved',
            ]));

            foreach ($slips as $index => $slip) {
                $csv->row(array_merge($this->baseRow($slip, $index), [
                    $this->userName($slip->filer),
                    $this->userName($slip->approver),
                    CsvReportWriter::dateTime($slip->approved_at, ''),
                ]));
            }

            $this->writeTail($csv, $slips, 'No approved pass slips matched the filters above.', [
                'Chargeable minutes are deducted as undertime on the employee\'s Daily Time Record; excused minutes are not.',
            ]);
        });
    }

    /**
     * Disapproved Pass Slips — refused requests and the reason for each.
     */
    public function disapproved(Request $request)
    {
        $slips = $this->slips('rejected', $request);

        return CsvReportWriter::download($this->fileName('Pass_Slips_Disapproved'), function (CsvReportWriter $csv) use ($slips, $request) {
            $csv->letterhead(
                'Pass Slips — Disapproved',
                'Human Resource Management Office · PRIME HRIS',
                'Refused requests to leave the workplace as of ' . now()->format('F d, Y')
            );

            $this->writeParameters($csv, $request, 'Disapproved', $slips->count());

            $csv->columns(array_merge($this->baseColumns(), [
                'Filed By', 'Disapproved By', 'Date Disapproved', 'Reason for Disapproval',
            ]));

            foreach ($slips as $index => $slip) {
                $csv->row(array_merge($this->baseRow($slip, $index), [
                    $this->userName($slip->filer),
                    // The decision stamps `approved_by` / `approved_at` whichever
                    // way it went — the columns are the record of who acted, not
                    // of which way they acted.
                    $this->userName($slip->approver),
                    CsvReportWriter::dateTime($slip->approved_at, ''),
                    $slip->remarks ?: 'No reason recorded',
                ]));
            }

            $this->writeTail($csv, $slips, 'No disapproved pass slips matched the filters above.', [
                'A disapproved slip is not applied to the Daily Time Record — the time away is treated as ordinary undertime or absence.',
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────
    //  Query
    // ─────────────────────────────────────────────────────────────────

    /**
     * The pass slips the page's filters select, in one status or in all.
     *
     * The four toolbar controls and the topbar search box are matched the same
     * way the page matches them in the browser, so a narrowed screen and its
     * export cover the same rows. A null `$status` is the complete register —
     * every status, for `all()`.
     */
    private function slips(?string $status, Request $request): Collection
    {
        $department = $this->filter($request, 'department');
        $type       = $this->filter($request, 'type');
        $dateFrom   = $this->filter($request, 'date_from');
        $dateTo     = $this->filter($request, 'date_to');
        $search     = mb_strtolower($this->filter($request, 'search'));

        return PassSlip::with([
                'employee.employmentDetail.departmentRelation',
                'employee.employmentDetail.designationRelation',
                'employee.schedule',
                'approver.employee',
                'filer.employee',
            ])
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy('date', 'desc')
            ->get()
            ->filter(function (PassSlip $slip) use ($department, $type, $dateFrom, $dateTo, $search) {
                if ($department !== '' && $this->department($slip) !== $department) {
                    return false;
                }

                if ($type !== '' && (string) $slip->type !== $type) {
                    return false;
                }

                $date = $slip->date?->format('Y-m-d');

                if ($dateFrom !== '' && (!$date || $date < $dateFrom)) {
                    return false;
                }

                if ($dateTo !== '' && (!$date || $date > $dateTo)) {
                    return false;
                }

                if ($search !== '') {
                    $haystack = mb_strtolower(implode(' ', [
                        $slip->slip_number,
                        $this->employeeName($slip),
                        $slip->employee->employee_id ?? '',
                        $this->department($slip),
                        $slip->destination,
                        $slip->reason,
                        $this->typeLabel($slip),
                    ]));

                    if (!str_contains($haystack, $search)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    // ─────────────────────────────────────────────────────────────────
    //  The shared parts of the document
    // ─────────────────────────────────────────────────────────────────

    /**
     * The parameter block: every filter, printed whether it was set or not.
     */
    private function writeParameters(CsvReportWriter $csv, Request $request, string $status, int $count): void
    {
        $type = $this->filter($request, 'type');

        $csv->parameters([
            'Period Covered:'      => $this->describeRange($this->filter($request, 'date_from'), $this->filter($request, 'date_to')),
            'Department / Office:' => $this->filter($request, 'department') ?: 'All Departments',
            'Type of Pass Slip:'   => $type !== '' ? (self::TYPE_LABELS[$type] ?? $type) : 'All Types',
            'Search Term:'         => $this->filter($request, 'search') ?: 'None',
            'Status:'              => $status,
        ], $count);
    }

    /** The columns every pass slip report carries, whatever its status. */
    private function baseColumns(): array
    {
        return [
            'No.', 'Pass Slip No.', 'Date Filed',
            'Employee ID', 'Employee Name', 'Position', 'Department / Office',
            'Date of Pass Slip', 'Day', 'Time Out', 'Time In',
            'Time Away', 'Office Minutes Covered', 'Charged as Undertime',
            'Type', 'Purpose', 'Destination', 'Reason / Details',
            'Recommended By', 'Attachment on File',
        ];
    }

    /** @return array<int,mixed> the same fields, in the same order */
    private function baseRow(PassSlip $slip, int $index): array
    {
        $detail = $slip->employee->employmentDetail ?? null;
        $officeMinutes = $this->officeMinutes($slip);

        return [
            $index + 1,
            $slip->slip_number ?: '—',
            CsvReportWriter::date($slip->created_at, ''),
            $slip->employee->employee_id ?? '',
            $this->employeeName($slip),
            $detail->designationRelation->title ?? 'Unassigned',
            $this->department($slip) ?: 'Unassigned',
            CsvReportWriter::date($slip->date, ''),
            $slip->date ? $slip->date->format('l') : '',
            $this->time($slip->time_out),
            $this->time($slip->time_in) ?: 'No return time stated',
            $this->awayLabel($slip),
            $officeMinutes,
            $this->chargeLabel($slip),
            $this->typeLabel($slip),
            PassSlip::PURPOSE_LABELS[$slip->purpose_category] ?? '',
            $slip->destination ?: '',
            $slip->reason,
            $slip->recommended_by_name ?: '',
            $slip->attachment ? 'Yes' : 'No',
        ];
    }

    /**
     * The empty notice, the totals, the breakdowns and the closing notes.
     */
    private function writeTail(
        CsvReportWriter $csv,
        Collection $slips,
        string $emptyMessage,
        array $notes,
        bool $includeStatusBreakdown = false
    ): void {
        if ($slips->isEmpty()) {
            $csv->emptyNotice($emptyMessage);
        }

        $officeMinutes = $slips->sum(fn (PassSlip $s) => $this->officeMinutes($s));
        $chargeable = $slips->filter(fn (PassSlip $s) => $this->compliance->isChargeable($s));

        $csv->summary('Summary', [
            'Total Pass Slips:'          => $slips->count(),
            'Employees Covered:'         => $slips->pluck('employee_id')->unique()->count(),
            'Official Activity:'         => $slips->where('type', 'official_activity')->count(),
            'Personal Reason:'           => $slips->where('type', 'personal_reason')->count(),
            'Total Office Minutes Covered:' => $officeMinutes,
            'Total Office Time Covered:' => CscTimeConversionService::formatMinutes((int) $officeMinutes),
            'Chargeable as Undertime:'   => $chargeable->count(),
            'Chargeable Minutes:'        => $chargeable->sum(fn (PassSlip $s) => $this->officeMinutes($s)),
            'No Return Time Stated:'     => $slips->filter(fn (PassSlip $s) => !$s->time_in)->count(),
            'With Attachment on File:'   => $slips->filter(fn (PassSlip $s) => (bool) $s->attachment)->count(),
        ]);

        // Only the complete register mixes statuses. On a single-status file
        // this block would restate the total record count as a breakdown.
        if ($includeStatusBreakdown) {
            $csv->summary('Breakdown by Status', $this->countsBy(
                $slips,
                fn (PassSlip $s) => $this->statusLabel($s)
            ));
        }

        $csv->summary('Breakdown by Department / Office', $this->countsBy(
            $slips,
            fn (PassSlip $s) => $this->department($s) ?: 'Unassigned'
        ));

        $csv->summary('Breakdown by Purpose', $this->countsBy(
            $slips,
            fn (PassSlip $s) => PassSlip::PURPOSE_LABELS[$s->purpose_category] ?? 'Unspecified'
        ));

        $csv->summary('Pass Slips per Employee', $this->countsBy(
            $slips,
            fn (PassSlip $s) => $this->employeeName($s) . ' (' . ($s->employee->employee_id ?? 'no ID') . ')'
        ));

        $csv->notes(array_merge([
            'Office Minutes Covered counts only the part of the time-out/time-in window that falls inside paid office hours — the unpaid lunch break in between is never counted.',
            'A slip with no return time stated is treated as running to the end of the PM session, per PassSlipComplianceService.',
            'Charged as Undertime follows CSC MC No. 21, s. 1991: an approved Official Activity is time rendered, an approved Personal Reason is undertime.',
        ], $notes));
    }

    // ─────────────────────────────────────────────────────────────────
    //  Figures
    // ─────────────────────────────────────────────────────────────────

    /**
     * Paid office minutes this slip's window covers.
     *
     * Computed by the same service the Daily Time Record computes from, against
     * the schedule in force on the slip's own date — an employee's schedule is
     * a dated row, so "their schedule" is always a question about a date.
     */
    private function officeMinutes(PassSlip $slip): int
    {
        return $this->compliance->computeGapMinutes($slip, $this->scheduleOn($slip));
    }

    /** The schedule covering the slip's date, or null when none is on file. */
    private function scheduleOn(PassSlip $slip): ?Schedule
    {
        $date = $slip->date?->format('Y-m-d');
        $employee = $slip->employee;

        if (!$date || !$employee) {
            return null;
        }

        // `start_date` / `end_date` are plain `Y-m-d` strings — Schedule
        // declares no casts — so they compare correctly as strings, which is
        // what Employee::currentSchedule() already relies on.
        return $employee->schedule
            ->first(fn (Schedule $s) => $s->start_date <= $date && $s->end_date >= $date);
    }

    /** How long the employee was away, as stated on the slip. */
    private function awayLabel(PassSlip $slip): string
    {
        $out = $this->toMinutes($slip->time_out);
        $in  = $this->toMinutes($slip->time_in);

        if ($out === null || $in === null || $in <= $out) {
            return 'Not determinable';
        }

        return CscTimeConversionService::formatMinutes($in - $out);
    }

    /**
     * Whether this slip's minutes come off the employee's time.
     *
     * Only an approved slip reaches the time record at all, so a pending or
     * refused one is neither charged nor excused — saying "No" there would read
     * as a decision that has not been made.
     */
    private function chargeLabel(PassSlip $slip): string
    {
        if ($slip->status !== 'approved') {
            return 'Not applied (' . ($slip->status === 'pending' ? 'awaiting decision' : 'not approved') . ')';
        }

        return $this->compliance->isChargeable($slip) ? 'Yes — undertime' : 'No — official business';
    }

    // ─────────────────────────────────────────────────────────────────
    //  Small readers
    // ─────────────────────────────────────────────────────────────────

    /** Filenames lead with the report so a folder of exports sorts by kind. */
    private function fileName(string $report): string
    {
        return $report . '_' . now()->format('M_d_Y') . '.csv';
    }

    /**
     * A query param, trimmed, with the selects' own word for "no filter"
     * treated as absent.
     */
    private function filter(Request $request, string $key): string
    {
        $value = trim((string) $request->get($key, ''));

        return strtolower($value) === 'all' ? '' : $value;
    }

    private function department(PassSlip $slip): string
    {
        return (string) ($slip->employee->employmentDetail->departmentRelation->name ?? '');
    }

    private function employeeName(PassSlip $slip): string
    {
        $employee = $slip->employee;

        if (!$employee) {
            return 'Unknown employee';
        }

        return trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
            $employee->suffix,
        ])));
    }

    /** The status, spelled the way the tab that holds it is spelled. */
    private function statusLabel(PassSlip $slip): string
    {
        return self::STATUS_LABELS[$slip->status] ?? ucfirst((string) $slip->status);
    }

    private function typeLabel(PassSlip $slip): string
    {
        return self::TYPE_LABELS[$slip->type] ?? (string) $slip->type;
    }

    /** `time_out` / `time_in` are `time` columns, so they arrive as strings. */
    private function time($value): string
    {
        if (!$value) {
            return '';
        }

        return Carbon::parse($value)->format('g:i A');
    }

    private function toMinutes($value): ?int
    {
        if (!$value) {
            return null;
        }

        $time = Carbon::parse($value);

        return $time->hour * 60 + $time->minute;
    }

    /** Whoever acted, named the way HR would recognise them. */
    private function userName($user): string
    {
        if (!$user) {
            return '';
        }

        $employee = $user->employee ?? null;

        if ($employee) {
            $name = trim($employee->first_name . ' ' . $employee->last_name);
            if ($name !== '') {
                return $name;
            }
        }

        // `users` carries no display name of its own beyond the username.
        return trim((string) ($user->username ?? '')) ?: 'System';
    }

    /**
     * label => count, largest first.
     *
     * @return array<string,int>
     */
    private function countsBy(Collection $items, callable $key): array
    {
        return $items->groupBy($key)
            ->map->count()
            ->sortDesc()
            ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
            ->all();
    }

    private function describeRange(string $from, string $to): string
    {
        if ($from === '' && $to === '') {
            return 'All dates';
        }
        if ($from !== '' && $to !== '') {
            return Carbon::parse($from)->format('F d, Y') . ' to ' . Carbon::parse($to)->format('F d, Y');
        }
        if ($from !== '') {
            return 'From ' . Carbon::parse($from)->format('F d, Y');
        }

        return 'Up to ' . Carbon::parse($to)->format('F d, Y');
    }
}
