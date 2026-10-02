@echo off
chcp 65001 >nul
title อัปเดต THC Hardware Audit Agent - โรงพยาบาลทุ่งหัวช้าง
color 0a

set "IS_SILENT=0"
if /i "%~1"=="/silent" set "IS_SILENT=1"
if /i "%~1"=="/quiet" set "IS_SILENT=1"
if /i "%~1"=="-Silent" set "IS_SILENT=1"
if /i "%~2"=="/silent" set "IS_SILENT=1"
if /i "%~2"=="/quiet" set "IS_SILENT=1"

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
set "SERVER_URL=https://thchospital.moph.go.th/it-system"

:: อ่าน ServerUrl จาก config.json ถ้ามี
if exist "%INSTALL_DIR%\config.json" (
    for /f "tokens=2 delims=:, " %%a in ('type "%INSTALL_DIR%\config.json" ^| findstr /i "ServerUrl"') do (
        set "SERVER_URL=%%~a"
    )
)
if not "%~1"=="" if not "%~1"=="/silent" if not "%~1"=="/quiet" if not "%~1"=="-Silent" (
    set "SERVER_URL=%~1"
)

if "%IS_SILENT%"=="0" (
    echo ==========================================================================
    echo   อัปเดต THC Hardware Audit Agent - โรงพยาบาลทุ่งหัวช้าง
    echo   (Thung Hua Chang Hospital IT Platform - Agent Auto-Updater)
    echo ==========================================================================
    echo.
    echo กำลังตรวจสอบและดาวน์โหลด Agent เวอร์ชันล่าสุดจากเซิร์ฟเวอร์ (%SERVER_URL%)...
    echo.
)

if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%" 2>nul

:: 1. ดาวน์โหลดไฟล์เวอร์ชันใหม่ลงใน Staging Directory
if "%IS_SILENT%"=="0" echo [1/4] กำลังดาวน์โหลดไฟล์โปรแกรมเวอร์ชันใหม่...
set "STAGING_DIR=%TEMP%\THC_Agent_Update"
if exist "%STAGING_DIR%" rmdir /s /q "%STAGING_DIR%" 2>nul
mkdir "%STAGING_DIR%" 2>nul

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
    "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " ^
    "[Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}; " ^
    "$urls = @('%SERVER_URL%/agent/THC_IT_Agent.exe', 'https://thchospital.moph.go.th/it-system/agent/THC_IT_Agent.exe', 'http://192.168.2.89:8000/agent/THC_IT_Agent.exe', 'http://192.168.2.89/it-system/agent/THC_IT_Agent.exe', 'http://localhost:8000/agent/THC_IT_Agent.exe'); " ^
    "$downloaded = $false; " ^
    "foreach ($u in $urls) { " ^
    "    try { " ^
    "        $wc = New-Object Net.WebClient; " ^
    "        $wc.DownloadFile($u, '%STAGING_DIR%\THC_IT_Agent.exe'); " ^
    "        if ((Get-Item '%STAGING_DIR%\THC_IT_Agent.exe').Length -gt 50000) { $downloaded = $true; break } " ^
    "    } catch {} " ^
    "} " ^
    "if (-not $downloaded) { exit 1 }" 2>nul

if not exist "%STAGING_DIR%\THC_IT_Agent.exe" (
    if "%IS_SILENT%"=="0" (
        echo [ผิดพลาด] ไม่สามารถดาวน์โหลดไฟล์ Agent เวอร์ชันใหม่ได้
        echo กรุณาตรวจสอบการเชื่อมต่อเครือข่าย หรือติดต่อผู้ดูแลระบบไอที
        echo.
        pause
    )
    exit /b 1
)

:: ดาวน์โหลดไฟล์สคริปต์เสริม (PowerShell agent, Uninstaller)
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
    "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " ^
    "[Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}; " ^
    "$base = '%SERVER_URL%/agent'; " ^
    "$wc = New-Object Net.WebClient; " ^
    "try { $wc.DownloadFile($base + '/uninstall_thc_agent.bat', '%STAGING_DIR%\uninstall.bat') } catch {}; " ^
    "try { $wc.DownloadFile($base + '/thc_audit_agent.ps1', '%STAGING_DIR%\thc_audit_agent.ps1') } catch {}; " 2>nul

:: 2. หยุดการทำงานของโปรแกรมเดิม
if "%IS_SILENT%"=="0" echo [2/4] กำลังปิด Agent เวอร์ชันเดิม...
taskkill /f /im THC_IT_Agent.exe 2>nul
timeout /t 1 /nobreak >nul

:: 3. ทำการสับเปลี่ยนไฟล์โปรแกรม
if "%IS_SILENT%"=="0" echo [3/4] กำลังติดตั้งไฟล์เวอร์ชันใหม่...
copy /y "%STAGING_DIR%\THC_IT_Agent.exe" "%INSTALL_DIR%\THC_IT_Agent.exe" >nul
if exist "%STAGING_DIR%\uninstall.bat" copy /y "%STAGING_DIR%\uninstall.bat" "%INSTALL_DIR%\uninstall.bat" >nul
if exist "%STAGING_DIR%\thc_audit_agent.ps1" copy /y "%STAGING_DIR%\thc_audit_agent.ps1" "%INSTALL_DIR%\thc_audit_agent.ps1" >nul
copy /y "%~f0" "%INSTALL_DIR%\update.bat" >nul 2>nul
rmdir /s /q "%STAGING_DIR%" 2>nul

:: ตรวจสอบและอัปเดตทางลัดให้ครบถ้วน
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
    "$ws = New-Object -ComObject WScript.Shell; " ^
    "$s = $ws.CreateShortcut([Environment]::GetFolderPath('Desktop') + '\THC IT Agent.lnk'); " ^
    "$s.TargetPath = '%INSTALL_DIR%\THC_IT_Agent.exe'; " ^
    "$s.Description = 'THC IT Agent - รพ.ทุ่งหัวช้าง'; " ^
    "$s.Save(); " ^
    "$s2 = $ws.CreateShortcut([Environment]::GetFolderPath('Startup') + '\THC IT Agent.lnk'); " ^
    "$s2.TargetPath = '%INSTALL_DIR%\THC_IT_Agent.exe'; " ^
    "$s2.Save();" 2>nul

:: 4. เริ่มการทำงานของ Agent เวอร์ชันใหม่
if "%IS_SILENT%"=="0" echo [4/4] กำลังเปิดใช้งาน THC IT Agent เวอร์ชันใหม่...
start "" "%INSTALL_DIR%\THC_IT_Agent.exe"

if "%IS_SILENT%"=="0" (
    echo.
    echo ==========================================================================
    echo   [สำเร็จ] อัปเดต THC IT Agent เวอร์ชันล่าสุดเรียบร้อยแล้ว!
    echo   - โปรแกรมเริ่มทำงานใหม่ใน System Tray แล้ว
    echo   - รองรับฟีเจอร์ตรวจเช็คอัตโนมัติและคำสั่งควบคุมจากส่วนกลาง
    echo ==========================================================================
    echo.
    timeout /t 3 >nul
)
exit /b 0
