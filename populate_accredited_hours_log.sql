-- Populate accredited_hours_log for existing attendance records
INSERT INTO accredited_hours_log (
    attendance_id,
    employee_id,
    schedule_id,
    am_accredited_minutes,
    pm_accredited_minutes,
    ot_minutes,
    late_minutes,
    undertime_minutes,
    total_accredited_minutes,
    total_actual_minutes,
    created_at,
    updated_at
)
SELECT 
    a.id AS attendance_id,
    a.employee_id,
    s.id AS schedule_id,
    CASE WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL THEN 240 ELSE 0 END AS am_accredited_minutes,
    CASE WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL THEN 240 ELSE 0 END AS pm_accredited_minutes,
    CASE WHEN a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out) ELSE 0 END AS ot_minutes,
    0 AS late_minutes,
    0 AS undertime_minutes,
    CASE WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL THEN 240 ELSE 0 END +
    CASE WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL THEN 240 ELSE 0 END AS total_accredited_minutes,
    COALESCE(TIMESTAMPDIFF(MINUTE, a.am_in, a.am_out), 0) +
    COALESCE(TIMESTAMPDIFF(MINUTE, a.pm_in, a.pm_out), 0) +
    COALESCE(TIMESTAMPDIFF(MINUTE, a.ot_in, a.ot_out), 0) AS total_actual_minutes,
    NOW() AS created_at,
    NOW() AS updated_at
FROM attendance a
LEFT JOIN schedules s ON s.employee_id = a.employee_id
    AND (s.start_date IS NULL OR a.date >= s.start_date)
    AND (s.end_date IS NULL OR a.date <= s.end_date)
WHERE NOT EXISTS (
    SELECT 1 FROM accredited_hours_log WHERE attendance_id = a.id
);

-- Update attendance table with calculated hours
UPDATE attendance a
LEFT JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
SET 
    a.accredited_hours = ahl.total_accredited_minutes / 60,
    a.total_hours = ahl.total_actual_minutes / 60
WHERE ahl.id IS NOT NULL;
