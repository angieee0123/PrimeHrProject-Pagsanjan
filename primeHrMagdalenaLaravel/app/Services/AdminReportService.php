<?php

namespace App\Services;

use App\Models\EmploymentDetail;
use App\Models\SalaryComputation;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The seven Admin Reports, built once and read by both surfaces that show them.
 *
 * Every one of these methods used to be private to `AdminReportsController`,
 * which was fine while the page was the only thing that rendered them. It
 * stopped being fine the moment the tabs grew Export buttons: an export that
 * re-derives its own figures is an export that can disagree with the screen it
 * was clicked from, and a payroll file whose gross does not match the card
 * above the button is worse than no file. Same rule the pass slip export
 * follows against `PassSlipComplianceService` — the report and the page compute
 * from one place, or they eventually contradict each other.
 *
 * Period filters (semi / month / year) drive the payroll-derived reports —
 * Payroll, Department, Deductions. Headcount is an as-of-now snapshot and
 * Training is filtered by year only, since neither is tied to a pay period.
 *
 * Recruitment and Performance have no backing tables in this schema, so they
 * report themselves as unavailable rather than showing invented figures.
 */
class AdminReportService
{
    /** The tabs, in the order the page lays them out. */
    public const TABS = [
        'payroll'     => 'Payroll Summary',
        'department'  => 'Department Breakdown',
        'deductions'  => 'Deductions Report',
        'headcount'   => 'Headcount Report',
        'recruitment' => 'Recruitment Report',
        'training'    => 'Training Report',
        'performance' => 'Performance Report',
    ];

    /** Pay-period status → the badge classes already defined in the admin CSS. */
    private const STATUS_BADGE = [
        'approved' => 'processed',
        'paid'     => 'processed',
        'pending'  => 'pending',
        'draft'    => 'on-hold',
    ];

    /**
     * Resolve the period the request is asking for.
     *
     * Defaults follow the most recent payroll on file rather than a hardcoded
     * date, and every value is clamped: a non-numeric `?year=` casts to 0 and
     * would build an unusable date.
     *
     * @return array{year:int,month:int,semi:int,start:Carbon,end:Carbon,label:string,tab:string,years:array<int>}
     */
    public function resolvePeriod(Request $request): array
    {
        $latest = SalaryComputation::orderByDesc('period_start')->first();

        $defaultYear  = $latest?->period_start?->year  ?? (int) now()->year;
        $defaultMonth = $latest?->period_start?->month ?? (int) now()->month;

        $year  = (int) $request->input('year', $defaultYear);
        $month = (int) $request->input('month', $defaultMonth);
        $semi  = (int) $request->input('semi', $latest && $latest->period_start->day > 15 ? 2 : 1);
        $tab   = (string) $request->input('tab', 'payroll');

        if ($year < 2000 || $year > (int) now()->year + 1) {
            $year = $defaultYear;
        }
        $month = max(1, min(12, $month));
        $semi  = $semi === 2 ? 2 : 1;

        $monthStart  = Carbon::create($year, $month, 1)->startOfDay();
        $windowStart = $semi === 1 ? $monthStart->copy() : $monthStart->copy()->day(16);
        $windowEnd   = $semi === 1 ? $monthStart->copy()->day(15) : $monthStart->copy()->endOfMonth();

        $label = $semi === 1
            ? $monthStart->format('F') . ' 1–15, ' . $year
            : $monthStart->format('F') . ' 16–' . $windowEnd->day . ', ' . $year;

        return [
            'year'  => $year,
            'month' => $month,
            'semi'  => $semi,
            'start' => $windowStart,
            'end'   => $windowEnd,
            'label' => $label,
            'tab'   => array_key_exists($tab, self::TABS) ? $tab : 'payroll',
            'years' => $this->availableYears($year),
        ];
    }

    /**
     * Payroll rows overlapping the window — a computation that spans several
     * months still belongs to any period it covers.
     */
    public function computations(Carbon $windowStart, Carbon $windowEnd): Collection
    {
        return SalaryComputation::with('employee.employmentDetail.departmentRelation')
            ->whereDate('period_start', '<=', $windowEnd)
            ->whereDate('period_end', '>=', $windowStart)
            ->orderBy('employee_id')
            ->get();
    }

