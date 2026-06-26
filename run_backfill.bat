@echo off
echo ========================================
echo Backfilling Payroll Data
echo ========================================
echo.

echo Step 1: Populating accredited_hours_log...
mysql -u root -p primehrismagdalena < populate_accredited_hours_log.sql
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to populate accredited_hours_log
    pause
    exit /b 1
)
echo   ✓ Done!
echo.

echo Step 2: Generating daily_salary_computation...
php backfill_daily_salary_computations.php
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to generate daily salary computations
    pause
    exit /b 1
)
echo   ✓ Done!
echo.

echo ========================================
echo Backfill Complete!
echo ========================================
echo.
echo You can now generate payroll in the admin panel.
echo.
pause
