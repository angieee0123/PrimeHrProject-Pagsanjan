<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\MonetizationRequest;
use App\Services\MonetizationFormDataService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonetizationRequestController extends Controller
{
    /**
     * Employee files a monetization request.
     *
     * Balances are only *read* here — nothing is deducted until an admin
     * approves, so a pending request never holds credits hostage and
     * cancelling one needs no balance restore.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vl_days' => 'nullable|numeric|min:0|max:999',
            'sl_days' => 'nullable|numeric|min:0|max:999',
            'reason' => 'required|string|max:500',
        ]);

        $employee = auth()->user()?->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $vlDays = (float) ($validated['vl_days'] ?? 0);
        $slDays = (float) ($validated['sl_days'] ?? 0);

        if ($vlDays <= 0 && $slDays <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Enter at least one day to monetize.',
            ], 422);
        }

        // Current balances — latest row per code, never the current-year
        // filter, via LeaveBalance::currentForCode(). Same rule the leave
        // pages and the AI Assistant read.
        $vlBalance = LeaveBalance::currentForCode($employee->id, 'VL');
        $slBalance = LeaveBalance::currentForCode($employee->id, 'SL');

        $vlAvailable = $vlBalance ? (float) $vlBalance->available_credits : 0;
        $slAvailable = $slBalance ? (float) $slBalance->available_credits : 0;

        if ($vlDays > $vlAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient Vacation Leave balance. You have ' . number_format($vlAvailable, 1) . ' days available.',
            ], 422);
        }

        if ($slDays > $slAvailable) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient Sick Leave balance. You have ' . number_format($slAvailable, 1) . ' days available.',
            ], 422);
        }

        $monthlySalary = $employee->employmentDetail?->designationRelation?->monthly_rate;

        if ($monthlySalary === null || (float) $monthlySalary <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No salary rate is on record for your position, so the amount cannot be computed. Please contact HR.',
            ], 422);
        }

        $monetization = new MonetizationRequest([
            'employee_id' => $employee->id,
            'vl_days' => $vlDays,
            'sl_days' => $slDays,
            'monthly_salary' => $monthlySalary,
            'vl_balance' => $vlAvailable,
            'sl_balance' => $slAvailable,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'filed_by' => auth()->id(),
        ]);
        $monetization->computed_amount = $monetization->computeAmount();
        $monetization->save();

        // Monetization shipped with no notifications at all, so a request sat
        // in the admin tab until somebody happened to open it. It is money and
        // leave credits both, which makes it the least suitable queue in the
        // system to discover by chance.
        NotificationService::monetizationSubmitted($monetization);

        return response()->json([
            'success' => true,
            'message' => 'Monetization request submitted successfully',
            'request_number' => $monetization->request_number,
        ]);
    }

    /** Employee's own request as JSON for the detail modal. */
    public function show($id)
    {
        $employee = auth()->user()?->employee;

        $monetization = MonetizationRequest::with(['approvedBy.employee'])
            ->where('id', $id)
            ->where('employee_id', $employee?->id)
            ->first();

        if (!$monetization) {
            return response()->json([
                'success' => false,
                'message' => 'Monetization request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'request' => $this->payload($monetization),
        ]);
    }

    /** Employee cancels their own pending request. No balances move. */
    public function cancel($id)
    {
        $employee = auth()->user()?->employee;

        $monetization = MonetizationRequest::where('id', $id)
            ->where('employee_id', $employee?->id)
            ->first();

        if (!$monetization) {
            return response()->json([
                'success' => false,
                'message' => 'Monetization request not found',
            ], 404);
        }

        if ($monetization->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending monetization requests can be cancelled',
            ], 422);
        }

        $monetization->status = 'cancelled';
        $monetization->save();

        NotificationService::monetizationCancelled($monetization);

        return response()->json([
            'success' => true,
            'message' => 'Monetization request cancelled successfully',
        ]);
    }

    /** Admin view of any request as JSON for the detail modal. */
    public function adminShow($id)
    {
        $monetization = MonetizationRequest::with([
            'employee.employmentDetail.designationRelation',
            'employee.employmentDetail.departmentRelation',
            'approvedBy.employee',
        ])->find($id);

        if (!$monetization) {
            return response()->json([
                'success' => false,
                'message' => 'Monetization request not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'request' => $this->payload($monetization),
        ]);
    }

    /**
     * Admin approves a pending request.
     *
     * This is the only place monetized days leave the balances: each code's
     * available credits drop and a debit LeaveTransaction is written, so the
     * Transaction History tab shows where the days went.
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $monetization = MonetizationRequest::findOrFail($id);

            if ($monetization->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending monetization requests can be approved',
                ], 422);
            }

            $this->deduct($monetization, 'VL', (float) $monetization->vl_days);
            $this->deduct($monetization, 'SL', (float) $monetization->sl_days);

            $monetization->status = 'approved';
            $monetization->approved_by = auth()->id();
            $monetization->approved_at = now();
            $monetization->save();

            DB::commit();

            // After the commit. The approval moved leave credits and wrote a
            // debit transaction; none of that may be rolled back because the
            // bell could not be written.
            NotificationService::monetizationStatusChanged($monetization, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Monetization request approved successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // The UI only gets a short message, so the real cause belongs
            // here — otherwise a failure like this one is undebuggable.
            \Log::error('Monetization approval failed', [
                'request_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            $clientMessage = $e->getCode() === 422 ? $e->getMessage() : 'Failed to approve monetization request';

            return response()->json([
                'success' => false,
                'message' => $clientMessage,
            ], $e->getCode() === 422 ? 422 : 500);
        }
    }

    /** Admin disapproves a pending request with a required reason. */
    public function disapprove(Request $request, $id)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
        ]);

        $monetization = MonetizationRequest::findOrFail($id);

        if ($monetization->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending monetization requests can be disapproved',
            ], 422);
        }

        $monetization->status = 'disapproved';
        $monetization->approver_remarks = $validated['remarks'];
        $monetization->approved_by = auth()->id();
        $monetization->approved_at = now();
        $monetization->save();

        // Carries the reason: a refusal the filer cannot act on is a refusal
        // they will file again unchanged.
        NotificationService::monetizationStatusChanged($monetization, 'disapproved');

        return response()->json([
            'success' => true,
            'message' => 'Monetization request disapproved',
        ]);
    }

    /**
     * The employee's own Monetization sheet — Print Sheet on the detail modal.
     *
     * Scoped to the caller's employee row, so the id in the URL cannot be
     * changed into somebody else's sheet.
     */
    public function generateOwnForm($id, MonetizationFormDataService $formService)
    {
        $employee = auth()->user()?->employee;

        if (! $employee) {
            abort(404);
        }

        return $this->renderForm(
            $formService,
            (int) $id,
            $employee->id,
            request()->routeIs('monetization.print-form')
        );
    }

    /** The same sheet from the admin Monetization Requests tab, any employee. */
    public function generateForm($id, MonetizationFormDataService $formService)
    {
        return $this->renderForm(
            $formService,
            (int) $id,
            null,
            request()->routeIs('admin.monetization.print-form')
        );
    }

    /**
     * Build the sheet and hand it back as a PDF.
     *
     * Print streams the document so it opens in the browser's own viewer at
     * the real page size with none of the dashboard around it; Download sends
     * the identical document as a file. Same pair as the Travel Order, the
     * Pass Slip and the printed DTR — and the same reason `routeIs()` decides
     * rather than a query parameter.
     *
     * Only an approved request prints. The office's sheet carries no status
     * anywhere on it, so a pending or disapproved one would come out looking
     * exactly like an authorised computation of money owed.
     */
    private function renderForm(
        MonetizationFormDataService $formService,
        int $id,
        ?int $scopeEmployeeId,
        bool $stream
    ) {
        try {
            $data = $formService->build($id, auth()->user(), $scopeEmployeeId);

            if ($data['request']->status !== 'approved') {
                abort(422, 'Only an approved monetization request can be printed.');
            }

            // 8.5 x 14 in — the paper the office's template is typed on, and
            // the proportions the tracing in the partial is measured to.
            $pdf = Pdf::loadView('admin.leaveAndBenefits.partials.monetization-form', $data)
                ->setPaper([0, 0, 612, 1008], 'portrait');

            return $stream
                ? $pdf->stream($data['filename'])
                : $pdf->download($data['filename']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Monetization request not found.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to generate monetization form', [
                'request_id' => $id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate the monetization sheet.',
            ], 500);
        }
    }

    /**
     * Move $days out of the employee's $leaveCode balance.
     *
     * @throws \Exception with code 422 when the balance cannot cover it.
     */
    private function deduct(MonetizationRequest $monetization, string $leaveCode, float $days): void
    {
        if ($days <= 0) {
            return;
        }

        $balance = LeaveBalance::currentForCode($monetization->employee_id, $leaveCode);

        if (!$balance || (float) $balance->available_credits < $days) {
            $available = $balance ? (float) $balance->available_credits : 0;
            throw new \Exception(
                "Insufficient {$leaveCode} balance. " . number_format($available, 1) . ' days available.',
                422
            );
        }

        $balanceBefore = (float) $balance->available_credits;
        $balance->available_credits = $balanceBefore - $days;
        $balance->used_credits = (float) $balance->used_credits + $days;
        $balance->save();

        LeaveTransaction::create([
            'employee_id' => $monetization->employee_id,
            'leave_code' => $leaveCode,
            'year' => $balance->year,
            'transaction_type' => 'debit',
            'amount' => -$days,
            'balance_before' => $balanceBefore,
            'balance_after' => (float) $balance->available_credits,
            'reference_type' => 'monetization',
            'reference_id' => $monetization->id,
            'transaction_date' => now(),
            'processed_by' => auth()->id(),
            'remarks' => "Monetized {$days} days ({$monetization->request_number})",
        ]);
    }

    /**
     * One stable shape for both detail modals, so the employee and admin
     * sheets cannot disagree about the same request.
     */
    private function payload(MonetizationRequest $monetization): array
    {
        $employee = $monetization->employee;
        $employment = $employee?->employmentDetail;

        return [
            'id' => $monetization->id,
            'request_number' => $monetization->request_number,
            'status' => $monetization->status,
            'vl_days' => (float) $monetization->vl_days,
            'sl_days' => (float) $monetization->sl_days,
            'total_days' => $monetization->totalDays(),
            'monthly_salary' => (float) $monetization->monthly_salary,
            'vl_balance' => (float) $monetization->vl_balance,
            'sl_balance' => (float) $monetization->sl_balance,
            'computed_amount' => (float) $monetization->computed_amount,
            'constant_factor' => MonetizationRequest::CONSTANT_FACTOR,
            'reason' => $monetization->reason,
            'approver_remarks' => $monetization->approver_remarks,
            'filed_at' => $monetization->created_at?->format('F d, Y'),
            'decided_at' => $monetization->approved_at?->format('F d, Y'),
            'decided_by' => $this->approverName($monetization->approvedBy),
            'employee_name' => trim(($employee?->first_name ?? '') . ' ' . ($employee?->last_name ?? '')),
            'employee_id_no' => $employee?->employee_id,
            'position' => $employment?->designationRelation?->title,
            'department' => $employment?->departmentRelation?->name,
        ];
    }

    /**
     * Who acted on the request — the approver's employee record where there is
     * one, otherwise the user account's name. Same rule as
     * LeaveFormDataService and LeaveBenefitsExportController::actorName().
     */
    private function approverName($user): ?string
    {
        if (!$user) {
            return null;
        }

        if ($user->employee) {
            $e = $user->employee;
            $name = trim(implode(' ', array_filter([
                $e->first_name,
                $e->middle_name ? substr($e->middle_name, 0, 1) . '.' : null,
                $e->last_name,
                $e->suffix,
            ])));

            if ($name !== '') {
                return $name;
            }
        }

        $fallback = trim((string) ($user->name ?? ''));

        return $fallback !== '' ? $fallback : null;
    }
}