    /**
     * Every report for a resolved period, keyed by tab.
     *
     * @param array $period from resolvePeriod()
     */
    public function all(array $period): array
    {
        $comps = $this->computations($period['start'], $period['end']);

        return [
            'payroll'     => $this->payrollReport($comps, $period['label']),
            'department'  => $this->departmentReport($comps, $period['label']),
            'deductions'  => $this->deductionsReport($comps, $period['label']),
            'headcount'   => $this->headcountReport(),
            'training'    => $this->trainingReport($period['year']),
            'recruitment' => $this->recruitmentReport(),
            'performance' => $this->performanceReport(),
        ];
    }

    /** One report, without paying for the other six. */
    public function one(string $tab, array $period): array
    {
        $needsPayroll = in_array($tab, ['payroll', 'department', 'deductions'], true);
        $comps = $needsPayroll ? $this->computations($period['start'], $period['end']) : collect();

        return match ($tab) {
            'department'  => $this->departmentReport($comps, $period['label']),
            'deductions'  => $this->deductionsReport($comps, $period['label']),
            'headcount'   => $this->headcountReport(),
            'training'    => $this->trainingReport($period['year']),
            'recruitment' => $this->recruitmentReport(),
            'performance' => $this->performanceReport(),
            default       => $this->payrollReport($comps, $period['label']),
        };
    }

    // ────────────────────────── Reports ──────────────────────────

    public function payrollReport(Collection $comps, string $periodLabel): array
    {
        $rows = $comps->map(function ($c) {
            $emp = $c->employee;
            $gross = (float) $c->gross_pay;
            $net   = (float) $c->net_pay;

            return [
                'code'       => $emp->employee_id ?? '—',
                'name'       => $this->employeeName($emp),
                'dept'       => $this->departmentName($emp),
                'gross'      => $gross,
                'deductions' => $gross - $net,
                'net'        => $net,
                'status'     => ucfirst($c->status),
                'badge'      => self::STATUS_BADGE[$c->status] ?? 'pending',
            ];
        })->values();

        $gross = $rows->sum('gross');
        $net   = $rows->sum('net');
        $settled = $rows->whereIn('status', ['Approved', 'Paid'])->count();

        return [
            'title'    => 'Payroll Summary',
            'subtitle' => $rows->count() . ' ' . str('record')->plural($rows->count()) . ' · ' . $periodLabel,
            'stats'    => [
                $this->stat('Gross Payroll', $this->peso($gross), $rows->count() . ' ' . str('employee')->plural($rows->count()) . ' · ' . $periodLabel, '#0b044d', 'peso'),
                $this->stat('Total Net Pay', $this->peso($net), 'After all deductions', '#15803d', 'peso'),
                $this->stat('Total Deductions', $this->peso($gross - $net), 'Mandatory, loans, and attendance', '#8e1e18', 'creditCard'),
                $this->stat('Approved / Paid', (string) $settled, max(0, $rows->count() - $settled) . ' still draft or pending', '#c9a227', 'checkCircle'),
            ],
            'rows'   => $rows,
            'totals' => ['gross' => $gross, 'deductions' => $gross - $net, 'net' => $net, 'settled' => $settled],
            'empty'  => 'No payroll has been computed for ' . $periodLabel . '.',
        ];
    }

