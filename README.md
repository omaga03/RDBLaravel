# 🏛️ คู่มือติดตั้งและเรียกคืนระบบ RDBLaravel สำหรับผู้เริ่มต้น (Zero to Hero)
> **ระบบบริหารจัดการฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB)**  
> พัฒนาด้วย Laravel 12 ทำงานบน Laragon (Apache, MariaDB, PHP 8.5)

---

## 📋 ภาพรวมระบบ (System Overview)

ระบบบริหารจัดการฐานข้อมูลงานวิจัย (Research Database Management System) ถูกออกแบบมาเพื่อจัดเก็บ บริหารจัดการ และเผยแพร่ข้อมูลผลงานวิจัยอย่างเป็นระบบ โดยประกอบด้วยโมดูลหลัก:
1. **📊 หน้าหลักและแดชบอร์ด (Dashboard & Analytics):** แสดงสถิติงานวิจัย งบประมาณ กำลังคน และกราฟเปรียบเทียบตามปีงบประมาณ
2. **📝 โครงการวิจัย (Research Projects):** บริหารจัดการโครงการ ทุนอุดหนุน สถานะ และระบบประเมินเล่มรายงานวิจัย (Peer Review)
3. **📑 ผลงานตีพิมพ์ (Publications):** ฐานข้อมูลบทความวิจัย วารสารระดับชาติและนานาชาติ (TCI, Scopus, WoS)
4. **💡 ทรัพย์สินทางปัญญา (Intellectual Property - DIP):** สิทธิบัตร อนุสิทธิบัตร ลิขสิทธิ์ และเครื่องหมายการค้า
5. **🚀 การนำไปใช้ประโยชน์ (Utilization):** ติดตามผลงานวิจัยเชิงพาณิชย์ เชิงนโยบาย เชิงสาธารณะ และเชิงวิชาการ
6. **👨‍🔬 ข้อมูลนักวิจัย (Researchers):** ข้อมูลส่วนตัว ผลงานสะสม และการเชื่อมโยง Scopus / ORCID

---

## 📌 สิ่งที่ต้องเตรียมไว้ในเครื่อง
ติดตั้งโปรแกรม **Laragon (Full)** ไว้ที่ `C:\laragon` ให้เรียบร้อย

---

## 🛠️ ขั้นตอนที่ 1: สลับเวอร์ชันใน Laragon ให้ตรงตามสเปก

เปิดโปรแกรม **Laragon** ขึ้นมา จากนั้นคลิกขวาที่พื้นที่ว่างในหน้าต่าง Laragon เพื่อเลือกเวอร์ชัน:

