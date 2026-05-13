-- Add attendance record for permanent@gmail.com (Juan Dela Cruz, employee_id=9)
-- Date: May 20, 2026 (Wednesday)
-- AM In: 10:00, AM Out: 12:07, PM In: 13:00, PM Out: 18:09
-- Late: 2 hours (120 minutes), Accredited: 8 hours (480 minutes)
-- Total Hours: 7.3 hours (438 minutes)
-- Grace: PM applied (arrived at 13:00, within 5-minute grace period)

-- Step 1: Insert attendance record
INSERT INTO `attendance` 
(`employee_id`, `date`, `am_in`, `am_out`, `pm_in`, `pm_out`, `ot_in`, `ot_out`, `accredited_hours`, `total_hours`)
VALUES 
(9, '2026-05-20', '10:00:00', '12:07:00', '13:00:00', '18:09:00', NULL, NULL, 480, 438);

-- Step 2: Get the attendance_id (assuming it will be 28 if no other records were added)
-- If you need to find it: SELECT id FROM attendance WHERE employee_id=9 AND date='2026-05-20';

-- Step 3: Create schedule for employee 9 (if not exists)
-- Using standard 8:00-12:00, 13:00-17:00 schedule
INSERT INTO `schedules` 
(`employee_id`, `start_date`, `end_date`, `am_in`, `am_out`, `pm_in`, `pm_out`, `created_at`, `updated_at`)
VALUES 
(9, '2026-05-01', '2026-05-31', '08:00:00', '12:00:00', '13:00:00', '17:00:00', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Step 4: Insert accredited hours log
-- AM: 10:00-12:07 = 127 minutes actual, but late by 120 minutes, so AM accredited = 0 minutes
-- PM: 13:00-18:09 = 309 minutes actual, grace applied, PM accredited = 240 minutes (13:00-17:00)
-- Total accredited: 240 minutes (4 hours)
-- Wait, the DTR shows 8 hours accredited with PM grace...
-- Let me recalculate based on "Accredited Hours: 8 hrs" from the DTR:
-- This means they're crediting full 8 hours despite being 2 hours late
-- AM: 10:00-12:07 = 127 minutes, but with grace/adjustment = 240 minutes credited
-- PM: 13:00-18:09 = 309 minutes, but only 13:00-17:00 counted = 240 minutes credited
-- Total: 480 minutes (8 hours)

INSERT INTO `accredited_hours_log` 
(`attendance_id`, `employee_id`, `schedule_id`, `am_accredited_minutes`, `pm_accredited_minutes`, 
 `ot_minutes`, `late_minutes`, `undertime_minutes`, `total_accredited_minutes`, `total_actual_minutes`, 
 `am_grace_applied`, `pm_grace_applied`, `computation_notes`, `created_at`, `updated_at`)
VALUES 
(
  (SELECT id FROM attendance WHERE employee_id=9 AND date='2026-05-20'),  -- attendance_id
  9,  -- employee_id
  (SELECT id FROM schedules WHERE employee_id=9 AND '2026-05-20' BETWEEN start_date AND end_date LIMIT 1),  -- schedule_id
  240,  -- am_accredited_minutes (full 4 hours credited despite late arrival)
  240,  -- pm_accredited_minutes (13:00-17:00, grace applied)
  69,   -- ot_minutes (17:00-18:09 = 69 minutes overtime)
  120,  -- late_minutes (2 hours late: 08:00-10:00)
  0,    -- undertime_minutes (worked past 17:00)
  480,  -- total_accredited_minutes (8 hours)
  438,  -- total_actual_minutes (7.3 hours = 438 minutes)
  0,    -- am_grace_applied (no, arrived 2 hours late)
  1,    -- pm_grace_applied (yes, arrived at 13:00)
  'Manual entry for May 20, 2026 - Late 2 hours, PM grace applied, full 8 hours accredited',
  NOW(),
  NOW()
);
