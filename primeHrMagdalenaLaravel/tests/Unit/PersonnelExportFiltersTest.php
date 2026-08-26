<?php

namespace Tests\Unit;

use App\Http\Controllers\PersonnelExportController;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The Personnel export is only trustworthy while it is built from the same
 * criteria the table on screen was filtered by.
 *
 * That is a property spanning three files — the toolbar declares the controls,
 * `adminPersonnel.js` reads them into one predicate, and
 * `PersonnelExportController` re-applies that predicate server-side — and
 * nothing about any one of them looks wrong on its own when they drift. It has
 * drifted twice already: the search box wrote `row.style.display` in a pass of
 * its own that undid the toolbar's, and the export sent both sets of criteria
 * to a server that ANDed them, so the file came back narrower than the screen.
 *
 * These pin the wiring rather than the rows. Rendering the page cannot catch
 * any of it: the markup is correct either way.
 */
class PersonnelExportFiltersTest extends TestCase
{
    /** Every control on the page => the query param it is sent as. */
    private const CONTROLS = [
        'departmentFilter'      => 'department',
        'statusFilter'          => 'status',
        'hiredDateFrom'         => 'hired_from',
        'hiredDateTo'           => 'hired_to',
        'personnelSearchInput'  => 'search',
    ];

    private function js(): string
    {
        return file_get_contents(resource_path('js/admin/personnel/adminPersonnel.js'));
    }

    private function controller(): string
    {
        return file_get_contents(app_path('Http/Controllers/PersonnelExportController.php'));
    }

    #[Test]
    public function every_toolbar_control_is_read_into_the_filter_state(): void
    {
        $js = $this->js();

        // personnelFilterState() is the single reader. A control read anywhere
        // else is a control the export can miss.
        $state = $this->between($js, 'function personnelFilterState()', "\n}");

        foreach (array_keys(self::CONTROLS) as $control) {
            $this->assertStringContainsString(
                $control,
                $state,
                "personnelFilterState() must read #{$control}; a control it does not "
                . 'read is one the Export button cannot send.'
            );
        }
    }

    #[Test]
    public function every_filter_state_field_is_sent_as_a_query_param(): void
    {
        $js = $this->between($this->js(), 'function exportTableData(', "\n}");

        foreach (self::CONTROLS as $control => $param) {
            $this->assertStringContainsString(
                "'" . $param . "'",
                $js,
                "exportTableData() must send `{$param}` (from #{$control}); a filter "
                . 'left off the query string produces a file wider than the screen.'
            );
        }
    }

    #[Test]
    public function the_controller_applies_every_param_the_page_sends(): void
    {
        $controller = $this->controller();

        foreach (self::CONTROLS as $param) {
            $this->assertStringContainsString(
                "'" . $param . "'",
                $controller,
                "PersonnelExportController must read `{$param}`; a param it ignores is "
                . 'a filter the file silently drops.'
            );
        }

        // The order the columns are sorted in travels too, so the file lists
        // the records in the order they were read on screen.
        foreach (['sort', 'dir'] as $param) {
            $this->assertStringContainsString("'" . $param . "'", $controller);
        }
    }

    #[Test]
    public function the_filters_and_the_search_compose_rather_than_replace_each_other(): void
    {
        $js = $this->js();

        // One predicate, declared once and called from one place. A second
        // pass over the roster means whichever ran last wins and the other's
        // criteria are silently dropped from the screen but not from the
        // export -- which is exactly how the search box used to undo the
        // toolbar.
        $this->assertSame(
            2,
            substr_count($js, 'matchesPersonnelFilters('),
            'matchesPersonnelFilters() is declared once and called once, from '
            . 'refreshPersonnelRows(). A second caller is a second pass writing '
            . 'row.style.display, and it undoes the first.'
        );

        // The appointed-date window is unique to the roster filter, so a second
        // implementation of the predicate shows up here first: every read of it
        // has to be inside the one predicate.
        $predicate = $this->between($js, 'function matchesPersonnelFilters(', "\n}");

        $this->assertGreaterThan(0, substr_count($predicate, 'dataset.hired'));
        $this->assertSame(
            substr_count($predicate, 'dataset.hired'),
            substr_count($js, 'dataset.hired'),
            'The appointed-date window is read in matchesPersonnelFilters() and '
            . 'nowhere else; a second reader is a second predicate to keep in step '
            . 'with the server.'
        );

        foreach (['function applyFilters()', 'function searchPersonnel()'] as $entry) {
            $this->assertStringContainsString(
                'refreshPersonnelRows()',
                $this->between($js, $entry, "\n}"),
                "{$entry} must go through refreshPersonnelRows() so the toolbar and "
                . 'the search box narrow the same set instead of clobbering it.'
            );
        }
    }

    #[Test]
    public function the_department_filter_matches_exactly_on_both_sides(): void
    {
        // `includes()` let "Accounting" pull in "Accounting and Budget Office",
        // which put rows in the file that the department select excludes.
        $this->assertStringNotContainsString(
            'deptText.includes(',
            $this->js(),
            'The department filter compares exactly; the select value is the '
            . "department's name verbatim, so a substring match only over-matches."
        );
    }

