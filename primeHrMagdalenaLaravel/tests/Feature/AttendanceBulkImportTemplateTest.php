<?php

namespace Tests\Feature;

use App\Http\Controllers\AttendanceController;
use App\Models\AccreditedHoursLog;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\BuildsAttendanceSchema;
use Tests\TestCase;

/**
 * The attendance bulk import template, and the week file generated from it.
 *
 * The template used to exist only in `bulkImportAttendance.js`, as an array
 * joined with `,`. Two things followed: a value containing a comma shifted
 * every column after it, and — because it lived in the browser — nothing could
 * assert it against the columns `bulkImport()` actually reads. It is now built
 * by `AttendanceController::templateCsv()`, and the tests here are what hold it
 * to the documented example and to a real import.
 *
 * The property that matters most is not the column *names* but the four daytime
 * punches. `computeAccreditedHours()` accredits a session only from a matched
 * pair, so a row carrying `am_in` and `pm_out` alone — the shorthand anyone
 * would reach for — imports without complaint and accredits zero minutes. A
 * template that taught that shape would cost a day's pay per row, silently, so
 * `the_week_file_accrues_a_full_day_per_row` asserts the accredited figure
 * rather than the rounding.
 *
 * No RefreshDatabase: the project's migrations cannot run on the test
 * connection (see CLAUDE.md), so `BuildsAttendanceSchema` raises the tables an
 * import reaches — attendance, its accreditation log, and the daily salary row
 * behind it.
 */
class AttendanceBulkImportTemplateTest extends TestCase
{
    use BuildsAttendanceSchema;

    private const COLUMNS = ['employee_id', 'date', 'am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out'];

