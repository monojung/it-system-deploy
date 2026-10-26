@echo off
chcp 65001 >nul
title ติดตั้ง THC IT Agent v2.12.0 - โรงพยาบาลทุ่งหัวช้าง
color 0b

set "IS_SILENT=0"
if /i "%~1"=="/silent" set "IS_SILENT=1"
if /i "%~1"=="/quiet" set "IS_SILENT=1"
if /i "%~1"=="-Silent" set "IS_SILENT=1"
if /i "%~2"=="/silent" set "IS_SILENT=1"
if /i "%~2"=="/quiet" set "IS_SILENT=1"

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
set "SERVER_URL=https://thchospital.moph.go.th/it-system"
if not "%~1"=="" if "%IS_SILENT%"=="0" set "SERVER_URL=%~1"

if "%IS_SILENT%"=="0" (
    echo ==========================================================================
    echo   โรงพยาบาลทุ่งหัวช้าง - ระบบติดตั้ง THC IT Agent v2.12.0 (Production)
    echo   THC Hardware Audit Agent - Enterprise System Tray Service
    echo   เชื่อมต่อเซิร์ฟเวอร์แบบ Real-time รายงานสเปก ปลอดภัย ไม่ติด Blocklist
    echo ==========================================================================
    echo.
    echo เซิร์ฟเวอร์เป้าหมาย: %SERVER_URL%
    echo ตำแหน่งติดตั้ง:      %INSTALL_DIR%
    echo.
)

:: ตรวจสอบสคริปต์ install.ps1 ในโฟลเดอร์เดียวกัน
if exist "%~dp0install.ps1" (
    if "%IS_SILENT%"=="1" (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install.ps1" -ServerUrl "%SERVER_URL%" -InstallDir "%INSTALL_DIR%" -Silent
    ) else (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0install.ps1" -ServerUrl "%SERVER_URL%" -InstallDir "%INSTALL_DIR%"
    )
    goto :COMPLETED
)

:: กรณีรันเดี่ยวๆ ไม่มี install.ps1 ให้ดำเนินการติดตั้งแบบ Native Batch
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%" 2>nul

if "%IS_SILENT%"=="0" echo [1/3] กำลังบันทึกการตั้งค่าระบบ (Heartbeat 60s)...
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$cfg = @{ ServerUrl = '%SERVER_URL%'; HeartbeatIntervalSec = 60; AutoStart = $true } | ConvertTo-Json; [IO.File]::WriteAllText('%INSTALL_DIR%\config.json', $cfg, [Text.Encoding]::UTF8)" 2>nul

if "%IS_SILENT%"=="0" echo [2/3] กำลังเตรียมไฟล์โปรแกรม Agent...
taskkill /f /im THC_IT_Agent.exe 2>nul

if exist "%~dp0THC_IT_Agent.exe" (
    copy /y "%~dp0THC_IT_Agent.exe" "%INSTALL_DIR%\THC_IT_Agent.exe" >nul
) else (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; (New-Object Net.WebClient).DownloadFile('%SERVER_URL%/agent/THC_IT_Agent.exe', '%INSTALL_DIR%\THC_IT_Agent.exe')" 2>nul
)

if exist "%~dp0uninstall_thc_agent.bat" copy /y "%~dp0uninstall_thc_agent.bat" "%INSTALL_DIR%\uninstall.bat" >nul
if exist "%~dp0update_thc_agent.bat" copy /y "%~dp0update_thc_agent.bat" "%INSTALL_DIR%\update.bat" >nul

if "%IS_SILENT%"=="0" echo [3/3] ตั้งค่าเริ่มทำงานอัตโนมัติพร้อม Windows...
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /t REG_SZ /d "\"%INSTALL_DIR%\THC_IT_Agent.exe\"" /f >nul 2>nul
reg add "HKCU\Software\THC-IT-Agent" /v "Installed" /t REG_DWORD /d 1 /f >nul 2>nul
reg add "HKCU\Software\THC-IT-Agent" /v "InstallPath" /t REG_SZ /d "%INSTALL_DIR%" /f >nul 2>nul
reg add "HKCU\Software\THC-IT-Agent" /v "Version" /t REG_SZ /d "2.12.0" /f >nul 2>nul

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut([Environment]::GetFolderPath('Desktop') + '\THC IT Agent.lnk'); $s.TargetPath = '%INSTALL_DIR%\THC_IT_Agent.exe'; $s.Description = 'THC IT Agent - รพ.ทุ่งหัวช้าง'; $s.Save(); $startupLnk = [Environment]::GetFolderPath('Startup') + '\THC IT Agent.lnk'; if (Test-Path $startupLnk) { Remove-Item $startupLnk -Force -ErrorAction SilentlyContinue }" 2>nul

start "" "%INSTALL_DIR%\THC_IT_Agent.exe"

:COMPLETED
if "%IS_SILENT%"=="0" (
    echo.
    echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
    pause >nul
)
