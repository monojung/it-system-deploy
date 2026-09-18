# ==============================================================================
# thc_audit_agent.ps1
# Thung Hua Chang Hospital - Client Hardware Audit Agent
# Embedded PowerShell Agent to collect physical hardware specs & transmit to IT Server
# Version: 2.1.0 (Compatible with IT System Platform v2.3.9)
# ==============================================================================

# Set console & pipeline encoding to UTF-8 for Thai character fidelity
try {
    [Console]::OutputEncoding = [System.Text.Encoding]::UTF8
    $OutputEncoding = [System.Text.Encoding]::UTF8
    chcp 65001 | Out-Null
} catch {}

# Enable TLS 1.2 & TLS 1.1 protocol on Windows PowerShell 5.1 & modern platforms
try {
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
} catch {}

# Parameters & Fallback Options (Full IEX pipeline and CLI compatibility)
if (-not (Get-Variable -Name ServerUrl -ErrorAction SilentlyContinue) -or [string]::IsNullOrWhiteSpace($ServerUrl)) {
    $ServerUrl = "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit"
}
if (-not (Get-Variable -Name FiscalYear -ErrorAction SilentlyContinue)) {
    $FiscalYear = 0
}
if (-not (Get-Variable -Name Silent -ErrorAction SilentlyContinue)) {
    $Silent = $false
}
if (-not (Get-Variable -Name AssetCode -ErrorAction SilentlyContinue) -or [string]::IsNullOrWhiteSpace($AssetCode)) {
    $AssetCode = ""
}

# Parse CLI arguments if run directly via powershell.exe -File
if ($args) {
    for ($i = 0; $i -lt $args.Count; $i++) {
        if ($args[$i] -eq '-ServerUrl' -and ($i + 1) -lt $args.Count) { $ServerUrl = $args[$i + 1] }
        if ($args[$i] -eq '-FiscalYear' -and ($i + 1) -lt $args.Count) { $FiscalYear = [int]$args[$i + 1] }
        if ($args[$i] -eq '-AssetCode' -and ($i + 1) -lt $args.Count) { $AssetCode = [string]$args[$i + 1] }
        if ($args[$i] -eq '-Silent') { $Silent = $true }
    }
}

$ErrorActionPreference = 'SilentlyContinue'

if (-not $Silent) {
    Write-Host ''
    Write-Host '==========================================================================' -ForegroundColor Cyan
    Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบตรวจนับและติดตามสเปคคอมพิวเตอร์ประจำปี' -ForegroundColor Yellow
    Write-Host '   THUNG HUA CHANG HOSPITAL - CLIENT HARDWARE AUDIT TELEMETRY AGENT v2.0' -ForegroundColor Gray
    Write-Host '==========================================================================' -ForegroundColor Cyan
    Write-Host 'กำลังตรวจสอบข้อมูลฮาร์ดแวร์จริงของเครื่อง กรุณารอสักครู่...' -ForegroundColor White
}

# ------------------------------------------------------------------------------
# 1. ข้อมูลระบบ เมนบอร์ด และตัวเครื่อง (System, Motherboard & Chassis)
# ------------------------------------------------------------------------------
$cs = Get-CimInstance Win32_ComputerSystem
$bios = Get-CimInstance Win32_Bios
$csp = Get-CimInstance Win32_ComputerSystemProduct
$bb = Get-CimInstance Win32_BaseBoard
$os = Get-CimInstance Win32_OperatingSystem

$computerName = $env:COMPUTERNAME

# กรองยี่ห้อ (Brand)
$rawBrand = if ($cs.Manufacturer) { $cs.Manufacturer.Trim() } else { '' }
$brand = $rawBrand
if ($brand -match 'Hewlett-Packard|HP Inc\.|HP') { $brand = 'HP' }
elseif ($brand -match 'Dell') { $brand = 'Dell' }
elseif ($brand -match 'Lenovo') { $brand = 'Lenovo' }
elseif ($brand -match 'ASUSTeK|ASUS') { $brand = 'ASUS' }
elseif ($brand -match 'Acer') { $brand = 'Acer' }
elseif ($brand -match 'Apple') { $brand = 'Apple' }