    private const WEEK_FILE = 'docs/bulk_import_attendance_2026-09-14_week.csv';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttendanceSchema();
        $this->createUsersSchema();
    }

    protected function tearDown(): void
    {
        foreach ([
            'leave_transactions', 'leave_balances', 'daily_salary_computations',
            'accredited_hours_log', 'pass_slips', 'attendance_exemptions',
            'attendance_punches', 'attendance', 'schedules', 'employment_details',
            'departments', 'designations', 'employees', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::create([
            'email' => 'hr@example.test',
            'username' => 'hradmin',
            'password' => 'x',
            'roles' => ['admin'],
            'status' => 'Active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function import(string $csv): TestResponse
    {
        return $this->actingAs($this->admin())->post('/admin/attendance/bulk-import', [
            'csv_file' => UploadedFile::fake()->createWithContent('attendance.csv', $csv),
        ]);
    }

    /**
     * Parse a CSV into rows of cells, so two files are compared by what a
     * reader reads out of them. `fputcsv` quotes any cell containing a space
     * where a hand-written example may leave it bare; both are the same CSV.
     *
     * @return array<int, array<int, string>>
     */
    private function parseCsv(string $csv): array
    {
        return array_map(
            fn ($line) => str_getcsv($line, ',', '"', ''),
            array_values(array_filter(
                explode("\n", str_replace("\r\n", "\n", trim($csv))),
                fn ($line) => $line !== '',
            )),
        );
    }

    private function weekFile(): string
    {
        $path = base_path(self::WEEK_FILE);

        $this->assertFileExists($path, 'The week import file is missing from docs/.');

        return (string) file_get_contents($path);
    }

    /** An employee on the municipality's standard 08:00–17:00 day. */
    private function employee(string $employeeId, string $firstName = 'Juan'): Employee
    {
        $employee = Employee::create([
            'employee_id' => $employeeId,
            'first_name' => $firstName,
            'last_name' => 'Dela Cruz',
        ]);

        Schedule::create([
            'employee_id' => $employee->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'am_in' => '08:00:00',
            'am_out' => '12:00:00',
            'pm_in' => '13:00:00',
            'pm_out' => '17:00:00',
        ]);

        return $employee;
    }

    /**
     * The download is the shape the importer reads, spelled the same way as the
     * week file in `docs/`.
     *
     * The header line is pinned as text because it is the one row quoting
     * cannot vary: no column name contains a space, so `fputcsv` writes them
     * bare, exactly as the committed file has them.
     */
    #[Test]
    public function the_downloaded_template_is_the_documented_shape(): void
    {
        $template = $this->parseCsv(AttendanceController::templateCsv());
        $week = $this->parseCsv($this->weekFile());

        $this->assertSame(self::COLUMNS, $template[0], 'The template columns are not the ones bulkImport() reads.');
        $this->assertSame($week[0], $template[0], 'The template and the week file disagree on their columns.');
        $this->assertSame(
            implode(',', self::COLUMNS),
            explode("\n", AttendanceController::templateCsv())[0],
            'The template header line is no longer written as a bare comma-separated row.',
        );
    }

    /**
     * The download route serves the template, behind the same `auth` gate as
     * the import it feeds.
     */
    #[Test]
    public function the_template_route_serves_the_template_to_a_signed_in_admin(): void
    {
        $this->get('/admin/attendance/bulk-import/template')->assertRedirect('/login');

        $response = $this->actingAs($this->admin())
            ->get('/admin/attendance/bulk-import/template')
            ->assertOk();

        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'Attendance_Import_Template.csv',
            (string) $response->headers->get('Content-Disposition'),
        );
        $this->assertSame(AttendanceController::templateCsv(), $response->getContent());
    }

    /**
     * Every sample row carries all four daytime punches.
     *
     * This is the anti-drift rule, and it is here rather than only in the
     * accreditation test below because a template that lost `am_out` and
     * `pm_in` — the two cells that look redundant on paper — would still have
     * eight columns and a plausible header.
     */
    #[Test]
    public function every_template_row_carries_all_four_daytime_punches(): void
    {
        $rows = $this->parseCsv(AttendanceController::templateCsv());
        array_shift($rows);

        $this->assertNotEmpty($rows);

        foreach ($rows as $index => $row) {
            foreach ([2, 3, 4, 5] as $column) {
                $this->assertNotSame(
                    '',
                    $row[$column],
                    "Template row {$index} leaves " . self::COLUMNS[$column] . " blank. A session is "
                    . 'accredited only from a matched pair, so this row would import and accredit zero minutes.',
                );
            }
        }
    }

    /**
     * The week file: one row per employee per weekday, no Jade Flores, and
     * every row a complete scheduled day.
     */
    #[Test]
    public function the_week_file_covers_every_employee_but_jade_flores(): void
    {
        $rows = $this->parseCsv($this->weekFile());
        $header = array_shift($rows);

        $this->assertSame(self::COLUMNS, $header);

        $ids = array_values(array_unique(array_column($rows, 0)));
        $dates = array_values(array_unique(array_column($rows, 1)));

        sort($dates);
        $this->assertSame(
            ['2026-09-14', '2026-09-15', '2026-09-16', '2026-09-17', '2026-09-18'],
            $dates,
            'The week file is not the working week it claims to be.',
        );

        // One row for every employee for every day — no gaps to fill in later.
        $this->assertCount(count($ids) * 5, $rows);
        $this->assertSame(count($rows), count(array_unique(array_map(
            fn ($row) => $row[0] . '|' . $row[1],
            $rows,
        ))), 'The week file repeats an employee/date pair.');

        $this->assertNotContains(
            'EMP-2025-0001',
            $ids,
            'Jade Flores is in the week file; she was excluded explicitly.',
        );

        foreach ($rows as $index => $row) {
            foreach ([2, 3, 4, 5] as $column) {
                $this->assertNotSame(
                    '',
                    $row[$column],
                    'Week file row ' . ($index + 2) . ' leaves ' . self::COLUMNS[$column] . ' blank.',
                );
            }
        }
    }

    /**
     * The week file imports, and each row accrues a full scheduled day.
     *
     * The employees are created here with the schedule read off the file's own
     * rows — `am_in`/`am_out`/`pm_in`/`pm_out` as the file states them. That is
     * the point: the file was generated from the live roster's schedules, so a
     * fixture that invented its own times would be testing the translation
     * rather than the file. The assertion that matters is the accredited figure
     * at the end.
     */
    #[Test]
    public function the_week_file_accrues_a_full_day_per_row(): void
    {
        $rows = $this->parseCsv($this->weekFile());
        array_shift($rows);

        $byEmployee = [];
        foreach ($rows as $row) {
            $byEmployee[$row[0]][] = $row;
        }

        $expectedMinutes = [];

        foreach ($byEmployee as $employeeId => $employeeRows) {
            $first = $employeeRows[0];
            $employee = $this->employee($employeeId, 'Employee' . count($expectedMinutes));

            Schedule::where('employee_id', $employee->id)->update([
                'am_in' => $first[2] . ':00',
                'am_out' => $first[3] . ':00',
                'pm_in' => $first[4] . ':00',
                'pm_out' => $first[5] . ':00',
            ]);

            $toMin = fn (string $time) => (int) explode(':', $time)[0] * 60 + (int) explode(':', $time)[1];

            // Each session is credited as scheduled; the file's punches match
            // the schedule exactly, so nothing is lost to lateness or undertime.
            $expectedMinutes[] = ($toMin($first[3]) - $toMin($first[2]))
                + ($toMin($first[5]) - $toMin($first[4]));
        }

        $response = $this->import($this->weekFile());

        $response->assertOk()->assertJson([
            'success' => true,
            'imported' => count($rows),
            'updated' => 0,
            'skipped' => 0,
        ]);
        $this->assertSame([], $response->json('errors'));

        $this->assertSame(count($rows), Attendance::count());

        // One weekday per employee, so every attendance row carries the same
        // accredited figure — the full scheduled day.
        $this->assertSame(
            array_values(array_unique($expectedMinutes)),
            Attendance::query()->distinct()->pluck('accredited_hours')->sort()->values()->all(),
            'A week row did not accrue its full scheduled day.',
        );

        $this->assertGreaterThan(0, min($expectedMinutes));

        // Nothing in this file is late or short: every punch is the schedule's.
        $this->assertSame(
            0,
            AccreditedHoursLog::where('late_minutes', '>', 0)
                ->orWhere('undertime_minutes', '>', 0)
                ->count(),
            'A row generated from the employee\'s own schedule reported late or undertime work.',
        );
    }

    /**
     * The trap the template's four punches exist to keep an admin out of.
     *
     * `am_in` and `pm_out` look like the whole day — they are the two punches an
     * employee clocks at the ends of it — and `bulkImport()` accepts such a row
     * without a word: the "No time punches provided" guard passes because two
     * cells are filled, the row is stored, and the DTR then reads "Incomplete".
     * What it accredits is nothing, because `computeAccreditedHours()` builds
     * each session from a matched pair. This is pinned so the sample rows in the
     * template cannot be "simplified" back into that shape.
     */
    #[Test]
    public function a_row_with_only_the_outer_punches_imports_but_accredits_nothing(): void
    {
        $this->employee('EMP-2026-0001', 'Juan');

        $csv = "employee_id,date,am_in,am_out,pm_in,pm_out\n"
            . "EMP-2026-0001,2026-09-14,08:00,,,17:00\n";

        $response = $this->import($csv);

        // Accepted, not refused: this is what makes it dangerous.
        $response->assertOk()->assertJson(['imported' => 1, 'skipped' => 0]);

        $attendance = Attendance::firstOrFail();

        $this->assertSame('08:00', $attendance->am_in);
        $this->assertSame('17:00', $attendance->pm_out);
        $this->assertSame(
            0,
            $attendance->accredited_hours,
            'A half-punched day accredited time; the template no longer needs all four punches.',
        );
    }
}
