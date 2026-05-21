-- Create trigger to automatically populate accredited_hours_log when attendance is inserted
DROP TRIGGER IF EXISTS after_attendance_insert;

CREATE TRIGGER after_attendance_insert
AFTER INSERT ON attendance
FOR EACH ROW
BEGIN
    DECLARE v_schedule_id BIGINT UNSIGNED;
    DECLARE v_am_scheduled_in TIME;
    DECLARE v_am_scheduled_out TIME;
    DECLARE v_pm_scheduled_in TIME;
    DECLARE v_pm_scheduled_out TIME;
    DECLARE v_am_accredited INT DEFAULT 0;
    DECLARE v_pm_accredited INT DEFAULT 0;
    DECLARE v_ot_minutes INT DEFAULT 0;
    DECLARE v_late_minutes INT DEFAULT 0;
    DECLARE v_undertime_minutes INT DEFAULT 0;
    DECLARE v_total_actual INT DEFAULT 0;
    
    -- Get schedule for the employee
    SELECT id, am_in, am_out, pm_in, pm_out
    INTO v_schedule_id, v_am_scheduled_in, v_am_scheduled_out, v_pm_scheduled_in, v_pm_scheduled_out
    FROM schedules
    WHERE employee_id = NEW.employee_id
    AND (start_date IS NULL OR NEW.date >= start_date)
    AND (end_date IS NULL OR NEW.date <= end_date)
    LIMIT 1;
    
    -- Calculate AM accredited minutes (4 hours = 240 minutes if present)
    IF NEW.am_in IS NOT NULL AND NEW.am_out IS NOT NULL THEN
        SET v_am_accredited = 240;
        SET v_total_actual = v_total_actual + TIMESTAMPDIFF(MINUTE, NEW.am_in, NEW.am_out);
        
        -- Calculate late minutes for AM
        IF v_am_scheduled_in IS NOT NULL AND NEW.am_in > v_am_scheduled_in THEN
            SET v_late_minutes = v_late_minutes + TIMESTAMPDIFF(MINUTE, v_am_scheduled_in, NEW.am_in);
        END IF;
        
        -- Calculate undertime for AM
        IF v_am_scheduled_out IS NOT NULL AND NEW.am_out < v_am_scheduled_out THEN
            SET v_undertime_minutes = v_undertime_minutes + TIMESTAMPDIFF(MINUTE, NEW.am_out, v_am_scheduled_out);
        END IF;
    END IF;
    
    -- Calculate PM accredited minutes (4 hours = 240 minutes if present)
    IF NEW.pm_in IS NOT NULL AND NEW.pm_out IS NOT NULL THEN
        SET v_pm_accredited = 240;
        SET v_total_actual = v_total_actual + TIMESTAMPDIFF(MINUTE, NEW.pm_in, NEW.pm_out);
        
        -- Calculate late minutes for PM
        IF v_pm_scheduled_in IS NOT NULL AND NEW.pm_in > v_pm_scheduled_in THEN
            SET v_late_minutes = v_late_minutes + TIMESTAMPDIFF(MINUTE, v_pm_scheduled_in, NEW.pm_in);
        END IF;
        
        -- Calculate undertime for PM
        IF v_pm_scheduled_out IS NOT NULL AND NEW.pm_out < v_pm_scheduled_out THEN
            SET v_undertime_minutes = v_undertime_minutes + TIMESTAMPDIFF(MINUTE, NEW.pm_out, v_pm_scheduled_out);
        END IF;
    END IF;
    
    -- Calculate OT minutes
    IF NEW.ot_in IS NOT NULL AND NEW.ot_out IS NOT NULL THEN
        SET v_ot_minutes = TIMESTAMPDIFF(MINUTE, NEW.ot_in, NEW.ot_out);
        SET v_total_actual = v_total_actual + v_ot_minutes;
    END IF;
    
    -- Insert into accredited_hours_log
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
    ) VALUES (
        NEW.id,
        NEW.employee_id,
        v_schedule_id,
        v_am_accredited,
        v_pm_accredited,
        v_ot_minutes,
        v_late_minutes,
        v_undertime_minutes,
        v_am_accredited + v_pm_accredited,
        v_total_actual,
        NOW(),
        NOW()
    );
    
    -- Update attendance table with accredited_hours and total_hours
    UPDATE attendance
    SET 
        accredited_hours = (v_am_accredited + v_pm_accredited) / 60,
        total_hours = v_total_actual / 60
    WHERE id = NEW.id;
END;
