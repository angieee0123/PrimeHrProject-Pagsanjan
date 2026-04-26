@echo off
echo ========================================
echo Stopping PrimeHR System Services
echo ========================================
echo.

taskkill /FI "WindowTitle eq App.py*" /T /F 2>nul
taskkill /FI "WindowTitle eq App Improved*" /T /F 2>nul
taskkill /FI "WindowTitle eq Chatbot Database*" /T /F 2>nul
taskkill /FI "WindowTitle eq QR Attendance*" /T /F 2>nul
taskkill /FI "WindowTitle eq Laravel Server*" /T /F 2>nul
taskkill /FI "WindowTitle eq Vite Dev Server*" /T /F 2>nul

echo.
echo ========================================
echo All services stopped!
echo ========================================
echo.
pause
