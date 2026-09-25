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

            // Enable TLS 1.2 and ignore self-signed certificates in hospital LAN
            try
            {
                ServicePointManager.SecurityProtocol = (SecurityProtocolType)3072 | SecurityProtocolType.Tls;
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

        private static string GetConfigPath()
        {
            string appDir = Path.GetDirectoryName(Assembly.GetExecutingAssembly().Location);
            string localConfig = Path.Combine(appDir, "config.json");
            if (File.Exists(localConfig)) return localConfig;

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
                    if (cfg != null) return cfg;
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
        public string CpuModel { get; set; }
        public int CpuCores { get; set; }
        public double RamCapacity { get; set; }
        public string RamType { get; set; }
        public string StorageType { get; set; }
        public double StorageCapacity { get; set; }
        public string StorageSecond { get; set; }
        public string OsName { get; set; }
        public string GpuModel { get; set; }
        public string IpAddress { get; set; }
        public string MacAddress { get; set; }
        public string MonitorInfo { get; set; }
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
                SerialNumber = "",
                CpuModel = "",
                RamType = "DDR4",
                StorageType = "SSD",
                StorageSecond = "",
                OsName = "",
                GpuModel = "",
                IpAddress = "",
                MacAddress = "",
                MonitorInfo = ""
            };

            // 1. Motherboard & UUID
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

            // Fallback for Serial Number via Win32_BIOS
            if (string.IsNullOrEmpty(specs.SerialNumber) || specs.SerialNumber.IndexOf("Default", StringComparison.OrdinalIgnoreCase) >= 0)
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

            // 2. CPU
            try
            {
                using (var s = new ManagementObjectSearcher("SELECT Name, NumberOfCores FROM Win32_Processor"))
                {
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["Name"] != null) specs.CpuModel = mo["Name"].ToString().Trim();
                        if (mo["NumberOfCores"] != null)
                        {
                            int cores;
                            if (int.TryParse(mo["NumberOfCores"].ToString(), out cores)) specs.CpuCores = cores;
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
                    foreach (ManagementObject mo in s.Get())
                    {
                        if (mo["Capacity"] != null) totalBytes += Convert.ToInt64(mo["Capacity"]);
                        if (mo["Speed"] != null && speed == 0) int.TryParse(mo["Speed"].ToString(), out speed);
                        if (mo["SMBIOSMemoryType"] != null && smbios == 0) int.TryParse(mo["SMBIOSMemoryType"].ToString(), out smbios);
                    }
                    specs.RamCapacity = Math.Round((double)totalBytes / (1024 * 1024 * 1024), 1);
                    if (smbios == 26) specs.RamType = "DDR4";
                    else if (smbios == 34) specs.RamType = "DDR5";
                    else if (smbios == 24) specs.RamType = "DDR3";
                    else if (smbios == 21) specs.RamType = "DDR2";
                    if (speed > 0) specs.RamType += " (" + speed + " MHz)";
                }
            }
            catch { }

            // 4. Storage & Disks (Accurately find Windows C: system drive)
            try
            {
                string sysDiskModel = "";
                long sysDiskSize = 0;
                string sysDiskInterface = "";
                string sysDiskMediaType = "";

                // Try finding disk partition containing C:
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
                                    if (disk["InterfaceType"] != null) sysDiskInterface = disk["InterfaceType"].ToString();
                                    if (disk["MediaType"] != null) sysDiskMediaType = disk["MediaType"].ToString();
                                    break;
                                }
                            }
                            if (!string.IsNullOrEmpty(sysDiskModel)) break;
                        }
                    }
                }
                catch { }

                // Iterate all physical disks
                var secList = new List<string>();
                using (var s = new ManagementObjectSearcher("SELECT Model, Size, MediaType, InterfaceType FROM Win32_DiskDrive"))
                {
                    int index = 0;
                    foreach (ManagementObject mo in s.Get())
                    {
                        string dModel = mo["Model"] != null ? mo["Model"].ToString().Trim() : "";
                        long dSize = mo["Size"] != null ? Convert.ToInt64(mo["Size"]) : 0;
                        double dGb = Math.Round((double)dSize / (1024 * 1024 * 1024), 0);
                        string iface = mo["InterfaceType"] != null ? mo["InterfaceType"].ToString() : "";
                        string mType = mo["MediaType"] != null ? mo["MediaType"].ToString() : "";

                        string dType = "SSD";
                        if (dModel.IndexOf("NVMe", StringComparison.OrdinalIgnoreCase) >= 0 || iface.IndexOf("SCSI", StringComparison.OrdinalIgnoreCase) >= 0)
                            dType = "NVMe SSD";
                        else if (mType.IndexOf("Fixed", StringComparison.OrdinalIgnoreCase) >= 0 && dModel.IndexOf("SSD", StringComparison.OrdinalIgnoreCase) < 0)
                            dType = "HDD";

                        // If this is the OS disk
                        if (!string.IsNullOrEmpty(sysDiskModel) && dModel.Equals(sysDiskModel, StringComparison.OrdinalIgnoreCase))
                        {
                            specs.StorageCapacity = dGb;
                            specs.StorageType = dType;
                        }
                        else if (string.IsNullOrEmpty(sysDiskModel) && index == 0)
                        {
                            specs.StorageCapacity = dGb;
                            specs.StorageType = dType;
                        }
                        else
                        {
                            secList.Add(dModel + " (" + dGb + " GB " + dType + ")");
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
                        if (mo["Caption"] != null) specs.OsName = mo["Caption"].ToString().Trim();
                        if (mo["OSArchitecture"] != null) specs.OsName += " (" + mo["OSArchitecture"].ToString().Trim() + ")";
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
                            if (string.IsNullOrEmpty(specs.GpuModel)) specs.GpuModel = g;
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
                            specs.MacAddress = mo["MACAddress"].ToString().Trim();
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
                specs.MonitorInfo = screens.Length + " จอ (" + Screen.PrimaryScreen.Bounds.Width + "x" + Screen.PrimaryScreen.Bounds.Height + ")";
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
                { "cpu_model", CpuModel },
                { "cpu_cores", CpuCores },
                { "ram_capacity", RamCapacity },
                { "ram_type", RamType },
                { "storage_type", StorageType },
                { "storage_capacity", StorageCapacity },
                { "storage_second", StorageSecond },
                { "os_name", OsName },
                { "gpu_model", GpuModel },
                { "ip_address", IpAddress },
                { "mac_address", MacAddress },
                { "monitor_info", MonitorInfo },
                { "agent_version", "2.5.5-TrayExe" }
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
            var titleItem = new ToolStripMenuItem("🖥️ THC IT Agent v2.5.5 (รพ.ทุ่งหัวช้าง)")
            {
                Enabled = false,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            _contextMenu.Items.Add(titleItem);

            // Status Item
            _statusMenuItem = new ToolStripMenuItem("⏳ กำลังเชื่อมต่อเซิร์ฟเวอร์...")
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

            // Start background worker thread
            _workerThread = new Thread(WorkerLoop) { IsBackground = true };
            _workerThread.Start();
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

            _notifyIcon.Icon = targetIcon;
            _notifyIcon.Text = ("THC IT Agent: " + iconState).Length > 63
                ? ("THC IT Agent: " + iconState).Substring(0, 63)
                : ("THC IT Agent: " + iconState);

            _statusMenuItem.Text = (online ? (busy ? "🟡 " : "🟢 ") : "🔴 ") + "สถานะ: " + iconState;
        }

        private void WorkerLoop()
        {
            // Initial brief sleep to let network settle
            Thread.Sleep(1500);

            var js = new JavaScriptSerializer();

            while (_isRunning)
            {
                try
                {
                    string serverUrl = _config.ServerUrl.TrimEnd('/');
                    string pollUrl = serverUrl + "/api/hardware-audit/agent-command";

                    // Lightweight Heartbeat Payload
                    var pingData = new Dictionary<string, object>
                    {
                        { "hostname", Environment.MachineName },
                        { "agent_version", "2.5.5-TrayExe" },
                        { "mode", "tray_agent" }
                    };

                    if (_cachedSpecs != null && !string.IsNullOrEmpty(_cachedSpecs.HardwareId))
                    {
                        pingData["hardware_id"] = _cachedSpecs.HardwareId;
                        pingData["hwid"] = _cachedSpecs.HardwareId;
                    }

                    string jsonReq = js.Serialize(pingData);
                    string respJson = HttpPostJson(pollUrl, jsonReq, 5000);

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
                            // On-Demand Pull Triggered by Admin!
                            SetStatus(true, true, "กำลังดึงและส่งสเปกเครื่องตามคำสั่งไอที...");
                            _notifyIcon.ShowBalloonTip(3000, "ฝ่ายไอทีกำลังดึงข้อมูลสเปก", "ระบบส่วนกลางส่งคำสั่งดึงสเปกเครื่อง (คำสั่ง #" + commandId + ")", ToolTipIcon.Info);

                            ExecuteScanAndSubmit(commandId);
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
                int interval = Math.Max(2, _config.HeartbeatIntervalSec);
                for (int i = 0; i < interval && _isRunning; i++)
                {
                    Thread.Sleep(1000);
                }
            }
        }

        private void ExecuteScanAndSubmit(int commandId)
        {
            try
            {
                SetStatus(true, true, "กำลังอ่านข้อมูลสเปกเครื่อง...");
                _cachedSpecs = HardwareSpecs.Collect();

                SetStatus(true, true, "กำลังส่งข้อมูลเข้าสู่ระบบไอที...");
                string serverUrl = _config.ServerUrl.TrimEnd('/');
                string submitUrl = serverUrl + "/api/hardware-audit/submit";

                var payload = _cachedSpecs.ToPayload(commandId > 0 ? (int?)commandId : null);
                var js = new JavaScriptSerializer();
                string jsonReq = js.Serialize(payload);

                string resp = HttpPostJson(submitUrl, jsonReq, 10000);
                SetStatus(true, false, "ส่งข้อมูลสเปกสำเร็จ");

                _notifyIcon.ShowBalloonTip(3000, "ส่งข้อมูลสเปกสำเร็จ", "บันทึกข้อมูลเครื่อง " + _cachedSpecs.Hostname + " เรียบร้อยแล้ว", ToolTipIcon.Info);
            }
            catch (Exception ex)
            {
                SetStatus(true, false, "ส่งข้อมูลไม่สำเร็จ: " + ex.Message);
                _notifyIcon.ShowBalloonTip(4000, "เกิดข้อผิดพลาดในการส่งข้อมูล", ex.Message, ToolTipIcon.Warning);
            }
        }

        public void TriggerManualScan()
        {
            new Thread(() =>
            {
                _notifyIcon.ShowBalloonTip(2000, "กำลังดึงสเปกเครื่อง", "กำลังอ่านข้อมูลฮาร์ดแวร์และส่งเข้าสู่ระบบ...", ToolTipIcon.Info);
                ExecuteScanAndSubmit(0);
            }) { IsBackground = true }.Start();
        }

        private string HttpPostJson(string url, string jsonBody, int timeoutMs)
        {
            var req = (HttpWebRequest)WebRequest.Create(url);
            req.Method = "POST";
            req.ContentType = "application/json; charset=utf-8";
            req.Accept = "application/json";
            req.Timeout = timeoutMs;
            req.UserAgent = "THC-IT-Agent/2.5.5";

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
                Text = "THC Client Hardware Audit Agent v2.5.5 (Standalone)",
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
                list.Items.Add(new ListViewItem(new[] { "ซีเรียลนัมเบอร์ (Serial)", _cachedSpecs.SerialNumber }));
                list.Items.Add(new ListViewItem(new[] { "รหัสฮาร์ดแวร์ (HWID)", _cachedSpecs.HardwareId }));
                list.Items.Add(new ListViewItem(new[] { "หน่วยประมวลผล (CPU)", _cachedSpecs.CpuModel + " (" + _cachedSpecs.CpuCores + " Cores)" }));
                list.Items.Add(new ListViewItem(new[] { "หน่วยความจำ (RAM)", _cachedSpecs.RamCapacity + " GB (" + _cachedSpecs.RamType + ")" }));
                list.Items.Add(new ListViewItem(new[] { "พื้นที่จัดเก็บหลัก (Storage)", _cachedSpecs.StorageCapacity + " GB (" + _cachedSpecs.StorageType + ")" }));
                if (!string.IsNullOrEmpty(_cachedSpecs.StorageSecond))
                    list.Items.Add(new ListViewItem(new[] { "พื้นที่จัดเก็บเสริม (Secondary)", _cachedSpecs.StorageSecond }));
                list.Items.Add(new ListViewItem(new[] { "ระบบปฏิบัติการ (OS)", _cachedSpecs.OsName }));
                list.Items.Add(new ListViewItem(new[] { "การ์ดแสดงผล (GPU)", _cachedSpecs.GpuModel }));
                list.Items.Add(new ListViewItem(new[] { "IP Address / MAC", _cachedSpecs.IpAddress + " (" + _cachedSpecs.MacAddress + ")" }));
                list.Items.Add(new ListViewItem(new[] { "หน้าจอแสดงผล (Monitor)", _cachedSpecs.MonitorInfo }));
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
                Size = new Size(480, 280),
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
                Location = new Point(20, 20),
                AutoSize = true,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            form.Controls.Add(lbl);

            var txtUrl = new TextBox
            {
                Text = _config.ServerUrl,
                Location = new Point(20, 48),
                Size = new Size(420, 26),
                Font = new Font("Segoe UI", 10F)
            };
            form.Controls.Add(txtUrl);

            var btnMoph = new Button
            {
                Text = "🌐 MOPH Cloud (ค่าเริ่มต้น)",
                Location = new Point(20, 85),
                Size = new Size(170, 28),
                Font = new Font("Segoe UI", 8F)
            };
            btnMoph.Click += (s, e) => txtUrl.Text = "https://thchospital.moph.go.th/it-system";
            form.Controls.Add(btnMoph);

            var btnLan = new Button
            {
                Text = "🏢 รพ. LAN (192.168.2.89)",
                Location = new Point(200, 85),
                Size = new Size(160, 28),
                Font = new Font("Segoe UI", 8F)
            };
            btnLan.Click += (s, e) => txtUrl.Text = "http://192.168.2.89:8000";
            form.Controls.Add(btnLan);

            var btnTest = new Button
            {
                Text = "🔍 ทดสอบการเชื่อมต่อ",
                Location = new Point(20, 135),
                Size = new Size(150, 32),
                BackColor = Color.FromArgb(52, 152, 219),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            btnTest.Click += (s, e) =>
            {
                try
                {
                    string testUrl = txtUrl.Text.TrimEnd('/') + "/api/hardware-audit/agent-command";
                    string testResp = HttpPostJson(testUrl, "{\"hostname\":\"TEST\",\"mode\":\"ping\"}", 4000);
                    if (!string.IsNullOrEmpty(testResp))
                    {
                        MessageBox.Show("เชื่อมต่อเซิร์ฟเวอร์สำเร็จ! 🟢\nURL: " + txtUrl.Text, "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }
                    else
                    {
                        MessageBox.Show("ไม่สามารถเชื่อมต่อได้ (ไม่ได้รับข้อมูลตอบกลับ)", "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
                catch (Exception ex)
                {
                    MessageBox.Show("เชื่อมต่อไม่สำเร็จ 🔴\nข้อผิดพลาด: " + ex.Message, "ผลการทดสอบ", MessageBoxButtons.OK, MessageBoxIcon.Error);
                }
            };
            form.Controls.Add(btnTest);

            var btnSave = new Button
            {
                Text = "บันทึกการตั้งค่า",
                Location = new Point(230, 185),
                Size = new Size(110, 34),
                BackColor = Color.FromArgb(46, 204, 113),
                ForeColor = Color.White,
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F, FontStyle.Bold)
            };
            btnSave.Click += (s, e) =>
            {
                _config.ServerUrl = txtUrl.Text.Trim();
                _config.Save();
                form.Close();
                _notifyIcon.ShowBalloonTip(2000, "บันทึกการตั้งค่าแล้ว", "กำลังเชื่อมต่อกับเซิร์ฟเวอร์: " + _config.ServerUrl, ToolTipIcon.Info);
            };
            form.Controls.Add(btnSave);

            var btnCancel = new Button
            {
                Text = "ยกเลิก",
                Location = new Point(350, 185),
                Size = new Size(90, 34),
                BackColor = Color.FromArgb(240, 240, 240),
                FlatStyle = FlatStyle.Flat,
                Font = new Font("Segoe UI", 9F)
            };
            btnCancel.Click += (s, e) => form.Close();
            form.Controls.Add(btnCancel);

            form.ShowDialog();
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
