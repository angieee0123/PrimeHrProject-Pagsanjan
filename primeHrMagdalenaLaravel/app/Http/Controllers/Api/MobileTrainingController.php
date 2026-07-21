<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobileTrainingController extends Controller
{
    private const GOAL_HOURS = 40;

    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $trainings = Training::where('employee_id', $employee->id)
            ->orderByDesc('date_from')
            ->get();

        $verified = $trainings->where('status', 'verified');

        $breakdown = ['leadership' => 0, 'technical' => 0, 'core' => 0];
        foreach ($verified as $training) {
            $cat = $training->ldCategory();
            $breakdown[$cat] = ($breakdown[$cat] ?? 0) + (int) $training->hours;
        }

        $totalHours = (int) $verified->sum('hours');

        return response()->json([
            'success' => true,
            'data' => [
                'fiscal_year' => (int) date('Y'),
                'goal_hours' => self::GOAL_HOURS,
                'stats' => [
                    'total_hours' => $totalHours,
                    'verified' => $verified->count(),
                    'pending' => $trainings->where('status', 'pending')->count(),
                    'rejected' => $trainings->where('status', 'rejected')->count(),
                    'goal_progress' => min(100, (int) round(($totalHours / self::GOAL_HOURS) * 100)),
                ],
                'breakdown' => $breakdown,
                'trainings' => $trainings->map(fn ($t) => $this->formatTraining($t))->values()->all(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$training) {
            return response()->json([
                'success' => false,
                'message' => 'Training record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatTraining($training),
        ]);
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'conducted_by' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'hours' => 'required|integer|min:1|max:999',
            'position_type' => 'required|in:Managerial,Supervisory,Technical,Clerical',
            'venue' => 'nullable|string|max:255',
            'cert_no' => 'nullable|string|max:100',
            'ref_doc_no' => 'required|string|max:100',
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('certificate')->store('training_certificates', 'public');

        $training = Training::create([
            'employee_id' => $employee->id,
            'title' => $data['title'],
            'conducted_by' => $data['conducted_by'],
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'hours' => $data['hours'],
            'position_type' => $data['position_type'],
            'venue' => $data['venue'] ?? null,
            'cert_no' => $data['cert_no'] ?? null,
            'ref_doc_no' => $data['ref_doc_no'],
            'certificate_path' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Training record submitted for HR verification',
            'data' => $this->formatTraining($training),
        ], 201);
    }

    public function destroy(int $id)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->first();

        if (!$training) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending training records can be deleted',
            ], 422);
        }

        if ($training->certificate_path) {
            Storage::disk('public')->delete($training->certificate_path);
        }

        $training->delete();

        return response()->json([
            'success' => true,
            'message' => 'Training record deleted successfully',
        ]);
    }

    public function certificate(int $id)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $training = Training::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$training || !$training->certificate_path) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        if (!Storage::disk('public')->exists($training->certificate_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate file not found',
            ], 404);
        }

        return response()->file(storage_path('app/public/' . $training->certificate_path));
    }

    private function formatTraining(Training $training): array
    {
        $ldCategory = $training->ldCategory();

        return [
            'id' => $training->id,
            'title' => $training->title,
            'conducted_by' => $training->conducted_by,
            'date_from' => $training->date_from instanceof Carbon
                ? $training->date_from->format('Y-m-d')
                : (string) $training->date_from,
            'date_to' => $training->date_to instanceof Carbon
                ? $training->date_to->format('Y-m-d')
                : (string) $training->date_to,
            'hours' => (int) $training->hours,
            'position_type' => $training->position_type,
            'venue' => $training->venue,
            'cert_no' => $training->cert_no,
            'ref_doc_no' => $training->ref_doc_no,
            'status' => $training->status,
            'status_label' => ucfirst($training->status),
            'ld_category' => $ldCategory,
            'ld_category_label' => $this->categoryLabel($ldCategory),
            'rejected_reason' => $training->rejected_reason,
            'verified_at' => $training->verified_at?->toIso8601String(),
            'certificate_url' => $training->certificate_path
                ? url('storage/' . $training->certificate_path)
                : null,
            'counts_toward_goal' => $training->status === 'verified',
            'created_at' => $training->created_at?->toIso8601String(),
        ];
    }

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            'leadership' => 'Leadership',
            'technical' => 'Technical',
            default => 'Core / Foundation',
        };
    }
}
