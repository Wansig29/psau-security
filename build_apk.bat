@echo off
cd /d "%~dp0"

echo ===========================================
echo Building Flutter APK for PSAU Parking System
echo ===========================================

cd psau_parking_system
if errorlevel 1 goto error

echo.
echo Running "flutter build apk"...
call C:\Users\Janssen\.puro\envs\stable\flutter\bin\flutter.bat build apk
if errorlevel 1 goto build_error

echo.
echo Copying APK to Laravel public directory...
copy /Y "build\app\outputs\flutter-apk\app-release.apk" "..\public\psau_parking.apk"
if errorlevel 1 goto copy_error

echo.
echo ===========================================
echo SUCCESS! APK has been copied to public/psau_parking.apk
echo You can now commit and deploy to Railway.
echo ===========================================
cd ..
pause
exit /b 0

:build_error
echo.
echo [ERROR] Flutter build failed. Ensure flutter is installed and in your PATH.
cd ..
pause
exit /b 1

:copy_error
echo.
echo [ERROR] Failed to copy the APK file.
cd ..
pause
exit /b 1

:error
echo.
echo [ERROR] Could not find the psau_parking_system directory.
pause
exit /b 1
