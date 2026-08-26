<?php

namespace Tests\Unit;

use App\Services\CsvReportWriter;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * One date format across every CSV this system hands out.
 *
 * The exports were split between two spellings with nothing recording a
 * decision either way: Payroll and Deductions wrote `2026-08-26`, while Leave &
 * Benefits, Travel Order and Pass Slip wrote `Aug 26, 2026`. An HR officer
 * exporting two tabs of the same system got two different date columns.
 *
 * The tie-breaker is the spreadsheet rather than the reader. `M d, Y` is a
 * string Excel sorts alphabetically — Apr, Aug, Dec — so a register sorted by
 * date comes back in month-name order, which looks sorted and is not; and it
 * is parsed as a date only under an English locale, so the same file opened on
 * a differently-configured machine silently degrades to text. ISO sorts
 * correctly as text *and* parses everywhere.
 *
 * Prose keeps the long form: nothing sorts a letterhead, and "August 26, 2026"
 * is what an official document says.
 */
class CsvDateFormatTest extends TestCase
{
    private function date(): Carbon
    {
        return Carbon::parse('2026-08-26 13:45:00');
    }

    #[Test]
    public function a_table_cell_gets_a_sortable_iso_date(): void
    {
        $this->assertSame('2026-08-26', CsvReportWriter::date($this->date()));
    }

    #[Test]
    public function a_timestamp_cell_keeps_the_iso_date_and_adds_the_time(): void
    {
        $this->assertSame('2026-08-26 1:45 PM', CsvReportWriter::dateTime($this->date()));
    }

    #[Test]
    public function prose_keeps_the_long_form(): void
    {
        $this->assertSame('August 26, 2026', CsvReportWriter::longDate($this->date()));
    }

    #[Test]
    public function a_missing_date_reads_as_a_dash_rather_than_an_epoch(): void
    {
        // Formatting null would give 1970-01-01, which states a date the
        // record does not have.
        $this->assertSame('—', CsvReportWriter::date(null));
        $this->assertSame('—', CsvReportWriter::dateTime(null));
        $this->assertSame('—', CsvReportWriter::longDate(null));
    }

    #[Test]
    public function a_caller_may_name_what_absence_means_in_its_column(): void
    {
        // "Open-ended" and "No end date" say something a dash does not: the
        // record is complete, the column simply has no end.
        $this->assertSame('Open-ended', CsvReportWriter::date(null, 'Open-ended'));
        $this->assertSame('', CsvReportWriter::date(null, ''));
    }

    /**
     * The controllers must route their table dates through the helper rather
     * than formatting inline — an inline `format('M d, Y')` is a column that
     * cannot follow this rule, which is how the two spellings arose.
     *
     * Listed rather than globbed. `PersonnelExportController` and
     * `TrainingExportController` still format inline and are deliberately not
     * listed: they belong to pages outside the change that introduced this
     * helper, and rewriting a column on a page nobody asked about is how an
     * export quietly stops matching the printout somebody files it against.
     * Add them here when those pages are next worked on — the helper is
     * already theirs to call.
     */
    #[Test]
    public function no_export_controller_formats_a_table_date_inline(): void
    {
        $controllers = [
            'AdminReportsExportController.php',
            'DeductionExportController.php',
            'LeaveBenefitsExportController.php',
            'PassSlipExportController.php',
            'PayrollExportController.php',
            'TravelOrderExportController.php',
        ];

        foreach ($controllers as $name) {
            $path = app_path('Http/Controllers/' . $name);

            $this->assertFileExists($path);

            $source = file_get_contents($path);

            foreach (["format('M d, Y')", "format('M d, Y g:i A')"] as $inline) {
                $this->assertStringNotContainsString(
                    $inline,
                    $source,
                    "{$name} formats a date inline as `{$inline}`. Table dates go "
                    . 'through CsvReportWriter::date()/dateTime() so one rule covers '
                    . 'every file; prose uses longDate().',
                );
            }
        }
    }
}