1. **เลือก Apache:** คลิกขวา -> **Apache** -> **Version** -> เลือก `httpd-2.4.69-261002-Win64-VS18`
2. **เลือก PHP:** คลิกขวา -> **PHP** -> **Version** -> เลือก `php-8.5.12-1-nts-Win32-vs17-x64`
3. **เลือก MariaDB:** คลิกขวา -> **MySQL** -> **Version** -> เลือก `mariadb-13.0.2-winx64`
4. **เปิด Extension ที่จำเป็นใน PHP (`php.ini`):**
   * คลิกขวาที่ Laragon -> **PHP** -> **php.ini** (โปรแกรม Notepad จะเปิดขึ้นมา)
   * กด `Ctrl + F` ค้นหาคำว่า `;extension=zip` แล้ว **ลบเครื่องหมาย `;` ด้านหน้าออก** ให้เหลือ `extension=zip`
   * **ตรวจสอบ Extension สำคัญทั้งหมดที่ระบบต้องใช้ (ต้องไม่มีเครื่องหมาย `;` อยู่ด้านหน้า):**
     * `extension=pdo_mysql` (เชื่อมต่อฐานข้อมูล MariaDB/MySQL)
     * `extension=mysqli` (ระบบฐานข้อมูล)
     * `extension=curl` (เชื่อมต่อ API ภายนอก / Scopus / Google SSO)
     * `extension=fileinfo` (ตรวจสอบประเภทไฟล์เอกสารและภาพที่อัปโหลด)
     * `extension=gd` (ประมวลผลและย่อรูปภาพนักวิจัย)
     * `extension=intl` (การจัดรูปแบบวันที่ พ.ศ. และภาษาไทย)
     * `extension=mbstring` (จัดการตัดคำและข้อความภาษาไทย UTF-8)
     * `extension=openssl` (ระบบรักษาความปลอดภัยและการเข้ารหัส)
     * `extension=zip` (แตกไฟล์แพ็กเกจของ Composer และดาวน์โหลดไฟล์ Zip)
     * `extension=bcmath` (คำนวณงบประมาณและสถิติที่มีทศนิยมละเอียด)
     * `extension=exif` (อ่านค่า Meta ภาพถ่ายโครงการ)
   > 💡 *หมายเหตุ:* ใน PHP ของ Laragon ส่วนใหญ่จะเปิดตัวอื่นมาให้พร้อมใช้แล้ว เหลือเพียง `zip` ตัวเดียวที่มักถูกปิด `;` ไว้เป็นค่าเริ่มต้น แต่หากใช้ PHP นอกเหนือจาก Laragon ให้ตรวจเช็คให้แน่ใจว่าเปิดครบทุกตัวตามรายการข้างต้น
   * กดบันทึกไฟล์ (`Ctrl + S`) แล้วปิด Notepad
5. กดปุ่ม **"Stop"** แล้วกด **"Start All"** ในหน้าต่าง Laragon

---

## 💻 ขั้นตอนที่ 2: เปิด Terminal ของ Laragon

> ⚠️ **ข้อสำคัญมาก:** ห้ามเปิด CMD หรือ PowerShell ธรรมดาจาก Windows  
> ให้คลิกปุ่ม **"Terminal"** บนหน้าต่าง Laragon เท่านั้น (หน้าต่างสีดำจะเปิดขึ้นมาพร้อมสภาพแวดล้อมที่ตั้งค่าไว้เสร็จสรรพ)

---

## 🚀 ขั้นตอนที่ 3: ดึงโค้ดโปรเจกต์จาก Git

พิมพ์คำสั่งด้านล่างนี้ลงใน Terminal แล้วกด Enter:
```bash
cd C:\laragon\www
git clone https://github.com/omaga03/RDBLaravel.git laravel_rdb
cd laravel_rdb
```

---

## 📦 ขั้นตอนที่ 4: ติดตั้งระบบและชุดโปรแกรมเสริม

รันคำสั่งเหล่านี้ทีละบรรทัด:

```bash
# 1. ติดตั้งไลบรารี PHP
composer install

# 2. คัดลอกไฟล์ตั้งค่าระบบ
copy .env.example .env

# 3. สร้างรหัสความปลอดภัยของระบบ
php artisan key:generate

# 4. ติดตั้งและคอมไพล์หน้าเว็บ (CSS/JavaScript)
npm install
npm run build
```

---

## 🗄️ ขั้นตอนที่ 5: นำเข้าฐานข้อมูลจริง (Database Setup)

พิมพ์คำสั่งนำเข้าฐานข้อมูลและสร้างตารางโครงสร้างทั้งหมด:

```bash
# 1. สร้างฐานข้อมูล rdbpcru เปล่า
mysql -u root -e "CREATE DATABASE IF NOT EXISTS rdbpcru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. นำเข้าข้อมูลจริงจากไฟล์ SQL Dump (เช่น DB_PCRURDI_691031.sql)
mysql -u root --default-character-set=utf8mb4 --max_allowed_packet=128M rdbpcru -e "source C:/laragon/www/laravel_rdb/database/migrations/DB_PCRURDI_691031.sql"

# 3. รันคำสั่งสร้าง View Aliases (ป้องกันปัญหา Table / View เดิม)
mysql -u root rdbpcru -e "CREATE OR REPLACE VIEW \`rdb_project_type\` AS SELECT * FROM \`rdb_project_type----\`; CREATE OR REPLACE VIEW \`rdb_strategic\` AS SELECT * FROM \`rdb_strategic---\`;"

# 4. อัปเดตโครงสร้างระบบล่าสุด
php artisan migrate

# 5. เชื่อมโยงโฟลเดอร์จัดเก็บไฟล์เอกสาร/ไฟล์แนบวิจัย
php artisan storage:link
```

