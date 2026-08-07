<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\SalaryComputation;
use App\Models\Training;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeeChatbotService
{
    public function __construct(private HrPolicyFactsService $facts)
    {
    }

    /**
     * The self-service navigation map. Menu paths live in Blade views, so there
     * is no table to read them from — unlike the policy figures above them,
     * which used to be typed here too (480, the 5-minute grace, "8:00–13:00",
     * "VL first then SL") and now come from HrPolicyFactsService, scoped to
     * *this* employee's own schedule rather than a assumed standard one.
     */
    private const NAVIGATION = <<<'TEXT'
HOW TO USE THE SYSTEM (employee self-service):
- Leave: Leave & Benefits → File Leave (select type, dates, reason; attach if required).
- Payslip: Payslip section to view/download periods.
- Attendance: Attendance / DTR for daily records and summary.
- Training: Training → Add New Training (upload certificate, submit for HR verification).
- Travel: Travel Orders → File Travel Order.
- Profile: update personal info where allowed.

PRIVACY: Employees may only view their own HR records, not coworkers' data.
TEXT;

    private function knowledge(Employee $employee): string
    {
        return $this->facts->knowledgeBlock($employee) . "\n\n" . self::NAVIGATION;
    }

    /**
     * The same self-scoped answer `handle()` produces, in the shape
     * AiQueryService dispatches (`answer` + `follow_ups`).
     *
     * This exists so the web assistant and the mobile chatbot share one
     * employee-facing brain instead of diverging. Every context builder below
     * filters on the employee's own id, which is what makes this path safe for
     * a caller who may not run generated SQL.
     *
     * No history parameter: the orchestrator resolves follow-ups into a
     * standalone question before dispatching, so the message arriving here
     * already names its own subject.
     *
     * @return array{answer: string, follow_ups: array<int, string>}
     */
    public function assist(Employee $employee, string $message): array
    {
        $result = $this->handle($employee, $message);

        return [
            'answer' => $result['response'],
            'follow_ups' => $result['follow_up_questions'] ?? [],
        ];
    }

    public function handle(Employee $employee, string $message): array
    {
        $message = trim($message);

        $validation = $this->validateQuery($message);
        if (!$validation['valid']) {
            return $this->result($validation['message'], $this->defaultFollowUps());
        }

        if ($this->isGreeting($message)) {
            $name = trim($employee->first_name . ' ' . $employee->last_name);
            $greeting = $name !== '' ? "Hello, {$name}!" : 'Hello!';
            return $this->result(
                "{$greeting} I'm your PRIME HRIS assistant. I can answer questions about **your** leave, payslip, attendance, training, and travel records, plus explain how our HR system works. What would you like to know?",
                [
                    'What is my leave balance?',
                    'How do I file a leave request?',
                    'Show my latest payslip',
                    'How is late deduction calculated?',
                ]
            );
        }

        if ($this->isCrossEmployeeQuery($message, $employee)) {
            return $this->result(
                "For privacy and data protection, I can only share **your own** HR information—not records of other employees. I can help with your leave balance, payslip, attendance, training, travel orders, or explain how to use PRIME HRIS. What would you like to know about your account?",
                $this->defaultFollowUps()
            );
        }

        // "How much leave did my late cost?" is a computation, not a policy
        // question, and it must not be answered by the model applying the rules
        // in its prompt — that is arithmetic on someone's pay. Run the real
        // conversion over the employee's own logged minutes instead.
        $computed = $this->computedDeductionAnswer($employee, $message);
        if ($computed !== null) {
            return $this->result($computed['answer'], $computed['follow_up']);
        }

        $policy = $this->getPolicyAnswer($employee, $message);
        if ($policy !== null) {
            return $this->result($policy['answer'], $policy['follow_up']);
        }

        $intent = $this->detectIntent($message);
        $context = $this->buildEmployeeContext($employee, $intent, $message);
        $response = $this->generateResponse($message, $context, $employee);

        return $this->result($response, $this->followUpsForIntent($intent));
    }

    private function result(string $response, array $followUps = []): array
    {
        return [
            'response' => $response,
            'follow_up_questions' => $followUps,
            'status' => 'success',
        ];
    }

    private function validateQuery(string $query): array
    {
        if (strlen($query) < 2) {
            return ['valid' => false, 'message' => 'Your message is too short. Please add a bit more detail.'];
        }

        $vowels = preg_match_all('/[aeiouAEIOUáéíóú]/u', $query);
        $consonants = preg_match_all('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZñ]/u', $query);

        if ($consonants > 0 && ($vowels / max($consonants, 1)) < 0.15) {
            return ['valid' => false, 'message' => "I couldn't understand that. Could you rephrase your question?"];
        }

        return ['valid' => true];
    }

    private function isGreeting(string $message): bool
    {
        return (bool) preg_match(
            '/^(hi|hello|hey|good\s+(morning|afternoon|evening)|kumusta|kamusta|magandang)\b/i',
            $message
        ) && str_word_count($message) <= 8;
    }

    private function isCrossEmployeeQuery(string $message, Employee $employee): bool
    {
        $lower = Str::lower($message);

        if (preg_match('/\b(my|mine|ako|aking|sarili)\b/u', $lower)) {
            return false;
        }

        if (preg_match('/\b(who am i|about me|my profile|my info|aking profile)\b/u', $lower)) {
            return false;
        }

        $blocked = [
            '/\b(how many|ilan|ilang|total|count|number of)\b.*\b(employee|empleyado|staff|personnel|tao|workers)\b/u',
            '/\b(list|show|display|ipakita)\b.*\b(all\s+)?(employee|empleyado|staff)\b/u',
            '/\b(who works|sino ang|employees? in|staff in|personnel in)\b/u',
            '/\b(find|search|hanapin|sino si|who is)\b(?!.*\b(me|myself|ako)\b)/u',
            '/\b(department head|head of|opisina ng)\b/u',
            '/\b(salary|sweldo|sahod)\s+of\b/u',
            '/\bother employee|ibang empleyado|coworker|co-worker|katrabaho\b/u',
            '/\b(admin|all users|everyone)\b.*\b(data|record|info)\b/u',
        ];

        foreach ($blocked as $pattern) {
            if (preg_match($pattern, $lower)) {
                return true;
            }
        }

        $selfName = Str::lower(trim($employee->first_name . ' ' . $employee->last_name));
        if (preg_match('/\b(find|who is|sino si|info about|details of)\b/u', $lower)) {
            if ($selfName && !str_contains($lower, $selfName) && !str_contains($lower, Str::lower($employee->first_name))) {
                if (preg_match('/\b[A-Z][a-z]+\s+[A-Z][a-z]+\b/', $message)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * "How much leave did my late cost?" answered from the employee's own
     * logged minutes, using the system's own conversion.
     *
     * The prompt used to carry a worked example ("30 mins late → 0.0625 VL
     * day") and the model was left to apply it. That is arithmetic on a real
     * person's pay done by a language model from a remembered rule — the exact
     * thing that produces a confident wrong number. `accredited_hours_log`
     * already stores the minutes the system computed, and
     * CscTimeConversionService already owns the conversion, so this reads both
     * and states the result.
     *
     * @return array{answer: string, follow_up: array<int, string>}|null
     */
    private function computedDeductionAnswer(Employee $employee, string $message): ?array
    {
        $q = Str::lower($message);

        $asksCost = preg_match('/\b(how\s+much|magkano|ilan|how\s+many)\b/u', $q)
            && preg_match('/\b(late|undertime|deduct\w*|nabawas|bawas)\b/u', $q);

        if (!$asksCost) {
            return null;
        }

        $f = $this->facts->facts();
        $minutesPerDay = $f['conversion']['minutes_per_day'];
        $order = $f['payroll']['deduction_order'];

        $year = $this->detectYear($message);

        try {
            $totals = \App\Models\AccreditedHoursLog::query()
                ->where('employee_id', $employee->id)
                ->whereYear('created_at', $year)
                ->selectRaw('COALESCE(SUM(late_minutes),0) late, COALESCE(SUM(undertime_minutes),0) undertime, COUNT(*) days')
                ->first();
        } catch (\Throwable) {
            return null; // Fall through to the ordinary path rather than guess.
        }

        $late = (int) ($totals->late ?? 0);
        $undertime = (int) ($totals->undertime ?? 0);

        if ($late === 0 && $undertime === 0) {
            return [
                'answer' => "You have **no late or undertime minutes recorded for {$year}**, so nothing has "
                    . 'been deducted from your leave credits on that basis.',
                'follow_up' => ['What is my leave balance?', 'What is my attendance this month?'],
            ];
        }

        // The system's own conversion, not a restated formula.
        $lateDays = CscTimeConversionService::convertMinutesToDays($late);
        $undertimeDays = CscTimeConversionService::convertMinutesToDays($undertime);
        $totalDays = $lateDays + $undertimeDays;

        $lines = ["**Your late and undertime for {$year}**", ''];

        if ($late > 0) {
            $lines[] = "- Late: **{$late} minute(s)** = " . $this->days($lateDays) . ' day(s)';
        }

        if ($undertime > 0) {
            $lines[] = "- Undertime: **{$undertime} minute(s)** = " . $this->days($undertimeDays) . ' day(s)';
        }

        $lines[] = '- Total to be covered by leave credits: **' . $this->days($totalDays) . ' day(s)**';
        $lines[] = '';
        $lines[] = "At {$minutesPerDay} minutes = 1 work day, drawn from **" . implode('** then **', $order)
            . '**. Anything your credits cannot cover becomes LWOP.';
        $lines[] = '';
        $lines[] = '_Computed from your accredited-hours log, not estimated._';

        return [
            'answer' => implode("\n", $lines),
            'follow_up' => ['What is my leave balance?', 'What is my attendance this month?', 'What is LWOP?'],
        ];
    }

    /** Leave-credit days are carried to 4 decimals by the CSC conversion. */
    private function days(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.') ?: '0';
    }

    private function detectYear(string $message): int
    {
        return preg_match('/\b(20\d{2})\b/', $message, $m) ? (int) $m[1] : (int) date('Y');
    }

    private function getPolicyAnswer(Employee $employee, string $question): ?array
    {
        $q = Str::lower($question);

        // Read, never written into the sentence — see HrPolicyFactsService.
        $f = $this->facts->facts();
        $minutesPerDay = $f['conversion']['minutes_per_day'];
        $grace = $f['attendance']['grace_minutes'];
        $order = $f['payroll']['deduction_order'];
        $orderText = '**' . implode('** first, then **', $order) . '**';

        if (preg_match('/\b(late|na-late|nalate)\b/u', $q)) {
            return [
                'answer' => "Late deduction is automatic: late minutes are taken from {$orderText}. "
                    . "The system uses **{$minutesPerDay} minutes = 1 work day**. There is a "
                    . "**{$grace}-minute grace period** after your scheduled AM and PM time-in. "
                    . 'If leave credits cannot cover the late time, the remainder becomes **LWOP** (Leave Without Pay).',
                'follow_up' => ['How much leave did my late cost?', 'What is my leave balance?', 'What are the working hours?'],
            ];
        }

        if (preg_match('/\b(grace\s*period|grace)\b/u', $q)) {
            $s = $this->facts->scheduleFor($employee);

            return [
                'answer' => "The grace period is **{$grace} minutes** after AM In (**{$s['am_in']}**) and "
                    . "PM In (**{$s['pm_in']}**) on {$s['source']}. Clocking in within that window is not "
                    . 'counted as late.',
                'follow_up' => ['How is late deduction calculated?', 'What are my attendance records?'],
            ];
        }

        if (preg_match('/\b(lwop|leave without pay)\b/u', $q)) {
            return [
                'answer' => '**LWOP (Leave Without Pay)** applies when your late or undertime minutes exceed '
                    . 'your available ' . implode('/', $order) . ' credits. Those uncovered minutes reduce '
                    . 'your accredited hours and your pay for that day.',
                'follow_up' => ['How much leave did my late cost?', 'What is my leave balance?'],
            ];
        }

        if (preg_match('/\b(working hours|schedule|oras ng trabaho)\b/u', $q)) {
            $s = $this->facts->scheduleFor($employee);
            $hours = $f['conversion']['hours_per_day'];

            return [
                'answer' => "Your schedule: **AM {$s['am_in']}–{$s['am_out']}**, "
                    . "**PM {$s['pm_in']}–{$s['pm_out']}** ({$hours} hours total), from {$s['source']}.",
                'follow_up' => ['How do I file leave?', 'View my attendance'],
            ];
        }

        if (preg_match('/\b(how to|paano|file|submit|apply).*\b(leave|bakasyon)\b/u', $q)) {
            return [
                'answer' => "To file leave in PRIME HRIS:\n1. Open **Leave & Benefits**\n2. Tap **File Leave**\n3. Choose leave type and dates (working days only)\n4. Enter your reason and attach a document if required\n5. Submit—status will be **Pending** until HR approves",
                'follow_up' => ['What is my leave balance?', 'How is late deduction calculated?'],
            ];
        }

        if (preg_match('/\b(how to|paano).*\b(payslip|payroll|sweldo)\b/u', $q)) {
            return [
                'answer' => 'Open the **Payslip** section in the app or portal to see your pay periods, basic pay, deductions, and net pay. You can view details for each period there.',
                'follow_up' => ['What is my latest payslip?', 'What are my deductions?'],
            ];
        }

        if (preg_match('/\b(how to|paano).*\b(training|seminar)\b/u', $q)) {
            return [
                'answer' => 'Go to **Training** → **Add New Training**. Upload your certificate (PDF/image), fill in the seminar details and reference document number, then submit for HR verification. Only **verified** hours count toward your annual L&D goal.',
                'follow_up' => ['How many training hours do I have?', 'What is my training status?'],
            ];
        }

        if (preg_match('/\b(how to|paano).*\b(travel order|travel)\b/u', $q)) {
            return [
                'answer' => 'Open **Travel Orders** → **File Travel**. Enter destination, purpose, travel dates, transportation mode, and optional budget. Submit for approval—only **pending** orders can be cancelled.',
                'follow_up' => ['What are my travel orders?', 'How do I file leave?'],
            ];
        }

        return null;
    }

    private function detectIntent(string $message): string
    {
        $q = Str::lower($message);

        if (preg_match('/\b(profile|who am i|my info|employee id|position|department)\b/u', $q)) {
            return 'profile';
        }
        if (preg_match('/\b(leave|vacation|vl|sl|sick|bakasyon|bakasyon)\b/u', $q)) {
            return 'leave';
        }
        if (preg_match('/\b(payslip|payroll|salary|net pay|sweldo|sahod|deduction)\b/u', $q)) {
            return 'payslip';
        }
        if (preg_match('/\b(attendance|dtr|time record|present|absent|late|undertime)\b/u', $q)) {
            return 'attendance';
        }
        if (preg_match('/\b(training|seminar|l&d|learning)\b/u', $q)) {
            return 'training';
        }
        if (preg_match('/\b(travel order|official travel|travel)\b/u', $q)) {
            return 'travel';
        }
        if (preg_match('/\b(performance|evaluation|rating|ipcr)\b/u', $q)) {
            return 'performance';
        }
        if (preg_match('/\b(how to|paano|what is|explain|system|process|policy|rule)\b/u', $q)) {
            return 'system';
        }

        return 'general';
    }

    private function buildEmployeeContext(Employee $employee, string $intent, string $message): string
    {
        $employee->loadMissing([
            'employmentDetail.designationRelation',
            'employmentDetail.departmentRelation',
            'user',
        ]);

        $lines = [];
        $lines[] = '=== EMPLOYEE (logged-in user only) ===';
        $lines[] = 'Name: ' . trim($employee->first_name . ' ' . $employee->last_name);
        $lines[] = 'Employee ID: ' . ($employee->employee_id ?? 'N/A');
        $lines[] = 'Position: ' . ($employee->employmentDetail?->designationRelation?->title
            ?? $employee->employmentDetail?->position ?? 'N/A');
        $lines[] = 'Department: ' . ($employee->employmentDetail?->departmentRelation?->name ?? 'N/A');
        $lines[] = 'Employment status: ' . ($employee->employmentDetail?->employment_status ?? 'N/A');

        $year = (int) date('Y');

        if (in_array($intent, ['leave', 'general', 'system'], true)) {
            $lines[] = $this->leaveContext($employee->id, $year);
        }
        if (in_array($intent, ['payslip', 'general'], true)) {
            $lines[] = $this->payslipContext($employee->id);
        }
        if (in_array($intent, ['attendance', 'general'], true)) {
            $lines[] = $this->attendanceContext($employee->id);
        }
        if (in_array($intent, ['training', 'general'], true)) {
            $lines[] = $this->trainingContext($employee->id);
        }
        if (in_array($intent, ['travel', 'general'], true)) {
            $lines[] = $this->travelContext($employee->id);
        }

        $lines[] = '=== SYSTEM KNOWLEDGE (share when relevant) ===';
        $lines[] = $this->knowledge($employee);

        return implode("\n", array_filter($lines));
    }

    private function leaveContext(int $employeeId, int $year): string
    {
        // Deliberately NOT filtered to $year. Balance rows are not rewritten
        // each January, so an employee's live credits can be stored under an
        // older year — and this filter used to answer "walang leave balances"
        // to someone whose own Leave & Benefits page was showing 136.25 SL
        // days. LeaveBalance::currentFor() is the same selection that page
        // makes, so the two can no longer disagree.
        $balances = LeaveBalance::currentFor($employeeId)
            ->filter(fn (LeaveBalance $b) => (float) $b->total_credits > 0)
            ->values();

        $balances->load('leaveType:leave_code,leave_name');

        $out = "\n=== YOUR CURRENT LEAVE BALANCES ===\n";
        if ($balances->isEmpty()) {
            $out .= "No leave balances on file.\n";
        } else {
            foreach ($balances as $b) {
                $name = $b->leaveType->leave_name ?? $b->leave_code;
                $out .= sprintf(
                    "- %s (%s): %.2f available, %.2f used, %.2f pending (total credits %.2f, as of FY %d)\n",
                    $name,
                    $b->leave_code,
                    $b->available_credits,
                    $b->used_credits,
                    $b->pending_credits,
                    $b->total_credits,
                    $b->year
                );
            }
        }

        $apps = LeaveApplication::where('employee_id', $employeeId)
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $out .= "\n=== YOUR RECENT LEAVE REQUESTS (latest 5) ===\n";
        if ($apps->isEmpty()) {
            $out .= "No leave applications on file.\n";
        } else {
            foreach ($apps as $app) {
                $out .= sprintf(
                    "- %s: %s to %s, %.1f day(s), status: %s\n",
                    $app->leaveType->leave_name ?? $app->leave_code,
                    $app->start_date->format('Y-m-d'),
                    $app->end_date->format('Y-m-d'),
                    $app->number_of_days,
                    $app->status
                );
            }
        }

        return $out;
    }

    private function payslipContext(int $employeeId): string
    {
        $payslips = SalaryComputation::where('employee_id', $employeeId)
            ->orderByDesc('period_end')
            ->limit(3)
            ->get();

        $out = "\n=== YOUR PAYSLIPS (latest 3 periods) ===\n";
        if ($payslips->isEmpty()) {
            $out .= "No payslip records found.\n";
        } else {
            foreach ($payslips as $p) {
                $ded = (float) ($p->late_deduction ?? 0) + (float) ($p->undertime_deduction ?? 0) + (float) ($p->other_deductions ?? 0);

                // Thousands separators come from number_format, not sprintf:
                // PHP has no `%,.2f` flag (that is C/Python), and passing one
                // throws "Unknown format specifier" — which is what turned
                // every "show my latest payslip" into a generic error.
                $out .= sprintf(
                    "- %s to %s: Basic PHP %s, Deductions PHP %s, Net PHP %s (%s)\n",
                    $p->period_start->format('M d, Y'),
                    $p->period_end->format('M d, Y'),
                    number_format((float) $p->basic_pay, 2),
                    number_format($ded, 2),
                    number_format((float) $p->net_pay, 2),
                    $p->status ?? 'processed'
                );
            }
        }

        return $out;
    }

    private function attendanceContext(int $employeeId): string
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();

        $records = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->limit(15)
            ->get();

        $present = 0;
        $incomplete = 0;
        foreach ($records as $r) {
            if ($r->am_in && $r->pm_out) {
                $present++;
            } else {
                $incomplete++;
            }
        }

        $out = "\n=== YOUR ATTENDANCE (current month) ===\n";
        $out .= sprintf(
            "Days with records: %d | Complete logs (AM in + PM out): %d | Incomplete: %d\n",
            $records->count(),
            $present,
            $incomplete
        );

        if ($records->isNotEmpty()) {
            $out .= "Recent DTR entries:\n";
            foreach ($records->take(5) as $r) {
                $out .= sprintf(
                    "- %s: AM %s–%s, PM %s–%s, accredited %s hrs\n",
                    $r->date->format('Y-m-d'),
                    $r->am_in ?? '—',
                    $r->am_out ?? '—',
                    $r->pm_in ?? '—',
                    $r->pm_out ?? '—',
                    $r->accredited_hours !== null ? number_format($r->accredited_hours / 60, 1) : '0'
                );
            }
        }

        return $out;
    }

    private function trainingContext(int $employeeId): string
    {
        $trainings = Training::where('employee_id', $employeeId)
            ->orderByDesc('date_from')
            ->limit(5)
            ->get();

        $verifiedHours = Training::where('employee_id', $employeeId)
            ->where('status', 'verified')
            ->sum('hours');

        $out = "\n=== YOUR TRAINING / L&D ===\n";
        $out .= sprintf("Verified L&D hours (all time): %d of 40 annual goal\n", (int) $verifiedHours);

        if ($trainings->isEmpty()) {
            $out .= "No training records on file.\n";
        } else {
            $out .= "Recent programs:\n";
            foreach ($trainings as $t) {
                $out .= sprintf(
                    "- %s (%s to %s): %d hrs, status: %s\n",
                    $t->title,
                    $t->date_from?->format('Y-m-d') ?? '?',
                    $t->date_to?->format('Y-m-d') ?? '?',
                    $t->hours,
                    $t->status
                );
            }
        }

        return $out;
    }

    private function travelContext(int $employeeId): string
    {
        $orders = TravelOrder::where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $out = "\n=== YOUR TRAVEL ORDERS (latest 5) ===\n";
        if ($orders->isEmpty()) {
            $out .= "No travel orders on file.\n";
        } else {
            foreach ($orders as $o) {
                $out .= sprintf(
                    "- %s: %s to %s, %d day(s), status: %s\n",
                    $o->destination,
                    $o->travel_date->format('Y-m-d'),
                    $o->return_date->format('Y-m-d'),
                    $o->duration,
                    $o->status
                );
            }
        }

        return $out;
    }

    private function generateResponse(string $message, string $context, Employee $employee): string
    {
        $prompt = <<<PROMPT
You are the PRIME HRIS assistant for a single municipal employee using the mobile app.

STRICT RULES:
1. Answer ONLY using "EMPLOYEE (logged-in user only)" data and "SYSTEM KNOWLEDGE" below.
2. NEVER invent numbers—use exact values from the context.
3. NEVER provide information about other employees, departments' staff lists, or organization-wide counts.
4. If the user asks about other people, refuse politely and offer help with their own records or system how-to.
5. For policy/process questions, use SYSTEM KNOWLEDGE.
6. Use Philippine Peso (PHP) for money. Be concise (3–6 sentences). Match Tagalog or English to the user's language.
7. You may use **bold** for emphasis.

{$context}

Employee's question: "{$message}"
PROMPT;

        $content = AiChatService::complete($employee->user, $prompt, 0.5, 450);

        return $content !== null ? trim($content) : $this->fallbackResponse($message, $context);
    }

    private function fallbackResponse(string $message, string $context): string
    {
        if (str_contains($context, 'LEAVE BALANCES')) {
            return "Here is a summary from your leave records:\n\n" . $this->extractSection($context, 'LEAVE BALANCES', 'RECENT LEAVE')
                . "\n\nAsk me how to file leave or about late deduction policies.";
        }

        return "I can help with **your** leave balance, payslip, attendance, training, and travel records, or explain PRIME HRIS processes. Please try asking something like \"What is my leave balance?\" or \"How do I file leave?\"";
    }

    private function extractSection(string $text, string $start, string $end): string
    {
        $pattern = '/=== YOUR ' . preg_quote($start, '/') . '.*?(?==== YOUR ' . preg_quote($end, '/') . '|=== SYSTEM)/s';
        if (preg_match($pattern, $text, $m)) {
            return trim($m[0]);
        }
        return '';
    }

    private function defaultFollowUps(): array
    {
        return [
            'What is my leave balance?',
            'How do I file a leave request?',
            'Show my latest payslip',
        ];
    }

    private function followUpsForIntent(string $intent): array
    {
        return match ($intent) {
            'leave' => ['How do I file leave?', 'What are my pending leave requests?', 'How is late deduction calculated?'],
            'payslip' => ['What is my latest net pay?', 'How do I view payslip in the app?'],
            'attendance' => ['What is my attendance this month?', 'What is the grace period?'],
            'training' => ['How many verified training hours do I have?', 'How do I add training?'],
            'travel' => ['What are my travel orders?', 'How do I file a travel order?'],
            default => $this->defaultFollowUps(),
        };
    }
}
