@echo off
echo Starting PrimeHR Applications...

start "App.py" cmd /k "python app.py"
start "App Improved" cmd /k "python app_improved.py"
start "Chatbot Database" cmd /k "python chatbot_to_database.py"
start "QR Attendance" cmd /k "python qr_attendance.py"
start "Laravel NPM" cmd /k "cd primeHrMagdalenaLaravel && npm run dev"
start "Laravel Server" cmd /k "cd primeHrMagdalenaLaravel && php artisan serve"

echo All applications started in separate windows.
pause
