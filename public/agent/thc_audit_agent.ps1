# ==============================================================================
# thc_audit_agent.ps1
# Thung Hua Chang Hospital - Client Hardware Audit Agent
# Embedded PowerShell Agent to collect physical hardware specs & transmit to IT Server
# Version: 2.2.0 (With Remote Scan Command & Background Polling Engine)
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

# Parameters & Fallback Options
$ServerUrl = "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit"
$FiscalYear = 0
$Silent = $false
$AssetCode = ""
$Background = $false
$PollOnce = $false
$ForceScan = $false
$ActiveCommandId = 0

# Parse CLI arguments if run directly via powershell.exe -File
if ($args) {
    for ($i = 0; $i -lt $args.Count; $i++) {
        if ($args[$i] -eq '-ServerUrl' -and ($i + 1) -lt $args.Count) { $ServerUrl = $args[$i + 1] }
        if ($args[$i] -eq '-FiscalYear' -and ($i + 1) -lt $args.Count) { $FiscalYear = [int]$args[$i + 1] }
        if ($args[$i] -eq '-AssetCode' -and ($i + 1) -lt $args.Count) { $AssetCode = [string]$args[$i + 1] }
        if ($args[$i] -eq '-CommandId' -and ($i + 1) -lt $args.Count) { $ActiveCommandId = [int]$args[$i + 1] }
        if ($args[$i] -eq '-Silent') { $Silent = $true }
        if ($args[$i] -eq '-Background') { $Background = $true; $Silent = $true }
        if ($args[$i] -eq '-PollOnce') { $PollOnce = $true; $Silent = $true }
        if ($args[$i] -eq '-ForceScan') { $ForceScan = $true }
    }
}

$ErrorActionPreference = 'SilentlyContinue'

# List of Server Endpoints to attempt
$candidateUrls = @(
    $ServerUrl,
    "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit",
    "http://192.168.2.89/it-system/api/hardware-audit/submit",
    "http://localhost:8000/api/hardware-audit/submit"
) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) } | Select-Object -Unique

# ------------------------------------------------------------------------------
# Helper Function: Quick Computer Hardware Identifiers
# ------------------------------------------------------------------------------
function Get-MachineIdentifiers {
    $comp = $env:COMPUTERNAME
    $csp = Get-CimInstance Win32_ComputerSystemProduct -ErrorAction SilentlyContinue
    $bb = Get-CimInstance Win32_BaseBoard -ErrorAction SilentlyContinue

    $hwId = if ($csp -and $csp.UUID) { $csp.UUID.Trim() } else { '' }
    if ($hwId -match '^[0F-]{36}$' -or $hwId -match 'Default string|None' -or [string]::IsNullOrWhiteSpace($hwId)) {
        $cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
        $cpuPart = if ($cpuObj -and $cpuObj.ProcessorId) { $cpuObj.ProcessorId.Trim() } else { 'CPUID' }
        $bbPart = if ($bb -and $bb.SerialNumber -and $bb.SerialNumber -notmatch 'Default string|None') { $bb.SerialNumber.Trim() } else { $comp }
        $hwId = "$bbPart-$cpuPart"
    }

    $net = Get-CimInstance Win32_NetworkAdapterConfiguration -ErrorAction SilentlyContinue | Where-Object { $_.IPEnabled -and $_.DefaultIPGateway } | Select-Object -First 1
    if (-not $net) {
        $net = Get-CimInstance Win32_NetworkAdapterConfiguration -ErrorAction SilentlyContinue | Where-Object { $_.IPEnabled -and $_.MACAddress } | Select-Object -First 1
    }
    $mac = if ($net -and $net.MACAddress) { $net.MACAddress.ToUpper() } else { '' }
    $ip = if ($net -and $net.IPAddress) { $net.IPAddress[0] } else { '' }

    return [PSCustomObject]@{
        ComputerName = $comp
        HardwareId   = $hwId
        MacAddress   = $mac
        IpAddress    = $ip
    }
}