---

## ✅ ขั้นตอนที่ 6: ตรวจสอบความถูกต้องของระบบ

รันคำสั่งทดสอบระบบเพื่อความมั่นใจ:
```bash
php artisan test --filter=BackendAuditTest
```
*(ระบบจะตรวจความถูกต้องทั้งหมด 47 รายการ ผลลัพธ์ต้องแสดง **47 passed (421 assertions)** สีเขียวทั้งหมด)*

---

## 🌐 ขั้นตอนที่ 7: เปิดใช้งานระบบ

เปิดเว็บบราวเซอร์ (Chrome / Edge) แล้วพิมพ์ URL:  
👉 **`http://laravel_rdb.test`**

### 🔑 บัญชีสำหรับเข้าสู่ระบบ (Login):
เข้าสู่ระบบได้ที่ลิงก์: `http://laravel_rdb.test/login`

* **ผู้ดูแลระบบสูงสุด (Super Admin):**
  * **ชื่อผู้ใช้งาน:** `admin` (หรืออีเมล `admin@pcru.ac.th`)
  * **รหัสผ่าน:** `admin1234`
* **นักวิจัย (Researcher):**
  * **ชื่อผู้ใช้งาน:** `655169` (ดร. ชลธิชา ระลึก)
  * **รหัสผ่าน:** `password123`

---

## 📚 ศูนย์รวมคู่มือการใช้งานระบบ (User Manuals & SOP Portal)

ระบบมาพร้อมชุดเอกสารคู่มือปฏิบัติงานมาตรฐาน (SOP) ฉบับสมบูรณ์ในโฟลเดอร์ `docs/manuals/` ทั้งรูปแบบ Markdown (`.md`) และเว็บเพจ HTML5 (`.html`) ที่รองรับการค้นหา สารบัญคลิกข้ามบท โหมดมืด/สว่าง และการสั่งพิมพ์:

* **หน้าสารบัญรวมศูนย์ (Documentation Portal):** [`docs/manuals/index.html`](docs/manuals/index.html)
* **เล่มที่ 1: คู่มือระบบหน้าบ้าน (Volume 1 - Frontend Manual 8 บท):** [`VOLUME_1_FRONTEND_USER_MANUAL.md`](docs/manuals/VOLUME_1_FRONTEND_USER_MANUAL.md) | [`.html`](docs/manuals/VOLUME_1_FRONTEND_USER_MANUAL.html)
* **เล่มที่ 2: คู่มือระบบหลังบ้านและนักวิจัย (Volume 2 - Backend Manual 10 บท):** [`VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.md`](docs/manuals/VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.md) | [`.html`](docs/manuals/VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.html)
* **คู่มือฉบับย่อ 3 นาทีสำหรับนักวิจัย (Quick-Start Cheatsheet):** [`RESEARCHER_QUICK_START_CHEATSHEET.md`](docs/manuals/RESEARCHER_QUICK_START_CHEATSHEET.md) | [`.html`](docs/manuals/RESEARCHER_QUICK_START_CHEATSHEET.html)
* **แผนและรายงานผลการตรวจรับระบบ (UAT & Acceptance Test Report):** [`SYSTEM_ACCEPTANCE_TEST_PLAN.md`](docs/manuals/SYSTEM_ACCEPTANCE_TEST_PLAN.md) | [`.html`](docs/manuals/SYSTEM_ACCEPTANCE_TEST_PLAN.html)

