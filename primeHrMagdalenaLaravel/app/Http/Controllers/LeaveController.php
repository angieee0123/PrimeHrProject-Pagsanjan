<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveType;
use App\Models\LeaveAccrualRate;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Services\CscTimeConversionService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\LeaveFormDataService;
use App\Services\LeaveImportService;

class LeaveController extends Controller
{
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Header section (Rows 1-5)
            $sheet->setCellValue('A1', 'Employee Name:');
            $sheet->setCellValue('B1', 'Juan Dela Cruz');
            $sheet->setCellValue('A2', 'Position:');
            $sheet->setCellValue('B2', 'Administrative Officer II');
            $sheet->setCellValue('A3', 'Department:');
            $sheet->setCellValue('B3', 'Municipal Engineering Office');
            $sheet->setCellValue('A4', 'Date Range:');
            $sheet->setCellValue('B4', 'January 2012 - May 2024');
            $sheet->setCellValue('A5', 'Instructions:');
            $sheet->setCellValue('B5', 'Fill data starting from row 6. Notes format: VL1, FL1, T(0-1-2), etc.');
            
            // Column headers (Row 6) - Only necessary columns
            $headers = ['Month/Year', 'Notes', 'VL Earned', 'VL Used', 'SL Earned', 'SL Used', 'VL Balance', 'SL Balance'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            
            foreach ($columns as $index => $col) {
                $sheet->setCellValue($col . '6', $headers[$index]);
            }
            
            // Style headers
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0B044D'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];
            
            foreach ($columns as $col) {
                $sheet->getStyle($col . '6')->applyFromArray($headerStyle);
            }
            
            // Add sample rows with sample data (7-14)
            $sampleData = [
                ['Jun-19', 'VL1', 2.5, 1.0, 0.5, 1.5, 1.5, 1.0],
                ['Jul-19', 'FL1', 2.5, 2.5, 0.0, 0.0, 1.5, 1.5],
                ['Aug-19', 'T(0-2-10)', 2.5, 0.0, 1.0, 2.5, 1.5, 0.5],
                ['Sep-19', '', 2.5, 0.5, 0.5, 2.0, 1.5, 1.0],
                ['Oct-19', 'VL1/T(0-1-2)', 2.5, 2.0, 1.5, 0.5, 1.5, 0.0],
                ['Nov-19', '', 2.5, 1.5, 0.0, 1.5, 1.5, 1.5],
                ['Dec-19', 'SL1', 2.5, 0.0, 1.0, 2.5, 1.5, 0.5],
                ['Jan-20', '', 2.5, 0.5, 0.5, 2.0, 1.5, 1.0],
            ];
            
            for ($row = 7; $row <= 14; $row++) {
                $dataIndex = $row - 7;
                $data = $sampleData[$dataIndex] ?? [];
                
                if (!empty($data)) {
                    $sheet->setCellValue('A' . $row, $data[0] ?? '');
                    $sheet->setCellValue('B' . $row, $data[1] ?? '');
                    $sheet->setCellValue('C' . $row, $data[2] ?? '');
                    $sheet->setCellValue('D' . $row, $data[3] ?? '');
                    $sheet->setCellValue('E' . $row, $data[4] ?? '');
                    $sheet->setCellValue('F' . $row, $data[5] ?? '');
                    $sheet->setCellValue('G' . $row, $data[6] ?? '');
                    $sheet->setCellValue('H' . $row, $data[7] ?? '');
                }
            }
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(12);
            
            // Hide unused columns to prevent empty spaces
            for ($col = 'I'; $col <= 'Z'; $col++) {
                $sheet->getColumnDimension($col)->setVisible(false);
            }
            
            // Generate file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'Leave_Records_Template_' . now()->format('Y-m-d_His') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $sortBy = request('sort_by', 'leave_code');
        $sortOrder = request('sort_order', 'asc');
        $perPage = request('per_page', 10);

        $allowedSortColumns = ['leave_code', 'leave_name', 'annual_limit', 'is_accrued', 'is_active'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'leave_code';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $leaveTypes = LeaveType::orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends([
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage
            ]);

        $accrualPerPage = request('accrual_per_page', 10);
        $accrualPerPage = in_array($accrualPerPage, [10, 25, 50, 100]) ? $accrualPerPage : 10;
        
        $accrualRates = LeaveAccrualRate::with('leaveType')
            ->orderBy('is_active', 'desc')
            ->orderBy('leave_type_id', 'asc')
            ->orderBy('effective_date', 'desc')
            ->paginate($accrualPerPage)
            ->appends(['accrual_per_page' => $accrualPerPage]);

        $accruedLeaveTypes = LeaveType::where('is_accrued', true)
            ->where('is_active', true)
            ->orderBy('leave_name')
            ->get();

        $leaveApplications = LeaveApplication::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'leaveType'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        $monetizationRequests = \App\Models\MonetizationRequest::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        $departments = $leaveApplications
            ->pluck('employee.employmentDetail.departmentRelation.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $employees = \App\Models\Employee::with('employmentDetail.departmentRelation')
            ->orderBy('employee_id')
            ->get();

        $transactionQuery = LeaveTransaction::with([
            'employee',
            'processedBy.employee'
        ]);

        // Date range filter for transactions
        $filterTransactionDateFrom = request('filter_transaction_date_from');
        $filterTransactionDateTo = request('filter_transaction_date_to');
        $filterTransactionYear = request('filter_transaction_year');
        
        if ($filterTransactionDateFrom && $filterTransactionDateTo) {
            // Date range filter
            $transactionQuery->whereBetween('transaction_date', [$filterTransactionDateFrom, $filterTransactionDateTo]);
        } elseif ($filterTransactionYear) {
            // Specific year filter
            $transactionQuery->whereYear('transaction_date', $filterTransactionYear);
        }
        // If neither, show most recent (all) transactions

        if (request('filter_employee')) {
            $transactionQuery->where('employee_id', request('filter_employee'));
        }
        if (request('filter_type')) {
            $transactionQuery->where('transaction_type', request('filter_type'));
        }
        if (request('filter_leave_code')) {
            $transactionQuery->where('leave_code', request('filter_leave_code'));
        }

        $sortBy = request('sort_by', 'transaction_date');
        $sortOrder = request('sort_order', 'desc');
        $allowedSortColumns = ['transaction_date', 'employee_id', 'leave_code', 'transaction_type', 'amount', 'balance_before', 'balance_after'];
        
        if (in_array($sortBy, $allowedSortColumns)) {
            $transactionQuery->orderBy($sortBy, $sortOrder);
        } else {
            $transactionQuery->orderBy('transaction_date', 'desc');
        }
        
        $transactionQuery->orderBy('created_at', 'desc');
        
        $transactionPerPage = request('transaction_per_page', 10);
        if ($transactionPerPage === 'all') {
            $transactionPerPage = max($transactionQuery->count(), 1);
        } elseif (!in_array((int) $transactionPerPage, [10, 25, 50, 100], true)) {
            $transactionPerPage = 10;
        }

        $leaveTransactions = $transactionQuery->paginate($transactionPerPage)->appends(request()->except('page'));

        $transactionEmployees = \App\Models\Employee::whereHas('leaveTransactions')
            ->orderBy('employee_id')
            ->get();

        // Get available years for transaction filter dropdown
        $yearExpr = DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(YEAR FROM transaction_date)'
            : 'YEAR(transaction_date)';
        $transactionYears = LeaveTransaction::select(DB::raw("$yearExpr as year"))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $allLeaveTypes = LeaveType::where('is_active', true)
            ->orderBy('leave_code')
            ->get();

        \Log::info('Leave Transactions Count: ' . $leaveTransactions->total());

        $benefitsData = [
            ['empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'gsis' => '₱3,794', 'philhealth' => '₱1,050', 'pagibig' => '₱100', 'vlBalance' => 15, 'slBalance' => 15],
            ['empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'gsis' => '₱3,428', 'philhealth' => '₱950', 'pagibig' => '₱100', 'vlBalance' => 12, 'slBalance' => 13],
            ['empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'gsis' => '₱3,046', 'philhealth' => '₱850', 'pagibig' => '₱100', 'vlBalance' => 13, 'slBalance' => 11],
            ['empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'gsis' => '₱4,253', 'philhealth' => '₱1,150', 'pagibig' => '₱100', 'vlBalance' => 10, 'slBalance' => 9],
            ['empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'gsis' => '₱3,159', 'philhealth' => '₱875', 'pagibig' => '₱100', 'vlBalance' => 14, 'slBalance' => 14],
            ['empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'gsis' => '₱2,748', 'philhealth' => '₱775', 'pagibig' => '₱100', 'vlBalance' => 8, 'slBalance' => 10],
        ];

        // Fetch employee leave balances for leave credits tab
        $filterYear = request('filter_credits_year');
        $filterDateFrom = request('filter_credits_date_from');
        $filterDateTo = request('filter_credits_date_to');
        
        if ($filterDateFrom && $filterDateTo) {
            // Date range filter: get balances as of the end date
            // For each employee-leave_code, find the balance record closest to (but not after) the end date
            $endYear = Carbon::parse($filterDateTo)->year;
            $employeeLeaveBalances = LeaveBalance::with(['employee'])
                ->whereIn('id', function($query) use ($filterDateTo) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('leave_balances')
                        ->where('year', '<=', Carbon::parse($filterDateTo)->year)
                        ->groupBy('employee_id', 'leave_code');
                })
                ->orderBy('employee_id', 'asc')
                ->orderBy('leave_code', 'asc')
                ->get();
        } elseif ($filterYear) {
            // If year filter is applied, get balances for that specific year
            $employeeLeaveBalances = LeaveBalance::with(['employee'])
                ->where('year', $filterYear)
                ->orderBy('employee_id', 'asc')
                ->orderBy('leave_code', 'asc')
                ->get();
        } else {
            // Get the most recent year's balance for each employee-leave_code combination
            $employeeLeaveBalances = LeaveBalance::with(['employee'])
                ->whereIn('id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                        ->from('leave_balances')
                        ->groupBy('employee_id', 'leave_code');
                })
                ->orderBy('employee_id', 'asc')
                ->orderBy('leave_code', 'asc')
                ->get();
        }

        // Get available years for filter dropdown
        $availableYears = LeaveBalance::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        \Log::info('Admin Leave Credits Data', [
            'filter_year' => $filterYear,
            'filter_date_from' => $filterDateFrom,
            'filter_date_to' => $filterDateTo,
            'total_balances' => $employeeLeaveBalances->count(),
            'unique_employees' => $employeeLeaveBalances->pluck('employee_id')->unique()->count(),
            'available_years' => $availableYears->toArray()
        ]);

        return view('admin.leaveAndBenefits.adminLeaveAndBenefits', compact(
            'leaveTypes', 
            'leaveApplications', 
            'monetizationRequests',
            'benefitsData', 
            'accrualRates', 
            'accruedLeaveTypes', 
            'departments', 
            'employees', 
            'leaveTransactions', 
            'transactionEmployees', 
            'transactionYears',
            'allLeaveTypes',
            'employeeLeaveBalances',
            'availableYears'
        ));
    }

    public function storeLeaveType(Request $request)
    {
        try {
            $validated = $request->validate([
                'leave_code' => 'required|string|max:10|unique:leave_types_config,leave_code',
                'leave_name' => 'required|string|max:100',
                'annual_limit' => 'required|numeric|min:0',
                'attachment_info' => 'nullable|string',
                'is_active' => 'required|boolean',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('leave_types_documents', 'public');
            }

            $leaveType = LeaveType::create([
                'leave_code' => strtoupper($validated['leave_code']),
                'leave_name' => $validated['leave_name'],
                'annual_limit' => $validated['annual_limit'],
                'is_accrued' => $request->input('is_accrued') == '1',
                'is_cumulative' => $request->input('is_cumulative') == '1',
                'requires_6_months' => $request->input('requires_6_months') == '1',
                'is_monetizable' => $request->input('is_monetizable') == '1',
                'requires_attachment' => $request->input('requires_attachment') == '1',
                'attachment_info' => $validated['attachment_info'] ?? null,
                'document_path' => $documentPath,
                'is_active' => $validated['is_active'],
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Leave type '{$leaveType->leave_name}' has been registered successfully!",
                    'data' => $leaveType
                ]);
            }

            return redirect()->route('admin.leave', ['tab' => 'types'])
                ->with('success', "Leave type '{$leaveType->leave_name}' has been registered successfully!");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to create leave type: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register leave type: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.leave', ['tab' => 'types'])
                ->with('error', 'Failed to register leave type: ' . $e->getMessage());
        }
    }

    public function show($code)
    {
        $leaveType = LeaveType::where('leave_code', $code)->firstOrFail();
        
        return response()->json([
            'id' => $leaveType->id,
            'leave_code' => $leaveType->leave_code,
            'leave_name' => $leaveType->leave_name,
            'annual_limit' => $leaveType->annual_limit,
            'is_accrued' => (bool) $leaveType->is_accrued,
            'is_cumulative' => (bool) $leaveType->is_cumulative,
            'requires_6_months' => (bool) $leaveType->requires_6_months,
            'is_monetizable' => (bool) $leaveType->is_monetizable,
            'requires_attachment' => (bool) $leaveType->requires_attachment,
            'attachment_info' => $leaveType->attachment_info,
            'document_path' => $leaveType->document_path,
            'is_active' => (bool) $leaveType->is_active,
        ]);
    }

    public function update(Request $request, $code)
    {
        try {
            $leaveType = LeaveType::where('leave_code', $code)->firstOrFail();

            $validated = $request->validate([
                'leave_name' => 'required|string|max:100',
                'annual_limit' => 'required|numeric|min:0',
                'attachment_info' => 'nullable|string',
                'is_active' => 'required|boolean',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            $documentPath = $leaveType->document_path;
            if ($request->hasFile('document')) {
                if ($documentPath && \Storage::disk('public')->exists($documentPath)) {
                    \Storage::disk('public')->delete($documentPath);
                }
                $documentPath = $request->file('document')->store('leave_types_documents', 'public');
            }

            $leaveType->update([
                'leave_name' => $validated['leave_name'],
                'annual_limit' => $validated['annual_limit'],
                'is_accrued' => $request->input('is_accrued') == '1',
                'is_cumulative' => $request->input('is_cumulative') == '1',
                'requires_6_months' => $request->input('requires_6_months') == '1',
                'is_monetizable' => $request->input('is_monetizable') == '1',
                'requires_attachment' => $request->input('requires_attachment') == '1',
                'attachment_info' => $validated['attachment_info'] ?? null,
                'document_path' => $documentPath,
                'is_active' => $validated['is_active'],
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Leave type '{$leaveType->leave_name}' has been updated successfully!",
                    'data' => $leaveType
                ]);
            }

            return redirect()->route('admin.leave', ['tab' => 'types'])
                ->with('success', "Leave type '{$leaveType->leave_name}' has been updated successfully!");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Failed to update leave type: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update leave type: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('admin.leave', ['tab' => 'types'])
                ->with('error', 'Failed to update leave type: ' . $e->getMessage());
        }
    }

    public function storeAccrualRate(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types_config,id',
            'accrual_frequency' => 'required|in:daily,monthly,yearly',
            'days_of_service_required' => 'required|numeric|min:0.01',
            'credits_earned_per_period' => 'required|numeric|min:0.0001',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        LeaveAccrualRate::create($validated);

        return redirect()->route('admin.leave')->with('success', 'Accrual rate added successfully!');
    }

    public function showAccrualRate($id)
    {
        $accrualRate = LeaveAccrualRate::with('leaveType')->findOrFail($id);
        return response()->json($accrualRate);
    }

    public function updateAccrualRate(Request $request, $id)
    {
        $accrualRate = LeaveAccrualRate::findOrFail($id);

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types_config,id',
            'accrual_frequency' => 'required|in:daily,monthly,yearly',
            'days_of_service_required' => 'required|numeric|min:0.01',
            'credits_earned_per_period' => 'required|numeric|min:0.0001',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:effective_date',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $accrualRate->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Accrual rate updated successfully!']);
        }

        return redirect()->route('admin.leave')->with('success', 'Accrual rate updated successfully!');
    }

    public function destroyAccrualRate($id)
    {
        $accrualRate = LeaveAccrualRate::findOrFail($id);
        $accrualRate->delete();

        return redirect()->route('admin.leave')->with('success', 'Accrual rate deleted successfully!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_code' => 'required|exists:leave_types_config,leave_code',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'number_of_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string|max:500',
            'commutation_requested' => 'nullable|boolean',
            'leave_location' => 'nullable|in:ph,abroad',
            'leave_location_specify' => 'nullable|string|max:255',
            'sick_leave_type' => 'nullable|in:in_hospital,out_patient',
            'illness_specify' => 'nullable|string|max:255',
            'study_leave_purpose' => 'nullable|in:masters,bar_review,other',
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

            $leaveType = LeaveType::where('leave_code', $validated['leave_code'])->first();
            
            if ($leaveType->requires_attachment && !$request->hasFile('attachment')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attachment is required for this leave type'
                ], 422);
            }

            $validation = CscTimeConversionService::validateLeaveDays(
                $validated['start_date'],
                $validated['end_date'],
                $validated['number_of_days']
            );

            if (!$validation['is_valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'] . ' Please adjust your leave request to match working days only (excluding weekends).'
                ], 422);
            }

            $hasOverlap = LeaveApplication::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                          ->orWhere(function($q) use ($validated) {
                              $q->where('start_date', '<=', $validated['start_date'])
                                ->where('end_date', '>=', $validated['end_date']);
                          });
                })
                ->exists();