# กรองรุ่น (Model)
$model = if ($cs.Model) { ($cs.Model -replace '\s+', ' ').Trim() } else { '' }

# จัดการเครื่องประกอบ (Custom/Assembled PCs) ตรวจสอบจากเมนบอร์ด
$bbBrand = if ($bb.Manufacturer) { ($bb.Manufacturer -replace 'ASUSTeK.*', 'ASUS').Trim() } else { '' }
$bbProduct = if ($bb.Product) { ($bb.Product -replace '\s+', ' ').Trim() } else { '' }

if ($brand -match 'To be filled|System manufacturer|Default string' -or [string]::IsNullOrWhiteSpace($brand)) {
    if ($bbBrand -and $bbBrand -notmatch 'To be filled|Default string') {
        $brand = "เครื่องประกอบ ($bbBrand)"
    } else {
        $brand = "เครื่องประกอบ (Custom PC)"
    }
}

if ($model -match 'To be filled|System Product Name|Default string' -or [string]::IsNullOrWhiteSpace($model)) {
    if ($bbProduct -and $bbProduct -notmatch 'To be filled|Default string') {
        $model = "$bbProduct (เมนบอร์ด)"
    } else {
        $model = "Custom Assembled PC"
    }
}

# หมายเลขเครื่อง (Serial Number)
$serialNumber = if ($bios.SerialNumber) { $bios.SerialNumber.Trim() } else { '' }
if ($serialNumber -match 'Default string|To be filled by O\.E\.M\.|None') {
    $serialNumber = if ($bb.SerialNumber -and $bb.SerialNumber -notmatch 'Default string|None') { $bb.SerialNumber.Trim() } else { '' }
}

# Unique Hardware ID (SMBIOS System UUID / Fallback Motherboard+CPU ID)
$hardwareId = if ($csp.UUID) { $csp.UUID.Trim() } else { '' }
if ($hardwareId -match '^[0F-]{36}$' -or $hardwareId -match 'Default string|None' -or [string]::IsNullOrWhiteSpace($hardwareId)) {
    $cpuObj = Get-CimInstance Win32_Processor | Select-Object -First 1
    $cpuPart = if ($cpuObj -and $cpuObj.ProcessorId) { $cpuObj.ProcessorId.Trim() } else { 'CPUID' }
    $bbPart = if ($bb.SerialNumber -and $bb.SerialNumber -notmatch 'Default string|None') { $bb.SerialNumber.Trim() } else { $computerName }
    $hardwareId = "$bbPart-$cpuPart"
}

# ------------------------------------------------------------------------------
# 2. ประเภทอุปกรณ์ (Device Type)
# ------------------------------------------------------------------------------
$chassis = Get-CimInstance Win32_SystemEnclosure | Select-Object -First 1
$chassisType = if ($chassis.ChassisTypes) { $chassis.ChassisTypes[0] } else { 0 }
$battery = Get-CimInstance Win32_Battery
$isLaptop = ($null -ne $battery) -or ($chassisType -in @(8, 9, 10, 14, 30, 31, 32))
$isAIO = ($chassisType -eq 13) -or ($model -match 'All-in-One|AIO|ProOne|IdeaCentre AIO|Inspiron AIO')
$isServer = ($os.Caption -match 'Server') -or ($chassisType -in @(23, 28))

$deviceTypeCode = 'PC'
if ($isServer) { $deviceTypeCode = 'SERVER' }
elseif ($isLaptop) { $deviceTypeCode = 'NB' }
elseif ($isAIO) { $deviceTypeCode = 'AIO' }

# ------------------------------------------------------------------------------
# 3. หน่วยประมวลผล (Processor / CPU)
# ------------------------------------------------------------------------------
$cpu = Get-CimInstance Win32_Processor | Select-Object -First 1
$rawCpuName = if ($cpu.Name) { $cpu.Name } else { 'Intel Core Processor' }
$cleanCpu = $rawCpuName -replace '\(R\)|\(TM\)|\(tm\)|CPU\s*@.*|@\s*[\d\.]+\s*GHz', ''
$cleanCpu = ($cleanCpu -replace '\s+', ' ').Trim()

