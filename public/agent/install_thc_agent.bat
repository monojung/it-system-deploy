@echo off
chcp 65001 >nul
title ติดตั้ง THC Hardware Audit Agent Standalone - โรงพยาบาลทุ่งหัวช้าง
color 0b

echo ==========================================================================
echo   ติดตั้งโปรแกรม THC Hardware Audit Agent v2.5.5 (Standalone)
echo   รูปแบบใหม่: System Tray Icon เชื่อมต่อเซิร์ฟเวอร์แบบ Real-time
echo   โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital IT Platform)
echo ==========================================================================
echo.

set "INSTALL_DIR=C:\ProgramData\THC-IT-Agent"
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" 2>nul
)

echo [1/3] กำลังเตรียมไฟล์ Agent (%INSTALL_DIR%)...
:: ปิดโพรเซส Agent เดิมที่อาจค้างอยู่เพื่อป้องกัน File Lock
taskkill /f /im THC_IT_Agent.exe 2>nul
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*thc_audit_agent.ps1*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }" 2>nul

if exist "%~dp0THC_IT_Agent.exe" (
    copy /y "%~dp0THC_IT_Agent.exe" "%INSTALL_DIR%\THC_IT_Agent.exe" >nul
) else (
    echo       กำลังดาวน์โหลดโปรแกรม Agent (.exe) ล่าสุดจากเซิร์ฟเวอร์...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = 3072; $urls = @('https://thchospital.moph.go.th/it-system/agent/THC_IT_Agent.exe', 'http://192.168.2.89:8000/agent/THC_IT_Agent.exe', 'http://192.168.2.89/it-system/agent/THC_IT_Agent.exe', 'http://127.0.0.1:8000/agent/THC_IT_Agent.exe', 'http://localhost:8000/agent/THC_IT_Agent.exe'); foreach ($u in $urls) { try { (New-Object Net.WebClient).DownloadFile($u, '%INSTALL_DIR%\THC_IT_Agent.exe'); break } catch {} }" 2>nul
)

if not exist "%INSTALL_DIR%\THC_IT_Agent.exe" (
    echo [ผิดพลาด] ไม่สามารถค้นหาหรือดาวน์โหลดไฟล์ THC_IT_Agent.exe ได้
    echo กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต/เครือข่ายโรงพยาบาล หรือติดต่อฝ่ายไอที
    echo.
    echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
    pause >nul
    exit /b 1
)

echo [2/3] กำลังตั้งค่าให้เริ่มทำงานอัตโนมัติเมื่อเปิดเครื่อง (Windows Startup)...
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "THC_IT_Agent" /t REG_SZ /d "\"%INSTALL_DIR%\THC_IT_Agent.exe\"" /f >nul 2>nul

:: สร้างทางลัดบน Desktop และ Startup
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut([Environment]::GetFolderPath('Desktop') + '\THC IT Agent.lnk'); $s.TargetPath = '%INSTALL_DIR%\THC_IT_Agent.exe'; $s.Description = 'THC IT Agent - รพ.ทุ่งหัวช้าง'; $s.Save(); $s2 = $ws.CreateShortcut([Environment]::GetFolderPath('Startup') + '\THC IT Agent.lnk'); $s2.TargetPath = '%INSTALL_DIR%\THC_IT_Agent.exe'; $s2.Save();" 2>nul

echo [3/3] กำลังเปิดโปรแกรม THC IT Agent...
start "" "%INSTALL_DIR%\THC_IT_Agent.exe"

echo.
echo ==========================================================================
echo   ติดตั้ง THC IT Agent เรียบร้อยแล้ว!
echo   - โปรแกรมจะทำงานที่ System Tray (มุมขวาล่างข้างนาฬิกา)
echo   - ไอคอน 🟢 เขียว = ออนไลน์พร้อมรับคำสั่ง (สแตนด์บาย)
echo   - ไอคอน 🟡 เหลือง = กำลังดึง/ส่งสเปกเครื่องตามคำสั่งไอที
echo   - ดับเบิ้ลคลิกที่ไอคอนเพื่อดูสเปก หรือคลิกขวาเพื่อสั่งส่งข้อมูลด้วยตนเอง
echo ==========================================================================
echo.

echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
pause >nul
