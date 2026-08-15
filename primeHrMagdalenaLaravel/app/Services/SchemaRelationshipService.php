<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * The database's own relationship graph, described for the model.
 *
 * The text-to-SQL prompts used to send a bare column listing —
 * `attendance(id, employee_id, date, am_in, …)` — and leave the model to work
 * out how anything connected to anything else. Every join it wrote was a guess
 * from column names, and the two prompts patched over that with a handful of
 * hand-typed domain rules ("employees link to departments through
 * employment_details.department_id") that covered four tables out of thirty-two.
 *
 * The schema declares **76 foreign keys**. They are the answer to exactly the
 * question the model was guessing at, and they are readable at runtime, so this
 * reads them instead of restating them — the same reasoning as
 * HrPolicyFactsService: a fact that can be read from the system does not belong
 * in a prompt as a literal, because the copy drifts and the original does not.
 *
 * Two properties this must hold:
 *
 *  - **The allow-list is enforced on both ends of every edge.** `users.employee_id`
 *    references `employees.id`, so an unfiltered graph would name `users` in the
 *    prompt — the one table SafeSqlService exists to keep the model from
 *    learning about. An edge survives only if both its tables are readable.
 *  - **information_schema is read here, never by the model.** The ban in
 *    SafeSqlService is on generated SQL referencing it. This is application
 *    code with a fixed statement and a bound parameter, which is what the ban
 *    is protecting, not a thing it forbids.
 */
class SchemaRelationshipService
{
    /**
     * Per-request memo. The schema does not change inside one request, and this
     * is consulted once per SQL generation attempt — including the retry after
     * a failed statement.
     *
     * @var array<int, array{table: string, column: string, references_table: string, references_column: string}>|null
     */
    private static ?array $memo = null;

    /**
     * Every foreign key in the current database, restricted to tables the
     * assistant may read.
     *
     * @return array<int, array{table: string, column: string, references_table: string, references_column: string}>
     */
    public function edges(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $allowed = array_flip(SafeSqlService::allowedTables());

        try {
            $rows = DB::select(
                'SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 ORDER BY TABLE_NAME, COLUMN_NAME',
            );
        } catch (\Throwable) {
            // SQLite in tests, or a user without information_schema rights.
            // The prompt simply goes without the block rather than failing the
            // question — a missing map is a worse query, not a broken one.
            return self::$memo = [];
        }

        $edges = [];

        foreach ($rows as $row) {
            $table = $row->TABLE_NAME;
            $references = $row->REFERENCED_TABLE_NAME;

            // Both ends must be readable. Dropping only the referenced side
            // would still print "users" as the target of an edge.
            if (!isset($allowed[$table], $allowed[$references])) {
                continue;
            }

            $edges[] = [
                'table' => $table,
                'column' => $row->COLUMN_NAME,
                'references_table' => $references,
                'references_column' => $row->REFERENCED_COLUMN_NAME,
            ];
        }

        return self::$memo = $edges;
    }

    /**
     * The join paths among a given set of tables, as prompt text.
     *
     * Restricted to the tables actually being described: SafeSqlService narrows
     * the schema to the subject of the question to keep the prompt affordable,
     * and a relationship block naming tables absent from that schema would
     * invite the model to join to something it cannot see.
     *
     * @param array<int, string> $tables
     */
    public function describeFor(array $tables): string
    {
        $scope = array_flip($tables);

        $lines = [];

        foreach ($this->edges() as $edge) {
            if (!isset($scope[$edge['table']], $scope[$edge['references_table']])) {
                continue;
            }

            $lines[] = sprintf(
                '%s.%s -> %s.%s',
                $edge['table'],
                $edge['column'],
                $edge['references_table'],
                $edge['references_column'],
            );
        }

        if (empty($lines)) {
            return '';
        }

        sort($lines);

        return implode("\n", array_unique($lines));
    }

    /**
     * Clear the per-request memo. For tests that rebuild the schema between
     * cases.
     */
    public static function flush(): void
    {
        self::$memo = null;
    }
}