            if ($hasOverlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a leave request for the selected dates. Please choose different dates or cancel your existing leave request.'
                ], 422);
            }

            $year = Carbon::parse($validated['start_date'])->year;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $validated['leave_code'])
                ->where('year', $year)
                ->first();

            // If no balance found for target year, use the latest available year
            if (!$leaveBalance) {
                $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_code', $validated['leave_code'])
                    ->orderBy('year', 'desc')
                    ->first();
            }

            if (!$leaveBalance || $leaveBalance->available_credits < $validated['number_of_days']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient leave balance. You have ' . ($leaveBalance ? number_format($leaveBalance->available_credits, 1) : '0') . ' days available.'
                ], 422);
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
            }

            $leaveApplication = LeaveApplication::create([
                'employee_id' => $employee->id,
                'leave_code' => $validated['leave_code'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'number_of_days' => $validated['number_of_days'],
                'reason' => $validated['reason'],
                'commutation_requested' => $request->boolean('commutation_requested'),
                'leave_location' => $validated['leave_location'] ?? null,
                'leave_location_specify' => $validated['leave_location_specify'] ?? null,
                'sick_leave_type' => $validated['sick_leave_type'] ?? null,
                'illness_specify' => $validated['illness_specify'] ?? null,
                'study_leave_purpose' => $validated['study_leave_purpose'] ?? null,
                'status' => 'pending',
                'attachment_path' => $attachmentPath,
                'filed_by' => auth()->id(),
            ]);

            $balanceBefore = $leaveBalance->available_credits;
            $leaveBalance->pending_credits += $validated['number_of_days'];
            $leaveBalance->available_credits -= $validated['number_of_days'];
            $leaveBalance->save();

            LeaveTransaction::create([
                'employee_id' => $employee->id,
                'leave_code' => $validated['leave_code'],
                'year' => $year,
                'transaction_type' => 'pending',
                'amount' => -$validated['number_of_days'],
                'balance_before' => $balanceBefore,
                'balance_after' => $leaveBalance->available_credits,
                'reference_type' => 'leave_application',
                'reference_id' => $leaveApplication->id,
                'transaction_date' => now(),
                'processed_by' => auth()->id(),
                'remarks' => "Pending leave application {$leaveApplication->application_number}",
            ]);

            DB::commit();

            // After the commit, never inside it. A notification is a courtesy;
            // the leave application is the record. Writing the bell inside the
            // transaction meant a failure there rolled the application back —
            // the employee was told their leave could not be filed because the
            // system could not tell HR about it.
            NotificationService::leaveRequestSubmitted($leaveApplication);

            return response()->json([
                'success' => true,
                'message' => 'Leave application submitted successfully',
                'application_number' => $leaveApplication->application_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit leave application: ' . $e->getMessage()
            ], 500);
        }
    }

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

            $leaveApplication = LeaveApplication::where('id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if (!$leaveApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave application not found'
                ], 404);
            }

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be cancelled'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->available_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $employee->id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year,
                    'transaction_type' => 'credit',
                    'amount' => $leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Cancelled leave application {$leaveApplication->application_number}",
                ]);
            }

            $leaveApplication->status = 'cancelled';
            $leaveApplication->save();

            DB::commit();

            // The admin queue is a list of things to decide, so an item leaving
            // it is news: without this, HR opens a request that is no longer
            // there to approve.
            NotificationService::leaveRequestCancelled($leaveApplication);

            return response()->json([
                'success' => true,
                'message' => 'Leave request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $leaveApplication = LeaveApplication::findOrFail($id);

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be approved'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->used_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $leaveApplication->employee_id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year,
                    'transaction_type' => 'debit',
                    'amount' => -$leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Approved leave application {$leaveApplication->application_number}",
                ]);
            }

            $leaveApplication->status = 'approved';
            $leaveApplication->approved_by = auth()->id();
            $leaveApplication->approved_at = now();
            $leaveApplication->approved_days_with_pay = $leaveApplication->number_of_days;
            $leaveApplication->approved_days_without_pay = 0;
            $leaveApplication->save();

            DB::commit();

            // After the commit — see store(). An approval that succeeded must
            // not be undone by a notification that did not.
            NotificationService::leaveRequestStatusChanged($leaveApplication, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'remarks' => 'required|string|max:500'
            ]);

            $leaveApplication = LeaveApplication::findOrFail($id);

            if ($leaveApplication->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending leave requests can be rejected'
                ], 422);
            }

            $year = Carbon::parse($leaveApplication->start_date)->year;
            $leaveBalance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_code', $leaveApplication->leave_code)
                ->where('year', $year)
                ->first();

            // If no balance found for that year, find the most recent one
            if (!$leaveBalance) {
                $leaveBalance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                    ->where('leave_code', $leaveApplication->leave_code)
                    ->orderBy('year', 'desc')
                    ->first();
            }

            if ($leaveBalance) {
                $balanceBefore = $leaveBalance->available_credits;
                $leaveBalance->pending_credits -= $leaveApplication->number_of_days;
                $leaveBalance->available_credits += $leaveApplication->number_of_days;
                $leaveBalance->save();

                LeaveTransaction::create([
                    'employee_id' => $leaveApplication->employee_id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $leaveBalance->year,
                    'transaction_type' => 'credit',
                    'amount' => $leaveApplication->number_of_days,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $leaveBalance->available_credits,
                    'reference_type' => 'leave_application',
                    'reference_id' => $leaveApplication->id,
                    'transaction_date' => now(),
                    'processed_by' => auth()->id(),
                    'remarks' => "Rejected leave application {$leaveApplication->application_number}: {$validated['remarks']}",
                ]);
            } else {
                \Log::error('Leave balance not found for rejection', [
                    'employee_id' => $leaveApplication->employee_id,
                    'leave_code' => $leaveApplication->leave_code,
                    'year' => $year
                ]);
            }

            $leaveApplication->status = 'rejected';
            $leaveApplication->approved_by = auth()->id();
            $leaveApplication->approved_at = now();
            $leaveApplication->approver_remarks = $validated['remarks'];
            $leaveApplication->save();

            DB::commit();

            NotificationService::leaveRequestStatusChanged($leaveApplication, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeBalances($employeeId)
    {
        try {
            $year = now()->year;
            
            $balances = LeaveBalance::where('employee_id', $employeeId)
                ->where('year', $year)
                ->pluck('available_credits', 'leave_code');

            $leaveTypes = LeaveType::where('is_active', true)
                ->orderBy('leave_name')
                ->get(['leave_code', 'leave_name']);

            return response()->json([
                'success' => true,
                'balances' => $balances,
                'leaveTypes' => $leaveTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load employee balances: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeManualCredit(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_code' => 'required|exists:leave_types_config,leave_code',
            'amount' => 'required|numeric|min:0.000001|max:999.999999',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:add,deduct',
            'remarks' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $year = Carbon::parse($validated['transaction_date'])->year;
            $isDeduction = $validated['transaction_type'] === 'deduct';
            
            $leaveBalance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'leave_code' => $validated['leave_code'],
                    'year' => $year,
                ],
                [
                    'total_credits' => 0,
                    'used_credits' => 0,
                    'pending_credits' => 0,
                    'available_credits' => 0,
                    'carried_over' => 0,
                ]
            );

            $balanceBefore = $leaveBalance->available_credits;
            
            if ($isDeduction && $balanceBefore < $validated['amount']) {
                \Log::warning('Manual deduction results in negative balance', [
                    'employee_id' => $validated['employee_id'],
                    'leave_code' => $validated['leave_code'],
                    'current_balance' => $balanceBefore,
                    'deduction_amount' => $validated['amount'],
                    'processed_by' => auth()->id()
                ]);
            }
            
            if ($isDeduction) {
                $leaveBalance->total_credits -= $validated['amount'];
                $leaveBalance->available_credits -= $validated['amount'];
                $transactionAmount = -$validated['amount'];
            } else {
                $leaveBalance->total_credits += $validated['amount'];
                $leaveBalance->available_credits += $validated['amount'];
                $transactionAmount = $validated['amount'];
            }
            
            $leaveBalance->save();

            LeaveTransaction::create([
                'employee_id' => $validated['employee_id'],
                'leave_code' => $validated['leave_code'],
                'year' => $year,
                'transaction_type' => 'adjustment',
                'amount' => $transactionAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $leaveBalance->available_credits,
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'transaction_date' => $validated['transaction_date'],
                'processed_by' => auth()->id(),
                'remarks' => ($isDeduction ? '[DEDUCTION] ' : '[ADDITION] ') . $validated['remarks'],
            ]);

            DB::commit();

            $message = $isDeduction 
                ? 'Leave credits deducted successfully!' 
                : 'Leave credits added successfully!';

            return redirect()->route('admin.leave')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.leave')
                ->with('error', 'Failed to adjust leave credits: ' . $e->getMessage());
        }
    }

    public function updateTransaction(Request $request, $id)
    {
        $transaction = LeaveTransaction::findOrFail($id);

        if ($transaction->reference_type !== 'manual_adjustment') {
            return redirect()->route('admin.leave', ['tab' => 'transactions'])
                ->with('error', 'Only manual adjustment transactions can be edited.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.000001|max:999.999999',
            'transaction_type' => 'required|in:add,deduct',
            'transaction_date' => 'required|date',
            'remarks' => 'required|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $isDeduction = $validated['transaction_type'] === 'deduct';
            $newAmount = $isDeduction ? -$validated['amount'] : $validated['amount'];
            $delta = $newAmount - $transaction->amount;

            $transaction->amount = $newAmount;
            $transaction->balance_after = $transaction->balance_before + $newAmount;
            $transaction->transaction_date = $validated['transaction_date'];
            $transaction->remarks = ($isDeduction ? '[DEDUCTION] ' : '[ADDITION] ') . $validated['remarks'];
            $transaction->save();

            if ($delta != 0) {
                LeaveTransaction::where('employee_id', $transaction->employee_id)
                    ->where('leave_code', $transaction->leave_code)
                    ->where('year', $transaction->year)
                    ->where('id', '>', $transaction->id)
                    ->orderBy('id')
                    ->each(function ($subsequent) use ($delta) {
                        $subsequent->balance_before += $delta;
                        $subsequent->balance_after += $delta;
                        $subsequent->save();
                    });

                $leaveBalance = LeaveBalance::where('employee_id', $transaction->employee_id)
                    ->where('leave_code', $transaction->leave_code)
                    ->where('year', $transaction->year)
                    ->first();

                if ($leaveBalance) {
                    $leaveBalance->available_credits += $delta;
                    $leaveBalance->total_credits += $delta;
                    $leaveBalance->save();
                }
            }

            DB::commit();

            return redirect()->route('admin.leave', ['tab' => 'transactions'])
                ->with('success', 'Transaction updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update leave transaction: ' . $e->getMessage());

            return redirect()->route('admin.leave', ['tab' => 'transactions'])
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    public function viewLeaveForm($id, LeaveFormDataService $formService)
    {
        try {
            return view('admin.leaveAndBenefits.leave-form-view', $formService->build($id));
        } catch (\Exception $e) {
            \Log::error('Failed to load leave form: ' . $e->getMessage());
            abort(404, 'Leave application not found.');
        }
    }

    public function generateLeaveForm($id, LeaveFormDataService $formService)
    {
        try {
            $data = $formService->build($id);
            $application = $data['application'];

            $pdf = Pdf::loadView('admin.leaveAndBenefits.leave-form-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('margin-top', 8)
                ->setOption('margin-bottom', 8)
                ->setOption('margin-left', 8)
                ->setOption('margin-right', 8);

            $filename = 'CS-Form-6-' . $application->application_number . '.pdf';

            // Print streams into the browser's viewer; Download sends the same
            // document as a file. Matched with a wildcard because the mayor's
            // read-only pair (mayor.leave.print-form / .download-form) routes to
            // this very method — naming only the admin route would have made the
            // mayor's Print button silently download instead.
            if (request()->routeIs('*.leave.print-form')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Failed to generate leave form: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate leave form: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importLeaveRecords(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
            ]);

            $file = $request->file('excel_file');
            $filePath = $file->store('temp_leave_imports');
            $fullPath = Storage::path($filePath);

            try {
                $parseResult = LeaveImportService::parseExcelFile($fullPath);

                if (!$parseResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $parseResult['message'],
                    ], 422);
                }

                $importResult = LeaveImportService::importLeaveRecords(
                    $validated['employee_id'],
                    $parseResult['records']
                );
            } finally {
                Storage::delete($filePath);
            }

            if (!$importResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $importResult['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $importResult['message'],
                'imported_count' => $importResult['imported_count'],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Leave import failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
