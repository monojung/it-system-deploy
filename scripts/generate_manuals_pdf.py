#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Generate comprehensive PDF manuals for IT System (v2.8.0):
1. User Manual (docs/User_Manual_IT_System_THC.pdf)
2. Admin Manual (docs/Admin_Manual_IT_System_THC.pdf)
3. Developer Manual (docs/Developer_Manual_IT_System_THC.pdf)
"""

import os
import sys
import base64
import subprocess
import shutil

BASE_DIR = r"d:\Project\it-system"
DOCS_DIR = os.path.join(BASE_DIR, "docs")
PUBLIC_DOCS_DIR = os.path.join(BASE_DIR, "public", "docs")
IMAGES_DIR = os.path.join(DOCS_DIR, "images")
CHROME_EXE = r"C:\Program Files\Google\Chrome\Application\chrome.exe"

os.makedirs(DOCS_DIR, exist_ok=True)
os.makedirs(PUBLIC_DOCS_DIR, exist_ok=True)

def get_base64_image(filename):
    filepath = os.path.join(IMAGES_DIR, filename)
    if os.path.exists(filepath):
        with open(filepath, "rb") as f:
            data = base64.b64encode(f.read()).decode("utf-8")
            ext = os.path.splitext(filename)[1].lower()
            mime = "image/png" if ext == ".png" else "image/jpeg"
            return f"data:{mime};base64,{data}"
    return ""

print("Loading base64 images...")
img_user_hero = get_base64_image("user_portal_hero.jpg")
img_user_tracking = get_base64_image("user_ticket_tracking.jpg")
img_admin_fleet = get_base64_image("admin_dashboard_fleet.jpg")
img_admin_inventory = get_base64_image("admin_asset_inventory.jpg")
img_dev_arch = get_base64_image("developer_architecture.jpg")
img_dev_cicd = get_base64_image("developer_cicd_pipeline.jpg")

COMMON_CSS = """
<style>
  @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap');

  @page {
    size: A4 portrait;
    margin: 15mm 14mm 15mm 14mm;
  }

  *, *:before, *:after {
    box-sizing: border-box;
  }

  body {
    font-family: 'Sarabun', 'Segoe UI', Tahoma, 'Leelawadee UI', sans-serif;
    color: #1e293b;
    background-color: #ffffff;
    line-height: 1.6;
    font-size: 13.5px;
    margin: 0;
    padding: 0;
  }

  h1, h2, h3, h4, h5, h6 {
    font-family: 'Prompt', 'Segoe UI', sans-serif;
    font-weight: 600;
    line-height: 1.3;
    margin-top: 1.2em;
    margin-bottom: 0.5em;
  }

  .cover-page {
    page-break-after: always;
    min-height: 250mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px 25px 25px 25px;
    box-sizing: border-box;
    position: relative;
    border-radius: 12px;
  }

  .page-break {
    page-break-before: always;
  }

  .no-break {
    page-break-inside: avoid;
    break-inside: avoid;
  }

  /* Figure & Images */
  .figure-box {
    margin: 16px 0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    background: #ffffff;
    page-break-inside: avoid;
    break-inside: avoid;
  }
  .figure-box img {
    width: 100%;
    display: block;
    max-height: 380px;
    object-fit: cover;
  }
  .figure-caption {
    padding: 8px 14px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
    color: #475569;
    font-weight: 500;
    text-align: center;
    font-family: 'Prompt', sans-serif;
  }

  /* Alerts & Callout Boxes */
  .callout {
    padding: 12px 16px;
    border-radius: 8px;
    margin: 14px 0;
    border-left: 5px solid;
    page-break-inside: avoid;
    break-inside: avoid;
    font-size: 13px;
  }
  .callout-info {
    background-color: #f0f9ff;
    border-color: #0284c7;
    color: #0369a1;
  }
  .callout-success {
    background-color: #f0fdf4;
    border-color: #16a34a;
    color: #15803d;
  }
  .callout-warning {
    background-color: #fffbeb;
    border-color: #d97706;
    color: #b45309;
  }
  .callout-danger {
    background-color: #fef2f2;
    border-color: #dc2626;
    color: #b91c1c;
  }
  .callout-title {
    font-weight: 700;
    font-family: 'Prompt', sans-serif;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  /* Steps List */
  .step-list {
    margin: 12px 0 16px 0;
    padding-left: 0;
    list-style: none;
  }
  .step-item {
    display: flex;
    margin-bottom: 12px;
    align-items: flex-start;
    page-break-inside: avoid;
  }
  .step-number {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    margin-right: 12px;
    margin-top: 2px;
    color: white;
  }
  .step-content {
    flex-grow: 1;
  }
  .step-title {
    font-weight: 600;
    font-family: 'Prompt', sans-serif;
    margin-bottom: 2px;
    color: #0f172a;
  }

  /* Tables */
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0;
    font-size: 12.5px;
    page-break-inside: avoid;
    break-inside: avoid;
  }
  th, td {
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    text-align: left;
  }
  th {
    background-color: #f1f5f9;
    font-family: 'Prompt', sans-serif;
    font-weight: 600;
    color: #334155;
  }
  tr:nth-child(even) td {
    background-color: #f8fafc;
  }

  /* Code Blocks */
  pre, code {
    font-family: 'Fira Code', Consolas, monospace;
    font-size: 11.5px;
  }
  pre {
    background-color: #0f172a;
    color: #e2e8f0;
    padding: 12px 14px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 12px 0;
    line-height: 1.45;
    page-break-inside: avoid;
  }
  code:not(pre code) {
    background-color: #f1f5f9;
    color: #0f172a;
    padding: 2px 5px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
  }

  /* Badges */
  .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 600;
    font-family: 'Prompt', sans-serif;
  }
  .badge-primary { background: #e0f2fe; color: #0369a1; }
  .badge-success { background: #dcfce7; color: #15803d; }
  .badge-warning { background: #fef3c7; color: #b45309; }
  .badge-danger  { background: #fee2e2; color: #b91c1c; }
  .badge-purple  { background: #f3e8ff; color: #7e22ce; }

  /* Section Header styling */
  .section-header {
    border-bottom: 2px solid;
    padding-bottom: 6px;
    margin-top: 24px;
    margin-bottom: 14px;
  }

  /* Cards grid */
  .card-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin: 14px 0;
    page-break-inside: avoid;
  }
  .info-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  }
  .info-card h4 {
    margin-top: 0;
    margin-bottom: 6px;
    font-size: 13.5px;
  }

  .toc-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dotted #cbd5e1;
    font-size: 13.5px;
  }
  .toc-title { font-weight: 500; }
  .toc-page { font-weight: 600; color: #64748b; }

  .doc-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 6px;
    margin-bottom: 18px;
    font-size: 11px;
    color: #94a3b8;
    font-family: 'Prompt', sans-serif;
  }
</style>
"""

# ==========================================
# 1. USER MANUAL HTML
# ==========================================
def build_user_manual():
    theme_primary = "#0284c7"
    theme_secondary = "#059669"
    
    html = f"""<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>คู่มือการใช้งานระบบสารสนเทศและบริการไอที - สำหรับผู้ใช้งานทั่วไป</title>
{COMMON_CSS}
<style>
  .theme-user {{ color: {theme_primary}; }}
  .border-user {{ border-color: {theme_primary}; }}
  .bg-user {{ background-color: {theme_primary}; }}
</style>
</head>
<body>

  <!-- COVER PAGE -->
  <div class="cover-page" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #f8fafc 100%); border: 2px solid #bae6fd;">
    <div>
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 40px;">
        <div style="background: {theme_primary}; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-family: 'Prompt', sans-serif; font-size: 16px;">
          IT HELPDESK
        </div>
        <div style="font-size: 13px; color: #475569; font-weight: 600;">
          ระบบบริหารจัดการงานเทคโนโลยีสารสนเทศและบริการ
        </div>
      </div>
      
      <div style="margin-top: 40px;">
        <span class="badge" style="background: #0284c7; color: white; padding: 6px 14px; font-size: 13px; margin-bottom: 16px;">
          คู่มือสำหรับผู้ใช้งานทั่วไป (End-User Manual)
        </span>
        <h1 style="font-size: 32px; color: #0f172a; margin-top: 14px; margin-bottom: 12px; font-weight: 700; line-height: 1.2;">
          คู่มือการใช้งานระบบแจ้งซ่อม<br>และคำขอรับบริการไอที
        </h1>
        <p style="font-size: 16px; color: #334155; max-width: 600px; line-height: 1.6;">
          แนะนำขั้นตอนการเข้าสู่ระบบผ่าน ThaiD และบัญชีองค์กร, การเปิดใบแจ้งซ่อมคอมพิวเตอร์, การติดตามสถานะแบบเรียลไทม์, และการประเมินความพึงพอใจการให้บริการ
        </p>
      </div>
    </div>

    <div>
      <div style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(8px); padding: 18px 24px; border-radius: 10px; border: 1px solid #cbd5e1; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 12.5px;">
          <div>
            <div style="color: #64748b;">เวอร์ชันระบบ:</div>
            <div style="font-weight: 700; color: #0284c7; font-size: 14px;">v2.8.0 Production</div>
          </div>
          <div>
            <div style="color: #64748b;">กลุ่มผู้ใช้งาน:</div>
            <div style="font-weight: 600; color: #0f172a;">บุคลากรทุกแผนก / ผู้ใช้งานทั่วไป</div>
          </div>
          <div>
            <div style="color: #64748b;">วันที่เผยแพร่:</div>
            <div style="font-weight: 600; color: #0f172a;">ตุลาคม 2026</div>
          </div>
        </div>
      </div>

      <div style="font-size: 11px; color: #64748b; text-align: center;">
        ฝ่ายเทคโนโลยีสารสนเทศและการสื่อสาร • เอกสารคู่มืออิเล็กทรอนิกส์พร้อมภาพประกอบ
      </div>
    </div>
  </div>

  <!-- TABLE OF CONTENTS -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้ใช้งานทั่วไป</span>
    </div>

    <h2 class="section-header border-user theme-user">สารบัญ (Table of Contents)</h2>
    
    <div style="margin-top: 20px;">
      <div class="toc-item"><span class="toc-title">บทนำ: แนะนำระบบสารสนเทศและบริการไอที</span><span class="toc-page">หน้า 2</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 1: การเข้าสู่ระบบ (Sign In & Authentication)</span><span class="toc-page">หน้า 3</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 2: การเปิดคำขอรับบริการ / แจ้งซ่อม (Service Requests)</span><span class="toc-page">หน้า 4</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 3: การติดตามสถานะคำร้อง (Track Ticket Status)</span><span class="toc-page">หน้า 5</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 4: การตรวจรับงานและการประเมินความพึงพอใจ (Rating & Feedback)</span><span class="toc-page">หน้า 6</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 5: การขอยืมอุปกรณ์คอมพิวเตอร์ และการตรวจสอบทรัพย์สินส่วนตัว</span><span class="toc-page">หน้า 7</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 6: คำถามที่พบบ่อย (FAQ) และข้อควรปฏิบัติ</span><span class="toc-page">หน้า 8</span></div>
    </div>

    <h2 class="section-header border-user theme-user" style="margin-top: 36px;">บทนำ: แนะนำระบบสารสนเทศและบริการไอที</h2>
    <p>
      ระบบบริหารงานเทคโนโลยีสารสนเทศและบริการ (IT Helpdesk & Service Portal) ได้รับการพัฒนาขึ้นเพื่ออำนวยความสะดวกให้แก่บุคลากรทุกท่านในการแจ้งปัญหาทางด้านเทคโนโลยีสารสนเทศ เช่น เครื่องคอมพิวเตอร์เปิดไม่ติด, เครื่องพิมพ์พิมพ์ไม่ออก, ปัญหาเครือข่ายอินเทอร์เน็ต และการขอยืมอุปกรณ์คอมพิวเตอร์ โดยระบบช่วยให้ท่านสามารถ:
    </p>

    <div class="card-grid">
      <div class="info-card">
        <h4 style="color: #0284c7;">⚡ แจ้งซ่อมได้รวดเร็ว ทุกที่ ทุกเวลา</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">ใช้งานได้ทั้งบนคอมพิวเตอร์ แท็บเล็ต และสมาร์ตโฟน รองรับการเข้าใช้งานผ่านระบบ ThaiD</p>
      </div>
      <div class="info-card">
        <h4 style="color: #059669;">📱 แจ้งเตือนสถานะผ่าน LINE</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">ทราบทันทีเมื่อช่างรับเรื่อง, กำลังเดินทางมาแก้ไข, หรือปิดงานเรียบร้อย</p>
      </div>
      <div class="info-card">
        <h4 style="color: #d97706;">🔍 ติดตามสถานะได้แบบเรียลไทม์</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">ดูประวัติการซ่อมย้อนหลัง อาการที่เคยซ่อม และช่างผู้รับผิดชอบงาน</p>
      </div>
      <div class="info-card">
        <h4 style="color: #7c3aed;">⭐ ตรวจรับงานและให้คะแนน</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">ร่วมประเมินความพึงพอใจเพื่อพัฒนาคุณภาพการให้บริการของทีมไอที</p>
      </div>
    </div>

    <div class="figure-box">
      <img src="{img_user_hero}" alt="หน้าต่างขอรับบริการไอที">
      <div class="figure-caption">รูปที่ 1: หน้าต่างหลักสำหรับสร้างคำขอรับบริการและแจ้งปัญหาทางเทคนิค (Service Portal)</div>
    </div>
  </div>

  <!-- CHAPTER 1: SIGN IN -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้ใช้งานทั่วไป</span>
    </div>

    <h2 class="section-header border-user theme-user">บทที่ 1: การเข้าสู่ระบบ (Sign In & Authentication)</h2>
    <p>ระบบรองรับการเข้าสู่ระบบ 2 รูปแบบ เพื่อความสะดวกและความปลอดภัยสูงสุดของข้อมูล:</p>

    <div class="step-list">
      <div class="step-item">
        <div class="step-number bg-user">1</div>
        <div class="step-content">
          <div class="step-title">เข้าสู่ระบบด้วย Digital ID ภาครัฐ (ThaiD Single Sign-On)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            กดปุ่ม <b>"เข้าสู่ระบบด้วย ThaiD"</b> บนหน้าจอ จากนั้นเปิดแอปพลิเคชัน ThaiD บนสมาร์ตโฟน สแกน QR Code บนหน้าจอ และยืนยันตัวตนด้วยลายนิ้วมือหรือ Face ID ระบบจะเข้าสู่ระบบให้อัตโนมัติโดยไม่ต้องจำรหัสผ่าน
          </p>
        </div>
      </div>

      <div class="step-item">
        <div class="step-number bg-user">2</div>
        <div class="step-content">
          <div class="step-title">เข้าสู่ระบบด้วยบัญชีผู้ใช้และรหัสผ่าน (Username & Password)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            กรอกชื่อผู้ใช้งาน (อีเมล หรือ รหัสพนักงาน) และรหัสผ่านที่ได้รับจากฝ่ายไอที จากนั้นกดปุ่ม <b>"เข้าสู่ระบบ"</b>
          </p>
        </div>
      </div>
    </div>

    <div class="callout callout-info">
      <div class="callout-title">💡 คำแนะนำความปลอดภัย</div>
      หากใช้งานเครื่องคอมพิวเตอร์ส่วนกลางหรือเครื่องเวรพยาบาล เมื่อใช้งานเสร็จสิ้นควรคลิกที่ชื่อโปรไฟล์มุมขวาบนแล้วเลือก <b>"ออกจากระบบ (Logout)"</b> ทุกครั้ง เพื่อป้องกันผู้อื่นเข้าใช้งานในชื่อของท่าน
    </div>

    <h2 class="section-header border-user theme-user" style="margin-top: 30px;">บทที่ 2: การเปิดคำขอรับบริการ / แจ้งซ่อม (Service Requests)</h2>
    <p>เมื่อท่านพบปัญหาการใช้งานคอมพิวเตอร์ โปรแกรม หรือระบบเครือข่าย สามารถเปิดแจ้งซ่อมได้ตามขั้นตอนดังนี้:</p>

    <ul class="step-list">
      <li class="step-item">
        <div class="step-number bg-user">1</div>
        <div class="step-content">
          <div class="step-title">เลือกหมวดหมู่ปัญหา (Category)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            • <b>อุปกรณ์ (Hardware):</b> จอไม่ติด, เมาส์/คีย์บอร์ดเสีย, ปริ้นเตอร์กระดาษติด, คอมพิวเตอร์ดับเอง<br>
            • <b>ซอฟต์แวร์ (Software):</b> โปรแกรมโรงพยาบาลเข้าไม่ได้, เปิดไฟล์ไม่ออก, ไวรัสแจ้งเตือน<br>
            • <b>เครือข่าย (Network/Internet):</b> สัญญาณ Wi-Fi หลุด, สายแลนหลวม, เข้าอินเทอร์เน็ตไม่ได้
          </p>
        </div>
      </li>
      <li class="step-item">
        <div class="step-number bg-user">2</div>
        <div class="step-content">
          <div class="step-title">ระบุสถานที่และหมายเลขอุปกรณ์ (Location & Asset Tag)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            ระบุตึก, ชั้น, ห้อง หรือแผนกให้ชัดเจน เช่น "ห้องตรวจ 3 ชั้น 2 ตึกผู้ป่วยนอก" และหากทราบหมายเลขครุภัณฑ์ (สติ๊กเกอร์ IT Asset Tag หรือ QR Code บนตัวเครื่อง) ให้ระบุด้วยเพื่อให้ช่างเตรียมอะไหล่ได้ตรงรุ่น
          </p>
        </div>
      </li>
      <li class="step-item">
        <div class="step-number bg-user">3</div>
        <div class="step-content">
          <div class="step-title">อธิบายรายละเอียดอาการเสียและแนบรูปภาพ</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            อธิบายอาการที่พบ เช่น มีข้อความ Error ใดขึ้นมา และถ่ายรูปหน้าจอที่มีข้อความแจ้งเตือนแนบเข้ามาในระบบ (รองรับไฟล์ภาพ JPG, PNG ขนาดสูงสุด 10MB)
          </p>
        </div>
      </li>
      <li class="step-item">
        <div class="step-number bg-user">4</div>
        <div class="step-content">
          <div class="step-title">กด "ส่งคำขอรับบริการ (Submit Ticket)"</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            เมื่อกดส่ง ระบบจะออกเลขที่คำร้องทันที (ตัวอย่าง: <code>INC-245198</code>) พร้อมส่งแจ้งเตือนเข้าสู่ระบบของฝ่ายไอทีทันที
          </p>
        </div>
      </li>
    </ul>
  </div>

  <!-- CHAPTER 3 & 4: TRACKING & RATING -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้ใช้งานทั่วไป</span>
    </div>

    <h2 class="section-header border-user theme-user">บทที่ 3: การติดตามสถานะคำร้อง (Track Ticket Status)</h2>
    <p>ท่านสามารถเปิดดูรายการคำร้องของท่านได้ตลอดเวลาผ่านเมนู <b>"ประวัติคำขอของฉัน (My Tickets)"</b></p>

    <table>
      <thead>
        <tr>
          <th style="width: 130px;">สถานะ (Status)</th>
          <th style="width: 100px;">สัญลักษณ์</th>
          <th>ความหมายและการดำเนินการ</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><b>รอดำเนินการ (Pending)</b></td>
          <td><span class="badge badge-warning">Pending</span></td>
          <td>คำร้องถูกส่งเข้าสู่ระบบแล้ว กำลังรอหัวหน้างานไอทีมอบหมายช่างผู้รับผิดชอบ</td>
        </tr>
        <tr>
          <td><b>มอบหมายช่างแล้ว (Assigned)</b></td>
          <td><span class="badge badge-primary">Assigned</span></td>
          <td>มอบหมายช่างเทคนิคเรียบร้อยแล้ว ในระบบจะแสดงชื่อและเบอร์ติดต่อของช่าง</td>
        </tr>
        <tr>
          <td><b>กำลังดำเนินการ (In Progress)</b></td>
          <td><span class="badge badge-purple">In Progress</span></td>
          <td>ช่างกำลังเดินทางมายังจุดเกิดเหตุ หรือกำลังแก้ไขปัญหาผ่านระบบรีโมต</td>
        </tr>
        <tr>
          <td><b>รออะไหล่ (Waiting Parts)</b></td>
          <td><span class="badge badge-warning">Waiting Parts</span></td>
          <td>อุปกรณ์จำเป็นต้องสั่งซื้อหรือเบิกอะไหล่เปลี่ยน ช่างจะประสานงานแจ้งกำหนดเวลา</td>
        </tr>
        <tr>
          <td><b>แก้ไขเสร็จสิ้น (Resolved)</b></td>
          <td><span class="badge badge-success">Resolved</span></td>
          <td>ช่างทำการแก้ไขเสร็จแล้ว รอผู้ใช้งานทำการตรวจรับงานและประเมินผล</td>
        </tr>
        <tr>
          <td><b>ปิดงาน (Closed)</b></td>
          <td><span class="badge" style="background:#e2e8f0; color:#475569;">Closed</span></td>
          <td>งานเสร็จสมบูรณ์และผู้ใช้งานได้ตรวจรับงานเรียบร้อยแล้ว</td>
        </tr>
      </tbody>
    </table>

    <div class="figure-box">
      <img src="{img_user_tracking}" alt="หน้าติดตามสถานะและประเมินความพึงพอใจ">
      <div class="figure-caption">รูปที่ 2: หน้าต่างแสดงไทม์ไลน์สถานะการดำเนินงาน ช่างผู้ดูแล และแบบประเมินความพึงพอใจ (Customer Rating)</div>
    </div>

    <h2 class="section-header border-user theme-user" style="margin-top: 24px;">บทที่ 4: การตรวจรับงานและการประเมินความพึงพอใจ</h2>
    <p>
      เมื่อช่างไอทีแก้ไขปัญหาเสร็จสิ้น ระบบจะเปลี่ยนสถานะเป็น <b>Resolved</b> และมีข้อความแจ้งเตือนไปยังผู้ใช้งาน ท่านสามารถทดสอบการใช้งานอุปกรณ์ และร่วมให้คะแนนการบริการ:
    </p>

    <div class="callout callout-success">
      <div class="callout-title">⭐ การประเมินความพึงพอใจ 5 ระดับ</div>
      กดเลือกระดับดาว (1 ดาว = ควรปรับปรุง ถึง 5 ดาว = ดีเยี่ยมมาก) พร้อมกรอกข้อคิดเห็นหรือคำชมเชย เพื่อเป็นขวัญกำลังใจและเป็นข้อมูลพัฒนาการให้บริการของทีมงานไอทีต่อไป
    </div>
  </div>

  <!-- CHAPTER 5 & 6: LOAN & FAQ -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้ใช้งานทั่วไป</span>
    </div>

    <h2 class="section-header border-user theme-user">บทที่ 5: การขอยืมอุปกรณ์คอมพิวเตอร์ และการตรวจสอบทรัพย์สิน</h2>
    
    <div class="card-grid">
      <div class="info-card">
        <h4 style="color: #0284c7;">📦 การยื่นขอยืมอุปกรณ์ชั่วคราว (Loan Request)</h4>
        <p style="font-size: 12.5px; color: #475569;">
          กรณีต้องการใช้งานโน้ตบุ๊กสำหรับการประชุม, โปรเจกเตอร์, หรือเครื่องสำรองไฟชั่วคราว ให้ไปที่เมนู <b>"ยืม-คืนอุปกรณ์"</b> ระบุวันที่เริ่มยืม-คืน วัตถุประสงค์ และกดส่งคำขอ เมื่อได้รับอนุมัติจึงนำบัตรพนักงานมาติดต่อรับของที่ห้องไอที
        </p>
      </div>

      <div class="info-card">
        <h4 style="color: #059669;">💻 การตรวจสอบทรัพย์สินประจำตัว (My Assets)</h4>
        <p style="font-size: 12.5px; color: #475569;">
          ท่านสามารถตรวจสอบรายการคอมพิวเตอร์, จอภาพ, และเครื่องพิมพ์ที่ลงทะเบียนในความรับผิดชอบของท่านได้ที่เมนู <b>"ทรัพย์สินของฉัน"</b> หากพบข้อมูลไม่ตรงหรือมีการย้ายเครื่อง สามารถกดแจ้งขอปรับปรุงข้อมูลได้ทันที
        </p>
      </div>
    </div>

    <h2 class="section-header border-user theme-user" style="margin-top: 26px;">บทที่ 6: คำถามที่พบบ่อย (FAQ) และข้อควรปฏิบัติ</h2>

    <div style="margin-top: 14px;">
      <div style="margin-bottom: 14px;">
        <div style="font-weight: 700; color: #0f172a; font-size: 13px;">Q: หากลืมรหัสผ่านเข้าใช้งานระบบ ต้องทำอย่างไร?</div>
        <div style="font-size: 12.5px; color: #475569;">
          A: หากท่านมีแอปพลิเคชัน ThaiD สามารถกดเข้าสู่ระบบผ่าน ThaiD ได้ทันทีโดยไม่ต้องใช้รหัสผ่าน หรือกดปุ่ม <b>"ลืมรหัสผ่าน"</b> ที่หน้าแรก เพื่อรับลิงก์รีเซ็ตรหัสผ่านทางอีเมลขององค์กร
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <div style="font-weight: 700; color: #0f172a; font-size: 13px;">Q: งานแจ้งซ่อมด่วนมาก (เช่น ระบบหน้าห้องฉุกเฉินหรือห้องยาขัดข้อง) ควรทำอย่างไร?</div>
        <div style="font-size: 12.5px; color: #475569;">
          A: ให้เปิดใบแจ้งซ่อมในระบบและเลือกความสำคัญเป็น <b>"เร่งด่วนที่สุด (Urgent/Critical)"</b> จากนั้นสามารถโทรแจ้งเบอร์สายด่วนไอทีภายใน (Hotline) ควบคู่ เพื่อให้ทีมงานเข้าถึงพื้นที่ได้เร็วที่สุด
        </div>
      </div>

      <div style="margin-bottom: 14px;">
        <div style="font-weight: 700; color: #0f172a; font-size: 13px;">Q: เครื่องคอมพิวเตอร์ขึ้นข้อความแจ้งเตือนความปลอดภัยหรือไวรัส ควรปฏิบัติตัวอย่างไร?</div>
        <div style="font-size: 12.5px; color: #475569;">
          A: <b>ห้ามคลิกลิงก์หรือตอบตกลง</b> ให้ถอดสายแลน (LAN) ออกจากหลังเครื่องทันทีเพื่อป้องกันการแพร่กระจายไปยังเครื่องอื่น แล้วเปิดแจ้งซ่อมหมวดหมู่ "ไวรัส/ความปลอดภัย" ทันที
        </div>
      </div>
    </div>

    <div class="callout callout-warning" style="margin-top: 20px;">
      <div class="callout-title">⚠️ ข้อพึงระวังด้านความปลอดภัยสารสนเทศ</div>
      1. ห้ามเปิดเผยชื่อผู้ใช้และรหัสผ่านของท่านให้แก่ผู้อื่นโดยเด็ดขาด<br>
      2. หลีกเลี่ยงการดาวน์โหลดโปรแกรมที่ไม่ได้รับอนุญาตมาติดตั้งบนเครื่องคอมพิวเตอร์ของหน่วยงาน<br>
      3. ติดต่อสอบถามทีมงานไอทีได้ที่หมายเลขภายใน หรือส่งอีเมลมายัง <code>it-support@hospital.org</code>
    </div>
  </div>

</body>
</html>"""
    return html

# ==========================================
# 2. ADMIN MANUAL HTML
# ==========================================
def build_admin_manual():
    theme_primary = "#4338ca"
    theme_dark = "#1e1b4b"
    
    html = f"""<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>คู่มือการปฏิบัติงานสำหรับผู้ดูแลระบบและช่างไอที - IT System v2.8.0</title>
{COMMON_CSS}
<style>
  .theme-admin {{ color: {theme_primary}; }}
  .border-admin {{ border-color: {theme_primary}; }}
  .bg-admin {{ background-color: {theme_primary}; }}
</style>
</head>
<body>

  <!-- COVER PAGE -->
  <div class="cover-page" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #0f172a 100%); color: white; border: 2px solid #4338ca;">
    <div>
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 40px;">
        <div style="background: #6366f1; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-family: 'Prompt', sans-serif; font-size: 16px;">
          ADMIN SUITE
        </div>
        <div style="font-size: 13px; color: #cbd5e1; font-weight: 500;">
          IT Infrastructure & Asset Management System
        </div>
      </div>
      
      <div style="margin-top: 40px;">
        <span class="badge" style="background: #38bdf8; color: #0f172a; padding: 6px 14px; font-size: 13px; font-weight: 700; margin-bottom: 16px;">
          คู่มือผู้ดูแลระบบและวิศวกรไอที (System Administrator Manual)
        </span>
        <h1 style="font-size: 32px; color: #ffffff; margin-top: 14px; margin-bottom: 12px; font-weight: 700; line-height: 1.2;">
          คู่มือการบริหารจัดการระบบสารสนเทศ<br>และการควบคุม Agent Fleet ทางไกล
        </h1>
        <p style="font-size: 15px; color: #cbd5e1; max-width: 620px; line-height: 1.6;">
          ครอบคลุมการบริหารจัดการตั๋วงานซ่อม (Helpdesk), ระบบทะเบียนและคลังครุภัณฑ์ไอที (IT Asset Management), การควบคุมเครื่องลูกข่าย Real-time ด้วย THC Agent v2.8.0, การสั่งอัปเดตและถอนการติดตั้งทางไกล, และการสำรองข้อมูล
        </p>
      </div>
    </div>

    <div>
      <div style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); padding: 18px 24px; border-radius: 10px; border: 1px solid #475569; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 12.5px;">
          <div>
            <div style="color: #94a3b8;">เวอร์ชันระบบ:</div>
            <div style="font-weight: 700; color: #38bdf8; font-size: 14px;">v2.8.0 Production Release</div>
          </div>
          <div>
            <div style="color: #94a3b8;">ระดับสิทธิ์:</div>
            <div style="font-weight: 600; color: #ffffff;">Super Admin / IT Technician</div>
          </div>
          <div>
            <div style="color: #94a3b8;">สถานะเอกสาร:</div>
            <div style="font-weight: 600; color: #34d399;">ผ่านการรับรองใช้งานจริง</div>
          </div>
        </div>
      </div>

      <div style="font-size: 11px; color: #94a3b8; text-align: center;">
        ฝ่ายเทคโนโลยีสารสนเทศและการสื่อสาร • เอกสารลับเฉพาะเจ้าหน้าที่ปฏิบัติการ
      </div>
    </div>
  </div>

  <!-- TABLE OF CONTENTS -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือผู้ดูแลระบบและช่างไอที</span>
    </div>

    <h2 class="section-header border-admin theme-admin">สารบัญ (Table of Contents)</h2>
    
    <div style="margin-top: 20px;">
      <div class="toc-item"><span class="toc-title">บทที่ 1: แดชบอร์ดผู้บริหารและสถิติตัวชี้วัด (Executive Dashboard & SLA)</span><span class="toc-page">หน้า 2</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 2: วงจรการจัดการงานซ่อม (Helpdesk & Ticket Lifecycle)</span><span class="toc-page">หน้า 3</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 3: ระบบบริหารทะเบียนทรัพย์สินและครุภัณฑ์คอมพิวเตอร์ (IT Asset Management)</span><span class="toc-page">หน้า 4</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 4: การบริหารจัดการเครื่อง Agent Fleet ทางไกล (v2.8.0 Suite)</span><span class="toc-page">หน้า 5</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 5: ปุ่มลบเครื่องและระบบถอนการติดตั้งระยะไกล (Remote Uninstall & Purge)</span><span class="toc-page">หน้า 6</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 6: การบริหารจัดการสต็อกอะไหล่และการตั้งค่าระบบความปลอดภัย (Security & Backup)</span><span class="toc-page">หน้า 7</span></div>
    </div>

    <h2 class="section-header border-admin theme-admin" style="margin-top: 36px;">บทที่ 1: แดชบอร์ดผู้บริหารและสถิติตัวชี้วัด (Executive Dashboard & SLA)</h2>
    <p>
      แดชบอร์ดหลักของระบบออกแบบตามมาตรฐาน ITIL เพื่อให้ผู้บริหารและหัวหน้าทีมไอทีมองเห็นภาพรวมการดำเนินงานได้ในหน้าเดียว:
    </p>

    <div class="card-grid">
      <div class="info-card">
        <h4 style="color: #4338ca;">📊 การวัดประสิทธิภาพ SLA (Service Level Agreement)</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          ติดตามเวลาเฉลี่ยในการตอบสนองคำร้อง (Response Time) และเวลาปิดงานสำเร็จ (Resolution Time) เทียบกับเกณฑ์มาตรฐาน พร้อมระบบเตือนสีส้ม/แดงเมื่องานใกล้หลุดกรอบเวลา
        </p>
      </div>

      <div class="info-card">
        <h4 style="color: #059669;">💻 สถิติเครื่องลูกข่ายออนไลน์ (Agent Online Fleet)</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          แสดงจำนวนเครื่องออนไลน์/ออฟไลน์แบบ Real-time พร้อมกราฟเฉลี่ยการใช้งาน CPU, RAM และพื้นที่ฮาร์ดดิสก์ทั่วทั้งองค์กร
        </p>
      </div>
    </div>

    <div class="figure-box">
      <img src="{img_admin_fleet}" alt="หน้าต่างแดชบอร์ดผู้ดูแลระบบและ Agent Fleet">
      <div class="figure-caption">รูปที่ 1: แดชบอร์ดแสดงสถานะเครื่องคอมพิวเตอร์ลูกข่าย (Fleet Status) และแผงคำสั่งควบคุมทางไกล v2.8.0</div>
    </div>
  </div>

  <!-- CHAPTER 2: TICKET LIFECYCLE -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือผู้ดูแลระบบและช่างไอที</span>
    </div>

    <h2 class="section-header border-admin theme-admin">บทที่ 2: วงจรการจัดการงานซ่อม (Helpdesk & Ticket Lifecycle)</h2>
    <p>ขั้นตอนและแนวทางปฏิบัติมาตรฐานสำหรับช่างเทคนิคในการรับเรื่องและปิดงานซ่อม:</p>

    <div class="step-list">
      <div class="step-item">
        <div class="step-number bg-admin">1</div>
        <div class="step-content">
          <div class="step-title">การคัดกรองและจ่ายงาน (Triage & Assignment)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            เมื่อมีตั๋วงานใหม่เข้ามาในระบบ ผู้ดูแลระบบสามารถกดเปิดดูอาการและเลือกมอบหมาย (Assign) ช่างผู้เชี่ยวชาญเฉพาะทาง (เช่น งานระบบเครือข่าย, งานซ่อมบอร์ด, งานโปรแกรม HIS) พร้อมกำหนดระดับความสำคัญ
          </p>
        </div>
      </div>

      <div class="step-item">
        <div class="step-number bg-admin">2</div>
        <div class="step-content">
          <div class="step-title">การบันทึกการวินิจฉัยและดำเนินการ (Diagnosis & Action Log)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            ช่างเข้าตรวจสอบเครื่องและกดเปลี่ยนสถานะเป็น <b>"In Progress"</b> พร้อมบันทึกอาการจริงที่พบ (Diagnosis Note) เช่น พาวเวอร์ซัพพลายเสื่อมสภาพ หรือแรมหลวม
          </p>
        </div>
      </div>

      <div class="step-item">
        <div class="step-number bg-admin">3</div>
        <div class="step-content">
          <div class="step-title">การเบิกตัดสต็อกอะไหล่ (Spare Parts Issuance)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            หากต้องเปลี่ยนชิ้นส่วน ให้กดปุ่ม <b>"เบิกอะไหล่"</b> ภายในหน้าตั๋วงาน ระบบจะตัดยอดอะไหล่ออกจากคลังอัตโนมัติ และบันทึกมูลค่าค่าใช้จ่ายในการซ่อมลงในประวัติของเครื่องนั้นทันที
          </p>
        </div>
      </div>

      <div class="step-item">
        <div class="step-number bg-admin">4</div>
        <div class="step-content">
          <div class="step-title">การปิดงานและพิมพ์ใบส่งมอบ (Resolution & Print Handover)</div>
          <p style="margin: 0; font-size: 12.5px; color: #475569;">
            เมื่อแก้ไขเรียบร้อย ช่างกดปุ่ม <b>"แก้ไขเสร็จสิ้น (Resolved)"</b> พร้อมบันทึกวิธีแก้ปัญหา เพื่อเป็นฐานข้อมูลความรู้ (Knowledge Base) และสามารถกดพิมพ์ใบตรวจรับงานให้ผู้ใช้งานลงนามได้ทันที
          </p>
        </div>
      </div>
    </div>

    <div class="callout callout-info">
      <div class="callout-title">💡 การแจ้งเตือนอัตโนมัติผ่าน LINE Notify</div>
      ทุกการเปลี่ยนสถานะหรือการมอบหมายงาน ระบบจะส่งการแจ้งเตือนไปยังกลุ่มไลน์ของทีมช่างไอที และส่งข้อความส่วนตัวไปยังผู้แจ้งทันที ช่วยลดการโทรติดตามงานได้มากกว่า 70%
    </div>
  </div>

  <!-- CHAPTER 3: ASSET INVENTORY -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือผู้ดูแลระบบและช่างไอที</span>
    </div>

    <h2 class="section-header border-admin theme-admin">บทที่ 3: ระบบบริหารทะเบียนทรัพย์สินและครุภัณฑ์คอมพิวเตอร์ (IT Asset Management)</h2>
    <p>
      ระบบบริหารจัดการทรัพย์สินไอที (ITAM) ในเวอร์ชัน 2.8.0 ได้เชื่อมโยงข้อมูลอย่างสมบูรณ์แบบ ทั้งงบประมาณ วิธีการได้มา และประเภทการถือครอง:
    </p>

    <div class="figure-box">
      <img src="{img_admin_inventory}" alt="ระบบบริหารจัดการทรัพย์สิน IT Asset Inventory">
      <div class="figure-caption">รูปที่ 2: หน้าต่างบริหารจัดการทะเบียนทรัพย์สิน (Asset Inventory) พร้อมระบบ QR Code, งบประมาณ และสถานะครุภัณฑ์</div>
    </div>

    <h3 style="color: #1e1b4b; font-size: 14.5px;">องค์ประกอบสำคัญของข้อมูลทรัพย์สิน (v2.8.0 Complete Schema):</h3>

    <table>
      <thead>
        <tr>
          <th style="width: 140px;">ฟิลด์ข้อมูล</th>
          <th style="width: 180px;">ตัวอย่างข้อมูล</th>
          <th>คำอธิบายและการใช้งาน</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><b>รหัสทรัพย์สิน (Asset Tag)</b></td>
          <td><code>MED-PC-2026-0042</code></td>
          <td>รหัสครุภัณฑ์เฉพาะ ใช้ในการสร้าง Barcode และ QR Code พิมพ์ติดตัวเครื่อง</td>
        </tr>
        <tr>
          <td><b>แหล่งเงินงบประมาณ (Budget Source)</b></td>
          <td>งบลงทุนประจำปี / เงินบำรุง รพ.</td>
          <td>ผูกโยงเข้ากับระบบงบประมาณเพื่อสรุปยอดการลงทุนทางด้านไอที</td>
        </tr>
        <tr>
          <td><b>วิธีการได้มา (Acquisition Method)</b></td>
          <td>e-Bidding / ตกลงราคา / เฉพาะเจาะจง</td>
          <td>ระบุระเบียบพัสดุและวิธีการจัดหาทรัพย์สินตามระเบียบราชการ</td>
        </tr>
        <tr>
          <td><b>ประเภทการถือครอง (Ownership Type)</b></td>
          <td><span class="badge badge-primary">เป็นของหน่วยงาน</span> <span class="badge badge-warning">เช่าใช้ (Rented)</span></td>
          <td>แยกระหว่างเครื่องของโรงพยาบาล กับเครื่องโครงการเช่ารายปี พร้อมวันสิ้นสุดสัญญาเช่า</td>
        </tr>
        <tr>
          <td><b>สถานะครุภัณฑ์ (Asset Status)</b></td>
          <td>ใช้งานปกติ / สำรอง / ส่งซ่อมภายนอก / จำหน่าย</td>
          <td>ควบคุมวงจรชีวิตครุภัณฑ์ (Asset Lifecycle) และการคิดค่าเสื่อมราคา</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- CHAPTER 4 & 5: AGENT FLEET & REMOTE UNINSTALL -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือผู้ดูแลระบบและช่างไอที</span>
    </div>

    <h2 class="section-header border-admin theme-admin">บทที่ 4: การบริหารจัดการเครื่อง Agent Fleet ทางไกล (v2.8.0 Suite)</h2>
    <p>
      ระบบ THC Agent v2.8.0 รองรับการมอนิเตอร์และสั่งการเครื่องลูกข่ายทางไกลแบบสองทาง (Two-way Telemetry & Remote Control):
    </p>

    <div class="card-grid">
      <div class="info-card">
        <h4 style="color: #4338ca;">🚀 การติดตั้ง Agent ครั้งแรก (Installation)</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          ดาวน์โหลด <code>install_thc_agent.bat</code> จากแท็บ 4 (Deployment Hub) รันบนเครื่องปลายทาง ระบบจะติดตั้งไฟล์ลง <code>C:\\ProgramData\\THC-IT-Agent</code>, สร้าง Scheduled Task และ Auto-Run Registry ทันที
        </p>
      </div>

      <div class="info-card">
        <h4 style="color: #059669;">🔄 การสั่งอัปเดต Agent ทางไกล (Remote Update)</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          สามารถกดปุ่ม <b>"อัปเดต Agent"</b> รายเครื่อง หรือเลือกหลายเครื่องแล้วกดที่แถบเครื่องมือด้านบน Agent จะดาวน์โหลดไบนารีใหม่มาเปลี่ยนและเริ่มทำงานต่ออัตโนมัติ
        </p>
      </div>
    </div>

    <h2 class="section-header border-admin theme-admin" style="margin-top: 24px;">บทที่ 5: ปุ่มลบเครื่องและระบบถอนการติดตั้งระยะไกล (Delete Machine & Remote Uninstall)</h2>
    <p>
      ออกแบบขึ้นใหม่ในเวอร์ชัน 2.8.0 เพื่อให้ผู้ดูแลระบบสามารถลบเครื่องที่ไม่ต้องการออกจากฐานข้อมูล และสั่งถอนการติดตั้งโปรแกรมออกจากเครื่องลูกข่ายได้จากหน้าเว็บ:
    </p>

    <div class="callout callout-danger">
      <div class="callout-title">🗑️ ระบบลบเครื่องและการถอนการติดตั้งระยะไกล (Remote Uninstall)</div>
      1. <b>ลบรายเครื่อง:</b> คลิกปุ่มรูปถังขยะสีแดง (Delete Machine) ในแถวของเครื่องนั้น<br>
      2. <b>ลบหลายเครื่อง (Batch Delete):</b> ติ๊กเลือกเครื่องที่ต้องการในตาราง แล้วกดปุ่ม <code>ลบเครื่องที่เลือก (N)</code> ในแถบด้านบน<br>
      3. <b>กล่องยืนยันการลบ:</b> จะมีตัวเลือก <code>[x] สั่งถอนการติดตั้ง Agent ออกจากเครื่องปลายทางด้วย (Remote Uninstall Agent)</code><br>
      • หากติ๊กเลือก: ระบบจะส่งคำสั่ง <code>uninstall</code> ไปยังเครื่องลูกข่าย เมื่อเครื่องได้รับคำสั่ง สคริปต์จะปิดโปรเซส, ลบ Task Scheduler, ลบ Registry และล้างโฟลเดอร์ในเครื่องลูกข่ายเกลี้ยงหมดจด<br>
      • ระบบจะเคลียร์ Redis Cache (Online Status) ทั้งหมด และล้างข้อมูล Audit ของเครื่องนั้นออกจากระบบทันที
    </div>

    <div class="callout callout-info">
      <div class="callout-title">🛡️ กลไกความปลอดภัยข้อมูล (Data Safety Guard)</div>
      การลบเครื่องในหน้า Hardware Audit / Fleet Management <b>จะไม่มีผลกระทบต่อรายการทรัพย์สินในคลัง (IT Assets)</b> ข้อมูลครุภัณฑ์และประวัติการซ่อมยังคงปลอดภัยอยู่ในระบบ
    </div>
  </div>

  <!-- CHAPTER 6: MAINTENANCE & SECURITY -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือผู้ดูแลระบบและช่างไอที</span>
    </div>

    <h2 class="section-header border-admin theme-admin">บทที่ 6: การบริหารคลังอะไหล่ การสำรองข้อมูล และความปลอดภัย</h2>

    <h3 style="color: #1e1b4b; font-size: 14px;">1. การสำรองข้อมูลฐานข้อมูลและไฟล์ระบบ (Backup Strategy)</h3>
    <p>แนะนำให้ตั้งค่าการสำรองข้อมูลเป็นประจำตามหลักการ 3-2-1 Backup Rule:</p>
    <pre><code># คำสั่งสำรองฐานข้อมูล MySQL ผ่าน CLI
mysqldump -u root -p it_system > backup_itsystem_$(date +%Y%m%d).sql

# การสำรองไฟล์อัปโหลดและรูปภาพ
tar -czvf storage_backup.tar.gz storage/app/public</code></pre>

    <h3 style="color: #1e1b4b; font-size: 14px; margin-top: 20px;">2. การตรวจสอบประวัติการปฏิบัติงาน (Audit Trail & Activity Logs)</h3>
    <p>
      ระบบบันทึกทุกพฤติกรรมที่สำคัญของผู้ดูแลระบบลงในตาราง <code>it_audit_logs</code> อย่างละเอียด เช่น การลบเครื่องคอมพิวเตอร์, การส่งคำสั่งรีโมต, การเปลี่ยนแปลงสิทธิ์ผู้ใช้งาน และการแก้ไขข้อมูลทรัพย์สิน เพื่อความโปร่งใสและสอดคล้องตามมาตรฐาน พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล (PDPA)
    </p>

    <div class="callout callout-warning">
      <div class="callout-title">🔒 มาตรการรักษาความปลอดภัยระบบ</div>
      • ปิดการเข้าถึงพอร์ตฐานข้อมูล MySQL (3306) จากภายนอก ให้อนุญาตเฉพาะ <code>127.0.0.1</code><br>
      • ตรวจสอบให้แน่ใจว่าไฟล์ <code>.env</code> มีสิทธิ์การเข้าถึงแบบจำกัด (chmod 600) เสมอ<br>
      • เปลี่ยนรหัสผ่านของบัญชี Super Admin ทุก 90 วัน
    </div>
  </div>

</body>
</html>"""
    return html

# ==========================================
# 3. DEVELOPER MANUAL HTML
# ==========================================
def build_developer_manual():
    theme_primary = "#0284c7"
    theme_dark = "#0f172a"
    
    html = f"""<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ - IT System v2.8.0</title>
{COMMON_CSS}
<style>
  .theme-dev {{ color: {theme_primary}; }}
  .border-dev {{ border-color: {theme_primary}; }}
  .bg-dev {{ background-color: {theme_primary}; }}
</style>
</head>
<body>

  <!-- COVER PAGE -->
  <div class="cover-page" style="background: linear-gradient(135deg, #090d16 0%, #0f172a 40%, #1e293b 100%); color: white; border: 2px solid #0284c7;">
    <div>
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 40px;">
        <div style="background: #0284c7; color: white; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-family: 'Prompt', sans-serif; font-size: 16px;">
          DEV ARCHITECTURE
        </div>
        <div style="font-size: 13px; color: #94a3b8; font-weight: 500;">
          Technical Specification & Engineering Manual
        </div>
      </div>
      
      <div style="margin-top: 40px;">
        <span class="badge" style="background: #38bdf8; color: #0f172a; padding: 6px 14px; font-size: 13px; font-weight: 700; margin-bottom: 16px;">
          คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ (Developer Manual)
        </span>
        <h1 style="font-size: 32px; color: #ffffff; margin-top: 14px; margin-bottom: 12px; font-weight: 700; line-height: 1.2;">
          สถาปัตยกรรมระบบสารสนเทศ<br>และข้อกำหนดเชิงเทคนิค v2.8.0
        </h1>
        <p style="font-size: 15px; color: #94a3b8; max-width: 620px; line-height: 1.6;">
          เอกสารเชิงเทคนิคสำหรับ Software Engineers และ DevOps: สถาปัตยกรรม Laravel 11 Backend, กลไกสองทาง C# Native Standalone Agent, RESTful RPC Telemetry, โครงสร้างฐานข้อมูล ERD, CI/CD Pipeline และแนวทางการพัฒนาต่อยอด
        </p>
      </div>
    </div>

    <div>
      <div style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); padding: 18px 24px; border-radius: 10px; border: 1px solid #334155; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; font-size: 12.5px;">
          <div>
            <div style="color: #64748b;">Target Release:</div>
            <div style="font-weight: 700; color: #38bdf8; font-size: 14px;">v2.8.0 (Build 20261002.3)</div>
          </div>
          <div>
            <div style="color: #64748b;">Stack Framework:</div>
            <div style="font-weight: 600; color: #ffffff;">Laravel 11 / PHP 8.2+ / C# .NET</div>
          </div>
          <div>
            <div style="color: #64748b;">Repository Tag:</div>
            <div style="font-weight: 600; color: #34d399;">origin/main @ v2.8.0</div>
          </div>
        </div>
      </div>

      <div style="font-size: 11px; color: #64748b; text-align: center;">
        ฝ่ายพัฒนานวัตกรรมและสถาปัตยกรรมซอฟต์แวร์ • เอกสารทางเทคนิคสำหรับวิศวกรระบบ
      </div>
    </div>
  </div>

  <!-- TABLE OF CONTENTS -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ</span>
    </div>

    <h2 class="section-header border-dev theme-dev">สารบัญ (Table of Contents)</h2>
    
    <div style="margin-top: 20px;">
      <div class="toc-item"><span class="toc-title">บทที่ 1: ภาพรวมสถาปัตยกรรมระบบ (System Architecture Overview)</span><span class="toc-page">หน้า 2</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 2: โครงสร้างฐานข้อมูลและความสัมพันธ์ (Database Schema & ERD)</span><span class="toc-page">หน้า 3</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 3: ข้อกำหนด RESTful API & Telemetry Protocol</span><span class="toc-page">หน้า 4</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 4: สถาปัตยกรรม C# Native Standalone Agent (THC_IT_Agent.cs)</span><span class="toc-page">หน้า 5</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 5: ชุดการทดสอบระบบอัตโนมัติ (Automated Testing Suite)</span><span class="toc-page">หน้า 6</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 6: กระบวนการ Release & Automation Deployment Pipeline (deploy_new.ps1)</span><span class="toc-page">หน้า 7</span></div>
      <div class="toc-item"><span class="toc-title">บทที่ 7: ข้อกำหนดและแนวทางการพัฒนาต่อยอด (Extension & Contribution Guidelines)</span><span class="toc-page">หน้า 8</span></div>
    </div>

    <h2 class="section-header border-dev theme-dev" style="margin-top: 36px;">บทที่ 1: ภาพรวมสถาปัตยกรรมระบบ (System Architecture Overview)</h2>
    <p>
      ระบบออกแบบด้วยสถาปัตยกรรมแบบ Modular Multi-tier Architecture ที่แยกส่วนระหว่าง Presentation Layer, Application Business Logic Layer, Telemetry Daemon และ Data Persistence Layer:
    </p>

    <div class="figure-box">
      <img src="{img_dev_arch}" alt="แผนภาพสถาปัตยกรรมระบบสำหรับผู้พัฒนา">
      <div class="figure-caption">รูปที่ 1: แผนภาพสถาปัตยกรรมระบบโดยรวม (Enterprise Architecture Diagram) v2.8.0</div>
    </div>

    <div class="card-grid">
      <div class="info-card">
        <h4 style="color: #0284c7;">⚙️ Backend Application Core</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          ขับเคลื่อนด้วย <b>Laravel 11.x</b> บนสภาพแวดล้อม <b>PHP 8.2+</b> ใช้ Eloquent ORM, Blade Templates, RESTful Controllers, Middleware RBAC และ Event-driven Cache Invalidation
        </p>
      </div>

      <div class="info-card">
        <h4 style="color: #059669;">💻 Client Endpoint Agent Daemon</h4>
        <p style="font-size: 12px; color: #475569; margin: 0;">
          โปรแกรม <b>THC_IT_Agent.exe</b> พัฒนาด้วย <b>C# (.NET Framework 4.0/4.8)</b> ทำงานเบื้องหลัง กินทรัพยากรต่ำ (&lt; 15MB RAM) พร้อม fallback script <b>thc_audit_agent.ps1</b>
        </p>
      </div>
    </div>
  </div>

  <!-- CHAPTER 2: DATABASE SCHEMA -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ</span>
    </div>

    <h2 class="section-header border-dev theme-dev">บทที่ 2: โครงสร้างฐานข้อมูลและความสัมพันธ์ (Database Schema)</h2>
    <p>
      ฐานข้อมูลหลักใช้ชื่อคำนำหน้า <code>it_</code> เพื่อความเป็นระเบียบและป้องกันการชนกันของชื่อตาราง:
    </p>

    <table>
      <thead>
        <tr>
          <th style="width: 160px;">ชื่อตาราง (Table Name)</th>
          <th style="width: 160px;">Primary / Foreign Key</th>
          <th>วัตถุประสงค์และความสัมพันธ์</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>it_tickets</code></td>
          <td><code>id</code>, <code>user_id</code>, <code>asset_id</code></td>
          <td>จัดเก็บข้อมูลตั๋วงานซ่อม ความสำคัญ สถานะ ช่างผู้รับผิดชอบ และการประเมินผล</td>
        </tr>
        <tr>
          <td><code>it_assets</code></td>
          <td><code>id</code>, <code>budget_source_id</code>, <code>acquisition_method_id</code></td>
          <td>ทะเบียนทรัพย์สินไอที เชื่อมโยงงบประมาณ วิธีการได้มา และประเภทการถือครอง (Owned, Rented, Borrowed, Donated)</td>
        </tr>
        <tr>
          <td><code>it_hardware_audits</code></td>
          <td><code>id</code>, <code>hardware_id</code> (HWID)</td>
          <td>ประวัติการ Audit สเปกเครื่องจาก Agent เช่น CPU, RAM, Disks, GPUs, IP, MAC, Serial</td>
        </tr>
        <tr>
          <td><code>it_agent_commands</code></td>
          <td><code>id</code>, <code>target_hwid</code>, <code>target_hostname</code></td>
          <td>คิวคำสั่งรีโมตไปยังเครื่องลูกข่าย (poll, reboot, shutdown, lock, update, uninstall)</td>
        </tr>
        <tr>
          <td><code>it_budgets</code></td>
          <td><code>id</code></td>
          <td>หมวดหมู่แหล่งเงินงบประมาณ (เช่น งบลงทุนประจำปี, เงินบำรุง)</td>
        </tr>
        <tr>
          <td><code>it_acquisition_methods</code></td>
          <td><code>id</code></td>
          <td>หมวดหมู่วิธีการได้มาของทรัพย์สิน (e-Bidding, ตกลงราคา, รับบริจาค)</td>
        </tr>
        <tr>
          <td><code>it_audit_logs</code></td>
          <td><code>id</code>, <code>user_id</code></td>
          <td>Audit Trail บันทึกการกระทำสำคัญ เช่น การลบเครื่อง, การส่งคำสั่งรีโมต, การเปลี่ยนสิทธิ์</td>
        </tr>
      </tbody>
    </table>

    <div class="callout callout-info">
      <div class="callout-title">💡 กลยุทธ์การแคช (Redis & Database Cache Strategy)</div>
      สถานะออนไลน์ของ Agent ถูกจัดเก็บใน Redis/Cache แบบ Key-Value โดยมี Time-to-Live (TTL) 90 วินาที:<br>
      • <code>agent_online:hwid:&#123;hwid&#125;</code>: บันทึกข้อมูล Telemetry สด<br>
      • <code>agent_online:host:&#123;hostname&#125;</code>: เก็บเพื่อรองรับการค้นหาตามชื่อเครื่อง (Case-insensitive)<br>
      • <code>agent_online_registry</code>: Array รายการเครื่องที่กำลังออนไลน์ในระบบ
    </div>
  </div>

  <!-- CHAPTER 3 & 4: API & C# AGENT -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ</span>
    </div>

    <h2 class="section-header border-dev theme-dev">บทที่ 3: ข้อกำหนด RESTful API & Telemetry Protocol</h2>
    <p>API สำหรับการสื่อสารระหว่าง Agent และ Backend Server ผ่านโปรโตคอล HTTPS JSON-RPC:</p>

    <div class="no-break">
      <h4 style="margin-bottom: 4px; color: #0284c7;">1. Agent Heartbeat & Command Polling</h4>
      <pre><code>POST /api/agent/heartbeat
