<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;

class DepartmentController extends Controller
{
    /**
     * Headcount is counted, never stored. `personnel_count` used to be a
     * column somebody typed into the Add Department form, so it drifted the
     * moment anyone was hired, transferred or separated — by the time it was
     * removed all 26 offices claimed 0 people against 14 real assignments.
     *
     * The count is aliased back to `personnel_count` so the view, the stat
     * cards, the JS sort comparator and the View modal keep the name they
     * already use. `employment_details` holds exactly one row per employee,
     * so this is a headcount and not a count of appointments.
     */
    private function withHeadcount()
    {
        return Department::withCount(['employmentDetails as personnel_count']);
    }

    public function index()
    {
        $departments  = $this->withHeadcount()->orderBy('name')->get();
        $designations = Designation::with('department')->orderBy('title')->get();
        return view('admin.departments.adminDepartments', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name'            => ['required', 'string', 'max:255'],
            'head'            => ['required', 'string', 'max:255'],
            'status'          => ['required', 'in:Active,Inactive'],
            'description'     => ['nullable', 'string'],
        ]);

        Department::create($data);

        return redirect()->route('admin.departments')->with('success', 'Department registered successfully.');
    }

    public function designationsForDepartment($id)
    {
        $designations = Designation::where('department_id', $id)
            ->orderBy('title')
            ->get(['id', 'title', 'employment_type', 'salary_grade']);
        return response()->json($designations);
    }

    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=departments_template.csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // No personnel_count column: headcount is counted from
            // employment_details, so there is nothing here for an importer to
            // state and nothing that could contradict the assignments.
            fputcsv($file, ['code', 'name', 'head', 'status', 'description']);
            fputcsv($file, ['MHO', 'Municipal Health Office', 'Municipal Health Officer', 'Active', 'Handles public health services']);
            fputcsv($file, ['MEO', 'Office of the Mun. Engineer', 'Municipal Engineer', 'Active', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');
        if ($file === false) {
            return redirect()->route('admin.departments')->with('error', 'Could not read the uploaded file.');
        }
        $header = fgetcsv($file);

        if ($header === false || count(array_filter($header, fn ($h) => trim((string) $h) !== '')) === 0) {
            fclose($file);
            return redirect()->route('admin.departments')->with('error', 'CSV file is empty or missing a header row.');
        }

        // Strip UTF-8 BOM from first header cell (Excel-saved CSVs).
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");

        // Files produced by the old template still carry a personnel_count
        // column in fourth place. Unpacked positionally that would land the
        // headcount in `status` and shunt the real status into `description`
        // -- a silent rewrite of two fields, on a file the municipality
        // already has on disk. The header names the columns, so use it: find
        // that column and drop it from every row.
        $legacyCountIndex = is_array($header)
            ? array_search('personnel_count', array_map(
                fn ($h) => strtolower(trim((string) $h)), $header), true)
            : false;

        $imported = 0;
        $skipped  = [];

        while (($row = fgetcsv($file)) !== false) {
            // Skip fully-blank rows (trailing newlines, Excel gaps).
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if ($legacyCountIndex !== false && count($row) > $legacyCountIndex) {
                array_splice($row, $legacyCountIndex, 1);
            }

            if (count($row) < 4) { $skipped[] = '(invalid row — missing columns)'; continue; }

            [$code, $name, $head, $status, $description] = array_pad($row, 5, null);
            $code = strtoupper(trim($code ?? ''));
            $name = trim($name ?? '');
            $head = trim($head ?? '');
            $status = trim($status ?? '');

            if (!$code || !$name || !$head) { $skipped[] = $code ?: '(empty code)'; continue; }
            if (!in_array($status, ['Active', 'Inactive'], true)) $status = 'Active';

            if (Department::where('code', $code)->exists()) {
                $skipped[] = "{$code} — " . trim($name) . ' (already exists)';
                continue;
            }

            Department::create([
                'code'        => $code,
                'name'        => trim($name),
                'head'        => trim($head),
                'status'      => $status,
                'description' => $description ? trim($description) : null,
            ]);
            $imported++;
        }

        fclose($file);

        return redirect()->route('admin.departments')
            ->with('import_imported', $imported)
            ->with('import_skipped', $skipped)
            ->with('import_type', 'department');
    }
}
