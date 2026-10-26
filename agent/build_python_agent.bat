@echo off
chcp 65001 >nul
title สร้างไฟล์ THC IT Agent (Python Edition)
echo ==============================================================================
echo   ระบบคอมไพล์ THC IT Agent Python Edition -> Standalone .exe (PyInstaller)
echo ==============================================================================
echo.

where pyinstaller >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [INFO] ติดตั้ง PyInstaller และไลบรารีที่จำเป็น...
    python -m pip install --upgrade pip
    python -m pip install -r "%~dp0requirements.txt"
)

echo.
echo กำลังสร้างไฟล์ THC_IT_Agent_Python.exe ด้วย PyInstaller...
pyinstaller --noconsole --onefile --clean ^
    --name="THC_IT_Agent_Python" ^
    --distpath="%~dp0dist" ^
    --workpath="%~dp0build_py" ^
    "%~dp0thc_agent.py"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ==============================================================================
    echo [SUCCESS] คอมไพล์ไฟล์สำเร็จ!
    echo ไฟล์ปลายทาง: %~dp0dist\THC_IT_Agent_Python.exe
    echo ==============================================================================
    copy /y "%~dp0dist\THC_IT_Agent_Python.exe" "%~dp0THC_IT_Agent_Python.exe" >nul
) else (
    echo.
    echo [ERROR] คอมไพล์ล้มเหลว กรุณาตรวจสอบข้อผิดพลาดด้านบน
)
