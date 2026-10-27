# scan_spec.ps1
# Thung Hua Chang Hospital IT Platform - Client Hardware Auto-Audit Utility
# Safe Read-Only WMI/CIM Query to inspect computer hardware specs

$ErrorActionPreference = 'SilentlyContinue'

Write-Host ''
Write-Host '==========================================================================' -ForegroundColor Cyan
Write-Host '  THUNG HUA CHANG HOSPITAL - CLIENT HARDWARE AUDIT UTILITY' -ForegroundColor Yellow
Write-Host '  Auto-detect computer specifications for IT Asset Management Platform' -ForegroundColor Gray
Write-Host '==========================================================================' -ForegroundColor Cyan
Write-Host 'Scanning computer hardware, please wait...' -ForegroundColor White

# 1. Computer System & BIOS
$cs = Get-CimInstance Win32_ComputerSystem
$bios = Get-CimInstance Win32_Bios
$os = Get-CimInstance Win32_OperatingSystem

$computerName = $env:COMPUTERNAME
$brand = if ($cs.Manufacturer) { $cs.Manufacturer.Trim() } else { '' }
$model = if ($cs.Model) { $cs.Model.Trim() } else { '' }
$serialNumber = if ($bios.SerialNumber) { $bios.SerialNumber.Trim() } else { '' }

if ($serialNumber -match 'Default string|To be filled by O.E.M.|None') {
    $serialNumber = ''
}

# 2. Processor (CPU)
$cpu = Get-CimInstance Win32_Processor | Select-Object -First 1
$cpuName = if ($cpu.Name) { ($cpu.Name -replace '\s+', ' ').Trim() } else { 'Intel Core Processor' }
$cpuSpeedGhz = if ($cpu.MaxClockSpeed) { ([math]::Round($cpu.MaxClockSpeed / 1000, 2)).ToString() + ' GHz' } else { '' }

# 3. Physical Memory (RAM)
$memoryModules = @(Get-CimInstance Win32_PhysicalMemory)
$totalRamBytes = ($memoryModules | Measure-Object -Property Capacity -Sum).Sum
$ramGb = [int][math]::Round($totalRamBytes / 1GB)
if ($ramGb -le 0) { $ramGb = 8 }
$slotCount = if ($memoryModules.Count -gt 0) { $memoryModules.Count } else { 1 }
$ramPerSlot = [int][math]::Round($ramGb / $slotCount)

$ramTypeSmbios = if ($memoryModules.Count -gt 0) { $memoryModules[0].SMBIOSMemoryType } else { 0 }
$ramType = 'DDR4'
if ($ramTypeSmbios -eq 34 -or $ramTypeSmbios -eq 35) {
    $ramType = 'DDR5'
} elseif ($ramTypeSmbios -eq 26) {
    $ramType = 'DDR4'
} elseif ($ramTypeSmbios -eq 24) {
    $ramType = 'DDR3'
}

$ramSpeedMhz = if ($memoryModules.Count -gt 0) { $memoryModules[0].ConfiguredClockSpeed } else { 0 }
$ramBus = if ($ramSpeedMhz -gt 0) { "$ramSpeedMhz MHz" } else { '3200 MHz' }
$ramSlotsStr = "$slotCount Slot ($ramPerSlot" + "GB x $slotCount)"

# 4. Storage (Primary Drive)
$disks = @(Get-CimInstance Win32_DiskDrive | Where-Object { $_.Size -gt 0 })
$primaryDisk = if ($disks.Count -gt 0) { $disks[0] } else { $null }
$diskModel = if ($primaryDisk) { $primaryDisk.Model } else { '' }
$diskSizeGb = if ($primaryDisk -and $primaryDisk.Size) { [int][math]::Round($primaryDisk.Size / 1GB) } else { 512 }

$storageType = 'SSD SATA 2.5"'
if ($diskModel -match 'NVMe|PCIe|M\.2|Optane') {
    $storageType = 'SSD NVMe M.2'
} elseif ($primaryDisk.MediaType -match 'Fixed hard disk' -and $diskModel -notmatch 'SSD') {
    $storageType = 'HDD SATA 3.5"'
}

$storageCap = '512 GB'
if ($diskSizeGb -ge 900) { $storageCap = '1 TB' }
elseif ($diskSizeGb -ge 450) { $storageCap = '512 GB' }
elseif ($diskSizeGb -ge 220) { $storageCap = '256 GB' }
elseif ($diskSizeGb -ge 110) { $storageCap = '128 GB' }

