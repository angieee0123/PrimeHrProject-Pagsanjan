<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::with(['employee.employmentDetail.departmentRelation'])
            ->orderByRaw("FIELD(status, 'pending', 'rejected', 'verified')")
            ->orderBy('created_at', 'desc')
            ->get();
        $stats = [
            'pending'  => $trainings->where('status', 'pending')->count(),
            'verified' => $trainings->where('status', 'verified')->count(),
            'rejected' => $trainings->where('status', 'rejected')->count(),
            'total'    => $trainings->count(),
        ];
        return view('admin.training.adminTraining', compact('trainings', 'stats'));
    }

    public function approve($id)
    {
        $training = Training::findOrFail($id);
        $training->update(['status' => 'verified', 'verified_by' => Auth::id(), 'verified_at' => now(), 'rejected_reason' => null]);
        return redirect()->route('admin.training')->with('success', 'Training approved for ' . $training->employee->first_name . ' ' . $training->employee->last_name . '.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $training = Training::findOrFail($id);
        $training->update(['status' => 'rejected', 'rejected_reason' => $request->reason, 'verified_by' => null, 'verified_at' => null]);
        return redirect()->route('admin.training')->with('success', 'Submission rejected.');
    }

    public function export()
    {
        $trainings = Training::with(['employee.employmentDetail.departmentRelation'])
            ->orderBy('status')->orderBy('created_at', 'desc')->get();
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=training_verification_' . date('Y-m-d') . '.csv'];
        $callback = function () use ($trainings) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Employee ID','Employee Name','Department','Training Title','Date From','Date To','Hours','Position Type','Conducted By','Ref Doc No','Cert No','Status','Submitted','Verified At','Rejected Reason']);
            foreach ($trainings as $t) {
                $emp = $t->employee;
                $dept = $emp->employmentDetail->departmentRelation->name ?? 'N/A';
                fputcsv($file, [$emp->employee_id, $emp->first_name.' '.$emp->last_name, $dept, $t->title,
                    $t->date_from?$t->date_from->format('m/d/Y'):'', $t->date_to?$t->date_to->format('m/d/Y'):'',
                    $t->hours, $t->position_type, $t->conducted_by, $t->ref_doc_no, $t->cert_no??'',
                    $t->status, $t->created_at?$t->created_at->format('m/d/Y'):'',
                    $t->verified_at?$t->verified_at->format('m/d/Y'):'', $t->rejected_reason??'']);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function certificate($id)
    {
        $training = Training::findOrFail($id);
        if (!$training->certificate_path || !Storage::disk('public')->exists($training->certificate_path)) abort(404);
        return response()->file(storage_path('app/public/' . $training->certificate_path));
    }
}
