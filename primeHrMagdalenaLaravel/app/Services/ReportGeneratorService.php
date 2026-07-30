<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Feature 5: Automatic Report Generator.
 *
 * Builds tabular reports off the live tables and hands back both the rows and
 * a column definition so the UI can render a table, plus a token the export
 * endpoint can exchange for a PDF.
 *
 * Every report returns the same envelope:
 *   ['answer' => string, 'data' => [...rows], 'report' => ['key','title','columns','period','totals']]
 */
class ReportGeneratorService
{
    private const MAX_ROWS = 500;

    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function generate(User $user, string $question, array $history = []): array
    {
        $type = $this->detectType($question);
        $period = $this->detectPeriod($question);

        $report = match ($type) {
            'attendance' => $this->attendanceReport($user, $period),
            'leave' => $this->leaveReport($user, $period),
            'payroll' => $this->payrollReport($user, $period),
            'department' => $this->departmentReport($user),
            'hiring' => $this->hiringReport($user, $period),
            'training' => $this->trainingReport($user, $period),
            'employee_summary' => $this->employeeSummaryReport($user),
            default => null,
        };

        if ($report === null) {
            return [
                'answer' => "I can generate these reports: **attendance**, **leave**, **payroll**, **department**, "
                    . "**hiring**, **training**, and **employee summary**.\n\n"
                    . 'Tell me which one and the period — for example "generate the payroll report for June".',
                'data' => [],
            ];
        }

        $report['period'] = $period;

        return [
            'answer' => $this->narrate($user, $question, $report, $history),
            'data' => $report['rows'],
            'report' => [
                'key' => $report['key'],
                'title' => $report['title'],
                'columns' => $report['columns'],
                'period' => [
                    'label' => $period['label'],
                    'start' => $period['start']->toDateString(),
                    'end' => $period['end']->toDateString(),
                ],
                'totals' => $report['totals'] ?? [],
                'row_count' => count($report['rows']),
            ],
        ];
    }

    private function detectType(string $question): ?string
    {
        $q = strtolower($question);

        return match (true) {
            (bool) preg_match('/\b(attendance|dtr|daily time record|absent|tardin\w+|late)\b/', $q) => 'attendance',
            (bool) preg_match('/\b(leave|vacation|sick|vl|sl|credits?)\b/', $q) => 'leave',
            (bool) preg_match('/\b(payroll|salary|payslip|compensation|net pay|deduction)\b/', $q) => 'payroll',
            (bool) preg_match('/\b(hiring|hired|appointment|new hires?|recruitment|turnover)\b/', $q) => 'hiring',
            (bool) preg_match('/\b(training|seminar|l&d|learning|development)\b/', $q) => 'training',
            (bool) preg_match('/\b(department|office|division)\b/', $q) => 'department',
            (bool) preg_match('/\b(employee summary|masterlist|roster|profile|personnel)\b/', $q) => 'employee_summary',
            default => null,
        };
    }

