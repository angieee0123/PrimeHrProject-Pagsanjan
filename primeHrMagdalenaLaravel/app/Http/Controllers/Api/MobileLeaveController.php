<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LeaveController;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileLeaveController extends Controller
{
    public function __construct(
        private readonly LeaveController $leaveController
    ) {
    }

    /**
     * Leave & benefits data for the authenticated employee (mirrors permanent web page).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $currentYear = Carbon::now()->year;

        $leaveTypes = LeaveType::where('is_active', true)
            ->with(['leaveBalances' => function ($query) use ($employee, $currentYear) {
                $query->where('employee_id', $employee->id)
                    ->where('year', $currentYear)
                    ->where('total_credits', '>', 0);
            }])
            ->orderBy('leave_name')
            ->get()
            ->filter(fn ($leaveType) => $leaveType->leaveBalances->isNotEmpty())
            ->values();

        $leaveApplications = LeaveApplication::where('employee_id', $employee->id)
            ->with(['leaveType', 'approvedBy.employee'])
            ->orderByDesc('created_at')
            ->get();

        $vlBalance = $this->balanceForCode($leaveTypes, 'VL');
        $slBalance = $this->balanceForCode($leaveTypes, 'SL');

        $stats = [
            'total_filed' => $leaveApplications->count(),
            'total_days_used' => round(
                (float) $leaveApplications->where('status', 'approved')->sum('number_of_days'),
                1
            ),
            'pending_requests' => $leaveApplications->where('status', 'pending')->count(),
            'vl_sl_balance' => round($vlBalance + $slBalance, 1),
            'vl_balance' => round($vlBalance, 1),
            'sl_balance' => round($slBalance, 1),
        ];

        $transactionQuery = LeaveTransaction::where('employee_id', $employee->id);

        if ($request->filled('filter_type')) {
            $transactionQuery->where('transaction_type', $request->input('filter_type'));
        }
        if ($request->filled('filter_leave_code')) {
            $transactionQuery->where('leave_code', $request->input('filter_leave_code'));
        }
        if ($request->filled('filter_date')) {
            $transactionQuery->whereDate('transaction_date', $request->input('filter_date'));
        }

        $sortBy = $request->input('sort_by', 'transaction_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSortColumns = [
            'transaction_date',
            'leave_code',
            'transaction_type',
            'amount',
            'balance_before',
            'balance_after',
        ];

        if (in_array($sortBy, $allowedSortColumns, true)) {
            $transactionQuery->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $transactionQuery->orderByDesc('transaction_date');
        }

        $transactions = $transactionQuery
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn ($tx) => $this->formatTransaction($tx))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'year' => $currentYear,
                'leave_types' => $leaveTypes->map(fn ($type) => $this->formatLeaveType($type))->values()->all(),
                'applications' => $leaveApplications->map(fn ($app) => $this->formatApplication($app))->values()->all(),
                'transactions' => $transactions,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $response = $this->leaveController->store($request);
        $this->clearLeaveCache();

        return $response;
    }

    public function cancel(Request $request, int $id)
    {
        $response = $this->leaveController->cancel($id);
        $this->clearLeaveCache();

        return $response;
    }

    private function balanceForCode($leaveTypes, string $code): float
    {
        $type = $leaveTypes->firstWhere('leave_code', $code);
        $balance = $type?->leaveBalances->first();

        return $balance ? (float) $balance->available_credits : 0.0;
    }

    private function formatLeaveType(LeaveType $type): array
    {
        $balance = $type->leaveBalances->first();
        $total = $balance ? (float) $balance->total_credits : 0.0;
        $used = $balance ? (float) $balance->used_credits : 0.0;
        $pending = $balance ? (float) $balance->pending_credits : 0.0;
        $available = $balance ? (float) $balance->available_credits : 0.0;

        return [
            'leave_code' => $type->leave_code,
            'leave_name' => $type->leave_name,
            'is_accrued' => (bool) $type->is_accrued,
            'requires_attachment' => (bool) $type->requires_attachment,
            'attachment_info' => $type->attachment_info,
            'total_credits' => round($total, 4),
            'used_credits' => round($used, 4),
            'pending_credits' => round($pending, 4),
            'available_credits' => round($available, 4),
            'credit_category' => $type->is_accrued ? 'accrued' : 'fixed',
        ];
    }

    private function formatApplication(LeaveApplication $app): array
    {
        $approver = $app->approvedBy?->employee;
        $approverName = $approver
            ? trim("{$approver->first_name} {$approver->last_name}")
            : null;

        return [
            'id' => $app->id,
            'application_number' => $app->application_number,
            'leave_code' => $app->leave_code,
            'leave_type' => $app->leaveType->leave_name ?? $app->leave_code,
            'start_date' => $app->start_date->format('Y-m-d'),
            'end_date' => $app->end_date->format('Y-m-d'),
            'number_of_days' => (float) $app->number_of_days,
            'reason' => $app->reason,
            'status' => $app->status,
            'status_label' => ucfirst($app->status),
            'approver_remarks' => $app->approver_remarks,
            'approved_at' => $app->approved_at?->toIso8601String(),
            'approver_name' => $approverName,
            'attachment_url' => $app->attachment_path
                ? url('storage/' . $app->attachment_path)
                : null,
            'created_at' => $app->created_at?->toIso8601String(),
        ];
    }

    private function formatTransaction(LeaveTransaction $tx): array
    {
        return [
            'id' => $tx->id,
            'leave_code' => $tx->leave_code,
            'transaction_type' => $tx->transaction_type,
            'amount' => (float) $tx->amount,
            'balance_before' => (float) $tx->balance_before,
            'balance_after' => (float) $tx->balance_after,
            'transaction_date' => $tx->transaction_date instanceof Carbon
                ? $tx->transaction_date->format('Y-m-d')
                : (string) $tx->transaction_date,
            'remarks' => $tx->remarks,
            'reference_type' => $tx->reference_type,
            'reference_id' => $tx->reference_id,
        ];
    }

    private function clearLeaveCache(): void
    {
        $employee = Auth::user()?->employee;
        if ($employee) {
            Cache::forget("leave_balances_{$employee->id}");
        }
    }
}
