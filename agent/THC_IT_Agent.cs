using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.IO;
using System.Management;
using System.Net;
using System.Net.Security;
using System.Reflection;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Threading;
using System.Web.Script.Serialization;
using System.Windows.Forms;
using Microsoft.Win32;

namespace ThcItAgent
{
    static class Program
    {
        private const string AppGuid = "THC-IT-Agent-f91aa586-7788-9900-thung-hua-chang";
        private static Mutex _mutex;

        [STAThread]
        static void Main(string[] args)
        {
            // Allow only one instance per user session
            bool createdNew;
            _mutex = new Mutex(true, AppGuid, out createdNew);
            if (!createdNew)
            {
                MessageBox.Show(
                    "โปรแกรม THC IT Agent กำลังทำงานอยู่ใน System Tray (มุมขวาล่างข้างนาฬิกา) แล้ว",
                    "THC IT Agent",
                    MessageBoxButtons.OK,
                    MessageBoxIcon.Information);
                return;
            }

            // Enable TLS 1.2, TLS 1.1, TLS 1.0 and ignore self-signed certificates in hospital LAN
            try
            {
                ServicePointManager.SecurityProtocol = (SecurityProtocolType)3072 | (SecurityProtocolType)768 | SecurityProtocolType.Tls;
                ServicePointManager.ServerCertificateValidationCallback =
                    delegate(object s, X509Certificate cert, X509Chain chain, SslPolicyErrors sslErr) { return true; };
            }
            catch { }

            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            Application.Run(new TrayContext());

            // Release mutex on normal exit
            try { _mutex.ReleaseMutex(); } catch { }
        }
    }

    public class AppConfig
    {
        public string ServerUrl { get; set; }
        public int HeartbeatIntervalSec { get; set; }
        public bool AutoStart { get; set; }

        public AppConfig()
        {
            ServerUrl = "https://thchospital.moph.go.th/it-system";
            HeartbeatIntervalSec = 4;
            AutoStart = true;
        }

        public static string[] CandidateUrls = new string[]
        {
            "https://thchospital.moph.go.th/it-system",
            "http://thchospital.moph.go.th/it-system",
            "http://192.168.2.89/it-system",
            "http://192.168.2.89:8000",
            "http://192.168.2.10/it-system",
            "http://192.168.2.10:8000",
            "http://localhost:8000",
            "http://127.0.0.1:8000"
        };

        private static string GetConfigPath()
        {
            // 1. Check in ProgramData (standard installation directory)
            string progDataDir = @"C:\ProgramData\THC-IT-Agent";
            if (Directory.Exists(progDataDir))
            {
                string pdConfig = Path.Combine(progDataDir, "config.json");
                if (File.Exists(pdConfig)) return pdConfig;
            }

            // 2. Check next to executable
            string appDir = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location);
            string localConfig = Path.Combine(appDir, "config.json");
            if (File.Exists(localConfig)) return localConfig;