# ------------------------------------------------------------------------------
# Function: Collect Full Specs & Submit to IT Server
# ------------------------------------------------------------------------------
function Invoke-HardwareAudit {
    param (
        [int]$CommandId = 0,
        [bool]$IsSilent = $false
    )

    if (-not $IsSilent) {
        Write-Host ''
        Write-Host '==========================================================================' -ForegroundColor Cyan
        Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบตรวจนับและติดตามสเปคคอมพิวเตอร์ประจำปี' -ForegroundColor Yellow
        Write-Host '   THUNG HUA CHANG HOSPITAL - CLIENT HARDWARE AUDIT AGENT v2.2.0' -ForegroundColor Gray
        Write-Host '==========================================================================' -ForegroundColor Cyan
        Write-Host 'กำลังตรวจสอบข้อมูลฮาร์ดแวร์จริงของเครื่อง กรุณารอสักครู่...' -ForegroundColor White
    }

    # 1. ข้อมูลระบบ เมนบอร์ด และตัวเครื่อง (System, Motherboard & Chassis)
    $cs = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
    $bios = Get-CimInstance Win32_Bios -ErrorAction SilentlyContinue
    $csp = Get-CimInstance Win32_ComputerSystemProduct -ErrorAction SilentlyContinue
    $bb = Get-CimInstance Win32_BaseBoard -ErrorAction SilentlyContinue
    $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue

    $computerName = $env:COMPUTERNAME

    # กรองยี่ห้อ (Brand)
    $rawBrand = if ($cs -and $cs.Manufacturer) { $cs.Manufacturer.Trim() } else { '' }
    $brand = $rawBrand
    if ($brand -match 'Hewlett-Packard|HP Inc\.|HP') { $brand = 'HP' }
    elseif ($brand -match 'Dell') { $brand = 'Dell' }
    elseif ($brand -match 'Lenovo') { $brand = 'Lenovo' }
    elseif ($brand -match 'ASUSTeK|ASUS') { $brand = 'ASUS' }
    elseif ($brand -match 'Acer') { $brand = 'Acer' }
    elseif ($brand -match 'Apple') { $brand = 'Apple' }

    # กรองรุ่น (Model)
    $model = if ($cs -and $cs.Model) { ($cs.Model -replace '\s+', ' ').Trim() } else { '' }

    # จัดการเครื่องประกอบ (Custom/Assembled PCs) ตรวจสอบจากเมนบอร์ด
    $bbBrand = if ($bb -and $bb.Manufacturer) { ($bb.Manufacturer -replace 'ASUSTeK.*', 'ASUS').Trim() } else { '' }
    $bbProduct = if ($bb -and $bb.Product) { ($bb.Product -replace '\s+', ' ').Trim() } else { '' }

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
    $serialNumber = if ($bios -and $bios.SerialNumber) { $bios.SerialNumber.Trim() } else { '' }
    if ($serialNumber -match 'Default string|To be filled by O\.E\.M\.|None') {
        $serialNumber = if ($bb -and $bb.SerialNumber -and $bb.SerialNumber -notmatch 'Default string|None') { $bb.SerialNumber.Trim() } else { '' }
    }

    # Unique Hardware ID (SMBIOS System UUID / Fallback Motherboard+CPU ID)
    $hardwareId = if ($csp -and $csp.UUID) { $csp.UUID.Trim() } else { '' }
    if ($hardwareId -match '^[0F-]{36}$' -or $hardwareId -match 'Default string|None' -or [string]::IsNullOrWhiteSpace($hardwareId)) {
        $cpuObj = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
        $cpuPart = if ($cpuObj -and $cpuObj.ProcessorId) { $cpuObj.ProcessorId.Trim() } else { 'CPUID' }
        $bbPart = if ($bb -and $bb.SerialNumber -and $bb.SerialNumber -notmatch 'Default string|None') { $bb.SerialNumber.Trim() } else { $computerName }
        $hardwareId = "$bbPart-$cpuPart"
    }

    # 2. ประเภทอุปกรณ์ (Device Type)
    $chassis = Get-CimInstance Win32_SystemEnclosure -ErrorAction SilentlyContinue | Select-Object -First 1
    $chassisType = if ($chassis -and $chassis.ChassisTypes) { $chassis.ChassisTypes[0] } else { 0 }
    $battery = Get-CimInstance Win32_Battery -ErrorAction SilentlyContinue
    $isLaptop = ($null -ne $battery) -or ($chassisType -in @(8, 9, 10, 14, 30, 31, 32))
    $isAIO = ($chassisType -eq 13) -or ($model -match 'All-in-One|AIO|ProOne|IdeaCentre AIO|Inspiron AIO')
    $isServer = ($os -and $os.Caption -match 'Server') -or ($chassisType -in @(23, 28))

    $deviceTypeCode = 'PC'
    if ($isServer) { $deviceTypeCode = 'SERVER' }
    elseif ($isLaptop) { $deviceTypeCode = 'NB' }
    elseif ($isAIO) { $deviceTypeCode = 'AIO' }

    # 3. หน่วยประมวลผล (Processor / CPU)
    $cpu = Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1
    $rawCpuName = if ($cpu -and $cpu.Name) { $cpu.Name } else { 'Intel Core Processor' }
    $cleanCpu = $rawCpuName -replace '\(R\)|\(TM\)|\(tm\)|CPU\s*@.*|@\s*[\d\.]+\s*GHz', ''
    $cleanCpu = ($cleanCpu -replace '\s+', ' ').Trim()

    $cores = if ($cpu) { $cpu.NumberOfCores } else { 4 }
    $threads = if ($cpu) { $cpu.NumberOfLogicalProcessors } else { 4 }
    $baseSpeedGhz = if ($cpu -and $cpu.MaxClockSpeed) { [math]::Round($cpu.MaxClockSpeed / 1000, 2).ToString() + ' GHz' } else { '' }
    $cpuSpeedFormatted = if ($cores -and $threads) { "$baseSpeedGhz ($cores Cores / $threads Threads)" } else { $baseSpeedGhz }

    # 4. หน่วยความจำหลัก (RAM Memory)
    $memoryModules = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    $totalRamBytes = ($memoryModules | Measure-Object -Property Capacity -Sum).Sum
    $ramGb = [int][math]::Round($totalRamBytes / 1GB)
    if ($ramGb -le 0) { $ramGb = 8 }

    $firstRam = $memoryModules | Select-Object -First 1
    $ramSpeed = if ($firstRam -and $firstRam.Speed) { "$($firstRam.Speed) MHz" } else { '' }
    $ramBus = $ramSpeed

    $ramType = 'DDR4'
    if ($firstRam) {
        $smBiosType = $firstRam.SMBIOSMemoryType
        switch ($smBiosType) {
            20 { $ramType = 'DDR' }
            21 { $ramType = 'DDR2' }
            24 { $ramType = 'DDR3' }
            26 { $ramType = 'DDR4' }
            30 { $ramType = 'DDR5' }
            34 { $ramType = 'LPDDR4' }
            35 { $ramType = 'LPDDR5' }
            default {
                if ($firstRam.Speed -gt 4000) { $ramType = 'DDR5' }
                elseif ($firstRam.Speed -gt 2000) { $ramType = 'DDR4' }
                elseif ($firstRam.Speed -gt 1000) { $ramType = 'DDR3' }
            }
        }
    }

    $installedSlots = $memoryModules.Count
    $ramSlotsFormatted = "$installedSlots Slot(s)"

    # 5. พื้นที่จัดเก็บข้อมูลหลักและรอง (Storage Drives)
    $disks = @(Get-CimInstance Win32_DiskDrive -ErrorAction SilentlyContinue | Where-Object { $_.Size -gt 10GB })
    $primaryDisk = $disks | Select-Object -First 1

    $storageType = 'SSD'
    $storageCap = '256 GB'

    if ($primaryDisk) {
        $capGb = [math]::Round($primaryDisk.Size / 1GB)
        if ($capGb -ge 900) {
            $storageCap = [math]::Round($capGb / 1000, 1).ToString() + ' TB'
        } else {
            $storageCap = "$capGb GB"
        }

        $mediaType = if ($primaryDisk.MediaType) { $primaryDisk.MediaType } else { '' }
        $modelStr = if ($primaryDisk.Model) { $primaryDisk.Model } else { '' }

        if ($modelStr -match 'NVMe|PCIe|M\.2|Optane') {
            $storageType = 'NVMe M.2 SSD'
        } elseif ($modelStr -match 'SSD' -or $mediaType -match 'SSD|Solid State') {
            $storageType = 'SATA SSD'
        } elseif ($mediaType -match 'Fixed hard disk|Hard Disk' -or $modelStr -match 'HDD|ST\d+|WDC\s*WD') {
            $storageType = 'HDD'
        } else {
            $storageType = 'SSD'
        }
    }

    $storageSecond = ''
    if ($disks.Count -gt 1) {
        $secDisk = $disks[1]
        $secCapGb = [math]::Round($secDisk.Size / 1GB)
        $secCap = if ($secCapGb -ge 900) { [math]::Round($secCapGb / 1000, 1).ToString() + ' TB' } else { "$secCapGb GB" }
        $secType = if ($secDisk.Model -match 'SSD') { 'SSD' } else { 'HDD' }
        $storageSecond = "$secType $secCap ($($secDisk.Model))"
    }

    # 6. ระบบปฏิบัติการและลิขสิทธิ์ (Operating System & License)
    $osName = if ($os -and $os.Caption) { ($os.Caption -replace 'Microsoft\s*', '').Trim() } else { 'Windows 10 Pro' }
    $osLicense = 'Digital License / OEM'

    # 7. การ์ดแสดงผลและการแสดงผล (GPU & Displays)
    $gpu = Get-CimInstance Win32_VideoController -ErrorAction SilentlyContinue | Where-Object { $_.Name -notmatch 'Virtual|Remote|Citrix' } | Select-Object -First 1
    $gpuModel = if ($gpu -and $gpu.Name) { $gpu.Name.Trim() } else { 'Integrated Graphics' }

    $monitor = Get-CimInstance Win32_DesktopMonitor -ErrorAction SilentlyContinue | Select-Object -First 1
    $monitorStr = if ($monitor -and $monitor.ScreenWidth -and $monitor.ScreenHeight) {
        "$($monitor.ScreenWidth)x$($monitor.ScreenHeight)"
    } else {
        if ($isLaptop) { "14.0/15.6 Inch (Built-in)" } else { "21.5 / 23.8 Inch Display" }
    }

    # 8. เครือข่าย (Network IP & MAC)
    $ident = Get-MachineIdentifiers
    $macAddress = $ident.MacAddress
    $ipAddress = $ident.IpAddress

    # 9. เตรียมข้อมูล JSON Payload
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
        client_agent_version  = '2.2.0'
    }

    if ($FiscalYear -gt 0) {
        $payload['fiscal_year'] = $FiscalYear
    }
    if (-not [string]::IsNullOrWhiteSpace($AssetCode)) {
        $payload['asset_code'] = $AssetCode
    }
    if ($CommandId -gt 0) {
        $payload['command_id'] = $CommandId
    }

    $jsonBody = $payload | ConvertTo-Json -Compress

    if (-not $IsSilent) {
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
        if ($CommandId -gt 0) {
            Write-Host "  ตอบรับคำสั่งสแกน ID:  #$CommandId" -ForegroundColor Green
        }
        Write-Host '-----------------------------------------------------------------------------' -ForegroundColor Green
        Write-Host ''
        Write-Host "กำลังส่งข้อมูลเข้าสู่ระบบ IT โรงพยาบาลทุ่งหัวช้าง..." -ForegroundColor Yellow
    }

    # 10. ส่งข้อมูลเข้าสู่ระบบ IT ผ่าน Web API (HTTP POST)
    $transmitted = $false
    foreach ($targetUrl in $candidateUrls) {
        if (-not $IsSilent) {
            Write-Host "  เชื่อมต่อไปยังเซิร์ฟเวอร์: $targetUrl" -ForegroundColor Gray
        }
        try {
            $bytes = [System.Text.Encoding]::UTF8.GetBytes($jsonBody)
            $req = [System.Net.HttpWebRequest]::Create($targetUrl)
            $req.Method = "POST"
            $req.ContentType = "application/json; charset=utf-8"
            $req.Accept = "application/json"
            $req.Timeout = 10000
            $req.UserAgent = "THC-Audit-Agent/2.2.0"
            
            $reqStream = $req.GetRequestStream()
            $reqStream.Write($bytes, 0, $bytes.Length)
            $reqStream.Close()

            $resp = $req.GetResponse()
            $respStream = $resp.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($respStream, [System.Text.Encoding]::UTF8)
            $responseString = $reader.ReadToEnd()
            $reader.Close()
            $resp.Close()

            if (-not $IsSilent) {
                Write-Host '  [สำเร็จ] ส่งข้อมูลเข้าสู่ระบบ IT เรียบร้อยแล้ว!' -ForegroundColor Green
                Write-Host '  => สถานะ: บันทึกข้อมูลเข้าสู่ระบบรอเจ้าหน้าที่ไอทีตรวจสอบและอนุมัติ' -ForegroundColor Yellow
            }
            $transmitted = $true
            break
        } catch {
            try {
                $headers = @{
                    "Content-Type" = "application/json; charset=utf-8"
                    "Accept"       = "application/json"
                }
                $respObj = Invoke-RestMethod -Uri $targetUrl -Method Post -Body $jsonBody -Headers $headers -ContentType 'application/json; charset=utf-8' -TimeoutSec 10
                if (-not $IsSilent) {
                    Write-Host '  [สำเร็จ] ส่งข้อมูลเข้าสู่ระบบ IT เรียบร้อยแล้ว (ผ่าน Fallback)!' -ForegroundColor Green
                }
                $transmitted = $true
                break
            } catch {}
        }
    }

    # Local Cache
    try {
        $localDir = "C:\ProgramData\THC-IT-Agent"
        if (-not (Test-Path $localDir)) {
            New-Item -ItemType Directory -Path $localDir -Force | Out-Null
        }
        $jsonBody | Out-File -FilePath "$localDir\last_audit.json" -Encoding UTF8 -Force
    } catch {}

    return $transmitted
}

