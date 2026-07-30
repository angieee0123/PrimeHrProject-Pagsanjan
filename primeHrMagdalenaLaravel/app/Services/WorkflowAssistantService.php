<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Feature 11: Smart Workflow Assistant.
 *
 * Prepares the documents and summaries an HR officer would otherwise assemble
 * by hand. Every task pulls the real records first and only then asks the model
 * to write the prose, so figures in the output come from the database rather
 * than from the model's imagination.
 *
 * Nothing here writes to the database — these are drafts for a human to review,
 * approve, and act on.
 */
class WorkflowAssistantService
{
    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function handle(User $user, string $question, array $history = []): array
    {
        $task = $this->detectTask($question);

        if ($task === null) {
            return [
                'answer' => "I can prepare:\n\n"
                    . "- **Leave approval summary** — everything pending, with balances\n"
                    . "- **Payroll preview** — computed payslips awaiting approval\n"
                    . "- **Employee summary** — a full profile for one person\n"
                    . "- **Onboarding checklist** — what a new hire still owes\n"
                    . "- **HR letter** — certificate of employment, notice, memo\n\n"
                    . 'Say which one, and name the employee or period if it applies.',
                'data' => [],
            ];
        }

        if (!$this->policy->hasOrgWideAccess($user) && $task !== 'employee_summary') {
            return [
                'answer' => 'Preparing organisation-wide workflow documents is limited to HR, admin, and mayor accounts.',
                'data' => [],
            ];
        }

        return match ($task) {
            'leave_approval_summary' => $this->leaveApprovalSummary($user, $question, $history),
            'payroll_preview' => $this->payrollPreview($user, $question, $history),
            'employee_summary' => $this->employeeSummary($user, $question, $history),
            'onboarding_checklist' => $this->onboardingChecklist($user, $question, $history),
            'hr_letter' => $this->hrLetter($user, $question, $history),
            default => ['answer' => 'That workflow is not supported yet.', 'data' => []],
        };
    }

