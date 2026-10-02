@echo off
chcp 65001 >nul
title ถอนการติดตั้ง THC Hardware Audit Agent - โรงพยาบาลทุ่งหัวช้าง
color 0c

set "IS_SILENT=0"
if /i "%~1"=="/silent" set "IS_SILENT=1"
if /i "%~1"=="/quiet" set "IS_SILENT=1"
if /i "%~1"=="-Silent" set "IS_SILENT=1"

if "%IS_SILENT%"=="0" (
    echo ==========================================================================
    echo   ถอนการติดตั้ง THC Hardware Audit Agent - โรงพยาบาลทุ่งหัวช้าง
    echo   (Thung Hua Chang Hospital IT Platform - Agent Uninstaller)
    echo ==========================================================================
    echo.
    echo ระบบกำลังดำเนินการถอนการติดตั้งโปรแกรมและล้างการตั้งค่าทั้งหมดออกจากเครื่องนี้...
    echo.
)

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"

:: 1. ปิดโปรเซสที่กำลังทำงานทั้งหมด
if "%IS_SILENT%"=="0" echo [1/5] กำลังหยุดการทำงานของ THC IT Agent...
taskkill /f /im THC_IT_Agent.exe 2>nul
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*thc_audit_agent.ps1*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>nul

:: 2. ถอดการเริ่มทำงานอัตโนมัติ (Startup Registry)
if "%IS_SILENT%"=="0" echo [2/5] กำลังลบการตั้งค่าเริ่มทำงานอัตโนมัติ (Startup Registry)...
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /f >nul 2>nul
reg delete "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /f >nul 2>nul

:: 3. ลบ Scheduled Tasks
if "%IS_SILENT%"=="0" echo [3/5] กำลังลบงานตามตารางเวลา (Scheduled Tasks)...
schtasks /delete /tn "THC_Hardware_Audit" /f >nul 2>nul
schtasks /delete /tn "THC_Hardware_Audit_Poll" /f >nul 2>nul

:: 4. ลบทางลัด (Shortcuts) บน Desktop และ Startup
if "%IS_SILENT%"=="0" echo [4/5] กำลังลบทางลัด (Shortcuts)...
del /f /q "%USERPROFILE%\Desktop\THC IT Agent.lnk" 2>nul
del /f /q "%PUBLIC%\Desktop\THC IT Agent.lnk" 2>nul
del /f /q "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk" 2>nul
del /f /q "%ALLUSERSPROFILE%\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk" 2>nul

:: 5. ลบไฟล์และโฟลเดอร์ติดตั้ง
if "%IS_SILENT%"=="0" echo [5/5] กำลังลบไฟล์โปรแกรมและโฟลเดอร์ข้อมูล...
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Remove-Item -Path '$env:LOCALAPPDATA\THC-IT-Agent' -Recurse -Force -ErrorAction SilentlyContinue" 2>nul

:: หากไฟล์ .bat นี้รันอยู่ข้างนอก ให้ลบ ProgramData ทันที แต่ถ้าอยู่ใน ProgramData ให้ spawn คำสั่งลบเบื้องหลัง
if exist "%INSTALL_DIR%" (
    start "" /b cmd /c "timeout /t 2 /nobreak >nul & rmdir /s /q \"%INSTALL_DIR%\" 2>nul"
)

if "%IS_SILENT%"=="0" (
    echo.
    echo ==========================================================================
    echo   [สำเร็จ] ถอนการติดตั้ง THC IT Agent เรียบร้อยสมบูรณ์แล้ว!
    echo   - ปิดโปรแกรมและหยุดการทำงานทั้งหมด
    echo   - ถอน Startup และ Scheduled Tasks เรียบร้อย
    echo   - ลบไฟล์โปรแกรมออกจากเครื่องเรียบร้อย
    echo ==========================================================================
    echo.
    echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
    pause >nul
)
exit /b 0
