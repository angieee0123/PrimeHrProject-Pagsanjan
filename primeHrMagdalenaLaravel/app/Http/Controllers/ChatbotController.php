<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
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
        $message = trim($request->input('message', ''));

        if (empty($message)) {
            return response()->json(['error' => 'No message provided'], 400);
        }

        // Greeting handler
        if (preg_match('/^(hi|hello|hey|good\s+(morning|afternoon|evening)|kumusta|kamusta)\b/i', $message) && str_word_count($message) <= 6) {
            return response()->json([
                'response' => "Hello! I'm your PRIME HRIS Assistant. I can answer questions about employees, attendance, leave balances, government IDs, deductions, payroll, and HR policies. What would you like to know?",
                'status'   => 'success',
            ]);
        }

        // Policy-only questions (no DB needed)
        $policyAnswer = $this->getPolicyAnswer($message);
        if ($policyAnswer) {
            return response()->json(['response' => $policyAnswer, 'status' => 'success']);
        }

        // Get DB schema
        $schema = $this->getDbSchema();

        // Ask Groq to generate SQL
        $sql = $this->generateSql($message, $schema);

        if (!$sql || strtoupper(substr(trim($sql), 0, 6)) !== 'SELECT') {
            // Fallback: let Groq answer from system knowledge alone
            return response()->json([
                'response' => $this->askGroqDirectly($message),
                'status'   => 'success',
            ]);
        }

        // Execute SQL
        try {
            $results = DB::select($sql);
        } catch (\Throwable $e) {
            Log::error('Chatbot SQL error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return response()->json([
                'response' => $this->askGroqDirectly($message),
                'status'   => 'success',
            ]);
        }

        // Narrate results
        $response = $this->narrateResults($message, $sql, $results);

        return response()->json(['response' => $response, 'status' => 'success']);
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

    private function generateSql(string $question, string $schema): ?string
    {
        $knowledge = self::SYSTEM_KNOWLEDGE;
        $today     = now()->toDateString();   // e.g. 2026-07-04
        $thisMonth = now()->format('Y-m');    // e.g. 2026-07

        $prompt = <<<PROMPT
You are a MySQL expert for the Prime HRIS Magdalena system. Generate a valid MySQL SELECT query to answer the user's question.

SYSTEM KNOWLEDGE:
{$knowledge}

Database Schema:
{$schema}

CRITICAL RULES — follow exactly:
- Only generate SELECT queries, never INSERT, UPDATE, DELETE, or DROP
- Return ONLY the raw SQL query — no explanation, no markdown, no backticks
- Use JOINs when data spans multiple tables (e.g. employee name + government_ids)
- For employee name searches always use LIKE on BOTH first_name AND last_name
- If the question cannot be answered from the schema, return: CANNOT_ANSWER
- "today" means date = '{$today}'
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

    private function narrateResults(string $question, string $sql, array $results): string
    {
        $knowledge = self::SYSTEM_KNOWLEDGE;
        $preview = count($results) > 0
            ? json_encode(array_slice($results, 0, 10), JSON_PRETTY_PRINT)
            : 'No results found';

        $total = count($results);

        $prompt = <<<PROMPT
You are a friendly HR assistant for Prime HRIS Magdalena. A user asked a question, a SQL query was run, and here are the results. Answer naturally and conversationally.

SYSTEM KNOWLEDGE:
{$knowledge}

User Question: {$question}
SQL Query Used: {$sql}
Query Results (first 10): {$preview}
Total Records Found: {$total}

Instructions:
- Answer in a friendly, concise tone (3-5 sentences max)
- If no results, say so politely and suggest checking the spelling or name
- All monetary amounts in Philippine Peso (PHP), never use dollar signs
- Match the user's language (Tagalog or English)
- Never expose raw SQL to the user
PROMPT;

        return $this->callGroq($prompt, 0.7, 400) ?? $this->buildFallbackNarration($results);
    }

    private function askGroqDirectly(string $question): string
    {
        $knowledge = self::SYSTEM_KNOWLEDGE;
        $prompt = <<<PROMPT
You are an HR assistant for Prime HRIS Magdalena. Answer this question using the system knowledge below.

{$knowledge}

User Question: {$question}

Provide a clear, friendly answer in 2-4 sentences. Match the user's language (Tagalog or English).
PROMPT;

        return $this->callGroq($prompt, 0.7, 300)
            ?? "I'm not sure how to answer that. Could you rephrase or ask about employees, attendance, leave balances, or HR policies?";
    }

    private function getPolicyAnswer(string $question): ?string
    {
        $q = strtolower($question);

        if (preg_match('/\b(grace\s*period)\b/', $q)) {
            return 'The grace period is **5 minutes** for both AM In (8:00) and PM In (13:00). Clocking in within that window is not counted as late.';
        }
        if (preg_match('/\blwop\b/', $q)) {
            return '**LWOP (Leave Without Pay)** is applied when your late or undertime minutes exceed your available VL/SL credits. Those uncovered minutes are deducted from your salary.';
        }
        if (preg_match('/\b(working hours?|oras ng trabaho)\b/', $q)) {
            return 'Standard working hours: **AM 8:00–12:00**, **PM 13:00–17:00** (8 hours total). Saturday and Sunday are non-working days.';
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
            return "I couldn't find any matching records in the database. Please check the name or details and try again.";
        }

        $first = (array) $results[0];
        $lines = [];
        foreach ($first as $key => $value) {
            $lines[] = "**{$key}**: {$value}";
        }

        $summary = implode(', ', $lines);
        $extra   = count($results) > 1 ? ' (' . count($results) . ' records found total)' : '';

        return "Here's what I found{$extra}: {$summary}.";
    }
}