$cores = $cpu.NumberOfCores
$threads = $cpu.NumberOfLogicalProcessors
$baseSpeedGhz = if ($cpu.MaxClockSpeed) { [math]::Round($cpu.MaxClockSpeed / 1000, 2).ToString() + ' GHz' } else { '' }
$cpuSpeedFormatted = if ($cores -and $threads) { "$baseSpeedGhz ($cores Cores / $threads Threads)" } else { $baseSpeedGhz }

# ------------------------------------------------------------------------------
# 4. หน่วยความจำหลัก (RAM Memory)
# ------------------------------------------------------------------------------
$memoryModules = @(Get-CimInstance Win32_PhysicalMemory)
$totalRamBytes = ($memoryModules | Measure-Object -Property Capacity -Sum).Sum
$ramGb = [int][math]::Round($totalRamBytes / 1GB)
if ($ramGb -le 0) { $ramGb = 8 }

$slotCount = if ($memoryModules.Count -gt 0) { $memoryModules.Count } else { 1 }
$stickSizes = $memoryModules | ForEach-Object { "$([int][math]::Round($_.Capacity / 1GB))GB" }
$allSameSize = ($stickSizes | Select-Object -Unique).Count -le 1

$ramSlotsFormatted = if ($allSameSize) {
    "$($stickSizes[0]) x $slotCount ช่อง"
} else {
    ($stickSizes -join ' + ') + " ($slotCount ช่อง)"
}

$ramSpeedMhz = if ($memoryModules.Count -gt 0 -and $memoryModules[0].ConfiguredClockSpeed) { $memoryModules[0].ConfiguredClockSpeed } else { 0 }
$ramBus = if ($ramSpeedMhz -gt 0) { "$ramSpeedMhz MHz" } else { '2666 MHz' }

$ramTypeSmbios = if ($memoryModules.Count -gt 0) { $memoryModules[0].SMBIOSMemoryType } else { 0 }
$ramType = 'DDR4'
if ($ramTypeSmbios -in @(34, 35) -or $ramSpeedMhz -ge 4800) {
    $ramType = 'DDR5'
} elseif ($ramTypeSmbios -eq 26 -or ($ramSpeedMhz -ge 2133 -and $ramSpeedMhz -lt 4800)) {
    $ramType = 'DDR4'
} elseif ($ramTypeSmbios -eq 24 -or ($ramSpeedMhz -ge 1066 -and $ramSpeedMhz -lt 2133)) {
    $ramType = 'DDR3'
}

# ------------------------------------------------------------------------------
# 5. อุปกรณ์จัดเก็บข้อมูล (Storage - ไดรฟ์หลักติดตั้ง OS + ไดรฟ์รอง)
# ------------------------------------------------------------------------------
$disks = @(Get-CimInstance Win32_DiskDrive | Where-Object { $_.Size -gt 0 -and $_.InterfaceType -ne 'USB' })
if ($disks.Count -eq 0) {
    $disks = @(Get-CimInstance Win32_DiskDrive | Where-Object { $_.Size -gt 0 })
}

# ค้นหาดิสก์จริงที่เป็นไดรฟ์บูตระบบปฏิบัติการ (Drive C:)
$osDiskIndex = 0
try {
    $part = Get-CimInstance -Query "ASSOCIATORS OF {Win32_LogicalDisk.DeviceID='C:'} WHERE AssocClass = Win32_LogicalDiskToPartition" | Select-Object -First 1
    if ($part) {
        $phys = Get-CimInstance -Query "ASSOCIATORS OF {Win32_DiskPartition.DeviceID='$($part.DeviceID)'} WHERE AssocClass = Win32_DiskDriveToDiskPartition" | Select-Object -First 1
        if ($phys) { $osDiskIndex = $phys.Index }
    }
} catch {}