    private function detectTask(string $question): ?string
    {
        $q = strtolower($question);

        return match (true) {
            (bool) preg_match('/\bleave\b.*\b(approval|summary|pending)\b|\bapproval\s+summary\b/', $q) => 'leave_approval_summary',
            (bool) preg_match('/\bpayroll\b.*\b(preview|draft|pending)\b|\bpreview\b.*\bpayroll\b/', $q) => 'payroll_preview',
            (bool) preg_match('/\bonboarding\b|\bnew\s+hire\s+checklist\b|\brequirements?\s+checklist\b/', $q) => 'onboarding_checklist',
            (bool) preg_match('/\b(letter|memo|notice|certificate\s+of\s+employment|coe)\b/', $q) => 'hr_letter',
            (bool) preg_match('/\bemployee\s+summary\b|\bprofile\s+summary\b|\bsummary\s+(?:for|of)\b/', $q) => 'employee_summary',
            default => null,
        };
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function leaveApprovalSummary(User $user, string $question, array $history): array
    {
        $pending = DB::table('leave_applications')
            ->join('employees', 'leave_applications.employee_id', '=', 'employees.id')
            ->leftJoin('employment_details', 'employees.id', '=', 'employment_details.employee_id')
            ->leftJoin('departments', 'employment_details.department_id', '=', 'departments.id')
            ->leftJoin('leave_types_config', 'leave_applications.leave_code', '=', 'leave_types_config.leave_code')
            ->selectRaw("
                leave_applications.id,
                leave_applications.application_number,
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
                COALESCE(departments.name, '—') AS department,
                COALESCE(leave_types_config.leave_name, leave_applications.leave_code) AS leave_type,
                leave_applications.leave_code,
                leave_applications.start_date,
                leave_applications.end_date,
                leave_applications.number_of_days,
                leave_applications.reason,
                leave_applications.employee_id
            ")
            ->where('leave_applications.status', 'pending')
            ->orderBy('leave_applications.start_date')
            ->limit(100)
            ->get();

        if ($pending->isEmpty()) {
            return ['answer' => 'There are no leave applications awaiting approval.', 'data' => []];
        }

        // Attach the balance for the specific leave code being requested, so
        // the approver can see whether the employee can actually afford it.
        $year = now()->year;
        $balances = DB::table('leave_balances')
            ->whereIn('employee_id', $pending->pluck('employee_id')->unique())
            ->where('year', $year)
            ->get()
            ->keyBy(fn ($row) => $row->employee_id . '|' . $row->leave_code);

        $rows = $pending->map(function ($row) use ($balances) {
            $balance = $balances->get($row->employee_id . '|' . $row->leave_code);
            $available = $balance ? (float) $balance->available_credits : null;

            return [
                'application_number' => $row->application_number,
                'employee' => $row->employee,
                'department' => $row->department,
                'leave_type' => $row->leave_type,
                'from' => $row->start_date,
                'to' => $row->end_date,
                'days' => (float) $row->number_of_days,
                'available_credits' => $available,
                'sufficient' => $available === null ? 'unknown' : ($available >= (float) $row->number_of_days ? 'yes' : 'NO'),
                'reason' => $row->reason,
            ];
        })->all();

        $short = array_filter($rows, fn (array $r) => $r['sufficient'] === 'NO');

        $system = <<<'PROMPT'
You are preparing a leave approval summary for an HR approver.

- Open with the number of applications awaiting a decision and the total days.
- Group by department where that helps.
- Flag prominently anyone whose available credits are below the days requested
  — those need a decision on leave-without-pay.
- Use only the figures given. Never invent a balance.
- End with a one-line recommendation of what to review first.
- Under 200 words.
PROMPT;

        $payload = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => "Pending applications:\n{$payload}"];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 900)
            ?? sprintf(
                '%d leave application(s) awaiting approval; %d have insufficient credits.',
                count($rows),
                count($short)
            );

        return [
            'answer' => $answer,
            'data' => $rows,
            'report' => [
                'key' => 'leave_approval_summary',
                'title' => 'Leave Approval Summary',
                'columns' => [
                    ['key' => 'application_number', 'label' => 'Application'],
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'department', 'label' => 'Department'],
                    ['key' => 'leave_type', 'label' => 'Type'],
                    ['key' => 'from', 'label' => 'From'],
                    ['key' => 'to', 'label' => 'To'],
                    ['key' => 'days', 'label' => 'Days', 'align' => 'right'],
                    ['key' => 'available_credits', 'label' => 'Credits', 'align' => 'right'],
                    ['key' => 'sufficient', 'label' => 'Sufficient'],
                ],
                'totals' => [
                    'Applications' => count($rows),
                    'Total days' => round(array_sum(array_column($rows, 'days')), 2),
                    'Insufficient credits' => count($short),
                ],
                'row_count' => count($rows),
            ],
        ];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function payrollPreview(User $user, string $question, array $history): array
    {
        $rows = DB::table('salary_computations')
            ->join('employees', 'salary_computations.employee_id', '=', 'employees.id')
            ->selectRaw("
                CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee,
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
            ->whereIn('salary_computations.status', ['draft', 'pending'])
            ->orderByDesc('salary_computations.period_start')
            ->limit(200)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();

        if (empty($rows)) {
            return ['answer' => 'There are no draft or pending salary computations to preview.', 'data' => []];
        }

        $gross = round(array_sum(array_map(fn ($r) => (float) $r['gross_pay'], $rows)), 2);
        $net = round(array_sum(array_map(fn ($r) => (float) $r['net_pay'], $rows)), 2);
        $deductions = round($gross - $net, 2);

        $system = 'You are presenting a payroll preview to an HR officer before approval. State the number of '
            . 'payslips, the period, gross, total deductions, and net. Call out any payslip whose deductions look '
            . 'unusually large relative to basic pay. Use only the figures given. Under 150 words.';

        $payload = json_encode(array_slice($rows, 0, 25), JSON_PRETTY_PRINT);
        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => "Totals — gross {$gross}, deductions {$deductions}, net {$net}.\n\nSample:\n{$payload}"];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 700)
            ?? sprintf('%d payslip(s) pending: gross %s, deductions %s, net %s.', count($rows), $gross, $deductions, $net);

