@echo off
chcp 65001 >nul
echo =======================================================
echo   Building THC IT Agent Standalone Executable (.exe)
echo =======================================================

set CSC=C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe
if not exist "%CSC%" (
    set CSC=C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe
)

if not exist "%CSC%" (
    echo [ERROR] csc.exe not found!
    exit /b 1
)

echo Compiling THC_IT_Agent.cs with %CSC%...
"%CSC%" /target:winexe /optimize+ /r:System.Windows.Forms.dll /r:System.Drawing.dll /r:System.Management.dll /r:System.Web.Extensions.dll /out:"%~dp0THC_IT_Agent.exe" "%~dp0THC_IT_Agent.cs"

if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] agent\THC_IT_Agent.exe built successfully!
    copy /y "%~dp0THC_IT_Agent.exe" "%~dp0..\public\agent\THC_IT_Agent.exe" >nul
    echo [SUCCESS] Copied to public\agent\THC_IT_Agent.exe
) else (
    echo [ERROR] Compilation failed!
    exit /b %ERRORLEVEL%
)
