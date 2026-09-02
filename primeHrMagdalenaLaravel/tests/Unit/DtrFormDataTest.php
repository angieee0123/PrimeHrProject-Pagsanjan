<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\User;
use App\Services\DtrFormDataService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The printed DTR is a document the municipality signs and files, so the
 * properties pinned here are the ones that make it a *record* rather than a
 * picture of one:
 *
 *  1. it carries the period it says it carries, and nothing outside it;
 *  2. the days are in the order they happened;
 *  3. a period longer than one sheet continues onto another, whole;
 *  4. a slot with no punch is blank — never a filled-in default;
 *  5. it narrows the same way the Detailed DTR modal narrows on screen.
 *
 * (5) is the pair rule from the employee exports: the modal decides in
 * JavaScript (`applyDtrChip()` / `chipState` in detailedDtrModal.js) and the
 * sheet decides in DtrFormDataService. Both halves keep working when they
 * drift; only their agreement breaks, which is what these cases watch.
 *
 * No database: the service reads an Employee and an array, so the relations
 * are stubbed rather than migrated. That is also why RefreshDatabase does not
 * appear here — it cannot run in this project at all.
 */
class DtrFormDataTest extends TestCase
{
    private function service(): DtrFormDataService
    {
        return new DtrFormDataService();
    }

    private function employee(
        string $first = 'Juan',
        string $middle = 'Santos',
        string $last = 'Dela Cruz',
        ?string $department = 'Human Resource Management Office',
        string $idNo = 'EMP-PGS-0001'
    ): Employee {
        $employee = new Employee([
            'employee_id' => $idNo,
            'first_name'  => $first,
            'middle_name' => $middle,
            'last_name'   => $last,
        ]);

        $detail = new EmploymentDetail();

        if ($department !== null) {
            $dept = new Department();
            $dept->name = $department;
            $detail->setRelation('departmentRelation', $dept);
        } else {
            $detail->setRelation('departmentRelation', null);
        }

        $employee->setRelation('employmentDetail', $detail);

        return $employee;
    }

    /**
     * One day, shaped the way generateDetailedRecords() shapes it.
     */
    private function day(string $date, array $overrides = []): array
    {
        $carbon = Carbon::parse($date);

        return array_merge([
            'date_key'     => $carbon->format('Y-m-d'),
            'date'         => $carbon->format('M d, Y'),
            'day'          => $carbon->format('l'),
            'am_in'        => '08:00',
            'am_out'       => '12:00',
            'pm_in'        => '13:00',
            'pm_out'       => '17:00',
            'ot_in'        => null,
            'ot_out'       => null,
            'late_minutes' => 0,
            'is_on_leave'  => false,
            'is_absent'    => false,
            'is_abandoned' => false,
        ], $overrides);
    }

    private function build(array $records, string $start, string $end, string $view = 'all'): array
    {
        return $this->service()->build(
            $this->employee(),
            $records,
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay(),
            $view
        );
    }

    /** A period of consecutive days, all of them fully logged. */
    private function span(string $start, string $end): array
    {
        $records = [];

        for ($d = Carbon::parse($start); $d->lte(Carbon::parse($end)); $d->addDay()) {
            $records[] = $this->day($d->format('Y-m-d'));
        }

        return $records;
    }

    // ── 1. The period ────────────────────────────────────────────────────

    #[Test]
    public function the_date_filter_bounds_what_the_sheet_prints(): void
    {
        // A generator handed days either side of the range: the sheet must
        // still carry only the range it prints in its own filename.
        $data = $this->build([
            $this->day('2025-12-31'),
            $this->day('2026-01-01'),
            $this->day('2026-01-15'),
            $this->day('2026-01-31'),
            $this->day('2026-02-01'),
        ], '2026-01-01', '2026-01-31');

        $this->assertSame(
            ['2026-01-01', '2026-01-15', '2026-01-31'],
            array_column($data['rows'], 'date_key')
        );
    }

    #[Test]
    public function both_ends_of_the_range_are_inclusive(): void
    {
        $data = $this->build($this->span('2026-03-01', '2026-03-15'), '2026-03-01', '2026-03-15');

        $this->assertCount(15, $data['rows']);
        $this->assertSame('2026-03-01', $data['rows'][0]['date_key']);
        $this->assertSame('2026-03-15', end($data['rows'])['date_key']);
    }

