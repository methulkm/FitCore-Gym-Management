-- Run this ONCE if your `fitcore` database already exists from before (i.e. you don't want to
-- re-import schema.sql and lose your test data). Adds: employee check-out time, used to show hours worked.
USE fitcore;

ALTER TABLE employee_attendance
    ADD COLUMN IF NOT EXISTS check_out_time TIME NULL AFTER check_in_time;
