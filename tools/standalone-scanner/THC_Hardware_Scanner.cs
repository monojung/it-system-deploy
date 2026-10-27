using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Management;
using System.Net;
using System.Text;
using System.Threading;
using System.Windows.Forms;

namespace THC.HardwareAudit.Standalone
{
    public class HardwareSpecs
    {
        public string Hostname = "";
        public string Brand = "";
        public string Model = "";
        public string SerialNumber = "";
        public string HardwareId = "";
        public string DeviceTypeCode = "PC";
        public string CpuModel = "";
        public string CpuSpeed = "";
        public int RamCapacity = 8;
        public string RamType = "DDR4";
        public string RamBus = "";
        public string StorageCapacity = "";
        public string StorageType = "";
        public string StorageSecond = "";
        public string OsName = "";
        public string GpuModel = "";
        public string MonitorSize = "";
        public string IpAddress = "";
        public string MacAddress = "";
        public string DepartmentName = "";
        public int FiscalYear = 2568;
        public long? CommandId = null;

        public static int CalculateFiscalYear()
        {
            DateTime now = DateTime.Now;
            int year = now.Year;
            return now.Month >= 10 ? year + 544 : year + 543;
        }

        public string ToJson()
        {
            StringBuilder sb = new StringBuilder();
            sb.Append("{");
            sb.AppendFormat("\"hostname\":\"{0}\",", EscapeJson(Hostname));
            sb.AppendFormat("\"brand\":\"{0}\",", EscapeJson(Brand));
            sb.AppendFormat("\"model\":\"{0}\",", EscapeJson(Model));
            sb.AppendFormat("\"serial_number\":\"{0}\",", EscapeJson(SerialNumber));
            sb.AppendFormat("\"hardware_id\":\"{0}\",", EscapeJson(HardwareId));
            sb.AppendFormat("\"device_type_code\":\"{0}\",", EscapeJson(DeviceTypeCode));
            sb.AppendFormat("\"cpu_model\":\"{0}\",", EscapeJson(CpuModel));
            sb.AppendFormat("\"cpu_speed\":\"{0}\",", EscapeJson(CpuSpeed));
            sb.AppendFormat("\"ram_capacity\":{0},", RamCapacity);
            sb.AppendFormat("\"ram_type\":\"{0}\",", EscapeJson(RamType));
            sb.AppendFormat("\"ram_bus\":\"{0}\",", EscapeJson(RamBus));
            sb.AppendFormat("\"storage_capacity\":\"{0}\",", EscapeJson(StorageCapacity));
            sb.AppendFormat("\"storage_type\":\"{0}\",", EscapeJson(StorageType));
            sb.AppendFormat("\"storage_second\":\"{0}\",", EscapeJson(StorageSecond));
            sb.AppendFormat("\"os_name\":\"{0}\",", EscapeJson(OsName));
            sb.AppendFormat("\"gpu_model\":\"{0}\",", EscapeJson(GpuModel));
            sb.AppendFormat("\"monitor_size\":\"{0}\",", EscapeJson(MonitorSize));
            sb.AppendFormat("\"ip_address\":\"{0}\",", EscapeJson(IpAddress));
            sb.AppendFormat("\"mac_address\":\"{0}\",", EscapeJson(MacAddress));
            sb.AppendFormat("\"department_name\":\"{0}\",", EscapeJson(DepartmentName));
            sb.AppendFormat("\"fiscal_year\":{0}", FiscalYear);
            if (CommandId.HasValue && CommandId.Value > 0)
            {
                sb.AppendFormat(",\"command_id\":{0}", CommandId.Value);
            }
            sb.Append("}");
            return sb.ToString();
        }

        private static string EscapeJson(string s)
        {
            if (string.IsNullOrEmpty(s)) return "";
            return s.Replace("\\", "\\\\")
                    .Replace("\"", "\\\"")
                    .Replace("\r", "\\r")
                    .Replace("\n", "\\n")
                    .Replace("\t", "\\t");
        }
    }

    public class AppConfig
    {
        public string ServerUrl = "http://127.0.0.1:8000/api/hardware-audits";
        public string ApiToken = "thc_audit_secure_token";
        public int PollIntervalSec = 60;