        return [
            'answer' => $answer,
            'data' => $rows,
            'report' => [
                'key' => 'payroll_preview',
                'title' => 'Payroll Preview (draft & pending)',
                'columns' => [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'period_start', 'label' => 'From'],
                    ['key' => 'period_end', 'label' => 'To'],
                    ['key' => 'basic_pay', 'label' => 'Basic', 'align' => 'right', 'format' => 'money'],
                    ['key' => 'gross_pay', 'label' => 'Gross', 'align' => 'right', 'format' => 'money'],
                    ['key' => 'net_pay', 'label' => 'Net', 'align' => 'right', 'format' => 'money'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'totals' => ['Payslips' => count($rows), 'Gross' => $gross, 'Deductions' => $deductions, 'Net' => $net],
                'row_count' => count($rows),
            ],
        ];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function employeeSummary(User $user, string $question, array $history): array
    {
        $employee = $this->resolveEmployee($user, $question);

        if (!$employee) {
            return [
                'answer' => 'Tell me which employee — a name or employee number — and I will put the summary together.',
                'data' => [],
            ];
        }

        if (!$this->policy->canAccessEmployee($user, (int) $employee->id)) {
            return ['answer' => 'You do not have permission to view that employee record.', 'data' => []];
        }

        $employee->load([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
            'governmentIds',
            'educations',
            'trainings',
            'documents',
        ]);

        $balances = DB::table('leave_balances')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get()
            ->map(fn ($r) => "{$r->leave_code}: " . round((float) $r->available_credits, 3))
            ->implode(', ');

        $profile = [
            'employee_no' => $employee->employee_id,
            'name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name} {$employee->suffix}"),
            'sex' => $employee->sex,
            'civil_status' => $employee->civil_status,
            'birth_date' => $employee->birth_date,
            'email' => $employee->email,
            'department' => $employee->employmentDetail?->departmentRelation?->name,
            'position' => $employee->employmentDetail?->designationRelation?->title,
            'employment_status' => $employee->employmentDetail?->employment_status,
            'appointment_date' => $employee->employmentDetail?->appointment_date,
            'salary_grade' => $employee->employmentDetail?->salary_grade,
            'leave_credits' => $balances ?: 'none on record',
            'trainings_count' => $employee->trainings->count(),
            'documents_count' => $employee->documents->count(),
            'education_count' => $employee->educations->count(),
            'has_government_ids' => $employee->governmentIds->isNotEmpty(),
        ];

        $system = 'You are writing a concise employee summary for an HR file. Cover identity, position and '
            . 'department, appointment details, leave credits, and completeness of records. Note any gap '
            . '(missing government IDs, no documents on file). Use only the data given. Under 200 words.';

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 800)
            ?? ('Summary for ' . $profile['name'] . ' (' . $profile['employee_no'] . ').');

        return ['answer' => $answer, 'data' => [$profile]];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function onboardingChecklist(User $user, string $question, array $history): array
    {
        $employee = $this->resolveEmployee($user, $question);

        if (!$employee) {
            $recent = DB::table('employment_details')
                ->join('employees', 'employment_details.employee_id', '=', 'employees.id')
                ->selectRaw("CONCAT_WS(' ', employees.first_name, employees.last_name) AS employee, employees.employee_id AS employee_no, employment_details.appointment_date")
                ->whereDate('employment_details.appointment_date', '>=', now()->subMonths(3)->toDateString())
                ->orderByDesc('employment_details.appointment_date')
                ->limit(20)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->all();

            return [
                'answer' => empty($recent)
                    ? 'No appointments in the last three months. Name an employee and I will build their checklist.'
                    : 'Which new hire? Recent appointments are listed below.',
                'data' => $recent,
            ];
        }

        $employee->load(['governmentIds', 'documents', 'educations', 'employmentDetail', 'addresses', 'contacts']);

        $govIds = $employee->governmentIds->first();

        // Each item is derived from an actual record check, not a template.
        $checklist = [
            ['requirement' => 'Employment details recorded', 'complete' => $employee->employmentDetail !== null],
            ['requirement' => 'Home address on file', 'complete' => $employee->addresses->isNotEmpty()],
            ['requirement' => 'Contact number on file', 'complete' => $employee->contacts->isNotEmpty()],
            ['requirement' => 'Email address', 'complete' => !empty($employee->email)],
            ['requirement' => 'GSIS number', 'complete' => !empty($govIds?->gsis_no)],
            ['requirement' => 'PhilHealth number', 'complete' => !empty($govIds?->philhealth_no)],
            ['requirement' => 'Pag-IBIG number', 'complete' => !empty($govIds?->pagibig_no)],
            ['requirement' => 'TIN', 'complete' => !empty($govIds?->tin_no)],
            ['requirement' => 'Educational background', 'complete' => $employee->educations->isNotEmpty()],
            ['requirement' => 'Supporting documents uploaded', 'complete' => $employee->documents->isNotEmpty()],
            ['requirement' => 'System user account', 'complete' => $employee->user()->exists()],
        ];

        $rows = array_map(fn (array $item) => [
            'requirement' => $item['requirement'],
            'status' => $item['complete'] ? 'Complete' : 'Outstanding',
        ], $checklist);

        $outstanding = array_values(array_filter($rows, fn (array $r) => $r['status'] === 'Outstanding'));
        $name = trim("{$employee->first_name} {$employee->last_name}");

        $answer = empty($outstanding)
            ? "**Onboarding checklist — {$name}**\n\nAll " . count($rows) . ' requirements are complete.'
            : "**Onboarding checklist — {$name}**\n\n"
                . count($outstanding) . ' of ' . count($rows) . " requirements still outstanding:\n\n- "
                . implode("\n- ", array_column($outstanding, 'requirement'));

        return [
            'answer' => $answer,
            'data' => $rows,
            'report' => [
                'key' => 'onboarding_checklist',
                'title' => "Onboarding Checklist — {$name}",
                'columns' => [
                    ['key' => 'requirement', 'label' => 'Requirement'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'totals' => ['Complete' => count($rows) - count($outstanding), 'Outstanding' => count($outstanding)],
                'row_count' => count($rows),
            ],
        ];
    }

    /**
     * Drafts a letter for a human to review and sign. Never asserts anything
     * the record does not support.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    private function hrLetter(User $user, string $question, array $history): array
    {
        $employee = $this->resolveEmployee($user, $question);

        if (!$employee) {
            return [
                'answer' => 'Name the employee the letter is for, and the kind of letter '
                    . '(certificate of employment, memo, notice).',
                'data' => [],
            ];
        }

        if (!$this->policy->canAccessEmployee($user, (int) $employee->id)) {
            return ['answer' => 'You do not have permission to draft a letter for that employee.', 'data' => []];
        }

        $employee->load(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation']);

        $facts = [
            'full_name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name} {$employee->suffix}"),
            'employee_no' => $employee->employee_id,
            'position' => $employee->employmentDetail?->designationRelation?->title,
            'department' => $employee->employmentDetail?->departmentRelation?->name,
            'employment_status' => $employee->employmentDetail?->employment_status,
            'appointment_date' => $employee->employmentDetail?->appointment_date,
            'salary_grade' => $employee->employmentDetail?->salary_grade,
            'today' => now()->format('F j, Y'),
        ];

        $system = <<<'PROMPT'
Draft a formal HR letter for a Philippine local government unit, for a human
officer to review and sign.

- Use ONLY the facts supplied. If a fact needed for the letter is missing,
  insert a clearly marked placeholder like [SALARY — not on record] rather
  than inventing it.
- Standard business format: date, addressee block, subject line, body, and a
  signature block reading "[Name], [Designation], Human Resource Management Office".
- Do not state compensation figures unless they were supplied.
- Return the letter text only.
PROMPT;

        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => "Requested: {$question}\n\nEmployee facts:\n" . json_encode($facts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];

        $letter = AiChatService::chat($user, $system, $messages, 0.4, 1200);

        if (!$letter) {
            return [
                'answer' => 'No AI provider is configured, so I cannot draft the letter. '
                    . 'Set one under Settings → AI/Chatbot.',
                'data' => [],
            ];
        }

        return [
            'answer' => $letter . "\n\n---\n*Draft only — review and sign before issuing.*",
            'data' => [$facts],
        ];
    }

    /**
     * Find the employee a workflow request is about, honouring access scope.
     */
    private function resolveEmployee(User $user, string $question): ?Employee
    {
        if (preg_match('/\b(\d{4}-\d{2,4})\b/', $question, $m)) {
            $byNumber = Employee::where('employee_id', $m[1])->first();
            if ($byNumber) {
                return $byNumber;
            }
        }

        if (preg_match('/"([^"]+)"/', $question, $m)) {
            $name = trim($m[1]);
        } elseif (preg_match('/\b(?:for|of|to)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/', $question, $m)) {
            $name = trim($m[1]);
        } elseif (preg_match('/\b([A-Z][a-z]{2,}\s+[A-Z][a-z]{2,})\b/', $question, $m)) {
            $name = trim($m[1]);
        } else {
            // No name given: an employee asking about themselves is unambiguous.
            $own = $this->policy->ownEmployeeId($user);

            return $own ? Employee::find($own) : null;
        }

        return Employee::whereRaw("CONCAT_WS(' ', first_name, last_name) LIKE ?", ["%{$name}%"])
            ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$name}%"])
            ->first();
    }
}
