@echo off
chcp 65001 >nul
title อัปเดต THC IT Agent v2.11.0 - โรงพยาบาลทุ่งหัวช้าง
color 0a

set "IS_SILENT=0"
if /i "%~1"=="/silent" set "IS_SILENT=1"
if /i "%~1"=="/quiet" set "IS_SILENT=1"
if /i "%~1"=="-Silent" set "IS_SILENT=1"
if /i "%~2"=="/silent" set "IS_SILENT=1"
if /i "%~2"=="/quiet" set "IS_SILENT=1"

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
set "SERVER_URL=https://thchospital.moph.go.th/it-system"

if exist "%INSTALL_DIR%\config.json" (
    for /f "tokens=2 delims=:, " %%a in ('type "%INSTALL_DIR%\config.json" ^| findstr /i "ServerUrl"') do (
        set "SERVER_URL=%%~a"
    )
)
if not "%~1"=="" if "%IS_SILENT%"=="0" set "SERVER_URL=%~1"

if "%IS_SILENT%"=="0" (
    echo ==========================================================================
    echo   โรงพยาบาลทุ่งหัวช้าง - ระบบอัปเดต THC IT Agent ล่าสุด (Auto-Updater)
    echo ==========================================================================
    echo.
    echo เซิร์ฟเวอร์: %SERVER_URL%
    echo กำลังดาวน์โหลดและอัปเดตเป็นเวอร์ชันล่าสุด...
    echo.
)

:: ถ้ามี update.ps1 ให้เรียกใช้งานเพื่อความแม่นยำและตรวจสอบ Hash
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

if "%IS_SILENT%"=="0" echo [2/3] กำลังปิดโปรแกรมเดิมและสลับไฟล์...
taskkill /f /im THC_IT_Agent.exe 2>nul
timeout /t 1 /nobreak >nul

if exist "%OLD_EXE%" del /f /q "%OLD_EXE%" 2>nul
if exist "%TARGET_EXE%" move /y "%TARGET_EXE%" "%OLD_EXE%" >nul
move /y "%NEW_EXE%" "%TARGET_EXE%" >nul

if "%IS_SILENT%"=="0" echo [3/3] เปิดใช้งาน Agent เวอร์ชันใหม่...
start "" "%TARGET_EXE%"

:COMPLETED
if "%IS_SILENT%"=="0" (
    echo.
    echo อัปเดตเสร็จสมบูรณ์แล้ว! กดปุ่มใดๆ เพื่อปิดหน้าต่าง...
    pause >nul
)
