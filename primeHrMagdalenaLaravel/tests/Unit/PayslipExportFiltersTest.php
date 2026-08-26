<?php

namespace Tests\Unit;

use App\Http\Controllers\PayrollExportController;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The Payslip Management export has to select the rows the tab is showing.
 *
 * `salary_computations.status` defaults to 'draft', so most generated payslips
 * are stored as drafts. The screen knows this — `filterPayslips()` normalises
 * 'draft' to 'pending' before comparing, which is why the option labelled
 * "Pending/Draft" lists both. The export did not: it ran
 * `where('status', 'pending')` and so returned none of the drafts the user was
 * looking at, beneath a parameter block that named itself "Pending / Draft".
 * The file therefore stated a coverage it did not have — the failure mode is
 * an empty register read as "no payslips are awaiting approval".
 *
 * This is a rule spanning two files that cannot drift visibly: the query is
 * correct-looking PHP and the filter is correct-looking JS, and only their
 * disagreement is the bug.
 */
class PayslipExportFiltersTest extends TestCase
{
    /** @return list<string> */
    private function statusesFor(string $status): array
    {
        $method = new ReflectionMethod(PayrollExportController::class, 'statusesFor');
        $method->setAccessible(true);

        return $method->invoke(new PayrollExportController(), $status);
    }

    #[Test]
    public function pending_covers_drafts_because_the_screen_shows_them_as_pending(): void
    {
        $statuses = $this->statusesFor('pending');

        $this->assertContains('pending', $statuses);
        $this->assertContains(
            'draft',
            $statuses,
            "The Pending/Draft option lists drafts on screen, so the export must "
            . 'select them too — `status` defaults to draft, so excluding it '
            . 'exports an empty file from a populated tab.',
        );
    }

    #[Test]
    public function every_other_status_maps_to_itself(): void
    {
        foreach (['approved', 'rejected', 'paid'] as $status) {
            $this->assertSame(
                [$status],
                $this->statusesFor($status),
                "`{$status}` has no second stored spelling; widening it would "
                . 'export rows the tab does not show.',
            );
        }
    }

    /**
     * The label and the query are read off the same word, so a file that says
     * "Pending / Draft" has to be the one that selected both.
     */
    #[Test]
    public function the_screen_normalises_draft_to_pending(): void
    {
        $js = file_get_contents(resource_path('js/admin/payroll/payslip-management.js'));

        $filter = $this->between($js, 'function filterPayslips()', "\n}");

        $this->assertStringContainsString(
            "'draft'",
            $filter,
            'filterPayslips() must keep normalising draft to pending; if the screen '
            . 'stops treating drafts as pending, PayrollExportController::statusesFor() '
            . 'has to stop too, or the file goes wider than the tab.',
        );
    }

    #[Test]
    public function the_export_sends_the_status_the_toolbar_is_showing(): void
    {
        $js = file_get_contents(resource_path('js/admin/payroll/payslip-management.js'));

        $export = $this->between($js, 'function exportPayslips()', "\n}");

        $this->assertStringContainsString('statusFilter', $export);
        $this->assertStringContainsString(
            'status=',
            $export,
            'The status the user picked must reach the endpoint, or the file '
            . 'covers every payslip on file regardless of the filter on screen.',
        );
    }

    private function between(string $haystack, string $start, string $end): string
    {
        $from = strpos($haystack, $start);
        $this->assertNotFalse($from, "Could not find `{$start}`.");

        $to = strpos($haystack, $end, $from);
        $this->assertNotFalse($to, "Could not find the end of `{$start}`.");

        return substr($haystack, $from, $to - $from);
    }
}
