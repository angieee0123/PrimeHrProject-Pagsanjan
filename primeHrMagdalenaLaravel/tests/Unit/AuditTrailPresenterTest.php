<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Models\User;
use App\Services\AuditTrailPresenter;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

/**
 * The Audit Trail table is readable only while a row stays one line tall, and
 * that property lives entirely in this presenter — the CSS clips, but what is
 * handed to it has to be short in the first place. These tests pin the parts
 * that would silently regrow the row or leak something into it.
 *
 * No database: the presenter reads an Audit instance's attributes and nothing
 * else, which is what lets it be tested at all in a project where
 * `RefreshDatabase` cannot run (see CLAUDE.md).
 */
class AuditTrailPresenterTest extends TestCase
{
    private AuditTrailPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenter = new AuditTrailPresenter();
    }

    private function audit(array $attributes = []): Audit
    {
        $audit = new Audit();

        $audit->forceFill($attributes + [
            'id'             => 1,
            'event'          => 'updated',
            'auditable_type' => 'App\\Models\\Employee',
            'auditable_id'   => 8,
            'url'            => 'https://hris.test/admin/personnel/8?tab=employment',
            'ip_address'     => '10.0.0.4',
            'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                . '(KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        ]);

        $audit->created_at = now();

        return $audit;
    }

    /**
     * The summary is what the table column renders. However many fields an
     * edit touched, it names at most three and counts the rest — a row that
     * lists twenty is the wall this page was rebuilt to stop being.
     */
    public function test_summary_names_at_most_three_fields_however_many_changed(): void
    {
        $old = $new = [];

        foreach (range(1, 20) as $i) {
            $old["field_$i"] = "before $i";
            $new["field_$i"] = "after $i";
        }

        $row = $this->presenter->row($this->audit(['old_values' => $old, 'new_values' => $new]), null);

        $this->assertSame(20, $row['change_count']);
        $this->assertSame('Field 1, Field 2, Field 3 +17 more', $row['summary']);
        $this->assertStringNotContainsString('after', $row['summary'], 'Values belong in the drawer, not the table.');
    }

    /** A key written back unchanged is not a change, and must not inflate the count. */
    public function test_untouched_fields_are_not_counted_as_changes(): void
    {
        $row = $this->presenter->row($this->audit([
            'old_values' => ['status' => 'Active', 'remarks' => 'old note'],
            'new_values' => ['status' => 'Active', 'remarks' => 'new note'],
        ]), null);

        $this->assertSame(1, $row['change_count']);
        $this->assertSame('Remarks', $row['changes'][0]['field']);
    }

    /**
     * `created` has no before and `deleted` has no after. Those sides must come
     * back as null so the drawer can render them as an absence rather than as
     * a value that was struck through or added.
     */
    public function test_created_has_no_before_and_deleted_has_no_after(): void
    {
        $created = $this->presenter->row($this->audit(['event' => 'created', 'new_values' => ['status' => 'Active']]), null);
        $deleted = $this->presenter->row($this->audit(['event' => 'deleted', 'old_values' => ['status' => 'Active']]), null);

        $this->assertNull($created['changes'][0]['old']);
        $this->assertSame('Active', $created['changes'][0]['new']);

        $this->assertSame('Active', $deleted['changes'][0]['old']);
        $this->assertNull($deleted['changes'][0]['new']);
    }

    /**
     * `updated` is a warning, not a danger. It was previously given the
     * `on-hold` class, which statusBadge.css maps to the danger palette — so
     * every ordinary edit rendered in the same red as a deletion.
     */
    public function test_each_event_gets_its_own_badge(): void
    {
        $badge = fn (string $event) => $this->presenter->row($this->audit(['event' => $event]), null)['badge'];

        $this->assertSame('is-success', $badge('created'));
        $this->assertSame('is-warning', $badge('updated'));
        $this->assertSame('is-danger', $badge('deleted'));
        $this->assertSame('is-info', $badge('restored'));
        $this->assertSame('is-neutral', $badge('something-else'));
    }

    /** An audit row is a verbatim copy of a model's columns, secrets included. */
    public function test_secret_looking_values_are_redacted(): void
    {
        $row = $this->presenter->row($this->audit([
            'old_values' => ['password' => 'hunter2', 'remember_token' => 'abc'],
            'new_values' => ['password' => 'letmein', 'remember_token' => 'xyz'],
        ]), null);

        foreach ($row['changes'] as $change) {
            $this->assertSame('••••••••', $change['old']);
            $this->assertSame('••••••••', $change['new']);
        }
    }

    /** A long text column must not arrive at the drawer at full length. */
    public function test_long_values_are_truncated(): void
    {
        $row = $this->presenter->row($this->audit([
            'new_values' => ['remarks' => str_repeat('a', 5000)],
            'old_values' => [],
        ]), null);

        $this->assertLessThan(250, strlen($row['changes'][0]['new']));
    }

    /** Field names read as English, and a foreign key is named for its target. */
    public function test_field_names_are_humanised(): void
    {
        $this->assertSame('Department', $this->presenter->label('department_id'));
        $this->assertSame('First name', $this->presenter->label('first_name'));
        $this->assertSame('IP address', $this->presenter->label('ip_address'));
    }

    /**
     * Audits written from a console command store the command name where a URL
     * would go; `parse_url` reads `artisan:` off one as a scheme and returns
     * the rest, so the shortening has to check first.
     */
    public function test_console_urls_are_not_mistaken_for_web_addresses(): void
    {
        $this->assertSame('/admin/personnel/8', $this->presenter->row($this->audit(), null)['path']);

        $console = $this->presenter->row($this->audit(['url' => 'artisan:leave:accrue']), null);
        $this->assertSame('artisan:leave:accrue', $console['path']);
    }

    /** The agent string is kept whole; what the table shows is the short form. */
    public function test_user_agent_is_summarised_without_being_lost(): void
    {
        $row = $this->presenter->row($this->audit(), null);

        $this->assertSame('Chrome on Windows', $row['device']);
        $this->assertStringContainsString('AppleWebKit', $row['user_agent']);
    }

    /** An audit with no user is the system acting, not a blank row. */
    public function test_a_missing_user_is_named_rather_than_left_empty(): void
    {
        $row = $this->presenter->row($this->audit(), null);

        $this->assertSame('System', $row['user_name']);
        $this->assertSame('SY', $row['user_initials']);
        $this->assertNull($row['user_photo'], 'There is no face to show for the system.');
    }

    /**
     * "Performed by" shows the person's photo. `employees.photo` already holds a
     * public URL, so it travels through unchanged — the views point an `<img>`
     * straight at it.
     */
    public function test_the_performing_users_photo_is_carried_through(): void
    {
        $row = $this->presenter->row($this->audit(), $this->user([
            'photo' => '/storage/employees/photos/juan.png',
        ]));

        $this->assertSame('/storage/employees/photos/juan.png', $row['user_photo']);
    }

    /**
     * Without a photo the tile falls back to initials — and they come from the
     * employee's name, not the username, because that is what the photo would
     * have shown. `admin01` would otherwise initialise to "AD" for Juan Dela Cruz.
     */
    public function test_initials_come_from_the_employees_name_when_there_is_no_photo(): void
    {
        $row = $this->presenter->row($this->audit(), $this->user(['photo' => null]));

        $this->assertNull($row['user_photo']);
        $this->assertSame('JD', $row['user_initials']);
    }

    /** A user with no linked employee row still gets a tile, off the username. */
    public function test_a_user_without_an_employee_record_falls_back_to_the_username(): void
    {
        $user = new User();
        $user->forceFill(['id' => 3, 'username' => 'admin01']);
        $user->setRelation('employee', null);

        $row = $this->presenter->row($this->audit(), $user);

        $this->assertNull($row['user_photo']);
        $this->assertSame('AD', $row['user_initials']);
    }

    /**
     * A user with their employee relation already loaded — the controller eager
     * loads it, so the presenter never queries.
     */
    private function user(array $employee): User
    {
        $record = new Employee();
        $record->forceFill($employee + [
            'id'         => 8,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);

        $user = new User();
        $user->forceFill(['id' => 3, 'username' => 'admin01', 'employee_id' => 8]);
        $user->setRelation('employee', $record);

        return $user;
    }
}
