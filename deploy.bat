@echo off
setlocal enabledelayedexpansion

REM ============================================
REM Bel Sekolah Digital - Git Commit, Push & Backup
REM ============================================
REM GitHub: https://github.com/dewecorp/bel_sekolah.git
REM Backup: overwrite (no timestamp)
REM Excludes: .bat files, .zip files
REM ============================================

set PROJECT_DIR=%~dp0
if "%PROJECT_DIR:~-1%"=="\" set PROJECT_DIR=%PROJECT_DIR:~0,-1%
set BACKUP_FILE=bel_sekolah_backup.zip
set GIT_REMOTE=https://github.com/dewecorp/bel_sekolah.git
set GIT_BRANCH=master

cd /d "%PROJECT_DIR%"

echo.
echo ============================================
echo  Bel Sekolah Digital - Auto Deploy ^& Backup
echo ============================================
echo.

REM Fix Windows "dubious ownership" error
git config --global --add safe.directory "%PROJECT_DIR%" 2>nul

REM ============================================
REM 1. GIT STATUS CHECK
REM ============================================
echo [1/5] Checking git status...
git -C "%PROJECT_DIR%" status --short
if errorlevel 1 (
    echo ERROR: Not a git repository or git not installed.
    pause
    exit /b 1
)

REM ============================================
REM 2. ENSURE REMOTE origin EXISTS
REM ============================================
echo [2/5] Checking git remote...
git -C "%PROJECT_DIR%" remote get-url origin >nul 2>nul
if errorlevel 1 (
    echo Adding remote origin...
    git -C "%PROJECT_DIR%" remote add origin "%GIT_REMOTE%"
    if errorlevel 1 (
        echo ERROR: Could not add remote origin.
        pause
        exit /b 1
    )
)

REM ============================================
REM 3. GIT ADD & COMMIT (WITH CONFIRMATION)
REM ============================================
echo [3/5] Adding all changes...
git -C "%PROJECT_DIR%" add -A

echo Checking for changes to commit...
git -C "%PROJECT_DIR%" diff --cached --quiet
if errorlevel 1 (
    echo.
    echo Enter commit message below. Just press Enter for auto message.
    set /p COMMIT_MSG=Commit message: 
    if "!COMMIT_MSG!"=="" set COMMIT_MSG=Auto commit: %DATE% %TIME%
    git -C "%PROJECT_DIR%" commit -m "!COMMIT_MSG!"
    if errorlevel 1 (
        echo ERROR: Commit failed.
        pause
        exit /b 1
    )
    echo Commit successful.
) else (
    echo No changes to commit.
)

REM ============================================
REM 4. GIT PUSH
REM ============================================
echo [4/5] Pushing to GitHub...
git -C "%PROJECT_DIR%" push origin %GIT_BRANCH%
if errorlevel 1 (
    echo WARNING: Push failed. Setting upstream and retrying...
    git -C "%PROJECT_DIR%" push -u origin %GIT_BRANCH%
    if errorlevel 1 (
        echo.
        echo ERROR: Push failed. Check credentials.
        echo.
        echo If prompted for username/password, use a GitHub Personal Access Token.
        echo To store credentials so push works automatically, run once:
        echo   git config --global credential.helper manager
        echo   git config --global user.name "dewecorp"
        echo   git config --global user.email "ibnuhasan3@gmail.com"
        pause
        exit /b 1
    )
)
echo Push successful.

REM ============================================
REM 5. CREATE BACKUP ZIP (OVERWRITE)
REM ============================================
echo [5/5] Creating backup zip (overwrite mode)...

REM Delete existing backup
if exist "%BACKUP_FILE%" del /q "%BACKUP_FILE%"

echo Creating backup zip...

REM Try 7-Zip first (most reliable on Windows)
if exist "C:\Program Files\7-Zip\7z.exe" (
    echo Creating backup with 7-Zip...
    "C:\Program Files\7-Zip\7z.exe" a -tzip "%BACKUP_FILE%" * -x@exclude.zlist
) else (
    echo Trying PowerShell...
    powershell -NoProfile -Command "Compress-Archive -Path * -DestinationPath '%BACKUP_FILE%' -Force -CompressionLevel Optimal -Exclude '*.bat','*.zip','.git*','node_modules*','vendor*','.env*','*.log','*.tmp','*.cache','Thumbs.db','desktop.ini'"
    if errorlevel 1 (
        echo ERROR: Neither 7-Zip nor PowerShell could be used.
        pause
        exit /b 1
    )
)

if exist "%BACKUP_FILE%" (
    for %%F in ("%BACKUP_FILE%") do set BACKUP_SIZE=%%~zF
    echo Backup created: %BACKUP_FILE% (!BACKUP_SIZE! bytes^)
) else (
    echo ERROR: Backup creation failed.
    pause
    exit /b 1
)

REM ============================================
REM 6. SUMMARY
REM ============================================
echo.
echo ============================================
echo  SUMMARY
echo ============================================
echo Project: %PROJECT_DIR%
echo Git Remote: %GIT_REMOTE%
echo Backup: %BACKUP_FILE%
if defined BACKUP_SIZE echo Backup Size: !BACKUP_SIZE! bytes
echo Branch: %GIT_BRANCH%
git -C "%PROJECT_DIR%" branch --show-current
echo Last Commit:
git -C "%PROJECT_DIR%" log -1 --oneline
echo ============================================
echo.

echo.
echo ============================================
echo  DEPLOY COMPLETE
echo ============================================
echo.
echo Tekan tombol apa saja untuk menutup window...
pause
endlocal
