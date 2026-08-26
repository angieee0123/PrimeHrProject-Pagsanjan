<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Services\CsvReportWriter;
use Illuminate\Http\Request;

/**
 * "Export" on the Departments & Offices page — one method per tab.
 *
 * Both files used to be a bare header row and a grid of values: no title, no
 * municipality, no note of which filters produced them, and no totals. They
 * now carry the same `CsvReportWriter` letterhead as the Personnel, Detailed
 * Time Record and Leave & Benefits exports.
 *
 * The two tabs answer different questions and the files say so: the
 * Departments tab exports the office directory with its headcount, the
 * Designations tab exports the plantilla of positions with its salary grades.
 *
 * Neither file carries personal data — an office name and a plantilla item
 * name nobody — so `notes()` is told to leave the RA 10173 warning off. A
 * privacy warning printed on a file that carries no personal data is how a
 * real one stops being read.
 */
class DepartmentExportController extends Controller
{
    /** Departments & Offices tab → the office directory. */
    public function departments(Request $request)
    {
        $status = trim((string) $request->get('status'));
        $search = trim((string) $request->get('search'));

        try {
            // The same derived headcount the page shows, so a spreadsheet
            // pulled from here cannot disagree with the table it came from.
            $departments = Department::withCount([
                    'employmentDetails as personnel_count',
                    'designations as designation_count',
                ])
                ->orderBy('name')
                ->get()
                ->filter(function (Department $dept) use ($status, $search) {
                    if ($status !== '' && $dept->status !== $status) {
                        return false;
                    }

                    if ($search !== '') {
                        $haystack = mb_strtolower(implode(' ', [
                            $dept->name, $dept->code, $dept->head,
                        ]));
                        if (!str_contains($haystack, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            $fileName = 'Departments_And_Offices_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $departments, $status, $search
            ) {
                $csv->letterhead(
                    'Departments & Offices — Organisational Directory',
                    'Human Resource Management Office · PRIME HRIS',
                    'Directory as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Status:'      => $status !== '' ? $status : 'All Status',
                    'Search Term:' => $search !== '' ? $search : 'None',
                ], $departments->count());

                $csv->columns([
                    'No.', 'Code', 'Department / Office', 'Department Head',
                    'Personnel Count', 'Positions Defined', 'Status', 'Description',
                ]);

                foreach ($departments as $index => $dept) {
                    $csv->row([
                        $index + 1,
                        $dept->code,
                        $dept->name,
                        $dept->head,
                        $dept->personnel_count,
                        $dept->designation_count,
                        $dept->status,
                        $dept->description ?? '',
                    ]);
                }

                if ($departments->isEmpty()) {
                    $csv->emptyNotice('No departments matched the filters above.');
                }

                $largest = $departments->sortByDesc('personnel_count')->first();

                $csv->summary('Summary', array_filter([
                    'Total Departments / Offices:' => $departments->count(),
                    'Active:'                      => $departments->where('status', 'Active')->count(),
                    'Inactive:'                    => $departments->where('status', '!=', 'Active')->count(),
                    'Total Personnel Assigned:'    => $departments->sum('personnel_count'),
                    'Total Positions Defined:'     => $departments->sum('designation_count'),
                    'Largest Office:'              => $largest && $largest->personnel_count > 0
                        ? $largest->name . ' (' . $largest->personnel_count . ')'
                        : null,
                ], fn ($value) => $value !== null));

                $csv->summary('Personnel per Department / Office',
                    $departments->sortByDesc('personnel_count')
                        ->mapWithKeys(fn (Department $d) => [$d->name . ':' => $d->personnel_count])
                        ->all()
                );

                $csv->notes([
                    'Personnel Count is derived from active employment records, not stored on the department.',
                    'Positions Defined counts the designations registered under each office.',
                ], containsPersonalData: false);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.departments')->with('export_error', $e->getMessage());
        }
    }

    /** Designations tab → the plantilla of positions. */
    public function designations(Request $request)
    {
        $departmentId = trim((string) $request->get('department_id'));
        $employmentType = trim((string) $request->get('employment_type'));
        $search = trim((string) $request->get('search'));

        try {
            $designations = Designation::with('department')
                ->withCount('employmentDetails as employee_count')
                ->orderBy('title')
                ->get()
                ->filter(function (Designation $d) use ($departmentId, $employmentType, $search) {
                    if ($departmentId !== '' && (string) $d->department_id !== $departmentId) {
                        return false;
                    }

                    if ($employmentType !== '' && $d->employment_type !== $employmentType) {
                        return false;
                    }

                    if ($search !== '') {
                        $haystack = mb_strtolower(implode(' ', [
                            $d->title,
                            $d->department->name ?? '',
                            $d->department->code ?? '',
                            $d->employment_type ?? '',
                            (string) $d->salary_grade,
                        ]));
                        if (!str_contains($haystack, mb_strtolower($search))) {
                            return false;
                        }
                    }

                    return true;
                })
                ->values();

            $departmentName = $departmentId !== ''
                ? (Department::find($departmentId)->name ?? 'Unknown department')
                : 'All Departments';

            $fileName = 'Designations_' . now()->format('M_d_Y') . '.csv';

            return CsvReportWriter::download($fileName, function (CsvReportWriter $csv) use (
                $designations, $departmentName, $employmentType, $search
            ) {
                $csv->letterhead(
                    'Designations — Plantilla of Positions',
                    'Human Resource Management Office · PRIME HRIS',
                    'Positions on file as of ' . now()->format('F d, Y')
                );

                $csv->parameters([
                    'Department / Office:' => $departmentName,
                    'Employment Type:'     => $employmentType !== '' ? $employmentType : 'All Employment Types',
                    'Search Term:'         => $search !== '' ? $search : 'None',
                ], $designations->count());

                $csv->columns([
                    'No.', 'Designation Title', 'Department / Office', 'Department Code',
                    'Salary Grade', 'Monthly Rate (PHP)', 'Employment Type',
                    'Employees Holding', 'Description',
                ]);

                foreach ($designations as $index => $d) {
                    $csv->row([
                        $index + 1,
                        $d->title,
                        $d->department->name ?? 'Unassigned',
                        $d->department->code ?? '',
                        $d->salary_grade ?? '',
                        // Written as a plain number so a spreadsheet can total
                        // the column; the header carries the currency.
                        $d->monthly_rate !== null ? number_format((float) $d->monthly_rate, 2, '.', '') : '',
                        $d->employment_type ?? '',
                        $d->employee_count,
                        $d->description ?? '',
                    ]);
                }

                if ($designations->isEmpty()) {
                    $csv->emptyNotice('No designations matched the filters above.');
                }

                $rated = $designations->filter(fn (Designation $d) => $d->monthly_rate !== null);

                $csv->summary('Summary', array_filter([
                    'Total Designations:'        => $designations->count(),
                    'Positions Filled:'          => $designations->where('employee_count', '>', 0)->count(),
                    'Positions Vacant:'          => $designations->where('employee_count', 0)->count(),
                    'Employees Covered:'         => $designations->sum('employee_count'),
                    'Lowest Monthly Rate (PHP):' => $rated->isNotEmpty()
                        ? number_format((float) $rated->min('monthly_rate'), 2)
                        : null,
                    'Highest Monthly Rate (PHP):' => $rated->isNotEmpty()
                        ? number_format((float) $rated->max('monthly_rate'), 2)
                        : null,
                ], fn ($value) => $value !== null));

                $csv->summary('Breakdown by Employment Type',
                    $designations->groupBy(fn (Designation $d) => $d->employment_type ?: 'Unspecified')
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->summary('Breakdown by Department / Office',
                    $designations->groupBy(fn (Designation $d) => $d->department->name ?? 'Unassigned')
                        ->map->count()
                        ->sortDesc()
                        ->mapWithKeys(fn ($count, $label) => [$label . ':' => $count])
                        ->all()
                );

                $csv->notes([
                    'Employees Holding counts the employment records currently assigned to each position.',
                    'Monthly Rate is the rate registered on the position, not an individual employee\'s salary.',
                ], containsPersonalData: false);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('admin.departments')->with('export_error', $e->getMessage());
        }
    }
}
