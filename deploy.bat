@echo off
color 0B
echo =======================================================
echo     PSAU Parking System - Easy GitHub Deployer
echo =======================================================
echo.

set /p commit_msg="Enter a short description of your changes (e.g., 'fixed login link'): "

echo.
echo [1/3] Adding changes to tracking...
git add .

echo.
echo [2/3] Tracing commit history...
git commit -m "%commit_msg%"

echo.
echo [3/3] Pushing to cloud... (This triggers Railway deploy)
git push

echo.
echo =======================================================
echo  SUCCESS!
echo =======================================================
echo  Your website is now deploying to Railway!
echo.
echo  Note: If you made changes to the mobile app folder 
echo  (psau_parking_system), GitHub Actions is secretly 
echo  building your new APK right now. Once it's done, 
echo  it will automatically attach it to the website so 
echo  users get the latest version!
echo =======================================================
pause