function Get-CleanDiskInfo($disk) {
    $sizeGb = [int][math]::Round($disk.Size / 1GB)
    $cap = "$sizeGb GB"
    if ($sizeGb -ge 1800) { $cap = "2 TB" }
    elseif ($sizeGb -ge 900) { $cap = "1 TB" }
    elseif ($sizeGb -ge 450 -and $sizeGb -le 530) {
        $cap = if ($disk.Model -match '500|480') { "500 GB" } else { "512 GB" }
    }
    elseif ($sizeGb -ge 220 -and $sizeGb -le 260) {
        $cap = if ($disk.Model -match '240') { "240 GB" } else { "256 GB" }
    }
    elseif ($sizeGb -ge 110 -and $sizeGb -le 130) {
        $cap = if ($disk.Model -match '120') { "120 GB" } else { "128 GB" }
    }

    $type = 'SSD SATA 2.5"'
    if ($disk.Model -match 'NVMe|PCIe|M\.2|Optane' -or ($disk.InterfaceType -match 'SCSI' -and $disk.Model -match 'SSD|SN\d+')) {
        $type = 'SSD NVMe M.2'
    } elseif ($disk.MediaType -match 'Fixed' -and $disk.Model -notmatch 'SSD|Flash|SN\d+') {
        $type = 'HDD SATA 3.5"'
    }

    $cleanModel = ($disk.Model -replace 'SCSI Disk Device|ATA Device|\s+', ' ').Trim()
    return @{
        Type     = $type
        Capacity = $cap
        Model    = $cleanModel
        Summary  = "$type $cap ($cleanModel)"
    }
}

$primaryDiskObj = $disks | Where-Object { $_.Index -eq $osDiskIndex } | Select-Object -First 1
if (-not $primaryDiskObj -and $disks.Count -gt 0) { $primaryDiskObj = $disks[0] }

$primaryInfo = if ($primaryDiskObj) { Get-CleanDiskInfo $primaryDiskObj } else { @{ Type='SSD SATA 2.5"'; Capacity='512 GB'; Summary='SSD 512 GB' } }
$storageType = $primaryInfo.Type
$storageCap = $primaryInfo.Capacity

$secondaryDisks = @($disks | Where-Object { $_.Index -ne $osDiskIndex })
$storageSecond = if ($secondaryDisks.Count -gt 0) {
    $secInfo = Get-CleanDiskInfo $secondaryDisks[0]
    $secInfo.Summary
} else { '' }

# ------------------------------------------------------------------------------
# 6. ระบบปฏิบัติการและลิขสิทธิ์ (Operating System & Genuine License)
# ------------------------------------------------------------------------------
$rawCaption = if ($os.Caption) { ($os.Caption -replace 'Microsoft ', '').Trim() } else { 'Windows 11 Pro' }
$osArch = if ($os.OSArchitecture) { $os.OSArchitecture } else { '64-bit' }
$reg = Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion"
$dispVer = if ($reg.DisplayVersion) { $reg.DisplayVersion } elseif ($reg.ReleaseId) { $reg.ReleaseId } else { '' }
$buildNum = if ($os.BuildNumber) { "Build $($os.BuildNumber)" } else { '' }

$osParts = @($rawCaption, $osArch)
$verSuffix = @($dispVer, $buildNum) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }
if ($verSuffix.Count -gt 0) {
    $osParts += "(" + ($verSuffix -join ' ') + ")"
}
$osName = $osParts -join ' '

# ตรวจสอบสถานะการ Activate ลิขสิทธิ์แท้
$osLicense = 'Windows แท้ (เปิดใช้งานแล้ว)'
try {
    $sls = Get-CimInstance SoftwareLicensingService
    $hasOA3 = -not [string]::IsNullOrWhiteSpace($sls.OA3xOriginalProductKey)
    $lic = Get-CimInstance -Query "SELECT LicenseStatus, Description FROM SoftwareLicensingProduct WHERE ApplicationId = '55c92734-d682-4d71-983e-d6ec3f16059f' AND LicenseStatus = 1" | Select-Object -First 1

    if ($lic -and $lic.LicenseStatus -eq 1) {
        if ($hasOA3) {
            $osLicense = 'OEM (ติดเครื่อง/BIOS OA3.0) - เปิดใช้งานแล้ว'
        } elseif ($lic.Description -match 'VOLUME_KMS') {
            $osLicense = 'Volume License (KMS) - เปิดใช้งานแล้ว'
        } elseif ($lic.Description -match 'VOLUME_MAK') {
            $osLicense = 'Volume License (MAK) - เปิดใช้งานแล้ว'
        } elseif ($lic.Description -match 'RETAIL') {
            $osLicense = 'Digital License / Retail - เปิดใช้งานแล้ว'
        } else {
            $osLicense = 'ลิขสิทธิ์ถูกต้อง - เปิดใช้งานแล้ว (Activated)'
        }
    } else {
        $osLicense = 'ยังไม่ได้เปิดใช้งาน (Not Activated / Grace Period)'
    }
} catch {
    $osLicense = 'OEM (ติดเครื่อง/BIOS)'
}

