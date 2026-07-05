<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /** Session key under which the running conversation is stored. */
    private const HISTORY_KEY = 'chatbot_conversation';

    /** Messages to keep (user + assistant combined) — bounds session size and prompt length. */
    private const MAX_HISTORY_MESSAGES = 12;

    private const SYSTEM_KNOWLEDGE = <<<'TEXT'
=== PRIME HRIS MAGDALENA SYSTEM RULES ===

ATTENDANCE & LEAVE POLICIES:
1. LATE DEDUCTION FROM LEAVE:
   - YES, vacation leave (VL) is deducted when an employee is late
   - System automatically deducts late minutes from VL first, then SL (Sick Leave)
   - Conversion: 480 minutes = 1 work day (8 hours)
   - Grace period: 5 minutes for both AM In (8:00) and PM In (13:00)
   - If late is fully covered by leave credits, employee gets full 8 hours accredited
   - If partially covered, remaining late time becomes LWOP (Leave Without Pay)

2. ATTENDANCE STATUS:
   - Present: All 4 time logs (AM In/Out, PM In/Out) recorded
   - Absent: No time logs at all on working day
   - Incomplete: Has some attendance but missing logs
   - On Leave: Approved leave application

3. WORKING HOURS:
   - Standard schedule: AM 8:00-12:00, PM 13:00-17:00 (8 hours total)
   - Weekends (Saturday/Sunday) are non-working days
   - Overtime (OT) is tracked separately after PM Out

4. LEAVE TYPES:
   - VL (Vacation Leave): Accrued, cumulative, monetizable
   - SL (Sick Leave): Accrued, cumulative, monetizable
   - SPL (Special Privilege Leave): 3 days annually
   - ML (Maternity Leave): 105 days
   - PL (Paternity Leave): 7 days
   - VAWC Leave: 10 days
   - Solo Parent Leave: 7 days

5. DEDUCTIONS:
   - GSIS (Government Service Insurance System)
   - PhilHealth (Philippine Health Insurance)
   - Pag-IBIG (Home Development Mutual Fund)
   - Loans: GSIS Salary, GSIS Policy, GSIS Emergency, Pag-IBIG MPL, Pag-IBIG Calamity

6. LATE-TO-LEAVE DEDUCTION COMPUTATION (Step-by-Step):
   STEP 1 — Compute AM late minutes:
     - If am_in > '08:05:00': AM late minutes = TIME_TO_SEC(am_in)/60 - 485  (i.e. minutes past 08:05)
     - If am_in <= '08:05:00' or null: AM late minutes = 0
   STEP 2 — Compute PM late minutes:
     - If pm_in > '13:05:00': PM late minutes = TIME_TO_SEC(pm_in)/60 - 785  (i.e. minutes past 13:05)
     - If pm_in <= '13:05:00' or null: PM late minutes = 0
   STEP 3 — Total late minutes = AM late minutes + PM late minutes
   STEP 4 — Convert to day fraction: late_days = total_late_minutes / 480
   STEP 5 — Deduct from VL first:
     - If available VL credits >= late_days → full late covered by VL, no LWOP
     - If available VL credits < late_days → VL fully consumed, check SL for remainder
   STEP 6 — Deduct remaining from SL:
     - If SL credits >= remaining → covered by SL, no LWOP
     - If SL credits < remaining → remaining becomes LWOP
   STEP 7 — LWOP salary impact:
     - LWOP days = uncovered late minutes / 480
     - Salary deduction = (monthly_rate / 22) * LWOP days  (22 = working days per month)

   EXAMPLES:
     - 30 mins late → 30/480 = 0.0625 VL day deducted
     - 60 mins late → 60/480 = 0.125 VL day deducted
     - 480 mins late (full day) → 1.0 VL day deducted
     - Employee with 0 VL, 0 SL, 30 mins late → 0.0625 LWOP day → salary deduction = (monthly_rate/22) * 0.0625
     - Employee with 0.05 VL, 30 mins late → VL covers 0.05 days (24 mins), remaining 6 mins → check SL

