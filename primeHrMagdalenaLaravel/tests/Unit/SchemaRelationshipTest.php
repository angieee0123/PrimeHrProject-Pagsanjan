<?php

namespace Tests\Unit;

use App\Services\SafeSqlService;
use App\Services\SchemaRelationshipService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The relationship map is read straight out of information_schema, which knows
 * nothing about the assistant's allow-list. `users.employee_id` references
 * `employees.id`, so an unfiltered graph would name `users` in the text-to-SQL
 * prompt — the exact table SafeSqlService exists to keep the model from
 * learning about, and which getDbSchema() was already rewritten once to stop
 * describing.
 *
 * These run against whatever connection the suite has. Where foreign keys
 * cannot be reported (SQLite has no information_schema) the service returns an
 * empty map by design, and the assertions below hold vacuously — the property
 * being pinned is "never names a forbidden table", which an empty map satisfies
 * honestly rather than by accident.
 */
class SchemaRelationshipTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SchemaRelationshipService::flush();
    }

    /**
     * The tables SafeSqlService deliberately excludes must never appear on
     * either end of an edge. Dropping only the referenced side would still
     * print "users" as the target of a join.
     */
    #[Test]
    public function the_map_never_names_a_table_outside_the_allow_list(): void
    {
        $allowed = SafeSqlService::allowedTables();
        $edges = (new SchemaRelationshipService())->edges();

        if ($edges === []) {
            $this->markTestSkipped('This connection cannot report foreign keys.');
        }

        foreach ($edges as $edge) {
            $this->assertContains($edge['table'], $allowed, "leaked source table: {$edge['table']}");
            $this->assertContains(
                $edge['references_table'],
                $allowed,
                "leaked referenced table: {$edge['references_table']}"
            );
        }
    }

    /**
     * The same guarantee at the layer that actually reaches the prompt: asking
     * for a description that includes a forbidden table must not produce one.
     */
    #[Test]
    public function describing_a_forbidden_table_yields_nothing_about_it(): void
    {
        $description = (new SchemaRelationshipService())->describeFor(
            ['users', 'sessions', 'personal_access_tokens', 'password_reset_tokens']
        );

        $this->assertSame('', $description);
    }

    /**
     * A narrowed schema must not be handed joins to tables it does not contain
     * — that invites the model to reference a table it was never shown.
     */
    #[Test]
    public function the_description_is_confined_to_the_tables_asked_for(): void
    {
        $service = new SchemaRelationshipService();
        $scope = ['employees', 'employment_details'];

        $description = $service->describeFor($scope);

        if ($description === '') {
            $this->markTestSkipped('This connection cannot report foreign keys.');
        }

        foreach (explode("\n", $description) as $line) {
            preg_match_all('/([a-z_]+)\./', $line, $matches);

            foreach ($matches[1] as $table) {
                $this->assertContains($table, $scope, "out-of-scope table in description: {$table}");
            }
        }
    }
}
