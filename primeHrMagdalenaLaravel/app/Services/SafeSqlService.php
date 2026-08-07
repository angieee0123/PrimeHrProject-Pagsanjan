<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Feature 3: Natural Language Database Querying.
 *
 * Turns a question into a single read-only SELECT, validates it against a
 * table allow-list, executes it with a hard row cap, and narrates the result.
 *
 * Security model — a generated statement must clear every one of these before
 * it reaches the database:
 *   1. Exactly one statement, no trailing semicolon payload.
 *   2. Starts with SELECT (or WITH … SELECT).
 *   3. No DDL/DML/administrative keyword anywhere, comments stripped first so
 *      they cannot be used to smuggle one past the check.
 *   4. Every table referenced appears in ALLOWED_TABLES. `users`,
 *      `personal_access_tokens`, `sessions`, and `password_reset_tokens` are
 *      deliberately absent — they hold password hashes and bearer tokens.
 *   5. No information_schema / mysql / performance_schema access.
 *   6. Caller holds an org-wide role.
 */
class SafeSqlService
{
    private const MAX_ROWS = 200;

    /** Generation attempts before giving up, including the first try. */
    private const MAX_ATTEMPTS = 3;

    /**
     * Tables the assistant may read. Anything not listed is rejected even if
     * the model invents a plausible join.
     */
    private const ALLOWED_TABLES = [
        'employees',
        'employment_details',
        'departments',
        'designations',
        'attendance',
        'attendance_corrections',
        'attendance_exemptions',
        'accredited_hours_log',
        'leave_applications',
        'leave_balances',
        'leave_transactions',
        'leave_types_config',
        'leave_accrual_rates',
        'salary_computations',
        'daily_salary_computations',
        'employee_deductions',
        'deduction_types',
        'deduction_schedules',
        'government_ids',
        'legal_requirements',
        'educations',
        'eligibilities',
        'work_experiences',
        'trainings',
        'family_members',
        'addresses',
        'contacts',
        'documents',
        'travel_orders',
        'pass_slips',
        'schedules',
        'employee_requests',
    ];

    /**
     * Rejected anywhere in the statement. Word-boundary matched so a column
     * named e.g. `created_at` cannot trip the CREATE rule.
     */
    private const FORBIDDEN_KEYWORDS = [
        'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE',
        'REPLACE', 'GRANT', 'REVOKE', 'EXEC', 'EXECUTE', 'CALL', 'HANDLER',
        'LOAD', 'OUTFILE', 'DUMPFILE', 'INFILE', 'LOCK', 'UNLOCK', 'RENAME',
        'SET', 'PREPARE', 'DEALLOCATE', 'SHUTDOWN', 'FLUSH', 'RESET', 'KILL',
        'BENCHMARK', 'SLEEP',
        // Underscored function names need their own entries: \bLOAD\b does not
        // match inside LOAD_FILE, because the underscore is a word character.
        'LOAD_FILE', 'SYS_EXEC', 'SYS_EVAL', 'GET_LOCK', 'MASTER_POS_WAIT',
    ];

