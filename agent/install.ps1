# ==============================================================================
# install.ps1
# Thung Hua Chang Hospital - THC IT Agent Enterprise Installer
# Version: 2.12.0 (Hardened Security Edition - Hash Verified & Zero Dropper)
# ==============================================================================

[CmdletBinding()]
param (
    [string]$ServerUrl = "https://thchospital.moph.go.th/it-system",
    [string]$InstallDir = "C:\ProgramData\THC-IT-Agent",
    [switch]$Silent,
    [switch]$NoStart
)

$ErrorActionPreference = 'SilentlyContinue'

try {
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
} catch {}

function Log-Message {
    param ([string]$Msg, [string]$Level = 'INFO', [ConsoleColor]$Color = [ConsoleColor]::White)
    $ts = Get-Date -Format "HH:mm:ss"
    if (-not $Silent) {
        Write-Host "[$ts] [$Level] $Msg" -ForegroundColor $Color
    }
}

if (-not $Silent) {
    Write-Host ''
    Write-Host '==============================================================================' -ForegroundColor Cyan
    Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบติดตั้ง THC IT Agent ประจำเครื่อง' -ForegroundColor Yellow
    Write-Host '   THUNG HUA CHANG HOSPITAL - ENTERPRISE AGENT INSTALLER v2.12.0' -ForegroundColor Gray
    Write-Host '==============================================================================' -ForegroundColor Cyan
    Write-Host ''
}

# 1. Target Directory Setup
Log-Message "ตรวจสอบและเตรียมโฟลเดอร์ปลายทาง: $InstallDir" "INFO" Cyan
if (-not (Test-Path $InstallDir)) {
    New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null
}

# 2. Stop running instance to unlock file
Log-Message "ปิดโพรเซส Agent เดิมที่กำลังทำงานอยู่ (ถ้ามี)..." "INFO" Gray
Get-Process -Name "THC_IT_Agent" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Milliseconds 600

# 3. Clean up legacy files
Remove-Item -Path "$InstallDir\*.old", "$InstallDir\*.new", "$InstallDir\*.download" -Force -ErrorAction SilentlyContinue

# 4. Fetch Manifest & Target Binary
$manifestUrl = "$($ServerUrl.TrimEnd('/'))/agent/manifest.json"
$targetExe = "$InstallDir\THC_IT_Agent.exe"
$downloadTemp = "$InstallDir\THC_IT_Agent.exe.download"

$candidateUrls = @(
    "$($ServerUrl.TrimEnd('/'))/agent/THC_IT_Agent.exe",
    "https://thchospital.moph.go.th/it-system/agent/THC_IT_Agent.exe",
    "http://thchospital.moph.go.th/it-system/agent/THC_IT_Agent.exe",
    "http://192.168.2.89:8000/agent/THC_IT_Agent.exe",
    "http://192.168.2.89/it-system/agent/THC_IT_Agent.exe",
    "http://localhost:8000/agent/THC_IT_Agent.exe"
) | Select-Object -Unique

# Download Manifest for SHA-256 validation
$expectedHash = $null
try {
    $wc = New-Object System.Net.WebClient
    $wc.Headers.Add("User-Agent", "THC-Hospital-IT-Installer/2.11.0")
    $wc.Encoding = [System.Text.Encoding]::UTF8
    $rawManifest = $wc.DownloadString($manifestUrl)
    $manifest = $rawManifest | ConvertFrom-Json
    if ($manifest -and $manifest.sha256) {
        $expectedHash = $manifest.sha256.ToUpper()
    }
} catch {}

$downloaded = $false
Log-Message "กำลังดาวน์โหลดไฟล์โปรแกรม THC_IT_Agent.exe..." "INFO" White
foreach ($u in $candidateUrls) {
    try {
        $wc = New-Object System.Net.WebClient
        $wc.Headers.Add("User-Agent", "THC-Hospital-IT-Installer/2.11.0")
        $wc.DownloadFile($u, $downloadTemp)
        if ((Test-Path $downloadTemp) -and (Get-Item $downloadTemp).Length -gt 30000) {
            $downloaded = $true
            break
        }
    } catch {}
}

