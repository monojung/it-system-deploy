@echo off
chcp 65001 >nul
echo =======================================================
echo   Building THC IT Agent Standalone Executable (.exe)
echo =======================================================

set "CSC=C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe"
if not exist "%CSC%" (
    set "CSC=C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe"
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
    copy /y "%~dp0install.ps1" "%~dp0..\public\agent\install.ps1" >nul
    copy /y "%~dp0install_thc_agent.bat" "%~dp0..\public\agent\install_thc_agent.bat" >nul
    copy /y "%~dp0update.ps1" "%~dp0..\public\agent\update.ps1" >nul
    copy /y "%~dp0update_thc_agent.bat" "%~dp0..\public\agent\update_thc_agent.bat" >nul
    copy /y "%~dp0uninstall.ps1" "%~dp0..\public\agent\uninstall.ps1" >nul
    copy /y "%~dp0uninstall_thc_agent.bat" "%~dp0..\public\agent\uninstall_thc_agent.bat" >nul
    copy /y "%~dp0build_agent.bat" "%~dp0..\public\agent\build_agent.bat" >nul
    echo [SUCCESS] Synchronized executable and scripts to public\agent\
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$exe = '%~dp0THC_IT_Agent.exe'; $sha = (Get-FileHash $exe -Algorithm SHA256).Hash; $md5 = (Get-FileHash $exe -Algorithm MD5).Hash; $sz = (Get-Item $exe).Length; $dt = (Get-Date).ToString('yyyy-MM-dd HH:mm:ss'); $json = @{ version='2.12.0'; app_name='THC IT Agent'; organization='Thung Hua Chang Hospital (รพ.ทุ่งหัวช้าง)'; sha256=$sha; md5=$md5; size_bytes=$sz; heartbeat_interval_sec=60; download_url='/agent/THC_IT_Agent.exe'; published_at=$dt } | ConvertTo-Json; Set-Content -Path '%~dp0manifest.json' -Value $json -Encoding UTF8; Set-Content -Path '%~dp0..\public\agent\manifest.json' -Value $json -Encoding UTF8; Write-Host ('[SUCCESS] Updated manifest.json with SHA256: ' + $sha) -ForegroundColor Green"
) else (
    echo [ERROR] Compilation failed!
    exit /b %ERRORLEVEL%
)
