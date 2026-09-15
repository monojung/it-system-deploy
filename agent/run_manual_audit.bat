@echo off
chcp 65001 >nul
title THC Client Hardware Audit - ส่งสเปคเข้าสู่ระบบ
color 0a

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0thc_audit_agent.ps1"

echo.
echo กดปุ่มใดๆ เพื่อปิดหน้าต่างนี้...
pause >nul