# ------------------------------------------------------------------------------
# 7. การ์ดจอและระบบแสดงผล (GPU & Displays)
# ------------------------------------------------------------------------------
$gpu = Get-CimInstance Win32_VideoController | Where-Object { $_.Name -notmatch 'Virtual|Remote|Basic Render|Radmin' } | Select-Object -First 1
$gpuModel = 'Onboard / Integrated'
if ($gpu -and $gpu.Name) {
    $cleanGpu = ($gpu.Name -replace '\(R\)|\(TM\)|\(tm\)', '').Trim()
    $cleanGpu = ($cleanGpu -replace '\s+', ' ').Trim()
    $vramGb = if ($gpu.AdapterRAM -gt 0) { [int][math]::Round($gpu.AdapterRAM / 1GB) } else { 0 }
    if ($vramGb -gt 0) {
        $gpuModel = "$cleanGpu ($vramGb GB)"
    } else {
        $gpuModel = $cleanGpu
    }
}

# ตรวจสอบจอภาพจาก WmiMonitorID & WmiMonitorBasicDisplayParams
$monitors = @(Get-CimInstance -Namespace root\wmi -ClassName WmiMonitorID)
$monParams = @(Get-CimInstance -Namespace root\wmi -ClassName WmiMonitorBasicDisplayParams)
$vc = Get-CimInstance Win32_VideoController | Select-Object -First 1
$resStr = if ($vc.CurrentHorizontalResolution -and $vc.CurrentVerticalResolution) {
    "$($vc.CurrentHorizontalResolution)x$($vc.CurrentVerticalResolution)"
} else { '' }

$monitorStr = ''
if ($monitors.Count -gt 0) {
    $mNames = @()
    for ($i = 0; $i -lt $monitors.Count; $i++) {
        $m = $monitors[$i]
        $nameBytes = $m.UserFriendlyName | Where-Object { $_ -gt 0 }
        $mName = if ($nameBytes) { ([System.Text.Encoding]::ASCII.GetString($nameBytes)).Trim() } else { '' }
        
        $diagInches = 0
        if ($i -lt $monParams.Count -and $monParams[$i].MaxHorizontalImageSize -gt 0) {
            $w = $monParams[$i].MaxHorizontalImageSize
            $h = $monParams[$i].MaxVerticalImageSize
            $diagInches = [math]::Round([math]::Sqrt($w*$w + $h*$h) / 2.54, 1)
        }

        $item = if ($mName -and $diagInches -gt 0) {
            "$mName $diagInches นิ้ว"
        } elseif ($mName) {
            $mName
        } elseif ($diagInches -gt 0) {
            "จอภาพ $diagInches นิ้ว"
        } else {
            "จอแสดงผล"
        }
        $mNames += $item
    }
    $resSuffix = if ($resStr) { " ($resStr)" } else { '' }
    $monitorStr = ($mNames -join ', ') + $resSuffix
} else {
    $resSuffix = if ($resStr) { " ($resStr)" } else { '' }
    if ($isLaptop) {
        $monitorStr = "หน้าจอโน้ตบุ๊ก" + $resSuffix
    } else {
        $monitorStr = "จอแสดงผล" + $resSuffix
    }
}