        public static string GetConfigPath()
        {
            string appDir = AppDomain.CurrentDomain.BaseDirectory;
            string localConfig = Path.Combine(appDir, "config.json");
            if (File.Exists(localConfig)) return localConfig;

            string progData = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData), "THC_Hardware_Scanner");
            if (!Directory.Exists(progData)) Directory.CreateDirectory(progData);
            return Path.Combine(progData, "config.json");
        }

        public static AppConfig Load()
        {
            AppConfig cfg = new AppConfig();
            try
            {
                string path = GetConfigPath();
                if (File.Exists(path))
                {
                    string txt = File.ReadAllText(path, Encoding.UTF8);
                    cfg.ServerUrl = ExtractJsonString(txt, "server_url", cfg.ServerUrl);
                    cfg.ApiToken = ExtractJsonString(txt, "api_token", cfg.ApiToken);
                    cfg.PollIntervalSec = ExtractJsonInt(txt, "poll_interval_sec", cfg.PollIntervalSec);
                }
            }
            catch { }
            return cfg;
        }

        public void Save()
        {
            try
            {
                string path = GetConfigPath();
                string dir = Path.GetDirectoryName(path);
                if (!Directory.Exists(dir)) Directory.CreateDirectory(dir);

                StringBuilder sb = new StringBuilder();
                sb.AppendLine("{");
                sb.AppendFormat("  \"server_url\": \"{0}\",\r\n", ServerUrl);
                sb.AppendFormat("  \"api_token\": \"{0}\",\r\n", ApiToken);
                sb.AppendFormat("  \"poll_interval_sec\": {0},\r\n", PollIntervalSec);
                sb.AppendFormat("  \"updated_at\": \"{0}\"\r\n", DateTime.Now.ToString("s"));
                sb.AppendLine("}");

                File.WriteAllText(path, sb.ToString(), Encoding.UTF8);
            }
            catch { }
        }

        private static string ExtractJsonString(string json, string key, string defVal)
        {
            try
            {
                string needle = "\"" + key + "\"";
                int idx = json.IndexOf(needle, StringComparison.OrdinalIgnoreCase);
                if (idx < 0) return defVal;
                int colon = json.IndexOf(':', idx);
                if (colon < 0) return defVal;
                int q1 = json.IndexOf('\"', colon);
                if (q1 < 0) return defVal;
                int q2 = json.IndexOf('\"', q1 + 1);
                if (q2 < 0) return defVal;
                return json.Substring(q1 + 1, q2 - q1 - 1).Trim();
            }
            catch { return defVal; }
        }

        private static int ExtractJsonInt(string json, string key, int defVal)
        {
            try
            {
                string needle = "\"" + key + "\"";
                int idx = json.IndexOf(needle, StringComparison.OrdinalIgnoreCase);
                if (idx < 0) return defVal;
                int colon = json.IndexOf(':', idx);
                if (colon < 0) return defVal;
                int end = json.IndexOfAny(new char[] { ',', '}', '\r', '\n' }, colon);
                if (end < 0) end = json.Length;
                string val = json.Substring(colon + 1, end - colon - 1).Trim().Replace("\"", "");
                int res;
                return int.TryParse(val, out res) ? res : defVal;
            }
            catch { return defVal; }
        }
    }

    public static class Logger
    {
        private static readonly object _lock = new object();

        public static void Log(string msg)
        {
            string line = string.Format("[{0}] {1}", DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss"), msg);
            Console.WriteLine(line);

            try
            {
                lock (_lock)
                {
                    string dir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.CommonApplicationData), "THC_Hardware_Scanner", "logs");
                    if (!Directory.Exists(dir)) Directory.CreateDirectory(dir);
                    string logFile = Path.Combine(dir, "scanner.log");
                    File.AppendAllText(logFile, line + "\r\n", Encoding.UTF8);
                }
            }
            catch { }
        }
    }

    public static class HardwareScanner
    {
        public static HardwareSpecs Scan()
        {
            HardwareSpecs specs = new HardwareSpecs();
            specs.Hostname = Environment.MachineName;
            specs.FiscalYear = HardwareSpecs.CalculateFiscalYear();

            // 1. Computer System (Brand, Model)
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Manufacturer, Model FROM Win32_ComputerSystem"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        specs.Brand = GetSafeString(obj["Manufacturer"]);
                        specs.Model = GetSafeString(obj["Model"]);
                        break;
                    }
                }
            }
            catch { }

            // 2. BIOS (Serial Number)
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT SerialNumber FROM Win32_Bios"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        specs.SerialNumber = GetSafeString(obj["SerialNumber"]);
                        break;
                    }
                }
            }
            catch { }

            // 3. Motherboard UUID
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT UUID FROM Win32_ComputerSystemProduct"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        specs.HardwareId = GetSafeString(obj["UUID"]);
                        break;
                    }
                }
            }
            catch { }

            // 4. CPU Info
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Name, NumberOfCores, NumberOfLogicalProcessors, MaxClockSpeed FROM Win32_Processor"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        specs.CpuModel = GetSafeString(obj["Name"]).Trim();
                        int cores = 0;
                        int threads = 0;
                        int.TryParse(GetSafeString(obj["NumberOfCores"]), out cores);
                        int.TryParse(GetSafeString(obj["NumberOfLogicalProcessors"]), out threads);
                        int speedMhz = 0;
                        int.TryParse(GetSafeString(obj["MaxClockSpeed"]), out speedMhz);
                        double speedGhz = Math.Round((double)speedMhz / 1000.0, 2);

                        if (cores > 0)
                        {
                            specs.CpuSpeed = string.Format("{0} GHz ({1} Cores / {2} Threads)", speedGhz, cores, threads > 0 ? threads : cores);
                        }
                        else
                        {
                            specs.CpuSpeed = string.Format("{0} GHz", speedGhz);
                        }
                        break;
                    }
                }
            }
            catch { }

            // 5. RAM Info
            try
            {
                long totalBytes = 0;
                string ramSpeed = "";
                string ramType = "DDR4";

                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Capacity, Speed, MemoryType, SMBIOSMemoryType FROM Win32_PhysicalMemory"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        long cap = 0;
                        long.TryParse(GetSafeString(obj["Capacity"]), out cap);
                        totalBytes += cap;

                        if (string.IsNullOrEmpty(ramSpeed))
                        {
                            ramSpeed = GetSafeString(obj["Speed"]);
                        }

                        int smbiosType = 0;
                        int.TryParse(GetSafeString(obj["SMBIOSMemoryType"]), out smbiosType);
                        if (smbiosType == 26) ramType = "DDR4";
                        else if (smbiosType == 34) ramType = "DDR5";
                        else if (smbiosType == 24) ramType = "DDR3";
                    }
                }

                if (totalBytes > 0)
                {
                    specs.RamCapacity = (int)Math.Round((double)totalBytes / (1024.0 * 1024.0 * 1024.0));
                }
                specs.RamType = ramType;
                if (!string.IsNullOrEmpty(ramSpeed))
                {
                    specs.RamBus = ramSpeed + " MHz";
                }
            }
            catch { }

            // 6. Disks
            try
            {
                List<string> disks = new List<string>();
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Model, Size, MediaType, InterfaceType FROM Win32_DiskDrive"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        string model = GetSafeString(obj["Model"]).Trim();
                        long bytes = 0;
                        long.TryParse(GetSafeString(obj["Size"]), out bytes);
                        if (bytes <= 0) continue;

                        int gb = (int)Math.Round((double)bytes / (1024.0 * 1024.0 * 1024.0));
                        string sizeStr = gb >= 1000 ? string.Format("{0} TB", Math.Round((double)gb / 1024.0, 1)) : string.Format("{0} GB", gb);
                        string iface = GetSafeString(obj["InterfaceType"]);
                        string media = GetSafeString(obj["MediaType"]);

                        string typeDesc = "SSD";
                        if (model.ToUpper().Contains("NVME") || iface.ToUpper().Contains("SCSI") || iface.ToUpper().Contains("NVME"))
                        {
                            typeDesc = "SSD NVMe M.2";
                        }
                        else if (model.ToUpper().Contains("SSD") || media.ToUpper().Contains("SSD"))
                        {
                            typeDesc = "SSD SATA";
                        }
                        else if (media.ToUpper().Contains("FIXED") || model.ToUpper().Contains("HDD"))
                        {
                            typeDesc = "HDD SATA";
                        }

                        disks.Add(string.Format("{0} ({1})", sizeStr, typeDesc));
                    }
                }

                if (disks.Count > 0)
                {
                    specs.StorageCapacity = disks[0];
                    specs.StorageType = disks[0].Contains("NVMe") ? "SSD NVMe M.2" : (disks[0].Contains("SSD") ? "SSD SATA" : "HDD");
                }
                if (disks.Count > 1)
                {
                    specs.StorageSecond = disks[1];
                }
            }
            catch { }

            // 7. OS
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Caption, OSArchitecture FROM Win32_OperatingSystem"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        string cap = GetSafeString(obj["Caption"]);
                        string arch = GetSafeString(obj["OSArchitecture"]);
                        specs.OsName = string.Format("{0} {1}", cap.Replace("Microsoft ", "").Trim(), arch).Trim();
                        break;
                    }
                }
            }
            catch { }

            // 8. GPU
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT Name FROM Win32_VideoController"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        specs.GpuModel = GetSafeString(obj["Name"]).Trim();
                        break;
                    }
                }
            }
            catch { }

            // 9. Network IP & MAC
            try
            {
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT IPAddress, MACAddress FROM Win32_NetworkAdapterConfiguration WHERE IPEnabled = True"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        string[] ips = obj["IPAddress"] as string[];
                        if (ips != null && ips.Length > 0)
                        {
                            specs.IpAddress = ips[0];
                        }
                        specs.MacAddress = GetSafeString(obj["MACAddress"]);
                        if (!string.IsNullOrEmpty(specs.IpAddress)) break;
                    }
                }
            }
            catch { }

            // 10. Display Resolution
            try
            {
                Rectangle bounds = Screen.PrimaryScreen.Bounds;
                specs.MonitorSize = string.Format("{0} x {1} (Primary Display)", bounds.Width, bounds.Height);
            }
            catch { }

            // 11. Device Type Detection (PC vs NB vs AIO)
            try
            {
                bool hasBattery = false;
                using (ManagementObjectSearcher searcher = new ManagementObjectSearcher("SELECT EstimatedChargeRemaining FROM Win32_Battery"))
                {
                    foreach (ManagementObject obj in searcher.Get())
                    {
                        hasBattery = true;
                        break;
                    }
                }

                if (hasBattery)
                {
                    specs.DeviceTypeCode = "NB";
                }
                else
                {
                    string m = (specs.Model + " " + specs.Brand).ToUpper();
                    if (m.Contains("ALL-IN-ONE") || m.Contains("AIO") || m.Contains("PROONE"))
                    {
                        specs.DeviceTypeCode = "AIO";
                    }
                    else
                    {
                        specs.DeviceTypeCode = "PC";
                    }
                }
            }
            catch { }

            return specs;
        }

        private static string GetSafeString(object val)
        {
            return val != null ? val.ToString().Trim() : "";
        }

        public static string HttpPost(string url, string jsonBody, string apiToken, out int statusCode)
        {
            statusCode = 0;
            try
            {
                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(url);
                req.Method = "POST";
                req.ContentType = "application/json; charset=utf-8";
                req.Accept = "application/json";
                req.Timeout = 20000;

                if (!string.IsNullOrEmpty(apiToken))
                {
                    req.Headers.Add("X-API-Token", apiToken);
                    req.Headers.Add("Authorization", "Bearer " + apiToken);
                }

                byte[] bytes = Encoding.UTF8.GetBytes(jsonBody);
                req.ContentLength = bytes.Length;

                using (Stream stream = req.GetRequestStream())
                {
                    stream.Write(bytes, 0, bytes.Length);
                }

                using (HttpWebResponse resp = (HttpWebResponse)req.GetResponse())
                {
                    statusCode = (int)resp.StatusCode;
                    using (StreamReader r = new StreamReader(resp.GetResponseStream(), Encoding.UTF8))
                    {
                        return r.ReadToEnd();
                    }
                }
            }
            catch (WebException wex)
            {
                if (wex.Response != null)
                {
                    HttpWebResponse resp = (HttpWebResponse)wex.Response;
                    statusCode = (int)resp.StatusCode;
                    using (StreamReader r = new StreamReader(resp.GetResponseStream(), Encoding.UTF8))
                    {
                        return r.ReadToEnd();
                    }
                }
                throw;
            }
        }
    }

    public static class ClientDaemon
    {
        public static void RunLoop(AppConfig config)
        {
            Logger.Log("THC Hardware Scanner Service started (Zero Inbound Port Architecture).");
            Logger.Log(string.Format("Server URL: {0}, Poll Interval: {1}s", config.ServerUrl, config.PollIntervalSec));

            string baseUrl = config.ServerUrl;
            string pollUrl = baseUrl.TrimEnd('/');
            if (pollUrl.EndsWith("/api/hardware-audits"))
            {
                pollUrl += "/command-poll";
            }
            else if (!pollUrl.Contains("command"))
            {
                pollUrl += "/api/hardware-audit/agent-command";
            }

            string submitUrl = baseUrl;
            if (submitUrl.EndsWith("/command-poll"))
            {
                submitUrl = submitUrl.Replace("/command-poll", "");
            }

            HardwareSpecs basicIdentity = HardwareScanner.Scan();

            while (true)
            {
                try
                {
                    PollServerOnce(pollUrl, submitUrl, config.ApiToken, basicIdentity);
                }
                catch (Exception ex)
                {
                    Logger.Log("Poll error: " + ex.Message);
                }

                Thread.Sleep(Math.Max(15, config.PollIntervalSec) * 1000);
            }
        }

        private static void PollServerOnce(string pollUrl, string submitUrl, string apiToken, HardwareSpecs identity)
        {
            string heartbeatJson = string.Format(
                "{{\"hostname\":\"{0}\",\"hardware_id\":\"{1}\",\"mac_address\":\"{2}\",\"client_version\":\"3.0.0-SCANNER\",\"mode\":\"service\"}}",
                identity.Hostname, identity.HardwareId, identity.MacAddress
            );

            int code;
            string resp = HardwareScanner.HttpPost(pollUrl, heartbeatJson, apiToken, out code);

            if (code >= 200 && code < 300)
            {
                if (resp.Contains("\"has_command\":true") || resp.Contains("\"has_command\": true"))
                {
                    long cmdId = ExtractId(resp, "command_id");
                    string cmdName = ExtractString(resp, "command");

                    Logger.Log(string.Format("Received Admin Command #{0}: {1}", cmdId, cmdName));

                    if (cmdName.ToLower() == "scan")
                    {
                        Logger.Log("Admin requested hardware scan. Executing WMI telemetry scan...");
                        HardwareSpecs freshSpecs = HardwareScanner.Scan();
                        freshSpecs.CommandId = cmdId;

                        string jsonPayload = freshSpecs.ToJson();
                        int submitCode;
                        string submitResp = HardwareScanner.HttpPost(submitUrl, jsonPayload, apiToken, out submitCode);

                        Logger.Log(string.Format("Telemetry submitted to API. HTTP {0}: {1}", submitCode, submitResp));

                        // Acknowledge command complete
                        try
                        {
                            string ackUrl = pollUrl.Replace("command-poll", "command-complete/" + cmdId)
                                                   .Replace("agent-command", "agent-command/" + cmdId + "/complete");
                            int ackCode;
                            HardwareScanner.HttpPost(ackUrl, "{\"summary\":\"Hardware audit recorded via Admin Pull\"}", apiToken, out ackCode);
                            Logger.Log(string.Format("Command #{0} marked as completed on server.", cmdId));
                        }
                        catch { }
                    }
                    else if (cmdName.ToLower() == "uninstall")
                    {
                        Logger.Log("Admin requested uninstall. Self-uninstalling...");
                        Installer.UninstallService();
                        Environment.Exit(0);
                    }
                }
            }
        }

        private static long ExtractId(string json, string key)
        {
            try
            {
                string needle = "\"" + key + "\"";
                int idx = json.IndexOf(needle, StringComparison.OrdinalIgnoreCase);
                if (idx < 0) return 0;
                int colon = json.IndexOf(':', idx);
                if (colon < 0) return 0;
                int end = json.IndexOfAny(new char[] { ',', '}', '\r', '\n' }, colon);
                if (end < 0) end = json.Length;
                string val = json.Substring(colon + 1, end - colon - 1).Trim().Replace("\"", "");
                long res;
                return long.TryParse(val, out res) ? res : 0;
            }
            catch { return 0; }
        }

        private static string ExtractString(string json, string key)
        {
            try
            {
                string needle = "\"" + key + "\"";
                int idx = json.IndexOf(needle, StringComparison.OrdinalIgnoreCase);
                if (idx < 0) return "";
                int colon = json.IndexOf(':', idx);
                if (colon < 0) return "";
                int q1 = json.IndexOf('\"', colon);
                if (q1 < 0) return "";
                int q2 = json.IndexOf('\"', q1 + 1);
                if (q2 < 0) return "";
                return json.Substring(q1 + 1, q2 - q1 - 1).Trim();
            }
            catch { return ""; }
        }
    }

    public static class Installer
    {
        public static bool InstallService(string targetServerUrl = "")
        {
            try
            {
                string installDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.ProgramFiles), "THC_Hardware_Scanner");
                if (!Directory.Exists(installDir)) Directory.CreateDirectory(installDir);

                string currentExe = Process.GetCurrentProcess().MainModule.FileName;
                string targetExe = Path.Combine(installDir, "THC_Hardware_Scanner.exe");

                if (!string.Equals(currentExe, targetExe, StringComparison.OrdinalIgnoreCase))
                {
                    File.Copy(currentExe, targetExe, true);
                }

                AppConfig cfg = AppConfig.Load();
                if (!string.IsNullOrEmpty(targetServerUrl))
                {
                    cfg.ServerUrl = targetServerUrl;
                }
                cfg.Save();

                // Register Windows Scheduled Task (Runs on system boot/logon)
                string taskName = "THC_Hardware_Scanner_Task";
                string cmdArgs = string.Format("/create /tn \"{0}\" /tr \"\\\"{1}\\\" /service\" /sc onlogon /rl highest /f", taskName, targetExe);

                ProcessStartInfo psi = new ProcessStartInfo("schtasks.exe", cmdArgs);
                psi.CreateNoWindow = true;
                psi.UseShellExecute = false;
                Process p = Process.Start(psi);
                p.WaitForExit();

                // Run task immediately
                ProcessStartInfo runPsi = new ProcessStartInfo("schtasks.exe", string.Format("/run /tn \"{0}\"", taskName));
                runPsi.CreateNoWindow = true;
                runPsi.UseShellExecute = false;
                Process.Start(runPsi);

                Logger.Log("THC Hardware Scanner installed successfully to: " + installDir);
                return true;
            }
            catch (Exception ex)
            {
                Logger.Log("Installation failed: " + ex.Message);
                return false;
            }
        }

        public static bool UninstallService()
        {
            try
            {
                string taskName = "THC_Hardware_Scanner_Task";
                ProcessStartInfo psi = new ProcessStartInfo("schtasks.exe", string.Format("/delete /tn \"{0}\" /f", taskName));
                psi.CreateNoWindow = true;
                psi.UseShellExecute = false;
                Process p = Process.Start(psi);
                p.WaitForExit();

                Logger.Log("THC Hardware Scanner service uninstalled.");
                return true;
            }
            catch (Exception ex)
            {
                Logger.Log("Uninstall failed: " + ex.Message);
                return false;
            }
        }
    }

    public class MainForm : Form
    {
        private HardwareSpecs currentSpecs;
        private AppConfig config;
        private TextBox txtServerUrl;
        private TextBox txtHostname;
        private TextBox txtBrand;
        private TextBox txtModel;
        private TextBox txtSerial;
        private TextBox txtCpu;
        private TextBox txtRam;
        private TextBox txtStorage;
        private TextBox txtOs;
        private TextBox txtGpu;
        private TextBox txtIp;
        private ComboBox cboDepartment;
        private ComboBox cboDeviceType;
        private Button btnScan;
        private Button btnSend;
        private Button btnInstall;
        private Button btnTestConn;
        private RichTextBox rtbConsole;
        private Label lblStatus;

        public MainForm()
        {
            config = AppConfig.Load();
            InitializeComponent();
            PerformScan();
        }

        private void InitializeComponent()
        {
            this.Text = "THC Hardware Audit - Standalone Client (Cybersecurity Standard v3.0)";
            this.Size = new Size(840, 730);
            this.MinimumSize = new Size(800, 700);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.Font = new Font("Segoe UI", 9.25f, FontStyle.Regular);
            this.BackColor = Color.FromArgb(248, 250, 252);
            this.Icon = SystemIcons.Application;

            // 1. Header Banner Panel
            Panel headerPanel = new Panel();
            headerPanel.Dock = DockStyle.Top;
            headerPanel.Height = 74;
            headerPanel.BackColor = Color.FromArgb(15, 23, 42);
            headerPanel.Padding = new Padding(20, 12, 20, 12);

            Label lblTitle = new Label();
            lblTitle.Text = "THC Hardware Audit — Client Terminal";
            lblTitle.ForeColor = Color.White;
            lblTitle.Font = new Font("Segoe UI", 13.5f, FontStyle.Bold);
            lblTitle.AutoSize = true;
            lblTitle.Location = new Point(18, 12);

            Label lblSub = new Label();
            lblSub.Text = "โปรแกรมเชื่อมต่อและตรวจนับสเปกตามมาตรฐาน Cybersecurity (Zero Inbound Port • Admin On-Demand Pull)";
            lblSub.ForeColor = Color.FromArgb(45, 212, 191);
            lblSub.Font = new Font("Segoe UI", 9f, FontStyle.Regular);
            lblSub.AutoSize = true;
            lblSub.Location = new Point(20, 42);

            headerPanel.Controls.Add(lblTitle);
            headerPanel.Controls.Add(lblSub);

            // 2. Server Settings Panel
            Panel serverPanel = new Panel();
            serverPanel.Dock = DockStyle.Top;
            serverPanel.Height = 52;
            serverPanel.BackColor = Color.FromArgb(241, 245, 249);
            serverPanel.Padding = new Padding(18, 10, 18, 10);

            Label lblUrl = new Label();
            lblUrl.Text = "URL เซิร์ฟเวอร์ API:";
            lblUrl.AutoSize = true;
            lblUrl.Location = new Point(18, 16);
            lblUrl.Font = new Font("Segoe UI", 9f, FontStyle.Bold);

            txtServerUrl = new TextBox();
            txtServerUrl.Location = new Point(146, 13);
            txtServerUrl.Size = new Size(530, 26);
            txtServerUrl.Text = config.ServerUrl;
            txtServerUrl.Font = new Font("Consolas", 9.5f, FontStyle.Regular);

            btnTestConn = new Button();
            btnTestConn.Text = "ทดสอบ API";
            btnTestConn.Location = new Point(688, 11);
            btnTestConn.Size = new Size(110, 30);
            btnTestConn.BackColor = Color.White;
            btnTestConn.FlatStyle = FlatStyle.Flat;
            btnTestConn.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnTestConn.Cursor = Cursors.Hand;
            btnTestConn.Click += (s, e) => TestConnection();

            serverPanel.Controls.Add(lblUrl);
            serverPanel.Controls.Add(txtServerUrl);
            serverPanel.Controls.Add(btnTestConn);

            // 3. Middle Specs Panel
            GroupBox grpSpecs = new GroupBox();
            grpSpecs.Text = " สเปกคอมพิวเตอร์ที่ตรวจจับได้จาก WMI (Read-Only Telemetry) ";
            grpSpecs.Font = new Font("Segoe UI", 9.5f, FontStyle.Bold);
            grpSpecs.ForeColor = Color.FromArgb(15, 23, 42);
            grpSpecs.Location = new Point(18, 134);
            grpSpecs.Size = new Size(788, 280);
            grpSpecs.Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right;

            int leftColLabel = 16, leftColVal = 135;
            int rightColLabel = 405, rightColVal = 520;
            int rowH = 34, startY = 28;

            AddLabel(grpSpecs, "ชื่อเครื่อง (Host):", leftColLabel, startY);
            txtHostname = AddTextBox(grpSpecs, leftColVal, startY - 2, 245);

            AddLabel(grpSpecs, "แผนก / หน่วยงาน:", rightColLabel, startY);
            cboDepartment = new ComboBox();
            cboDepartment.Location = new Point(rightColVal, startY - 2);
            cboDepartment.Size = new Size(245, 26);
            cboDepartment.Font = new Font("Segoe UI", 9f);
            cboDepartment.Items.AddRange(new string[] {
                "ผู้ป่วยนอก (OPD)", "ผู้ป่วยใน (IPD)", "อุบัติเหตุและฉุกเฉิน (ER)", "ทันตกรรม",
                "เภสัชกรรม", "เทคนิคการแพทย์ (LAB)", "รังสีวิทยา (X-Ray)", "ห้องผ่าตัด (OR)",
                "เวชระเบียน", "การเงินและบัญชี", "ศูนย์คอมพิวเตอร์และสารสนเทศ", "บริหารทั่วไป/ธุรการ",
                "ส่งเสริมสุขภาพและป้องกันโรค", "โภชนาการ", "ซ่อมบำรุง", "ไม่ระบุแผนก"
            });
            cboDepartment.SelectedIndex = 0;
            grpSpecs.Controls.Add(cboDepartment);

            AddLabel(grpSpecs, "ยี่ห้อ (Brand):", leftColLabel, startY + rowH);
            txtBrand = AddTextBox(grpSpecs, leftColVal, startY + rowH - 2, 245);

            AddLabel(grpSpecs, "รุ่นตัวเครื่อง (Model):", rightColLabel, startY + rowH);
            txtModel = AddTextBox(grpSpecs, rightColVal, startY + rowH - 2, 245);

            AddLabel(grpSpecs, "ซีเรียล (Serial):", leftColLabel, startY + rowH * 2);
            txtSerial = AddTextBox(grpSpecs, leftColVal, startY + rowH * 2 - 2, 245);

            AddLabel(grpSpecs, "ประเภทเครื่อง:", rightColLabel, startY + rowH * 2);
            cboDeviceType = new ComboBox();
            cboDeviceType.Location = new Point(rightColVal, startY + rowH * 2 - 2);
            cboDeviceType.Size = new Size(245, 26);
            cboDeviceType.Font = new Font("Segoe UI", 9f);
            cboDeviceType.DropDownStyle = ComboBoxStyle.DropDownList;
            cboDeviceType.Items.Add("PC - คอมพิวเตอร์ตั้งโต๊ะ");
            cboDeviceType.Items.Add("NB - คอมพิวเตอร์พกพา (โน้ตบุ๊ก)");
            cboDeviceType.Items.Add("AIO - ออลอินวัน (All-in-One)");
            cboDeviceType.SelectedIndex = 0;
            grpSpecs.Controls.Add(cboDeviceType);

            AddLabel(grpSpecs, "ซีพียู (CPU):", leftColLabel, startY + rowH * 3);
            txtCpu = AddTextBox(grpSpecs, leftColVal, startY + rowH * 3 - 2, 245);

            AddLabel(grpSpecs, "แรม (RAM):", rightColLabel, startY + rowH * 3);
            txtRam = AddTextBox(grpSpecs, rightColVal, startY + rowH * 3 - 2, 245);

            AddLabel(grpSpecs, "ฮาร์ดดิสก์ (Disk):", leftColLabel, startY + rowH * 4);
            txtStorage = AddTextBox(grpSpecs, leftColVal, startY + rowH * 4 - 2, 245);

            AddLabel(grpSpecs, "ระบบปฏิบัติการ:", rightColLabel, startY + rowH * 4);
            txtOs = AddTextBox(grpSpecs, rightColVal, startY + rowH * 4 - 2, 245);

            AddLabel(grpSpecs, "การ์ดจอ (GPU):", leftColLabel, startY + rowH * 5);
            txtGpu = AddTextBox(grpSpecs, leftColVal, startY + rowH * 5 - 2, 245);

            AddLabel(grpSpecs, "ไอพีแอดเดรส (IP):", rightColLabel, startY + rowH * 5);
            txtIp = AddTextBox(grpSpecs, rightColVal, startY + rowH * 5 - 2, 245);

            // 4. Action Buttons Bar
            Panel actionPanel = new Panel();
            actionPanel.Location = new Point(18, 424);
            actionPanel.Size = new Size(788, 46);
            actionPanel.Anchor = AnchorStyles.Top | AnchorStyles.Left | AnchorStyles.Right;

            btnSend = new Button();
            btnSend.Text = "🚀 ส่งสเปกเข้า API ทันที (Manual Push)";
            btnSend.Size = new Size(300, 42);
            btnSend.Location = new Point(0, 0);
            btnSend.BackColor = Color.FromArgb(13, 148, 136);
            btnSend.ForeColor = Color.White;
            btnSend.Font = new Font("Segoe UI", 10f, FontStyle.Bold);
            btnSend.FlatStyle = FlatStyle.Flat;
            btnSend.FlatAppearance.BorderSize = 0;
            btnSend.Cursor = Cursors.Hand;
            btnSend.Click += (s, e) => SubmitSpecs();

            btnScan = new Button();
            btnScan.Text = "🔄 สแกนใหม่";
            btnScan.Size = new Size(110, 42);
            btnScan.Location = new Point(310, 0);
            btnScan.BackColor = Color.White;
            btnScan.ForeColor = Color.FromArgb(15, 23, 42);
            btnScan.FlatStyle = FlatStyle.Flat;
            btnScan.FlatAppearance.BorderColor = Color.FromArgb(203, 213, 225);
            btnScan.Cursor = Cursors.Hand;
            btnScan.Click += (s, e) => PerformScan();

            btnInstall = new Button();
            btnInstall.Text = "⚙️ ติดตั้ง Background Standby";
            btnInstall.Size = new Size(240, 42);
            btnInstall.Location = new Point(430, 0);
            btnInstall.BackColor = Color.White;
            btnInstall.ForeColor = Color.FromArgb(2, 132, 199);
            btnInstall.FlatStyle = FlatStyle.Flat;
            btnInstall.FlatAppearance.BorderColor = Color.FromArgb(186, 230, 253);
            btnInstall.Cursor = Cursors.Hand;
            btnInstall.Click += (s, e) => SetupBackgroundService();

            Button btnExit = new Button();
            btnExit.Text = "ปิด";
            btnExit.Size = new Size(100, 42);
            btnExit.Location = new Point(680, 0);
            btnExit.BackColor = Color.White;
            btnExit.ForeColor = Color.FromArgb(239, 68, 68);
            btnExit.FlatStyle = FlatStyle.Flat;
            btnExit.FlatAppearance.BorderColor = Color.FromArgb(254, 202, 202);
            btnExit.Cursor = Cursors.Hand;
            btnExit.Click += (s, e) => this.Close();

            actionPanel.Controls.Add(btnSend);
            actionPanel.Controls.Add(btnScan);
            actionPanel.Controls.Add(btnInstall);
            actionPanel.Controls.Add(btnExit);

            // 5. Console & Status Log
            GroupBox grpLog = new GroupBox();
            grpLog.Text = " บันทึกการทำงานและความปลอดภัย (Security & Audit Console) ";
            grpLog.Font = new Font("Segoe UI", 9f, FontStyle.Bold);
            grpLog.ForeColor = Color.FromArgb(71, 85, 105);
            grpLog.Location = new Point(18, 480);
            grpLog.Size = new Size(788, 160);
            grpLog.Anchor = AnchorStyles.Top | AnchorStyles.Bottom | AnchorStyles.Left | AnchorStyles.Right;

            rtbConsole = new RichTextBox();
            rtbConsole.Dock = DockStyle.Fill;
            rtbConsole.BackColor = Color.FromArgb(9, 13, 22);
            rtbConsole.ForeColor = Color.FromArgb(56, 189, 248);
            rtbConsole.Font = new Font("Consolas", 9f);
            rtbConsole.ReadOnly = true;
            rtbConsole.BorderStyle = BorderStyle.None;
            grpLog.Controls.Add(rtbConsole);

            // 6. Status Strip
            lblStatus = new Label();
            lblStatus.Text = " สถานะ: พร้อมใช้งาน • มาตรฐานความปลอดภัย Cybersecurity (Zero Inbound Port)";
            lblStatus.Dock = DockStyle.Bottom;
            lblStatus.Height = 24;
            lblStatus.BackColor = Color.FromArgb(226, 232, 240);
            lblStatus.ForeColor = Color.FromArgb(51, 65, 85);
            lblStatus.Font = new Font("Segoe UI", 8.5f);

            this.Controls.Add(grpLog);
            this.Controls.Add(actionPanel);
            this.Controls.Add(grpSpecs);
            this.Controls.Add(serverPanel);
            this.Controls.Add(headerPanel);
            this.Controls.Add(lblStatus);
        }

        private void AddLabel(GroupBox parent, string text, int x, int y)
        {
            Label lbl = new Label();
            lbl.Text = text;
            lbl.Location = new Point(x, y);
            lbl.AutoSize = true;
            lbl.Font = new Font("Segoe UI", 9f, FontStyle.Regular);
            lbl.ForeColor = Color.FromArgb(71, 85, 105);
            parent.Controls.Add(lbl);
        }

        private TextBox AddTextBox(GroupBox parent, int x, int y, int w)
        {
            TextBox txt = new TextBox();
            txt.Location = new Point(x, y);
            txt.Size = new Size(w, 24);
            txt.Font = new Font("Segoe UI", 9f);
            txt.BackColor = Color.White;
            parent.Controls.Add(txt);
            return txt;
        }

        private void PerformScan()
        {
            lblStatus.Text = " กำลังสแกนสเปกฮาร์ดแวร์ผ่าน WMI...";
            Application.DoEvents();

            try
            {
                currentSpecs = HardwareScanner.Scan();

                txtHostname.Text = currentSpecs.Hostname;
                txtBrand.Text = currentSpecs.Brand;
                txtModel.Text = currentSpecs.Model;
                txtSerial.Text = currentSpecs.SerialNumber;
                txtCpu.Text = currentSpecs.CpuModel;
                txtRam.Text = string.Format("{0} GB ({1} {2})", currentSpecs.RamCapacity, currentSpecs.RamType, currentSpecs.RamBus).Trim();
                txtStorage.Text = currentSpecs.StorageCapacity;
                txtOs.Text = currentSpecs.OsName;
                txtGpu.Text = currentSpecs.GpuModel;
                txtIp.Text = currentSpecs.IpAddress;

                if (currentSpecs.DeviceTypeCode == "NB") cboDeviceType.SelectedIndex = 1;
                else if (currentSpecs.DeviceTypeCode == "AIO") cboDeviceType.SelectedIndex = 2;
                else cboDeviceType.SelectedIndex = 0;

                Log(string.Format("สแกน WMI สำเร็จ: {0} ({1})", currentSpecs.Hostname, currentSpecs.CpuModel));
                lblStatus.Text = string.Format(" สแกนสเปกเรียบร้อย: {0} ({1} GB RAM)", currentSpecs.Hostname, currentSpecs.RamCapacity);
            }
            catch (Exception ex)
            {
                Log("สแกนล้มเหลว: " + ex.Message);
                lblStatus.Text = " เกิดข้อผิดพลาดในการสแกนสเปก";
            }
        }

        private void TestConnection()
        {
            string url = txtServerUrl.Text.Trim();
            if (string.IsNullOrEmpty(url))
            {
                MessageBox.Show("กรุณาระบุ URL ของ API Server", "แจ้งเตือน", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            Log("กำลังทดสอบการเชื่อมต่อ: " + url);
            btnTestConn.Enabled = false;

            try
            {
                HttpWebRequest req = (HttpWebRequest)WebRequest.Create(url);
                req.Method = "GET";
                req.Accept = "application/json";
                req.Timeout = 6000;

                using (HttpWebResponse resp = (HttpWebResponse)req.GetResponse())
                {
                    Log(string.Format("เชื่อมต่อสำเร็จ: HTTP {0} ({1})", (int)resp.StatusCode, resp.StatusDescription));
                    MessageBox.Show(string.Format("เชื่อมต่อกับ API Server สำเร็จ!\nHTTP Status: {0} {1}", (int)resp.StatusCode, resp.StatusDescription), "เชื่อมต่อสำเร็จ", MessageBoxButtons.OK, MessageBoxIcon.Information);

                    config.ServerUrl = url;
                    config.Save();
                }
            }
            catch (Exception ex)
            {
                Log("เชื่อมต่อล้มเหลว: " + ex.Message);
                MessageBox.Show(string.Format("ไม่สามารถเชื่อมต่อกับ API Server ได้:\n{0}", ex.Message), "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            finally
            {
                btnTestConn.Enabled = true;
            }
        }

        private void SubmitSpecs()
        {
            if (currentSpecs == null) PerformScan();

            currentSpecs.Hostname = txtHostname.Text.Trim();
            currentSpecs.Brand = txtBrand.Text.Trim();
            currentSpecs.Model = txtModel.Text.Trim();
            currentSpecs.SerialNumber = txtSerial.Text.Trim();
            currentSpecs.StorageCapacity = txtStorage.Text.Trim();
            currentSpecs.DepartmentName = cboDepartment.Text.Trim();

            if (cboDeviceType.SelectedIndex == 1) currentSpecs.DeviceTypeCode = "NB";
            else if (cboDeviceType.SelectedIndex == 2) currentSpecs.DeviceTypeCode = "AIO";
            else currentSpecs.DeviceTypeCode = "PC";

            string url = txtServerUrl.Text.Trim();
            if (string.IsNullOrEmpty(url))
            {
                MessageBox.Show("กรุณาระบุ URL ของ API Server", "แจ้งเตือน", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                return;
            }

            btnSend.Enabled = false;
            btnSend.Text = "⏳ กำลังส่งข้อมูล...";
            lblStatus.Text = " กำลังส่งข้อมูลสเปกเข้า API...";
            Application.DoEvents();

            try
            {
                string json = currentSpecs.ToJson();
                Log("ส่งคำขอ POST ไปยัง " + url);

                int statusCode;
                string respBody = HardwareScanner.HttpPost(url, json, config.ApiToken, out statusCode);

                Log(string.Format("ผลตอบกลับ HTTP {0}:\r\n{1}", statusCode, respBody));

                if (statusCode >= 200 && statusCode < 300)
                {
                    lblStatus.Text = string.Format(" บันทึกข้อมูลสำเร็จ! HTTP {0}", statusCode);
                    MessageBox.Show(string.Format("บันทึกข้อมูลสเปกเข้าสู่ระบบเรียบร้อยแล้ว!\n\nชื่อเครื่อง: {0}\nปีงบประมาณ: {1}", currentSpecs.Hostname, currentSpecs.FiscalYear), "สำเร็จ", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
                else
                {
                    lblStatus.Text = string.Format(" เซิร์ฟเวอร์ตอบกลับรหัส: {0}", statusCode);
                    MessageBox.Show(string.Format("เซิร์ฟเวอร์ตอบกลับข้อผิดพลาด (HTTP {0}):\n{1}", statusCode, respBody), "แจ้งเตือน", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            }
            catch (Exception ex)
            {
                Log("ส่งข้อมูลไม่สำเร็จ: " + ex.Message);
                lblStatus.Text = " ข้อผิดพลาดการส่งข้อมูล: " + ex.Message;
                MessageBox.Show(string.Format("ไม่สามารถส่งข้อมูลได้:\n{0}", ex.Message), "ข้อผิดพลาด", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
            finally
            {
                btnSend.Enabled = true;
                btnSend.Text = "🚀 ส่งสเปกเข้า API ทันที (Manual Push)";
            }
        }

        private void SetupBackgroundService()
        {
            DialogResult dr = MessageBox.Show(
                "ต้องการติดตั้งโปรแกรม THC Hardware Scanner เข้าสู่ระบบ Windows Task Scheduler หรือไม่?\n\n" +
                "เมื่อติดตั้งแล้ว:\n" +
                "- โปรแกรมจะทำงานในโหมด Standby เบื้องหลัง\n" +
                "- ไม่มีพอร์ตเปิดรับการเชื่อมต่อ (Zero Inbound Port)\n" +
                "- จะทำการสแกนสเปกก็ต่อเมื่อแอดมิน 'กดปุ่มสั่งดึงข้อมูล' จากหน้าเว็บเท่านั้น\n\n" +
                "กด 'Yes' เพื่อติดตั้งทันที",
                "ยืนยันการติดตั้ง Service",
                MessageBoxButtons.YesNo,
                MessageBoxIcon.Question
            );

            if (dr == DialogResult.Yes)
            {
                string url = txtServerUrl.Text.Trim();
                bool ok = Installer.InstallService(url);
                if (ok)
                {
                    Log("ติดตั้งบริการ Standby Task Scheduler สำเร็จ!");
                    MessageBox.Show("ติดตั้งบริการ THC Hardware Scanner สำเร็จ!\nเครื่องนี้พร้อมรับคำสั่งดึงสเปกจากแอดมินผ่านหน้าเว็บแล้ว", "ติดตั้งสำเร็จ", MessageBoxButtons.OK, MessageBoxIcon.Information);
                }
                else
                {
                    Log("การติดตั้งล้มเหลว (อาจต้องใช้สิทธิ์ Administrator)");
                    MessageBox.Show("การติดตั้งล้มเหลว กรุณาคลิกขวาที่โปรแกรมแล้วเลือก 'Run as administrator'", "สิทธิ์ไม่เพียงพอ", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                }
            }
        }

        private void Log(string msg)
        {
            if (rtbConsole.IsDisposed) return;
            string txt = string.Format("[{0}] {1}\r\n", DateTime.Now.ToString("HH:mm:ss"), msg);
            rtbConsole.AppendText(txt);
            rtbConsole.SelectionStart = rtbConsole.Text.Length;
            rtbConsole.ScrollToCaret();
        }
    }

    public static class Program
    {
        [STAThread]
        public static int Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            AppConfig config = AppConfig.Load();
            string mode = "gui";
            string overrideServer = "";

            foreach (string arg in args)
            {
                string a = arg.Trim().ToLower();
                if (a == "/service" || a == "-service" || a == "/poll" || a == "-poll" || a == "/daemon")
                {
                    mode = "service";
                }
                else if (a == "/auto" || a == "-auto" || a == "/silent" || a == "-silent")
                {
                    mode = "auto";
                }
                else if (a == "/install" || a == "-install")
                {
                    mode = "install";
                }
                else if (a == "/uninstall" || a == "-uninstall")
                {
                    mode = "uninstall";
                }
                else if (a.StartsWith("/server:") || a.StartsWith("-server:"))
                {
                    overrideServer = arg.Substring(8).Trim();
                }
            }

            if (!string.IsNullOrEmpty(overrideServer))
            {
                config.ServerUrl = overrideServer;
                config.Save();
            }

            if (mode == "service")
            {
                // Run background polling daemon waiting for Admin Pull commands
                ClientDaemon.RunLoop(config);
                return 0;
            }
            else if (mode == "install")
            {
                bool ok = Installer.InstallService(config.ServerUrl);
                return ok ? 0 : 1;
            }
            else if (mode == "uninstall")
            {
                bool ok = Installer.UninstallService();
                return ok ? 0 : 1;
            }
            else if (mode == "auto")
            {
                try
                {
                    HardwareSpecs specs = HardwareScanner.Scan();
                    string json = specs.ToJson();
                    int code;
                    HardwareScanner.HttpPost(config.ServerUrl, json, config.ApiToken, out code);
                    return code >= 200 && code < 300 ? 0 : 1;
                }
                catch (Exception ex)
                {
                    Logger.Log("Auto scan error: " + ex.Message);
                    return 2;
                }
            }
            else
            {
                Application.Run(new MainForm());
                return 0;
            }
        }
    }
}