    public function departmentReport(Collection $comps, string $periodLabel): array
    {
        $grouped = $comps->groupBy(fn ($c) => $this->departmentName($c->employee));
        $totalGross = (float) $comps->sum(fn ($c) => (float) $c->gross_pay);

        $rows = $grouped->map(function ($items, $dept) use ($totalGross) {
            $gross = (float) $items->sum(fn ($c) => (float) $c->gross_pay);

            return [
                'dept'      => $dept,
                'headcount' => $items->pluck('employee_id')->unique()->count(),
                'gross'     => $gross,
                'net'       => (float) $items->sum(fn ($c) => (float) $c->net_pay),
                'pct'       => $totalGross > 0 ? round($gross / $totalGross * 100, 1) : 0.0,
            ];
        })->sortByDesc('gross')->values();

        return [
            'title'    => 'Department Payroll Breakdown',
            'subtitle' => $rows->count() . ' ' . str('department')->plural($rows->count()) . ' · ' . $periodLabel,
            'stats'    => [
                $this->stat('Departments Paid', (string) $rows->count(), 'With payroll this period', '#0b044d', 'building'),
                $this->stat('Gross Payroll', $this->peso($totalGross), $periodLabel, '#15803d', 'peso'),
                $this->stat('Largest Share', $rows->first()['dept'] ?? '—', $rows->first() ? $rows->first()['pct'] . '% of gross' : 'No data', '#c9a227', 'trendingUp'),
                $this->stat('Personnel Paid', (string) $comps->pluck('employee_id')->unique()->count(), 'Across all departments', '#8e1e18', 'users'),
            ],
            'rows'   => $rows,
            'totals' => [
                'gross'     => $totalGross,
                'net'       => (float) $comps->sum(fn ($c) => (float) $c->net_pay),
                'headcount' => $comps->pluck('employee_id')->unique()->count(),
            ],
            'empty'  => 'No departmental payroll for ' . $periodLabel . '.',
        ];
    }

    public function deductionsReport(Collection $comps, string $periodLabel): array
    {
        $buckets = [];

        foreach ($comps as $c) {
            foreach ($this->breakdown($c) as $code => $line) {
                $name = is_array($line) ? ($line['name'] ?? $code) : $code;
                $amount = (float) (is_array($line) ? ($line['amount'] ?? 0) : $line);
                $category = is_array($line) ? ($line['category'] ?? 'OTHER') : 'OTHER';
                if ($amount <= 0) {
                    continue;
                }
                $key = $code ?: $name;
                $buckets[$key] ??= ['name' => $name, 'category' => ucfirst(strtolower($category)), 'amount' => 0.0, 'employees' => []];
                $buckets[$key]['amount'] += $amount;
                $buckets[$key]['employees'][$c->employee_id] = true;
            }

            // Attendance-driven deductions live in their own columns, not the JSON.
            foreach (['Late Deduction' => 'late_deduction', 'Undertime Deduction' => 'undertime_deduction'] as $label => $col) {
                $amount = (float) $c->{$col};
                if ($amount <= 0) {
                    continue;
                }
                $buckets[$label] ??= ['name' => $label, 'category' => 'Attendance', 'amount' => 0.0, 'employees' => []];
                $buckets[$label]['amount'] += $amount;
                $buckets[$label]['employees'][$c->employee_id] = true;
            }
        }

        $itemised = array_sum(array_column($buckets, 'amount'));

        $rows = collect($buckets)->map(fn ($b) => [
            'name'      => $b['name'],
            'category'  => $b['category'],
            'amount'    => $b['amount'],
            'employees' => count($b['employees']),
            'pct'       => $itemised > 0 ? round($b['amount'] / $itemised * 100, 1) : 0.0,
        ])->sortByDesc('amount')->values();

        $gross = (float) $comps->sum(fn ($c) => (float) $c->gross_pay);
        $net   = (float) $comps->sum(fn ($c) => (float) $c->net_pay);
        $mandatory = $rows->where('category', 'Mandatory')->sum('amount');

        return [
            'title'    => 'Deductions Breakdown',
            'subtitle' => $rows->count() . ' ' . str('deduction type')->plural($rows->count()) . ' · ' . $periodLabel,
            'stats'    => [
                $this->stat('Total Deductions', $this->peso($gross - $net), 'Gross less net pay', '#8e1e18', 'creditCard'),
                $this->stat('Itemised', $this->peso($itemised), $rows->count() . ' deduction types', '#0b044d', 'trendingUp'),
                $this->stat('Mandatory Share', $this->peso($mandatory), 'GSIS, PhilHealth, Pag-IBIG', '#15803d', 'checkCircle'),
                $this->stat('Employees Affected', (string) $comps->pluck('employee_id')->unique()->count(), $periodLabel, '#c9a227', 'users'),
            ],
            'rows'   => $rows,
            'totals' => [
                'amount'         => $itemised,
                'gross_less_net' => $gross - $net,
                'mandatory'      => (float) $mandatory,
                'employees'      => $comps->pluck('employee_id')->unique()->count(),
            ],
            // Itemised lines come from each payslip's stored breakdown; if a payslip
            // predates that field the two figures can differ, so surface it. The
            // ₱1 tolerance keeps per-row rounding from raising a false alarm.
            'note'   => abs($itemised - ($gross - $net)) > 1.00
                ? 'Itemised lines total ' . $this->peso($itemised) . ' against ' . $this->peso($gross - $net)
                    . ' of gross-less-net. The difference is deductions recorded before the itemised breakdown was captured.'
                : null,
            'empty'  => 'No deductions recorded for ' . $periodLabel . '.',
        ];
    }

