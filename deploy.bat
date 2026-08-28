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
set BACKUP_FILE=bel_sekolah_backup.zip
set GIT_REMOTE=https://github.com/dewecorp/bel_sekolah.git
set GIT_BRANCH=master

cd /d "%PROJECT_DIR%"

echo.
echo ============================================
echo  Bel Sekolah Digital - Auto Deploy & Backup
echo ============================================
echo.

REM ============================================
REM 1. GIT STATUS CHECK
REM ============================================
echo [1/5] Checking git status...
git status --short
if errorlevel 1 (
    echo ERROR: Not a git repository or git not installed.
    pause
    exit /b 1
)

REM ============================================
REM 2. GIT ADD & COMMIT
REM ============================================
echo [2/5] Adding all changes...
git add -A

echo Checking for changes to commit...
git diff --cached --quiet
if errorlevel 1 (
    echo Committing with auto message...
    git commit -m "Auto commit: %DATE% %TIME%"
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
REM 3. GIT PUSH
REM ============================================
echo [3/5] Pushing to GitHub...
git push %GIT_REMOTE% %GIT_BRANCH%
if errorlevel 1 (
    echo WARNING: Push failed. Trying to set upstream...
    git push -u %GIT_REMOTE% %GIT_BRANCH%
    if errorlevel 1 (
        echo ERROR: Push failed. Check credentials/connection.
        pause
        exit /b 1
    )
)
echo Push successful.

REM ============================================
REM 4. CREATE BACKUP ZIP (OVERWRITE)
REM ============================================
echo [4/5] Creating backup zip (overwrite mode)...

REM Delete existing backup
if exist "%BACKUP_FILE%" del /q "%BACKUP_FILE%"

echo Creating backup zip...

REM Try 7-Zip first (most reliable on Windows)
if exist "C:\Program Files\7-Zip\7z.exe" (
    echo Creating backup with 7-Zip...
    "C:\Program Files\7-Zip\7z.exe" a -tzip "%BACKUP_FILE%" * -x!*.bat -x!*.zip -x!.git -x!node_modules -x!vendor -x!.env* -x!*.log -x!*.tmp -x!*.cache -x!Thumbs.db -x!desktop.ini
) else (
    echo Trying PowerShell...
    powershell -NoProfile -Command ^
        "Compress-Archive -Path * -DestinationPath '%BACKUP_FILE%' -Force -CompressionLevel Optimal ^
         -Exclude @('*.bat', '*.zip', '.git*', 'node_modules*', 'vendor*', '.env*', '*.log', '*.tmp', '*.cache', 'Thumbs.db', 'desktop.ini'); ^
         Write-Host 'Backup created successfully.'"
    
    if errorlevel 1 (
        echo ERROR: Both 7-Zip and PowerShell zip failed.
        pause
        exit /b 1
    )
)

if exist "%BACKUP_FILE%" (
    for %%F in ("%BACKUP_FILE%") do set BACKUP_SIZE=%%~zF
    echo Backup created: %BACKUP_FILE% (!BACKUP_SIZE! bytes)
) else (
    echo ERROR: Backup creation failed.
    pause
    exit /b 1
)

REM ============================================
REM 5. SUMMARY
REM ============================================
echo.
echo ============================================
echo  SUMMARY
echo ============================================
echo Project: %PROJECT_DIR%
echo Git Remote: %GIT_REMOTE%
echo Backup: %BACKUP_FILE%
if defined BACKUP_SIZE echo Backup Size: %BACKUP_SIZE! bytes
echo Branch: %GIT_BRANCH%
git branch --show-current
echo Last Commit:
git log -1 --oneline
echo ============================================
echo.

echo.
echo ============================================
echo  DEPLOY COMPLETE
echo ============================================
echo.
pause
endlocal