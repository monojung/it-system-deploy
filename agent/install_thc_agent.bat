@echo off
chcp 65001 >nul
title ติดตั้ง THC Hardware Audit Agent - โรงพยาบาลทุ่งหัวช้าง
color 0b

echo ==========================================================================
echo   ติดตั้งโปรแกรม THC Client Hardware Audit Agent ประจำเครื่อง
echo   โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital IT Platform)
echo ==========================================================================
echo.

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" 2>nul
)

echo [1/3] กำลังคัดลอกไฟล์ Agent ไปยัง %INSTALL_DIR%...
copy /y "%~dp0thc_audit_agent.ps1" "%INSTALL_DIR%\thc_audit_agent.ps1" >nul

echo [2/3] กำลังลงทะเบียน Windows Scheduled Task (THC_Hardware_Audit)...
schtasks /create /tn "THC_Hardware_Audit" /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File \"%INSTALL_DIR%\thc_audit_agent.ps1\"" /sc onlogon /f >nul 2>nul

echo [3/3] กำลังสร้างทางลัด (Shortcut) สำหรับสั่งส่งสเปคเครื่องด้วยตนเอง...
(
echo @echo off
echo chcp 65001 ^>nul
echo title ส่งสเปคเครื่องเข้าสู่ระบบ IT
echo powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"
echo pause
) > "%USERPROFILE%\Desktop\ส่งสเปคเครื่องเข้าสู่ระบบ IT.bat" 2>nul

(
echo @echo off
echo chcp 65001 ^>nul
echo powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"
echo pause
) > "%INSTALL_DIR%\run_audit.bat" 2>nul

echo.
echo ==========================================================================
echo   ติดตั้ง Agent ฝังลงในเครื่องนี้เรียบร้อยแล้ว!
echo   ระบบจะทำการสแกนและส่งรายงานสเปคเบื้องต้นเข้าสู่เซิร์ฟเวอร์เดี๋ยวนี้...
echo ==========================================================================
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"

echo.
echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
pause >nul
