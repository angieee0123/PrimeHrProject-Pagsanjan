<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TravelOrder;
use App\Models\Department;
use App\Services\NotificationService;
use App\Services\TravelOrderFormDataService;
use Barryvdh\DomPDF\Facade\Pdf;

class TravelOrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $pendingOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'companions.employee'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'pending_page');

        $approvedOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->paginate($perPage, ['*'], 'approved_page');

        $disapprovedOrders = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'disapproved_page');

        $departments = Department::where('status', 'Active')->orderBy('name')->get();

        return view('admin.travelOrder.travelOrder', compact('pendingOrders', 'approvedOrders', 'disapprovedOrders', 'departments'));
    }

    public function approve($id)
    {
        $travelOrder = TravelOrder::findOrFail($id);

        $travelOrder->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => null,
        ]);

        $travelOrder->logHistory('approved', 'Travel order approved by HR/Admin.');
        NotificationService::travelOrderStatusChanged($travelOrder, 'approved');

        return redirect()->route('admin.travelorder', ['tab' => 'approved'])
            ->with('success', 'Travel order approved successfully.');
    }

    public function disapprove(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $travelOrder = TravelOrder::findOrFail($id);

        $travelOrder->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $request->reason,
        ]);

        $travelOrder->logHistory('disapproved', 'Travel order disapproved by HR/Admin. Reason: ' . $request->reason);
        NotificationService::travelOrderStatusChanged($travelOrder, 'disapproved');

        return redirect()->route('admin.travelorder', ['tab' => 'disapproved'])
            ->with('success', 'Travel order disapproved.');
    }

    public function show($id)
    {
        $travelOrder = TravelOrder::with(['employee.employmentDetail.departmentRelation', 'approver', 'companions.employee', 'histories.performer.employee'])
            ->findOrFail($id);

        return response()->json($travelOrder);
    }

    /**
     * On-screen HTML preview of the official Travel Order form.
     *
     * Restricted to approved orders, and not merely because that is the only
     * tab offering the action: the sheet is an *authority to travel* that names
     * two signatories and grants a per diem. Printing one for an order nobody
     * has approved yet would hand out a document asserting a decision that has
     * not been made.
     */
    public function viewForm($id, TravelOrderFormDataService $formService)
    {
        try {
            $data = $formService->build($id);

            abort_unless($data['isApproved'], 404, 'The Travel Order form is available for approved travel orders only.');

            return view('admin.travelOrder.travel-order-form-view', $data);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // An abort() above is the answer, not a failure to be swallowed by
            // the catch below and reported as a missing travel order.
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to load travel order form: ' . $e->getMessage());
            abort(404, 'Travel order not found.');
        }
    }

    /**
     * Stream (print-form) or download (download-form) the Travel Order PDF.
     */
    public function generateForm($id, TravelOrderFormDataService $formService)
    {
        try {
            $data = $formService->build($id);

            abort_unless($data['isApproved'], 404, 'The Travel Order form is available for approved travel orders only.');

            // Philippine long bond, 8.5 x 13 in — the sheet the office's own
            // template is set on, and the proportions the layout is built to.
            // Margins come from the view's @page rule, which dompdf honours.
            $pdf = Pdf::loadView('admin.travelOrder.travel-order-pdf', $data)
                ->setPaper([0, 0, 612, 936], 'portrait');

            $filename = 'Travel-Order-' . $data['orderNumber'] . '.pdf';

            if (request()->routeIs('admin.travelorder.print-form')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to generate travel order form: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate travel order form: ' . $e->getMessage(),
            ], 500);
        }
    }
}