# ------------------------------------------------------------------------------
# 8. เครือข่าย (Network IP & MAC)
# ------------------------------------------------------------------------------
$net = Get-CimInstance Win32_NetworkAdapterConfiguration | Where-Object { $_.IPEnabled -and $_.DefaultIPGateway } | Select-Object -First 1
if (-not $net) {
    $net = Get-CimInstance Win32_NetworkAdapterConfiguration | Where-Object { $_.IPEnabled -and $_.MACAddress } | Select-Object -First 1
}
$macAddress = if ($net -and $net.MACAddress) { $net.MACAddress.ToUpper() } else { '' }
$ipAddress = if ($net -and $net.IPAddress) { $net.IPAddress[0] } else { '' }

# ------------------------------------------------------------------------------
# 9. เตรียมข้อมูล JSON Payload สำหรับส่งเข้าสู่เซิร์ฟเวอร์
# ------------------------------------------------------------------------------
$payload = [ordered]@{
    hostname              = $computerName
    hardware_id           = $hardwareId
    serial_number         = $serialNumber
    mac_address           = $macAddress
    ip_address            = $ipAddress
    brand                 = $brand
    model                 = $model
    device_type_code      = $deviceTypeCode
    cpu_model             = $cleanCpu
    cpu_speed             = $cpuSpeedFormatted
    ram_capacity          = $ramGb
    ram_type              = $ramType
    ram_bus               = $ramBus
    ram_slots             = $ramSlotsFormatted
    storage_type          = $storageType
    storage_capacity      = $storageCap
    storage_second        = $storageSecond
    os_name               = $osName
    os_license            = $osLicense
    gpu_model             = $gpuModel
    monitor_size          = $monitorStr
    client_agent_version  = '2.1.0'
}

if ($FiscalYear -gt 0) {
    $payload['fiscal_year'] = $FiscalYear
}
if (-not [string]::IsNullOrWhiteSpace($AssetCode)) {
    $payload['asset_code'] = $AssetCode
}

$jsonBody = $payload | ConvertTo-Json -Compress

# ------------------------------------------------------------------------------
# แสดงผลลัพธ์บนหน้าจอ Terminal ให้ผู้ใช้งานและช่างไอทีอ่านเข้าใจง่าย ชัดเจน
# ------------------------------------------------------------------------------
if (-not $Silent) {
    Write-Host ''
    Write-Host '----------------------- ข้อมูลสเปคคอมพิวเตอร์ที่ตรวจพบ -----------------------' -ForegroundColor Green
    Write-Host "  ชื่อเครื่อง (Host):   $computerName" -ForegroundColor White
    Write-Host "  Hardware ID (UUID):  $hardwareId" -ForegroundColor Magenta
    if ($AssetCode) {
        Write-Host "  รหัสครุภัณฑ์ (Asset): $AssetCode" -ForegroundColor Yellow
    }
    Write-Host "  ยี่ห้อ / รุ่นเครื่อง:    $brand $model" -ForegroundColor White
    if ($serialNumber) {
        Write-Host "  หมายเลข Serial:      $serialNumber" -ForegroundColor Yellow
    }
    Write-Host "  ประเภทอุปกรณ์:       $deviceTypeCode" -ForegroundColor White
    Write-Host "  หน่วยประมวลผล (CPU): $cleanCpu ($cpuSpeedFormatted)" -ForegroundColor Cyan
    Write-Host "  หน่วยความจำ (RAM):   $ramGb GB $ramType @ $ramBus [$ramSlotsFormatted]" -ForegroundColor Cyan
    Write-Host "  ฮาร์ดดิสก์หลัก (OS):  $storageType $storageCap" -ForegroundColor Cyan
    if ($storageSecond) {
        Write-Host "  ฮาร์ดดิสก์เสริม (2):  $storageSecond" -ForegroundColor Cyan
    }
    Write-Host "  การ์ดจอแสดงผล (GPU): $gpuModel" -ForegroundColor Cyan
    Write-Host "  จอภาพ (Monitor):     $monitorStr" -ForegroundColor Cyan
    Write-Host "  ระบบปฏิบัติการ (OS):  $osName" -ForegroundColor Cyan
    Write-Host "  สถานะลิขสิทธิ์:       $osLicense" -ForegroundColor Yellow
    Write-Host "  การเชื่อมต่อระบบ:     IP: $ipAddress | MAC: $macAddress" -ForegroundColor White
    Write-Host '-----------------------------------------------------------------------------' -ForegroundColor Green
    Write-Host ''
    Write-Host "กำลังส่งข้อมูลเข้าสู่ระบบ IT โรงพยาบาลทุ่งหัวช้าง..." -ForegroundColor Yellow
}

