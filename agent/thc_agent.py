# -*- coding: utf-8 -*-
"""
THC IT Agent - Python Edition
Thung Hua Chang Hospital (รพ.ทุ่งหัวช้าง)
Version: 2.12.0-Python
Hardware Audit & Remote Management Background Agent
"""

import os
import sys
import json
import time
import socket
import logging
import platform
import hashlib
import threading
import subprocess
import winreg
from datetime import datetime

# Windows API via ctypes for single-instance Mutex & basic controls
import ctypes
from ctypes import wintypes

CURRENT_VERSION = "2.12.0-Python"
APP_GUID = "THC-IT-Agent-f91aa586-7788-9900-thung-hua-chang-python"
STANDARD_INSTALL_DIR = r"C:\ProgramData\THC-IT-Agent"
STANDARD_EXE_NAME = "THC_IT_Agent_Python.exe"
REG_RUN_KEY = r"Software\Microsoft\Windows\CurrentVersion\Run"
REG_APP_KEY = r"Software\THC-IT-Agent"
DEFAULT_SERVER_URL = "https://thchospital.moph.go.th/it-system"

# Setup logging
os.makedirs(STANDARD_INSTALL_DIR, exist_ok=True)
log_file = os.path.join(STANDARD_INSTALL_DIR, "agent_python.log")
logging.basicConfig(
    filename=log_file,
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S"
)

# Optional dependencies with safe fallbacks
try:
    import requests
except ImportError:
    requests = None

try:
    import win32com.client
except ImportError:
    win32com = None

try:
    import pystray
    from PIL import Image, ImageDraw
except ImportError:
    pystray = None


# ------------------------------------------------------------------------------
# App Configuration
# ------------------------------------------------------------------------------
class AppConfig:
    def __init__(self):
        self.server_url = DEFAULT_SERVER_URL
        self.heartbeat_interval = 60
        self.auto_start = True
        self.candidate_urls = [
            "https://thchospital.moph.go.th/it-system",
            "http://thchospital.moph.go.th/it-system",
            "http://192.168.2.89:8000",
            "http://192.168.2.89/it-system",
            "http://localhost:8000"
        ]
        self.load()

    def get_config_path(self):
        return os.path.join(STANDARD_INSTALL_DIR, "config.json")

    def load(self):
        cfg_path = self.get_config_path()
        if os.path.exists(cfg_path):
            try:
                with open(cfg_path, "r", encoding="utf-8") as f:
                    data = json.load(f)
                    self.server_url = data.get("ServerUrl", data.get("server_url", self.server_url)).rstrip('/')
                    self.heartbeat_interval = int(data.get("HeartbeatIntervalSec", data.get("heartbeat_interval", self.heartbeat_interval)))
                    self.auto_start = bool(data.get("AutoStart", data.get("auto_start", self.auto_start)))
            except Exception as e:
                logging.error(f"Error loading config.json: {e}")

    def save(self):
        cfg_path = self.get_config_path()
        try:
            data = {
                "ServerUrl": self.server_url,
                "HeartbeatIntervalSec": self.heartbeat_interval,
                "AutoStart": self.auto_start
            }
            with open(cfg_path, "w", encoding="utf-8") as f:
                json.dump(data, f, indent=4, ensure_ascii=False)
        except Exception as e:
            logging.error(f"Error saving config.json: {e}")

    @staticmethod
    def set_autostart(enable=True):
        """Standard Windows Autostart: Writes standard installed path to HKCU Run"""
        try:
            std_exe = os.path.join(STANDARD_INSTALL_DIR, STANDARD_EXE_NAME)
            target_path = std_exe if os.path.exists(std_exe) else sys.executable
            if not target_path.lower().endswith(".exe"):
                # Running as script with python.exe
                target_cmd = f'"{sys.executable}" "{os.path.abspath(__file__)}"'
            else:
                target_cmd = f'"{target_path}"'

            with winreg.OpenKey(winreg.HKEY_CURRENT_USER, REG_RUN_KEY, 0, winreg.KEY_SET_VALUE) as key:
                if enable:
                    winreg.SetValueEx(key, "THC_IT_Agent", 0, winreg.REG_SZ, target_cmd)
                else:
                    try:
                        winreg.DeleteValue(key, "THC_IT_Agent")
                    except FileNotFoundError:
                        pass

            # Clean up duplicate Startup folder shortcut to prevent double-starts on Windows boot
            startup_dir = os.path.join(os.environ.get("APPDATA", ""), r"Microsoft\Windows\Start Menu\Programs\Startup")
            startup_lnk = os.path.join(startup_dir, "THC IT Agent.lnk")
            if os.path.exists(startup_lnk):
                try:
                    os.remove(startup_lnk)
                except Exception:
                    pass
        except Exception as e:
            logging.error(f"Error setting autostart: {e}")


