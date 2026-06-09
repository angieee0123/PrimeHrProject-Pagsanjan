<?php
// This demonstrates the fix needed in AttendanceController.php index() method
// Replace the section that says:
// "// Simplified detailed records pagination
// $detailedRecords = [];
// $detailedPagination = [...];"

// WITH THIS CODE:

$tab = $request->get('tab', 'summary');
$detailedRecords = [];
$detailedPagination = [
    'current_page' => 1,
    'per_page' => 10,
    'total' => 0,
    'last_page' => 0,
    'from' => 0,
    'to' => 0,
];

if ($tab === 'detailed') {
    $detailedPerPage = (int)$perPage ?: 10;
    $detailedPage = (int)$page ?: 1;
    $employeeName = $request->get('employee_name');
    
    $allEmps = Employee::with(['employmentDetail.departmentRelation', 'schedule'])->orderBy('first_name')->get();
    if ($department && $department !== 'All Departments') {
        $allEmps = $allEmps->filter(fn($e) => $e->employmentDetail?->departmentRelation?->name === $department);
    }
    
    $detailedRecordsData = [];
    foreach ($allEmps as $emp) {
        $att = Attendance::where('employee_id', $emp->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('accreditedHoursLogs.schedule')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d'));
        
        $leaves = \App\Models\LeaveApplication::where('employee_id', $emp->id)
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(fn($q2) => $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate)))
            ->with('leaveType')->get();
        
        $recs = $this->generateDetailedRecords($startDate, $endDate, $att, $emp, $leaves);
        $detailedRecordsData = array_merge($detailedRecordsData, $recs);
    }
    
    if ($employeeName) {
        $detailedRecordsData = array_filter($detailedRecordsData, fn($r) => $r['employee_name'] === $employeeName);
        $detailedRecordsData = array_values($detailedRecordsData);
    }
    
    $total = count($detailedRecordsData);
    $lastPage = max(1, ceil($total / $detailedPerPage));
    $from = (($detailedPage - 1) * $detailedPerPage) + 1;
    $to = min($detailedPage * $detailedPerPage, $total);
    
    $detailedRecords = array_slice($detailedRecordsData, ($detailedPage - 1) * $detailedPerPage, $detailedPerPage);
    
    $detailedPagination = [
        'current_page' => $detailedPage,
        'per_page' => $detailedPerPage,
        'total' => $total,
        'last_page' => $lastPage,
        'from' => $total > 0 ? $from : 0,
        'to' => $total > 0 ? $to : 0,
    ];
}
