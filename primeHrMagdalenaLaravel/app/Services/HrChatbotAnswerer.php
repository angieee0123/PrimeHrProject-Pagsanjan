<?php

namespace App\Services;

use App\Models\User;

/**
 * The HR chatbot "brain": policy-question shortcuts and DB-free conversational
 * fallback, bilingual. Extracted out of ChatbotController so both the
 * session-backed floating widget and the DB-backed full-page AI Assistant call
 * the exact same logic instead of maintaining two copies of this prompt
 * engineering.
 *
 * Deliberately has no database access. Anything that needs real records is
 * handled upstream by SafeSqlService (validated, table-allow-listed, row-capped,
 * gated to org-wide roles) before a question ever reaches here — see
 * AiQueryService::dataQuery(). This class only used to run its own independent
 * text-to-SQL pipeline against the full schema with none of those guards; that
 * was removed because it was reachable by every role, including self-scoped
 * employee accounts, whenever a question fell through pattern matching.
 */
class HrChatbotAnswerer
{
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
   1. Go to Users > User List (or Personnel > Edit Employee)
   2. Add a new user: enter username, password, role(s) (Employee/HR/Admin/Mayor), and link to employee record
   3. An employee can hold multiple roles at once (e.g. HR + Mayor) via checkboxes in the Role / Access Level field
   4. Activate or deactivate accounts under User Status
   5. Roles control what modules the user can access; users with multiple roles choose which dashboard to use at login

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
- users: User accounts with status (Active/Inactive) and roles (JSON array, e.g. ["hr","mayor"])
TEXT;

    /**
     * Answer a single question given the conversation so far.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    public function answer(?User $user, string $message, array $history = []): string
    {
        $message = trim($message);

        // Greeting handler
        if (preg_match('/^(hi|hello|hey|good\s+(morning|afternoon|evening)|kumusta|kamusta)\b/i', $message) && str_word_count($message) <= 6) {
            return "Hello! I'm your PRIME HRIS Assistant. I can answer questions about employees, attendance, leave balances, government IDs, deductions, payroll, and HR policies. What would you like to know?";
        }

        // Policy-only questions (no DB needed)
        $policyAnswer = $this->getPolicyAnswer($message);
        if ($policyAnswer) {
            return $policyAnswer;
        }

        // Anything that needs real records goes through SafeSqlService before
        // reaching here (see AiQueryService::dataQuery) — this is the DB-free
        // conversational tail, not a second text-to-SQL pipeline. It used to
        // run its own unrestricted DB::select() against every table in the
        // schema (including users/personal_access_tokens/sessions) with no
        // allow-list, no forbidden-keyword check, and no row cap — that has
        // been removed in favour of the single validated path.
        return $this->askDirectly($user, $message, $history);
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

    private function askDirectly(?User $user, string $question, array $history = []): string
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

        return $this->callAi($user, $prompt, 0.7, 500)
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

    private function callAi(?User $user, string $prompt, float $temperature, int $maxTokens): ?string
    {
        return AiChatService::complete($user, $prompt, $temperature, $maxTokens);
    }
}