# ------------------------------------------------------------------------------
# Function: Check Pending Scan Command from Server
# ------------------------------------------------------------------------------
function Check-ServerCommand {
    $ident = Get-MachineIdentifiers
    $hwId = [System.Uri]::EscapeDataString($ident.HardwareId)
    $hostName = [System.Uri]::EscapeDataString($ident.ComputerName)
    $mac = [System.Uri]::EscapeDataString($ident.MacAddress)

    foreach ($submitUrl in $candidateUrls) {
        $cmdUrl = $submitUrl -replace '/(api/)?hardware-audit/submit', '/api/hardware-audit/agent-command'
        $pollUrl = "$cmdUrl`?hardware_id=$hwId&hostname=$hostName&mac_address=$mac&client_version=2.2.0"
        
        try {
            $resp = Invoke-RestMethod -Uri $pollUrl -Method Get -TimeoutSec 6 -Headers @{ "Accept" = "application/json" }
            if ($resp -and $resp.has_command -eq $true) {
                return $resp
            }
            # If server responded with valid JSON saying no command, stop trying fallbacks
            if ($resp -and $resp.has_command -eq $false) {
                return $null
            }
        } catch {}
    }
    return $null
}

# ==============================================================================
# MAIN EXECUTION ROUTER
# ==============================================================================

