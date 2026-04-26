@echo off
echo ========================================
echo Starting PrimeHR System Services
echo ========================================
echo.

cd /d "%~dp0"

start "App.py" cmd /k "python app.py"
timeout /t 2 /nobreak >nul

start "App Improved" cmd /k "python app_improved.py"
timeout /t 2 /nobreak >nul

start "Chatbot Database" cmd /k "python chatbot_to_database.py"
timeout /t 2 /nobreak >nul

start "QR Attendance" cmd /k "python qr_attendance.py"
timeout /t 2 /nobreak >nul

cd primeHrMagdalenaLaravel
start "Laravel Server" cmd /k "php artisan serve"
timeout /t 2 /nobreak >nul

start "Vite Dev Server" cmd /k "npm run dev"

echo.
echo ========================================
echo All services started successfully!
echo ========================================
echo.
echo Press any key to exit this window...
pause >nul