    #[Test]
    public function the_masterlist_columns_are_grouped_and_labelled(): void
    {
        $columns = $this->invoke('masterlistColumns');

        $this->assertNotEmpty($columns);

        foreach ($columns as $i => $column) {
            foreach (['group', 'label', 'value'] as $key) {
                $this->assertArrayHasKey($key, $column, "Column {$i} is missing `{$key}`.");
            }
            $this->assertNotSame('', trim($column['label']), "Column {$i} has no heading.");
        }

        // Groups are contiguous: the band prints each name once, over its first
        // column, so a group split in two would read as two headings.
        $seen = [];
        $previous = null;
        foreach ($columns as $column) {
            if ($column['group'] !== $previous) {
                $this->assertNotContains(
                    $column['group'],
                    $seen,
                    "Group '{$column['group']}' appears in two blocks; the band names "
                    . 'each group once, over its first column.'
                );
                $seen[] = $column['group'];
                $previous = $column['group'];
            }
        }
    }

    #[Test]
    public function a_column_no_record_filled_in_is_dropped_but_the_core_ones_stay(): void
    {
        $columns = [
            ['group' => 'A', 'label' => 'No.',     'always' => true, 'value' => fn ($r, $i) => $i + 1],
            ['group' => 'A', 'label' => 'Name',    'always' => true, 'value' => fn ($r) => $r['name']],
            ['group' => 'A', 'label' => 'Suffix',                    'value' => fn ($r) => $r['suffix']],
            ['group' => 'B', 'label' => 'Office',  'always' => true, 'value' => fn ($r) => $r['office']],
            ['group' => 'B', 'label' => 'Step',                      'value' => fn ($r) => $r['step']],
        ];

        $rows = collect([
            ['name' => 'Juan', 'suffix' => '',    'office' => 'HRMO', 'step' => '1'],
            ['name' => 'Ana',  'suffix' => '',    'office' => 'HRMO', 'step' => ''],
        ]);

        $grid = $this->invoke('buildGrid', $rows, $columns);

        $this->assertSame(
            ['No.', 'Name', 'Office', 'Step'],
            $grid['headings'],
            'Suffix is blank on every row and must be dropped; Step is filled on one '
            . 'and must stay.'
        );

        // The band still lines up with the columns that survived.
        $this->assertSame(['A', '', 'B', ''], $grid['band']);
        $this->assertSame(
            [['1', 'Juan', 'HRMO', '1'], ['2', 'Ana', 'HRMO', '']],
            $grid['rows']
        );

        // An `always` column stays even when nothing filled it in -- a
        // masterlist with no name column is not a masterlist.
        $blank = $this->invoke('buildGrid', collect([
            ['name' => '', 'suffix' => '', 'office' => '', 'step' => ''],
        ]), $columns);

        $this->assertSame(['No.', 'Name', 'Office'], $blank['headings']);
    }

    #[Test]
    public function an_unknown_sort_key_falls_back_to_the_order_the_page_loads_in(): void
    {
        // The Personnel page loads created_at desc, so an export taken without
        // touching a column heading has to come back in that order -- otherwise
        // the file lists the same records the screen shows, shuffled.
        foreach (['salary_grade); DROP TABLE employees;--', '', 'nonsense'] as $key) {
            $sort = $this->invoke('resolveSort', \Illuminate\Http\Request::create('/', 'GET', [
                'sort' => $key,
                'dir' => 'sideways',
            ]));

            $this->assertSame('recent', $sort['key']);
            $this->assertSame('desc', $sort['dir']);
            $this->assertStringContainsString('newest first', $sort['label']);
        }
    }

    #[Test]
    public function the_sortable_columns_line_up_on_both_sides(): void
    {
        // SORT_KEYS is indexed by the table's column order; every key in it has
        // to be one the controller understands, or clicking that heading and
        // exporting silently reverts the file to the default order.
        preg_match('/const SORT_KEYS = \[(.*?)\];/s', $this->js(), $m);
        $this->assertNotEmpty($m, 'SORT_KEYS must be declared in adminPersonnel.js.');

        preg_match_all("/'([a-z]+)'/", $m[1], $keys);
        $this->assertNotEmpty($keys[1]);

        foreach ($keys[1] as $key) {
            $sort = $this->invoke(
                'resolveSort',
                \Illuminate\Http\Request::create('/', 'GET', ['sort' => $key])
            );

            $this->assertSame(
                $key,
                $sort['key'],
                "resolveSort() must know the `{$key}` column; a key the server does "
                . 'not recognise silently falls back to the default order.'
            );
        }
    }

    /** Call a private method on the controller. */
    private function invoke(string $method, ...$args)
    {
        $controller = new PersonnelExportController();
        $reflection = new ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($controller, ...$args);
    }

    /** The text between a marker and the next line that is exactly `$end`. */
    private function between(string $haystack, string $start, string $end): string
    {
        $from = strpos($haystack, $start);
        $this->assertNotFalse($from, "Could not find `{$start}`.");

        $to = strpos($haystack, $end, $from);
        $this->assertNotFalse($to, "Could not find the end of `{$start}`.");

        return substr($haystack, $from, $to - $from);
    }
}
