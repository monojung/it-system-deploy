# ==============================================================================
# update.ps1
# Thung Hua Chang Hospital - THC IT Agent Enterprise Updater
# Version: 2.11.0 (Atomic Binary Replacement & Validation Engine)
# ==============================================================================

[CmdletBinding()]
param (
    [string]$ServerUrl = "",
    [string]$InstallDir = "C:\ProgramData\THC-IT-Agent",
    [switch]$Silent
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

# Read ServerUrl from local config if not provided
if ([string]::IsNullOrWhiteSpace($ServerUrl) -and (Test-Path "$InstallDir\config.json")) {
    try {
        $cfg = Get-Content "$InstallDir\config.json" -Raw | ConvertFrom-Json
        if ($cfg -and $cfg.ServerUrl) { $ServerUrl = $cfg.ServerUrl }
    } catch {}
}
if ([string]::IsNullOrWhiteSpace($ServerUrl)) {
    $ServerUrl = "https://thchospital.moph.go.th/it-system"
}

if (-not $Silent) {
    Write-Host ''
    Write-Host '==============================================================================' -ForegroundColor Cyan
    Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบอัปเดต THC IT Agent ล่าสุด' -ForegroundColor Yellow
    Write-Host '   THUNG HUA CHANG HOSPITAL - AGENT AUTO-UPDATER v2.11.0' -ForegroundColor Gray
    Write-Host '==============================================================================' -ForegroundColor Cyan
    Write-Host ''
}

Log-Message "ตรวจสอบการอัปเดตจากเซิร์ฟเวอร์: $ServerUrl" "INFO" White

$targetExe = "$InstallDir\THC_IT_Agent.exe"
$newExe = "$InstallDir\THC_IT_Agent.exe.new"
$oldExe = "$InstallDir\THC_IT_Agent.exe.old"
$downloadUrl = "$($ServerUrl.TrimEnd('/'))/agent/THC_IT_Agent.exe"
$manifestUrl = "$($ServerUrl.TrimEnd('/'))/agent/manifest.json"

$downloaded = $false
try {
    $wc = New-Object System.Net.WebClient
    $wc.Headers.Add("User-Agent", "THC-Hospital-IT-Updater/2.11.0")
    $wc.DownloadFile($downloadUrl, $newExe)
    if ((Test-Path $newExe) -and (Get-Item $newExe).Length -gt 30000) {
        $downloaded = $true
    }
} catch {
    Log-Message "ดาวน์โหลดไม่สำเร็จ: $($_.Exception.Message)" "ERROR" Red
}

if (-not $downloaded) {
    Log-Message "ไม่สามารถดาวน์โหลดเวอร์ชันใหม่ได้ กรุณาตรวจสอบการเชื่อมต่อ" "ERROR" Red
    exit 1
}

# Cryptographic SHA-256 Verification
$expectedSha = $null
try {
    $wcMan = New-Object System.Net.WebClient
    $wcMan.Headers.Add("User-Agent", "THC-Hospital-IT-Updater/2.11.0")
    $manStr = $wcMan.DownloadString($manifestUrl)
    $manObj = $manStr | ConvertFrom-Json
    if ($manObj.sha256) { $expectedSha = $manObj.sha256 }
    elseif ($manObj.files -and $manObj.files.executable -and $manObj.files.executable.sha256) { $expectedSha = $manObj.files.executable.sha256 }
} catch {}

if ($expectedSha) {
    $actualSha = (Get-FileHash -Path $newExe -Algorithm SHA256).Hash
    if ($actualSha -ne $expectedSha) {
        Log-Message "ตรวจสอบความปลอดภัยล้มเหลว: SHA-256 ไม่ตรงกับ Manifest (Expected: $expectedSha, Got: $actualSha)" "ERROR" Red
        Remove-Item $newExe -Force -ErrorAction SilentlyContinue
        exit 1
    }
    Log-Message "ตรวจสอบความถูกต้องไฟล์สมบูรณ์ (SHA-256 Verified: $actualSha)" "INFO" Green
}

Log-Message "ดาวน์โหลดเวอร์ชันใหม่สำเร็จ กำลังเตรียมสลับไฟล์ (Atomic Swap)..." "INFO" Cyan

# Stop current process
Get-Process -Name "THC_IT_Agent" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Milliseconds 600

# Atomic replacement
if (Test-Path $oldExe) { Remove-Item -Force $oldExe -ErrorAction SilentlyContinue }
if (Test-Path $targetExe) { Move-Item -Path $targetExe -Destination $oldExe -Force }
Move-Item -Path $newExe -Destination $targetExe -Force

# Start new version
Log-Message "เริ่มทำงานเวอร์ชันใหม่..." "INFO" Green
Start-Process -FilePath $targetExe -WorkingDirectory $InstallDir

if (-not $Silent) {
    Write-Host ''
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host '   อัปเดต THC IT Agent เป็นเวอร์ชันล่าสุดเรียบร้อยแล้ว!' -ForegroundColor Green
    Write-Host '==============================================================================' -ForegroundColor Green
    Write-Host ''
}
