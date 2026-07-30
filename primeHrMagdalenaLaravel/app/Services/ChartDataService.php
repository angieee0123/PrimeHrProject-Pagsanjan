<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Feature 6: Automatic Charts.
 *
 * Returns a render-agnostic chart spec — form, labels, series, and the colour
 * role — that the Blade partial turns into inline SVG.
 *
 * Form is chosen by the data's job, not by preference:
 *   magnitude comparison → bar, one hue (length already encodes the value)
 *   trend over time      → line / area
 *   part-to-whole        → stacked bar (never a pie of two slices)
 *   distinct series      → grouped bar or multi-line, categorical hues
 *
 * Rules held to deliberately:
 *   - Never a dual-axis chart. Two measures of different scale become two charts.
 *   - Categorical hues are assigned in fixed slot order and never cycled;
 *     past MAX_SERIES the tail folds into "Other" rather than inventing a hue.
 *   - The palette below is the validated default set (adjacent-pair CVD ΔE 9.1
 *     light / 8.4 dark). In light mode aqua and yellow fall under 3:1 against
 *     the surface, so every chart ships beside its data table — that is the
 *     required relief, not an optional extra.
 */
class ChartDataService
{
    private const MAX_SERIES = 8;
    private const MAX_CATEGORIES = 12;