    private const FORBIDDEN_SCHEMAS = ['information_schema', 'mysql', 'performance_schema', 'sys'];

    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * The allow-list, for callers that need to describe the readable schema
     * without going through generation — notably HrChatbotAnswerer, which must
     * not show the model a table this service would refuse to query.
     *
     * @return array<int, string>
     */
    public static function allowedTables(): array
    {
        return self::ALLOWED_TABLES;
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function query(User $user, string $question, array $history = []): array
    {
        if (!$this->policy->canRunGeneratedSql($user)) {
            return [
                'answer' => 'Ad-hoc database questions are limited to HR, admin, and mayor accounts. '
                    . 'I can still look up your own records for you.',
                'data' => [],
                'blocked' => true,
            ];
        }

        // Text-to-SQL is not reliable in one shot: the model regularly emits a
        // statement that violates ONLY_FULL_GROUP_BY, references a column that
        // does not exist, or returns nothing at all. Feeding the failure back
        // and asking for a correction turns most of those into a good answer,
        // so the query is attempted up to MAX_ATTEMPTS times.
        $sql = null;
        $lastError = null;
        $rows = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $sql = $this->generateSql($user, $question, $history, $sql, $lastError);

            if (!$sql) {
                $lastError = 'No statement was produced.';
                continue;
            }

            $verdict = $this->validate($sql);

            if (!$verdict['safe']) {
                Log::channel('ai_audit')->warning('AI SQL rejected', [
                    'user_id' => $user->id,
                    'reason' => $verdict['reason'],
                    'sql' => $sql,
                ]);

                // A rejected statement is a safety decision, not a bug to
                // iterate on — stop rather than coaching around the guard.
                return [
                    'answer' => "I generated a query for that, but blocked it before it ran: {$verdict['reason']}. "
                        . 'The assistant is read-only by design.',
                    'data' => [],
                    'blocked' => true,
                ];
            }

            try {
                $rows = array_map(
                    fn ($row) => (array) $row,
                    DB::select($this->enforceRowCap($sql))
                );
                $lastError = null;
                break;
            } catch (\Throwable $e) {
                $lastError = $this->summariseDbError($e->getMessage());
                $rows = null;

                Log::channel('ai_audit')->warning('AI SQL attempt failed', [
                    'user_id' => $user->id,
                    'attempt' => $attempt,
                    'sql' => $sql,
                    'error' => $lastError,
                ]);
            }
        }

        if ($rows === null) {
            return [
                'answer' => 'I could not build a working query for that. Try naming the records more directly '
                    . '— for example "employees with 2 or more absences this year".',
                'data' => [],
                'error' => $lastError ?? 'no statement produced',
            ];
        }

        return [
            'answer' => $this->narrate($user, $question, $sql, $rows, $history),
            'data' => $rows,
            'sql' => $sql,
            'count' => count($rows),
        ];
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function generateSql(
        User $user,
        string $question,
        array $history,
        ?string $previousSql = null,
        ?string $previousError = null,
    ): ?string {
        $schema = $this->describeSchema($question);
        $today = now()->toDateString();

        $system = <<<PROMPT
You write MySQL SELECT statements for the PRIME HRIS database. Today is {$today}.

SCHEMA (these are the only tables you may reference):
{$schema}

RULES — a violation means the query is discarded:
1. Output ONE SELECT statement and nothing else. No prose, no markdown fences,
   no trailing semicolon, no comments.
2. SELECT only. Never INSERT, UPDATE, DELETE, DROP, ALTER, CREATE, or SET.
3. Only use tables from the schema above. Never touch users,
   personal_access_tokens, sessions, or information_schema.
4. Always include a LIMIT of at most 200.
5. Prefer explicit JOINs and select named columns rather than SELECT *.
6. This server runs with ONLY_FULL_GROUP_BY. Every selected column must either
   appear in GROUP BY or be wrapped in an aggregate (COUNT, SUM, MAX…). When
   aggregating, do NOT also select per-row columns such as a date.
7. Give every aggregate a readable alias, e.g. COUNT(*) AS total_absences.

DOMAIN RULES — these matter more than they look:
8. Employees link to departments through employment_details.department_id, and
   to job titles through employment_details.designation_id.
9. An ABSENCE is `attendance.attendance_type = 'ABSENT'` and nothing else.
   Never infer absence from accredited_hours or total_hours — those are NULL
   until payroll computes the day, and counting NULL as absent reports almost
   every employee as absent.
10. attendance uses the column `date`, not `attendance_date`.
11. leave_applications.status is one of: pending, approved, rejected, cancelled
    (lowercase). Its dates are start_date and end_date.

Example:
Question: employees with 2 or more absences
SELECT e.employee_id, e.first_name, e.last_name, COUNT(*) AS total_absences
FROM employees e
JOIN attendance a ON a.employee_id = e.id
WHERE a.attendance_type = 'ABSENT'
GROUP BY e.id, e.employee_id, e.first_name, e.last_name
HAVING COUNT(*) >= 2
ORDER BY total_absences DESC
LIMIT 200
PROMPT;

        $messages = $history;

        if ($previousSql !== null && $previousError !== null) {
            // Hand back the exact statement and the database's complaint; the
            // model corrects far more reliably from the real error than from a
            // restated instruction.
            $messages[] = ['role' => 'user', 'content' => $question];
            $messages[] = ['role' => 'assistant', 'content' => $previousSql];
            $messages[] = [
                'role' => 'user',
                'content' => "That statement failed with:\n{$previousError}\n\n"
                    . 'Return a corrected single SELECT statement. Output only the SQL.',
            ];
        } else {
            $messages[] = ['role' => 'user', 'content' => $question];
        }

        $raw = AiChatService::chat($user, $system, $messages, 0.0, 600);

        return $raw ? $this->extractSql($raw) : null;
    }

    /**
     * MySQL errors arrive wrapped in the full PDO message plus the entire
     * statement. Keep the part that tells the model what to fix.
     */
    private function summariseDbError(string $message): string
    {
        if (preg_match('/SQLSTATE\[\w+\]:?\s*(.+?)\s*\(Connection:/s', $message, $m)) {
            return trim($m[1]);
        }

        return mb_substr($message, 0, 400);
    }

    /**
     * Pull the statement out of whatever wrapping the model produced.
     */
    private function extractSql(string $raw): ?string
    {
        $sql = trim($raw);

        if (preg_match('/```(?:sql)?\s*(.+?)\s*```/is', $sql, $m)) {
            $sql = trim($m[1]);
        }

        // Drop any leading prose the model added before the statement.
        if (preg_match('/\b(WITH|SELECT)\b.*/is', $sql, $m)) {
            $sql = trim($m[0]);
        }

        $sql = rtrim($sql, "; \t\n\r");

        return $sql === '' ? null : $sql;
    }

    /**
     * @return array{safe: bool, reason?: string}
     */
    public function validate(string $sql): array
    {
        // Checked BEFORE comments are stripped. MySQL *executes* the contents
        // of /*! … */ and /*+ … */ rather than ignoring them, so stripping
        // first would hide whatever is inside from every check below — the
        // classic way to smuggle a UNION onto a restricted table.
        if (preg_match('/\/\*[!+]/', $sql)) {
            return ['safe' => false, 'reason' => 'it used a MySQL executable comment'];
        }

        $stripped = $this->stripLiteralsAndComments($sql);
        $upper = strtoupper($stripped);

        if (trim($stripped) === '') {
            return ['safe' => false, 'reason' => 'the statement was empty'];
        }

        // Multiple statements: anything after a semicolon is a second command.
        if (str_contains(rtrim($stripped, "; \t\n\r"), ';')) {
            return ['safe' => false, 'reason' => 'it contained more than one statement'];
        }

        if (!preg_match('/^\s*(SELECT|WITH)\b/i', $stripped)) {
            return ['safe' => false, 'reason' => 'only SELECT statements are allowed'];
        }

        foreach (self::FORBIDDEN_KEYWORDS as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/', $upper)) {
                return ['safe' => false, 'reason' => "it used the forbidden keyword {$keyword}"];
            }
        }

        foreach (self::FORBIDDEN_SCHEMAS as $schema) {
            if (str_contains(strtolower($stripped), $schema)) {
                return ['safe' => false, 'reason' => "it referenced the restricted schema {$schema}"];
            }
        }

        foreach ($this->referencedTables($stripped) as $table) {
            if (!in_array($table, self::ALLOWED_TABLES, true)) {
                return ['safe' => false, 'reason' => "the table `{$table}` is not readable by the assistant"];
            }
        }

        return ['safe' => true];
    }

