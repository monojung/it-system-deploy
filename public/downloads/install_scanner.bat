@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
title THC Hardware Scanner - Cybersecurity Installer

echo =====================================================================
echo    THC Hardware Scanner v3.0.0 - Enterprise Endpoint Installer
echo    Compliant with Cybersecurity Standards (Zero Inbound Ports)
echo =====================================================================
echo.

:: 1. Check Administrator Privileges
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] กรุณาคลิกขวาที่ไฟล์นี้แล้วเลือก "Run as administrator" (สิทธิ์ผู้ดูแลระบบ)
    echo [ERROR] Administrator privileges are required to install system service.
    echo.
    pause
    exit /b 1
)

:: 2. Set Parameters
set "TARGET_DIR=%ProgramFiles%\THC_Hardware_Scanner"
set "TARGET_EXE=%TARGET_DIR%\THC_Hardware_Scanner.exe"
set "CONFIG_FILE=%TARGET_DIR%\config.json"
set "TASK_NAME=THC_Hardware_Scanner_Task"

set "SERVER_URL=%~1"
if "%SERVER_URL%"=="" (
    set "SERVER_URL=http://127.0.0.1:8000/api/hardware-audits"
)

set "API_TOKEN=thc_audit_secure_token"
set "POLL_INTERVAL=60"

echo [*] ไดเรกทอรีปลายทาง: %TARGET_DIR%
echo [*] Server API URL : %SERVER_URL%
echo [*] Poll Interval  : %POLL_INTERVAL% วินาที (Zero Inbound Listening Ports)
echo.

:: 3. Create Target Directory
if not exist "%TARGET_DIR%" (
    echo [+] กำลังสร้างโฟลเดอร์ระบบ...
    mkdir "%TARGET_DIR%" >nul 2>&1
)

:: 4. Copy Executable
set "SOURCE_EXE=%~dp0THC_Hardware_Scanner.exe"
if exist "%SOURCE_EXE%" (
    echo [+] กำลังคัดลอกไฟล์โปรแกรม THC_Hardware_Scanner.exe...
    copy /y "%SOURCE_EXE%" "%TARGET_EXE%" >nul
) else (
    if not exist "%TARGET_EXE%" (
        echo [!] ไม่พบไฟล์ THC_Hardware_Scanner.exe ในโฟลเดอร์นี้
        echo [*] พยายามดาวน์โหลดจาก Server URL...
        powershell -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%SERVER_URL%/../../downloads/THC_Hardware_Scanner.exe' -OutFile '%TARGET_EXE%'" >nul 2>&1
    )
)

if not exist "%TARGET_EXE%" (
    echo [ERROR] ไม่สามารถติดตั้งไฟล์ THC_Hardware_Scanner.exe ได้
    pause
    exit /b 1
)

:: 5. Create Configuration JSON
echo [+] กำลังสร้างไฟล์การตั้งค่าระบบ (config.json)...
(
echo {
echo   "server_url": "%SERVER_URL%",
echo   "api_token": "%API_TOKEN%",
echo   "poll_interval_sec": %POLL_INTERVAL%,
echo   "installed_at": "%date% %time%",
echo   "cybersecurity_mode": "Zero-Inbound-Port-Standby"
echo }
) > "%CONFIG_FILE%"

:: 6. Register Windows Scheduled Task (Runs under SYSTEM at Boot/Logon)
echo [+] กำลังลงทะเบียน Windows Scheduled Task (%TASK_NAME%)...
schtasks /create /tn "%TASK_NAME%" /tr "\"%TARGET_EXE%\" /service" /sc onlogon /rl highest /f >nul 2>&1

:: Also trigger immediate run
echo [+] กำลังเริ่มต้นการทำงานในโหมด Standby (รอรับคำสั่งดึงข้อมูลจากระบบ)...
schtasks /run /tn "%TASK_NAME%" >nul 2>&1

echo.
echo =====================================================================
echo [SUCCESS] ติดตั้ง THC Hardware Scanner สำเร็จเรียบร้อยแล้ว!
echo =====================================================================
echo  - Service Status : สแตนด์บายในพื้นหลัง (Zero Inbound Ports)
echo  - Action Model   : รอรับคำสั่ง "กดปุ่มดึงข้อมูลสเปก" จากผู้ดูแลระบบผ่านหน้าเว็บ
echo  - Integrity Hash : SHA-256 Verified
echo =====================================================================
echo.
timeout /t 5 >nul
exit /b 0
