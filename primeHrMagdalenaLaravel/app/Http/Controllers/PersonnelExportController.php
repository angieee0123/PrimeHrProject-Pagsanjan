<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\CsvReportWriter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * "Export" on the Personnel page — one method per tab.
 *
 * The exports used to be built in the browser by scraping the rendered table,
 * so they could only ever carry the columns the table happens to show and
 * arrived as a bare grid of values with no title, no municipality and no note
 * of which filters produced them — files nobody could identify a week later.
 *
 * They are built here now, from the records themselves, and the letterhead
 * comes from `CsvReportWriter` — the same block the Detailed Time Record and
 * the six Leave & Benefits exports carry, so every file this system hands out
 * reads as a document from the same office.
 *
 * The two tabs export different things and say so: Employee Records is the
 * personnel masterlist, Work Schedules is who is covered by which shift and
 * until when. Neither file is the other with columns hidden.
 *
 * **The file is the screen, not a superset of it.** Every control on the
 * Personnel page — the department and status selects, the appointed-date
 * range, the topbar search box, and the column the table is sorted on — is
 * sent as a query param and re-applied here against the records themselves.
 * The predicate below is field-for-field the one `applyFilters()` runs in the
 * browser, so a row hidden on screen cannot appear in the download and a row
 * on screen cannot be missing from it.
 */
class PersonnelExportController extends Controller
{
    /**
     * Employee Records tab → the personnel masterlist.
     *
     * Columns are declared once, in reading order and grouped by what they are
     * about: who the person is, what they hold, their personal details, how to
     * reach them. An optional column no record filled in is dropped from the
     * file rather than printed as a column of blanks — which is what used to
     * leave "Suffix" and "Step" as two empty gaps in the middle of every
     * masterlist this office produced.
     */
    public function export(Request $request)
    {
        $department = $this->param($request, 'department');
        $employmentType = $this->param($request, 'employment_type');
        $status = $this->param($request, 'status');
        $hiredFrom = $this->param($request, 'hired_from');
        $hiredTo = $this->param($request, 'hired_to');
        $search = $this->param($request, 'search');
        $sort = $this->resolveSort($request);

        // Same relations the page loads, plus the 201-file rows the table has
        // no column for -- the point of exporting is to get more than the
        // screen shows.
        $employees = Employee::with([
                'employmentDetail.departmentRelation',
                'employmentDetail.designationRelation',
                'user',
                'addresses',
                'contacts',
            ])
            ->get();

        $employees = $employees->filter(function (Employee $employee) use (
            $department, $employmentType, $status, $hiredFrom, $hiredTo, $search
        ) {
            $detail = $employee->employmentDetail;

            // Exact match, never a substring one: "Accounting" must not pull in
            // "Accounting and Budget Office". The select's value is the
            // department's name verbatim, so there is nothing to loosen for.
            if ($department !== '' && $department !== 'All Departments') {
                if (($detail->departmentRelation->name ?? '') !== $department) {
                    return false;
                }
            }

            if ($employmentType !== '' && ($detail->employment_status ?? '') !== $employmentType) {
                return false;
            }

            if ($status !== '' && $this->accountStatus($employee) !== $status) {
                return false;
            }

            // The page filters on the appointment date, and an employee with no
            // appointment date on file cannot satisfy a date window -- matching
            // applyFilters(), which hides a row with an empty data-hired.
            if ($hiredFrom !== '' || $hiredTo !== '') {
                $appointed = $detail->appointment_date ?? null;
                if (!$appointed) {
                    return false;
                }
                $appointed = Carbon::parse($appointed)->format('Y-m-d');
                if ($hiredFrom !== '' && $appointed < $hiredFrom) {
                    return false;
                }
                if ($hiredTo !== '' && $appointed > $hiredTo) {
                    return false;
                }
            }

            // The three fields the topbar box searches, in the order it reads
            // them: the name as displayed, the employee id, the position.
            if ($search !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $this->fullName($employee),
                    (string) $employee->employee_id,
                    $detail->designationRelation->title ?? '',
                ]));
                if (!str_contains($haystack, mb_strtolower($search))) {
                    return false;
                }
            }

