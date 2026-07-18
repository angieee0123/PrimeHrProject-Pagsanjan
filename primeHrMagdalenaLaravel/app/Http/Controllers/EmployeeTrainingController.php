<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Training;
use App\Models\User;

class EmployeeTrainingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            return view('employee.training.employeeTraining', [
                'trainings'   => collect(),
                'stats'       => ['total_hours' => 0, 'verified' => 0, 'pending' => 0, 'rejected' => 0],
                'breakdown'   => ['leadership' => 0, 'technical' => 0, 'core' => 0],
                'goal_hours'  => 40,
                'fiscal_year' => date('Y'),
            ]);
        }

        // Load employee relationships for topbar
        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $trainings = Training::where('employee_id', $employee->id)
            ->orderBy('date_from', 'desc')
            ->get();

        $verified = $trainings->where('status', 'verified');
        $stats = [
            'total_hours' => $verified->sum('hours'),
            'verified'    => $verified->count(),
            'pending'     => $trainings->where('status', 'pending')->count(),
            'rejected'    => $trainings->where('status', 'rejected')->count(),
        ];

        $breakdown = ['leadership' => 0, 'technical' => 0, 'core' => 0];
        foreach ($verified as $training) {
            $cat = $training->ldCategory();
            $breakdown[$cat] = ($breakdown[$cat] ?? 0) + (int) $training->hours;
        }

        return view('employee.training.employeeTraining', compact('employee', 'trainings', 'stats', 'breakdown'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return back()->with('error', 'No employee record found.');
        }

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'conducted_by'  => 'required|string|max:255',
            'date_from'     => 'required|date',
            'date_to'       => 'required|date|after_or_equal:date_from',
            'hours'         => 'required|integer|min:1|max:999',
            'position_type' => 'required|in:Managerial,Supervisory,Technical,Clerical',
            'venue'         => 'nullable|string|max:255',
            'cert_no'       => 'nullable|string|max:100',
            'ref_doc_no'    => 'required|string|max:100',
            'certificate'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('certificate')->store('training_certificates', 'public');

        Training::create([
            'employee_id'      => $employee->id,
            'title'            => $data['title'],
            'conducted_by'     => $data['conducted_by'],
            'date_from'        => $data['date_from'],
            'date_to'          => $data['date_to'],
            'hours'            => $data['hours'],
            'position_type'    => $data['position_type'],
            'venue'            => $data['venue'] ?? null,
            'cert_no'          => $data['cert_no'] ?? null,
            'ref_doc_no'       => $data['ref_doc_no'],
            'certificate_path' => $path,
            'status'           => 'pending',
        ]);

        return redirect()->route('employee.training')
            ->with('success', 'Training record submitted for HR verification.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.training')->with('error', 'No employee record found.');
        }
        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($training->certificate_path) {
            Storage::disk('public')->delete($training->certificate_path);
        }
        $training->delete();

        return redirect()->route('employee.training')->with('success', 'Training record deleted.');
    }

    public function export()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.training')->with('error', 'No employee record found.');
        }
        $trainings = Training::where('employee_id', $employee->id)
            ->where('status', 'verified')
            ->orderBy('date_from', 'desc')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=training_pds_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($trainings) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Title of Seminar/Training', 'Date From', 'Date To', 'No. of Hours', 'Type of Position', 'Conducted/Sponsored By', 'Venue', 'Cert No', 'Ref Doc No']);
            foreach ($trainings as $t) {
                fputcsv($file, [
                    $t->title,
                    $t->date_from ? $t->date_from->format('m/d/Y') : '',
                    $t->date_to   ? $t->date_to->format('m/d/Y')   : '',
                    $t->hours,
                    $t->position_type,
                    $t->conducted_by,
                    $t->venue    ?? '',
                    $t->cert_no  ?? '',
                    $t->ref_doc_no ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function certificate($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            abort(403, 'No employee record found.');
        }
        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if (!$training->certificate_path || !Storage::disk('public')->exists($training->certificate_path)) {
            abort(404, 'Certificate not found.');
        }

        return response()->file(storage_path('app/public/' . $training->certificate_path));
    }
}
