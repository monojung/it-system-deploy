# ==============================================================================
# uninstall.ps1
# Thung Hua Chang Hospital - THC IT Agent Enterprise Uninstaller
# Version: 2.11.0 (Clean Purge & Farewell Beacon Engine)
# ==============================================================================

[CmdletBinding()]
param (
    [switch]$Silent
)

$ErrorActionPreference = 'SilentlyContinue'

try {
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
} catch {}

$InstallDir = "C:\ProgramData\THC-IT-Agent"
$ExePath = Join-Path $InstallDir "THC_IT_Agent.exe"
$ConfigFile = Join-Path $InstallDir "config.json"

if (-not $Silent) {
    Write-Host ''
    Write-Host '==============================================================================' -ForegroundColor Red
    Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบถอนการติดตั้ง THC IT Agent' -ForegroundColor Yellow
    Write-Host '   THUNG HUA CHANG HOSPITAL - AGENT UNINSTALLER v2.11.0' -ForegroundColor Gray
    Write-Host '==============================================================================' -ForegroundColor Red
    Write-Host ''
}

# 1. Send farewell uninstalled beacon to server
if (Test-Path $ConfigFile) {
    try {
        $cfg = Get-Content $ConfigFile -Raw | ConvertFrom-Json
        if ($cfg -and $cfg.ServerUrl) {
            $pingUrl = "$($cfg.ServerUrl.TrimEnd('/'))/api/hardware-audit/agent-ping"
            $bodyObj = @{
                action = "uninstalled"
                hostname = $env:COMPUTERNAME
                reason = "powershell_uninstaller"
                timestamp = (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
            }
            $jsonPayload = $bodyObj | ConvertTo-Json -Compress
            $wc = New-Object System.Net.WebClient
            $wc.Headers.Add("Content-Type", "application/json; charset=utf-8")
            $wc.Headers.Add("User-Agent", "THC-Hospital-IT-Agent-Uninstaller/2.11.0")
            [void]$wc.UploadString($pingUrl, "POST", $jsonPayload)
        }
    } catch {}
}

# 2. If executable exists, invoke its native uninstaller switch
if (Test-Path $ExePath) {
    try {
        Start-Process -FilePath $ExePath -ArgumentList "/uninstall /silent" -Wait -WindowStyle Hidden
    } catch {}
}

# 3. Ensure process termination
Get-Process -Name "THC_IT_Agent" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue

# 4. Clean Registry Keys
Remove-ItemProperty -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "THC_IT_Agent" -ErrorAction SilentlyContinue
Remove-ItemProperty -Path "HKLM:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "THC_IT_Agent" -ErrorAction SilentlyContinue
Remove-Item -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Uninstall\THC_IT_Agent" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item -Path "HKLM:\Software\Microsoft\Windows\CurrentVersion\Uninstall\THC_IT_Agent" -Recurse -Force -ErrorAction SilentlyContinue

# 5. Clean Desktop and Startup Shortcuts
$shortcuts = @(
    "$env:USERPROFILE\Desktop\THC IT Agent.lnk",
    "$env:PUBLIC\Desktop\THC IT Agent.lnk",
    "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk",
    "$env:ALLUSERSPROFILE\Microsoft\Windows\Start Menu\Programs\Startup\THC IT Agent.lnk"
)
foreach ($sc in $shortcuts) {
    if (Test-Path $sc) { Remove-Item $sc -Force -ErrorAction SilentlyContinue }
}

# 6. Delete scheduled tasks
schtasks.exe /delete /tn "THC_Hardware_Audit" /f 2>$null | Out-Null
schtasks.exe /delete /tn "THC_Hardware_Audit_Poll" /f 2>$null | Out-Null

# 7. Purge files in C:\ProgramData\THC-IT-Agent
if (Test-Path $InstallDir) {
    Start-Sleep -Milliseconds 500
    Remove-Item $InstallDir -Recurse -Force -ErrorAction SilentlyContinue
}

if (-not $Silent) {
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host '   [สำเร็จ] ถอนการติดตั้ง THC IT Agent ออกจากเครื่องเรียบร้อยแล้ว!' -ForegroundColor Green
    Write-Host '   - ปิดโปรเซสและล้างโปรแกรมออกจากหน่วยความจำ' -ForegroundColor White
    Write-Host '   - ถอนการลงทะเบียนจาก Windows Apps และ Startup' -ForegroundColor White
    Write-Host '   - ลบไฟล์โปรแกรมและโฟลเดอร์ C:\ProgramData\THC-IT-Agent ทั้งหมด' -ForegroundColor White
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host ''
}