# ------------------------------------------------------------------------------
# 10. ส่งข้อมูลเข้าสู่ระบบ IT ผ่าน Web API (HTTP POST)
# ------------------------------------------------------------------------------
$candidateUrls = @(
    $ServerUrl,
    "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit",
    "http://192.168.2.89/it-system/api/hardware-audit/submit",
    "http://localhost:8000/api/hardware-audit/submit"
) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) } | Select-Object -Unique

$transmitted = $false
foreach ($targetUrl in $candidateUrls) {
    if (-not $Silent) {
        Write-Host "  เชื่อมต่อไปยังเซิร์ฟเวอร์: $targetUrl" -ForegroundColor Gray
    }
    try {
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
        [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
        $bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonBody)
        $req = [System.Net.HttpWebRequest]::Create($targetUrl)
        $req.Method = "POST"
        $req.ContentType = "application/json; charset=utf-8"
        $req.Accept = "application/json"
        $req.Timeout = 10000
        $req.UserAgent = "THC-Audit-Agent/2.1"
        
        $reqStream = $req.GetRequestStream()
        $reqStream.Write($bytes, 0, $bytes.Length)
        $reqStream.Close()

        $resp = $req.GetResponse()
        $respStream = $resp.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($respStream, [System.Text.Encoding]::UTF8)
        $responseString = $reader.ReadToEnd()
        $reader.Close()
        $resp.Close()

        if (-not $Silent) {
            Write-Host '  [สำเร็จ] ส่งข้อมูลเข้าสู่ระบบ IT เรียบร้อยแล้ว!' -ForegroundColor Green
            Write-Host '  => สถานะ: บันทึกข้อมูลเข้าสู่ระบบรอเจ้าหน้าที่ไอทีตรวจสอบและอนุมัติ' -ForegroundColor Yellow
        }
        $transmitted = $true
        break
    } catch {
        $firstErr = $_.Exception.Message
        try {
            [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
            [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
            $bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonBody)
            $headers = @{
                "Content-Type" = "application/json; charset=utf-8"
                "Accept"       = "application/json"
            }
            $respObj = Invoke-RestMethod -Uri $targetUrl -Method Post -Body $jsonBody -Headers $headers -ContentType 'application/json; charset=utf-8' -TimeoutSec 10
            if (-not $Silent) {
                Write-Host '  [สำเร็จ] ส่งข้อมูลเข้าสู่ระบบ IT เรียบร้อยแล้ว (ผ่าน Fallback)!' -ForegroundColor Green
                Write-Host '  => สถานะ: บันทึกข้อมูลเข้าสู่ระบบรอเจ้าหน้าที่ไอทีตรวจสอบและอนุมัติ' -ForegroundColor Yellow
            }
            $transmitted = $true
            break
        } catch {
            if (-not $Silent) {
                Write-Host "  [ไม่สามารถเชื่อมต่อได้] $targetUrl ($firstErr / $($_.Exception.Message))" -ForegroundColor DarkGray
            }
        }
    }
}

if (-not $transmitted -and -not $Silent) {
    Write-Host "  [ข้อควรระวัง] ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ IT ได้ในขณะนี้" -ForegroundColor Red
    Write-Host "  โปรดตรวจสอบการเชื่อมต่ออินเทอร์เน็ตหรือเครือข่ายภายในโรงพยาบาล" -ForegroundColor White
}

# บันทึกสำเนาล่าสุดไว้ที่เครื่อง (Local Cache)
try {
    $localDir = "C:\ProgramData\THC-IT-Agent"
    if (-not (Test-Path $localDir)) {
        New-Item -ItemType Directory -Path $localDir -Force | Out-Null
    }
    $jsonBody | Out-File -FilePath "$localDir\last_audit.json" -Encoding UTF8 -Force
} catch {}

if (-not $Silent) {
    Write-Host ''
}