# ------------------------------------------------------------------------------
# Installation State & Standard Windows Helpers
# ------------------------------------------------------------------------------
class InstallerHelper:
    @staticmethod
    def is_installed():
        """Checks if THC IT Agent is already installed on this machine"""
        std_cfg = os.path.join(STANDARD_INSTALL_DIR, "config.json")
        std_exe = os.path.join(STANDARD_INSTALL_DIR, STANDARD_EXE_NAME)
        if os.path.exists(std_cfg) and (os.path.exists(std_exe) or os.path.exists(os.path.join(STANDARD_INSTALL_DIR, "THC_IT_Agent.exe"))):
            return True

        # Check registry installed flag
        try:
            with winreg.OpenKey(winreg.HKEY_CURRENT_USER, REG_APP_KEY, 0, winreg.KEY_READ) as key:
                val, _ = winreg.QueryValueEx(key, "Installed")
                if val == 1:
                    return True
        except FileNotFoundError:
            pass
        return False

    @staticmethod
    def mark_installed(version=CURRENT_VERSION):
        try:
            with winreg.CreateKey(winreg.HKEY_CURRENT_USER, REG_APP_KEY) as key:
                winreg.SetValueEx(key, "Installed", 0, winreg.REG_DWORD, 1)
                winreg.SetValueEx(key, "InstallPath", 0, winreg.REG_SZ, STANDARD_INSTALL_DIR)
                winreg.SetValueEx(key, "Version", 0, winreg.REG_SZ, version)
                winreg.SetValueEx(key, "InstallDate", 0, winreg.REG_SZ, datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
        except Exception as e:
            logging.error(f"Error writing registry installed key: {e}")

    @staticmethod
    def uninstall():
        try:
            AppConfig.set_autostart(False)
            try:
                winreg.DeleteKey(winreg.HKEY_CURRENT_USER, REG_APP_KEY)
            except Exception:
                pass
            logging.info("Agent uninstalled successfully.")
            return True
        except Exception as e:
            logging.error(f"Uninstall failed: {e}")
            return False


# ------------------------------------------------------------------------------
# Hardware Telemetry Collector
# ------------------------------------------------------------------------------
class HardwareSpecs:
    @staticmethod
    def collect():
        specs = {
            "hostname": platform.node(),
            "hardware_id": "",
            "serial_number": "",
            "brand": "",
            "model": "",
            "device_type_code": "COM",
            "cpu_model": platform.processor(),
            "cpu_speed": "",
            "ram_capacity": 8,
            "ram_type": "DDR4",
            "ram_bus": "",
            "ram_slots": "",
            "storage_type": "SSD",
            "storage_capacity": "",
            "storage_second": "",
            "os_name": f"{platform.system()} {platform.release()} ({platform.architecture()[0]})",
            "os_license": "OEM",
            "gpu_model": "Integrated Graphics",
            "ip_address": "",
            "mac_address": "",
            "monitor_size": "",
            "monitor_info": "",
            "client_agent_version": CURRENT_VERSION,
            "agent_version": CURRENT_VERSION
        }

        # IP Address fallback
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            s.connect(("8.8.8.8", 80))
            specs["ip_address"] = s.getsockname()[0]
            s.close()
        except Exception:
            try:
                specs["ip_address"] = socket.gethostbyname(socket.gethostname())
            except Exception:
                pass

        # WMI Inspection via win32com
        if win32com:
            try:
                wmi = win32com.client.GetObject("winmgmts:")

                # 1. Computer System & Product (UUID / Serial / Model)
                for item in wmi.InstancesOf("Win32_ComputerSystemProduct"):
                    specs["hardware_id"] = str(getattr(item, "UUID", "")).strip().upper()
                    specs["serial_number"] = str(getattr(item, "IdentifyingNumber", "")).strip()

                for item in wmi.InstancesOf("Win32_ComputerSystem"):
                    specs["brand"] = str(getattr(item, "Manufacturer", "")).strip()
                    specs["model"] = str(getattr(item, "Model", "")).strip()
                    total_ram = getattr(item, "TotalPhysicalMemory", 0)
                    if total_ram:
                        specs["ram_capacity"] = int(round(int(total_ram) / (1024**3)))

                # 2. CPU
                for item in wmi.InstancesOf("Win32_Processor"):
                    cpu_name = str(getattr(item, "Name", "")).strip()
                    if cpu_name:
                        specs["cpu_model"] = cpu_name
                    max_clock = getattr(item, "MaxClockSpeed", 0)
                    if max_clock:
                        specs["cpu_speed"] = f"{round(int(max_clock)/1000.0, 2)} GHz"
                    break

                # 3. RAM Modules
                ram_types = {24: "DDR3", 26: "DDR4", 34: "DDR5"}
                slots = 0
                speeds = []
                for item in wmi.InstancesOf("Win32_PhysicalMemory"):
                    slots += 1
                    sp = getattr(item, "Speed", 0)
                    if sp:
                        speeds.append(f"{sp} MHz")
                    sm_type = getattr(item, "SMBIOSMemoryType", 0)
                    if sm_type in ram_types:
                        specs["ram_type"] = ram_types[sm_type]
                specs["ram_slots"] = f"{slots} Slot(s)"
                if speeds:
                    specs["ram_bus"] = speeds[0]

                # 4. Storage Disks
                disks = []
                for item in wmi.InstancesOf("Win32_DiskDrive"):
                    size = getattr(item, "Size", 0)
                    if size and int(size) > 5 * 1024**3:
                        gb = round(int(size) / (1024**3))
                        d_type = "SSD" if "SSD" in str(getattr(item, "Model", "")).upper() else "HDD"
                        if "NVME" in str(getattr(item, "Model", "")).upper():
                            d_type = "NVMe SSD"
                        cap_str = f"{gb} GB" if gb < 900 else f"{round(gb/1000.0, 1)} TB"
                        disks.append(f"{d_type} {cap_str}")

                if disks:
                    specs["storage_capacity"] = disks[0]
                    if len(disks) > 1:
                        specs["storage_second"] = ", ".join(disks[1:])

                # 5. OS Details
                for item in wmi.InstancesOf("Win32_OperatingSystem"):
                    cap = str(getattr(item, "Caption", "")).replace("Microsoft", "").strip()
                    arch = str(getattr(item, "OSArchitecture", "")).strip()
                    specs["os_name"] = f"{cap} ({arch})" if arch else cap
                    break

                # 6. GPU
                gpus = []
                for item in wmi.InstancesOf("Win32_VideoController"):
                    name = str(getattr(item, "Name", "")).strip()
                    if name and "virtual" not in name.lower() and "remote" not in name.lower():
                        gpus.append(name)
                if gpus:
                    specs["gpu_model"] = ", ".join(gpus)

                # 7. Network MAC Address
                for item in wmi.InstancesOf("Win32_NetworkAdapterConfiguration"):
                    if getattr(item, "IPEnabled", False):
                        mac = getattr(item, "MACAddress", "")
                        if mac:
                            specs["mac_address"] = str(mac).strip().upper()
                            break

            except Exception as e:
                logging.error(f"Error gathering WMI specs: {e}")

        # Fallback Hardware ID if UUID missing
        if not specs["hardware_id"]:
            specs["hardware_id"] = hashlib.md5(f"{specs['hostname']}-{specs['mac_address']}".encode("utf-8")).hexdigest().upper()

        return specs


# ------------------------------------------------------------------------------
# Agent Daemon / Background Service
# ------------------------------------------------------------------------------
class ThcAgentDaemon:
    def __init__(self, config):
        self.config = config
        self.running = True
        self.status = "ออนไลน์ (Standby)"
        self.is_scanning = False
        self.cached_specs = None
        self.tray_icon = None

    def start(self):
        logging.info(f"THC IT Agent Daemon v{CURRENT_VERSION} starting...")
        # Start background polling thread
        t = threading.Thread(target=self._heartbeat_loop, daemon=True)
        t.start()

        # Run system tray or wait
        self._run_tray()

    def _http_request(self, endpoint, method="GET", payload=None, timeout=10):
        urls = [self.config.server_url] + [u for u in self.config.candidate_urls if u != self.config.server_url]
        for base in urls:
            url = f"{base.rstrip('/')}/{endpoint.lstrip('/')}"
            try:
                if requests:
                    if method == "POST":
                        resp = requests.post(url, json=payload, timeout=timeout, headers={"User-Agent": f"THC-Agent-Python/{CURRENT_VERSION}"})
                    else:
                        resp = requests.get(url, params=payload, timeout=timeout, headers={"User-Agent": f"THC-Agent-Python/{CURRENT_VERSION}"})
                    if resp.status_code == 200:
                        return resp.json() if "application/json" in resp.headers.get("Content-Type", "") else resp.text
                else:
                    # Native urllib fallback (zero external dependencies)
                    import urllib.request
                    req = urllib.request.Request(url, headers={"User-Agent": f"THC-Agent-Python/{CURRENT_VERSION}"})
                    if method == "POST" and payload:
                        data = json.dumps(payload).encode("utf-8")
                        req.add_header("Content-Type", "application/json")
                        with urllib.request.urlopen(req, data=data, timeout=timeout) as response:
                            return json.loads(response.read().decode("utf-8"))
                    else:
                        with urllib.request.urlopen(req, timeout=timeout) as response:
                            return json.loads(response.read().decode("utf-8"))
            except Exception as e:
                logging.debug(f"HTTP request to {url} failed: {e}")
        return None

    def _heartbeat_loop(self):
        # Initial scan and submit on boot
        time.sleep(2)
        self.scan_and_submit(is_initial=True)

        while self.running:
            try:
                specs = self.cached_specs or HardwareSpecs.collect()
                ping_payload = {
                    "hostname": specs["hostname"],
                    "hardware_id": specs["hardware_id"],
                    "agent_version": CURRENT_VERSION,
                    "ip_address": specs["ip_address"],
                    "mac_address": specs["mac_address"],
                    "mode": "tray_agent"
                }

                resp = self._http_request("/api/hardware-audit/agent-command", method="GET", payload=ping_payload, timeout=8)
                if resp and isinstance(resp, dict):
                    self.status = "ออนไลน์ 🟢"
                    has_command = resp.get("has_command", False)
                    if has_command:
                        cmd = resp.get("command", "")
                        cmd_id = resp.get("command_id", 0)
                        logging.info(f"Received server command: {cmd} (ID: {cmd_id})")

                        if cmd == "scan":
                            self.scan_and_submit(command_id=cmd_id)
                        elif cmd == "update":
                            self.execute_update(resp.get("parameters", {}))
                        elif cmd == "uninstall":
                            self.execute_uninstall()
                else:
                    self.status = "ออฟไลน์ (เชื่อมต่อเซิร์ฟเวอร์ไม่ได้) 🔴"
            except Exception as e:
                logging.error(f"Error in heartbeat loop: {e}")
                self.status = "ผิดพลาด 🔴"

            time.sleep(self.config.heartbeat_interval)

    def scan_and_submit(self, command_id=None, is_initial=False):
        self.is_scanning = True
        self.status = "กำลังสแกนสเปก 🟡"
        try:
            specs = HardwareSpecs.collect()
            self.cached_specs = specs
            if command_id:
                specs["command_id"] = command_id

            resp = self._http_request("/api/hardware-audit/submit", method="POST", payload=specs, timeout=15)
            if resp:
                logging.info(f"Hardware specs submitted successfully for {specs['hostname']}")
                self.status = "ออนไลน์ 🟢"
                if command_id:
                    self._http_request(f"/api/hardware-audit/agent-command/{command_id}/complete", method="POST", payload={"summary": "Scan completed by Python Agent"})
            else:
                logging.warning("Failed to submit hardware specs.")
        except Exception as e:
            logging.error(f"Scan and submit error: {e}")
        finally:
            self.is_scanning = False

    def execute_update(self, params):
        """PowerShell Atomic Swap updater for Python Agent"""
        logging.info("Initiating auto-update for Python Agent...")
        cur_pid = os.getpid()
        cur_exe = sys.executable if sys.executable.lower().endswith(".exe") else os.path.abspath(__file__)
        cur_dir = os.path.dirname(cur_exe)
        log_path = os.path.join(STANDARD_INSTALL_DIR, "update.log").replace("'", "''")

        download_url = params.get("download_url", f"{self.config.server_url}/agent/{STANDARD_EXE_NAME}")
        new_file = f"{cur_exe}.new"

        try:
            # Download new file
            if requests:
                r = requests.get(download_url, timeout=30)
                with open(new_file, "wb") as f:
                    f.write(r.content)
            else:
                import urllib.request
                urllib.request.urlretrieve(download_url, new_file)

            # PowerShell detached swap
            ps_cmd = (
                f"$ErrorActionPreference = 'SilentlyContinue'; "
                f"Add-Content -Path '{log_path}' -Value ('[' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + '] [PID {cur_pid}] Python Updater started.'); "
                f"$p = Get-Process -Id {cur_pid} -ErrorAction SilentlyContinue; "
                f"if ($p) {{ $p.WaitForExit(7000) }} "
                f"Stop-Process -Id {cur_pid} -Force -ErrorAction SilentlyContinue; "
                f"Start-Sleep -Milliseconds 600; "
                f"if (Test-Path '{new_file}') {{ "
                f"  Move-Item -Path '{new_file}' -Destination '{cur_exe}' -Force; "
                f"  Start-Process -FilePath '{cur_exe}' -WorkingDirectory '{cur_dir}'; "
                f"  Add-Content -Path '{log_path}' -Value ('[' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + '] Python Agent updated and restarted.'); "
                f"}}"
            )
            subprocess.Popen(["powershell.exe", "-NoProfile", "-ExecutionPolicy", "Bypass", "-Command", ps_cmd])
            self.running = False
            sys.exit(0)
        except Exception as e:
            logging.error(f"Auto-update failed: {e}")

    def execute_uninstall(self):
        logging.info("Executing remote uninstall...")
        InstallerHelper.uninstall()
        self.running = False
        sys.exit(0)

    def _create_tray_icon(self, color="green"):
        """Generate dynamic PIL tray icon"""
        width = 64
        height = 64
        image = Image.new("RGBA", (width, height), (0, 0, 0, 0))
        draw = ImageDraw.Draw(image)
        colors = {"green": (46, 204, 113), "yellow": (241, 196, 15), "red": (231, 76, 60)}
        c = colors.get(color, (46, 204, 113))
        draw.ellipse([4, 4, width - 4, height - 4], fill=c, outline=(255, 255, 255), width=3)
        return image

    def _run_tray(self):
        if not pystray:
            logging.info("pystray not installed. Running in headless background service mode.")
            while self.running:
                time.sleep(1)
            return

        def on_scan(icon, item):
            threading.Thread(target=self.scan_and_submit, daemon=True).start()

        def on_exit(icon, item):
            self.running = False
            icon.stop()

        def on_open_dashboard(icon, item):
            import webbrowser
            webbrowser.open(f"{self.config.server_url}/hardware-audits")

        menu = pystray.Menu(
            pystray.MenuItem(lambda text: f"สถานะ: {self.status}", None, enabled=False),
            pystray.MenuItem("🔄 ตรวจสอบสเปกเดี๋ยวนี้ (Audit Now)", on_scan),
            pystray.MenuItem("🌐 เปิดหน้าระบบครุภัณฑ์ (Dashboard)", on_open_dashboard),
            pystray.Menu.SEPARATOR,
            pystray.MenuItem("❌ ออกจากโปรแกรม (Exit)", on_exit)
        )

        self.tray_icon = pystray.Icon("THC_IT_Agent_Python", self._create_tray_icon("green"), "THC IT Agent - รพ.ทุ่งหัวช้าง", menu)
        self.tray_icon.run()


# ------------------------------------------------------------------------------
# Main Entry Point
# ------------------------------------------------------------------------------
def main():
    # 1. Single Instance Named Mutex (Windows API)
    kernel32 = ctypes.windll.kernel32
    mutex = kernel32.CreateMutexW(None, True, APP_GUID)
    last_error = kernel32.GetLastError()
    ERROR_ALREADY_EXISTS = 183

    # Parse CLI flags
    args = [a.lower().strip("-/") for a in sys.argv[1:]]
    is_silent = any(a in ["silent", "quiet", "s", "q"] for a in args)
    is_install = any(a in ["install", "setup"] for a in args)
    is_uninstall = any(a in ["uninstall", "remove"] for a in args)

    if is_uninstall:
        InstallerHelper.uninstall()
        sys.exit(0)

    # 2. Check Standard Location & Installation State
    cur_exe = sys.executable if sys.executable.lower().endswith(".exe") else os.path.abspath(__file__)
    std_exe = os.path.join(STANDARD_INSTALL_DIR, STANDARD_EXE_NAME)
    is_standard_loc = os.path.normpath(cur_exe).lower() == os.path.normpath(std_exe).lower()
    is_already_installed = InstallerHelper.is_installed()

    if is_install:
        AppConfig.set_autostart(True)
        InstallerHelper.mark_installed()
        logging.info("Installation completed via CLI flag.")
        if not is_silent:
            print("THC IT Agent installed successfully!")
        sys.exit(0)

    # If already installed, ensure autostart registry points to standard location
    if is_already_installed:
        AppConfig.set_autostart(True)

    # If running outside standard location (e.g. Downloads/Desktop) and already installed:
    # NEVER prompt to install again! Launch installed instance and exit immediately!
    if not is_standard_loc and is_already_installed and not is_install:
        logging.info(f"Already installed on this PC. Starting installed instance from {std_exe} and exiting temporary runner.")
        if os.path.exists(std_exe):
            subprocess.Popen([std_exe])
        sys.exit(0)

    # If another instance is already running in tray:
    if last_error == ERROR_ALREADY_EXISTS:
        if not is_silent and not is_standard_loc and len(sys.argv) == 1:
            ctypes.windll.user32.MessageBoxW(0, "โปรแกรม THC IT Agent กำลังทำงานอยู่ใน System Tray แล้ว", "THC IT Agent", 0x40)
        sys.exit(0)

    # Run agent daemon
    cfg = AppConfig()
    daemon = ThcAgentDaemon(cfg)
    daemon.start()


if __name__ == "__main__":
    main()
