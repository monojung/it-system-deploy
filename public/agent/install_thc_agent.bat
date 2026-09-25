@echo off
chcp 65001 >nul
title ติดตั้ง THC Hardware Audit Agent v2.3.0 - โรงพยาบาลทุ่งหัวช้าง
color 0b

echo ==========================================================================
echo   ติดตั้งโปรแกรม THC Client Hardware Audit Agent ประจำเครื่อง v2.3.0
echo   รองรับการสั่งสแกนระยะไกลจากระบบ IT (Remote Scan Engine)
echo   โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital IT Platform)
echo ==========================================================================
echo.

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" 2>nul
)

echo [1/3] กำลังเตรียมไฟล์ Agent (%INSTALL_DIR%)...
:: ปิดโพรเซส Agent เดิมที่อาจค้างอยู่เพื่อป้องกัน File Lock
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*thc_audit_agent.ps1*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>nul

if exist "%~dp0thc_audit_agent.ps1" (
    copy /y "%~dp0thc_audit_agent.ps1" "%INSTALL_DIR%\thc_audit_agent.ps1" >nul
) else (
    echo       กำลังดาวน์โหลดไฟล์ Agent ล่าสุดจากเซิร์ฟเวอร์...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = 3072; $urls = @('https://thchospital.moph.go.th/it-system/agent/thc_audit_agent.ps1', 'http://192.168.2.89:8000/agent/thc_audit_agent.ps1', 'http://192.168.2.89/it-system/agent/thc_audit_agent.ps1', 'http://127.0.0.1:8000/agent/thc_audit_agent.ps1', 'http://localhost:8000/agent/thc_audit_agent.ps1'); foreach ($u in $urls) { try { (New-Object Net.WebClient).DownloadFile($u, '%INSTALL_DIR%\thc_audit_agent.ps1'); break } catch {} }" 2>nul
)

if not exist "%INSTALL_DIR%\thc_audit_agent.ps1" (
    echo [ผิดพลาด] ไม่สามารถดาวน์โหลดหรือค้นหาไฟล์ thc_audit_agent.ps1 ได้
    echo กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต/เครือข่ายโรงพยาบาล หรือติดต่อฝ่ายไอที
    echo.
    echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
    pause >nul
    exit /b 1
)

echo [2/3] กำลังลงทะเบียน Windows Scheduled Task & ระบบรับคำสั่งระยะไกล...
schtasks /create /tn "THC_Hardware_Audit" /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File \"%INSTALL_DIR%\thc_audit_agent.ps1\" -Background" /sc onlogon /rl HIGHEST /f >nul 2>nul
schtasks /create /tn "THC_Hardware_Audit_Poll" /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File \"%INSTALL_DIR%\thc_audit_agent.ps1\" -PollOnce" /sc minute /mo 5 /rl HIGHEST /f >nul 2>nul

:: สตาร์ท Daemon พื้นหลังสำหรับรับคำสั่งสแกนทันที
start "" powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%INSTALL_DIR%\thc_audit_agent.ps1" -Background

echo [3/3] กำลังสร้างทางลัด (Shortcut) สำหรับส่งสเปคเครื่องด้วยตนเอง...
(
echo @echo off
echo chcp 65001 ^>nul
echo title ส่งสเปคเครื่องเข้าสู่ระบบ IT - โรงพยาบาลทุ่งหัวช้าง
echo color 0b
echo powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"
echo.
echo กดปุ่มใดๆ เพื่อปิด...
echo pause ^>nul
) > "%USERPROFILE%\Desktop\ส่งสเปคเครื่องเข้าสู่ระบบ IT.bat" 2>nul

(
echo @echo off
echo chcp 65001 ^>nul
echo title ส่งสเปคเครื่องเข้าสู่ระบบ IT
echo powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"
echo pause
) > "%INSTALL_DIR%\run_audit.bat" 2>nul

echo.
echo ==========================================================================
echo   ติดตั้ง Agent ฝังลงในเครื่องนี้เรียบร้อยแล้ว!
echo   - ระบบฝัง Daemon ตรวจสอบคำสั่งสแกนจากระบบ IT อัตโนมัติทุก 30 วินาที
echo   - มีระบบ Watchdog สำรองตรวจสอบทุก 5 นาที
echo   ระบบจะทำการสแกนสเปคครั้งแรกและส่งรายงานเข้าสู่เซิร์ฟเวอร์เดี๋ยวนี้...
echo ==========================================================================
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%INSTALL_DIR%\thc_audit_agent.ps1"

echo.
echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
pause >nul
