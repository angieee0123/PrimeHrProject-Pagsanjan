<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobileTravelOrderController extends Controller
{
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found',
            ], 404);
        }

        $orders = TravelOrder::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'approved' => $orders->where('status', 'approved')->count(),
            'rejected' => $orders->whereIn('status', ['disapproved', 'rejected'])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'orders' => $orders->map(fn ($order) => $this->formatOrder($order))->values()->all(),
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

        $order = TravelOrder::where('id', $id)
            ->where('employee_id', $employee->id)
            ->with(['approver.employee', 'disapprover.employee'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Travel order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatOrder($order, detailed: true),
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
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:300',
            'travel_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:travel_date',
            'duration' => 'required|integer|min:1',
            'transportation_mode' => 'nullable|string|max:100',
            'estimated_budget' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('travel_orders', 'public');
        }

        $order = TravelOrder::create([
            'employee_id' => $employee->id,
            'destination' => $data['destination'],
            'purpose' => $data['purpose'],
            'travel_date' => $data['travel_date'],
            'return_date' => $data['return_date'],
            'duration' => $data['duration'],
            'transportation_mode' => $data['transportation_mode'] ?? null,
            'estimated_budget' => $data['estimated_budget'] ?? null,
            'attachment' => $attachmentPath,
            'status' => 'pending',
            'filed_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Travel order submitted successfully',
            'data' => $this->formatOrder($order),
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

        $order = TravelOrder::where('id', $id)
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending travel orders can be cancelled',
            ], 422);
        }

        $path = $order->attachment;
        if ($path) {
            Storage::disk('public')->delete($path);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Travel order cancelled successfully',
        ]);
    }

    private function formatOrder(TravelOrder $order, bool $detailed = false): array
    {
        $attachmentPath = $order->attachment;
        $approver = $order->relationLoaded('approver') ? $order->approver : null;
        $disapprover = $order->relationLoaded('disapprover') ? $order->disapprover : null;

        $processedBy = null;
        $processedAt = null;

        if ($order->status === 'approved' && $approver) {
            $emp = $approver->employee;
            $processedBy = $emp
                ? trim("{$emp->first_name} {$emp->last_name}")
                : ($approver->name ?? 'Admin');
            $processedAt = $order->approved_at?->toIso8601String();
        } elseif (in_array($order->status, ['disapproved', 'rejected'], true) && $disapprover) {
            $emp = $disapprover->employee;
            $processedBy = $emp
                ? trim("{$emp->first_name} {$emp->last_name}")
                : ($disapprover->name ?? 'Admin');
            $processedAt = $order->disapproved_at?->toIso8601String();
        }

        $payload = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'destination' => $order->destination,
            'purpose' => $order->purpose,
            'travel_date' => $order->travel_date instanceof Carbon
                ? $order->travel_date->format('Y-m-d')
                : (string) $order->travel_date,
            'return_date' => $order->return_date instanceof Carbon
                ? $order->return_date->format('Y-m-d')
                : (string) $order->return_date,
            'duration' => (int) $order->duration,
            'transportation_mode' => $order->transportation_mode,
            'estimated_budget' => $order->estimated_budget !== null
                ? (float) $order->estimated_budget
                : null,
            'status' => $order->status,
            'status_label' => $this->statusLabel($order->status),
            'attachment_url' => $attachmentPath
                ? url('storage/' . $attachmentPath)
                : null,
            'disapproval_reason' => $order->disapproval_reason,
            'processed_by' => $processedBy,
            'processed_at' => $processedAt,
            'created_at' => $order->created_at?->toIso8601String(),
        ];

        if ($detailed) {
            $payload['formatted_dates'] = $order->formatted_dates;
        }

        return $payload;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'disapproved', 'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }
}
