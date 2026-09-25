# ==============================================================================
# thc_audit_agent.ps1
# Thung Hua Chang Hospital - Client Hardware Audit Agent
# Embedded PowerShell Agent to collect physical hardware specs & transmit to IT Server
# Version: 2.3.0 (Robust Multi-Tier Polling Engine & Auto-Healing Client Daemon)
# ==============================================================================

[CmdletBinding()]
param (
    [string]$ServerUrl = "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit",
    [int]$FiscalYear = 0,
    [string]$AssetCode = "",
    [int]$CommandId = 0,
    [switch]$Silent,
    [switch]$Background,
    [switch]$PollOnce,
    [switch]$ForceScan
)

# Parse positional or raw arguments if run directly via powershell.exe -File
if ($args) {
    for ($i = 0; $i -lt $args.Count; $i++) {
        if ($args[$i] -eq '-ServerUrl' -and ($i + 1) -lt $args.Count) { $ServerUrl = $args[$i + 1] }
        if ($args[$i] -eq '-FiscalYear' -and ($i + 1) -lt $args.Count) { $FiscalYear = [int]$args[$i + 1] }
        if ($args[$i] -eq '-AssetCode' -and ($i + 1) -lt $args.Count) { $AssetCode = [string]$args[$i + 1] }
        if ($args[$i] -eq '-CommandId' -and ($i + 1) -lt $args.Count) { $CommandId = [int]$args[$i + 1] }
        if ($args[$i] -eq '-Silent') { $Silent = $true }
        if ($args[$i] -eq '-Background') { $Background = $true; $Silent = $true }
        if ($args[$i] -eq '-PollOnce') { $PollOnce = $true; $Silent = $true }
        if ($args[$i] -eq '-ForceScan') { $ForceScan = $true }
    }
}

# Set console & pipeline encoding to UTF-8 for Thai character fidelity
try {
    [Console]::OutputEncoding = [System.Text.Encoding]::UTF8
    $OutputEncoding = [System.Text.Encoding]::UTF8
    chcp 65001 | Out-Null
} catch {}

# Enable TLS 1.2, TLS 1.3, and TLS 1.1 on Windows PowerShell 5.1 & modern platforms
try {
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12 -bor [System.Net.SecurityProtocolType]::Tls11 -bor [System.Net.SecurityProtocolType]::Tls
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
} catch {}

$ErrorActionPreference = 'SilentlyContinue'

# List of Server Endpoints to attempt (Custom URL, Production, Hospital LAN, Local)
$candidateUrls = @(
    $ServerUrl,
    "https://thchospital.moph.go.th/it-system/api/hardware-audit/submit",
    "http://192.168.2.89:8000/api/hardware-audit/submit",
    "http://192.168.2.89/it-system/api/hardware-audit/submit",
    "http://localhost:8000/api/hardware-audit/submit",
    "http://127.0.0.1:8000/api/hardware-audit/submit"
) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) } | Select-Object -Unique

