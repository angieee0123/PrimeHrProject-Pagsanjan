-- Migration: Restore OT columns to attendance table
-- Date: 2024
-- Description: Adds back ot_in and ot_out columns for overtime tracking

-- Add the OT columns back
ALTER TABLE attendance 
ADD COLUMN ot_in TIME NULL AFTER pm_out,
ADD COLUMN ot_out TIME NULL AFTER ot_in;

-- Verify the change
DESCRIBE attendance;
