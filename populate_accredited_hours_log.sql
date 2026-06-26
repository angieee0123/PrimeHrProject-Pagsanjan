-- Backfill accredited_hours_log for existing attendance records
-- Uses employee schedule to compute late/undertime/accredited minutes correctly
-- Safe to re-run: only inserts where no log exists yet

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
    am_grace_applied,
    pm_grace_applied,
    requires_salary_deduction,
    computation_notes,
    created_at,
    updated_at
)
SELECT
    a.id AS attendance_id,
    a.employee_id,
    s.id AS schedule_id,

    -- AM accredited: from schedule start (or actual if late) to min(am_out, schedule am_out)
    CASE
        WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL THEN
            GREATEST(0,
                LEAST(
                    TIME_TO_SEC(COALESCE(s.am_out, '12:00:00')) / 60,
                    TIME_TO_SEC(STR_TO_DATE(a.am_out, '%H:%i')) / 60
                ) -
                CASE
                    WHEN TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                         <= (TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60 + 5)
                    THEN TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60
                    ELSE TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                END
            )
        ELSE 0
    END AS am_accredited_minutes,

    -- PM accredited: from schedule pm_in (or actual if late) to min(pm_out, schedule pm_out)
    CASE
        WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL THEN
            GREATEST(0,
                LEAST(
                    TIME_TO_SEC(COALESCE(s.pm_out, '17:00:00')) / 60,
                    TIME_TO_SEC(STR_TO_DATE(a.pm_out, '%H:%i')) / 60
                ) -
                CASE
                    WHEN TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60
                         <= (TIME_TO_SEC(COALESCE(s.pm_in, '13:00:00')) / 60 + 5)
                    THEN TIME_TO_SEC(COALESCE(s.pm_in, '13:00:00')) / 60
                    ELSE TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60
                END
            )
        ELSE 0
    END AS pm_accredited_minutes,

    -- OT minutes
    CASE
        WHEN a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL
        THEN GREATEST(0, TIME_TO_SEC(STR_TO_DATE(a.ot_out, '%H:%i')) / 60 - TIME_TO_SEC(STR_TO_DATE(a.ot_in, '%H:%i')) / 60)
        ELSE 0
    END AS ot_minutes,

    -- Late minutes (AM only; beyond 5-min grace from schedule am_in)
    CASE
        WHEN a.am_in IS NOT NULL
             AND TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                 > (TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60 + 5)
        THEN ROUND(
            TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
            - TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60
        )
        ELSE 0
    END AS late_minutes,

    -- Undertime minutes (pm_out left before schedule pm_out)
    CASE
        WHEN a.pm_out IS NOT NULL
             AND TIME_TO_SEC(STR_TO_DATE(a.pm_out, '%H:%i')) / 60
                 < TIME_TO_SEC(COALESCE(s.pm_out, '17:00:00')) / 60
        THEN ROUND(
            TIME_TO_SEC(COALESCE(s.pm_out, '17:00:00')) / 60
            - TIME_TO_SEC(STR_TO_DATE(a.pm_out, '%H:%i')) / 60
        )
        ELSE 0
    END AS undertime_minutes,

    -- total_accredited_minutes = am + pm
    (
        CASE
            WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL THEN
                GREATEST(0,
                    LEAST(
                        TIME_TO_SEC(COALESCE(s.am_out, '12:00:00')) / 60,
                        TIME_TO_SEC(STR_TO_DATE(a.am_out, '%H:%i')) / 60
                    ) -
                    CASE
                        WHEN TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                             <= (TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60 + 5)
                        THEN TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60
                        ELSE TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                    END
                )
            ELSE 0
        END
        +
        CASE
            WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL THEN
                GREATEST(0,
                    LEAST(
                        TIME_TO_SEC(COALESCE(s.pm_out, '17:00:00')) / 60,
                        TIME_TO_SEC(STR_TO_DATE(a.pm_out, '%H:%i')) / 60
                    ) -
                    CASE
                        WHEN TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60
                             <= (TIME_TO_SEC(COALESCE(s.pm_in, '13:00:00')) / 60 + 5)
                        THEN TIME_TO_SEC(COALESCE(s.pm_in, '13:00:00')) / 60
                        ELSE TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60
                    END
                )
            ELSE 0
        END
    ) AS total_accredited_minutes,

    -- total_actual_minutes = raw time worked (am + pm + ot)
    (
        COALESCE(
            CASE WHEN a.am_in IS NOT NULL AND a.am_out IS NOT NULL
                THEN GREATEST(0, TIME_TO_SEC(STR_TO_DATE(a.am_out, '%H:%i')) / 60 - TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60)
                ELSE 0 END, 0)
        + COALESCE(
            CASE WHEN a.pm_in IS NOT NULL AND a.pm_out IS NOT NULL
                THEN GREATEST(0, TIME_TO_SEC(STR_TO_DATE(a.pm_out, '%H:%i')) / 60 - TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60)
                ELSE 0 END, 0)
        + COALESCE(
            CASE WHEN a.ot_in IS NOT NULL AND a.ot_out IS NOT NULL
                THEN GREATEST(0, TIME_TO_SEC(STR_TO_DATE(a.ot_out, '%H:%i')) / 60 - TIME_TO_SEC(STR_TO_DATE(a.ot_in, '%H:%i')) / 60)
                ELSE 0 END, 0)
    ) AS total_actual_minutes,

    -- am_grace_applied
    CASE
        WHEN a.am_in IS NOT NULL
             AND TIME_TO_SEC(STR_TO_DATE(a.am_in, '%H:%i')) / 60
                 <= (TIME_TO_SEC(COALESCE(s.am_in, '08:00:00')) / 60 + 5)
        THEN 1 ELSE 0
    END AS am_grace_applied,

    -- pm_grace_applied
    CASE
        WHEN a.pm_in IS NOT NULL
             AND TIME_TO_SEC(STR_TO_DATE(a.pm_in, '%H:%i')) / 60
                 <= (TIME_TO_SEC(COALESCE(s.pm_in, '13:00:00')) / 60 + 5)
        THEN 1 ELSE 0
    END AS pm_grace_applied,

    -- requires_salary_deduction (absent = no time records)
    CASE WHEN a.am_in IS NULL AND a.pm_in IS NULL THEN 1 ELSE 0 END AS requires_salary_deduction,

    'Backfill from seed data' AS computation_notes,
    NOW() AS created_at,
    NOW() AS updated_at

FROM attendance a
LEFT JOIN schedules s ON s.employee_id = a.employee_id
    AND (s.start_date IS NULL OR a.date >= s.start_date)
    AND (s.end_date IS NULL OR a.date <= s.end_date)
WHERE NOT EXISTS (
    SELECT 1 FROM accredited_hours_log WHERE attendance_id = a.id
)
ORDER BY s.start_date DESC;

-- Also sync total_hours and accredited_hours back to the attendance table
UPDATE attendance a
INNER JOIN accredited_hours_log ahl ON ahl.attendance_id = a.id
SET
    a.accredited_hours = ahl.total_accredited_minutes,
    a.total_hours      = ahl.total_actual_minutes
WHERE a.accredited_hours IS NULL OR a.total_hours IS NULL;

SELECT CONCAT('accredited_hours_log records created: ', COUNT(*)) AS result
FROM accredited_hours_log;