    /**
     * Remove string literals and comments before keyword scanning, so neither
     * `'-- DROP'` nor `/*!DELETE* /` can slip a banned word past the filter.
     */
    private function stripLiteralsAndComments(string $sql): string
    {
        $sql = preg_replace("/'(?:[^'\\\\]|\\\\.)*'/", "''", $sql) ?? $sql;
        $sql = preg_replace('/"(?:[^"\\\\]|\\\\.)*"/', '""', $sql) ?? $sql;
        $sql = preg_replace('/\/\*.*?\*\//s', ' ', $sql) ?? $sql;
        $sql = preg_replace('/--[^\n]*/', ' ', $sql) ?? $sql;
        $sql = preg_replace('/#[^\n]*/', ' ', $sql) ?? $sql;

        return $sql;
    }

    /**
     * @return array<int, string>
     */
    private function referencedTables(string $sql): array
    {
        preg_match_all('/\b(?:FROM|JOIN)\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?/i', $sql, $matches);

        $tables = array_map('strtolower', $matches[1] ?? []);

        // CTE names are defined inline, not real tables — do not reject them.
        preg_match_all('/\bWITH\s+`?([a-zA-Z_][a-zA-Z0-9_]*)`?|,\s*`?([a-zA-Z_][a-zA-Z0-9_]*)`?\s+AS\s*\(/i', $sql, $cteMatches);
        $ctes = array_map('strtolower', array_filter(array_merge($cteMatches[1] ?? [], $cteMatches[2] ?? [])));

        return array_values(array_unique(array_diff($tables, $ctes)));
    }

