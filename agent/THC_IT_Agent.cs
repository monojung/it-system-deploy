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
using System.Security.Cryptography;
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
        public const string CurrentVersion = "2.11.0";
        private const string AppGuid = "THC-IT-Agent-f91aa586-7788-9900-thung-hua-chang";
        private static Mutex _mutex;

        [STAThread]
        static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            // Clean up any pending .old file from previous atomic update
            try
            {
                string exePath = Assembly.GetExecutingAssembly().Location;
                string oldFile = exePath + ".old";
                if (File.Exists(oldFile)) File.Delete(oldFile);
            }
            catch { }

            // Configure TLS security protocols
            try
            {
                ServicePointManager.SecurityProtocol = (SecurityProtocolType)3072 | (SecurityProtocolType)768 | SecurityProtocolType.Tls;
                ServicePointManager.ServerCertificateValidationCallback =
                    delegate(object s, X509Certificate cert, X509Chain chain, SslPolicyErrors sslErr) { return true; };
            }
            catch { }

            // Parse command line arguments
            bool isInstall = false;
            bool isUninstall = false;
            bool isSilent = false;
            string updatedFrom = null;
            string customServer = null;

            if (args != null)
            {
                for (int i = 0; i < args.Length; i++)
                {
                    string a = args[i].ToLowerInvariant().Trim('-', '/');
                    if (a == "install" || a == "setup") isInstall = true;
                    else if (a == "uninstall" || a == "remove") isUninstall = true;
                    else if (a == "silent" || a == "quiet" || a == "s" || a == "q") isSilent = true;
                    else if ((a == "updated-from" || a == "update-post") && i + 1 < args.Length) updatedFrom = args[++i];
                    else if ((a == "server" || a == "url") && i + 1 < args.Length) customServer = args[++i];
                }
            }

            // Command-line Uninstallation
            if (isUninstall)
            {
                // Farewell beacon before wiping
                try
                {
                    var cfg = AppConfig.Load();
                    if (cfg != null && !string.IsNullOrEmpty(cfg.ServerUrl))
                    {
                        string url = cfg.ServerUrl.TrimEnd('/') + "/api/hardware-audit/agent-ping";
                        var req = (HttpWebRequest)WebRequest.Create(url);
                        req.Method = "POST";
                        req.Timeout = 2500;
                        req.ContentType = "application/json; charset=utf-8";
                        req.UserAgent = "THC-Hospital-IT-Agent/" + CurrentVersion + " (CLI Uninstaller)";
                        var js = new JavaScriptSerializer();
                        string payload = js.Serialize(new
                        {
                            action = "uninstalled",
                            hostname = Environment.MachineName,
                            reason = "cli_uninstall",
                            timestamp = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
                        });
                        byte[] bytes = Encoding.UTF8.GetBytes(payload);
                        req.ContentLength = bytes.Length;
                        using (var os = req.GetRequestStream())
                        {
                            os.Write(bytes, 0, bytes.Length);
                        }
                        using (var resp = (HttpWebResponse)req.GetResponse()) { }
                    }
                }
                catch { }

                InstallerHelper.Uninstall(isSilent);
                return;
            }

            // Command-line Installation
            if (isInstall)
            {
                InstallerHelper.Install(customServer, isSilent);
                return;
            }

            // Interactive First-Run / Setup Wizard check:
            // If user double-clicked THC_IT_Agent.exe outside C:\ProgramData\THC-IT-Agent\ (e.g. from Downloads/Desktop)
            string curExe = Assembly.GetExecutingAssembly().Location;
            string stdExe = InstallerHelper.GetStandardExePath();
            bool isStandardLocation = curExe.Equals(stdExe, StringComparison.OrdinalIgnoreCase);

            if (!isStandardLocation && !isSilent)
            {
                using (var wizard = new SetupWizardForm(curExe, stdExe))
                {
                    var dlg = wizard.ShowDialog();
                    if (dlg == DialogResult.OK)
                    {
                        // Installed successfully and started installed process
                        return;
                    }
                    if (!wizard.ProceedWithPortable)
                    {
                        // User canceled setup
                        return;
                    }
                }
            }

            // Allow only one instance per user session
            bool createdNew;
            _mutex = new Mutex(true, AppGuid, out createdNew);
            if (!createdNew)
            {
                if (!isSilent)
                {
                    MessageBox.Show(
                        "โปรแกรม THC IT Agent กำลังทำงานอยู่ใน System Tray (มุมขวาล่างข้างนาฬิกา) แล้ว",
                        "THC IT Agent",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Information);
                }
                return;
            }

            Application.Run(new TrayContext(updatedFrom));

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
            HeartbeatIntervalSec = 60;
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
                        // Auto-migrate legacy 4s interval to safe 60s to prevent C2 / Botnet beaconing false positives
                        if (cfg.HeartbeatIntervalSec < 30)
                        {
                            cfg.HeartbeatIntervalSec = 60;
                            try { cfg.Save(); } catch { }
                        }
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
                { "client_agent_version", "2.11.0-Standalone" },
                { "agent_version", "2.11.0-Standalone" }
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
        private static readonly Random _random = new Random();

        public TrayContext(string updatedFrom = null)
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
            var titleItem = new ToolStripMenuItem("🖥️ THC IT Agent v2.11.0 (รพ.ทุ่งหัวช้าง)")
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

            // On-Demand Pull Mode: Do NOT scan or transmit specs on computer startup.
            // Client stays quietly in tray on Standby until an Admin dispatches a scan command.
            SetStatus(true, false, "ออนไลน์ (สแตนด์บายพร้อมรับคำสั่ง)");

            // Notify if newly updated
            if (!string.IsNullOrEmpty(updatedFrom))
            {
                try
                {
                    _notifyIcon.ShowBalloonTip(4000, "อัปเดต THC IT Agent สำเร็จ", "อัปเดตเป็นเวอร์ชัน " + Program.CurrentVersion + " เรียบร้อยแล้ว (อัปเกรดจาก " + updatedFrom + ")", ToolTipIcon.Info);
                }
                catch { }
            }
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
                        { "agent_version", "2.11.0-Standalone" },
                        { "client_version", "2.11.0-Standalone" },
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

                // Sleep interval with random jitter to avoid repetitive C2 beaconing signatures
                int baseInterval = Math.Max(20, _config.HeartbeatIntervalSec);
                int jitter = _random.Next(-10, 11);
                int interval = Math.Max(15, baseInterval + jitter);
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
            req.UserAgent = "THC-Hospital-IT-Agent/2.11.0 (Windows NT; Asset Management; Thung Hua Chang Hospital)";
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
                Text = "THC Client Hardware Audit Agent v2.11.0 (Standalone System Tray)",
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
                string currentExe = Assembly.GetExecutingAssembly().Location;
                string currentDir = Path.GetDirectoryName(currentExe);
                string newExe = currentExe + ".new";
                string oldExe = currentExe + ".old";

                string primary = _config.ServerUrl.TrimEnd('/');
                string manifestUrl = primary + "/agent/manifest.json";
                string expectedSha256 = null;
                string remoteVersion = null;

                // 1. Fetch manifest for version check and hash validation
                try
                {
                    var manReq = (HttpWebRequest)WebRequest.Create(manifestUrl);
                    manReq.Timeout = 8000;
                    manReq.UserAgent = "THC-Hospital-IT-Agent-Updater/" + Program.CurrentVersion;
                    using (var manResp = (HttpWebResponse)manReq.GetResponse())
                    using (var reader = new StreamReader(manResp.GetResponseStream(), Encoding.UTF8))
                    {
                        string json = reader.ReadToEnd();
                        var js = new JavaScriptSerializer();
                        var dict = js.Deserialize<Dictionary<string, object>>(json);
                        if (dict != null)
                        {
                            if (dict.ContainsKey("version")) remoteVersion = dict["version"] as string;
                            if (dict.ContainsKey("files"))
                            {
                                var files = dict["files"] as Dictionary<string, object>;
                                if (files != null && files.ContainsKey("executable"))
                                {
                                    var exeInfo = files["executable"] as Dictionary<string, object>;
                                    if (exeInfo != null && exeInfo.ContainsKey("sha256"))
                                    {
                                        expectedSha256 = exeInfo["sha256"] as string;
                                    }
                                }
                            }
                        }
                    }
                }
                catch { }

                // 2. Interactive confirmation
                if (!isRemote)
                {
                    string prompt = "ต้องการตรวจสอบและอัปเดตโปรแกรม THC IT Agent เป็นเวอร์ชันล่าสุดจากเซิร์ฟเวอร์หรือไม่?\n\n" +
                                   "• เวอร์ชันปัจจุบัน: v" + Program.CurrentVersion + "\n" +
                                   "• เวอร์ชันบนเซิร์ฟเวอร์: " + (remoteVersion != null ? "v" + remoteVersion : "ตรวจสอบจากเซิร์ฟเวอร์");

                    if (!string.IsNullOrEmpty(remoteVersion) && remoteVersion.Equals(Program.CurrentVersion, StringComparison.OrdinalIgnoreCase))
                    {
                        prompt = "โปรแกรม THC IT Agent เป็นเวอร์ชันล่าสุดแล้ว (v" + Program.CurrentVersion + ")\n\n" +
                                 "ต้องการดาวน์โหลดและติดตั้งทับใหม่ทั้งหมด (Force Reinstall) หรือไม่?";
                    }

                    DialogResult confirm = MessageBox.Show(prompt, "อัปเดต THC IT Agent", MessageBoxButtons.YesNo, MessageBoxIcon.Question);
                    if (confirm != DialogResult.Yes) return;
                }

                SetStatus(true, true, "กำลังดาวน์โหลดไฟล์ Agent เวอร์ชันล่าสุด...");

                var urlsToTry = new List<string>();
                urlsToTry.Add(primary + "/agent/THC_IT_Agent.exe");
                foreach (string cand in AppConfig.CandidateUrls)
                {
                    string u = cand.TrimEnd('/') + "/agent/THC_IT_Agent.exe";
                    if (!urlsToTry.Contains(u)) urlsToTry.Add(u);
                }

                bool downloaded = false;
                foreach (string url in urlsToTry)
                {
                    try
                    {
                        var req = (HttpWebRequest)WebRequest.Create(url);
                        req.Timeout = 15000;
                        req.UserAgent = "THC-Hospital-IT-Agent-Updater/" + Program.CurrentVersion;
                        using (var resp = (HttpWebResponse)req.GetResponse())
                        using (var stream = resp.GetResponseStream())
                        using (var fs = new FileStream(newExe, FileMode.Create, FileAccess.Write, FileShare.None))
                        {
                            byte[] buffer = new byte[8192];
                            int read;
                            while ((read = stream.Read(buffer, 0, buffer.Length)) > 0)
                            {
                                fs.Write(buffer, 0, read);
                            }
                        }

                        if (File.Exists(newExe) && new FileInfo(newExe).Length > 20000)
                        {
                            downloaded = true;
                            break;
                        }
                    }
                    catch { }
                }

                if (!downloaded)
                {
                    if (File.Exists(newExe)) try { File.Delete(newExe); } catch { }
                    SetStatus(true, false, "อัปเดตไม่สำเร็จ: ดาวน์โหลดไฟล์ล้มเหลว");
                    if (!isRemote)
                    {
                        MessageBox.Show("ไม่สามารถดาวน์โหลดไฟล์ Agent เวอร์ชันล่าสุดจากเซิร์ฟเวอร์ได้\nกรุณาตรวจสอบการเชื่อมต่อเครือข่าย", "อัปเดตไม่สำเร็จ", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                    return;
                }

                // 3. Binary & Integrity Validation (Anti-corruption check)
                if (!InstallerHelper.IsValidPEBinary(newExe))
                {
                    if (File.Exists(newExe)) try { File.Delete(newExe); } catch { }
                    throw new Exception("ไฟล์ที่ดาวน์โหลดมาไม่ใช่ไฟล์ไบนารี Windows ที่ถูกต้อง (อาจเป็นหน้าแจ้งเตือนเครือข่าย)");
                }

                if (!string.IsNullOrEmpty(expectedSha256))
                {
                    string actualSha = InstallerHelper.ComputeSha256(newExe);
                    if (!string.IsNullOrEmpty(actualSha) && !actualSha.Equals(expectedSha256.Trim(), StringComparison.OrdinalIgnoreCase))
                    {
                        if (File.Exists(newExe)) try { File.Delete(newExe); } catch { }
                        throw new Exception("ตรวจสอบความถูกต้องล้มเหลว: SHA-256 ไม่ตรงกับ Manifest");
                    }
                }

                // 4. In-Process Atomic Binary Swap
                if (File.Exists(oldExe)) try { File.Delete(oldExe); } catch { }
                File.Move(currentExe, oldExe);
                File.Move(newExe, currentExe);

                // 5. Spawn new process with Automatic Rollback on failure
                try
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = currentExe,
                        WorkingDirectory = currentDir,
                        UseShellExecute = true,
                        Arguments = "/update-post " + Program.CurrentVersion
                    });
                }
                catch (Exception startEx)
                {
                    // Rollback immediately if spawn failed
                    try
                    {
                        if (File.Exists(currentExe)) File.Delete(currentExe);
                        if (File.Exists(oldExe)) File.Move(oldExe, currentExe);
                    }
                    catch { }
                    throw new Exception("ไม่สามารถเริ่มโปรเซสเวอร์ชันใหม่ได้ (Rollback กลับสู่เวอร์ชันเดิมแล้ว): " + startEx.Message);
                }

                // Clean exit
                _isRunning = false;
                if (_notifyIcon != null)
                {
                    _notifyIcon.Visible = false;
                    _notifyIcon.Dispose();
                }
                Application.Exit();
            }
            catch (Exception ex)
            {
                SetStatus(true, false, "อัปเดตไม่สำเร็จ: " + ex.Message);
                if (!isRemote)
                {
                    MessageBox.Show("เกิดข้อผิดพลาดในการอัปเดต: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
        }

        private void ExecuteSelfUninstall(bool isRemote)
        {
            try
            {
                if (!isRemote)
                {
                    var confirm = MessageBox.Show(
                        "ต้องการถอนการติดตั้ง THC IT Agent ออกจากเครื่องนี้หรือไม่?\n\n" +
                        "• ระบบจะลบไฟล์โปรแกรม การตั้งค่า และการเริ่มทำงานอัตโนมัติทั้งหมด\n" +
                        "• นำเครื่องนี้ออกจากระบบมอนิเตอร์ของโรงพยาบาล",
                        "ยืนยันการถอนการติดตั้ง - THC IT Agent",
                        MessageBoxButtons.YesNo,
                        MessageBoxIcon.Warning);
                    if (confirm != DialogResult.Yes) return;
                }

                SetStatus(false, false, "กำลังส่งข้อมูลยืนยันการถอนการติดตั้งไปยังเซิร์ฟเวอร์...");

                // 1. Send farewell uninstalled beacon to server
                SendUninstallBeacon(isRemote ? "remote_command" : "tray_menu");

                // 2. Perform deep system cleanup & self-destruction
                bool success = InstallerHelper.Uninstall(isRemote);
                if (success)
                {
                    ExitApp();
                }
            }
            catch (Exception ex)
            {
                if (!isRemote)
                {
                    MessageBox.Show("ไม่สามารถถอนการติดตั้งได้: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            }
        }

        private void SendUninstallBeacon(string reason)
        {
            try
            {
                if (_config == null || string.IsNullOrEmpty(_config.ServerUrl)) return;
                string url = _config.ServerUrl.TrimEnd('/') + "/api/hardware-audit/agent-ping";
                var req = (HttpWebRequest)WebRequest.Create(url);
                req.Method = "POST";
                req.Timeout = 3000;
                req.ContentType = "application/json; charset=utf-8";
                req.UserAgent = "THC-Hospital-IT-Agent/" + Program.CurrentVersion + " (Uninstaller)";
                var js = new JavaScriptSerializer();
                string payload = js.Serialize(new
                {
                    action = "uninstalled",
                    hostname = _cachedSpecs != null ? _cachedSpecs.Hostname : Environment.MachineName,
                    hardware_id = _cachedSpecs != null ? _cachedSpecs.HardwareId : "",
                    ip_address = _cachedSpecs != null ? _cachedSpecs.IpAddress : "",
                    reason = reason,
                    timestamp = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
                });
                byte[] bytes = Encoding.UTF8.GetBytes(payload);
                req.ContentLength = bytes.Length;
                using (var os = req.GetRequestStream())
                {
                    os.Write(bytes, 0, bytes.Length);
                }
                using (var resp = (HttpWebResponse)req.GetResponse()) { }
            }
            catch { }
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

    public static class InstallerHelper
    {
        public const string StandardInstallDir = @"C:\ProgramData\THC-IT-Agent";
        public const string UninstallRegKey = @"Software\Microsoft\Windows\CurrentVersion\Uninstall\THC_IT_Agent";

        public static string GetStandardExePath()
        {
            return Path.Combine(StandardInstallDir, "THC_IT_Agent.exe");
        }

        public static string ComputeSha256(string filePath)
        {
            try
            {
                using (var sha = SHA256.Create())
                using (var fs = new FileStream(filePath, FileMode.Open, FileAccess.Read, FileShare.Read))
                {
                    byte[] hash = sha.ComputeHash(fs);
                    StringBuilder sb = new StringBuilder();
                    for (int i = 0; i < hash.Length; i++)
                    {
                        sb.Append(hash[i].ToString("X2"));
                    }
                    return sb.ToString();
                }
            }
            catch
            {
                return null;
            }
        }

        public static bool IsValidPEBinary(string filePath)
        {
            try
            {
                if (!File.Exists(filePath)) return false;
                var fi = new FileInfo(filePath);
                if (fi.Length < 1024) return false;
                using (var fs = new FileStream(filePath, FileMode.Open, FileAccess.Read, FileShare.Read))
                {
                    byte[] buf = new byte[2];
                    fs.Read(buf, 0, 2);
                    return buf[0] == 0x4D && buf[1] == 0x5A; // MZ header
                }
            }
            catch
            {
                return false;
            }
        }

        public static void RegisterWindowsUninstall(string installDir, string exePath, string version)
        {
            try
            {
                using (RegistryKey key = Registry.CurrentUser.CreateSubKey(UninstallRegKey))
                {
                    if (key != null)
                    {
                        key.SetValue("DisplayName", "THC IT Agent - ระบบตรวจสอบครุภัณฑ์ รพ.ทุ่งหัวช้าง");
                        key.SetValue("DisplayVersion", version);
                        key.SetValue("Publisher", "โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital)");
                        key.SetValue("InstallLocation", installDir);
                        key.SetValue("DisplayIcon", exePath + ",0");
                        key.SetValue("UninstallString", "\"" + exePath + "\" /uninstall");
                        key.SetValue("QuietUninstallString", "\"" + exePath + "\" /uninstall /silent");
                        key.SetValue("HelpLink", "http://192.168.1.181:8000/hardware-audits");
                        key.SetValue("NoModify", 1, RegistryValueKind.DWord);
                        key.SetValue("NoRepair", 1, RegistryValueKind.DWord);
                        try
                        {
                            if (File.Exists(exePath))
                            {
                                key.SetValue("EstimatedSize", (int)(new FileInfo(exePath).Length / 1024), RegistryValueKind.DWord);
                            }
                        }
                        catch { }
                    }
                }
            }
            catch { }
        }

        public static void UnregisterWindowsUninstall()
        {
            try
            {
                Registry.CurrentUser.DeleteSubKeyTree(UninstallRegKey, false);
            }
            catch { }
            try
            {
                Registry.LocalMachine.DeleteSubKeyTree(UninstallRegKey, false);
            }
            catch { }
        }

        public static void CreateShortcut(string shortcutPath, string targetPath, string description)
        {
            try
            {
                Type shellType = Type.GetTypeFromProgID("WScript.Shell");
                if (shellType == null) return;
                object shell = Activator.CreateInstance(shellType);
                object shortcut = shellType.InvokeMember("CreateShortcut", BindingFlags.InvokeMethod, null, shell, new object[] { shortcutPath });
                if (shortcut == null) return;
                Type shortcutType = shortcut.GetType();
                shortcutType.InvokeMember("TargetPath", BindingFlags.SetProperty, null, shortcut, new object[] { targetPath });
                shortcutType.InvokeMember("WorkingDirectory", BindingFlags.SetProperty, null, shortcut, new object[] { Path.GetDirectoryName(targetPath) });
                shortcutType.InvokeMember("Description", BindingFlags.SetProperty, null, shortcut, new object[] { description });
                shortcutType.InvokeMember("Save", BindingFlags.InvokeMethod, null, shortcut, null);
            }
            catch { }
        }

        public static bool Install(string serverUrl, bool silent)
        {
            try
            {
                string currentExe = Assembly.GetExecutingAssembly().Location;
                string targetExe = GetStandardExePath();

                if (!Directory.Exists(StandardInstallDir))
                {
                    Directory.CreateDirectory(StandardInstallDir);
                }

                if (!currentExe.Equals(targetExe, StringComparison.OrdinalIgnoreCase))
                {
                    try
                    {
                        int curPid = Process.GetCurrentProcess().Id;
                        foreach (var p in Process.GetProcessesByName("THC_IT_Agent"))
                        {
                            if (p.Id != curPid)
                            {
                                try { p.Kill(); p.WaitForExit(2000); } catch { }
                            }
                        }
                    }
                    catch { }

                    File.Copy(currentExe, targetExe, true);
                }

                var cfg = AppConfig.Load();
                if (!string.IsNullOrEmpty(serverUrl))
                {
                    cfg.ServerUrl = serverUrl.TrimEnd('/');
                }
                cfg.HeartbeatIntervalSec = 60;
                cfg.AutoStart = true;
                cfg.Save();

                AppConfig.SetAutoStart(true);
                RegisterWindowsUninstall(StandardInstallDir, targetExe, Program.CurrentVersion);

                string desktop = Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory);
                string startup = Environment.GetFolderPath(Environment.SpecialFolder.Startup);
                CreateShortcut(Path.Combine(desktop, "THC IT Agent.lnk"), targetExe, "THC IT Agent - รพ.ทุ่งหัวช้าง");
                CreateShortcut(Path.Combine(startup, "THC IT Agent.lnk"), targetExe, "THC IT Agent - รพ.ทุ่งหัวช้าง");

                if (!currentExe.Equals(targetExe, StringComparison.OrdinalIgnoreCase))
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = targetExe,
                        WorkingDirectory = StandardInstallDir,
                        UseShellExecute = true
                    });
                }

                if (!silent)
                {
                    MessageBox.Show(
                        "ติดตั้งโปรแกรม THC IT Agent สำเร็จเรียบร้อยแล้ว!\n\n" +
                        "• ตำแหน่งติดตั้ง: " + StandardInstallDir + "\n" +
                        "• เซิร์ฟเวอร์: " + cfg.ServerUrl + "\n" +
                        "• ลงทะเบียนใน Windows Programs & Features สำเร็จ\n" +
                        "• โปรแกรมจะเริ่มทำงานอัตโนมัติที่ถาดระบบ (System Tray) ข้างนาฬิกา",
                        "ติดตั้งสำเร็จ - THC IT Agent",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Information);
                }
                return true;
            }
            catch (Exception ex)
            {
                if (!silent)
                {
                    MessageBox.Show("การติดตั้งไม่สำเร็จ: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
                return false;
            }
        }

        public static bool Uninstall(bool silent)
        {
            try
            {
                // 1. Terminate other running THC_IT_Agent instances
                try
                {
                    int curPid = Process.GetCurrentProcess().Id;
                    foreach (var p in Process.GetProcessesByName("THC_IT_Agent"))
                    {
                        if (p.Id != curPid)
                        {
                            try { p.Kill(); p.WaitForExit(2000); } catch { }
                        }
                    }
                }
                catch { }

                // 2. Disable AutoStart & Remove Windows Uninstall entry
                AppConfig.SetAutoStart(false);
                UnregisterWindowsUninstall();

                // 3. Delete Desktop & Startup Shortcuts
                string desktop = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.DesktopDirectory), "THC IT Agent.lnk");
                string startup = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.Startup), "THC IT Agent.lnk");
                string pubDesktop = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonDesktopDirectory), "THC IT Agent.lnk");
                string pubStartup = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonStartup), "THC IT Agent.lnk");
                if (File.Exists(desktop)) try { File.Delete(desktop); } catch { }
                if (File.Exists(startup)) try { File.Delete(startup); } catch { }
                if (File.Exists(pubDesktop)) try { File.Delete(pubDesktop); } catch { }
                if (File.Exists(pubStartup)) try { File.Delete(pubStartup); } catch { }

                // 4. Delete scheduled tasks
                try
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = "schtasks.exe",
                        Arguments = "/delete /tn \"THC_Hardware_Audit\" /f",
                        WindowStyle = ProcessWindowStyle.Hidden,
                        CreateNoWindow = true
                    });
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = "schtasks.exe",
                        Arguments = "/delete /tn \"THC_Hardware_Audit_Poll\" /f",
                        WindowStyle = ProcessWindowStyle.Hidden,
                        CreateNoWindow = true
                    });
                }
                catch { }

                // 5. Clean Post-Exit Purge (Self-destruction of install folder)
                string curExe = Assembly.GetExecutingAssembly().Location;
                string installDir = StandardInstallDir;
                bool isInstalledLocation = curExe.StartsWith(installDir, StringComparison.OrdinalIgnoreCase);

                if (isInstalledLocation)
                {
                    Process.Start(new ProcessStartInfo
                    {
                        FileName = "cmd.exe",
                        Arguments = "/C ping 127.0.0.1 -n 3 > nul & rmdir /S /Q \"" + installDir + "\"",
                        WindowStyle = ProcessWindowStyle.Hidden,
                        CreateNoWindow = true
                    });
                }

                if (!silent)
                {
                    MessageBox.Show(
                        "ถอนการติดตั้ง THC IT Agent เรียบร้อยสมบูรณ์แล้ว!\n\n" +
                        "• ลบโปรแกรมและการตั้งค่าทั้งหมดออกจากเครื่องแล้ว\n" +
                        "• ถอนการลงทะเบียนเริ่มทำงานอัตโนมัติและ Windows Apps เรียบร้อย",
                        "ถอนการติดตั้งสำเร็จ",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Information);
                }
                return true;
            }
            catch (Exception ex)
            {
                if (!silent)
                {
                    MessageBox.Show("เกิดข้อผิดพลาดในการถอนการติดตั้ง: " + ex.Message, "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
                return false;
            }
        }
    }

    public class SetupWizardForm : Form
    {
        public bool ProceedWithPortable { get; private set; }
        private TextBox _txtServer;
        private CheckBox _chkAutoStart;
        private CheckBox _chkDesktopShortcut;

        public SetupWizardForm(string currentExe, string standardExe)
        {
            this.Text = "🚀 ติดตั้ง THC IT Agent - โรงพยาบาลทุ่งหัวช้าง";
            this.Size = new Size(560, 480);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.MinimizeBox = false;
            this.BackColor = Color.White;
            this.Font = new Font("Segoe UI", 9F);

            // Top Header Panel
            var pnlHead = new Panel
            {
                Dock = DockStyle.Top,
                Height = 75,
                BackColor = Color.FromArgb(15, 118, 110)
            };
            var lblTitle = new Label
            {
                Text = "โรงพยาบาลทุ่งหัวช้าง - ระบบติดตามและรายงานสเปก",
                ForeColor = Color.White,
                Font = new Font("Segoe UI", 12F, FontStyle.Bold),
                Location = new Point(18, 14),
                AutoSize = true
            };
            var lblSub = new Label
            {
                Text = "ตัวติดตั้ง THC IT Agent v2.11.0 (Enterprise Standalone Setup)",
                ForeColor = Color.FromArgb(204, 251, 241),
                Font = new Font("Segoe UI", 9F),
                Location = new Point(19, 40),
                AutoSize = true
            };
            pnlHead.Controls.Add(lblTitle);
            pnlHead.Controls.Add(lblSub);
            this.Controls.Add(pnlHead);

            // Body content
            int top = 90;
            var lblIntro = new Label
            {
                Text = "ยินดีต้อนรับสู่ตัวติดตั้ง THC IT Agent!\n" +
                       "โปรแกรมนี้จะสแตนด์บายในถาดระบบ (System Tray) เพื่อรายงานข้อมูลฮาร์ดแวร์เมื่อฝ่ายไอทีสั่งดึงข้อมูล\n" +
                       "ปลอดภัย 100%, ตรวจสอบสเปกแม่นยำ และกินทรัพยากรเครื่องน้อยมาก",
                Location = new Point(20, top),
                Size = new Size(505, 52),
                ForeColor = Color.FromArgb(51, 65, 85)
            };
            this.Controls.Add(lblIntro);
            top += 62;

            // Server URL Group
            var lblServer = new Label
            {
                Text = "ที่อยู่เซิร์ฟเวอร์ระบบไอที (Server URL):",
                Font = new Font("Segoe UI", 9F, FontStyle.Bold),
                Location = new Point(20, top),
                AutoSize = true
            };
            this.Controls.Add(lblServer);
            top += 24;

            _txtServer = new TextBox
            {
                Text = "https://thchospital.moph.go.th/it-system",
                Location = new Point(20, top),
                Size = new Size(505, 26),
                Font = new Font("Segoe UI", 10F)
            };
            this.Controls.Add(_txtServer);
            top += 34;

            // Presets
            var btnMophHttps = new Button { Text = "🌐 MOPH Cloud (HTTPS)", Location = new Point(20, top), Size = new Size(160, 26), Font = new Font("Segoe UI", 8F) };
            btnMophHttps.Click += (s, e) => _txtServer.Text = "https://thchospital.moph.go.th/it-system";
            this.Controls.Add(btnMophHttps);

            var btnMophHttp = new Button { Text = "🌐 MOPH Cloud (HTTP)", Location = new Point(186, top), Size = new Size(150, 26), Font = new Font("Segoe UI", 8F) };
            btnMophHttp.Click += (s, e) => _txtServer.Text = "http://thchospital.moph.go.th/it-system";
            this.Controls.Add(btnMophHttp);

            var btnLan = new Button { Text = "🏢 รพ. LAN (:8000)", Location = new Point(342, top), Size = new Size(130, 26), Font = new Font("Segoe UI", 8F) };
            btnLan.Click += (s, e) => _txtServer.Text = "http://192.168.2.89:8000";
            this.Controls.Add(btnLan);
            top += 36;

            // Checkboxes
            _chkAutoStart = new CheckBox
            {
                Text = "เริ่มทำงานอัตโนมัติเมื่อเปิดเครื่อง (Start with Windows - แนะนำ)",
                Checked = true,
                Location = new Point(22, top),
                AutoSize = true,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold),
                ForeColor = Color.FromArgb(15, 118, 110)
            };
            this.Controls.Add(_chkAutoStart);
            top += 26;

            _chkDesktopShortcut = new CheckBox
            {
                Text = "สร้างไอคอนทางลัดบนหน้าจอเดสก์ท็อป (Desktop Shortcut)",
                Checked = true,
                Location = new Point(22, top),
                AutoSize = true
            };
            this.Controls.Add(_chkDesktopShortcut);
            top += 36;

            // Divider line
            var pnlLine = new Panel { Location = new Point(20, top), Size = new Size(505, 1), BackColor = Color.FromArgb(226, 232, 240) };
            this.Controls.Add(pnlLine);
            top += 16;

            // Action Buttons
            var btnInstall = new Button
            {
                Text = "🚀 ติดตั้งลงในเครื่องทันที (Install)",
                Location = new Point(20, top),
                Size = new Size(240, 42),
                BackColor = Color.FromArgb(16, 185, 129),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 10F, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnInstall.FlatAppearance.BorderSize = 0;
            btnInstall.Click += (s, e) =>
            {
                btnInstall.Enabled = false;
                btnInstall.Text = "กำลังติดตั้ง...";
                string sUrl = _txtServer.Text.Trim();
                bool ok = InstallerHelper.Install(sUrl, false);
                if (ok)
                {
                    this.DialogResult = DialogResult.OK;
                    this.Close();
                }
                else
                {
                    btnInstall.Enabled = true;
                    btnInstall.Text = "🚀 ติดตั้งลงในเครื่องทันที (Install)";
                }
            };
            this.Controls.Add(btnInstall);

            var btnPortable = new Button
            {
                Text = "รันชั่วคราวครั้งเดียว (Portable)",
                Location = new Point(268, top),
                Size = new Size(160, 42),
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(71, 85, 105),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold),
                Cursor = Cursors.Hand
            };
            btnPortable.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnPortable.Click += (s, e) =>
            {
                this.ProceedWithPortable = true;
                this.DialogResult = DialogResult.Ignore;
                this.Close();
            };
            this.Controls.Add(btnPortable);

            var btnCancel = new Button
            {
                Text = "ยกเลิก",
                Location = new Point(436, top),
                Size = new Size(88, 42),
                BackColor = Color.FromArgb(241, 245, 249),
                ForeColor = Color.FromArgb(100, 116, 139),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F)
            };
            btnCancel.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnCancel.Click += (s, e) =>
            {
                this.DialogResult = DialogResult.Cancel;
                this.Close();
            };
            this.Controls.Add(btnCancel);
        }
    }
}