    /** Categorical slots, fixed order. Index 0 is also the single-series hue. */
    private const PALETTE_LIGHT = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];
    private const PALETTE_DARK = ['#3987e5', '#d95926', '#199e70', '#c98500', '#d55181', '#008300', '#9085e9', '#e66767'];

    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function generate(User $user, string $question, array $history = []): array
    {
        if (!$this->policy->hasOrgWideAccess($user)) {
            return [
                'answer' => 'Organisation-wide charts are limited to HR, admin, and mayor accounts.',
                'data' => [],
            ];
        }

        $chart = match ($this->detectSubject($question)) {
            'attendance_trend' => $this->attendanceTrend($question),
            'leave_utilisation' => $this->leaveUtilisation($question),
            'leave_by_type' => $this->leaveByType($question),
            'headcount' => $this->headcountByDepartment(),
            'gender' => $this->genderDistribution($question),
            'hiring' => $this->hiringTrend($question),
            'payroll' => $this->payrollTrend($question),
            'absenteeism_compare' => $this->absenteeismComparison($question),
            default => null,
        };

        if ($chart === null) {
            return [
                'answer' => "I can chart: **monthly attendance**, **leave utilisation**, **leave by type**, "
                    . "**headcount by department**, **gender distribution**, **hiring trend**, **payroll expenses**, "
                    . "and **absenteeism comparisons**.\n\nFor example: \"generate a graph of monthly attendance\".",
                'data' => [],
            ];
        }

        if (empty($chart['labels'])) {
            return [
                'answer' => "There is no data in that period to chart yet.",
                'data' => [],
            ];
        }

        return [
            'answer' => $this->narrate($user, $question, $chart, $history),
            'data' => $this->asTableRows($chart),
            'charts' => [$this->decorate($chart)],
        ];
    }

    private function detectSubject(string $question): ?string
    {
        $q = strtolower($question);

        return match (true) {
            (bool) preg_match('/\bcompar\w+\b/', $q) && (bool) preg_match('/\b(absen\w+|attendance)\b/', $q) => 'absenteeism_compare',
            (bool) preg_match('/\battendance\b|\bdtr\b|\bpresent\b/', $q) => 'attendance_trend',
            (bool) preg_match('/\bleave\b.*\b(type|kind|breakdown|category)\b|\bby\s+leave\s+type\b/', $q) => 'leave_by_type',
            (bool) preg_match('/\bleave\b|\bvacation\b|\bsick\b|\bcredits?\b/', $q) => 'leave_utilisation',
            (bool) preg_match('/\bgender\b|\bsex\b|\bmale\b|\bfemale\b/', $q) => 'gender',
            (bool) preg_match('/\b(headcount|department\s+size|per\s+department|by\s+department|personnel)\b/', $q) => 'headcount',
            (bool) preg_match('/\b(hiring|hired|appointment|recruitment|new\s+hires?)\b/', $q) => 'hiring',
            (bool) preg_match('/\b(payroll|salary|expense|compensation|net\s+pay)\b/', $q) => 'payroll',
            default => null,
        };
    }

    /** Trend over time → line. Single series, one hue. */
    private function attendanceTrend(string $question): array
    {
        $year = $this->detectYear($question);

        $rows = DB::table('attendance')
            ->selectRaw("
                MONTH(date) AS m,
                COUNT(*) AS records,
                SUM(CASE WHEN attendance_type = 'ABSENT' THEN 1 ELSE 0 END) AS absences
            ")
            ->whereYear('date', $year)
            ->groupByRaw('MONTH(date)')
            ->orderByRaw('MONTH(date)')
            ->get();

        return [
            'type' => 'line',
            'color_role' => 'sequential',
            'title' => "Monthly attendance records — {$year}",
            'x_label' => 'Month',
            'y_label' => 'Attendance records',
            'labels' => $rows->map(fn ($r) => date('M', mktime(0, 0, 0, (int) $r->m, 1)))->all(),
            'series' => [
                ['name' => 'Records logged', 'values' => $rows->map(fn ($r) => (int) $r->records)->all()],
            ],
        ];
    }

    /** Trend over time, two comparable measures on one scale → multi-line. */
    private function leaveUtilisation(string $question): array
    {
        $year = $this->detectYear($question);

        $rows = DB::table('leave_applications')
            ->selectRaw('MONTH(start_date) AS m, COUNT(*) AS applications, ROUND(SUM(number_of_days), 1) AS days')
            ->whereYear('start_date', $year)
            ->where('status', 'approved')
            ->groupByRaw('MONTH(start_date)')
            ->orderByRaw('MONTH(start_date)')
            ->get();

        return [
            'type' => 'line',
            'color_role' => 'categorical',
            'title' => "Approved leave utilisation — {$year}",
            'x_label' => 'Month',
            'y_label' => 'Count',
            'labels' => $rows->map(fn ($r) => date('M', mktime(0, 0, 0, (int) $r->m, 1)))->all(),
            'series' => [
                ['name' => 'Applications', 'values' => $rows->map(fn ($r) => (int) $r->applications)->all()],
                ['name' => 'Days taken', 'values' => $rows->map(fn ($r) => (float) $r->days)->all()],
            ],
        ];
    }

    /** Part-to-whole → stacked bar, categorical, tail folded into "Other". */
    private function leaveByType(string $question): array
    {
        $year = $this->detectYear($question);

        $rows = DB::table('leave_applications')
            ->leftJoin('leave_types_config', 'leave_applications.leave_code', '=', 'leave_types_config.leave_code')
            ->selectRaw('COALESCE(leave_types_config.leave_name, leave_applications.leave_code) AS label, COUNT(*) AS total')
            ->whereYear('leave_applications.start_date', $year)
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $folded = $this->foldTail(
            $rows->map(fn ($r) => ['label' => (string) $r->label, 'value' => (int) $r->total])->all()
        );

        return [
            'type' => 'stacked_bar',
            'color_role' => 'categorical',
            'orientation' => 'horizontal',
            'title' => "Leave applications by type — {$year}",
            'x_label' => 'Applications',
            'y_label' => '',
            'labels' => ['All leave types'],
            'series' => array_map(
                fn (array $slice) => ['name' => $slice['label'], 'values' => [$slice['value']]],
                $folded
            ),
        ];
    }

    /** Magnitude comparison → bar, one hue; length carries the value. */
    private function headcountByDepartment(): array
    {
        $rows = DB::table('departments')
            ->leftJoin('employment_details', 'departments.id', '=', 'employment_details.department_id')
            ->selectRaw('departments.name AS label, COUNT(employment_details.id) AS total')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->limit(self::MAX_CATEGORIES)
            ->get();

        return [
            'type' => 'bar',
            'color_role' => 'sequential',
            'orientation' => 'horizontal',
            'title' => 'Headcount by department',
            'x_label' => 'Employees',
            'y_label' => '',
            'labels' => $rows->map(fn ($r) => (string) $r->label)->all(),
            'series' => [
                ['name' => 'Employees', 'values' => $rows->map(fn ($r) => (int) $r->total)->all()],
            ],
        ];
    }

    /**
     * Two-category part-to-whole. A pie of two slices is the wrong form, so
     * this is a stacked bar per department.
     */
    private function genderDistribution(string $question): array
    {
        $byDepartment = (bool) preg_match('/\b(department|office|division|per|by)\b/', strtolower($question));

        if (!$byDepartment) {
            $rows = DB::table('employees')
                ->selectRaw("COALESCE(sex, 'Unspecified') AS label, COUNT(*) AS total")
                ->groupBy('sex')
                ->get();

            return [
                'type' => 'stacked_bar',
                'color_role' => 'categorical',
                'orientation' => 'horizontal',
                'title' => 'Gender distribution',
                'x_label' => 'Employees',
                'y_label' => '',
                'labels' => ['All employees'],
                'series' => $rows->map(fn ($r) => ['name' => (string) $r->label, 'values' => [(int) $r->total]])->all(),
            ];
        }

        $rows = DB::table('departments')
            ->leftJoin('employment_details', 'departments.id', '=', 'employment_details.department_id')
            ->leftJoin('employees', 'employment_details.employee_id', '=', 'employees.id')
            ->selectRaw("
                departments.name AS label,
                SUM(CASE WHEN employees.sex = 'Male' THEN 1 ELSE 0 END) AS male,
                SUM(CASE WHEN employees.sex = 'Female' THEN 1 ELSE 0 END) AS female
            ")
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('male')
            ->limit(self::MAX_CATEGORIES)
            ->get();

        return [
            'type' => 'stacked_bar',
            'color_role' => 'categorical',
            'orientation' => 'horizontal',
            'title' => 'Gender distribution by department',
            'x_label' => 'Employees',
            'y_label' => '',
            'labels' => $rows->map(fn ($r) => (string) $r->label)->all(),
            'series' => [
                ['name' => 'Male', 'values' => $rows->map(fn ($r) => (int) $r->male)->all()],
                ['name' => 'Female', 'values' => $rows->map(fn ($r) => (int) $r->female)->all()],
            ],
        ];
    }

    private function hiringTrend(string $question): array
    {
        $year = $this->detectYear($question);

        $rows = DB::table('employment_details')
            ->selectRaw('MONTH(appointment_date) AS m, COUNT(*) AS total')
            ->whereYear('appointment_date', $year)
            ->groupByRaw('MONTH(appointment_date)')
            ->orderByRaw('MONTH(appointment_date)')
            ->get();

        return [
            'type' => 'bar',
            'color_role' => 'sequential',
            'title' => "New appointments — {$year}",
            'x_label' => 'Month',
            'y_label' => 'Appointments',
            'labels' => $rows->map(fn ($r) => date('M', mktime(0, 0, 0, (int) $r->m, 1)))->all(),
            'series' => [
                ['name' => 'Appointments', 'values' => $rows->map(fn ($r) => (int) $r->total)->all()],
            ],
        ];
    }

    private function payrollTrend(string $question): array
    {
        $year = $this->detectYear($question);

        $rows = DB::table('salary_computations')
            ->selectRaw('MONTH(period_start) AS m, ROUND(SUM(net_pay), 2) AS net, ROUND(SUM(gross_pay), 2) AS gross')
            ->whereYear('period_start', $year)
            ->groupByRaw('MONTH(period_start)')
            ->orderByRaw('MONTH(period_start)')
            ->get();

        // Gross and net share a scale (pesos), so one axis is correct here.
        return [
            'type' => 'line',
            'color_role' => 'categorical',
            'title' => "Payroll expense — {$year}",
            'x_label' => 'Month',
            'y_label' => 'Amount (PHP)',
            'format' => 'money',
            'labels' => $rows->map(fn ($r) => date('M', mktime(0, 0, 0, (int) $r->m, 1)))->all(),
            'series' => [
                ['name' => 'Gross pay', 'values' => $rows->map(fn ($r) => (float) $r->gross)->all()],
                ['name' => 'Net pay', 'values' => $rows->map(fn ($r) => (float) $r->net)->all()],
            ],
        ];
    }

    /**
     * "Compare absenteeism between January and June" — two periods, same
     * measure, per department → grouped bar with two categorical series.
     */
    private function absenteeismComparison(string $question): array
    {
        [$first, $second] = $this->detectComparisonMonths($question);

        $fetch = function (Carbon $month) {
            return DB::table('attendance')
                ->join('employees', 'attendance.employee_id', '=', 'employees.id')
                ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                ->join('departments', 'employment_details.department_id', '=', 'departments.id')
                ->selectRaw("departments.name AS label, SUM(CASE WHEN attendance.attendance_type = 'ABSENT' THEN 1 ELSE 0 END) AS absences")
                ->whereBetween('attendance.date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                ->groupBy('departments.id', 'departments.name')
                ->pluck('absences', 'label');
        };

        $a = $fetch($first);
        $b = $fetch($second);

        $labels = collect($a->keys())->merge($b->keys())->unique()->take(self::MAX_CATEGORIES)->values();

        return [
            'type' => 'grouped_bar',
            'color_role' => 'categorical',
            'orientation' => 'horizontal',
            'title' => 'Absences: ' . $first->format('F Y') . ' vs ' . $second->format('F Y'),
            'x_label' => 'Absences',
            'y_label' => '',
            'labels' => $labels->all(),
            'series' => [
                ['name' => $first->format('F'), 'values' => $labels->map(fn ($l) => (int) ($a[$l] ?? 0))->all()],
                ['name' => $second->format('F'), 'values' => $labels->map(fn ($l) => (int) ($b[$l] ?? 0))->all()],
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function detectComparisonMonths(string $question): array
    {
        $months = ['january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12];

        $year = $this->detectYear($question);
        $found = [];

        foreach ($months as $name => $number) {
            if (str_contains(strtolower($question), $name)) {
                $found[] = Carbon::create($year, $number, 1);
            }
        }

        if (count($found) >= 2) {
            return [$found[0], $found[1]];
        }

        return [now()->copy()->subMonthNoOverflow()->startOfMonth(), now()->copy()->startOfMonth()];
    }

    private function detectYear(string $question): int
    {
        return preg_match('/\b(20\d{2})\b/', $question, $m) ? (int) $m[1] : now()->year;
    }

    /**
     * Keep the top slots and sum the remainder into "Other" — never generate a
     * ninth hue.
     *
     * @param array<int, array{label: string, value: int|float}> $slices
     * @return array<int, array{label: string, value: int|float}>
     */
    private function foldTail(array $slices): array
    {
        if (count($slices) <= self::MAX_SERIES) {
            return $slices;
        }

        $head = array_slice($slices, 0, self::MAX_SERIES - 1);
        $tail = array_slice($slices, self::MAX_SERIES - 1);

        $head[] = ['label' => 'Other', 'value' => array_sum(array_column($tail, 'value'))];

        return $head;
    }

    /**
     * Attach the palette and the presentation flags the renderer needs.
     */
    private function decorate(array $chart): array
    {
        $count = count($chart['series']);

        $chart['legend'] = $count >= 2;
        // A number on every point is noise; label selectively unless the set
        // is small enough that every label fits.
        $chart['direct_label'] = $count <= 4;
        $chart['colors'] = [
            'light' => array_slice(self::PALETTE_LIGHT, 0, max($count, 1)),
            'dark' => array_slice(self::PALETTE_DARK, 0, max($count, 1)),
        ];
        $chart['orientation'] ??= 'vertical';
        $chart['format'] ??= 'number';

        return $chart;
    }

    /**
     * The table that accompanies every chart. Doubles as the accessible view
     * and as the light-mode contrast relief.
     *
     * @return array<int, array<string, mixed>>
     */
    private function asTableRows(array $chart): array
    {
        $rows = [];

        foreach ($chart['labels'] as $i => $label) {
            $row = [$chart['x_label'] ?: 'Category' => $label];

            foreach ($chart['series'] as $series) {
                $row[$series['name']] = $series['values'][$i] ?? 0;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function narrate(User $user, string $question, array $chart, array $history): string
    {
        $summary = collect($chart['series'])->map(function (array $series) {
            $values = $series['values'];

            if (empty($values)) {
                return "{$series['name']}: no data";
            }

            $max = max($values);
            $total = array_sum($values);

            return "{$series['name']}: total {$total}, peak {$max}";
        })->implode('; ');

        $system = <<<'PROMPT'
You are the PRIME HRIS Assistant introducing a chart you just generated.

- Say what the chart shows and over what period.
- Describe the actual shape: where it peaks, where it dips, the direction of
  travel, any outlier worth acting on.
- Quote only numbers present in the series. Never estimate a value.
- Do not describe the colours or tell the user to "see the chart below".
- Under 130 words.
PROMPT;

        $payload = json_encode([
            'title' => $chart['title'],
            'labels' => $chart['labels'],
            'series' => $chart['series'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => "Request: {$question}\n\n{$payload}"];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 600);

        return $answer ?: "**{$chart['title']}** — {$summary}.";
    }
}