# 5. Graphics Card (GPU)
$gpu = Get-CimInstance Win32_VideoController | Where-Object { $_.Name -notmatch 'Virtual|Remote|Basic Render' } | Select-Object -First 1
$gpuModel = if ($gpu -and $gpu.Name) { ($gpu.Name -replace '\s+', ' ').Trim() } else { 'Onboard / Integrated' }

# 6. Operating System (OS)
$osCaption = if ($os.Caption) { ($os.Caption -replace 'Microsoft ', '').Trim() } else { 'Windows 11 Pro' }
$osName = 'Windows 11 Pro'
if ($osCaption -match 'Windows 11') {
    $osName = if ($osCaption -match 'Home') { 'Windows 11 Home' } else { 'Windows 11 Pro' }
} elseif ($osCaption -match 'Windows 10') {
    $osName = if ($osCaption -match 'Home') { 'Windows 10 Home' } else { 'Windows 10 Pro' }
} elseif ($osCaption -match 'Windows 7') {
    $osName = 'Windows 7 Pro'
}
$osLicense = 'OEM (ติดเครื่อง/BIOS)'

# 7. Network (MAC & IP)
$net = Get-CimInstance Win32_NetworkAdapterConfiguration | Where-Object { $_.IPEnabled -and $_.MACAddress } | Select-Object -First 1
$macAddress = if ($net -and $net.MACAddress) { $net.MACAddress } else { '' }
$ipAddress = if ($net -and $net.IPAddress) { $net.IPAddress[0] } else { '' }

# 8. Device Form Factor
$battery = Get-CimInstance Win32_Battery
$isLaptop = ($null -ne $battery)
$deviceTypeCode = if ($isLaptop) { 'NB' } else { 'PC' }
$deviceTypeName = if ($isLaptop) { 'Notebook' } else { 'Desktop PC' }

# 9. Screen / Monitor
$monitorSize = if ($isLaptop) { '15.6 นิ้ว FHD' } else { '23.8 นิ้ว IPS FHD' }

# 10. Construct Clean JSON Output
$assetName = "คอมพิวเตอร์ $computerName"
if ($brand -or $model) {
    $assetName = "คอมพิวเตอร์ $computerName ($brand $model)".Trim()
}

$specData = [ordered]@{
    source            = 'powershell_wmi_audit'
    timestamp         = (Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
    hostname          = $computerName
    name              = $assetName
    brand             = $brand
    model             = $model
    serial_number     = $serialNumber
    device_type_code  = $deviceTypeCode
    device_type_name  = $deviceTypeName
    cpu_model         = $cpuName
    cpu_speed         = $cpuSpeedGhz
    ram_capacity      = $ramGb
    ram_type          = $ramType
    ram_bus           = $ramBus
    ram_slots         = $ramSlotsStr
    storage_type      = $storageType
    storage_capacity  = $storageCap
    os_name           = $osName
    os_license        = $osLicense
    gpu_model         = $gpuModel
    monitor_size      = $monitorSize
    mac_address       = $macAddress
    ip_address        = $ipAddress
}

$jsonSpec = $specData | ConvertTo-Json -Compress

# Copy directly to Windows Clipboard
try {
    Set-Clipboard -Value $jsonSpec
} catch {
    $jsonSpec | clip.exe
}

# Display Summary
Write-Host ''
Write-Host '------------------ HARDWARE SCAN SUMMARY ------------------' -ForegroundColor Green
Write-Host "  Computer Name:  $computerName" -ForegroundColor White
Write-Host "  Brand / Model:  $brand $model" -ForegroundColor White
Write-Host "  Serial Number:  $serialNumber" -ForegroundColor Yellow
Write-Host "  Device Type:    $deviceTypeName ($deviceTypeCode)" -ForegroundColor White
Write-Host "  CPU Model:      $cpuName ($cpuSpeedGhz)" -ForegroundColor Cyan
Write-Host "  RAM Memory:     $ramGb GB $ramType ($ramBus) [$ramSlotsStr]" -ForegroundColor Cyan
Write-Host "  Primary Disk:   $storageType $storageCap ($diskModel)" -ForegroundColor Cyan
Write-Host "  Graphics (GPU): $gpuModel" -ForegroundColor Cyan
Write-Host "  OS Version:     $osName" -ForegroundColor Cyan
Write-Host "  Network:        IP: $ipAddress | MAC: $macAddress" -ForegroundColor White
Write-Host '-----------------------------------------------------------' -ForegroundColor Green
Write-Host ''
Write-Host '  [SUCCESS] Hardware specifications copied to Clipboard!' -ForegroundColor Green
Write-Host '  => Switch back to web browser and click "Paste Scan" (วางข้อมูลสเปค)' -ForegroundColor Yellow
Write-Host ''