    // ── 2. The order ─────────────────────────────────────────────────────

    #[Test]
    public function days_print_in_date_order_however_they_arrived(): void
    {
        $data = $this->build([
            $this->day('2026-01-04'),
            $this->day('2026-01-01'),
            $this->day('2026-01-03'),
            $this->day('2026-01-02'),
        ], '2026-01-01', '2026-01-31');

        $this->assertSame(
            ['2026-01-01', '2026-01-02', '2026-01-03', '2026-01-04'],
            array_column($data['rows'], 'date_key')
        );
    }

    #[Test]
    public function a_day_handed_over_twice_prints_once(): void
    {
        $data = $this->build([
            $this->day('2026-01-02'),
            $this->day('2026-01-02', ['am_in' => '09:00']),
        ], '2026-01-01', '2026-01-31');

        $this->assertCount(1, $data['rows']);
    }

    // ── 3. Continuation pages ────────────────────────────────────────────

    #[Test]
    public function a_period_longer_than_one_sheet_continues_onto_another(): void
    {
        config(['dtr_form.rows_per_page' => 24]);

        // 51 days -> 24 + 24 + 3, with nothing dropped between the sheets.
        $data = $this->build($this->span('2026-01-01', '2026-02-20'), '2026-01-01', '2026-02-20');

        $this->assertCount(51, $data['rows']);
        $this->assertSame(3, $data['pageCount']);
        $this->assertSame([24, 24, 3], array_map('count', $data['pages']));

        $printed = array_merge(...array_map(
            fn (array $page) => array_column($page, 'date_key'),
            $data['pages']
        ));

        $this->assertSame(array_column($data['rows'], 'date_key'), $printed);
    }

    #[Test]
    public function the_sheets_page_at_the_configured_row_count(): void
    {
        // The row count belongs to the *form*: re-cut the sheet and the
        // pagination has to follow it rather than a constant in the service.
        config(['dtr_form.rows_per_page' => 10]);

        $data = $this->build($this->span('2026-01-01', '2026-01-25'), '2026-01-01', '2026-01-25');

        $this->assertSame([10, 10, 5], array_map('count', $data['pages']));
    }

    #[Test]
    public function a_period_with_no_records_still_prints_one_sheet(): void
    {
        // The blank ruled form for that period, over the employee's own name —
        // what the office would file. A zero-page PDF will not open.
        $data = $this->build([], '2026-01-01', '2026-01-31');

        $this->assertSame([], $data['rows']);
        $this->assertSame(1, $data['pageCount']);
        $this->assertSame([[]], $data['pages']);
    }

    // ── 4. What lands in a cell ──────────────────────────────────────────

    #[Test]
    public function a_slot_with_no_punch_is_left_blank(): void
    {
        $data = $this->build([
            $this->day('2026-01-05', ['pm_in' => null, 'pm_out' => null]),
        ], '2026-01-01', '2026-01-31');

        $row = $data['rows'][0];

        $this->assertSame('8:00', $row['am_in']);
        $this->assertSame('12:00', $row['am_out']);
        $this->assertSame('', $row['pm_in']);
        $this->assertSame('', $row['pm_out']);
        $this->assertSame('', $row['ot_in']);
    }

    #[Test]
    public function overtime_keeps_the_meridiem_its_column_does_not_name(): void
    {
        // "am IN" and "pm IN" say which half of the day they are. "OT IN"
        // does not, so a bare 6:00 there could be either end of the day.
        $data = $this->build([
            $this->day('2026-01-05', ['ot_in' => '17:30', 'ot_out' => '20:00']),
        ], '2026-01-01', '2026-01-31');

        $row = $data['rows'][0];

        $this->assertSame('5:30 PM', $row['ot_in']);
        $this->assertSame('8:00 PM', $row['ot_out']);
        $this->assertSame('8:00', $row['am_in'], 'the am column carries no meridiem');
    }

