<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelOrder;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TravelOrderController extends Controller
{
    /**
     * Display travel orders for admin
     */
    public function index()
    {
        // Get paginated travel orders by status
        $pendingOrders = TravelOrder::with(['employee', 'employee.employmentDetail.departmentRelation'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pending_page');

        $approvedOrders = TravelOrder::with(['employee', 'employee.employmentDetail.departmentRelation', 'approver.employee'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->paginate(10, ['*'], 'approved_page');

        $disapprovedOrders = TravelOrder::with(['employee', 'employee.employmentDetail.departmentRelation', 'disapprover.employee'])
            ->where('status', 'disapproved')
            ->orderBy('disapproved_at', 'desc')
            ->paginate(10, ['*'], 'disapproved_page');

        // Get departments for filtering
        $departments = Department::orderBy('name')->get();

        return view('admin.travelOrder.travelOrder', compact(
            'pendingOrders',
            'approvedOrders', 
            'disapprovedOrders',
            'departments'
        ));
    }

    /**
     * Store a new travel order (for permanent employees)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string|max:500',
            'travel_date' => 'required|date|after_or_equal:today',
            'return_date' => 'required|date|after_or_equal:travel_date',
            'transportation_mode' => 'nullable|string|max:100',
            'estimated_budget' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $employee = auth()->user()->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            // Calculate duration
            $startDate = Carbon::parse($validated['travel_date']);
            $endDate = Carbon::parse($validated['return_date']);
            $duration = $startDate->diffInDays($endDate) + 1;

            // Check for overlapping travel orders (pending or approved)
            $hasOverlap = TravelOrder::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('travel_date', [$validated['travel_date'], $validated['return_date']])
                          ->orWhereBetween('return_date', [$validated['travel_date'], $validated['return_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('travel_date', '<=', $validated['travel_date'])
                                ->where('return_date', '>=', $validated['return_date']);
                          });
                })
                ->exists();

            if ($hasOverlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a travel order for the selected dates. Please choose different dates or cancel your existing travel order.'
                ], 422);
            }

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('travel_order_attachments', 'public');
            }

            // Create travel order
            $travelOrder = TravelOrder::create([
                'employee_id' => $employee->id,
                'destination' => $validated['destination'],
                'purpose' => $validated['purpose'],
                'travel_date' => $validated['travel_date'],
                'return_date' => $validated['return_date'],
                'duration' => $duration,
                'transportation_mode' => $validated['transportation_mode'],
                'estimated_budget' => $validated['estimated_budget'],
                'attachment' => $attachmentPath,
                'status' => 'pending',
                'filed_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Travel order submitted successfully',
                'order_number' => $travelOrder->order_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit travel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a travel order
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $travelOrder = TravelOrder::findOrFail($id);

            if ($travelOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending travel orders can be approved'
                ], 422);
            }

            // Update travel order status
            $travelOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // The attendance records will be created automatically via the model's boot method

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Travel order approved successfully. Attendance records have been created automatically.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve travel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disapprove a travel order
     */
    public function disapprove(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();

        try {
            $travelOrder = TravelOrder::findOrFail($id);

            if ($travelOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending travel orders can be disapproved'
                ], 422);
            }

            $travelOrder->update([
                'status' => 'disapproved',
                'disapproved_by' => auth()->id(),
                'disapproved_at' => now(),
                'disapproval_reason' => $validated['reason'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Travel order disapproved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to disapprove travel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show travel order details
     */
    public function show($id)
    {
        $travelOrder = TravelOrder::with([
            'employee',
            'employee.employmentDetail.departmentRelation',
            'approver.employee',
            'disapprover.employee'
        ])->findOrFail($id);

        return response()->json($travelOrder);
    }

    /**
     * Get travel orders for permanent employee
     */
    public function permanentIndex()
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            abort(404, 'Employee record not found');
        }

        $travelOrders = TravelOrder::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('employee.travelOrder.employeeTravelOrder', compact('travelOrders'));
    }

    /**
     * Cancel a travel order (for employees)
     */
    public function cancel($id)
    {
        DB::beginTransaction();

        try {
            $employee = auth()->user()->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found'
                ], 404);
            }

            $travelOrder = TravelOrder::where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$travelOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Travel order not found'
                ], 404);
            }

            if ($travelOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending travel orders can be cancelled'
                ], 422);
            }

            $travelOrder->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Travel order cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel travel order: ' . $e->getMessage()
            ], 500);
        }
    }
}