    /**
     * Guarantee a row cap even if the model omitted one. Public because every
     * path that executes generated SQL must apply it, not just this one.
     */
    public function enforceRowCap(string $sql): string
    {
        if (preg_match('/\bLIMIT\s+(\d+)/i', $sql, $m)) {
            return (int) $m[1] <= self::MAX_ROWS
                ? $sql
                : preg_replace('/\bLIMIT\s+\d+/i', 'LIMIT ' . self::MAX_ROWS, $sql);
        }

        return $sql . ' LIMIT ' . self::MAX_ROWS;
    }

    /**
     * Which tables a question is likely to need. Sending all 32 allowed tables
     * with full column types costs ~2,900 tokens per attempt — enough to burn a
     * daily LLM quota in about ten questions — so the schema is narrowed to the
     * subject of the question plus the core employee tables everything joins to.
     *
     * @var array<string, array<int, string>>
     */
    private const TABLE_TOPICS = [
        // Stems, not whole words: "absen" covers absent/absence/absences.
        'attend' => ['attendance', 'attendance_corrections', 'attendance_exemptions', 'accredited_hours_log', 'schedules'],
        'absen' => ['attendance'],
        'present' => ['attendance'],
        'late' => ['attendance', 'accredited_hours_log'],
        'tardi' => ['attendance', 'accredited_hours_log'],
        'undertime' => ['attendance', 'accredited_hours_log'],
        'overtime' => ['attendance'],
        'dtr' => ['attendance'],
        'schedul' => ['schedules', 'attendance'],
        'leave' => ['leave_applications', 'leave_balances', 'leave_transactions', 'leave_types_config', 'leave_accrual_rates'],
        'vacation' => ['leave_applications', 'leave_balances', 'leave_types_config'],
        'sick' => ['leave_applications', 'leave_balances', 'leave_types_config'],
        'maternity' => ['leave_applications', 'leave_types_config'],
        'credit' => ['leave_balances', 'leave_types_config'],
        'payroll' => ['salary_computations', 'daily_salary_computations', 'employee_deductions', 'deduction_types', 'deduction_schedules'],
        'salar' => ['salary_computations', 'daily_salary_computations', 'designations'],
        'payslip' => ['salary_computations'],
        'net pay' => ['salary_computations'],
        'gross' => ['salary_computations'],
        'compensat' => ['salary_computations', 'designations'],
        'deduct' => ['employee_deductions', 'deduction_types', 'deduction_schedules'],
        'loan' => ['employee_deductions', 'deduction_types'],
        'train' => ['trainings'],
        'seminar' => ['trainings'],
        'certificat' => ['trainings', 'documents'],
        'travel' => ['travel_orders'],
        'document' => ['documents'],
        'file' => ['documents'],
        'upload' => ['documents'],
        'government' => ['government_ids'],
        'gsis' => ['government_ids'],
        'philhealth' => ['government_ids'],
        'pag-ibig' => ['government_ids'],
        'pagibig' => ['government_ids'],
        'tin' => ['government_ids'],
        'educat' => ['educations', 'eligibilities'],
        'school' => ['educations'],
        'degree' => ['educations'],
        'eligib' => ['eligibilities'],
        'experience' => ['work_experiences'],
        'famil' => ['family_members'],
        'address' => ['addresses'],
        'contact' => ['contacts'],
        'pass slip' => ['pass_slips'],
        'passslip' => ['pass_slips'],
        'request' => ['employee_requests'],
        'requirement' => ['legal_requirements', 'government_ids'],
        // People-only questions need nothing beyond the core tables. Listing
        // them here means such a question counts as "matched" and so does not
        // trigger the send-everything fallback.
        'employee' => [],
        'personnel' => [],
        'staff' => [],
        'department' => [],
        'office' => [],
        'position' => [],
        'designation' => [],
        'hired' => [],
        'appoint' => [],
        'headcount' => [],
        'gender' => [],
        'male' => [],
        'female' => [],
    ];