            // 3. Fallback to LocalAppData
            string appData = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "THC-IT-Agent");
            if (!Directory.Exists(appData)) Directory.CreateDirectory(appData);
            return Path.Combine(appData, "config.json");
        }

        public static AppConfig Load()
        {
            try
            {
                string path = GetConfigPath();
                if (File.Exists(path))
                {
                    string json = File.ReadAllText(path, Encoding.UTF8);
                    var js = new JavaScriptSerializer();
                    var cfg = js.Deserialize<AppConfig>(json);
                    if (cfg != null && !string.IsNullOrEmpty(cfg.ServerUrl))
                    {
                        cfg.ServerUrl = cfg.ServerUrl.TrimEnd('/');
                        return cfg;
                    }
                }
            }
            catch { }
            return new AppConfig();
        }

        public void Save()
        {
            try
            {
                string path = GetConfigPath();
                var js = new JavaScriptSerializer();
                string json = js.Serialize(this);
                File.WriteAllText(path, json, Encoding.UTF8);

                // Also mirror to ProgramData if directory exists
                string pdDir = @"C:\ProgramData\THC-IT-Agent";
                if (Directory.Exists(pdDir))
                {
                    try { File.WriteAllText(Path.Combine(pdDir, "config.json"), json, Encoding.UTF8); } catch { }
                }
            }
            catch { }
        }

        public static bool IsAutoStartEnabled()
        {
            try
            {
                using (RegistryKey key = Registry.CurrentUser.OpenSubKey(@"Software\Microsoft\Windows\CurrentVersion\Run", false))
                {
                    if (key != null)
                    {
                        object val = key.GetValue("THC_IT_Agent");
                        return val != null;
                    }
                }
            }
            catch { }
            return false;
        }

        public static void SetAutoStart(bool enable)
        {
            try
            {
                using (RegistryKey key = Registry.CurrentUser.OpenSubKey(@"Software\Microsoft\Windows\CurrentVersion\Run", true))
                {
                    if (key != null)
                    {
                        if (enable)
                        {
                            string exePath = Assembly.GetExecutingAssembly().Location;
                            key.SetValue("THC_IT_Agent", "\"" + exePath + "\"");
                        }
                        else
                        {
                            key.DeleteValue("THC_IT_Agent", false);
                        }
                    }
                }
            }
            catch { }
        }
    }

    public static class IconHelper
    {
        public static Icon CreateCircleIcon(Color color, string text = "")
        {
            using (Bitmap bmp = new Bitmap(32, 32))
            using (Graphics g = Graphics.FromImage(bmp))
            {
                g.SmoothingMode = SmoothingMode.AntiAlias;
                g.Clear(Color.Transparent);

                // Draw outer shadow
                using (SolidBrush shadow = new SolidBrush(Color.FromArgb(50, 0, 0, 0)))
                {
                    g.FillEllipse(shadow, 2, 2, 28, 28);
                }

                // Draw main colored circle
                using (SolidBrush brush = new SolidBrush(color))
                {
                    g.FillEllipse(brush, 1, 1, 28, 28);
                }

                // Draw inner ring border
                using (Pen pen = new Pen(Color.FromArgb(200, 255, 255, 255), 2))
                {
                    g.DrawEllipse(pen, 2, 2, 26, 26);
                }

                // Draw small symbol or text
                if (!string.IsNullOrEmpty(text))
                {
                    using (Font font = new Font("Arial", 12, FontStyle.Bold, GraphicsUnit.Pixel))
                    using (SolidBrush textBrush = new SolidBrush(Color.White))
                    {
                        StringFormat sf = new StringFormat
                        {
                            Alignment = StringAlignment.Center,
                            LineAlignment = StringAlignment.Center
                        };
                        g.DrawString(text, font, textBrush, new RectangleF(0, 0, 30, 30), sf);
                    }
                }

                IntPtr hIcon = bmp.GetHicon();
                return Icon.FromHandle(hIcon);
            }
        }
    }

    public class HardwareSpecs
    {
        public string Hostname { get; set; }
        public string HardwareId { get; set; }
        public string SerialNumber { get; set; }
        public string Brand { get; set; }
        public string Model { get; set; }
        public string DeviceTypeCode { get; set; }
        public string CpuModel { get; set; }
        public string CpuSpeed { get; set; }
        public int CpuCores { get; set; }
        public int CpuThreads { get; set; }
        public int RamCapacity { get; set; }
        public string RamType { get; set; }
        public string RamBus { get; set; }
        public string RamSlots { get; set; }
        public string StorageType { get; set; }
        public string StorageCapacity { get; set; }
        public string StorageSecond { get; set; }
        public string OsName { get; set; }
        public string OsLicense { get; set; }
        public string GpuModel { get; set; }
        public string IpAddress { get; set; }
        public string MacAddress { get; set; }
        public string MonitorSize { get; set; }
        public DateTime CollectedAt { get; set; }

        public static HardwareSpecs Collect()
        {
            var specs = new HardwareSpecs
            {
                Hostname = Environment.MachineName,
                CollectedAt = DateTime.Now,
                HardwareId = "",
                Brand = "",
                Model = "",
                DeviceTypeCode = "PC",
                CpuModel = "",
                CpuSpeed = "",
                CpuCores = 4,
                CpuThreads = 4,
                RamCapacity = 8,
                RamType = "DDR4",
                RamBus = "",
                RamSlots = "1 Slot(s)",
                StorageType = "SSD",
                StorageCapacity = "256 GB",
                StorageSecond = "",
                OsName = "Windows 10 Pro",
                OsLicense = "Digital License / OEM",
                GpuModel = "Integrated Graphics",
                IpAddress = "",
                MacAddress = "",
                MonitorSize = "Display"
            };

            // 1. Motherboard, UUID & Chassis
            string bbSerial = "";
            string bbMaker = "";
            string bbModel = "";
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT SerialNumber, Manufacturer, Product FROM Win32_BaseBoard"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["SerialNumber"] != null) bbSerial = mo["SerialNumber"].ToString().Trim();
                        if (mo["Manufacturer"] != null) bbMaker = mo["Manufacturer"].ToString().Trim();
                        if (mo["Product"] != null) bbModel = mo["Product"].ToString().Trim();
                        break;
                    }
                }
            }
            catch { }

            try
            {
                using (var s = new ManagementObjectSearcher("SELECT UUID, Vendor, Name, IdentifyingNumber FROM Win32_ComputerSystemProduct"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["UUID"] != null) specs.HardwareId = mo["UUID"].ToString().Trim();
                        if (mo["Vendor"] != null) specs.Brand = mo["Vendor"].ToString().Trim();
                        if (mo["Name"] != null) specs.Model = mo["Name"].ToString().Trim();
                        if (mo["IdentifyingNumber"] != null) specs.SerialNumber = mo["IdentifyingNumber"].ToString().Trim();
                        break;
                    }
                }
            }
            catch { }

            // Clean HardwareId / UUID fallback if invalid or OEM default
            if (string.IsNullOrEmpty(specs.HardwareId) ||
                specs.HardwareId.IndexOf("Default", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.HardwareId.IndexOf("None", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.HardwareId.StartsWith("00000000-0000", StringComparison.OrdinalIgnoreCase) ||
                specs.HardwareId.StartsWith("FFFFFFFF-FFFF", StringComparison.OrdinalIgnoreCase))
            {
                string basePart = !string.IsNullOrEmpty(bbSerial) && bbSerial.IndexOf("Default", StringComparison.OrdinalIgnoreCase) < 0 ? bbSerial : specs.Hostname;
                specs.HardwareId = "THC-" + basePart;
            }

            // Fallback for Serial Number via Win32_BIOS
            if (string.IsNullOrEmpty(specs.SerialNumber) || specs.SerialNumber.IndexOf("Default", StringComparison.OrdinalIgnoreCase) >= 0 || specs.SerialNumber.IndexOf("To be filled", StringComparison.OrdinalIgnoreCase) >= 0)
            {
                try
                {
                    using (var s = new ManagementObjectSearcher("SELECT SerialNumber, Manufacturer FROM Win32_BIOS"))
                    {
                        foreach (ManagementObject mo in s.Get())
                        {
                            if (mo["SerialNumber"] != null) specs.SerialNumber = mo["SerialNumber"].ToString().Trim();
                            if (string.IsNullOrEmpty(specs.Brand) && mo["Manufacturer"] != null)
                                specs.Brand = mo["Manufacturer"].ToString().Trim();
                            break;
                        }
                    }
                }
                catch { }

                if (string.IsNullOrEmpty(specs.SerialNumber) || specs.SerialNumber.IndexOf("Default", StringComparison.OrdinalIgnoreCase) >= 0)
                {
                    specs.SerialNumber = bbSerial;
                }
            }

            // Fallback for Brand / Model via Win32_ComputerSystem
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Manufacturer, Model FROM Win32_ComputerSystem"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (string.IsNullOrEmpty(specs.Brand) && mo["Manufacturer"] != null)
                            specs.Brand = mo["Manufacturer"].ToString().Trim();
                        if (string.IsNullOrEmpty(specs.Model) && mo["Model"] != null)
                            specs.Model = mo["Model"].ToString().Trim();
                        break;
                    }
                }
            }
            catch { }

            // Normalize Brand and Model for Custom / DIY PCs
            if (string.IsNullOrEmpty(specs.Brand) ||
                specs.Brand.IndexOf("To be filled", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.Brand.IndexOf("System manufacturer", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.Brand.IndexOf("Default string", StringComparison.OrdinalIgnoreCase) >= 0)
            {
                specs.Brand = !string.IsNullOrEmpty(bbMaker) && bbMaker.IndexOf("To be filled", StringComparison.OrdinalIgnoreCase) < 0 ? "เครื่องประกอบ (" + bbMaker + ")" : "เครื่องประกอบ (Custom PC)";
            }
            if (string.IsNullOrEmpty(specs.Model) ||
                specs.Model.IndexOf("To be filled", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.Model.IndexOf("System Product", StringComparison.OrdinalIgnoreCase) >= 0 ||
                specs.Model.IndexOf("Default string", StringComparison.OrdinalIgnoreCase) >= 0)
            {
                specs.Model = !string.IsNullOrEmpty(bbModel) ? bbModel : "Custom Assembled PC";
            }

            // Detect Device Type Code (PC, NB, AIO, SERVER)
            bool isLaptop = false;
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT BatteryStatus FROM Win32_Battery"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        isLaptop = true;
                        break;
                    }
                }
            }
            catch { }

            bool isAIO = specs.Model.IndexOf("All-in-One", StringComparison.OrdinalIgnoreCase) >= 0 ||
                         specs.Model.IndexOf("AIO", StringComparison.OrdinalIgnoreCase) >= 0 ||
                         specs.Model.IndexOf("ProOne", StringComparison.OrdinalIgnoreCase) >= 0;

            if (isLaptop) specs.DeviceTypeCode = "NB";
            else if (isAIO) specs.DeviceTypeCode = "AIO";
            else specs.DeviceTypeCode = "PC";

            // 2. CPU
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Name, NumberOfCores, NumberOfLogicalProcessors, MaxClockSpeed, ProcessorId FROM Win32_Processor"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["Name"] != null)
                        {
                            string cName = mo["Name"].ToString().Trim();
                            // Clean CPU name
                            cName = System.Text.RegularExpressions.Regex.Replace(cName, @"\(R\)|\(TM\)|\(tm\)|CPU\s*@.*|@\s*[\d\.]+\s*GHz", "");
                            specs.CpuModel = System.Text.RegularExpressions.Regex.Replace(cName, @"\s+", " ").Trim();
                        }
                        if (mo["NumberOfCores"] != null)
                        {
                            int cores;
                            if (int.TryParse(mo["NumberOfCores"].ToString(), out cores)) specs.CpuCores = cores;
                        }
                        if (mo["NumberOfLogicalProcessors"] != null)
                        {
                            int threads;
                            if (int.TryParse(mo["NumberOfLogicalProcessors"].ToString(), out threads)) specs.CpuThreads = threads;
                        }
                        if (mo["MaxClockSpeed"] != null)
                        {
                            int speed;
                            if (int.TryParse(mo["MaxClockSpeed"].ToString(), out speed))
                            {
                                double ghz = Math.Round((double)speed / 1000.0, 2);
                                specs.CpuSpeed = ghz + " GHz (" + specs.CpuCores + " Cores / " + specs.CpuThreads + " Threads)";
                            }
                        }
                        break;
                    }
                }
            }
            catch { }

            // 3. RAM
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Capacity, Speed, MemoryType, SMBIOSMemoryType FROM Win32_PhysicalMemory"))
                {
                    long totalBytes = 0;
                    int speed = 0;
                    int smbios = 0;
                    int moduleCount = 0;
                    foreach (ManagementObject mo in s.Get())
                    {
                        moduleCount++;
                        if (mo["Capacity"] != null) totalBytes += Convert.ToInt64(mo["Capacity"]);
                        if (mo["Speed"] != null && speed == 0) int.TryParse(mo["Speed"].ToString(), out speed);
                        if (mo["SMBIOSMemoryType"] != null && smbios == 0) int.TryParse(mo["SMBIOSMemoryType"].ToString(), out smbios);
                    }

                    int ramGb = (int)Math.Round((double)totalBytes / (1024 * 1024 * 1024));
                    if (ramGb <= 0) ramGb = 8;
                    specs.RamCapacity = ramGb;

                    if (smbios == 26) specs.RamType = "DDR4";
                    else if (smbios == 34) specs.RamType = "DDR5";
                    else if (smbios == 24) specs.RamType = "DDR3";
                    else if (smbios == 21) specs.RamType = "DDR2";
                    else if (speed > 4000) specs.RamType = "DDR5";
                    else if (speed > 2000) specs.RamType = "DDR4";
                    else if (speed > 1000) specs.RamType = "DDR3";

                    if (speed > 0) specs.RamBus = speed + " MHz";
                    specs.RamSlots = moduleCount + " Slot(s)";
                }
            }
            catch { }

            // 4. Storage & Disks (Detect OS disk C:)
            try
            {
                string sysDiskModel = "";
                long sysDiskSize = 0;

                try
                {
                    string qPart = "ASSOCIATORS OF {Win32_LogicalDisk.DeviceID='C:'} WHERE AssocClass = Win32_LogicalDiskToPartition";
                    using (var sPart = new ManagementObjectSearcher(qPart))
                    {
                        foreach (ManagementObject part in sPart.Get())
                        {
                            string partId = part["DeviceID"].ToString();
                            string qDrive = "ASSOCIATORS OF {Win32_DiskPartition.DeviceID='" + partId + "'} WHERE AssocClass = Win32_DiskDriveToDiskPartition";
                            using (var sDrive = new ManagementObjectSearcher(qDrive))
                            {
                                foreach (ManagementObject disk in sDrive.Get())
                                {
                                    if (disk["Model"] != null) sysDiskModel = disk["Model"].ToString().Trim();
                                    if (disk["Size"] != null) sysDiskSize = Convert.ToInt64(disk["Size"]);
                                    break;
                                }
                            }
                            if (!string.IsNullOrEmpty(sysDiskModel)) break;
                        }
                    }
                }
                catch { }

                var secList = new List<string>();
                using (var s = new ManagementObjectSearcher("SELECT Model, Size, MediaType, InterfaceType FROM Win32_DiskDrive"))
                {
                    int index = 0;
                    foreach (ManagementObject mo in s.Get())
                    {
                        string dModel = mo["Model"] != null ? mo["Model"].ToString().Trim() : "";
                        long dSize = mo["Size"] != null ? Convert.ToInt64(mo["Size"]) : 0;
                        if (dSize < 5L * 1024 * 1024 * 1024) continue; // Skip USB thumb drives < 5GB

                        double dGb = Math.Round((double)dSize / (1024 * 1024 * 1024), 0);
                        string capStr = dGb >= 900 ? (Math.Round(dGb / 1000.0, 1) + " TB") : (dGb + " GB");
                        string iface = mo["InterfaceType"] != null ? mo["InterfaceType"].ToString() : "";
                        string mType = mo["MediaType"] != null ? mo["MediaType"].ToString() : "";

                        string dType = "SSD";
                        if (dModel.IndexOf("NVMe", StringComparison.OrdinalIgnoreCase) >= 0 || iface.IndexOf("SCSI", StringComparison.OrdinalIgnoreCase) >= 0)
                            dType = "NVMe SSD";
                        else if (mType.IndexOf("Fixed", StringComparison.OrdinalIgnoreCase) >= 0 && dModel.IndexOf("SSD", StringComparison.OrdinalIgnoreCase) < 0)
                            dType = "HDD";

                        if (!string.IsNullOrEmpty(sysDiskModel) && dModel.Equals(sysDiskModel, StringComparison.OrdinalIgnoreCase))
                        {
                            specs.StorageCapacity = capStr;
                            specs.StorageType = dType;
                        }
                        else if (string.IsNullOrEmpty(sysDiskModel) && index == 0)
                        {
                            specs.StorageCapacity = capStr;
                            specs.StorageType = dType;
                        }
                        else
                        {
                            secList.Add(dType + " " + capStr + " (" + dModel + ")");
                        }
                        index++;
                    }
                }
                specs.StorageSecond = string.Join(", ", secList.ToArray());
            }
            catch { }

            // 5. Operating System
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Caption, OSArchitecture FROM Win32_OperatingSystem"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["Caption"] != null)
                        {
                            string cap = mo["Caption"].ToString().Replace("Microsoft", "").Trim();
                            specs.OsName = cap;
                        }
                        if (mo["OSArchitecture"] != null && !specs.OsName.Contains("bit"))
                        {
                            specs.OsName += " (" + mo["OSArchitecture"].ToString().Trim() + ")";
                        }
                        break;
                    }
                }
            }
            catch { }

            // 6. GPU
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Name FROM Win32_VideoController"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["Name"] != null)
                        {
                            string g = mo["Name"].ToString().Trim();
                            if (g.IndexOf("Virtual", StringComparison.OrdinalIgnoreCase) >= 0 || g.IndexOf("Remote", StringComparison.OrdinalIgnoreCase) >= 0) continue;
                            if (string.IsNullOrEmpty(specs.GpuModel) || specs.GpuModel == "Integrated Graphics") specs.GpuModel = g;
                            else if (!specs.GpuModel.Contains(g)) specs.GpuModel += ", " + g;
                        }
                    }
                }
            }
            catch { }

            // 7. Network (IP & MAC)
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT IPAddress, MACAddress, DefaultIPGateway FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = True"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["IPAddress"] != null)
                        {
                            string[] ips = (string[])mo["IPAddress"];
                            if (ips.Length > 0 && string.IsNullOrEmpty(specs.IpAddress)) specs.IpAddress = ips[0];
                        }
                        if (mo["MACAddress"] != null && string.IsNullOrEmpty(specs.MacAddress))
                        {
                            specs.MacAddress = mo["MACAddress"].ToString().Trim().ToUpper();
                        }
                        if (mo["DefaultIPGateway"] != null) break;
                    }
                }
            }
            catch { }

            // 8. Screen / Monitor
            try
            {
                var screens = Screen.AllScreens;
                if (screens.Length > 0)
                {
                    var p = Screen.PrimaryScreen.Bounds;
                    specs.MonitorSize = screens.Length > 1 ? (screens.Length + " จอ (" + p.Width + "x" + p.Height + ")") : (p.Width + "x" + p.Height);
                }
            }
            catch { }

            return specs;
        }

        public Dictionary<string, object> ToPayload(int? commandId = null)
        {
            var p = new Dictionary<string, object>
            {
                { "hostname", Hostname },
                { "hardware_id", HardwareId },
                { "serial_number", SerialNumber },
                { "brand", Brand },
                { "model", Model },
                { "device_type_code", DeviceTypeCode },
                { "cpu_model", CpuModel },
                { "cpu_speed", CpuSpeed },
                { "ram_capacity", RamCapacity }, // int
                { "ram_type", RamType },
                { "ram_bus", RamBus },
                { "ram_slots", RamSlots },
                { "storage_type", StorageType },
                { "storage_capacity", StorageCapacity }, // string e.g. "256 GB"
                { "storage_second", StorageSecond },
                { "os_name", OsName },
                { "os_license", OsLicense },
                { "gpu_model", GpuModel },
                { "ip_address", IpAddress },
                { "mac_address", MacAddress },
                { "monitor_size", MonitorSize },
                { "monitor_info", MonitorSize },
                { "client_agent_version", "2.8.0-TrayExe" },
                { "agent_version", "2.8.0-TrayExe" }
            };

            if (commandId.HasValue && commandId.Value > 0)
            {
                p.Add("command_id", commandId.Value);
            }

            return p;
        }
    }

    public class TrayContext : ApplicationContext
    {
        private readonly NotifyIcon _notifyIcon;
        private readonly ContextMenuStrip _contextMenu;
        private readonly ToolStripMenuItem _statusMenuItem;
        private readonly ToolStripMenuItem _autoStartMenuItem;
        private readonly AppConfig _config;

        private Icon _iconOnline;
        private Icon _iconBusy;
        private Icon _iconOffline;

        private Thread _workerThread;
        private bool _isRunning = true;
        private bool _isOnline = false;
        private bool _isBusy = false;

        private HardwareSpecs _cachedSpecs;
        private int _lastExecutedCommandId = 0;
        private DateTime _lastExecutedTime = DateTime.MinValue;

        public TrayContext()
        {
            _config = AppConfig.Load();

            // Create tray icons
            _iconOnline = IconHelper.CreateCircleIcon(Color.FromArgb(46, 204, 113), "✔");
            _iconBusy = IconHelper.CreateCircleIcon(Color.FromArgb(241, 196, 15), "⏳");
            _iconOffline = IconHelper.CreateCircleIcon(Color.FromArgb(231, 76, 60), "✖");

            // Setup Context Menu
            _contextMenu = new ContextMenuStrip();
            _contextMenu.Font = new Font("Segoe UI", 9F);

            // Title Item
            var titleItem = new ToolStripMenuItem("🖥️ THC IT Agent v2.8.0 (รพ.ทุ่งหัวช้าง)")
            {
                Enabled = false,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            _contextMenu.Items.Add(titleItem);

            // Status Item
            _statusMenuItem = new ToolStripMenuItem("⏳ กำลังเริ่มระบบและตรวจสอบการเชื่อมต่อ...")
            {
                Enabled = false
            };
            _contextMenu.Items.Add(_statusMenuItem);

            _contextMenu.Items.Add(new ToolStripSeparator());

            // Scan & Upload Now
            var scanItem = new ToolStripMenuItem("📥 ส่งสเปกเครื่องเดี๋ยวนี้ (Scan & Upload)", null, (s, e) =>
            {
                TriggerManualScan();
            });
            scanItem.Font = new Font("Segoe UI", 9F, FontStyle.Bold);
            _contextMenu.Items.Add(scanItem);

            // View Specs
            var specsItem = new ToolStripMenuItem("💻 ดูสเปกเครื่องนี้ (Device Specs)", null, (s, e) =>
            {
                ShowDeviceSpecs();
            });
            _contextMenu.Items.Add(specsItem);

            // Server Settings
            var configItem = new ToolStripMenuItem("⚙️ ตั้งค่าเซิร์ฟเวอร์ (Server URL)", null, (s, e) =>
            {
                ShowServerSettings();
            });
            _contextMenu.Items.Add(configItem);

            // Auto-start with Windows
            _autoStartMenuItem = new ToolStripMenuItem("🔄 เริ่มทำงานพร้อม Windows (Auto-start)", null, (s, e) =>
            {
                bool newState = !_autoStartMenuItem.Checked;
                AppConfig.SetAutoStart(newState);
                _autoStartMenuItem.Checked = newState;
                _config.AutoStart = newState;
                _config.Save();
            });
            _autoStartMenuItem.Checked = AppConfig.IsAutoStartEnabled();
            _contextMenu.Items.Add(_autoStartMenuItem);

            _contextMenu.Items.Add(new ToolStripSeparator());

            // Check & Update Version
            var updateItem = new ToolStripMenuItem("🔄 ตรวจสอบและอัปเดต Agent ล่าสุด (Update Agent)", null, (s, e) =>
            {
                ExecuteSelfUpdate(false);
            });
            _contextMenu.Items.Add(updateItem);

            // Uninstall Agent
            var uninstallItem = new ToolStripMenuItem("🗑️ ถอนการติดตั้ง Agent ออกจากเครื่อง (Uninstall)", null, (s, e) =>
            {
                ExecuteSelfUninstall(false);
            });
            _contextMenu.Items.Add(uninstallItem);

            _contextMenu.Items.Add(new ToolStripSeparator());

            // Exit
            var exitItem = new ToolStripMenuItem("❌ ออกจากโปรแกรม (Exit)", null, (s, e) =>
            {
                ExitApp();
            });
            _contextMenu.Items.Add(exitItem);

            // Initialize NotifyIcon
            _notifyIcon = new NotifyIcon
            {
                Icon = _iconOffline,
                ContextMenuStrip = _contextMenu,
                Text = "THC IT Agent - กำลังเริ่มระบบ...",
                Visible = true
            };

            _notifyIcon.DoubleClick += (s, e) => ShowDeviceSpecs();

            // Auto-enable autostart on first run if enabled in config
            if (_config.AutoStart && !AppConfig.IsAutoStartEnabled())
            {
                AppConfig.SetAutoStart(true);
                _autoStartMenuItem.Checked = true;
            }

            // Immediately collect machine identifiers & full specs on startup
            try
            {
                _cachedSpecs = HardwareSpecs.Collect();
            }
            catch { }

            // Start background worker thread
            _workerThread = new Thread(WorkerLoop) { IsBackground = true };
            _workerThread.Start();

            // Run initial hardware scan & report in background on startup
            new Thread(() =>
            {
                Thread.Sleep(2500); // Give worker loop a moment to verify server connection
                try
                {
                    ExecuteScanAndSubmit(0, isInitial: true);
                }
                catch { }
            }) { IsBackground = true }.Start();
        }

        private void SetStatus(bool online, bool busy, string message)
        {
            _isOnline = online;
            _isBusy = busy;

            if (_notifyIcon == null) return;

            Icon targetIcon = _iconOffline;
            string iconState = "ออฟไลน์";

            if (busy)
            {
                targetIcon = _iconBusy;
                iconState = "กำลังดึง/ส่งข้อมูล";
            }
            else if (online)
            {
                targetIcon = _iconOnline;
                iconState = "ออนไลน์ (พร้อมรับคำสั่ง)";
            }

            try
            {
                _notifyIcon.Icon = targetIcon;
                string notifyText = "THC IT Agent: " + iconState;
                _notifyIcon.Text = notifyText.Length > 63 ? notifyText.Substring(0, 63) : notifyText;
                _statusMenuItem.Text = (online ? (busy ? "🟡 " : "🟢 ") : "🔴 ") + "สถานะ: " + iconState;
            }
            catch { }
        }

        private void WorkerLoop()
        {
            Thread.Sleep(1000);
            var js = new JavaScriptSerializer();

            while (_isRunning)
            {
                try
                {
                    // Ensure cached specs exist
                    if (_cachedSpecs == null)
                    {
                        _cachedSpecs = HardwareSpecs.Collect();
                    }

                    // Heartbeat Payload with full hardware identifiers
                    var pingData = new Dictionary<string, object>
                    {
                        { "hostname", _cachedSpecs.Hostname },
                        { "hardware_id", _cachedSpecs.HardwareId },
                        { "mac_address", _cachedSpecs.MacAddress },
                        { "ip_address", _cachedSpecs.IpAddress },
                        { "agent_version", "2.8.0-TrayExe" },
                        { "client_version", "2.8.0-TrayExe" },
                        { "mode", "tray_agent" }
                    };

                    string jsonReq = js.Serialize(pingData);
                    string respJson = HttpPostWithFallback("/api/hardware-audit/agent-command", jsonReq, 6000);

                    if (!string.IsNullOrEmpty(respJson))
                    {
                        var resp = js.Deserialize<Dictionary<string, object>>(respJson);

                        bool hasCommand = resp != null && resp.ContainsKey("has_command") && Convert.ToBoolean(resp["has_command"]);
                        string command = resp != null && resp.ContainsKey("command") ? resp["command"].ToString() : "";
                        int commandId = 0;
                        if (resp != null && resp.ContainsKey("command_id") && resp["command_id"] != null)
                        {
                            int.TryParse(resp["command_id"].ToString(), out commandId);
                        }

                        if (hasCommand && command.Equals("scan", StringComparison.OrdinalIgnoreCase))
                        {
                            // Check if this command was already executed recently (prevent infinite loops)
                            if (commandId > 0 && commandId == _lastExecutedCommandId && (DateTime.Now - _lastExecutedTime).TotalSeconds < 40)
                            {
                                // Already executed, send direct ACK without re-running full scan
                                SetStatus(true, false, "ออนไลน์ (ส่งยืนยันผลคำสั่งแล้ว)");
                                AcknowledgeCommandComplete(commandId, "คำสั่งเสร็จสมบูรณ์แล้ว (Cached Ack)");
                            }
                            else
                            {
                                // On-Demand Pull Triggered by Admin!
                                _lastExecutedCommandId = commandId;
                                _lastExecutedTime = DateTime.Now;

                                SetStatus(true, true, "กำลังดึงและส่งสเปกเครื่องตามคำสั่งไอที...");
                                _notifyIcon.ShowBalloonTip(3000, "ฝ่ายไอทีกำลังดึงข้อมูลสเปก", "ระบบส่วนกลางสั่งดึงสเปกเครื่อง (คำสั่ง #" + commandId + ")", ToolTipIcon.Info);

                                ExecuteScanAndSubmit(commandId);
                            }
                        }
                        else if (hasCommand && command.Equals("update", StringComparison.OrdinalIgnoreCase))
                        {
                            if (commandId > 0 && commandId == _lastExecutedCommandId && (DateTime.Now - _lastExecutedTime).TotalSeconds < 40)
                            {
                                SetStatus(true, false, "ออนไลน์ (ส่งยืนยันผลคำสั่งแล้ว)");
                                AcknowledgeCommandComplete(commandId, "คำสั่งอัปเดตดำเนินการแล้ว (Cached Ack)");
                            }
                            else
                            {
                                _lastExecutedCommandId = commandId;
                                _lastExecutedTime = DateTime.Now;

                                SetStatus(true, true, "ได้รับคำสั่งอัปเดตเวอร์ชัน Agent ล่าสุด...");
                                AcknowledgeCommandComplete(commandId, "รับคำสั่งอัปเดตสำเร็จ กำลังดำเนินการอัปเดตบน " + _cachedSpecs.Hostname);
                                _notifyIcon.ShowBalloonTip(3000, "อัปเดต THC IT Agent", "ระบบส่วนกลางสั่งอัปเดตเวอร์ชัน Agent ล่าสุด...", ToolTipIcon.Info);

                                ExecuteSelfUpdate(true);
                            }
                        }
                        else if (hasCommand && command.Equals("uninstall", StringComparison.OrdinalIgnoreCase))
                        {
                            _lastExecutedCommandId = commandId;
                            _lastExecutedTime = DateTime.Now;

                            SetStatus(false, false, "ได้รับคำสั่งถอนการติดตั้ง Agent...");
                            if (commandId > 0)
                            {
                                AcknowledgeCommandComplete(commandId, "รับคำสั่งถอนการติดตั้งสำเร็จ กำลังถอนการติดตั้งออกจาก " + _cachedSpecs.Hostname);
                            }
                            _notifyIcon.ShowBalloonTip(3000, "ถอนการติดตั้ง THC IT Agent", "กำลังดำเนินการถอนการติดตั้ง Agent ตามคำสั่งจากระบบส่วนกลาง...", ToolTipIcon.Warning);

                            ExecuteSelfUninstall(true);
                        }
                        else
                        {
                            // Normal Idle Standby
                            SetStatus(true, false, "ออนไลน์ (พร้อมรับคำสั่ง)");
                        }
                    }
                    else
                    {
                        SetStatus(false, false, "ขาดการเชื่อมต่อเซิร์ฟเวอร์");
                    }
                }
                catch (Exception ex)
                {
                    SetStatus(false, false, "ข้อผิดพลาด: " + ex.Message);
                }

                // Sleep interval
                int interval = Math.Max(3, _config.HeartbeatIntervalSec);
                for (int i = 0; i < interval && _isRunning; i++)
                {
                    Thread.Sleep(1000);
                }
            }
        }

        private void ExecuteScanAndSubmit(int commandId, bool isInitial = false)
        {
            try
            {
                SetStatus(true, true, isInitial ? "กำลังลงทะเบียนส่งสเปกแรกเริ่ม..." : "กำลังอ่านข้อมูลสเปกเครื่อง...");
                _cachedSpecs = HardwareSpecs.Collect();

                SetStatus(true, true, "กำลังส่งข้อมูลเข้าสู่ระบบไอที...");
                var payload = _cachedSpecs.ToPayload(commandId > 0 ? (int?)commandId : null);
                var js = new JavaScriptSerializer();
                string jsonReq = js.Serialize(payload);

                string resp = HttpPostWithFallback("/api/hardware-audit/submit", jsonReq, 12000);
                SetStatus(true, false, "ส่งข้อมูลสเปกสำเร็จ");

                // Explicit Command Completion ACK
                if (commandId > 0)
                {
                    AcknowledgeCommandComplete(commandId, "สแกนสำเร็จจาก " + _cachedSpecs.Hostname);
                }

                if (!isInitial)
                {
                    _notifyIcon.ShowBalloonTip(3000, "ส่งข้อมูลสเปกสำเร็จ", "บันทึกข้อมูลเครื่อง " + _cachedSpecs.Hostname + " เรียบร้อยแล้ว", ToolTipIcon.Info);
                }
                else
                {
                    _notifyIcon.ShowBalloonTip(3000, "THC IT Agent ออนไลน์แล้ว", "ส่งรายงานสเปกเครื่อง " + _cachedSpecs.Hostname + " เข้าสู่ระบบสำเร็จ", ToolTipIcon.Info);
                }
            }
            catch (Exception ex)
            {
                SetStatus(true, false, "ส่งข้อมูลไม่สำเร็จ: " + ex.Message);
                if (!isInitial)
                {
                    _notifyIcon.ShowBalloonTip(4000, "เกิดข้อผิดพลาดในการส่งข้อมูล", ex.Message, ToolTipIcon.Warning);
                }
            }
        }

        private void AcknowledgeCommandComplete(int commandId, string summary)
        {
            try
            {
                string path = "/api/hardware-audit/agent-command/" + commandId + "/complete";
                string json = "{\"summary\":\"" + summary.Replace("\"", "'") + "\"}";
                HttpPostWithFallback(path, json, 5000);
            }
            catch { }
        }

        public void TriggerManualScan()
        {
            new Thread(() =>
            {
                _notifyIcon.ShowBalloonTip(2000, "กำลังดึงสเปกเครื่อง", "กำลังอ่านข้อมูลฮาร์ดแวร์และส่งเข้าสู่ระบบ...", ToolTipIcon.Info);
                ExecuteScanAndSubmit(0);
            }) { IsBackground = true }.Start();
        }

        /// <summary>
        /// Intelligent HTTP Request Engine with Multi-Server Automatic Failover
        /// </summary>
        private string HttpPostWithFallback(string relativePath, string jsonBody, int timeoutMs)
        {
            var urlsToTry = new List<string>();

            // 1. Primary: currently configured ServerUrl
            string primary = _config.ServerUrl.TrimEnd('/');
            urlsToTry.Add(primary);

            // 2. Candidate fallbacks
            foreach (string cand in AppConfig.CandidateUrls)
            {
                string c = cand.TrimEnd('/');
                if (!urlsToTry.Contains(c))
                {
                    urlsToTry.Add(c);
                }
            }

            Exception lastEx = null;

            foreach (string baseUrl in urlsToTry)
            {
                string fullUrl = baseUrl + relativePath;
                try
                {
                    string result = HttpPostRaw(fullUrl, jsonBody, timeoutMs);
                    if (!string.IsNullOrEmpty(result))
                    {
                        // If this successful URL was different from current config, auto-switch and save
                        if (!baseUrl.Equals(primary, StringComparison.OrdinalIgnoreCase))
                        {
                            _config.ServerUrl = baseUrl;
                            _config.Save();
                        }
                        return result;
                    }
                }
                catch (WebException wex)
                {
                    lastEx = wex;
                    // If server responded with an error (e.g. 422, 500), try to read error body for diagnostics
                    if (wex.Response != null)
                    {
                        try
                        {
                            using (var reader = new StreamReader(wex.Response.GetResponseStream(), Encoding.UTF8))
                            {
                                string errBody = reader.ReadToEnd();
                                if (!string.IsNullOrEmpty(errBody) && errBody.Contains("\"success\""))
                                {
                                    return errBody; // Handled JSON response
                                }
                            }
                        }
                        catch { }
                    }
                }
                catch (Exception ex)
                {
                    lastEx = ex;
                }
            }

            if (lastEx != null) throw lastEx;
            return null;
        }

        private string HttpPostRaw(string url, string jsonBody, int timeoutMs)
        {
            var req = (HttpWebRequest)WebRequest.Create(url);
            req.Method = "POST";
            req.ContentType = "application/json; charset=utf-8";
            req.Accept = "application/json";
            req.Timeout = timeoutMs;
            req.UserAgent = "THC-IT-Agent/2.8.0";
            req.KeepAlive = false;

            byte[] bytes = Encoding.UTF8.GetBytes(jsonBody);
            req.ContentLength = bytes.Length;

            using (Stream reqStream = req.GetRequestStream())
            {
                reqStream.Write(bytes, 0, bytes.Length);
            }

            using (var resp = (HttpWebResponse)req.GetResponse())
            using (var reader = new StreamReader(resp.GetResponseStream(), Encoding.UTF8))
            {
                return reader.ReadToEnd();
            }
        }

        private void ShowDeviceSpecs()
        {
            if (_cachedSpecs == null)
            {
                _cachedSpecs = HardwareSpecs.Collect();
            }

            var form = new Form
            {
                Text = "💻 ข้อมูลสเปกเครื่อง - THC IT Agent",
                Size = new Size(580, 520),
                StartPosition = FormStartPosition.CenterScreen,
                FormBorderStyle = FormBorderStyle.FixedDialog,
                MaximizeBox = false,
                MinimizeBox = false,
                ShowInTaskbar = true,
                BackColor = Color.White
            };

            var pnlHeader = new Panel
            {
                Dock = DockStyle.Top,
                Height = 65,
                BackColor = Color.FromArgb(41, 128, 185)
            };
            var lblTitle = new Label
            {
                Text = "โรงพยาบาลทุ่งหัวช้าง - ระบบตรวจเช็คทรัพย์สินไอที",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12F, FontStyle.Bold),
                Location = new Point(16, 12),
                AutoSize = true
            };
            var lblSub = new Label
            {
                Text = "THC Client Hardware Audit Agent v2.8.0 (Standalone System Tray)",
                ForeColor = Color.FromArgb(220, 240, 255),
                Font = new Font("Segoe UI", 9F),
                Location = new Point(17, 36),
                AutoSize = true
            };
            pnlHeader.Controls.Add(lblTitle);
            pnlHeader.Controls.Add(lblSub);
            form.Controls.Add(pnlHeader);

            var list = new ListView
            {
                Location = new Point(16, 80),
                Size = new Size(532, 340),
                View = View.Details,
                FullRowSelect = true,
                GridLines = true,
                Font = new Font("Segoe UI", 9F)
            };
            list.Columns.Add("รายการฮาร์ดแวร์", 160);
            list.Columns.Add("ข้อมูลที่ตรวจพบ", 350);

            Action addRow = () =>
            {
                list.Items.Clear();
                list.Items.Add(new ListViewItem(new[] { "ชื่อเครื่อง (Hostname)", _cachedSpecs.Hostname }));
                list.Items.Add(new ListViewItem(new[] { "ยี่ห้อ / รุ่น (Model)", _cachedSpecs.Brand + " " + _cachedSpecs.Model }));
                list.Items.Add(new ListViewItem(new[] { "ประเภทอุปกรณ์", _cachedSpecs.DeviceTypeCode }));
                list.Items.Add(new ListViewItem(new[] { "ซีเรียลนัมเบอร์ (Serial)", _cachedSpecs.SerialNumber }));
                list.Items.Add(new ListViewItem(new[] { "รหัสฮาร์ดแวร์ (HWID)", _cachedSpecs.HardwareId }));
                list.Items.Add(new ListViewItem(new[] { "หน่วยประมวลผล (CPU)", _cachedSpecs.CpuModel + " (" + _cachedSpecs.CpuSpeed + ")" }));
                list.Items.Add(new ListViewItem(new[] { "หน่วยความจำ (RAM)", _cachedSpecs.RamCapacity + " GB " + _cachedSpecs.RamType + " (" + _cachedSpecs.RamBus + " [" + _cachedSpecs.RamSlots + "])" }));
                list.Items.Add(new ListViewItem(new[] { "พื้นที่จัดเก็บหลัก (Storage)", _cachedSpecs.StorageType + " " + _cachedSpecs.StorageCapacity }));
                if (!string.IsNullOrEmpty(_cachedSpecs.StorageSecond))
                    list.Items.Add(new ListViewItem(new[] { "พื้นที่จัดเก็บเสริม (Secondary)", _cachedSpecs.StorageSecond }));
                list.Items.Add(new ListViewItem(new[] { "ระบบปฏิบัติการ (OS)", _cachedSpecs.OsName + " (" + _cachedSpecs.OsLicense + ")" }));
                list.Items.Add(new ListViewItem(new[] { "การ์ดแสดงผล (GPU)", _cachedSpecs.GpuModel }));
                list.Items.Add(new ListViewItem(new[] { "IP Address / MAC", _cachedSpecs.IpAddress + " (" + _cachedSpecs.MacAddress + ")" }));
                list.Items.Add(new ListViewItem(new[] { "หน้าจอแสดงผล (Monitor)", _cachedSpecs.MonitorSize }));
                list.Items.Add(new ListViewItem(new[] { "เซิร์ฟเวอร์เชื่อมต่อ", _config.ServerUrl }));
            };
            addRow();
            form.Controls.Add(list);

            var btnSubmit = new Button
            {
                Text = "📥 ส่งข้อมูลขึ้นระบบเดี๋ยวนี้",
                Location = new Point(16, 432),
                Size = new Size(180, 34),
                BackColor = Color.FromArgb(46, 204, 113),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            btnSubmit.Click += (s, e) =>
            {
                TriggerManualScan();
                MessageBox.Show("กำลังส่งข้อมูลสเปกเครื่องเข้าสู่ระบบ IT...", "THC IT Agent", MessageBoxButtons.OK, MessageBoxIcon.Information);
            };
            form.Controls.Add(btnSubmit);

            var btnRefresh = new Button
            {
                Text = "🔄 รีเฟรชสเปก",
                Location = new Point(204, 432),
                Size = new Size(110, 34),
                BackColor = Color.FromArgb(240, 240, 240),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F)
            };
            btnRefresh.Click += (s, e) =>
            {
                _cachedSpecs = HardwareSpecs.Collect();
                addRow();
            };
            form.Controls.Add(btnRefresh);

            var btnClose = new Button
            {
                Text = "ปิดหน้าต่าง",
                Location = new Point(438, 432),
                Size = new Size(110, 34),
                BackColor = Color.FromArgb(240, 240, 240),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F)
            };
            btnClose.Click += (s, e) => form.Close();
            form.Controls.Add(btnClose);

            form.ShowDialog();
        }

        private void ShowServerSettings()
        {
            var form = new Form
            {
                Text = "⚙️ ตั้งค่าเซิร์ฟเวอร์ - THC IT Agent",
                Size = new Size(500, 320),
                StartPosition = FormStartPosition.CenterScreen,
                FormBorderStyle = FormBorderStyle.FixedDialog,
                MaximizeBox = false,
                MinimizeBox = false,
                ShowInTaskbar = true,
                BackColor = Color.White
            };

            var lbl = new Label
            {
                Text = "ที่อยู่เซิร์ฟเวอร์ระบบไอที (Server API URL):",
                Location = new Point(20, 18),
                AutoSize = true,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            form.Controls.Add(lbl);

            var txtUrl = new TextBox
            {
                Text = _config.ServerUrl,
                Location = new Point(20, 44),
                Size = new Size(440, 26),
                Font = new Font("Segoe UI", 10F)
            };
            form.Controls.Add(txtUrl);

            var lblPresets = new Label
            {
                Text = "เลือกเซิร์ฟเวอร์ด่วน:",
                Location = new Point(20, 80),
                AutoSize = true,
                Font = new Font("Segoe UI", 8.5F, FontStyle.Regular),
                ForeColor = Color.Gray
            };
            form.Controls.Add(lblPresets);

            var btnMophHttps = new Button
            {
                Text = "🌐 MOPH Cloud (HTTPS)",
                Location = new Point(20, 104),
                Size = new Size(140, 28),
                Font = new Font("Segoe UI", 8F)
            };
            btnMophHttps.Click += (s, e) => txtUrl.Text = "https://thchospital.moph.go.th/it-system";
            form.Controls.Add(btnMophHttps);

            var btnMophHttp = new Button
            {
                Text = "🌐 MOPH Cloud (HTTP)",
                Location = new Point(168, 104),
                Size = new Size(140, 28),
                Font = new Font("Segoe UI", 8F)
            };
            btnMophHttp.Click += (s, e) => txtUrl.Text = "http://thchospital.moph.go.th/it-system";
            form.Controls.Add(btnMophHttp);

            var btnLan = new Button
            {
                Text = "🏢 รพ. LAN (:8000)",
                Location = new Point(316, 104),
                Size = new Size(144, 28),
                Font = new Font("Segoe UI", 8F)
            };
            btnLan.Click += (s, e) => txtUrl.Text = "http://192.168.2.89:8000";
            form.Controls.Add(btnLan);

            var btnTest = new Button
            {
                Text = "🔍 ทดสอบการเชื่อมต่อเซิร์ฟเวอร์",
                Location = new Point(20, 150),
                Size = new Size(200, 32),
                BackColor = Color.FromArgb(52, 152, 219),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            btnTest.Click += (s, e) =>
            {
                var sw = Stopwatch.StartNew();
                try
                {
                    string target = txtUrl.Text.Trim().TrimEnd('/');
                    string testUrl = target + "/api/hardware-audit/agent-command";
                    string testResp = HttpPostRaw(testUrl, "{\"hostname\":\"TEST-PING\",\"mode\":\"ping\"}", 5000);
                    sw.Stop();
                    if (!string.IsNullOrEmpty(testResp))
                    {
                        MessageBox.Show("เชื่อมต่อเซิร์ฟเวอร์สำเร็จ! 🟢\nเวลาตอบสนอง: " + sw.ElapsedMilliseconds + " ms\nURL: " + target, "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }
                    else
                    {
                        MessageBox.Show("ไม่สามารถเชื่อมต่อได้ (ไม่ได้รับข้อมูลตอบกลับ)", "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
                catch (Exception ex)
                {
                    sw.Stop();
                    MessageBox.Show("เชื่อมต่อไม่สำเร็จ 🔴\nข้อผิดพลาด: " + ex.Message, "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            };
            form.Controls.Add(btnTest);

            var btnSave = new Button
            {
                Text = "บันทึกการตั้งค่า",
                Location = new Point(240, 215),
                Size = new Size(110, 34),
                BackColor = Color.FromArgb(46, 204, 113),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            btnSave.Click += (s, e) =>
            {
                _config.ServerUrl = txtUrl.Text.Trim().TrimEnd('/');
                _config.Save();
                form.Close();
                _notifyIcon.ShowBalloonTip(2000, "บันทึกการตั้งค่าแล้ว", "กำลังเชื่อมต่อกับเซิร์ฟเวอร์: " + _config.ServerUrl, ToolTipIcon.Info);
            };
            form.Controls.Add(btnSave);

            var btnCancel = new Button
            {
                Text = "ยกเลิก",
                Location = new Point(360, 215),
                Size = new Size(100, 34),
                BackColor = Color.FromArgb(240, 240, 240),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F)
            };
            btnCancel.Click += (s, e) => form.Close();
            form.Controls.Add(btnCancel);

            form.ShowDialog();
        }

        private void ExecuteSelfUpdate(bool isRemote)
        {
            try
            {
                if (!isRemote)
                {
                    DialogResult confirm = MessageBox.Show(
                        "ต้องการตรวจสอบและอัปเดตโปรแกรม THC IT Agent เป็นเวอร์ชันล่าสุดหรือไม่?",
                        "อัปเดต THC IT Agent",
                        MessageBoxButtons.YesNo,
                        MessageBoxIcon.Question);
                    if (confirm != DialogResult.Yes) return;
                }

                string appDir = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location);
                string updateBat = Path.Combine(appDir, "update.bat");
                string progDataUpdate = @"C:\ProgramData\THC-IT-Agent\update.bat";

                string scriptToRun = null;
                if (File.Exists(updateBat)) scriptToRun = updateBat;
                else if (File.Exists(progDataUpdate)) scriptToRun = progDataUpdate;

                if (scriptToRun != null)
                {
                    ProcessStartInfo psi = new ProcessStartInfo();
                    psi.FileName = "cmd.exe";
                    psi.Arguments = string.Format("/c \"{0}\" \"{1}\" {2}", scriptToRun, _config.ServerUrl, isRemote ? "/silent" : "");
                    psi.WindowStyle = isRemote ? ProcessWindowStyle.Hidden : ProcessWindowStyle.Normal;
                    psi.CreateNoWindow = isRemote;
                    Process.Start(psi);
                }
                else
                {
                    string tempBat = Path.Combine(Path.GetTempPath(), "thc_agent_quick_update.bat");
                    string psScript = string.Format(
                        "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " +
                        "[Net.ServicePointManager]::ServerCertificateValidationCallback = {{$true}}; " +
                        "(New-Object Net.WebClient).DownloadFile('{0}/agent/update_thc_agent.bat', '{1}'); " +
                        "Start-Process -FilePath '{1}' -ArgumentList '\"{0}\" {2}'",
                        _config.ServerUrl, tempBat, isRemote ? "/silent" : "");

                    ProcessStartInfo psi = new ProcessStartInfo();
                    psi.FileName = "powershell.exe";
                    psi.Arguments = "-NoProfile -ExecutionPolicy Bypass -Command \"" + psScript + "\"";
                    psi.WindowStyle = ProcessWindowStyle.Hidden;
                    psi.CreateNoWindow = true;
                    Process.Start(psi);
                }

                ThreadPool.QueueUserWorkItem(state =>
                {
                    Thread.Sleep(1500);
                    ExitApp();
                });
            }
            catch (Exception ex)
            {
                if (!isRemote)
                {
                    MessageBox.Show("ไม่สามารถเริ่มการอัปเดตได้: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
        }

        private void ExecuteSelfUninstall(bool isRemote)
        {
            try
            {
                if (!isRemote)
                {
                    DialogResult confirm = MessageBox.Show(
                        "คุณแน่ใจหรือไม่ว่าต้องการถอนการติดตั้ง THC IT Agent ออกจากเครื่องนี้?\n\nระบบจะลบโปรแกรม ทางลัด และการตั้งค่าทั้งหมด",
                        "ยืนยันการถอนการติดตั้ง",
                        MessageBoxButtons.YesNo,
                        MessageBoxIcon.Warning);
                    if (confirm != DialogResult.Yes) return;
                }

                string appDir = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location);
                string uninstallBat = Path.Combine(appDir, "uninstall.bat");
                string progDataUninstall = @"C:\ProgramData\THC-IT-Agent\uninstall.bat";

                string scriptToRun = null;
                if (File.Exists(uninstallBat)) scriptToRun = uninstallBat;
                else if (File.Exists(progDataUninstall)) scriptToRun = progDataUninstall;

                if (scriptToRun != null)
                {
                    ProcessStartInfo psi = new ProcessStartInfo();
                    psi.FileName = "cmd.exe";
                    psi.Arguments = string.Format("/c \"{0}\" {1}", scriptToRun, isRemote ? "/silent" : "");
                    psi.WindowStyle = isRemote ? ProcessWindowStyle.Hidden : ProcessWindowStyle.Normal;
                    psi.CreateNoWindow = isRemote;
                    Process.Start(psi);
                }
                else
                {
                    string tempBat = Path.Combine(Path.GetTempPath(), "thc_agent_quick_uninstall.bat");
                    string psScript = string.Format(
                        "[Net.ServicePointManager]::SecurityProtocol = 3072 -bor 768 -bor [Net.SecurityProtocolType]::Tls; " +
                        "[Net.ServicePointManager]::ServerCertificateValidationCallback = {{$true}}; " +
                        "(New-Object Net.WebClient).DownloadFile('{0}/agent/uninstall_thc_agent.bat', '{1}'); " +
                        "Start-Process -FilePath '{1}' -ArgumentList '{2}'",
                        _config.ServerUrl, tempBat, isRemote ? "/silent" : "");

                    ProcessStartInfo psi = new ProcessStartInfo();
                    psi.FileName = "powershell.exe";
                    psi.Arguments = "-NoProfile -ExecutionPolicy Bypass -Command \"" + psScript + "\"";
                    psi.WindowStyle = ProcessWindowStyle.Hidden;
                    psi.CreateNoWindow = true;
                    Process.Start(psi);
                }

                ThreadPool.QueueUserWorkItem(state =>
                {
                    Thread.Sleep(1200);
                    ExitApp();
                });
            }
            catch (Exception ex)
            {
                if (!isRemote)
                {
                    MessageBox.Show("ไม่สามารถถอนการติดตั้งได้: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
        }

        private void ExitApp()
        {
            _isRunning = false;
            if (_notifyIcon != null)
            {
                _notifyIcon.Visible = false;
                _notifyIcon.Dispose();
            }
            Application.Exit();
        }
    }
}
