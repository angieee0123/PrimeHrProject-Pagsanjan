<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Feature 10: Dashboard Assistant.
 *
 * Answers the standing "how many / who / which department" questions off the
 * live tables. Every metric here is computed from columns that actually exist
 * — see the note on expiringIds() for the one the schema cannot answer.
 */
class DashboardAssistantService
{
    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function answer(User $user, string $question, array $history = []): array
    {
        if (!$this->policy->hasOrgWideAccess($user)) {
            return [
                'answer' => 'Dashboard metrics cover the whole organisation, so they are limited to HR, admin, and mayor accounts. '
                    . 'I can still answer questions about your own records.',
                'data' => [],
            ];
        }

        $metric = $this->detectMetric($question);

        return match ($metric) {
            'active_employees' => $this->activeEmployees(),
            'on_leave_today' => $this->onLeaveToday(),
            'absenteeism' => $this->absenteeismByDepartment(),
            'pending_approvals' => $this->pendingApprovals(),
            'headcount_by_department' => $this->headcountByDepartment(),
            'missing_requirements' => $this->missingRequirements(),
            'expiring_ids' => $this->expiringIds(),
            'new_hires' => $this->newHires(),
            default => $this->overview(),
        };
    }

    private function detectMetric(string $question): string
    {
        return match (true) {
            (bool) preg_match('/\b(on\s+leave|out\s+of\s+office)\b.*\b(today|now|right now)\b|\btoday\b.*\bon\s+leave\b/i', $question) => 'on_leave_today',
            (bool) preg_match('/\b(absent|absence|absenteeism|attendance)\b/i', $question) => 'absenteeism',
            (bool) preg_match('/\b(pending|awaiting|for approval|approvals?)\b/i', $question) => 'pending_approvals',
            (bool) preg_match('/\b(missing|incomplete|lacking)\b/i', $question) => 'missing_requirements',
            (bool) preg_match('/\b(expir\w*)\b/i', $question) => 'expiring_ids',
            (bool) preg_match('/\b(per|by|each)\s+department\b|\bdepartment\s+(size|breakdown|distribution|headcount)\b/i', $question) => 'headcount_by_department',
            (bool) preg_match('/\b(new\s+hires?|recently\s+hired|hired\s+this)\b/i', $question) => 'new_hires',
            (bool) preg_match('/\b(how\s+many|count|total|number\s+of)\b.*\bemployees?\b|\bactive\s+employees?\b/i', $question) => 'active_employees',
            default => 'overview',
        };
    }

    private function activeEmployees(): array
    {
        $total = Employee::count();
        $active = Employee::whereHas('employmentDetail', fn ($q) => $q->where('employment_status', 'Active'))->count();

        // employment_status is free text in this schema, so report the real
        // spread rather than asserting a single "active" definition.
        $byStatus = DB::table('employment_details')
            ->selectRaw('COALESCE(NULLIF(employment_status, ""), "Unspecified") AS status, COUNT(*) AS total')
            ->groupBy('employment_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['status' => $row->status, 'count' => (int) $row->total])
            ->all();

        $breakdown = collect($byStatus)->map(fn ($r) => "{$r['status']}: {$r['count']}")->implode(', ');

        return [
            'answer' => "There are **{$total} employee records** in the system"
                . ($active > 0 ? ", **{$active}** flagged Active" : '')
                . ".\n\nBy employment status — {$breakdown}.",
            'data' => ['total_employees' => $total, 'active' => $active, 'by_status' => $byStatus],
        ];
    }

    private function onLeaveToday(): array
    {
        $today = now()->toDateString();

        $rows = LeaveApplication::query()
            ->with('employee')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        $names = $rows->map(function (LeaveApplication $leave) {
            $name = trim(($leave->employee?->first_name ?? '') . ' ' . ($leave->employee?->last_name ?? '')) ?: 'Unknown';

            return "{$name} ({$leave->leave_code}, until " . $leave->end_date?->format('M d') . ')';
        })->all();

        $count = count($names);
        $answer = $count === 0
            ? 'No employees are on approved leave today.'
            : "**{$count} employee(s)** are on approved leave today:\n\n- " . implode("\n- ", $names);

        return [
            'answer' => $answer,
            'data' => ['date' => $today, 'count' => $count, 'employees' => $names],
        ];
    }

    private function absenteeismByDepartment(): array
    {
        $since = now()->subMonths(3)->toDateString();

        // Absence is determined solely by attendance_type. accredited_hours is
        // NULL on most rows in this database (it is only populated once payroll
        // computes the day), so treating a null as a zero-hour day would report
        // almost every record as an absence.
        $rows = DB::table('attendance')
            ->join('employees', 'attendance.employee_id', '=', 'employees.id')
            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->join('departments', 'employment_details.department_id', '=', 'departments.id')
            ->selectRaw('departments.name AS department, COUNT(*) AS total_days, '
                . "SUM(CASE WHEN attendance.attendance_type = 'ABSENT' THEN 1 ELSE 0 END) AS absent_days, "
                . "SUM(CASE WHEN attendance.attendance_type = 'LEAVE' THEN 1 ELSE 0 END) AS leave_days")
            ->whereDate('attendance.date', '>=', $since)
            ->groupBy('departments.id', 'departments.name')
            ->havingRaw('COUNT(*) > 0')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'answer' => "No attendance records found since {$since}, so absenteeism cannot be compared yet.",
                'data' => [],
            ];
        }