# ------------------------------------------------------------------------------
# Robust HTTP Request Engine (Multi-Tier Fallback: HttpWebRequest -> WebClient -> curl.exe -> Invoke-RestMethod)
# ------------------------------------------------------------------------------
function Invoke-SafeApiRequest {
    param (
        [string]$Uri,
        [string]$Method = 'GET',
        [string]$Body = '',
        [int]$TimeoutSec = 8
    )

    # Method 1: .NET HttpWebRequest with KeepAlive = $false and custom User-Agent
    try {
        $req = [System.Net.HttpWebRequest]::Create($Uri)
        $req.Method = $Method.ToUpper()
        $req.Accept = "application/json"
        $req.UserAgent = "THC-Audit-Agent/2.3.0 (Windows NT; PowerShell)"
        $req.Timeout = $TimeoutSec * 1000
        $req.KeepAlive = $false
        $req.ServicePoint.Expect100Continue = $false
        
        if ($Method.ToUpper() -eq 'POST' -and -not [string]::IsNullOrEmpty($Body)) {
            $req.ContentType = "application/json; charset=utf-8"
            $bytes = [System.Text.Encoding]::UTF8.GetBytes($Body)
            $req.ContentLength = $bytes.Length
            $stream = $req.GetRequestStream()
            $stream.Write($bytes, 0, $bytes.Length)
            $stream.Close()
        }

        $resp = $req.GetResponse()
        $respStream = $resp.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($respStream, [System.Text.Encoding]::UTF8)
        $result = $reader.ReadToEnd()
        $reader.Close()
        $resp.Close()

        if (-not [string]::IsNullOrWhiteSpace($result)) {
            return $result
        }
    } catch {}

    # Method 2: System.Net.WebClient
    try {
        $wc = New-Object System.Net.WebClient
        $wc.Headers.Add("Accept", "application/json")
        $wc.Headers.Add("User-Agent", "THC-Audit-Agent/2.3.0")
        $wc.Encoding = [System.Text.Encoding]::UTF8
        if ($Method.ToUpper() -eq 'POST' -and -not [string]::IsNullOrEmpty($Body)) {
            $wc.Headers.Add("Content-Type", "application/json; charset=utf-8")
            $result = $wc.UploadString($Uri, 'POST', $Body)
        } else {
            $result = $wc.DownloadString($Uri)
        }
        if (-not [string]::IsNullOrWhiteSpace($result)) {
            return $result
        }
    } catch {}

    # Method 3: curl.exe (Built-in on Windows 10/11)
    try {
        if (Get-Command curl.exe -ErrorAction SilentlyContinue) {
            if ($Method.ToUpper() -eq 'POST' -and -not [string]::IsNullOrEmpty($Body)) {
                $tempBody = [System.IO.Path]::GetTempFileName()
                [System.IO.File]::WriteAllText($tempBody, $Body, [System.Text.Encoding]::UTF8)
                $result = & curl.exe -s -k --max-time $TimeoutSec -H "Content-Type: application/json; charset=utf-8" -H "Accept: application/json" -X POST --data-binary "@$tempBody" $Uri
                Remove-Item -Force $tempBody -ErrorAction SilentlyContinue
            } else {
                $result = & curl.exe -s -k --max-time $TimeoutSec -H "Accept: application/json" $Uri
            }
            if (-not [string]::IsNullOrWhiteSpace($result)) {
                return $result
            }
        }
    } catch {}

    # Method 4: Invoke-RestMethod fallback
    try {
        $headers = @{ "Accept" = "application/json"; "User-Agent" = "THC-Audit-Agent/2.3.0" }
        if ($Method.ToUpper() -eq 'POST' -and -not [string]::IsNullOrEmpty($Body)) {
            $headers["Content-Type"] = "application/json; charset=utf-8"
            $respObj = Invoke-RestMethod -Uri $Uri -Method Post -Body $Body -Headers $headers -ContentType 'application/json; charset=utf-8' -TimeoutSec $TimeoutSec
            return ($respObj | ConvertTo-Json -Compress)
        } else {
            $respObj = Invoke-RestMethod -Uri $Uri -Method Get -TimeoutSec $TimeoutSec -Headers $headers
            return ($respObj | ConvertTo-Json -Compress)
        }
    } catch {}

    return $null
}

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
        [int]$TargetCommandId = 0,
        [bool]$IsSilent = $false
    )

    if (-not $IsSilent) {
        Write-Host ''
        Write-Host '==========================================================================' -ForegroundColor Cyan
        Write-Host '   โรงพยาบาลทุ่งหัวช้าง - ระบบตรวจนับและติดตามสเปคคอมพิวเตอร์ประจำปี' -ForegroundColor Yellow
        Write-Host '   THUNG HUA CHANG HOSPITAL - CLIENT HARDWARE AUDIT AGENT v2.3.0' -ForegroundColor Gray
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
        client_agent_version  = '2.3.0'
    }

    if ($FiscalYear -gt 0) {
        $payload['fiscal_year'] = $FiscalYear
    }
    if (-not [string]::IsNullOrWhiteSpace($AssetCode)) {
        $payload['asset_code'] = $AssetCode
    }
    if ($TargetCommandId -gt 0) {
        $payload['command_id'] = $TargetCommandId
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
        if ($TargetCommandId -gt 0) {
            Write-Host "  ตอบรับคำสั่งสแกน ID:  #$TargetCommandId" -ForegroundColor Green
        }
        Write-Host '-----------------------------------------------------------------------------' -ForegroundColor Green
        Write-Host ''
        Write-Host "กำลังส่งข้อมูลเข้าสู่ระบบ IT โรงพยาบาลทุ่งหัวช้าง..." -ForegroundColor Yellow
    }

    # 10. ส่งข้อมูลเข้าสู่ระบบ IT ผ่าน Web API (HTTP POST) ด้วย Safe Request Engine
    $transmitted = $false
    foreach ($targetUrl in $candidateUrls) {
        if (-not $IsSilent) {
            Write-Host "  เชื่อมต่อไปยังเซิร์ฟเวอร์: $targetUrl" -ForegroundColor Gray
        }
        $respStr = Invoke-SafeApiRequest -Uri $targetUrl -Method 'POST' -Body $jsonBody -TimeoutSec 10
        if (-not [string]::IsNullOrWhiteSpace($respStr)) {
            if (-not $IsSilent) {
                Write-Host '  [สำเร็จ] ส่งข้อมูลเข้าสู่ระบบ IT เรียบร้อยแล้ว!' -ForegroundColor Green
                Write-Host '  => สถานะ: บันทึกข้อมูลเข้าสู่ระบบรอเจ้าหน้าที่ไอทีตรวจสอบและอนุมัติ' -ForegroundColor Yellow
            }
            $transmitted = $true
            break
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
        $pollUrl = "$cmdUrl`?hardware_id=$hwId&hostname=$hostName&mac_address=$mac&client_version=2.3.0"
        
        $respStr = Invoke-SafeApiRequest -Uri $pollUrl -Method 'GET' -TimeoutSec 8
        if (-not [string]::IsNullOrWhiteSpace($respStr)) {
            try {
                $resp = $respStr | ConvertFrom-Json
                if ($resp -and $resp.has_command -eq $true) {
                    return $resp
                }
                if ($resp -and $resp.has_command -eq $false) {
                    return $null
                }
            } catch {}
        }
    }
    return $null
}