            return true;
        });

        $employees = $this->sortEmployees($employees, $sort);

        $columns = $this->masterlistColumns();
        $grid = $this->buildGrid($employees, $columns);

        $fileName = 'Employee_Records_' . now()->format('Y-m-d') . '.csv';

        return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
            $employees, $grid, $department, $employmentType, $status,
            $hiredFrom, $hiredTo, $search, $sort
        ) {
            $csv->letterhead(
                'Employee Records — Personnel Masterlist',
                'Human Resource Management Office · PRIME HRIS',
                'Records on file as of ' . now()->format('F d, Y')
            );

            // Every control on the page, printed whether or not it was touched:
            // a reader has to be able to tell "this file covers everything"
            // from "this cell did not get written". The two date bounds are
            // spelled out separately as well as combined, because the question
            // asked of this block is usually "what start date did they pick".
            $csv->parameters([
                'Department / Office:' => $department !== '' ? $department : 'All Departments',
                'Employment Type:'     => $employmentType !== '' ? $employmentType : 'All Types',
                'Account Status:'      => $status !== '' ? $status : 'All Status',
                'Date Appointed From:' => $hiredFrom !== '' ? Carbon::parse($hiredFrom)->format('F d, Y') : 'No start date set',
                'Date Appointed To:'   => $hiredTo !== '' ? Carbon::parse($hiredTo)->format('F d, Y') : 'No end date set',
                'Period Covered:'      => $this->describeRange($hiredFrom, $hiredTo),
                'Search Term:'         => $search !== '' ? $search : 'None',
                'Sorted By:'           => $sort['label'],
            ], $employees->count());

            $csv->row(['EMPLOYEE RECORDS']);
            $csv->row($grid['band']);
            $csv->columns($grid['headings']);

            foreach ($grid['rows'] as $row) {
                $csv->row($row);
            }

            if ($employees->isEmpty()) {
                $csv->emptyNotice('No employee records matched the filters above.');
            }

            $active = $employees->filter(fn (Employee $e) => $this->accountStatus($e) === 'Active')->count();

            $csv->summary('Summary', [
                'Total Employees:'         => $employees->count(),
                'Active Accounts:'         => $active,
                'Inactive Accounts:'       => $employees->count() - $active,
                'Departments Represented:' => $employees
                    ->map(fn (Employee $e) => $e->employmentDetail->departmentRelation->name ?? 'Unassigned')
                    ->unique()
                    ->count(),
            ]);

            $csv->summary('Breakdown by Employment Type', $this->countsBy(
                $employees,
                fn (Employee $e) => $this->text($e->employmentDetail->employment_status ?? '') ?: 'Unspecified'
            ));

            $csv->summary('Breakdown by Department / Office', $this->countsBy(
                $employees,
                fn (Employee $e) => $e->employmentDetail->departmentRelation->name ?? 'Unassigned'
            ));

            $csv->notes([
                'Rows are exactly the records shown on the Personnel page under the filters printed above.',
                'A blank cell means the detail is not on file. Optional columns no record filled in are omitted from this file.',
                'Dates are written as MMM DD, YYYY. Length of service is measured to the generation date above.',
            ]);
        });
    }

    /**
     * Work Schedules tab → who is covered by which shift, and until when.
     *
     * The status and the date it turns over are resolved by
     * `Employee::scheduleStatus()`, the same method the table reads, so the
     * file and the screen cannot disagree about who is unscheduled.
     */
    public function schedules(Request $request)
    {
        $department = $this->param($request, 'department');

        try {
            $employees = Employee::with([
                    'schedule',
                    'employmentDetail.departmentRelation',
                    'employmentDetail.designationRelation',
                ])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            if ($department !== '' && $department !== 'All Departments') {
                $employees = $employees->filter(
                    fn (Employee $e) => ($e->employmentDetail->departmentRelation->name ?? '') === $department
                );
            }

            $employees = $employees->values();

            // Resolved once per employee: scheduleStatus() walks the whole
            // schedule collection, and the rows and the summary below both
            // need the same answer.
            $states = $employees->mapWithKeys(
                fn (Employee $e) => [$e->id => $e->scheduleStatus()]
            );

            $fileName = 'Work_Schedules_' . now()->format('Y-m-d') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $employees, $states, $department
            ) {
                $csv->letterhead(
                    'Work Schedules — Employee Shift Assignments',
                    'Human Resource Management Office · PRIME HRIS',
                    'Schedules in force as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Department / Office:' => $department !== '' ? $department : 'All Departments',
                    'Schedule as of:'      => now()->format('F d, Y'),
                ], $employees->count());

                // Same banded header as the masterlist: fourteen columns wide,
                // the band is what tells a reader where the shift slots stop
                // and the coverage dates begin.
                $csv->row(['WORK SCHEDULES']);
                $csv->row([
                    'EMPLOYEE', '', '', '', '',
                    'SHIFT', '', '', '', '',
                    'COVERAGE', '', '', '',
                ]);
                $csv->columns([
                    'No.', 'Employee ID', 'Employee Name', 'Department / Office', 'Position',
                    'AM In', 'AM Out', 'PM In', 'PM Out', 'Daily Hours',
                    'Schedule Status', 'Effective From', 'Effective Until', 'Remarks',
                ]);

                $time = fn (?string $value) => $value ? Carbon::parse($value)->format('g:i A') : '--:--';

                foreach ($employees as $index => $employee) {
                    $schedule = $employee->currentSchedule();
                    $state = $states[$employee->id];
                    $detail = $employee->employmentDetail;

                    $csv->row([
                        $index + 1,
                        $this->text($employee->employee_id),
                        $this->fullName($employee),
                        $this->text($detail->departmentRelation->name ?? '') ?: 'Unassigned',
                        $this->text($detail->designationRelation->title ?? ''),
                        $time($schedule?->am_in),
                        $time($schedule?->am_out),
                        $time($schedule?->pm_in),
                        $time($schedule?->pm_out),
                        $this->dailyHours($schedule),
                        $state['label'],
                        $this->date($schedule?->start_date),
                        $this->date($schedule?->end_date),
                        // What the screen puts under the badge: the date the
                        // state turns over. A list of statuses with no dates
                        // cannot answer "whose schedule lapses this month",
                        // which is the question the column exists for.
                        $state['date']
                            ? trim($state['note'] . ' ' . $this->date($state['date']))
                            : '',
                    ]);
                }

                if ($employees->isEmpty()) {
                    $csv->emptyNotice('No employees matched the filter above.');
                }

                $csv->summary('Summary', [
                    'Total Employees:'      => $employees->count(),
                    'Active Schedules:'     => $states->where('state', 'active')->count(),
                    'Upcoming Schedules:'   => $states->where('state', 'upcoming')->count(),
                    'Expired Schedules:'    => $states->where('state', 'expired')->count(),
                    'No Schedule Assigned:' => $states->where('state', 'none')->count(),
                ]);

                $csv->summary('Breakdown by Department / Office', $this->countsBy(
                    $employees,
                    fn (Employee $e) => $e->employmentDetail->departmentRelation->name ?? 'Unassigned'
                ));

                $csv->notes([
                    'Times shown are the schedule in force on the generation date. "--:--" means the slot is not set.',
                    'Expired and Not Set rows are the ones awaiting an assignment.',
                ]);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.personnel')->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * The masterlist's columns, in reading order.
     *
     * `group` is the band printed above the header row, so a twenty-column
     * sheet opened in Excel says where the identification columns stop and the
     * employment ones begin. `always` marks the columns that stay even when
     * every row leaves them blank — a masterlist with no name column is not a
     * masterlist, whereas a "Suffix" column nobody filled in is just a gap.
     *
     * @return array<int,array{group:string,label:string,value:callable,always?:bool}>
     */
    private function masterlistColumns(): array
    {
        $detail = fn (Employee $e) => $e->employmentDetail;

        return [
            ['group' => 'Identification', 'always' => true, 'label' => 'No.',
                'value' => fn (Employee $e, int $i) => $i + 1],
            ['group' => 'Identification', 'always' => true, 'label' => 'Employee ID',
                'value' => fn (Employee $e) => $this->text($e->employee_id)],
            ['group' => 'Identification', 'always' => true, 'label' => 'Last Name',
                'value' => fn (Employee $e) => $this->text($e->last_name)],
            ['group' => 'Identification', 'always' => true, 'label' => 'First Name',
                'value' => fn (Employee $e) => $this->text($e->first_name)],
            ['group' => 'Identification', 'label' => 'Middle Name',
                'value' => fn (Employee $e) => $this->text($e->middle_name)],
            ['group' => 'Identification', 'label' => 'Suffix',
                'value' => fn (Employee $e) => $this->text($e->suffix)],

            ['group' => 'Employment Details', 'always' => true, 'label' => 'Position / Designation',
                'value' => fn (Employee $e) => $this->text($detail($e)->designationRelation->title ?? '')],
            ['group' => 'Employment Details', 'always' => true, 'label' => 'Department / Office',
                'value' => fn (Employee $e) => $this->text($detail($e)->departmentRelation->name ?? '')],
            ['group' => 'Employment Details', 'always' => true, 'label' => 'Employment Type',
                'value' => fn (Employee $e) => $this->text($detail($e)->employment_status ?? '')],
            ['group' => 'Employment Details', 'label' => 'Salary Grade',
                'value' => fn (Employee $e) => $this->text($detail($e)->salary_grade ?? '')],
            ['group' => 'Employment Details', 'label' => 'Step Increment',
                'value' => fn (Employee $e) => $this->text($detail($e)->step_increment ?? '')],
            ['group' => 'Employment Details', 'always' => true, 'label' => 'Date Appointed',
                'value' => fn (Employee $e) => $this->date($detail($e)->appointment_date ?? null)],
            ['group' => 'Employment Details', 'always' => true, 'label' => 'Length of Service',
                'value' => fn (Employee $e) => $this->lengthOfService($detail($e)->appointment_date ?? null)],
            ['group' => 'Employment Details', 'always' => true, 'label' => 'Account Status',
                'value' => fn (Employee $e) => $this->accountStatus($e)],

            ['group' => 'Personal Information', 'label' => 'Sex',
                'value' => fn (Employee $e) => $this->titleCase($e->sex)],
            ['group' => 'Personal Information', 'label' => 'Date of Birth',
                'value' => fn (Employee $e) => $this->date($e->birth_date)],
            ['group' => 'Personal Information', 'label' => 'Age',
                'value' => fn (Employee $e) => $this->age($e->birth_date)],
            ['group' => 'Personal Information', 'label' => 'Civil Status',
                'value' => fn (Employee $e) => $this->titleCase($e->civil_status)],

            ['group' => 'Contact Information', 'label' => 'Email Address',
                'value' => fn (Employee $e) => mb_strtolower($this->text(optional($e->user)->email ?: $e->email))],
            ['group' => 'Contact Information', 'label' => 'Contact Number',
                'value' => fn (Employee $e) => $this->contactNumber($e)],
            ['group' => 'Contact Information', 'label' => 'Residential Address',
                'value' => fn (Employee $e) => $this->address($e)],
        ];
    }

    /**
     * Render the rows, then drop the optional columns every one of them left
     * blank, so the band and the header describe the sheet actually written.
     *
     * @param  Collection<int,Employee> $employees
     * @param  array<int,array<string,mixed>> $columns
     * @return array{band:array<int,string>,headings:array<int,string>,rows:array<int,array<int,string>>}
     */
    private function buildGrid(Collection $employees, array $columns): array
    {
        $rows = [];

        foreach ($employees->values() as $index => $employee) {
            $cells = [];
            foreach ($columns as $column) {
                $cells[] = (string) ($column['value'])($employee, $index);
            }
            $rows[] = $cells;
        }

        $keep = [];
        foreach ($columns as $i => $column) {
            $filled = !empty($column['always']);

            if (!$filled) {
                foreach ($rows as $cells) {
                    if (trim($cells[$i]) !== '') {
                        $filled = true;
                        break;
                    }
                }
            }

            if ($filled) {
                $keep[] = $i;
            }
        }

        // The band names each group once, over its first surviving column;
        // repeating it across all six identification columns would read as six
        // separate headings rather than one span.
        $band = [];
        $previous = null;
        foreach ($keep as $i) {
            $group = $columns[$i]['group'];
            $band[] = $group === $previous ? '' : mb_strtoupper($group);
            $previous = $group;
        }

        return [
            'band' => $band,
            'headings' => array_map(fn ($i) => $columns[$i]['label'], $keep),
            'rows' => array_map(
                fn (array $cells) => array_map(fn ($i) => $cells[$i], $keep),
                $rows
            ),
        ];
    }

    /**
     * The column the table is sorted on, sent by the page so the file lists
     * the records in the order they were read on screen.
     *
     * Dates are compared as dates here rather than as the "Mar 05, 2024"
     * strings the browser compares — a file that sorts April before March is
     * not something anyone can check a printout against.
     *
     * @return array{key:string,dir:string,label:string}
     */
    private function resolveSort(Request $request): array
    {
        $keys = [
            'name'       => 'Employee Name',
            'position'   => 'Position / Designation',
            'department' => 'Department / Office',
            'type'       => 'Employment Type',
            'appointed'  => 'Date Appointed',
            'status'     => 'Account Status',
        ];

        $key = $this->param($request, 'sort');
        // Anything unrecognised -- and the case where the table has not been
        // sorted at all -- falls back to the order the Personnel page loads in
        // (newest record first), so an export taken without touching a heading
        // lists the roster the way it is being read.
        $key = isset($keys[$key]) ? $key : 'recent';
        $dir = strtolower($this->param($request, 'dir')) === 'desc' ? 'desc' : 'asc';

        if ($key === 'recent') {
            return ['key' => 'recent', 'dir' => 'desc', 'label' => 'Date Added (newest first)'];
        }

        return [
            'key' => $key,
            'dir' => $dir,
            'label' => $keys[$key] . ($dir === 'asc' ? ' (A → Z)' : ' (Z → A)'),
        ];
    }

    /**
     * @param  Collection<int,Employee> $employees
     * @return Collection<int,Employee>
     */
    private function sortEmployees(Collection $employees, array $sort): Collection
    {
        $value = match ($sort['key']) {
            'name'       => fn (Employee $e) => mb_strtolower($this->fullName($e)),
            'position'   => fn (Employee $e) => mb_strtolower((string) ($e->employmentDetail->designationRelation->title ?? '')),
            'department' => fn (Employee $e) => mb_strtolower((string) ($e->employmentDetail->departmentRelation->name ?? '')),
            'type'       => fn (Employee $e) => mb_strtolower((string) ($e->employmentDetail->employment_status ?? '')),
            // Blanks sort last whichever way the column is pointed: "no date on
            // file" is not the earliest appointment this office ever made.
            'appointed'  => fn (Employee $e) => ($e->employmentDetail->appointment_date ?? null)
                ? Carbon::parse($e->employmentDetail->appointment_date)->format('Y-m-d')
                : '9999-12-31',
            'status'     => fn (Employee $e) => mb_strtolower($this->accountStatus($e)),
            default      => fn (Employee $e) => (string) $e->created_at,
        };

        // The order `/admin/personnel` loads in: `created_at` descending, with
        // the id ascending inside a tie -- a seeded roster shares one
        // `created_at` to the second, and without the tie-break the file
        // reverses those rows against the screen. Two passes rather than one
        // compound key because the two run in opposite directions, and PHP's
        // sort has been stable since 8.0.
        if ($sort['key'] === 'recent') {
            return $employees->sortBy('id')->sortByDesc($value)->values();
        }

        return $employees->sortBy($value, SORT_NATURAL, $sort['dir'] === 'desc')->values();
    }

    /** Trimmed query value; a missing param and a blank one are one case. */
    private function param(Request $request, string $key): string
    {
        return trim((string) $request->query($key, ''));
    }

    /**
     * label => count, largest first.
     *
     * Sorted by count rather than by name because these blocks are read to
     * find the biggest office, not to look one up.
     *
     * @return array<string,int>
     */
    private function countsBy($items, callable $key): array
    {
        return $items->groupBy($key)
            ->map->count()
            ->sortDesc()
            ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
            ->all();
    }

    /** Hours the schedule's two blocks cover, as the DTR would read them. */
    private function dailyHours($schedule): string
    {
        if (!$schedule) {
            return '';
        }

        $minutes = 0;

        foreach ([['am_in', 'am_out'], ['pm_in', 'pm_out']] as [$in, $out]) {
            if ($schedule->{$in} && $schedule->{$out}) {
                $start = Carbon::parse($schedule->{$in});
                $end = Carbon::parse($schedule->{$out});
                if ($end->greaterThan($start)) {
                    // Carbon 3 returns a float here; a schedule is whole
                    // minutes, so the cast is exact rather than a rounding.
                    $minutes += (int) $start->diffInMinutes($end);
                }
            }
        }

        if ($minutes === 0) {
            return '';
        }

        return rtrim(rtrim(number_format($minutes / 60, 2, '.', ''), '0'), '.') . ' hrs';
    }

    /** The name as the Personnel table prints it, so a search matches on both. */
    private function fullName(Employee $employee): string
    {
        return trim(implode(' ', array_filter([
            $employee->first_name,
            $employee->middle_name ? mb_substr($employee->middle_name, 0, 1) . '.' : null,
            $employee->last_name,
            $employee->suffix,
        ])));
    }

    /** Mirrors the table: no user row reads as an inactive account, not a blank. */
    private function accountStatus(Employee $employee): string
    {
        return $employee->user ? ($employee->user->status ?: 'Inactive') : 'Inactive';
    }

    /**
     * The number HR would ring.
     *
     * An emergency contact is somebody else's number under somebody else's
     * name, so it is never used as the employee's own — a column headed
     * "Contact Number" holding a next-of-kin's mobile is worse than a blank
     * one. The old fallback to `contacts->first()` could return exactly that.
     */
    private function contactNumber(Employee $employee): string
    {
        $contacts = $employee->contacts;

        $contact = $contacts->firstWhere('type', 'mobile')
            ?? $contacts->firstWhere('type', 'landline');

        return $this->text($contact->number ?? '');
    }

    private function address(Employee $employee): string
    {
        $address = $employee->addresses->firstWhere('type', 'residential')
            ?? $employee->addresses->first();

        if (!$address) {
            return '';
        }

        return $this->text(implode(', ', array_filter([
            trim($address->house_no . ' ' . $address->street) ?: null,
            $address->barangay,
            $address->city,
            $address->province,
            $address->zip_code,
        ])));
    }

    private function lengthOfService($appointmentDate): string
    {
        if (!$appointmentDate) {
            return '';
        }

        $start = Carbon::parse($appointmentDate);
        if ($start->isFuture()) {
            return 'Not yet started';
        }

        // Carbon 3 returns a float from diffIn*(), so the whole months are
        // taken once and split -- diffInYears() straight into a string prints
        // "0.649 yr".
        $totalMonths = (int) floor($start->diffInMonths(now()));
        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' yr' . ($years > 1 ? 's' : '');
        }
        if ($months > 0) {
            $parts[] = $months . ' mo' . ($months > 1 ? 's' : '');
        }

        return $parts ? implode(', ', $parts) : 'Less than a month';
    }

    /** One date format across the whole file: MMM DD, YYYY. */
    private function date($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $e) {
            return $this->text($value);
        }
    }

    private function age($birthDate): string
    {
        if (!$birthDate) {
            return '';
        }

        try {
            $born = Carbon::parse($birthDate);
        } catch (\Throwable $e) {
            return '';
        }

        return $born->isFuture() ? '' : (string) $born->age;
    }

    /** Collapses whitespace, so a stray newline cannot split a CSV cell open. */
    private function text($value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }

    /** "MARRIED" and "married" are one fact; the sheet prints one of them. */
    private function titleCase($value): string
    {
        $value = $this->text($value);

        return $value === '' ? '' : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    private function describeRange(string $from, string $to): string
    {
        if ($from === '' && $to === '') {
            return 'All appointment dates';
        }
        if ($from !== '' && $to !== '') {
            return Carbon::parse($from)->format('F d, Y') . ' to ' . Carbon::parse($to)->format('F d, Y');
        }
        if ($from !== '') {
            return 'From ' . Carbon::parse($from)->format('F d, Y') . ' onwards';
        }

        return 'Up to ' . Carbon::parse($to)->format('F d, Y');
    }
}