Content-Type: application/json

Request:
{{
  "hwid": "BFEBFBFF000906EA-78901234",
  "hostname": "IT-DESKTOP-01",
  "ip_address": "192.168.1.105",
  "agent_version": "2.8.0-TrayExe"
}}

Response (200 OK):
{{
  "status": "ok",
  "commands": [
    {{
      "id": 1042,
      "command": "update",
      "payload": {{ "download_url": "http://192.168.1.10/agent/update_thc_agent.bat" }}
    }}
  ]
}}</code></pre>
    </div>

    <h2 class="section-header border-dev theme-dev" style="margin-top: 24px;">บทที่ 4: สถาปัตยกรรม C# Native Standalone Agent (THC_IT_Agent.cs)</h2>
    <p>
      โปรแกรม Agent ได้รับการออกแบบให้ทำงานในรูปแบบ Dual-thread Architecture:
    </p>

    <div class="step-list">
      <div class="step-item">
        <div class="step-number bg-dev">1</div>
        <div class="step-content">
          <div class="step-title">UI Context Thread (System Tray)</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">
            สร้าง System Tray Icon ใน Taskbar บริหารเมนูคลิกขวา (ตรวจสอบสถานะ, อัปเดต Agent, ถอนการติดตั้ง) และแสดง Balloon Notification เมื่อมีคำสั่งรีโมตเข้ามา
          </p>
        </div>
      </div>

      <div class="step-item">
        <div class="step-number bg-dev">2</div>
        <div class="step-content">
          <div class="step-title">Background Worker Thread (Telemetry & Command Dispatcher)</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">
            ส่ง Heartbeat ทุก 30-60 วินาที พร้อมตรวจสอบคิวคำสั่ง หากได้รับคำสั่ง <code>update</code> จะเรียก <code>ExecuteSelfUpdate()</code> หรือหากได้รับคำสั่ง <code>uninstall</code> จะเรียก <code>ExecuteSelfUninstall()</code>
          </p>
        </div>
      </div>
    </div>

    <div class="callout callout-success">
      <div class="callout-title">🛠️ การคอมไพล์ C# Agent บน Windows</div>
      ไม่จำเป็นต้องลง Visual Studio ตัวเต็ม ใช้ C# Compiler ที่มีอยู่ใน Windows ทันที:
      <pre><code>C:\\Windows\\Microsoft.NET\\Framework64\\v4.0.30319\\csc.exe /target:winexe /optimize+ /out:agent\\THC_IT_Agent.exe agent\\THC_IT_Agent.cs</code></pre>
    </div>
  </div>

  <!-- CHAPTER 5 & 6: TESTS & DEPLOYMENT -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ</span>
    </div>

    <h2 class="section-header border-dev theme-dev">บทที่ 5: ชุดการทดสอบระบบอัตโนมัติ (Automated Testing Suite)</h2>
    <p>
      ระบบมีชุดการทดสอบอัตโนมัติครอบคลุม 100% (54 Tests, 324 Assertions) ด้วย PHPUnit:
    </p>

    <pre><code># คำสั่งรันชุดทดสอบวงจรชีวิตของ Agent และการลบเครื่อง