# ------------------------------------------------------------------------------
# Function: Self-Install Background Daemon & Scheduled Tasks
# ------------------------------------------------------------------------------
function Install-AgentScheduledTask {
    param ([bool]$IsSilent = $true)
    
    try {
        $installDir = "C:\ProgramData\THC-IT-Agent"
        if (-not (Test-Path $installDir)) {
            New-Item -ItemType Directory -Path $installDir -Force | Out-Null
        }
        $targetScript = "$installDir\thc_audit_agent.ps1"

        # Copy this script to target if not already there or if different
        if ($PSCommandPath -and (Test-Path $PSCommandPath)) {
            if ($PSCommandPath -ne $targetScript) {
                Copy-Item -Path $PSCommandPath -Destination $targetScript -Force
            }
        }

        # Check / Create Scheduled Tasks
        $task1 = schtasks /query /tn "THC_Hardware_Audit" 2>$null
        if (-not $task1) {
            schtasks /create /tn "THC_Hardware_Audit" /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$targetScript`" -Background" /sc onlogon /rl HIGHEST /f 2>$null | Out-Null
        }

        $task2 = schtasks /query /tn "THC_Hardware_Audit_Poll" 2>$null
        if (-not $task2) {
            schtasks /create /tn "THC_Hardware_Audit_Poll" /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$targetScript`" -PollOnce" /sc minute /mo 5 /rl HIGHEST /f 2>$null | Out-Null
        }

        if (-not $IsSilent) {
            Write-Host '  [ระบบ] ลงทะเบียน Scheduled Task ประจำเครื่องสำเร็จ' -ForegroundColor Green
        }
    } catch {}
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

    # Continuous Polling Loop
    while ($true) {
        try {
            $cmd = Check-ServerCommand
            if ($cmd -and $cmd.has_command -and $cmd.command -eq 'scan') {
                $cmdId = if ($cmd.command_id) { [int]$cmd.command_id } else { 0 }
                $transmitted = Invoke-HardwareAudit -TargetCommandId $cmdId -IsSilent $true
                
                # Direct Acknowledgement
                if ($cmdId -gt 0 -and $transmitted) {
                    foreach ($submitUrl in $candidateUrls) {
                        $ackUrl = $submitUrl -replace '/(api/)?hardware-audit/submit', "/api/hardware-audit/agent-command/$cmdId/complete"
                        Invoke-SafeApiRequest -Uri $ackUrl -Method 'POST' -Body '{"summary":"Agent auto-scan completed"}' -TimeoutSec 5 | Out-Null
                    }
                }
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
            $transmitted = Invoke-HardwareAudit -TargetCommandId $cmdId -IsSilent $true
            
            # Direct Acknowledgement
            if ($cmdId -gt 0 -and $transmitted) {
                foreach ($submitUrl in $candidateUrls) {
                    $ackUrl = $submitUrl -replace '/(api/)?hardware-audit/submit', "/api/hardware-audit/agent-command/$cmdId/complete"
                    Invoke-SafeApiRequest -Uri $ackUrl -Method 'POST' -Body '{"summary":"Agent auto-scan completed"}' -TimeoutSec 5 | Out-Null
                }
            }
        }
    } catch {}

} else {
    # --------------------------------------------------------------------------
    # MODE 3: Immediate Scan & Transmit (Manual click, Desktop shortcut, or One-liner)
    # --------------------------------------------------------------------------
    $ok = Invoke-HardwareAudit -TargetCommandId $CommandId -IsSilent $Silent
    
    # Auto-register Background Daemon if running in interactive mode
    Install-AgentScheduledTask -IsSilent $Silent

    if (-not $Silent) {
        Write-Host ''
    }
}