    /**
     * Resolve the reporting window from the question, defaulting to the
     * current month.
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    private function detectPeriod(string $question): array
    {
        $q = strtolower($question);
        $now = now();

        $months = ['january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12];

        // Explicit year, e.g. "for 2024".
        $year = preg_match('/\b(20\d{2})\b/', $q, $m) ? (int) $m[1] : $now->year;

        foreach ($months as $name => $number) {
            if (str_contains($q, $name)) {
                $start = Carbon::create($year, $number, 1)->startOfMonth();

                return ['start' => $start, 'end' => $start->copy()->endOfMonth(), 'label' => $start->format('F Y')];
            }
        }

        if (preg_match('/\blast\s+month\b/', $q)) {
            $start = $now->copy()->subMonthNoOverflow()->startOfMonth();

            return ['start' => $start, 'end' => $start->copy()->endOfMonth(), 'label' => $start->format('F Y')];
        }

        if (preg_match('/\bthis\s+year\b/', $q) || preg_match('/\b(20\d{2})\b/', $q)) {
            $start = Carbon::create($year, 1, 1)->startOfYear();

            return ['start' => $start, 'end' => $start->copy()->endOfYear(), 'label' => (string) $year];
        }

        if (preg_match('/\blast\s+(\d+)\s+months?\b/', $q, $m)) {
            $start = $now->copy()->subMonthsNoOverflow((int) $m[1])->startOfMonth();

            return ['start' => $start, 'end' => $now->copy()->endOfDay(), 'label' => "last {$m[1]} months"];
        }

        if (preg_match('/\b(this\s+week|weekly)\b/', $q)) {
            return ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek(), 'label' => 'this week'];
        }

        return [
            'start' => $now->copy()->startOfMonth(),
            'end' => $now->copy()->endOfMonth(),
            'label' => $now->format('F Y'),
        ];
    }

    /**
     * @param array{start: Carbon, end: Carbon, label: string} $period
     */
    private function attendanceReport(User $user, array $period): array
    {
        $query = DB::table('attendance')
            ->join('employees', 'attendance.employee_id', '=', 'employees.id')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            // Counts come from attendance_type, never from accredited_hours:
            // that column is only populated once payroll computes the day, so
            // a NULL means "not yet computed", not "worked zero hours".
            ->selectRaw("
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                COUNT(*) AS days_recorded,
                SUM(CASE WHEN attendance.attendance_type = 'ABSENT' THEN 1 ELSE 0 END) AS days_absent,
                SUM(CASE WHEN attendance.attendance_type = 'LEAVE' THEN 1 ELSE 0 END) AS days_on_leave,
                SUM(CASE WHEN attendance.attendance_type = 'TRAVEL_ORDER' THEN 1 ELSE 0 END) AS days_on_travel,
                SUM(CASE WHEN attendance.accredited_hours IS NOT NULL THEN 1 ELSE 0 END) AS days_computed
            ")
            ->whereBetween('attendance.date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->groupBy('employees.id', 'employees.employee_id', 'employees.first_name', 'employees.last_name', 'departments.name')
            ->orderByDesc('days_absent');

        $this->policy->scopeByEmployeeId($query, $user, 'attendance.employee_id');

        $rows = $this->toArray($query->limit(self::MAX_ROWS)->get());

        return [
            'key' => 'attendance',
            'title' => 'Attendance Report — ' . $period['label'],
            'columns' => [
                ['key' => 'employee_no', 'label' => 'Employee No.'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'days_recorded', 'label' => 'Days Recorded', 'align' => 'right'],
                ['key' => 'days_absent', 'label' => 'Absent', 'align' => 'right'],
                ['key' => 'days_on_leave', 'label' => 'On Leave', 'align' => 'right'],
                ['key' => 'days_on_travel', 'label' => 'On Travel', 'align' => 'right'],
                ['key' => 'days_computed', 'label' => 'Hours Computed', 'align' => 'right'],
            ],
            'rows' => $rows,
            'totals' => [
                'Employees' => count($rows),
                'Days recorded' => array_sum(array_column($rows, 'days_recorded')),
                'Total absences' => array_sum(array_column($rows, 'days_absent')),
                'Days on leave' => array_sum(array_column($rows, 'days_on_leave')),
            ],
        ];
    }

    /**
     * @param array{start: Carbon, end: Carbon, label: string} $period
     */
    private function leaveReport(User $user, array $period): array
    {
        $query = DB::table('leave_applications')
            ->join('employees', 'leave_applications.employee_id', '=', 'employees.id')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->leftJoin('leave_types_config', 'leave_applications.leave_code', '=', 'leave_types_config.leave_code')
            ->selectRaw("
                leave_applications.application_number,
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                COALESCE(leave_types_config.leave_name, leave_applications.leave_code) AS leave_type,
                leave_applications.start_date,
                leave_applications.end_date,
                leave_applications.number_of_days,
                leave_applications.status
            ")
            ->whereBetween('leave_applications.start_date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->orderByDesc('leave_applications.start_date');

        $this->policy->scopeByEmployeeId($query, $user, 'leave_applications.employee_id');

        $rows = $this->toArray($query->limit(self::MAX_ROWS)->get());

        $byStatus = [];
        foreach ($rows as $row) {
            $byStatus[$row['status']] = ($byStatus[$row['status']] ?? 0) + 1;
        }

        return [
            'key' => 'leave',
            'title' => 'Leave Report — ' . $period['label'],
            'columns' => [
                ['key' => 'application_number', 'label' => 'Application No.'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'leave_type', 'label' => 'Leave Type'],
                ['key' => 'start_date', 'label' => 'From'],
                ['key' => 'end_date', 'label' => 'To'],
                ['key' => 'number_of_days', 'label' => 'Days', 'align' => 'right'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'totals' => array_merge(
                ['Applications' => count($rows), 'Total days' => round(array_sum(array_column($rows, 'number_of_days')), 2)],
                array_combine(
                    array_map(fn ($s) => ucfirst((string) $s), array_keys($byStatus)),
                    array_values($byStatus)
                ) ?: []
            ),
        ];
    }

    /**
     * @param array{start: Carbon, end: Carbon, label: string} $period
     */
    private function payrollReport(User $user, array $period): array
    {
        $query = DB::table('salary_computations')
            ->join('employees', 'salary_computations.employee_id', '=', 'employees.id')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->selectRaw("
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                salary_computations.period_start,
                salary_computations.period_end,
                salary_computations.basic_pay,
                salary_computations.ot_pay,
                salary_computations.late_deduction,
                salary_computations.undertime_deduction,
                salary_computations.other_deductions,
                salary_computations.gross_pay,
                salary_computations.net_pay,
                salary_computations.status
            ")
            ->where('salary_computations.period_start', '>=', $period['start']->toDateString())
            ->where('salary_computations.period_end', '<=', $period['end']->toDateString())
            ->orderBy('employees.last_name');

        $this->policy->scopeByEmployeeId($query, $user, 'salary_computations.employee_id');

        $rows = $this->toArray($query->limit(self::MAX_ROWS)->get());

        return [
            'key' => 'payroll',
            'title' => 'Payroll Report — ' . $period['label'],
            'columns' => [
                ['key' => 'employee_no', 'label' => 'Employee No.'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'basic_pay', 'label' => 'Basic Pay', 'align' => 'right', 'format' => 'money'],
                ['key' => 'ot_pay', 'label' => 'OT Pay', 'align' => 'right', 'format' => 'money'],
                ['key' => 'other_deductions', 'label' => 'Deductions', 'align' => 'right', 'format' => 'money'],
                ['key' => 'gross_pay', 'label' => 'Gross', 'align' => 'right', 'format' => 'money'],
                ['key' => 'net_pay', 'label' => 'Net Pay', 'align' => 'right', 'format' => 'money'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'totals' => [
                'Payslips' => count($rows),
                'Gross total' => round(array_sum(array_map('floatval', array_column($rows, 'gross_pay'))), 2),
                'Net total' => round(array_sum(array_map('floatval', array_column($rows, 'net_pay'))), 2),
            ],
        ];
    }

    private function departmentReport(User $user): array
    {
        if (!$this->policy->hasOrgWideAccess($user)) {
            return $this->restricted('department');
        }

        $rows = $this->toArray(DB::table('departments')
            ->leftJoin('employment_details', 'departments.id', '=', 'employment_details.department_id')
            ->leftJoin('employees', 'employment_details.employee_id', '=', 'employees.id')
            ->selectRaw("
                departments.code,
                departments.name AS department,
                COALESCE(departments.head, '—') AS head,
                departments.status,
                COUNT(employees.id) AS headcount,
                SUM(CASE WHEN employees.sex = 'Male' THEN 1 ELSE 0 END) AS male,
                SUM(CASE WHEN employees.sex = 'Female' THEN 1 ELSE 0 END) AS female
            ")
            ->groupBy('departments.id', 'departments.code', 'departments.name', 'departments.head', 'departments.status')
            ->orderByDesc('headcount')
            ->get());

        return [
            'key' => 'department',
            'title' => 'Department Report',
            'columns' => [
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'head', 'label' => 'Head'],
                ['key' => 'headcount', 'label' => 'Headcount', 'align' => 'right'],
                ['key' => 'male', 'label' => 'Male', 'align' => 'right'],
                ['key' => 'female', 'label' => 'Female', 'align' => 'right'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'totals' => [
                'Departments' => count($rows),
                'Total headcount' => array_sum(array_column($rows, 'headcount')),
            ],
        ];
    }

    /**
     * @param array{start: Carbon, end: Carbon, label: string} $period
     */
    private function hiringReport(User $user, array $period): array
    {
        if (!$this->policy->hasOrgWideAccess($user)) {
            return $this->restricted('hiring');
        }

        $rows = $this->toArray(DB::table('employment_details')
            ->join('employees', 'employment_details.employee_id', '=', 'employees.id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw("
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                COALESCE(designations.title, '—') AS position,
                employment_details.employment_status,
                employment_details.appointment_date,
                employment_details.salary_grade
            ")
            ->whereBetween('employment_details.appointment_date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->orderByDesc('employment_details.appointment_date')
            ->limit(self::MAX_ROWS)
            ->get());

        return [
            'key' => 'hiring',
            'title' => 'Hiring Report — ' . $period['label'],
            'columns' => [
                ['key' => 'employee_no', 'label' => 'Employee No.'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'position', 'label' => 'Position'],
                ['key' => 'employment_status', 'label' => 'Status'],
                ['key' => 'appointment_date', 'label' => 'Appointed'],
                ['key' => 'salary_grade', 'label' => 'SG', 'align' => 'right'],
            ],
            'rows' => $rows,
            'totals' => ['New appointments' => count($rows)],
        ];
    }

    /**
     * @param array{start: Carbon, end: Carbon, label: string} $period
     */
    private function trainingReport(User $user, array $period): array
    {
        $query = DB::table('trainings')
            ->join('employees', 'trainings.employee_id', '=', 'employees.id')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->selectRaw("
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                trainings.title,
                trainings.conducted_by,
                trainings.date_from,
                trainings.date_to,
                trainings.hours,
                trainings.status
            ")
            ->whereBetween('trainings.date_from', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->orderByDesc('trainings.date_from');

        $this->policy->scopeByEmployeeId($query, $user, 'trainings.employee_id');

        $rows = $this->toArray($query->limit(self::MAX_ROWS)->get());

        return [
            'key' => 'training',
            'title' => 'Training Report — ' . $period['label'],
            'columns' => [
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'title', 'label' => 'Training'],
                ['key' => 'conducted_by', 'label' => 'Conducted By'],
                ['key' => 'date_from', 'label' => 'From'],
                ['key' => 'date_to', 'label' => 'To'],
                ['key' => 'hours', 'label' => 'Hours', 'align' => 'right'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'totals' => [
                'Records' => count($rows),
                'Total hours' => array_sum(array_map('intval', array_column($rows, 'hours'))),
            ],
        ];
    }

    private function employeeSummaryReport(User $user): array
    {
        $query = DB::table('employees')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->leftJoin('designations', 'employment_details.designation_id', '=', 'designations.id')
            ->selectRaw("
                employees.employee_id AS employee_no,
                CONCAT_WS(' ', employees.first_name, employees.middle_name, employees.last_name) AS employee,
                employees.sex,
                employees.civil_status,
                employees.email,
                COALESCE(departments.name, '—') AS department,
                COALESCE(designations.title, '—') AS position,
                employment_details.employment_status,
                employment_details.appointment_date
            ")
            ->orderBy('employees.last_name');

        $this->policy->scopeEmployeeQuery($query, $user, 'employees.id');

        $rows = $this->toArray($query->limit(self::MAX_ROWS)->get());

        return [
            'key' => 'employee_summary',
            'title' => 'Employee Summary Report',
            'columns' => [
                ['key' => 'employee_no', 'label' => 'Employee No.'],
                ['key' => 'employee', 'label' => 'Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'position', 'label' => 'Position'],
                ['key' => 'employment_status', 'label' => 'Status'],
                ['key' => 'appointment_date', 'label' => 'Appointed'],
                ['key' => 'email', 'label' => 'Email'],
            ],
            'rows' => $rows,
            'totals' => ['Employees' => count($rows)],
        ];
    }

    private function restricted(string $key): array
    {
        return [
            'key' => $key,
            'title' => ucfirst($key) . ' Report',
            'columns' => [],
            'rows' => [],
            'totals' => [],
            'restricted' => true,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function toArray($collection): array
    {
        return $collection->map(fn ($row) => (array) $row)->all();
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function narrate(User $user, string $question, array $report, array $history): string
    {
        if (!empty($report['restricted'])) {
            return 'That report covers the whole organisation, so it is limited to HR, admin, and mayor accounts.';
        }

        $rowCount = count($report['rows']);

        if ($rowCount === 0) {
            return "**{$report['title']}** produced no rows — there are no records in that period.";
        }

        $totals = collect($report['totals'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(' · ');

        $system = <<<'PROMPT'
You are the PRIME HRIS Assistant introducing a report you just generated for
an HR administrator.

- Open with the report title and period, and the headline totals.
- Note the two or three most significant findings from the sample rows
  (outliers, concentrations, anything that needs action).
- Use only figures present in the data. Never estimate.
- End by noting the table is displayed below and can be exported to PDF.
- Under 160 words. No markdown table, no JSON.
PROMPT;

        $sample = json_encode(array_slice($report['rows'], 0, 15), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => "Request: {$question}\n\nReport: {$report['title']}\nRows: {$rowCount}\nTotals: {$totals}\n\nSample:\n{$sample}",
        ];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 700);

        return $answer ?: "**{$report['title']}**\n\n{$rowCount} row(s). {$totals}\n\nThe full table is shown below and can be exported to PDF.";
    }
}