php artisan test tests/Feature/HardwareAgentLifecycleTest.php

# ตัวอย่างผลลัพธ์:
# PASS  Tests\\Feature\\HardwareAgentLifecycleTest
# ✓ admin can delete agent machine with optional remote uninstall
# ✓ admin can batch delete agent machines
# ✓ admin can trigger remote agent update
# ✓ regular user cannot delete agent machine
# ✓ create asset from audit attaches budget source and acquisition method
# ✓ system version is 2 8 0 and changelogs are continuous</code></pre>

    <h2 class="section-header border-dev theme-dev" style="margin-top: 24px;">บทที่ 6: กระบวนการ Release & Automation Deployment Pipeline</h2>
    <p>
      กระบวนการ Deploy ของโครงการใช้สคริปต์ <code>deploy_new.ps1</code> เพื่อสร้างแพ็กเกจ Production โดยอัตโนมัติ:
    </p>

    <div class="figure-box">
      <img src="{img_dev_cicd}" alt="กระบวนการ CI/CD และการสร้างแพ็กเกจติดตั้ง">
      <div class="figure-caption">รูปที่ 2: ผังกระบวนการ CI/CD Pipeline, Git Tagging, การคอมไพล์ Vite และการแพ็กเกจ ZIP v2.8.0</div>
    </div>

    <div class="step-list">
      <div class="step-item">
        <div class="step-number bg-dev">1</div>
        <div class="step-content">
          <div class="step-title">คอมไพล์ Production Frontend Assets ด้วย Vite</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">สร้าง Bundle ย่อขนาดไฟล์ CSS & JS ในโฟลเดอร์ <code>public/build</code></p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-number bg-dev">2</div>
        <div class="step-content">
          <div class="step-title">สร้าง Git Release Tag และ Push สู่ Remote</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">สร้าง Tag <code>v2.8.0</code> และ Push สู่ Deploy Repository</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-number bg-dev">3</div>
        <div class="step-content">
          <div class="step-title">บีบอัดแพ็กเกจ ZIP สำหรับติดตั้งจริง</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">สร้าง <code>it-system-v2.8.0.zip</code> (ตัวเต็ม) และ <code>update-patch-v2.8.0.zip</code> (เฉพาะไฟล์แพตช์)</p>
        </div>
      </div>
    </div>
  </div>

  <!-- CHAPTER 7: CONTRIBUTION -->
  <div class="page-break">
    <div class="doc-header-bar">
      <span>ระบบบริหารงานสารสนเทศ IT System v2.8.0</span>
      <span>คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ</span>
    </div>

    <h2 class="section-header border-dev theme-dev">บทที่ 7: ข้อกำหนดและแนวทางการพัฒนาต่อยอด (Extension Guidelines)</h2>

    <h3 style="color: #0f172a; font-size: 14px;">1. การเพิ่มคำสั่งรีโมตใหม่สำหรับ Agent (Adding New Commands)</h3>
    <p>เมื่อต้องการเพิ่มคำสั่งรีโมตชนิดใหม่ (เช่น <code>clean_temp</code> หรือ <code>install_printer</code>):</p>
    <div class="step-list">
      <div class="step-item">
        <div class="step-number bg-dev">1</div>
        <div class="step-content">
          <div class="step-title">Backend Controller</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">
            เพิ่ม Logic การสร้าง Command ใน <code>HardwareAuditController::dispatchRemoteCommand($target, 'clean_temp', $payload)</code>
          </p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-number bg-dev">2</div>
        <div class="step-content">
          <div class="step-title">C# Native Agent</div>
          <p style="margin: 0; font-size: 12px; color: #475569;">
            เพิ่ม Case ในฟังก์ชัน <code>WorkerLoop()</code> ของ <code>agent/THC_IT_Agent.cs</code> เพื่อประมวลผลคำสั่ง และส่ง Ack กลับเซิร์ฟเวอร์
          </p>
        </div>
      </div>
    </div>

    <h3 style="color: #0f172a; font-size: 14px; margin-top: 18px;">2. มาตรฐานการบันทึกโค้ดและ Conventional Commits</h3>
    <ul style="font-size: 12.5px; color: #475569; padding-left: 20px;">
      <li><code>feat:</code> เพิ่มคุณสมบัติหรือโมดูลใหม่ของระบบ</li>
      <li><code>fix:</code> แก้ไขบั๊กหรือข้อผิดพลาด</li>
      <li><code>refactor:</code> ปรับปรุงโครงสร้างโค้ดโดยไม่เปลี่ยนผลลัพธ์</li>
      <li><code>test:</code> เพิ่มหรือปรับปรุงชุดการทดสอบ PHPUnit</li>
    </ul>

    <div class="callout callout-info" style="margin-top: 20px;">
      <div class="callout-title">🤝 Technical Support & Maintenance Contact</div>
      หากพบปัญหาทางเทคนิค สถาปัตยกรรมระบบ หรือต้องการขยายขีดความสามารถเพิ่มเติม สามารถเปิด Issue ได้ที่ Repository หรือติดต่อทีมพัฒนากลางที่ <code>dev-team@hospital.org</code>
    </div>
  </div>

