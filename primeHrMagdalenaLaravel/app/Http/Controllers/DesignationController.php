<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;

class DesignationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'department_id'   => ['required', 'exists:departments,id'],
            'salary_grade'    => ['nullable', 'string', 'max:50'],
            'monthly_rate'    => ['nullable', 'numeric', 'min:0'],
            'employment_type' => ['nullable', 'in:Permanent,Temporary,Coterminous,Casual,Contractual,Job Order'],
            'description'     => ['nullable', 'string'],
        ]);

        Designation::create($data);

        return redirect()->route('admin.departments')->with('success', 'Designation added successfully.');
    }

    public function template()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=designations_template.csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['title', 'department_code', 'salary_grade', 'monthly_rate', 'employment_type', 'description']);
            fputcsv($file, ['Municipal Health Officer', 'MHO', 'SG-24', '35000', 'Permanent', 'Head of Municipal Health Office']);
            fputcsv($file, ['Administrative Assistant', 'OM', 'SG-8', '18000', 'Casual', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $file     = fopen($request->file('csv_file')->getRealPath(), 'r');
        if ($file === false) {
            return redirect()->route('admin.departments')->with('error', 'Could not read the uploaded file.');
        }
        $header   = fgetcsv($file);
        if ($header === false || count(array_filter($header, fn ($h) => trim((string) $h) !== '')) === 0) {
            fclose($file);
            return redirect()->route('admin.departments')->with('error', 'CSV file is empty or missing a header row.');
        }
        // Strip UTF-8 BOM from first header cell (Excel-saved CSVs).
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");

        $imported = 0;
        $skipped  = [];

        $allowedTypes = ['Permanent', 'Temporary', 'Coterminous', 'Casual', 'Contractual', 'Job Order'];

        while (($row = fgetcsv($file)) !== false) {
            // Skip fully-blank rows (trailing newlines, Excel gaps).
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($row) < 2) { $skipped[] = '(invalid row — missing columns)'; continue; }

            [$title, $department_code, $salary_grade, $monthly_rate, $employment_type, $description] = array_pad($row, 6, null);
            $title           = trim($title ?? '');
            $department_code = strtoupper(trim($department_code ?? ''));
            $employment_type = trim($employment_type ?? '');

            if (!$title || !$department_code) { $skipped[] = $title ?: '(empty title)'; continue; }

            // Normalise employment_type — invalid values become null rather than a DB surprise.
            if ($employment_type !== '' && !in_array($employment_type, $allowedTypes, true)) {
                $employment_type = null;
            }
            if ($employment_type === '') {
                $employment_type = null;
            }

            $department = Department::where('code', $department_code)->first();
            if (!$department) { $skipped[] = "{$title} — department code '{$department_code}' not found"; continue; }

            $monthly_rate_clean = $monthly_rate ? (float) preg_replace('/[^0-9.]/', '', $monthly_rate) : null;

            $exists = Designation::where('title', $title)
                ->where('department_id', $department->id)
                ->where('monthly_rate', $monthly_rate_clean)
                ->exists();

            if ($exists) { $skipped[] = "{$title} ({$department_code}) ₱" . number_format((float) $monthly_rate_clean, 2) . ' — already exists'; continue; }

            Designation::create([
                'title'           => $title,
                'department_id'   => $department->id,
                'salary_grade'    => $salary_grade ? trim($salary_grade) : null,
                'monthly_rate'    => $monthly_rate_clean ?: null,
                'employment_type' => $employment_type,
                'description'     => $description ? trim($description) : null,
            ]);
            $imported++;
        }

        fclose($file);

        return redirect()->route('admin.departments')
            ->with('import_imported', $imported)
            ->with('import_skipped', $skipped)
            ->with('import_type', 'designation');
    }
}