    #[Test]
    public function a_covered_day_prints_its_marker_once_rather_than_in_four_columns(): void
    {
        // generateDetailedRecords() writes "ON LEAVE" into all four slots. Four
        // copies do not fit the columns, and blanking it outright would make an
        // approved absence read as an unexplained one on the document that
        // certifies it.
        $data = $this->build([
            $this->day('2026-01-05', [
                'is_on_leave' => true,
                'am_in' => 'ON LEAVE', 'am_out' => 'ON LEAVE',
                'pm_in' => 'ON LEAVE', 'pm_out' => 'ON LEAVE',
            ]),
            $this->day('2026-01-06', [
                'am_in' => 'ON TRAVEL', 'am_out' => 'ON TRAVEL',
                'pm_in' => 'ON TRAVEL', 'pm_out' => 'ON TRAVEL',
            ]),
        ], '2026-01-01', '2026-01-31');

        $this->assertSame('ON LEAVE', $data['rows'][0]['marker']);
        $this->assertSame('ON TRAVEL', $data['rows'][1]['marker']);

        foreach (['am_in', 'am_out', 'pm_in', 'pm_out'] as $slot) {
            $this->assertSame('', $data['rows'][0][$slot]);
            $this->assertSame('', $data['rows'][1][$slot]);
        }
    }

    #[Test]
    public function a_logged_day_carries_no_marker(): void
    {
        $data = $this->build([$this->day('2026-01-05')], '2026-01-01', '2026-01-31');

        $this->assertSame('', $data['rows'][0]['marker']);
    }

    // ── 5. The View chip, mirrored ───────────────────────────────────────

    #[Test]
    public function the_view_chip_narrows_the_sheet_the_way_it_narrows_the_table(): void
    {
        $records = [
            $this->day('2026-01-05'),                                        // Mon, present
            $this->day('2026-01-06', ['late_minutes' => 12]),                // Tue, late
            $this->day('2026-01-07', ['pm_out' => null]),                    // Wed, incomplete
            $this->day('2026-01-08', [                                       // Thu, absent
                'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
            ]),
            $this->day('2026-01-09', ['is_on_leave' => true]),               // Fri, leave
            $this->day('2026-01-10', [                                       // Sat, weekend
                'am_in' => null, 'am_out' => null, 'pm_in' => null, 'pm_out' => null,
            ]),
        ];

        $keys = fn (string $view) => array_column(
            $this->build($records, '2026-01-01', '2026-01-31', $view)['rows'],
            'date_key'
        );

        $this->assertCount(6, $keys('all'));
        $this->assertSame(['2026-01-05'], $keys('present'));
        $this->assertSame(['2026-01-06'], $keys('late'));
        $this->assertSame(['2026-01-07'], $keys('incomplete'));
        $this->assertSame(['2026-01-08'], $keys('absent'));
        $this->assertSame(['2026-01-09'], $keys('leave'));
        $this->assertSame(['2026-01-10'], $keys('weekend'));
        $this->assertSame(['2026-01-05'], $keys('mon'));
        $this->assertCount(5, $keys('weekdays'));
    }

    #[Test]
    public function a_weekend_with_punches_is_still_a_weekend(): void
    {
        // chipState checks the weekend before it checks completeness, so a
        // Saturday worked is filed under "weekend", not "present".
        $data = $this->build([$this->day('2026-01-10')], '2026-01-01', '2026-01-31', 'present');

        $this->assertSame([], $data['rows']);
        $this->assertCount(1, $this->build([$this->day('2026-01-10')], '2026-01-01', '2026-01-31', 'weekend')['rows']);
    }

    #[Test]
    public function an_unknown_view_prints_the_whole_period(): void
    {
        // A chip this service has not heard of is a bug in the pair above.
        // Answering it with an empty sheet would report the period as holding
        // no records at all — a false statement, where printing everything is
        // merely an unapplied filter.
        $data = $this->build($this->span('2026-01-01', '2026-01-05'), '2026-01-01', '2026-01-31', 'nonsense');

        $this->assertCount(5, $data['rows']);
        $this->assertSame('all', $data['view']);
    }

    // ── The identity block and the file ──────────────────────────────────

    #[Test]
    public function the_identity_block_is_read_from_the_employee(): void
    {
        $data = $this->build([], '2026-01-01', '2026-01-31');

        $this->assertSame('EMP-PGS-0001', $data['employee']['id_no']);
        $this->assertSame('Juan S. Dela Cruz', $data['employee']['name']);
        $this->assertSame('Human Resource Management Office', $data['employee']['department']);
    }