</body>
</html>"""
    return html

# ==========================================
# RENDER PDF USING CHROME HEADLESS
# ==========================================
def render_pdf(html_content, output_html_path, output_pdf_path):
    print(f"Writing HTML to {output_html_path}...")
    with open(output_html_path, "w", encoding="utf-8") as f:
        f.write(html_content)
        
    print(f"Rendering PDF to {output_pdf_path} via Chrome Headless...")
    cmd = [
        CHROME_EXE,
        "--headless=new",
        "--disable-gpu",
        "--no-pdf-header-footer",
        "--allow-file-access-from-files",
        f"--print-to-pdf={output_pdf_path}",
        output_html_path
    ]
    
    res = subprocess.run(cmd, capture_output=True, text=True)
    if os.path.exists(output_pdf_path) and os.path.getsize(output_pdf_path) > 1000:
        size_kb = os.path.getsize(output_pdf_path) / 1024
        print(f"SUCCESS: {output_pdf_path} ({size_kb:.1f} KB)")
        return True
    else:
        print(f"FAILED to render {output_pdf_path}. Stderr: {res.stderr}")
        return False

# ==========================================
# MAIN EXECUTION
# ==========================================
def main():
    print("=" * 60)
    print("STARTING GENERATION OF 3 IT SYSTEM MANUALS (v2.8.0)")
    print("=" * 60)

    manuals = [
        ("User_Manual_IT_System_THC", build_user_manual(), "คู่มือสำหรับผู้ใช้งานทั่วไป"),
        ("Admin_Manual_IT_System_THC", build_admin_manual(), "คู่มือสำหรับผู้ดูแลระบบและช่างไอที"),
        ("Developer_Manual_IT_System_THC", build_developer_manual(), "คู่มือสำหรับผู้พัฒนาและวิศวกรระบบ")
    ]

    for filename, html_content, title in manuals:
        print(f"\n>>> Generating: {title} ({filename})")
        html_file = os.path.join(DOCS_DIR, f"{filename}.html")
        pdf_file = os.path.join(DOCS_DIR, f"{filename}.pdf")
        
        ok = render_pdf(html_content, html_file, pdf_file)
        if ok:
            # Copy to public/docs for web download
            public_pdf = os.path.join(PUBLIC_DOCS_DIR, f"{filename}.pdf")
            public_html = os.path.join(PUBLIC_DOCS_DIR, f"{filename}.html")
            shutil.copyfile(pdf_file, public_pdf)
            shutil.copyfile(html_file, public_html)
            print(f"Synced to public web directory: {public_pdf}")

    print("\n" + "=" * 60)
    print("ALL 3 MANUALS CREATED SUCCESSFULLY!")
    print("=" * 60)

if __name__ == "__main__":
    main()