7. ACCREDITED HOURS:
   - If late is fully covered by leave: accredited_hours = 8.0 (full day)
   - If partially covered (LWOP applies): accredited_hours = 8 - (LWOP_minutes / 60)
   - Absent with approved leave: accredited_hours = 8.0
   - Absent without leave: accredited_hours = 0

8. HOW-TO GUIDE — SYSTEM NAVIGATION:

   HOW TO FILE A LEAVE APPLICATION:
   1. Go to Leave Management > File Leave Application
   2. Select the Leave Type (VL, SL, SPL, ML, PL, VAWC, Solo Parent)
   3. Choose the date range (From Date and To Date)
   4. Enter the reason/remarks
   5. Click Submit — status will be "Pending" until approved by admin
   6. Admin approves or rejects under Leave Management > Leave Approvals
   Note: You must have sufficient leave credits before filing VL or SL

   HOW TO VIEW LEAVE BALANCE:
   1. Go to Leave Management > Leave Balances
   2. Select the employee and year to view available, used, and pending credits
   3. Leave balances are broken down per leave type (VL, SL, SPL, etc.)

   HOW TO RECORD ATTENDANCE (Admin):
   1. Go to Attendance > Daily Time Record (DTR)
   2. Select the employee and date
   3. Enter AM In, AM Out, PM In, PM Out (and OT In/Out if applicable)
   4. Save — the system auto-computes accredited hours and late minutes
   Note: Employees may also use biometric/time-in devices if integrated

   HOW TO VIEW ATTENDANCE RECORDS:
   1. Go to Attendance > Attendance List or DTR Report
   2. Filter by employee name, department, or date range
   3. You can view individual DTR or generate a monthly summary

   HOW TO ADD A NEW EMPLOYEE:
   1. Go to Employees > Add Employee
   2. Fill in personal info: first name, last name, birth date, sex, civil status, email
   3. Fill in employment details: position/designation, department, employment status, salary grade, appointment date
   4. Add government IDs: GSIS, PhilHealth, Pag-IBIG, TIN
   5. Set up deductions under Employee Deductions
   6. Create a user account under Users > Add User and link to the employee

   HOW TO VIEW/EDIT EMPLOYEE PROFILE:
   1. Go to Employees > Employee List
   2. Search by name or employee ID
   3. Click the employee to view or edit their profile, employment details, and government IDs

   HOW TO VIEW PAYSLIP / SALARY COMPUTATION:
   1. Go to Payroll > Salary Computations
   2. Select the employee and payroll period
   3. The payslip shows: basic pay, deductions (GSIS, PhilHealth, Pag-IBIG, loans), and net pay
   4. Admins can generate and print payslips from this section

   HOW TO MANAGE DEDUCTIONS:
   1. Go to Employees > Employee Deductions
   2. Select the employee
   3. Add or update deductions: GSIS, PhilHealth, Pag-IBIG, and loan deductions
   4. Deductions are automatically applied in the next payroll computation

   HOW TO FILE A TRAVEL ORDER:
   1. Go to Travel Orders > File Travel Order
   2. Enter destination, purpose, date range, and transportation details
   3. Submit for approval — status will be "Pending"
   4. Admin approves under Travel Orders > Travel Order Approvals

   HOW TO ADD A TRAINING RECORD:
   1. Go to Trainings > Add Training
   2. Enter training title, date, venue, and number of hours
   3. Upload supporting documents if required
   4. Admin can verify the training record after review

   HOW TO MANAGE USER ACCOUNTS:
   1. Go to Users > User List
   2. Add a new user: enter username, password, role (Admin/Employee), and link to employee record
   3. Activate or deactivate accounts under User Status
   4. Roles control what modules the user can access

   HOW TO GENERATE REPORTS:
   1. Go to Reports section
   2. Available reports: Attendance Summary, Leave Summary, Payroll Report, DTR Report
   3. Filter by department, date range, or individual employee
   4. Export to PDF or print directly from the system

