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

        $sql = $this->generateSql($user, $question, $history);

        if (!$sql) {
            return [
                'answer' => 'I could not turn that into a database query. Try naming the records you want '
                    . '— for example "employees appointed after 2023" or "approved leave in June".',
                'data' => [],
            ];
        }

        $verdict = $this->validate($sql);

        if (!$verdict['safe']) {
            Log::channel('ai_audit')->warning('AI SQL rejected', [
                'user_id' => $user->id,
                'reason' => $verdict['reason'],
                'sql' => $sql,
            ]);

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
        } catch (\Throwable $e) {
            Log::channel('ai_audit')->error('AI SQL execution failed', [
                'user_id' => $user->id,
                'sql' => $sql,
                'error' => $e->getMessage(),
            ]);

            return [
                'answer' => 'That query failed against the database. Try rephrasing the question.',
                'data' => [],
                'error' => $e->getMessage(),
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
    private function generateSql(User $user, string $question, array $history): ?string
    {
        $schema = $this->describeSchema();
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
6. Employees link to departments through employment_details.department_id, and
   to job titles through employment_details.designation_id.
7. leave_applications.status is one of: pending, approved, rejected, cancelled.
8. attendance uses the column `date`, not `attendance_date`.

Example:
Question: employees hired in 2024
SELECT e.employee_id, e.first_name, e.last_name, d.name AS department, ed.appointment_date
FROM employees e
JOIN employment_details ed ON ed.employee_id = e.id
LEFT JOIN departments d ON d.id = ed.department_id
WHERE YEAR(ed.appointment_date) = 2024
LIMIT 200
PROMPT;

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $question];

        $raw = AiChatService::chat($user, $system, $messages, 0.1, 600);

        return $raw ? $this->extractSql($raw) : null;
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
     * Guarantee a row cap even if the model omitted one.
     */
    private function enforceRowCap(string $sql): string
    {
        if (preg_match('/\bLIMIT\s+(\d+)/i', $sql, $m)) {
            return (int) $m[1] <= self::MAX_ROWS
                ? $sql
                : preg_replace('/\bLIMIT\s+\d+/i', 'LIMIT ' . self::MAX_ROWS, $sql);
        }

        return $sql . ' LIMIT ' . self::MAX_ROWS;
    }

    /**
     * Column listing for the allowed tables only, read from the live schema so
     * it cannot drift from the database.
     */
    private function describeSchema(): string
    {
        $lines = [];

        foreach (self::ALLOWED_TABLES as $table) {
            try {
                $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
            } catch (\Throwable) {
                continue; // Table not present in this deployment.
            }

            $cols = implode(', ', array_map(fn ($c) => "{$c->Field} {$c->Type}", $columns));
            $lines[] = "{$table}: {$cols}";
        }

        return implode("\n", $lines);
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

- State what the data shows, in plain language, leading with the headline number.
- Call out the notable rows and any pattern worth acting on.
- Every figure you cite must come from the rows given. Never estimate or invent.
- If the rows were truncated, say the count shown may be partial.
- Under 180 words. No SQL, no raw JSON.
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