        $ranked = $rows->map(fn ($row) => [
            'department' => $row->department,
            'total_days' => (int) $row->total_days,
            'absent_days' => (int) $row->absent_days,
            'leave_days' => (int) $row->leave_days,
            'absence_rate' => round(((int) $row->absent_days / max((int) $row->total_days, 1)) * 100, 2),
        ])->sortByDesc('absence_rate')->values();

        $totalAbsences = $ranked->sum('absent_days');

        if ($totalAbsences === 0) {
            return [
                'answer' => "No absences are recorded since {$since} — every attendance row in that window is "
                    . 'marked REGULAR, LEAVE, TRAVEL_ORDER, or HOLIDAY.',
                'data' => ['since' => $since, 'departments' => $ranked->all()],
            ];
        }

        $top = $ranked->first();
        $lines = $ranked->where('absent_days', '>', 0)->take(5)
            ->map(fn ($r) => "- {$r['department']}: {$r['absence_rate']}% ({$r['absent_days']} of {$r['total_days']} days)")
            ->implode("\n");

        return [
            'answer' => "**{$top['department']}** has the highest absenteeism at **{$top['absence_rate']}%** "
                . "over the last 3 months ({$totalAbsences} recorded absences in total).\n\n{$lines}",
            'data' => ['since' => $since, 'total_absences' => $totalAbsences, 'departments' => $ranked->all()],
        ];
    }

    private function pendingApprovals(): array
    {
        $leave = LeaveApplication::where('status', 'pending')->count();
        $travel = DB::table('travel_orders')->whereIn('status', ['pending', 'awaiting_companions'])->count();
        $trainings = DB::table('trainings')->where('status', 'pending')->count();
        $documents = DB::table('documents')->where('status', 'pending')->count();
        $monetization = DB::table('monetization_requests')->where('status', 'pending')->count();

        $total = $leave + $travel + $trainings + $documents + $monetization;

        return [
            'answer' => "There are **{$total} item(s) awaiting approval**:\n\n"
                . "- Leave applications: {$leave}\n"
                . "- Travel orders: {$travel}\n"
                . "- Training records: {$trainings}\n"
                . "- Uploaded documents: {$documents}\n"
                . "- Monetization requests: {$monetization}",
            'data' => [
                'total' => $total,
                'leave_applications' => $leave,
                'travel_orders' => $travel,
                'trainings' => $trainings,
                'documents' => $documents,
                'monetization_requests' => $monetization,
            ],
        ];
    }

    private function headcountByDepartment(): array
    {
        $rows = DB::table('employees')
            ->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->join('departments', 'employment_details.department_id', '=', 'departments.id')
            ->selectRaw('departments.name AS department, COUNT(employees.id) AS total')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['department' => $row->department, 'count' => (int) $row->total])
            ->all();

        $unassigned = Employee::whereDoesntHave('employmentDetail')->count();

        $lines = collect($rows)->map(fn ($r) => "- {$r['department']}: {$r['count']}")->implode("\n");
        $note = $unassigned > 0 ? "\n\n{$unassigned} employee(s) have no employment details on file." : '';

        return [
            'answer' => "**Headcount by department:**\n\n{$lines}{$note}",
            'data' => ['departments' => $rows, 'unassigned' => $unassigned],
        ];
    }

    private function missingRequirements(): array
    {
        $noGovIds = Employee::whereDoesntHave('governmentIds')->count();
        $noEmployment = Employee::whereDoesntHave('employmentDetail')->count();
        $noDocuments = Employee::whereDoesntHave('documents')->count();

        $incompleteGovIds = DB::table('government_ids')
            ->whereNull('gsis_no')->orWhereNull('philhealth_no')
            ->orWhereNull('pagibig_no')->orWhereNull('tin_no')
            ->count();

        return [
            'answer' => "**Records with missing requirements:**\n\n"
                . "- No government IDs on file: {$noGovIds}\n"
                . "- Government ID record incomplete (missing GSIS/PhilHealth/Pag-IBIG/TIN): {$incompleteGovIds}\n"
                . "- No employment details: {$noEmployment}\n"
                . "- No uploaded documents: {$noDocuments}",
            'data' => [
                'no_government_ids' => $noGovIds,
                'incomplete_government_ids' => $incompleteGovIds,
                'no_employment_details' => $noEmployment,
                'no_documents' => $noDocuments,
            ],
        ];
    }

    /**
     * The spec asks for "show all expired IDs", but this schema stores no
     * expiry date anywhere: government_ids holds only the ID numbers
     * (gsis_no, philhealth_no, pagibig_no, tin_no, license_no) and
     * legal_requirements holds only saln_submitted / oath_of_office /
     * assumption_date. Rather than invent a date, say so plainly.
     */
    private function expiringIds(): array
    {
        return [
            'answer' => "I cannot answer that from the current database. ID expiry dates are not stored anywhere: "
                . "`government_ids` holds only the ID numbers (GSIS, PhilHealth, Pag-IBIG, TIN, license), with no issue or expiry date columns.\n\n"
                . 'To enable this, add `issued_date` and `expiry_date` columns to `government_ids` — once those exist I can report expired and soon-to-expire IDs.',
            'data' => ['supported' => false, 'reason' => 'no expiry columns in government_ids'],
        ];
    }

    private function newHires(): array
    {
        $year = now()->year;

        $thisYear = DB::table('employment_details')->whereYear('appointment_date', $year)->count();

        $monthly = DB::table('employment_details')
            ->selectRaw('MONTH(appointment_date) AS month, COUNT(*) AS total')
            ->whereYear('appointment_date', $year)
            ->groupByRaw('MONTH(appointment_date)')
            ->orderByRaw('MONTH(appointment_date)')
            ->get()
            ->map(fn ($row) => [
                'month' => date('F', mktime(0, 0, 0, (int) $row->month, 1)),
                'count' => (int) $row->total,
            ])
            ->all();

        $lines = collect($monthly)->map(fn ($r) => "- {$r['month']}: {$r['count']}")->implode("\n");

        return [
            'answer' => "**{$thisYear} employee(s) were appointed in {$year}.**\n\n{$lines}",
            'data' => ['year' => $year, 'total' => $thisYear, 'monthly' => $monthly],
        ];
    }

    private function overview(): array
    {
        $employees = Employee::count();
        $departments = DB::table('departments')->count();
        $onLeave = $this->onLeaveToday()['data']['count'];
        $pending = $this->pendingApprovals()['data']['total'];

        return [
            'answer' => "**System overview**\n\n"
                . "- Employees on record: {$employees}\n"
                . "- Departments: {$departments}\n"
                . "- On approved leave today: {$onLeave}\n"
                . "- Items awaiting approval: {$pending}\n\n"
                . 'Ask me about absenteeism by department, headcount, new hires, pending approvals, or missing requirements.',
            'data' => [
                'employees' => $employees,
                'departments' => $departments,
                'on_leave_today' => $onLeave,
                'pending_approvals' => $pending,
            ],
        ];
    }
}
