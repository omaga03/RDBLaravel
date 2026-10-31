---
name: documentation-and-manuals
description: Standards, automated screenshot capture workflows, and HTML5/Markdown compilation procedures for maintaining README.md, System Acceptance Test Plans (UAT), and User Manuals (Volume 1 Frontend, Volume 2 Backend, and Researcher Quick-Start Cheatsheet) in docs/manuals/.
---

# Documentation & Manual Curator Skill (`documentation-and-manuals`)

Use this skill whenever updating `README.md`, adding or modifying system features that affect user workflows, updating user manuals (`docs/manuals/`), capturing or annotating UI screenshots (`docs/manuals/screenshots/`), or regenerating the standalone HTML5 documentation portal.

---

## 1. Scope of Managed Documentation Files

| ไฟล์เอกสาร | พาธในโปรเจกต์ | หน้าที่และขอบเขตเนื้อหา |
| :--- | :--- | :--- |
| **1. คู่มือการติดตั้งระบบ (Installation & Recovery Guide)** | `README.md` | คู่มือสำหรับผู้เริ่มต้นติดตั้งระบบจาก Git บน Laragon (`httpd`, `php-8.x`, `mariadb`), รายชื่อ PHP Extensions ที่ต้องเปิดใน `php.ini`, การนำเข้าฐานข้อมูล `rdbpcru` และคำสั่ง Artisan ดูแลระบบ |
| **2. สารบัญศูนย์รวมคู่มือ (Documentation Portal)** | `docs/manuals/index.html` | หน้าสารบัญหลักรูปแบบ HTML5 สำหรับคลิกเปิดอ่านคู่มือทุกเล่ม รองรับ Dark/Light Mode และการสั่งพิมพ์ |
| **3. เล่มที่ 1: คู่มือระบบหน้าบ้าน (Volume 1: Frontend Manual)** | `docs/manuals/VOLUME_1_FRONTEND_USER_MANUAL.md`<br>`docs/manuals/VOLUME_1_FRONTEND_USER_MANUAL.html` | คู่มือสำหรับประชาชน นักศึกษา และคณาจารย์ ครบ 8 บท (การสืบค้นอัจฉริยะ, โครงการวิจัย, สิทธิ์ไฟล์ 3 ระดับ, งานตีพิมพ์, การคัดลอก Citation APA 7/IEEE/MLA 9, ทรัพย์สินทางปัญญา, การใช้ประโยชน์, ทำเนียบนักวิจัย และแดชบอร์ด) |
| **4. เล่มที่ 2: คู่มือระบบหลังบ้าน (Volume 2: Backend Manual)** | `docs/manuals/VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.md`<br>`docs/manuals/VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html` | คู่มือสำหรับนักวิจัย ผู้ทรงคุณวุฒิ และผู้ดูแลระบบ ครบ 9 บท (Google SSO, RBAC & Impersonation, วงจรส่งร่าง-ตรวจรับ-อนุมัติ WF-01 ถึง WF-07, ระบบตรวจนับหน้า PDF & ปก `.webp`, Peer Review, ส่งออก UTF-8 BOM Excel, DSpace SAF และ Audit Logs) |
| **5. คู่มือฉบับย่อสำหรับนักวิจัย (Quick-Start Cheatsheet)** | `docs/manuals/RESEARCHER_QUICK_START_CHEATSHEET.md`<br>`docs/manuals/RESEARCHER_QUICK_START_CHEATSHEET.html` | แผ่นพับสรุปขั้นตอนเร่งด่วนภายใน 3 นาทีสำหรับอาจารย์และนักวิจัย (ล็อกอิน ➔ สร้างร่าง ➔ ส่งตรวจสอบ ➔ ขอปลดล็อกแก้ไข) |
| **6. แผนและรายงานผลการตรวจรับระบบ (UAT Report)** | `docs/manuals/SYSTEM_ACCEPTANCE_TEST_PLAN.md`<br>`docs/manuals/SYSTEM_ACCEPTANCE_TEST_PLAN.html` | ตารางรายการทดสอบระบบครบทุกโมดูลและบันทึกผลการรันชุดทดสอบอัตโนมัติพร้อมตารางลงนามตรวจรับ |
| **7. แผนแม่บทการจัดทำคู่มือ (Manual Master Plan)** | `docs/manuals/USER_MANUAL_DEVELOPMENT_PLAN.md`<br>`docs/manuals/USER_MANUAL_DEVELOPMENT_PLAN.html` | โครงสร้างสารบัญ มาตรฐานการจับภาพหน้าจอ และแผนการดำเนินงานจัดทำคู่มือ |
| **8. คลังภาพหน้าจอระบบจริง (Annotated Screenshots)** | `docs/manuals/screenshots/*.png` | ภาพหน้าจอจริงความละเอียด 1920x1080 (`frontend_01..10.png` และ `backend_01..16.png`) พร้อมกรอบสีแดงและตัวเลขกำกับขั้นตอน `[1]`, `[2]`, `[3]` |

---

## 2. Synchronization Rules (กฎการซิงก์ข้อมูลเอกสารให้ตรงกับโค้ดเสมอ)

1. **Dual-Format Parity (`.md` ↔ `.html`)**:
   - ทุกครั้งที่มีการแก้ไขเนื้อหาในไฟล์ `.md` ภายใน `docs/manuals/` **ต้องรันสคริปต์คอมไพล์เป็นไฟล์ `.html` เสมอ** เพื่อให้ไฟล์ `.md` และ `.html` มีเนื้อหาตรงกัน 100%
   - รันคำสั่งคอมไพล์ HTML:
     ```powershell
     php .agents/skills/documentation-and-manuals/scripts/build_html_manuals.php
     ```
2. **Real System Screenshots Only (ห้ามใช้ภาพจำลอง)**:
   - ภาพประกอบทั้งหมดใน `docs/manuals/screenshots/` ต้องจับภาพจากระบบจริงที่ความละเอียด `1920x1080`
   - ใช้กรอบเน้นจุดคลิกสีแดง (`#DC2626`) และวงกลมหมายเลขกำกับขั้นตอน (`[1]`, `[2]`, `[3]`) ด้วย PHP GD
   - เมื่อหน้าจอ UI เปลี่ยนแปลง สามารถรันสคริปต์จับภาพหน้าจอใหม่ได้ที่:
     ```powershell
     php .agents/skills/documentation-and-manuals/scripts/capture_manual_screenshots.php
     ```
3. **Editorial & Typography Standards**:
   - ใช้โทนสีวิชาการ Deep Academic Navy (`#1E3A5F`), Royal Blue (`#2563EB`), และ Citation Amber (`#D97706`)
   - ใช้ฟอนต์ `Prompt` สำหรับหัวข้อ และ `Sarabun` สำหรับเนื้อหา พร้อม `font-variant-numeric: tabular-nums` สำหรับตารางข้อมูล
   - วันที่ในตัวอย่างคู่มือต้องแสดงผลเป็นรูปแบบ **พ.ศ.** เสมอ
4. **`README.md` Beginner-Friendliness Rule**:
   - การแก้ไข `README.md` ต้องเขียนแบบ Step-by-Step สำหรับผู้ที่ไม่เคยใช้งานมาก่อนให้สามารถทำตามได้ตั้งแต่ต้นจนจบ
   - ต้องระบุรายการ PHP Extensions ที่จำเป็นครบถ้วน (`pdo_mysql`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `zip`) และห้ามเผลอลบขั้นตอนการตั้งค่า `.env`, `storage:link`, หรือการตรวจสอบ Views ทั้ง 46 รายการ