    /** Always present — nearly every question joins through these. */
    private const CORE_TABLES = ['employees', 'employment_details', 'departments', 'designations'];

    /**
     * Condensed column listing, read from the live schema so it cannot drift
     * from the database. Types are omitted except for enums, whose allowed
     * values the model genuinely needs in order to write a correct WHERE.
     */
    private function describeSchema(string $question = ''): string
    {
        $lines = [];

        foreach ($this->relevantTables($question) as $table) {
            try {
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } catch (\Throwable) {
                continue; // Table not present in this deployment.
            }

            $cols = array_map(function ($column) {
                return str_starts_with(strtolower($column->Type), 'enum')
                    ? $column->Field . ' ' . $column->Type
                    : $column->Field;
            }, $columns);

            $lines[] = "{$table}(" . implode(', ', $cols) . ')';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, string>
     */
    private function relevantTables(string $question): array
    {
        if ($question === '') {
            return self::ALLOWED_TABLES;
        }

        $q = strtolower($question);
        $tables = self::CORE_TABLES;
        $matched = false;

        foreach (self::TABLE_TOPICS as $keyword => $topicTables) {
            if (str_contains($q, $keyword)) {
                $matched = true;
                $tables = array_merge($tables, $topicTables);
            }
        }

        // Nothing recognisable: the question could be about anything, so send
        // the full list rather than guessing wrong and omitting the one table
        // it needed. A narrow-but-incomplete schema produces a broken query;
        // a wide one only costs tokens.
        if (!$matched) {
            return self::ALLOWED_TABLES;
        }

        return array_values(array_intersect(self::ALLOWED_TABLES, array_unique($tables)));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, array{role: string, content: string}> $history
     */
    private function narrate(User $user, string $question, string $sql, array $rows, array $history): string
    {
        if (empty($rows)) {
            return 'That query ran successfully but returned no rows — there is no data matching those criteria.';
        }

        $system = <<<'PROMPT'
You are the PRIME HRIS Assistant explaining the result of a database query to
an HR administrator.

The rows are ALREADY shown to the user as a table directly beneath your reply.
So summarise them — do not transcribe them.

- Lead with the headline number ("4 employees have 2 or more absences").
- Add the insight the table does not show: the pattern, the outlier, what to do.
- Do NOT list each row's field values; that is what the table is for.
- Every figure you cite must come from the rows given. Never estimate or invent.
- Under 120 words. No SQL, no JSON, no markdown table.
PROMPT;

        $sample = json_encode(array_slice($rows, 0, 25), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $count = count($rows);

        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => "Question: {$question}\n\nReturned {$count} row(s):\n{$sample}",
        ];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 800);

        return $answer ?: $this->fallbackNarration($rows);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function fallbackNarration(array $rows): string
    {
        $count = count($rows);
        $first = $rows[0] ?? [];

        if ($count === 1 && count($first) === 1) {
            return 'Result: ' . reset($first);
        }

        $headers = implode(' | ', array_keys($first));
        $lines = array_map(
            fn (array $row) => implode(' | ', array_map(fn ($v) => (string) $v, $row)),
            array_slice($rows, 0, 10)
        );

        return "Returned {$count} row(s):\n{$headers}\n" . implode("\n", $lines);
    }
}