```bash
# คำสั่งคอมไพล์คู่มือ Markdown ทั้งหมดเป็น HTML5 Portal อัตโนมัติ
php .agents/skills/documentation-and-manuals/scripts/build_html_manuals.php
```

---

## 📝 บันทึกการอัปเดตระบบล่าสุด (Changelog / Update Log)

### อัปเดตประจำวันที่ 2 พฤศจิกายน 2569 (Release 2026.11)
1. **ปรับโครงสร้างเมนูระบบหลังบ้านเป็นแถบด้านซ้าย (Responsive Left Sidebar & Accordion Navigation):**
   * ย้ายเมนูจัดการระบบหลังบ้านทั้งหมดมาไว้ที่แถบด้านซ้าย (`<x-backend-sidebar />`) พร้อมตัดเมนูแถบบนแบบเดิมออกเพื่อเพิ่มพื้นที่แสดงผลแนวตั้ง
   * จัดกลุ่มเมนูย่อยเป็นโซนชัดเจน (`ข้อมูลโครงการ`, `ประเมินเล่มรายงาน`, `การส่งออกข้อมูล`) พร้อมสัญลักษณ์ `-` นำหน้าและเส้นคั่นแบ่งโซน
   * ยุบรวมเมนู **ตั้งค่าข้อมูลพื้นฐาน (Master Data)** ให้พับเก็บ/กางออกย่อยได้ (`#sidebarMasterProject`, `#sidebarMasterPublished`)
   * ระบบพับเก็บแบบ **Accordion อัตโนมัติ** (`data-bs-parent="#rdbSidebarAccordion"`): เมื่อคลิกขยายหมวดเมนูหลักใด หมวดเมนูอื่นที่เปิดอยู่จะยุบเก็บให้อัตโนมัติทันที
   * รองรับทุกขนาดหน้าจอ: ย่อเป็น Mini Sidebar (`68px`) บนจอโน้ตบุ๊กขนาดกลาง และเปลี่ยนเป็น Offcanvas Drawer บนแท็บเล็ต/มือถือ (`< 992px`) พร้อมป้องกันตารางกว้างดันหน้าจอล้น (`min-width: 0`)
2. **จัดระเบียบระยะห่างไอคอนและข้อความทั้งระบบ (Uniform Icon-to-Text Spacing System):**
   * เพิ่มชุด CSS Fractional Spacing Utilities (`.me-1.5`, `.gap-1.5`, `.px-2.5`, `.py-1.5` ฯลฯ) และปรับระยะห่างไอคอนคู่ข้อความให้เท่ากันทั้งระบบหน้าบ้านและหลังบ้าน (67 ไฟล์ Blade)
3. **ปรับปรุงหน้ารายละเอียดโครงการวิจัย (`/backend/rdb_project/{id}`):**
   * นำป้าย `หลักฐานการเงิน: ไม่ครบถ้วน` เดิมออกจากการ์ดงบประมาณโครงการ
   * ปรับการแสดงผลชื่อโครงการทุนย่อย (`pts_name`) และป้ายคำสำคัญที่มีความยาวมากให้ตัดขึ้นบรรทัดใหม่อัตโนมัติ (`text-wrap text-start lh-base`) ไม่ล้นขอบจอเล็ก
4. **ยกระดับชุดคู่มือผู้ใช้งาน (`docs/manuals/`) สู่ระดับ Comprehensive SOP:**
   * ขยายเนื้อหาคู่มือเล่ม 1 และเล่ม 2 ให้ครอบคลุมพจนานุกรมฟิลด์แบบฟอร์ม, ขั้นตอนคลิกทีละจุด `[1]`, `[2]`, `[3]`, แผนภาพ Mermaid Workflow และแก้ไขสารบัญ (TOC) ให้ลิงก์ข้ามไปยังหัวข้อบทหลักได้อย่างแม่นยำ
