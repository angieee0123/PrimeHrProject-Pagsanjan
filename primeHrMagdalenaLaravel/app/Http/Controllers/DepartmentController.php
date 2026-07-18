<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments  = Department::orderBy('name')->get();
        $designations = Designation::with('department')->orderBy('title')->get();
        return view('admin.departments.adminDepartments', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name'            => ['required', 'string', 'max:255'],
            'head'            => ['required', 'string', 'max:255'],
            'personnel_count' => ['nullable', 'integer', 'min:0'],
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

    public function export()
    {
        try {
            $departments = Department::orderBy('name')->get();

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=departments_' . now()->format('Y-m-d') . '.csv',
            ];

            $callback = function () use ($departments) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
                fputcsv($file, ['Code', 'Department / Office', 'Department Head', 'Personnel Count', 'Status', 'Description']);
                foreach ($departments as $dept) {
                    fputcsv($file, [
                        $dept->code,
                        $dept->name,
                        $dept->head,
                        $dept->personnel_count,
                        $dept->status,
                        $dept->description ?? '',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->route('admin.departments')->with('export_error', $e->getMessage());
        }
    }

    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=departments_template.csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['code', 'name', 'head', 'personnel_count', 'status', 'description']);
            fputcsv($file, ['MHO', 'Municipal Health Office', 'Municipal Health Officer', '38', 'Active', 'Handles public health services']);
            fputcsv($file, ['MEO', 'Office of the Mun. Engineer', 'Municipal Engineer', '22', 'Active', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);

        $file = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($file);

        $imported = 0;
        $skipped  = [];

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 5) { $skipped[] = '(invalid row — missing columns)'; continue; }

            [$code, $name, $head, $personnel_count, $status, $description] = array_pad($row, 6, null);
            $code = strtoupper(trim($code ?? ''));

            if (!$code || !$name || !$head) { $skipped[] = $code ?: '(empty code)'; continue; }
            if (!in_array($status, ['Active', 'Inactive'])) $status = 'Active';

            if (Department::where('code', $code)->exists()) {
                $skipped[] = "{$code} — " . trim($name) . ' (already exists)';
                continue;
            }

            Department::create([
                'code'            => $code,
                'name'            => trim($name),
                'head'            => trim($head),
                'personnel_count' => (int) ($personnel_count ?? 0),
                'status'          => $status,
                'description'     => $description ? trim($description) : null,
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
