@echo off
chcp 65001 >nul
title อัปเดต THC IT Agent v2.12.0 - โรงพยาบาลทุ่งหัวช้าง
color 0a

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
set "SERVER_URL="
set "IS_SILENT=0"

:: Check arguments for /silent or flags
for %%A in (%*) do (
    if /i "%%~A"=="/silent" set "IS_SILENT=1"
    if /i "%%~A"=="-silent" set "IS_SILENT=1"
    if /i "%%~A"=="/quiet" set "IS_SILENT=1"
    if /i "%%~A"=="-quiet" set "IS_SILENT=1"
    if /i "%%~A"=="-q" set "IS_SILENT=1"
    if /i "%%~A"=="/q" set "IS_SILENT=1"
    if /i "%%~A"=="-s" set "IS_SILENT=1"
    if /i "%%~A"=="/s" set "IS_SILENT=1"
)

:: Extract URL from arguments if passed
if not "%~1"=="" (
    set "ARG1=%~1"
    if /i "%ARG1:~0,4%"=="http" set "SERVER_URL=%~1"
)
if not "%~2"=="" (
    set "ARG2=%~2"
    if /i "%ARG2:~0,4%"=="http" set "SERVER_URL=%~2"
)

:: Fallback to local config.json if server url not passed in arguments
if "%SERVER_URL%"=="" (
    if exist "%INSTALL_DIR%\config.json" (
        for /f "usebackq delims=" %%u in (`powershell.exe -NoProfile -Command "try { (Get-Content '%INSTALL_DIR%\config.json' -Raw | ConvertFrom-Json).ServerUrl } catch {}" 2^>nul`) do (
            if not "%%u"=="" set "SERVER_URL=%%u"
        )
    )
)

if "%SERVER_URL%"=="" set "SERVER_URL=https://thchospital.moph.go.th/it-system"

if "%IS_SILENT%"=="0" (
    echo ==========================================================================
    echo   โรงพยาบาลทุ่งหัวช้าง - ระบบอัปเดต THC IT Agent ล่าสุด (Auto-Updater)
    echo ==========================================================================
    echo.
    echo เซิร์ฟเวอร์: %SERVER_URL%
    echo กำลังดาวน์โหลดและอัปเดตเป็นเวอร์ชันล่าสุด...
    echo.
)

:: Try to download update.ps1 if not already next to this batch file
if not exist "%~dp0update.ps1" (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
        "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " ^
        "(New-Object Net.WebClient).DownloadFile('%SERVER_URL%/agent/update.ps1', '%~dp0update.ps1')" >nul 2>&1
)

:: If update.ps1 exists, delegate to it for secure SHA-256 validation
if exist "%~dp0update.ps1" (
    if "%IS_SILENT%"=="1" (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0update.ps1" -ServerUrl "%SERVER_URL%" -InstallDir "%INSTALL_DIR%" -Silent
    ) else (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0update.ps1" -ServerUrl "%SERVER_URL%" -InstallDir "%INSTALL_DIR%"
    )
    goto :COMPLETED
)

:: Native Atomic Update Fallback
set "TARGET_EXE=%INSTALL_DIR%\THC_IT_Agent.exe"
set "NEW_EXE=%INSTALL_DIR%\THC_IT_Agent.exe.new"
set "OLD_EXE=%INSTALL_DIR%\THC_IT_Agent.exe.old"

if "%IS_SILENT%"=="0" echo [1/3] กำลังดาวน์โหลดไฟล์เวอร์ชันใหม่...
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
    "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " ^
    "(New-Object Net.WebClient).DownloadFile('%SERVER_URL%/agent/THC_IT_Agent.exe', '%NEW_EXE%')" 2>nul

if not exist "%NEW_EXE%" (
    if "%IS_SILENT%"=="0" echo [ผิดพลาด] ดาวน์โหลดไม่สำเร็จ กรุณาตรวจสอบการเชื่อมต่อ
    exit /b 1
)

for %%F in ("%NEW_EXE%") do (
    if %%~zF LSS 20000 (
        del /f /q "%NEW_EXE%" 2>nul
        if "%IS_SILENT%"=="0" echo [ผิดพลาด] ขนาดไฟล์ไม่ถูกต้อง
        exit /b 1
    )
)

if "%IS_SILENT%"=="0" echo [2/3] กำลังปิดโปรแกรมเดิมและสลับไฟล์...
taskkill /f /im THC_IT_Agent.exe >nul 2>&1
timeout /t 2 /nobreak >nul

if exist "%OLD_EXE%" del /f /q "%OLD_EXE%" >nul 2>&1
if exist "%TARGET_EXE%" move /y "%TARGET_EXE%" "%OLD_EXE%" >nul 2>&1
move /y "%NEW_EXE%" "%TARGET_EXE%" >nul 2>&1

if "%IS_SILENT%"=="0" echo [3/3] เปิดใช้งาน Agent เวอร์ชันใหม่...
start "" "%TARGET_EXE%" /update-post /silent

:COMPLETED
if "%IS_SILENT%"=="0" (
    echo.
    echo อัปเดตเสร็จสมบูรณ์แล้ว! กดปุ่มใดๆ เพื่อปิดหน้าต่าง...
    pause >nul
)
