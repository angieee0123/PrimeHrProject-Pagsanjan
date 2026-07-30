<?php

namespace Tests\Unit;

use App\Services\AiAccessPolicy;
use App\Services\SafeSqlService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The assistant generates SQL from user text, so validate() is the boundary
 * between "the model wrote something odd" and "the database executed it".
 * These cases are the contract for that boundary.
 */
class SafeSqlValidationTest extends TestCase
{
    private SafeSqlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SafeSqlService(new AiAccessPolicy());
    }

    #[Test]
    #[DataProvider('destructiveStatements')]
    public function it_blocks_destructive_statements(string $sql): void
    {
        $verdict = $this->service->validate($sql);

        $this->assertFalse($verdict['safe'], "Should have blocked: {$sql}");
        $this->assertNotEmpty($verdict['reason']);
    }

    public static function destructiveStatements(): array
    {
        return [
            'delete' => ['DELETE FROM employees'],
            'update' => ["UPDATE employees SET first_name = 'x'"],
            'insert' => ["INSERT INTO employees (first_name) VALUES ('x')"],
            'drop' => ['DROP TABLE employees'],
            'truncate' => ['TRUNCATE TABLE attendance'],
            'alter' => ['ALTER TABLE employees ADD COLUMN x INT'],
            'create' => ['CREATE TABLE evil (id INT)'],
            'grant' => ["GRANT ALL ON *.* TO 'x'@'%'"],
            'stacked statement' => ['SELECT 1; DROP TABLE employees'],
            'stacked with newline' => ["SELECT id FROM employees;\nDELETE FROM employees"],
            'outfile exfiltration' => ["SELECT * FROM employees INTO OUTFILE '/tmp/dump'"],
            'load_file' => ["SELECT LOAD_FILE('/etc/passwd')"],
            'time-based probe' => ['SELECT SLEEP(10)'],
            'benchmark probe' => ['SELECT BENCHMARK(10000000, MD5(1))'],
            'session variable write' => ['SET @@global.read_only = 0'],
        ];
    }

    #[Test]
    #[DataProvider('restrictedTables')]
    public function it_blocks_tables_holding_credentials(string $sql): void
    {
        $verdict = $this->service->validate($sql);

        $this->assertFalse($verdict['safe'], "Should have blocked: {$sql}");
    }

    public static function restrictedTables(): array
    {
        return [
            'users holds password hashes' => ['SELECT * FROM users'],
            'api tokens' => ['SELECT * FROM personal_access_tokens'],
            'sessions' => ['SELECT * FROM sessions'],
            'password resets' => ['SELECT * FROM password_reset_tokens'],
            'join onto users' => ['SELECT e.id FROM employees e JOIN users u ON u.employee_id = e.id'],
            'union onto users' => ['SELECT first_name FROM employees UNION SELECT password FROM users'],
            'union all onto tokens' => ['SELECT id FROM employees UNION ALL SELECT token FROM personal_access_tokens'],
            'subquery onto users' => ['SELECT * FROM employees WHERE id IN (SELECT employee_id FROM users)'],
            'information_schema' => ['SELECT table_name FROM information_schema.tables'],
            'mysql system db' => ['SELECT user FROM mysql.user'],
        ];
    }

    #[Test]
    #[DataProvider('legitimateQueries')]
    public function it_allows_legitimate_read_queries(string $sql): void
    {
        $verdict = $this->service->validate($sql);

        $this->assertTrue($verdict['safe'], "Should have allowed: {$sql} — got: " . ($verdict['reason'] ?? ''));
    }

    public static function legitimateQueries(): array
    {
        return [
            'simple select' => ['SELECT * FROM employees LIMIT 10'],
            'lowercase' => ['select id from employees limit 5'],
            'join' => ['SELECT e.first_name, d.name FROM employees e JOIN employment_details ed ON ed.employee_id = e.id JOIN departments d ON d.id = ed.department_id'],
            'aggregate' => ['SELECT COUNT(*) AS total FROM attendance WHERE date >= "2026-01-01"'],
            'column named created_at does not trip CREATE' => ['SELECT created_at FROM departments'],
            'column named updated_at does not trip UPDATE' => ['SELECT updated_at FROM departments'],
            'CTE' => ['WITH recent AS (SELECT * FROM leave_applications) SELECT * FROM recent'],
        ];
    }

    /**
     * A banned keyword hidden inside a string literal or a comment is inert —
     * but only because we strip both before scanning. If that stripping ever
     * regresses, these turn into false positives and the feature breaks.
     */
    #[Test]
    public function it_treats_keywords_inside_literals_and_comments_as_inert(): void
    {
        $inLiteral = 'SELECT name FROM departments WHERE name = "; DROP TABLE employees; --"';
        $inComment = 'SELECT id FROM employees WHERE 1=1 -- DROP TABLE employees';

        $this->assertTrue($this->service->validate($inLiteral)['safe']);
        $this->assertTrue($this->service->validate($inComment)['safe']);
    }

    /**
     * The classic trick: hide the payload in a comment that MySQL executes but
     * a naive scanner skips.
     */
    #[Test]
    public function it_blocks_mysql_executable_comments(): void
    {
        $verdict = $this->service->validate('SELECT * FROM employees /*! UNION SELECT password FROM users */');

        $this->assertFalse($verdict['safe']);
    }

    #[Test]
    public function it_rejects_empty_and_non_select_input(): void
    {
        $this->assertFalse($this->service->validate('')['safe']);
        $this->assertFalse($this->service->validate('   ')['safe']);
        $this->assertFalse($this->service->validate('SHOW TABLES')['safe']);
        $this->assertFalse($this->service->validate('DESCRIBE employees')['safe']);
    }
}