if ($Background) {
    # --------------------------------------------------------------------------
    # MODE 1: Background Polling Daemon (Runs continuously, sleeps 30 seconds)
    # --------------------------------------------------------------------------
    # Initial scan if no recent audit in the last 6 hours
    $lastAuditFile = "C:\ProgramData\THC-IT-Agent\last_audit.json"
    $shouldInitialScan = $true
    if (Test-Path $lastAuditFile) {
        $lastWrite = (Get-Item $lastAuditFile).LastWriteTime
        if ((Get-Date) - $lastWrite -lt [TimeSpan]::FromHours(6)) {
            $shouldInitialScan = $false
        }
    }
    if ($shouldInitialScan) {
        Invoke-HardwareAudit -IsSilent $true | Out-Null
    }

    # Polling Loop
    while ($true) {
        try {
            $cmd = Check-ServerCommand
            if ($cmd -and $cmd.has_command -and $cmd.command -eq 'scan') {
                $cmdId = if ($cmd.command_id) { [int]$cmd.command_id } else { 0 }
                Invoke-HardwareAudit -CommandId $cmdId -IsSilent $true | Out-Null
            }
        } catch {}
        Start-Sleep -Seconds 30
    }

} elseif ($PollOnce) {
    # --------------------------------------------------------------------------
    # MODE 2: Single Check & Execute (Invoked every 5 mins by Scheduled Task)
    # --------------------------------------------------------------------------
    try {
        $cmd = Check-ServerCommand
        if ($cmd -and $cmd.has_command -and $cmd.command -eq 'scan') {
            $cmdId = if ($cmd.command_id) { [int]$cmd.command_id } else { 0 }
            Invoke-HardwareAudit -CommandId $cmdId -IsSilent $true | Out-Null
        }
    } catch {}

} else {
    # --------------------------------------------------------------------------
    # MODE 3: Immediate Scan & Transmit (Default on manual click or Desktop shortcut)
    # --------------------------------------------------------------------------
    $ok = Invoke-HardwareAudit -CommandId $ActiveCommandId -IsSilent $Silent
    if (-not $Silent) {
        Write-Host ''
    }
}