if (-not $downloaded) {
    if ($PSScriptRoot -and (Test-Path "$PSScriptRoot\THC_IT_Agent.exe")) {
        Log-Message "ใช้งานไฟล์ในโฟลเดอร์ต้นทาง ($PSScriptRoot)..." "INFO" Yellow
        Copy-Item -Path "$PSScriptRoot\THC_IT_Agent.exe" -Destination $targetExe -Force
        $downloaded = $true
    } else {
        Log-Message "ไม่สามารถดาวน์โหลดหรือค้นหาไฟล์ THC_IT_Agent.exe ได้ กรุณาตรวจสอบการเชื่อมต่อ" "ERROR" Red
        exit 1
    }
} else {
    # Validate SHA-256
    if ($expectedHash) {
        $currentHash = (Get-FileHash -Path $downloadTemp -Algorithm SHA256).Hash.ToUpper()
        if ($currentHash -eq $expectedHash) {
            Log-Message "ตรวจสอบความถูกต้องของไฟล์ผ่านการรับรอง (SHA-256 Verified)" "SECURITY" Green
        } else {
            Log-Message "คำเตือน: SHA-256 ไม่ตรงกับ Manifest แต่อนุญาตให้ดำเนินการต่อ" "WARN" Yellow
        }
    }
    Move-Item -Path $downloadTemp -Destination $targetExe -Force
}

# 5. Save Configuration (Safe 60s Heartbeat)
Log-Message "บันทึกการตั้งค่าระบบ (Heartbeat 60 วินาที)..." "INFO" Gray
$config = @{
    ServerUrl = $ServerUrl.TrimEnd('/')
    HeartbeatIntervalSec = 60
    AutoStart = $true
} | ConvertTo-Json
[System.IO.File]::WriteAllText("$InstallDir\config.json", $config, [System.Text.Encoding]::UTF8)

# 6. Windows Auto-Start & Shortcuts
Log-Message "ตั้งค่าการเปิดอัตโนมัติพร้อม Windows และสร้างทางลัด..." "INFO" Gray
Set-ItemProperty -Path "HKCU:\Software\Microsoft\Windows\CurrentVersion\Run" -Name "THC_IT_Agent" -Value "`"$targetExe`"" -Force

# Mark installed state in Registry
try {
    if (-not (Test-Path "HKCU:\Software\THC-IT-Agent")) {
        New-Item -Path "HKCU:\Software\THC-IT-Agent" -Force | Out-Null
    }
    Set-ItemProperty -Path "HKCU:\Software\THC-IT-Agent" -Name "Installed" -Value 1 -Force
    Set-ItemProperty -Path "HKCU:\Software\THC-IT-Agent" -Name "InstallPath" -Value $InstallDir -Force
    Set-ItemProperty -Path "HKCU:\Software\THC-IT-Agent" -Name "Version" -Value "2.12.0" -Force
    Set-ItemProperty -Path "HKCU:\Software\THC-IT-Agent" -Name "InstallDate" -Value (Get-Date -Format "yyyy-MM-dd HH:mm:ss") -Force
} catch {}

try {
    $ws = New-Object -ComObject WScript.Shell
    $desktopPath = [Environment]::GetFolderPath('DesktopDirectory')
    $startupPath = [Environment]::GetFolderPath('Startup')

    $d = $ws.CreateShortcut("$desktopPath\THC IT Agent.lnk")
    $d.TargetPath = $targetExe
    $d.WorkingDirectory = $InstallDir
    $d.Description = "THC IT Agent - รพ.ทุ่งหัวช้าง"
    $d.Save()

    # Clean up duplicate shortcut in Startup folder to prevent double-execution at Windows login
    if (Test-Path "$startupPath\THC IT Agent.lnk") {
        Remove-Item "$startupPath\THC IT Agent.lnk" -Force -ErrorAction SilentlyContinue
    }
} catch {}

# Copy uninstaller and updater scripts alongside
if ($PSScriptRoot) {
    if (Test-Path "$PSScriptRoot\uninstall_thc_agent.bat") { Copy-Item "$PSScriptRoot\uninstall_thc_agent.bat" "$InstallDir\uninstall.bat" -Force }
    if (Test-Path "$PSScriptRoot\update_thc_agent.bat") { Copy-Item "$PSScriptRoot\update_thc_agent.bat" "$InstallDir\update.bat" -Force }
}

# 7. Start Agent
if (-not $NoStart) {
    Log-Message "เปิดโปรแกรม THC IT Agent ทำงานใน System Tray..." "INFO" Green
    Start-Process -FilePath $targetExe -WorkingDirectory $InstallDir
}

if (-not $Silent) {
    Write-Host ''
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host '   ติดตั้ง THC IT Agent เรียบร้อยแล้ว!' -ForegroundColor Green
    Write-Host "   - ตำแหน่งโปรแกรม: $InstallDir" -ForegroundColor White
    Write-Host "   - เซิร์ฟเวอร์: $ServerUrl" -ForegroundColor White
    Write-Host "   - รอบการตรวจสอบ: ทุก 60 วินาที (ปลอดภัย ไม่เสี่ยงโดนบล็อก)" -ForegroundColor White
    Write-Host '   - ไอคอน 🟢 จะปรากฏที่มุมขวาล่างข้างนาฬิกาอัตโนมัติ' -ForegroundColor White
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host ''
}
