@echo off
echo ========================================
echo  Flask AI Resume Analyzer API
echo ========================================
echo.
cd /d C:\Users\azi\Desktop\UR\AI-Resume-Analyzer\App
if errorlevel 1 (
    echo ERROR: Could not change to AI directory!
    pause
    exit /b 1
)
echo Current directory: %CD%
echo.
echo Activating virtual environment...
call venv39\Scripts\activate.bat
if errorlevel 1 (
    echo ERROR: Could not activate virtual environment!
    echo Make sure venv39 exists in the App folder.
    pause
    exit /b 1
)
echo.
    echo ========================================
    echo  Starting Flask API...
    echo  URL: http://127.0.0.1:8502
    echo ========================================
    echo.
    echo Keep this window open while using the app!
    echo Press Ctrl+C to stop the server
    echo.
    echo Starting NEW Flask API (flask_api_new.py)...
    echo.
    python flask_api_new.py
pause

