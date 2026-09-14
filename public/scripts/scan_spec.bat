@echo off
chcp 65001 >nul
title Thung Hua Chang Hospital - Hardware Specification Auditor
powershell -NoProfile -ExecutionPolicy Bypass -Command "Invoke-Expression (New-Object Net.WebClient).DownloadString('%~dp0scan_spec.ps1')" 2>nul
if %errorlevel% neq 0 (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scan_spec.ps1"
)
pause
