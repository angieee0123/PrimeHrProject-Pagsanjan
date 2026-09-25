<?php

namespace Tests\Unit;

use App\Models\PassSlip;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A pass slip's `date` is a calendar date, and it must reach the browser as one.
 *
 * It was cast as a plain `date`, which Carbon reads as Asia/Manila midnight and
 * `toJSON()` then reports in UTC — so a slip dated the 14th went over the wire
 * as `2026-09-13T16:00:00.000000Z`. The history table printed the Carbon
 * (`format('M d, Y')` → "Sep 14, 2026") while the detail modal read the
 * calendar date off the first ten characters of the wire value → "Sep 13".
 * The same slip named two different days on one screen.
 *
 * `approved_at` is the opposite case and is deliberately left alone: it is a
 * real instant, and flattening it to a date is what made a slip approved at 1am
 * Manila print the previous day.
 */
class PassSlipDateFormatTest extends TestCase
{
    private function slip(): PassSlip
    {
        $slip = new PassSlip([
            'date' => '2026-09-14',
            'approved_at' => '2026-09-14 09:30:00',
        ]);

        $slip->syncOriginal();

        return $slip;
    }

    /**
     * The property the bug broke: whatever the modal reads off the wire has to
     * name the day the table names.
     *
     * `formatPassSlipDate()` in `resources/js/employee/employeePassSlip.js`
     * takes the first ten characters of the value verbatim, so this asserts the
     * two surfaces against each other rather than either one against a literal.
     */
    #[Test]
    public function the_wire_date_names_the_same_day_the_table_prints(): void
    {
        $slip = $this->slip();

        $this->assertSame(
            $slip->date->format('Y-m-d'),
            substr($slip->toArray()['date'], 0, 10),
        );
    }

    /**
     * Stated as the failure itself, because the ambient timezone is what drove
     * it: under `Asia/Manila` (the app's default) a midnight date serialized as
     * 16:00Z the day before, and under UTC the same value happened to be
     * harmless. The format is on the cast so neither can move it.
     */
    #[Test]
    public function a_midnight_date_is_never_published_as_the_previous_utc_day(): void
    {
        $this->assertSame('2026-09-14', $this->slip()->toArray()['date']);
    }

    /** The table, the printed form and the export all still get a Carbon. */
    #[Test]
    public function the_attribute_is_still_a_date_the_rest_of_the_system_can_format(): void
    {
        $slip = $this->slip();

        $this->assertInstanceOf(Carbon::class, $slip->date);
        $this->assertSame('Sep 14, 2026', $slip->date->format('M d, Y'));
    }

    /** A value assigned straight from the request is stored as the same day. */
    #[Test]
    public function the_stored_value_does_not_shift_a_day(): void
    {
        $this->assertSame('2026-09-14', $this->slip()->date->format('Y-m-d'));
    }

    /**
     * `approved_at` stays a real instant. Cast it to `date:Y-m-d` for symmetry
     * and a slip approved at 1am Manila — 17:30Z the day before — loses both
     * the time and, on the wire, the day.
     */
    #[Test]
    public function approved_at_stays_an_instant_and_is_not_flattened_to_a_date(): void
    {
        $wire = $this->slip()->toArray()['approved_at'];

        $this->assertStringContainsString('T', $wire, 'approved_at must keep its time.');
        $this->assertSame('Z', substr($wire, -1), 'approved_at is published as a UTC instant.');
    }

    /**
     * The other half of the bug lived in the modal, which PHPUnit cannot run:
     * `approved_at` was routed through the literal calendar-date reader, so a
     * slip approved at 1am Manila printed "Sep 13". The instant needs the
     * converting reader; the calendar date needs the literal one.
     */
    #[Test]
    public function the_detail_modal_converts_the_approval_instant_instead_of_reading_it_literally(): void
    {
        $js = file_get_contents(resource_path('js/employee/employeePassSlip.js'));

        $this->assertStringContainsString(
            'formatPassSlipTimestamp(data.approved_at)',
            $js,
            'The approval instant must go through formatPassSlipTimestamp(); '
            . 'formatPassSlipDate() reads a calendar date literally and prints the '
            . 'day before for anything approved between midnight and 8am Manila.',
        );

        $this->assertStringContainsString(
            'formatPassSlipDate(data.date)',
            $js,
            'The slip date is a calendar date and is read literally.',
        );
    }
}
