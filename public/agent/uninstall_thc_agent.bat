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
    echo   (Thung Hua Chang Hospital IT Platform - Agent Uninstaller v2.11.0)
    echo ==========================================================================
    echo.
    echo ระบบกำลังดำเนินการถอนการติดตั้งโปรแกรมและล้างการตั้งค่าทั้งหมดออกจากเครื่องนี้...
    echo.
)

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
set "EXE_FILE=%INSTALL_DIR%\THC_IT_Agent.exe"

:: 1. หากมีไฟล์ EXE ให้สั่ง Native CLI Uninstall
if exist "%EXE_FILE%" (
    if "%IS_SILENT%"=="0" echo [1/5] สั่งโปรแกรมถอนการติดตั้งตัวเองผ่าน Native Engine...
    "%EXE_FILE%" /uninstall /silent 2>nul
)

:: 2. ปิดโปรเซสที่ค้างอยู่
if "%IS_SILENT%"=="0" echo [2/5] ปิดโปรเซสที่อาจค้างอยู่ในหน่วยความจำ...
taskkill /f /im THC_IT_Agent.exe 2>nul

:: 3. ถอดการเริ่มทำงานอัตโนมัติ และ Windows Uninstall Key
if "%IS_SILENT%"=="0" echo [3/5] ถอนการลงทะเบียน Windows Apps และ Startup...
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /f >nul 2>nul
reg delete "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /f >nul 2>nul
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Uninstall\THC_IT_Agent" /f >nul 2>nul
reg delete "HKLM\Software\Microsoft\Windows\CurrentVersion\Uninstall\THC_IT_Agent" /f >nul 2>nul

:: 4. ลบ Scheduled Tasks ^& Shortcuts
if "%IS_SILENT%"=="0" echo [4/5] ลบงานตารางเวลาและทางลัดบน Desktop...
schtasks /delete /tn "THC_Hardware_Audit" /f >nul 2>nul
schtasks /delete /tn "THC_Hardware_Audit_Poll" /f >nul 2>nul
del /f /q "%USERPROFILE%\Desktop\THC IT Agent.lnk" 2>nul
del /f /q "%PUBLIC%\Desktop\THC IT Agent.lnk" 2>nul
del /f /q "%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk" 2>nul
del /f /q "%ALLUSERSPROFILE%\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk" 2>nul

:: 5. ลบไฟล์และโฟลเดอร์ C:\ProgramData\THC-IT-Agent
if "%IS_SILENT%"=="0" echo [5/5] ลบไฟล์และโฟลเดอร์ติดตั้ง...
if exist "%INSTALL_DIR%" (
    start "" /b cmd /c "ping 127.0.0.1 -n 3 >nul & rmdir /s /q \"%INSTALL_DIR%\" 2>nul"
)

if "%IS_SILENT%"=="0" (
    echo.
    echo ==========================================================================
    echo   [สำเร็จ] ถอนการติดตั้ง THC IT Agent เรียบร้อยสมบูรณ์แล้ว!
    echo   - ปิดโปรแกรมและหยุดการทำงานทั้งหมด
    echo   - ถอน Startup และ Windows Programs เรียบร้อย
    echo   - ลบไฟล์โปรแกรมออกจากเครื่องเรียบร้อย
    echo ==========================================================================
    echo.
    echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
    pause >nul
)
exit /b 0