    public function headcountReport(): array
    {
        $details = EmploymentDetail::with(['departmentRelation', 'employee'])->get()
            ->filter(fn ($d) => $d->employee !== null);

        $rows = $details->groupBy(fn ($d) => $d->departmentRelation->name ?? 'Unassigned')
            ->map(fn ($items, $dept) => [
                'dept'      => $dept,
                'total'     => $items->count(),
                'permanent' => $items->where('employment_status', 'Permanent')->count(),
                'joborder'  => $items->where('employment_status', 'Job Order')->count(),
                // Anything that is neither — Casual, Contractual, Co-terminous.
                // The screen has no column for it; the export needs the figure
                // so Permanent + Job Order + Other reconciles to Total.
                'other'     => $items->count()
                    - $items->where('employment_status', 'Permanent')->count()
                    - $items->where('employment_status', 'Job Order')->count(),
                'pct'       => $details->count() > 0 ? round($items->count() / $details->count() * 100, 1) : 0.0,
            ])->sortByDesc('total')->values();

        return [
            'title'    => 'Headcount Report',
            'subtitle' => $details->count() . ' personnel across ' . $rows->count() . ' ' . str('department')->plural($rows->count()) . ' · as of ' . now()->format('M j, Y'),
            'stats'    => [
                $this->stat('Total Personnel', (string) $details->count(), 'With an employment record', '#0b044d', 'users'),
                $this->stat('Permanent', (string) $details->where('employment_status', 'Permanent')->count(), 'Plantilla positions', '#15803d', 'checkCircle'),
                $this->stat('Job Order', (string) $details->where('employment_status', 'Job Order')->count(), 'Contract of service', '#c9a227', 'clipboard'),
                $this->stat('Departments', (string) $rows->count(), 'With assigned personnel', '#8e1e18', 'building'),
            ],
            'rows'   => $rows,
            'totals' => [
                'total'     => $details->count(),
                'permanent' => $details->where('employment_status', 'Permanent')->count(),
                'joborder'  => $details->where('employment_status', 'Job Order')->count(),
            ],
            'empty'  => 'No employment records on file.',
        ];
    }