    #[Test]
    public function an_employee_with_no_department_leaves_that_field_blank(): void
    {
        $data = $this->service()->build(
            $this->employee(department: null),
            [],
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-31'),
        );

        $this->assertSame('', $data['employee']['department']);
    }

    #[Test]
    public function the_filename_names_the_employee_and_the_period(): void
    {
        $data = $this->build([], '2026-01-01', '2026-01-31');

        $this->assertSame('DTR_Juan_S_Dela_Cruz_2026-01-01_to_2026-01-31.pdf', $data['filename']);
    }

    #[Test]
    public function the_filename_survives_a_name_a_file_system_would_not_take(): void
    {
        // It reaches a Content-Disposition header and a Windows file system;
        // a "#" is enough to truncate one and be refused by the other.
        $data = $this->service()->build(
            $this->employee(first: 'José', middle: '', last: "D'Cruz #2"),
            [],
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-31'),
        );

        $this->assertMatchesRegularExpression(
            '/^DTR_[A-Za-z0-9_]+_2026-01-01_to_2026-01-31\.pdf$/',
            $data['filename']
        );
    }

    // ── The signatories ──────────────────────────────────────────────────

    #[Test]
    public function the_hrmo_signs_before_the_municipal_administrator(): void
    {
        $data = $this->build([], '2026-01-01', '2026-01-31');

        $this->assertSame('HRMO - OIC', $data['signatories'][0]['title']);
        $this->assertSame('ENGR. ALEX C. PAGUIO', $data['signatories'][1]['name']);
        $this->assertSame('Municipal Administrator', $data['signatories'][1]['title']);
    }

    /** An account that generated the sheet, with an optional employee row. */
    private function generator(
        ?string $name = null,
        ?string $username = null,
        ?Employee $employee = null
    ): User {
        $user = new User();
        $user->name = $name;
        $user->username = $username;
        $user->setRelation('employee', $employee);

        return $user;
    }

    private function signedBy(?User $generatedBy): array
    {
        return $this->service()->build(
            $this->employee(),
            [],
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-01-31'),
            'all',
            $generatedBy
        )['signatories'];
    }

    #[Test]
    public function the_hrmo_rule_is_signed_by_whoever_generated_the_sheet(): void
    {
        // The staffer who chose the period and pressed the button is the one
        // certifying this copy. A fixed name there puts somebody else's name
        // over a record they never saw.
        $signatories = $this->signedBy($this->generator(
            name: 'ignored when an employee row exists',
            employee: $this->employee(first: 'Angelica', middle: 'Dizon', last: 'Cuevas')
        ));

        $this->assertSame('ANGELICA D. CUEVAS', $signatories[0]['name']);
        $this->assertSame('HRMO - OIC', $signatories[0]['title'], 'the capacity stays configuration');
    }

    #[Test]
    public function an_account_with_no_employee_row_is_named_from_the_account(): void
    {
        // An HR account need not be linked to an employee record, and there is
        // still a person behind it.
        $this->assertSame(
            'ROSARIO T. MENDOZA',
            $this->signedBy($this->generator(name: 'Rosario T. Mendoza'))[0]['name']
        );

        $this->assertSame(
            'HRMO.STAFF',
            $this->signedBy($this->generator(username: 'hrmo.staff'))[0]['name']
        );
    }

    #[Test]
    public function an_account_with_no_usable_name_falls_back_to_the_configured_officer(): void
    {
        // "admin" over a signature rule reads as a signed document. So does
        // "N/A". Both hand the rule back to the configured fallback.
        foreach ([null, '', 'admin', 'N/A'] as $useless) {
            $signatories = $this->signedBy($this->generator(name: $useless));

            $this->assertSame('JEREMY R. POGI', $signatories[0]['name'], "for name: " . var_export($useless, true));
        }

        $this->assertSame('JEREMY R. POGI', $this->signedBy(null)[0]['name']);
    }

    #[Test]
    public function the_municipal_administrator_is_never_replaced_by_the_generator(): void
    {
        // Only the signatory the config marks `name_from => generator` moves.
        // Nothing in this system records the Administrator's decision, so that
        // name stands over a blank rule for a wet signature.
        $signatories = $this->signedBy($this->generator(
            employee: $this->employee(first: 'Angelica', middle: 'Dizon', last: 'Cuevas')
        ));

        $this->assertSame('ENGR. ALEX C. PAGUIO', $signatories[1]['name']);
    }
}
