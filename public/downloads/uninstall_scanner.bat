@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
title THC Hardware Scanner - Uninstaller

echo =====================================================================
echo    THC Hardware Scanner - Complete Uninstaller
echo    Clean Removal with Zero Residual System Footprint
echo =====================================================================
echo.

:: 1. Check Administrator Privileges
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] กรุณาคลิกขวาที่ไฟล์นี้แล้วเลือก "Run as administrator" (สิทธิ์ผู้ดูแลระบบ)
    echo [ERROR] Administrator privileges are required to uninstall.
    echo.
    pause
    exit /b 1
)

set "TARGET_DIR=%ProgramFiles%\THC_Hardware_Scanner"
set "TASK_NAME=THC_Hardware_Scanner_Task"

echo [+] กำลังหยุดการทำงานของโปรแกรมสแกนเนอร์...
taskkill /f /im THC_Hardware_Scanner.exe >nul 2>&1

echo [+] กำลังลบ Windows Scheduled Task (%TASK_NAME%)...
schtasks /delete /tn "%TASK_NAME%" /f >nul 2>&1

echo [+] กำลังลบไฟล์โปรแกรมและโฟลเดอร์ติดตั้ง...
if exist "%TARGET_DIR%" (
    rmdir /s /q "%TARGET_DIR%" >nul 2>&1
)

echo [+] กำลังลบแคชและประวัติบันทึกใน ProgramData...
set "PROG_DATA_DIR=%ProgramData%\THC_Hardware_Scanner"
if exist "%PROG_DATA_DIR%" (
    rmdir /s /q "%PROG_DATA_DIR%" >nul 2>&1
)

echo.
echo =====================================================================
echo [SUCCESS] ถอนการติดตั้ง THC Hardware Scanner เรียบร้อยแล้ว!
echo =====================================================================
echo  - Service & Tasks : ลบออกจาก Task Scheduler ทั้งหมด
echo  - Files & Cache   : ลบไฟล์และแคชทั้งหมด ไม่มีไฟล์ตกค้างในระบบ
echo =====================================================================
echo.
timeout /t 3 >nul
exit /b 0
