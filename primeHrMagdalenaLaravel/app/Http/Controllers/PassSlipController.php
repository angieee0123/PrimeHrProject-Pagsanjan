<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PassSlip;
use App\Models\User;
use App\Services\PassSlipFormDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PassSlipController extends Controller
{
    /**
     * Pass slip list for the logged-in permanent employee.
     */
    public function indexPermanent()
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;

        if (!$employee) {
            $passSlips = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1);
            return view('employee.passSlip.employeePassSlip', compact('passSlips'));
        }

        $employee->load('employmentDetail.designationRelation', 'employmentDetail.departmentRelation');

        $perPage = request('per_page', 10);
        $passSlips = PassSlip::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('employee.passSlip.employeePassSlip', compact('employee', 'passSlips'));
    }

    /**
     * File a new pass slip (permanent employee).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return back()->with('error', 'No employee record found.');
        }

        $data = $request->validate([
            'type' => 'required|in:official_activity,personal_reason',
            'purpose_category' => 'required|in:coordinate_with,meeting_conference,secure_documents,follow_up,personal_matter',
            'destination' => 'nullable|string|max:255',
            'reason' => 'required|string|max:300',
            'date' => 'required|date',
            'time_out' => 'required',
            'time_in' => 'nullable',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('pass_slips', 'public');
        }

        $employee->loadMissing('employmentDetail.departmentRelation');

        PassSlip::create([
            'employee_id' => $employee->id,
            'type' => $data['type'],
            'purpose_category' => $data['purpose_category'],
            'destination' => $data['destination'] ?? null,
            'recommended_by_name' => $employee->employmentDetail?->departmentRelation?->head,
            'reason' => $data['reason'],
            'date' => $data['date'],
            'time_out' => $data['time_out'],
            'time_in' => $data['time_in'] ?? null,
            'attachment' => $attachmentPath,
            'filed_by' => $user->id,
            'status' => 'pending',
        ]);

        return redirect()->route('employee.passslip')->with('success', 'Pass slip submitted successfully.');
    }

    /**
     * JSON detail for the employee's own pass slip (used by the view modal).
     */
    public function show($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            abort(403, 'No employee record found.');
        }

        $passSlip = PassSlip::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with('approver')
            ->firstOrFail();

        return response()->json($passSlip);
    }

    /**
     * Cancel a pending pass slip (employee-initiated).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $employee = $user instanceof User ? $user->employee : null;
        if (!$employee) {
            return redirect()->route('employee.passslip')->with('error', 'No employee record found.');
        }

        $passSlip = PassSlip::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($passSlip->attachment) {
            Storage::disk('public')->delete($passSlip->attachment);
        }

        $passSlip->delete();

        return redirect()->route('employee.passslip')->with('success', 'Pass slip cancelled successfully.');
    }

    /**
     * Pending/approved/disapproved pass slip buckets for the admin list.
     */
    public function indexAdmin(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $pendingSlips = PassSlip::with(['employee.employmentDetail.departmentRelation'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'pending_page');

        $approvedSlips = PassSlip::with(['employee.employmentDetail.departmentRelation', 'approver'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->paginate($perPage, ['*'], 'approved_page');

        $disapprovedSlips = PassSlip::with(['employee.employmentDetail.departmentRelation', 'approver'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'disapproved_page');

        $departments = Department::where('status', 'Active')->orderBy('name')->get();

        return view('admin.passSlip.passSlip', compact('pendingSlips', 'approvedSlips', 'disapprovedSlips', 'departments'));
    }

    public function approve($id)
    {
        $passSlip = PassSlip::findOrFail($id);

        $passSlip->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => null,
        ]);

        return redirect()->route('admin.passslip', ['tab' => 'approved'])
            ->with('success', 'Pass slip approved successfully.');
    }

    public function disapprove(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $passSlip = PassSlip::findOrFail($id);

        $passSlip->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $request->reason,
        ]);

        return redirect()->route('admin.passslip', ['tab' => 'disapproved'])
            ->with('success', 'Pass slip disapproved.');
    }

    /**
     * JSON detail for the admin view modal.
     */
    public function viewAdmin($id)
    {
        $passSlip = PassSlip::with(['employee.employmentDetail.departmentRelation', 'approver'])
            ->findOrFail($id);

        return response()->json($passSlip);
    }

    /**
     * On-screen HTML preview of the official Pass Slip form.
     */
    public function viewForm($id, PassSlipFormDataService $formService)
    {
        try {
            return view('admin.passSlip.pass-slip-form-view', $formService->build($id));
        } catch (\Exception $e) {
            \Log::error('Failed to load pass slip form: ' . $e->getMessage());
            abort(404, 'Pass slip not found.');
        }
    }

    /**
     * Stream (print-form) or download (download-form) the Pass Slip PDF.
     */
    public function generateForm($id, PassSlipFormDataService $formService)
    {
        try {
            $data = $formService->build($id);
            $slip = $data['slip'];

            $pdf = Pdf::loadView('admin.passSlip.pass-slip-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('margin-top', 8)
                ->setOption('margin-bottom', 8)
                ->setOption('margin-left', 8)
                ->setOption('margin-right', 8);

            $filename = 'Pass-Slip-' . $slip->slip_number . '.pdf';

            if (request()->routeIs('admin.passslip.print-form')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Failed to generate pass slip form: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate pass slip form: ' . $e->getMessage(),
            ], 500);
        }
    }
}
