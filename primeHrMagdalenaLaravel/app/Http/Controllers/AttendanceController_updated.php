<?php
// This is a patch file showing the changes needed to AttendanceController.php

// In the calculateEmployeeAttendance() method, around line 151, change this:

/*
        return [
            'id' => $employee->employee_id,
            'employee_id' => $employee->id,
            'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
            'position' => $employee->employmentDetail->position ?? 'N/A',
            'dept' => $deptName,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'halfday' => $halfday,
            'overtime' => $overtime,
            'on_leave' => $onLeave,
            'rate' => $rate,
            'status' => $status,
        ];
*/

// TO THIS:

return [
    'id' => $employee->employee_id,
    'employee_id' => $employee->id,
    'name' => trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name),
    'photo' => $employee->photo,
    'position' => $employee->employmentDetail->position ?? 'N/A',
    'dept' => $deptName,
    'present' => $present,
    'absent' => $absent,
    'late' => $late,
    'halfday' => $halfday,
    'overtime' => $overtime,
    'on_leave' => $onLeave,
    'rate' => $rate,
    'status' => $status,
];
