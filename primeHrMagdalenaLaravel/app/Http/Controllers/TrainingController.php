<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Training;
use App\Services\NotificationService;

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
        NotificationService::trainingVerified($training, 'verified');
        $emp = $training->employee;
        $empName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
        $empName = $empName !== '' ? $empName : 'Unknown Employee';
        return redirect()->route('admin.training')->with('success', 'Training approved for ' . $empName . '.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $training = Training::findOrFail($id);
        $training->update(['status' => 'rejected', 'rejected_reason' => $request->reason, 'verified_by' => null, 'verified_at' => null]);
        NotificationService::trainingVerified($training, 'rejected');
        return redirect()->route('admin.training')->with('success', 'Submission rejected.');
    }

    public function certificate($id)
    {
        $training = Training::findOrFail($id);
        if (!$training->certificate_path || !Storage::disk('public')->exists($training->certificate_path)) abort(404);
        return response()->file(storage_path('app/public/' . $training->certificate_path));
    }
}