DATABASE KEY TABLES:
- employees: Employee master data (first_name, last_name, employee_id, email, birth_date, sex, civil_status)
- government_ids: GSIS, PhilHealth, Pag-IBIG, TIN, license numbers per employee
- employment_details: Position, department, employment status, salary grade, appointment date
- departments: Department/office list with head and personnel count
- designations: Position titles with salary grade and monthly rate
- attendance: Daily time records (am_in, am_out, pm_in, pm_out, ot_in, ot_out, accredited_hours)
- leave_balances: Employee leave credits by year (available_credits, used_credits, pending_credits)
- leave_applications: Leave requests with status (pending/approved/rejected)
- leave_transactions: Leave credit/debit history
- salary_computations: Payslip records (basic_pay, net_pay, deductions, period)
- employee_deductions: Active deductions per employee
- trainings: Training/seminar records with verification status
- travel_orders: Travel order requests with approval status
- users: User accounts with status (Active/Inactive) and role
TEXT;

    public function chat(Request $request)
    {
        if ($request->boolean('reset')) {
            $request->session()->forget(self::HISTORY_KEY);
        }

        $message = trim($request->input('message', ''));

        if (empty($message)) {
            if ($request->boolean('reset')) {
                return response()->json(['response' => null, 'status' => 'success']);
            }
            return response()->json(['error' => 'No message provided'], 400);
        }

        $history = $request->session()->get(self::HISTORY_KEY, []);

        // Greeting handler
        if (preg_match('/^(hi|hello|hey|good\s+(morning|afternoon|evening)|kumusta|kamusta)\b/i', $message) && str_word_count($message) <= 6) {
            $greeting = "Hello! I'm your PRIME HRIS Assistant. I can answer questions about employees, attendance, leave balances, government IDs, deductions, payroll, and HR policies. What would you like to know?";
            $this->rememberTurn($request, $history, $message, $greeting);

            return response()->json(['response' => $greeting, 'status' => 'success']);
        }

        // Policy-only questions (no DB needed)
        $policyAnswer = $this->getPolicyAnswer($message);
        if ($policyAnswer) {
            $this->rememberTurn($request, $history, $message, $policyAnswer);

            return response()->json(['response' => $policyAnswer, 'status' => 'success']);
        }

        // Get DB schema
        $schema = $this->getDbSchema();

        // Ask Groq to generate SQL
        $sql = $this->generateSql($message, $schema, $history);

        if (!$sql || strtoupper(substr(trim($sql), 0, 6)) !== 'SELECT') {
            // Fallback: let Groq answer from system knowledge alone
            $answer = $this->askGroqDirectly($message, $history);
            $this->rememberTurn($request, $history, $message, $answer);

            return response()->json(['response' => $answer, 'status' => 'success']);
        }

        // Execute SQL
        try {
            $results = DB::select($sql);
        } catch (\Throwable $e) {
            Log::error('Chatbot SQL error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            $answer = $this->askGroqDirectly($message, $history);
            $this->rememberTurn($request, $history, $message, $answer);

            return response()->json(['response' => $answer, 'status' => 'success']);
        }

        // Narrate results
        $response = $this->narrateResults($message, $sql, $results, $history);
        $this->rememberTurn($request, $history, $message, $response);

        return response()->json(['response' => $response, 'status' => 'success']);
    }

    /**
     * Returns the session-backed conversation so the widget can re-render it
     * after a page navigation instead of resetting to the default greeting.
     */
    public function history(Request $request)
    {
        return response()->json([
            'history' => $request->session()->get(self::HISTORY_KEY, []),
            'status'  => 'success',
        ]);
    }

    /**
     * Append the latest exchange to the session-backed conversation and trim it
     * to MAX_HISTORY_MESSAGES so the prompt context and session payload stay bounded.
     */
    private function rememberTurn(Request $request, array $history, string $userMessage, string $botResponse): void
    {
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $botResponse];
        $history   = array_slice($history, -self::MAX_HISTORY_MESSAGES);

        $request->session()->put(self::HISTORY_KEY, $history);
    }

    private function formatHistory(array $history): string
    {
        if (empty($history)) {
            return '(This is the start of the conversation — no previous messages.)';
        }

        $lines = [];
        foreach ($history as $turn) {
            $speaker  = $turn['role'] === 'user' ? 'User' : 'Assistant';
            $lines[] = "{$speaker}: {$turn['content']}";
        }

        return implode("\n", $lines);
    }

    private function getDbSchema(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key    = "Tables_in_{$dbName}";

        $schema = '';
        foreach ($tables as $row) {
            $table   = $row->$key;
            $columns = DB::select("DESCRIBE `{$table}`");
            $cols    = implode(', ', array_map(function($c) { return "{$c->Field} ({$c->Type})"; }, $columns));
            $schema .= "Table `{$table}`: {$cols}\n";
        }

        return $schema;
    }

    private function generateSql(string $question, string $schema, array $history = []): ?string
    {
        $knowledge   = self::SYSTEM_KNOWLEDGE;
        $today       = now()->toDateString();       // e.g. 2026-07-04
        $yesterday   = now()->subDay()->toDateString();
        $tomorrow    = now()->addDay()->toDateString();
        $thisMonth   = now()->format('Y-m');         // e.g. 2026-07
        $conversation = $this->formatHistory($history);

        $prompt = <<<PROMPT
You are a MySQL expert for the Prime HRIS Magdalena system. Generate a valid MySQL SELECT query to answer the user's question.

SYSTEM KNOWLEDGE:
{$knowledge}

Database Schema:
{$schema}

CONVERSATION SO FAR (use this to resolve pronouns like "siya"/"he"/"her" and follow-up references to a previously mentioned employee, date, or topic):
{$conversation}

CRITICAL RULES — follow exactly:
- Only generate SELECT queries, never INSERT, UPDATE, DELETE, or DROP
- Return ONLY the raw SQL query — no explanation, no markdown, no backticks
- Use JOINs when data spans multiple tables (e.g. employee name + government_ids)
- For employee name searches always use LIKE on BOTH first_name AND last_name
- If the question refers back to someone/something from the conversation above (e.g. "him", "her", "siya", "that employee"), resolve it using the conversation history instead of returning CANNOT_ANSWER
- If the question cannot be answered from the schema, return: CANNOT_ANSWER
- "today" means date = '{$today}'
- "yesterday" or "kahapon" means date = '{$yesterday}'
- "tomorrow" or "bukas" means date = '{$tomorrow}'
- "this month" means date LIKE '{$thisMonth}%'
- am_in, am_out, pm_in, pm_out are VARCHAR time strings (e.g. '07:45:00') — compare as strings
- "early bird" or "earliest" means smallest (earliest) am_in value where am_in IS NOT NULL AND am_in != ''
- "top 5 early birds today" example SQL:
  SELECT e.first_name, e.last_name, a.am_in FROM attendance a
  JOIN employees e ON e.id = a.employee_id
  WHERE a.date = '{$today}' AND a.am_in IS NOT NULL AND a.am_in != ''
  ORDER BY a.am_in ASC LIMIT 5
- "who was late" means am_in > '08:05:00'
- "absent today" means employees with no attendance row for date = '{$today}'

LATE MINUTES COMPUTATION IN SQL:
- AM late minutes: GREATEST(0, TIME_TO_SEC(am_in)/60 - 485) when am_in IS NOT NULL AND am_in > '08:05:00', else 0
- PM late minutes: GREATEST(0, TIME_TO_SEC(pm_in)/60 - 785) when pm_in IS NOT NULL AND pm_in > '13:05:00', else 0
- Total late minutes expression:
  (CASE WHEN am_in > '08:05:00' THEN GREATEST(0, TIME_TO_SEC(am_in)/60 - 485) ELSE 0 END +
   CASE WHEN pm_in > '13:05:00' THEN GREATEST(0, TIME_TO_SEC(pm_in)/60 - 785) ELSE 0 END)
- VL deduction in days: total_late_minutes / 480
- LWOP days (when VL=0 and SL=0): total_late_minutes / 480
- Salary deduction from LWOP: (monthly_rate / 22) * lwop_days — join designations for monthly_rate
- For "late deduction" or "VL deducted" queries, always SELECT: employee name, am_in, pm_in, total late minutes, VL deduction (days), and available VL credits
- For "LWOP" queries, also include: SL credits, LWOP days, and estimated salary deduction

User Question: {$question}

SQL Query:
PROMPT;

        $content = $this->callGroq($prompt, 0.1, 400);

        if (!$content || str_contains(strtoupper($content), 'CANNOT_ANSWER')) {
            return null;
        }

        // Strip any accidental markdown fences
        $content = preg_replace('/```[a-z]*\n?/i', '', $content);
        $content = trim($content, "`\n ");

        return $content;
    }

    private function narrateResults(string $question, string $sql, array $results, array $history = []): string
    {
        $knowledge = self::SYSTEM_KNOWLEDGE;
        $preview = count($results) > 0
            ? json_encode(array_slice($results, 0, 10), JSON_PRETTY_PRINT)
            : 'No results found';

        $total        = count($results);
        $conversation = $this->formatHistory($history);

        $prompt = <<<PROMPT
You are a friendly HR assistant for Prime HRIS Magdalena. A user asked a question, a SQL query was run, and here are the results. Answer naturally and conversationally, as a continuation of the ongoing chat below — don't reintroduce yourself or restate things already established in the conversation.

SYSTEM KNOWLEDGE:
{$knowledge}

CONVERSATION SO FAR:
{$conversation}

Latest User Question: {$question}
SQL Query Used: {$sql}
Query Results (first 10): {$preview}
Total Records Found: {$total}

Instructions:
- Answer in a friendly, concise tone (3-5 sentences max), like you're picking up the conversation naturally
- Never dump raw field names or key/value pairs — turn the data into a natural sentence (e.g. "Jeremy clocked in at 8:11 AM yesterday, about 6.5 minutes late")
- If no results, say so politely and suggest checking the spelling or name
- All monetary amounts in Philippine Peso (PHP), never use dollar signs
- Match the user's language (Tagalog or English) and mirror the tone of the conversation so far
- Never expose raw SQL to the user
- If the question involves late deductions or leave computation, explain the math step-by-step:
    * How many minutes late (AM and/or PM)
    * How many VL days were deducted (late_minutes / 480)
    * Whether SL was used after VL was exhausted
    * Whether any LWOP applies and how much salary is affected
    * Use plain language: e.g. "30 minutes late = 0.0625 VL day deducted"
- If the result shows LWOP, always mention the estimated salary deduction in PHP
- If the result shows leave balances, mention remaining VL and SL credits after deduction
PROMPT;

        return $this->callGroq($prompt, 0.7, 400) ?? $this->buildFallbackNarration($results);
    }

    private function askGroqDirectly(string $question, array $history = []): string
    {
        $knowledge    = self::SYSTEM_KNOWLEDGE;
        $conversation = $this->formatHistory($history);

        $prompt = <<<PROMPT
You are an HR assistant for Prime HRIS Magdalena. Answer this question using the system knowledge below.

{$knowledge}

CONVERSATION SO FAR (continue naturally from this, resolving any pronouns or follow-up references):
{$conversation}

Latest User Question: {$question}

Provide a clear, friendly answer in 2-4 sentences. Match the user's language (Tagalog or English) and don't repeat introductions already made earlier in the conversation.
PROMPT;

        return $this->callGroq($prompt, 0.7, 500)
            ?? "I'm not sure how to answer that. Could you rephrase or ask about employees, attendance, leave balances, or HR policies?";
    }

    private function getPolicyAnswer(string $question): ?string
    {
        $q = strtolower($question);

        if (preg_match('/\b(grace\s*period)\b/', $q)) {
            return 'The grace period is **5 minutes** for both AM In (8:00) and PM In (13:00). Clocking in within that window is not counted as late.';
        }
        if (preg_match('/\blwop\b/', $q) && !preg_match('/\b(who|which|employee|list|show|how many)\b/', $q)) {
            return '**LWOP (Leave Without Pay)** is applied when late minutes exceed available VL and SL credits. The uncovered minutes are converted to days (÷480) and deducted from salary: **(monthly_rate ÷ 22) × LWOP days**.';
        }
        if (preg_match('/\b(working hours?|oras ng trabaho)\b/', $q)) {
            return 'Standard working hours: **AM 8:00–12:00**, **PM 13:00–17:00** (8 hours total). Saturday and Sunday are non-working days.';
        }
        if (preg_match('/\b(how.*(late.*deduct|deduct.*late)|late.*comput|comput.*late|late.*vl|vl.*late)\b/', $q)) {
            return implode("\n", [
                '**How Late Deductions Work in PRIME HRIS:**',
                '1. Compute late minutes: AM late = minutes past 08:05 | PM late = minutes past 13:05',
                '2. Total late minutes ÷ 480 = fraction of a day to deduct',
                '3. Deduct from **VL** first. If VL is exhausted, deduct from **SL**.',
                '4. Any remaining uncovered minutes become **LWOP**.',
                '5. LWOP salary deduction = **(monthly rate ÷ 22) × LWOP days**',
                '',
                '**Example:** 30 mins late → 30÷480 = **0.0625 VL day** deducted.',
                '**Example:** 60 mins late, 0 VL, 0 SL → 60÷480 = **0.125 LWOP day** → salary deduction = monthly_rate ÷ 22 × 0.125',
            ]);
        }
        if (preg_match('/\b(how.*(vl|vacation leave).*(accru|earn|comput)|vl.*(accru|earn)|leave.*(accru|earn))\b/', $q)) {
            return 'VL and SL are accrued monthly. Both are cumulative (carry over year to year) and monetizable. SPL is fixed at 3 days per year and does not carry over.';
        }
        if (preg_match('/\b(leave types?|kinds? of leave|uri ng leave|anong leave)\b/', $q)) {
            return implode("\n", [
                '**Leave Types in PRIME HRIS:**',
                '- **VL** (Vacation Leave) — accrued, cumulative, monetizable',
                '- **SL** (Sick Leave) — accrued, cumulative, monetizable',
                '- **SPL** (Special Privilege Leave) — 3 days/year',
                '- **ML** (Maternity Leave) — 105 days',
                '- **PL** (Paternity Leave) — 7 days',
                '- **VAWC Leave** — 10 days',
                '- **Solo Parent Leave** — 7 days',
            ]);
        }
        if (preg_match('/\b(how.*(file|apply|submit).*(leave|vl|sl|vacation|sick)|mag.*file.*leave|pano.*mag.*leave)\b/', $q)) {
            return implode("\n", [
                '**How to File a Leave Application:**',
                '1. Go to **Leave Management > File Leave Application**',
                '2. Select the Leave Type (VL, SL, SPL, ML, PL, VAWC, Solo Parent)',
                '3. Choose your date range and enter your reason/remarks',
                '4. Click **Submit** — status will be "Pending" until approved by admin',
                '',
                '> Make sure you have enough leave credits before filing VL or SL.',
            ]);
        }
        if (preg_match('/\b(how.*(record|log|enter|add).*(attendance|time|dtr)|pano.*attendance|mag.*time.*in)\b/', $q)) {
            return implode("\n", [
                '**How to Record Attendance:**',
                '1. Go to **Attendance > Daily Time Record (DTR)**',
                '2. Select the employee and date',
                '3. Enter **AM In, AM Out, PM In, PM Out** (and OT if applicable)',
                '4. Save — the system auto-computes accredited hours and late minutes',
            ]);
        }
        if (preg_match('/\b(how.*(add|create|register|enroll).*(employee|empleyado)|pano.*mag.*add.*employee)\b/', $q)) {
            return implode("\n", [
                '**How to Add a New Employee:**',
                '1. Go to **Employees > Add Employee**',
                '2. Fill in personal info: name, birth date, sex, civil status, email',
                '3. Fill in employment details: position, department, salary grade, appointment date',
                '4. Add government IDs: GSIS, PhilHealth, Pag-IBIG, TIN',
                '5. Set up deductions under **Employee Deductions**',
                '6. Create a user account under **Users > Add User** and link to the employee',
            ]);
        }
        if (preg_match('/\b(how.*(view|check|see|open).*(payslip|salary|payroll)|pano.*payslip|saan.*payslip)\b/', $q)) {
            return implode("\n", [
                '**How to View Payslip / Salary Computation:**',
                '1. Go to **Payroll > Salary Computations**',
                '2. Select the employee and payroll period',
                '3. The payslip shows: basic pay, deductions (GSIS, PhilHealth, Pag-IBIG, loans), and net pay',
                '4. You can print the payslip directly from this section',
            ]);
        }
        if (preg_match('/\b(how.*(file|apply|submit).*(travel|travel order)|pano.*travel order)\b/', $q)) {
            return implode("\n", [
                '**How to File a Travel Order:**',
                '1. Go to **Travel Orders > File Travel Order**',
                '2. Enter destination, purpose, date range, and transportation details',
                '3. Submit — status will be "Pending" until approved by admin',
            ]);
        }
        if (preg_match('/\b(how.*(add|record|enter).*(training|seminar)|pano.*training)\b/', $q)) {
            return implode("\n", [
                '**How to Add a Training Record:**',
                '1. Go to **Trainings > Add Training**',
                '2. Enter training title, date, venue, and number of hours',
                '3. Upload supporting documents if required',
                '4. Admin can verify the training record after review',
            ]);
        }
        if (preg_match('/\b(how.*(view|check).*(leave balance|leave credit)|pano.*leave balance)\b/', $q)) {
            return implode("\n", [
                '**How to View Leave Balance:**',
                '1. Go to **Leave Management > Leave Balances**',
                '2. Select the employee and year',
                '3. You will see available, used, and pending credits per leave type',
            ]);
        }
        if (preg_match('/\b(absent.*deduct|deduct.*absent|no.*show|nawala)\b/', $q)) {
            return '**Absent without leave** = 1 full LWOP day. Salary deduction = **(monthly rate ÷ 22) × 1**. If the employee has VL/SL, they can file a leave application to cover the absence and avoid LWOP.';
        }

        return null;
    }

    private function callGroq(string $prompt, float $temperature, int $maxTokens): ?string
    {
        $apiKey = config('services.groq.api_key') ?: env('GROQ_API_KEY');
        if (!$apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(20)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages'    => [['role' => 'user', 'content' => $prompt]],
                'temperature' => $temperature,
                'max_tokens'  => $maxTokens,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('Groq API error: ' . $response->status() . ' ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Groq exception: ' . $e->getMessage());
        }

        return null;
    }

    private function buildFallbackNarration(array $results): string
    {
        if (empty($results)) {
            return "I couldn't find any matching records for that. Could you double-check the name or details and try again?";
        }

        $first = (array) $results[0];
        $parts = [];
        foreach ($first as $key => $value) {
            $label   = ucwords(str_replace('_', ' ', $key));
            $parts[] = "{$label}: {$value}";
        }

        $summary = implode(', ', $parts);
        $total   = count($results);
        $intro   = $total > 1
            ? "I found {$total} matching records — here's the first one:"
            : "Here's what I found:";

        return "{$intro} {$summary}.";
    }
}