    public function trainingReport(int $year): array
    {
        $trainings = Training::with('employee.employmentDetail.departmentRelation')
            ->whereYear('date_from', $year)
            ->orderByDesc('date_from')
            ->get();

        $rows = $trainings->map(fn ($t) => [
            'code'      => $t->employee->employee_id ?? '—',
            'name'      => $this->employeeName($t->employee),
            'dept'      => $this->departmentName($t->employee),
            'title'     => $t->title,
            'conductor' => $t->conducted_by ?: '—',
            'dates'     => $this->dateRange($t->date_from, $t->date_to),
            'from'      => $t->date_from ? Carbon::parse($t->date_from)->format('M d, Y') : '',
            'to'        => $t->date_to ? Carbon::parse($t->date_to)->format('M d, Y') : '',
            'hours'     => (float) $t->hours,
            // A rejected submission credits 0 hours to CSC PDS Section IV
            // however many it declared — the same rule the Training
            // Verification export and the employee's own Training page apply.
            'credited'  => $t->status === 'verified' ? (float) $t->hours : 0.0,
            'status'    => ucfirst($t->status ?? 'pending'),
            'badge'     => ($t->status === 'verified') ? 'processed' : (($t->status === 'rejected') ? 'on-hold' : 'pending'),
        ])->values();

        return [
            'title'    => 'Training & Development Report',
            'subtitle' => $rows->count() . ' ' . str('record')->plural($rows->count()) . ' · ' . $year,
            'stats'    => [
                $this->stat('Training Records', (string) $rows->count(), 'Filed in ' . $year, '#0b044d', 'bookOpen'),
                $this->stat('Verified', (string) $trainings->where('status', 'verified')->count(), 'Confirmed by HR', '#15803d', 'checkCircle'),
                $this->stat('Pending Review', (string) $trainings->where('status', 'pending')->count(), 'Awaiting verification', '#c9a227', 'clipboard'),
                $this->stat('Total Hours', number_format((float) $trainings->sum('hours'), 1), 'Across all participants', '#8e1e18', 'trendingUp'),
            ],
            'rows'   => $rows,
            'totals' => [
                'hours'    => (float) $trainings->sum('hours'),
                'credited' => (float) $trainings->where('status', 'verified')->sum('hours'),
            ],
            'empty'  => 'No training records filed in ' . $year . '.',
        ];
    }

    public function recruitmentReport(): array
    {
        return $this->unavailable(
            'Recruitment Report',
            'Job postings and applicant statistics',
            'No recruitment tables exist in the database yet — job postings and applicants are not being recorded, so there is nothing to report on.'
        );
    }

    public function performanceReport(): array
    {
        return $this->unavailable(
            'Performance Evaluation Report',
            'Employee performance ratings',
            'No performance-evaluation tables exist in the database yet — ratings are not being recorded, so there is nothing to report on.'
        );
    }

    private function unavailable(string $title, string $subtitle, string $reason): array
    {
        return [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'stats'       => [],
            'rows'        => collect(),
            'totals'      => [],
            'unavailable' => $reason,
        ];
    }

    // ────────────────────────── Helpers ──────────────────────────

    /**
     * `deduction_breakdown` is cast to array, but existing rows were written
     * double-encoded — the cast then yields the inner JSON string rather than
     * an array. Decode until we actually have one.
     */
    private function breakdown($comp): array
    {
        $raw = $comp->deduction_breakdown;

        for ($i = 0; $i < 3 && is_string($raw); $i++) {
            $raw = json_decode($raw, true);
        }

        return is_array($raw) ? $raw : [];
    }

    public function availableYears(int $current): array
    {
        $years = collect(SalaryComputation::selectRaw('DISTINCT YEAR(period_start) AS y')->pluck('y'))
            ->merge(Training::whereNotNull('date_from')->selectRaw('DISTINCT YEAR(date_from) AS y')->pluck('y'))
            ->push($current)
            ->push(now()->year)
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $years ?: [now()->year];
    }

    private function employeeName($emp): string
    {
        if (!$emp) {
            return 'Unknown employee';
        }

        return trim($emp->first_name . ' ' . ($emp->middle_name ? substr($emp->middle_name, 0, 1) . '. ' : '') . $emp->last_name);
    }

    private function departmentName($emp): string
    {
        return $emp?->employmentDetail?->departmentRelation?->name ?: 'Unassigned';
    }

    private function dateRange($from, $to): string
    {
        if (!$from) {
            return '—';
        }
        $from = Carbon::parse($from);
        $to = $to ? Carbon::parse($to) : null;

        if (!$to || $from->isSameDay($to)) {
            return $from->format('M j, Y');
        }

        return $from->format('M j') . ' – ' . $to->format('M j, Y');
    }

    private function stat(string $label, string $value, string $sub, string $accent, string $icon): array
    {
        return compact('label', 'value', 'sub', 'accent', 'icon');
    }

    private function peso(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }
}
