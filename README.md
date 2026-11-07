# 🏛️ คู่มือติดตั้งและเรียกคืนระบบ RDBLaravel สำหรับผู้เริ่มต้น (Zero to Hero)
> **ระบบบริหารจัดการฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB)**  
> พัฒนาด้วย Laravel 13 ทำงานบน Laragon (Apache, MariaDB, PHP 8.5)

---

## 📋 ภาพรวมระบบ (System Overview)

ระบบบริหารจัดการฐานข้อมูลงานวิจัย (Research Database Management System) ถูกออกแบบมาเพื่อจัดเก็บ บริหารจัดการ และเผยแพร่ข้อมูลผลงานวิจัยอย่างเป็นระบบ โดยประกอบด้วยโมดูลหลัก:
1. **📊 หน้าหลักและแดชบอร์ด (Dashboard & Analytics):** แสดงสถิติงานวิจัย งบประมาณ กำลังคน และกราฟเปรียบเทียบตามปีงบประมาณ
2. **📝 โครงการวิจัย (Research Projects):** บริหารจัดการโครงการ ทุนอุดหนุน สถานะ และระบบประเมินเล่มรายงานวิจัย (Peer Review)
3. **📑 ผลงานตีพิมพ์ (Publications):** ฐานข้อมูลบทความวิจัย วารสารระดับชาติและนานาชาติ (TCI, Scopus, WoS)
4. **💡 ทรัพย์สินทางปัญญา (Intellectual Property - DIP):** สิทธิบัตร อนุสิทธิบัตร ลิขสิทธิ์ และเครื่องหมายการค้า
5. **🚀 การนำไปใช้ประโยชน์ (Utilization):** ติดตามผลงานวิจัยเชิงพาณิชย์ เชิงนโยบาย เชิงสาธารณะ และเชิงวิชาการ
6. **👨‍🔬 ข้อมูลนักวิจัย (Researchers):** ข้อมูลส่วนตัว ผลงานสะสม และการเชื่อมโยง Scopus / ORCID
7. **📢 ข่าวทุนวิจัยและงานประชุมวิชาการ (News & Conferences):** ระบบดึงข่าวทุนวิจัยอัตโนมัติจาก NRIIS และงานประชุมจาก Facebook พร้อม Webhook, Telegram Notify, ระบบตรวจดูข้อมูลจำลอง (Dry-Run Inspector) และรายงานประจำเดือน
8. **🔌 ระบบศูนย์บริการข้อมูล API สองระดับ (Dual-Tier & Open Public API):** RESTful APIs สำหรับเชื่อมโยงระบบภายนอก (PMIS, Mobile Apps, Open Data) พร้อมระบบจัดการ Token และคู่มือ Endpoint บนหน้าเว็บ
9. **📈 เล่มรายงานสรุปภาพรวมผู้บริหารและการจัดการข้อมูลชุดใหญ่ (Executive Strategic Intelligence & Bulk Operations Hub):** สรุปตัวชี้วัดเชิงยุทธศาสตร์ 5 มิติ (Scopus Q1-Q4, TCI, การใช้ประโยชน์ 4 ด้าน, แหล่งทุน, และ Top 5 โครงการ) พร้อมโหมดสั่งพิมพ์ A4 / บันทึก PDF ในคลิกเดียว และ Floating Batch Toolbar สำหรับเปลี่ยนสถานะ กำหนดสิทธิ์เอกสาร และ Export ข้อมูลขนาดใหญ่เป็นกลุ่ม

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

## 📂 ขั้นตอนที่ 5.1: นำเข้าไฟล์เอกสารและภาพจากระบบเดิม (Legacy Uploads — 9 Folders)

ระบบได้รับการออกแบบโครงสร้างโฟลเดอร์จัดเก็บไฟล์ใน `storage/app/public/uploads/` ให้**ตรงกับระบบเดิม (Yii2) ทั้ง 9 โฟลเดอร์ 100%**  
ผู้ดูแลระบบสามารถคัดลอก (Copy & Paste) ทั้ง 9 โฟลเดอร์จากระบบเดิมที่ `frontend/web/` มาวางใน `storage/app/public/uploads/` ได้ทันที และระบบจะสามารถเปิดดู/ดาวน์โหลดไฟล์ได้ตามปกติทุกโมดูล:

| ลำดับ | โฟลเดอร์ใน `storage/app/public/uploads/` | โมดูลที่ใช้งาน | โครงสร้างจัดเก็บไฟล์ |
| :---: | :--- | :--- | :--- |
| 1 | `datafiles` | เล่มรายงานวิจัยฉบับสมบูรณ์ (`pro_file`), บทคัดย่อ (`pro_abstract_file`), ไฟล์แนบ (`rf_files`), เอกสาร ป.ป.ช. | `datafiles/{pro_id}/{filename}` |
| 2 | `datapublished` | เอกสารบทความผลงานตีพิมพ์ (`pub_file`) | `datapublished/{id}/{filename}` |
| 3 | `datadip` | เอกสารทรัพย์สินทางปัญญาและไฟล์แนบเพิ่มเติม | `datadip/{dip_id}/{filename}` |
| 4 | `datautilize` | เอกสารการนำผลงานวิจัยไปใช้ประโยชน์ | `datautilize/{utz_id}/{filename}` |
| 5 | `dataresearcher` | ภาพถ่ายโปรไฟล์นักวิจัย | `dataresearcher/{filename}` |
| 6 | `datapts` | เอกสารประเภทย่อยทุนวิจัย | `datapts/{filename}` |
| 7 | `datacoferenceinthai` | ภาพโปสเตอร์งานประชุมวิชาการ | `datacoferenceinthai/{filename}` |
| 8 | `datanewsimg` | ภาพข่าวประชาสัมพันธ์และข่าวทุนวิจัย | `datanewsimg/{filename}` |
| 9 | `datajournalstatus` | เอกสารตรวจสอบสถานะวารสาร | `datajournalstatus/{id}/{filename}` |

> 💡 **คำสั่งตรวจสอบความสมบูรณ์ของไฟล์หลังวางโฟลเดอร์ (File Verification):**
> ```bash
> php artisan rdb:sync-attachments --dry-run
> ```
> *(ระบบจะรายงานสถิติความพร้อมของไฟล์ทั้ง 10 โมดูล โดยต้องมีความครอบคลุมมากกว่า 97% ขึ้นไป)*

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

## ⚙️ ศูนย์จัดการคำสั่งระบบและการบำรุงรักษา (System Commands & Maintenance Dashboard)

สำหรับผู้ดูแลระบบ (Super Admin) สามารถเข้าใช้งานหน้าต่างควบคุมและสั่งรันคำสั่งบำรุงรักษาระบบผ่านหน้าเว็บได้โดยตรงที่:
👉 **`http://laravel_rdb.test/backend/system-commands`** (เมนูแถบซ้าย: **การดูแลระบบ ➔ คำสั่งและบำรุงรักษาระบบ**)

เครื่องมือที่พร้อมใช้งานผ่านหน้าเว็บ:
1. **สร้างภาพหน้าแรกของ PDF เป็นภาพ (.webp)**: สั่งประมวลผลเรนเดอร์หน้าแรกของไฟล์ PDF ทั้งระบบ แยกตามโมดูล พร้อมตัวเลือกบังคับสร้างใหม่ (`--force`) และกำหนดขอบเขตจำนวนรายการ (Limit)
2. **ตรวจนับจำนวนหน้าเอกสาร PDF ย้อนหลังทั้งระบบ**: คำนวณจำนวนหน้าด้วย 4-Tier Engine (Poppler pdftotext แม่นยำ 99.99%) และบันทึกลงฐานข้อมูล `pro_page_num` และ `pub_numfile` อัตโนมัติ
3. **แปลงไฟล์รูปภาพทั้งระบบเป็น .webp (Universal WebP Image Converter &le;1024px)**: คำสั่งแปลงรูปภาพโปรไฟล์นักวิจัย ภาพข่าวทุนวิจัย NRIIS และภาพงานประชุมวิชาการ Facebook เป็น `.webp` คุณภาพ 85 พร้อมปรับลดขนาดความกว้าง/สูงสูงสุดไม่เกิน 1,024 พิกเซลอัตโนมัติ ย้อนหลังทั้งระบบพร้อมอัปเดตฐานข้อมูล
4. **ล้างแคชระบบและเพิ่มประสิทธิภาพ (Clear Cache)**: ล้าง Application, Route, Blade View, และ Config Cache เพื่อให้ระบบอ่านค่าล่าสุดทันที
5. **แผงทางลัดเครื่องมือเชื่อมโยง (Quick Shortcuts Strip)**: ลิงก์ตรงสู่ระบบจัดการ API (API Management), เครื่องมือทดสอบจำลองดึงข่าว NRIIS Dry-Run, ทดสอบดึงงานประชุม Facebook Dry-Run, และระบบตั้งเวลาอัตโนมัติ (Scheduler)
6. **ตรวจสอบความพร้อมของเซิร์ฟเวอร์ (Server Engine Status)**: ตรวจจับ MuPDF/PyMuPDF, Poppler pdftotext/pdftoppm, PHP GD WebP, และสถานะ Storage Symlink พร้อมปุ่มคู่มือติดตั้ง

```bash
# คำสั่งแปลงรูปภาพเป็น WebP ผ่าน Terminal:
php artisan rdb:convert-images-webp                                  # แปลงทุกโมดูลทั้งระบบ (max 1024px)
php artisan rdb:convert-images-webp --module=researcher               # เฉพาะภาพนักวิจัย
php artisan rdb:convert-images-webp --module=news                     # เฉพาะภาพข่าว NRIIS
php artisan rdb:convert-images-webp --module=conference               # เฉพาะภาพงานประชุม
php artisan rdb:convert-images-webp --module=all --max-dimension=1024 # ระบุขนาดภาพสูงสุด
```

---

## 💾 ศูนย์สำรองและกู้คืนข้อมูลระบบ (System Backup & Disaster Recovery Hub — The Creator Only)

ระบบสำรองและกู้คืนฐานข้อมูลที่ปลอดภัยสูงสุด ถูกแยกออกเป็นเมนูเฉพาะสำหรับ **The Creator (ผู้ดูแลระบบสูงสุด)**:
👉 **`http://laravel_rdb.test/backend/system-backup`** (เมนูแถบซ้าย: **การดูแลระบบ ➔ สำรองและกู้คืนระบบ (Disaster Recovery)**)

* **สำรองข้อมูล 3 รูปแบบในเบื้องหลัง (Background Worker & Live Progress Bar)**:
  - 1. เฉพาะฐานข้อมูล MariaDB (`.sql.gz`)
  - 2. เฉพาะไฟล์เอกสาร Storage (`.zip` ~20 GB พร้อมระบบไม่บีบอัดซ้ำไฟล์ภาพ/PDF เพิ่มความเร็วสูงสุด)
  - 3. สำรองทั้งระบบแบบเต็ม (Full Archive DB + Files + Environment Snapshot)
  - มีแถบแสดงสถานะสด (Live Progress Bar) อัปเดต % ความคืบหน้า จำนวนไฟล์ที่ประมวลผล และเวลาที่ใช้ไปแบบเรียลไทม์ ทำงานเบื้องหลัง ไม่ค้างหน้าจอ
* **ระบบคำนวณ Checksum ไร้คอขวด (Fast Cryptographic Fingerprinting)**: ตรวจสอบความถูกต้องของไฟล์สำรองขนาดใหญ่ระดับ 20+ GB ด้วยเทคนิค 64KB Header/Middle/Footer Sampling ร่วมกับแคชถาวร เปิดหน้าเว็บได้ทันทีใน 0.001 วินาที (จากเดิมที่ต้องรอนานกว่า 46 วินาที)
* **ระบบอัปเดตโค้ดขึ้น GitHub ในคลิกเดียว (One-Click Git Commit & Push Modal)**:
  - ปุ่ม **"อัปเดตขึ้น Git (Git Push)"** พร้อมป้าย Badge แสดงจำนวนไฟล์ที่แก้ไขค้างอยู่แบบสด
  - หน้าต่าง Modal แสดงสาขา, Commit ล่าสุด, และรายการไฟล์ที่มีการเปลี่ยนแปลง (Modified, Added, Deleted)
  - ระบุ Commit Message และกด Push ขึ้น GitHub ได้ทันทีผ่านหน้าเว็บ พร้อม Console Log แบบสด
  - รองรับทั้งบน Windows/Laragon และ Linux Production Server (มีระบบ Auto-fallback Identity และกลไกความปลอดภัยบล็อก `.env` และไฟล์ขนาดเกิน 50 MB อัตโนมัติ)
* **ระบบกู้คืนฐานข้อมูลผ่านหน้าเว็บ (Safe Web Restore with Email OTP)**: ยืนยันรหัส OTP 6 หลักส่งตรงเข้า Email ของ The Creator พร้อมสร้าง Safety Snapshot ให้อัตโนมัติก่อนเริ่มกู้คืนเสมอ
* **ระบบลบไฟล์สำรองแบบ Idempotent ปลอดภัย**: ป้องกันการส่งคำสั่งลบซ้ำและตัดปัญหาระบบแจ้งเตือนว่าลบไม่ได้ ทั้งที่ไฟล์ถูกลบไปแล้ว
* **ดาวน์โหลดไฟล์สำรองและซิงค์คลาวด์**: ดาวน์โหลดไฟล์สำรองลงเครื่องแอดมินโดยตรง พร้อมคู่มือซิงค์ขึ้น Google Drive อัตโนมัติผ่าน Rclone

```bash
# คำสั่งสำรองข้อมูลผ่าน Terminal
php artisan rdb:backup --type=all   # หรือ --type=db, --type=files
php artisan rdb:backup --type=all --clean-older-than-days=30

# คำสั่งกู้คืนระบบฉุกเฉินผ่าน Shell
php artisan rdb:restore backup_db_xxxx.sql.gz --force
```

---

## 🚀 แนวทางการนำระบบขึ้นใช้งานจริงบนเซิร์ฟเวอร์ (Production Deployment Guide)

เมื่อต้องการ Deploy โปรเจกต์ขึ้นเซิร์ฟเวอร์จริง (เช่น Ubuntu / Debian Linux) ให้ปฏิบัติตามขั้นตอนดังนี้:

### 1. ติดตั้งแพ็กเกจระบบที่จำเป็น (Server Packages)
```bash
sudo apt update
sudo apt install -y git mariadb-client poppler-utils
```
*(ติดตั้ง `poppler-utils` เพื่อให้เอนจิน `pdftotext` สามารถนับหน้าเอกสาร PDF ได้แม่นยำ 99.99%)*

### 2. ตั้งค่าสิทธิ์และ Identity ของ Git
เพื่อให้ฟังก์ชัน **One-Click Git Push** บนหน้าเว็บทำงานได้อย่างสมบูรณ์:
* **การยืนยันตัวตนกับ GitHub (เลือกวิธีใดวิธีหนึ่ง):**
  * **แบบ SSH Deploy Key (แนะนำ):** สร้าง SSH Key (`ssh-keygen -t ed25519 -C "server@pcru.ac.th"`) แล้วนำ Public Key ไปเพิ่มใน GitHub Repo (Settings ➔ Deploy keys ➔ ติ๊ก **Allow write access**)
  * **แบบ Personal Access Token (PAT):** กำหนด Remote URL:
    ```bash
    git remote set-url origin https://<YOUR_GITHUB_TOKEN>@github.com/omaga03/RDBLaravel.git
    ```
* **กำหนดสิทธิ์โฟลเดอร์สำหรับ Web Server User (`www-data`):**
  ```bash
  sudo chown -R www-data:www-data /var/www/laravel_rdb
  sudo chmod -R 775 /var/www/laravel_rdb/storage /var/www/laravel_rdb/bootstrap/cache /var/www/laravel_rdb/.git
  ```

### 3. ตัวแปร Environment (`.env`) สำหรับ Production
* กำหนด `APP_ENV=production` และ `APP_DEBUG=false`
* กำหนดผู้บันทึก Git Commit ในกรณีสั่งงานผ่านหน้าเว็บ:
  ```ini
  GIT_AUTHOR_NAME="PCRU Admin"
  GIT_AUTHOR_EMAIL="omaga03@gmail.com"
  ```

---

## ⏱️ ศูนย์จัดการระบบตั้งเวลาทำงานอัตโนมัติ (Automated Task Scheduler Hub) & ระบบแจ้งเตือน Telegram

ควบคุมงานประมวลผลอัตโนมัติทั้ง 6 งาน และระบบแจ้งเตือนผ่านบอท Telegram สำหรับผู้ดูแลระบบ:
👉 **`http://laravel_rdb.test/backend/system-scheduler`** (เมนูแถบซ้าย: **การดูแลระบบ ➔ ระบบตั้งเวลาอัตโนมัติ (Scheduler)**)

* **ตารางรอบงานอัตโนมัติ 6 รายการ**: สำรองฐานข้อมูลประจำวัน (02:00), สำรองเต็มรูปแบบประจำสัปดาห์ (วันอาทิตย์ 03:00), ตรวจนับหน้า PDF (04:00), เรนเดอร์ปก WebP (04:30), ซิงค์ข่าวทุน NRIIS (06:00), ซิงค์ข่าวประชุมวิชาการ Facebook (06:30)
* **สั่งรันงานทันที (Run Now)**: ผู้ดูแลระบบสามารถกดปุ่มสั่งรันงานใดก็ได้ทันที พร้อมหน้าต่าง Terminal Console Output
* **ระบบแจ้งเตือนผ่าน Telegram Bot อัตโนมัติ**:
  * **Super Admin**: แจ้งเตือนทันทีเมื่อมีการ **เพิ่ม (Create), ลบ (Delete), แก้ไข (Update), อัปโหลดไฟล์ (File Upload), สำรอง/กู้คืนระบบ (Backup/Restore)** หรือกิจกรรมสำคัญ
  * **แจ้งเตือนส่วนบุคคลของนักวิจัย**: รองรับการส่งแจ้งเตือนผลการอนุมัติงานวิจัย และงานมอบหมายประเมินผ่าน Telegram ควบคู่กับอีเมล (`mail` + `telegram`) สำหรับผู้ใช้ที่ระบุ `telegram_chat_id`

```bash
# ตรวจสอบรายการและรอบการรันงานอัตโนมัติ
php artisan schedule:list

# รันตัวจำลองเวลาบนเครื่อง Development / Laragon
php artisan schedule:work
```

---

## 📢 ระบบศูนย์ข่าวทุนวิจัยและงานประชุมวิชาการ (Research News & Conferences Hub)

ระบบบริหารจัดการข่าวสารและงานประชุมวิชาการทั้งหน้าบ้านและหลังบ้าน:
* **ข่าวทุนวิจัยและกิจกรรม (`/backend/research_news`)**:
  - เชื่อมโยงอัตโนมัติกับ **NRIIS API** (`NriisSyncService`) ทุกวัน เวลา 06:00 น.
  - ระบบส่งต่อไปยัง Make.com Webhook (Facebook Automation) และส่งแจ้งเตือน Telegram
  - **เครื่องมือทดสอบดึงข้อมูล (Dry-Run Inspector)**: เข้าได้ที่ `/backend/research_news/test-sync` สำหรับทดสอบดึงข้อมูลข่าวจาก NRIIS เพื่อดูโครงสร้างข้อมูลโดยไม่บันทึกลงฐานข้อมูลจริง
  - **หน้ารายงานสรุปข่าวประจำเดือน**: แยกเป็นเมนูย่อยเฉพาะใน "ข่าว/กิจกรรม" ที่ `/backend/research_news/reports` แสดงสถิติจำนวนข่าวแยกตามประเภทและเดือน
  - รองรับการลบลงถังขยะ (Soft Delete), การกู้คืน (Restore), และการลบถาวร (Force Delete พร้อมลบไฟล์ภาพ)
* **งานประชุมวิชาการในและต่างประเทศ (`/backend/research_conference`)**:
  - ดึงโพสต์งานประชุมจาก Facebook อัตโนมัติ (`FacebookConferenceSyncService`) ทุกวัน เวลา 06:30 น.
  - **เครื่องมือทดสอบดึงโพสต์ Facebook (Dry-Run Inspector)**: เข้าได้ที่ `/backend/research_conference/test-sync`
  - รองรับระบบถังขยะและลบถาวรแบบเดียวกัน
* **การแปลงรูปภาพเป็น WebP อัตโนมัติ**:
  - รูปภาพข่าวและงานประชุมที่ถูกนำเข้าสู่ระบบทั้งจากการดาวน์โหลด Sync และการอัปโหลดผ่านแบบฟอร์ม จะถูกแปลงเป็นนามสกุล `.webp` คุณภาพ 85 พร้อม Auto-resize ไม่เกิน 1024px โดยอัตโนมัติ

---

## 🔌 ศูนย์จัดการและบริการข้อมูล API (Dual-Tier & Open Public API Management)

ระบบบริการข้อมูลเชื่อมโยงภายนอก (RESTful APIs) ครอบคลุม 3 ระดับ เข้าถึงคู่มือและการจัดการได้ที่:
👉 **`http://laravel_rdb.test/backend/api-management`** (เมนูแถบซ้าย: **การดูแลระบบ ➔ จัดการ API & Token**)

* **Tier 1: Private Administrative API (ต้องใช้ Bearer Token)**:
  - สิทธิ์เฉพาะ The Creator ในการสร้างและเพิกถอน Token สำหรับแอปพลิเคชันภายในหรือ Mobile App
  - Endpoints: ข้อมูลสถิติ, ข้อมูลนักวิจัย, ผลงานวิจัยฉบับเต็ม (`/api/v1/projects`, `/api/v1/publications` ฯลฯ)
* **Tier 2: PMIS & Legacy Integration API (Public Read-Only)**:
  - รองรับระบบประเมินคุณภาพการศึกษา PMIS และระบบดั้งเดิม
  - Endpoints: `/api/pmis/summary-by-faculty`, `/api/pmis/portfolio-by-year`, `/api/project/json-detail` ฯลฯ
* **Tier 3: Open Public API สำหรับข่าวสารและงานประชุม (ไม่ต้องใช้ Token)**:
  - บริการข้อมูลข่าวทุนวิจัยและงานประชุมวิชาการแบบเปิดสำหรับเว็บไซต์คณะ หรือหน่วยงานภายนอก
  - Endpoints:
    - `GET /api/v1/news` และ `GET /api/v1/news/{id}` (หรือ `GET /api/news`)
    - `GET /api/v1/conferences` และ `GET /api/v1/conferences/{id}` (หรือ `GET /api/conferences`)
* **เครื่องมือทดสอบและคัดลอก (Non-Secure HTTP Safe Clipboard)**:
  - แท็บ **"รายการ Endpoints"** บนหน้าเว็บ มีปุ่ม **"คัดลอก JSON"** และ **"คัดลอกคำสั่ง cURL"** ที่ปลอดภัยรองรับทั้งสภาพแวดล้อม HTTP (`laravel_rdb.test`) และ HTTPS

---

## 🔍 ระบบสืบค้นและทำดัชนีทางวิชาการสากล (Academic SEO & Sitemaps)

ระบบติดตั้งสถาปัตยกรรม Academic SEO อัตโนมัติเต็มรูปแบบ:
* **Google Scholar & Highwire Press Tags**: ฝังเมทาดาทา `citation_title`, `citation_author`, `citation_publication_date`, `citation_pdf_url`, `citation_journal_title`
* **Dublin Core Metadata**: ครบถ้วนตามมาตรฐานบรรณารักษศาสตร์ (`DC.title`, `DC.creator`, `DC.date`, `DC.publisher`, `DC.identifier`)
* **Social Sharing & Open Graph**: ดึงภาพหน้าปก `.webp` จากหน้าแรกของ PDF มาเป็นภาพพรีวิวขนาดใหญ่ (`summary_large_image`) เมื่อแชร์บน Facebook, LINE, X
* **Schema.org Structured Data (JSON-LD)**: รองรับสคีมา `ScholarlyArticle`, `ResearchProject`, `Person`
* **Dynamic XML Sitemap Index**: เข้าถึงได้ที่ **`http://laravel_rdb.test/sitemap.xml`** (แยกย่อยเป็น 5 หมวด: โครงการ, ผลงานตีพิมพ์, ทรัพย์สินทางปัญญา, การใช้ประโยชน์ และนักวิจัย โดยคัดกรองเฉพาะ `data_show = 1`)

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

### อัปเดตประจำวันที่ 7 พฤศจิกายน 2569 (Release 2026.11.07)
1. **ระบบอัปเดตโค้ดขึ้น GitHub ผ่านหน้าเว็บในคลิกเดียว (One-Click Git Commit & Push Hub):**
   * เพิ่มปุ่ม **"อัปเดตขึ้น Git (Git Push)"** พร้อม Live Status Badge แสดงจำนวนไฟล์ที่มีการแก้ไขค้างอยู่
   * หน้าต่าง Git Sync Modal แสดงสาขา, รายการไฟล์ที่มีการเปลี่ยนแปลง (Modified, Added, Deleted), กล่องระบุ Commit Message, และผลการรัน Console แบบเรียลไทม์
   * เสริมกลไกความปลอดภัยบล็อกไฟล์ `.env` และป้องกันการ Push ไฟล์ขนาดเกิน 50 MB
   * ปรับปรุงให้รองรับสภาพแวดล้อม Apache Web Server ทั้งบน Windows/Laragon และ Linux Production Server (Auto Author Fallback & Process Environment Isolation)
2. **ปรับปรุงระบบสำรองข้อมูลและแถบสถานะสด (Asynchronous Live Backup Progress Engine):**
   * พัฒนาระบบ Background Backup ทำงานเบื้องหลังด้วย CLI Worker ไม่บล็อกการทำงานของหน้าเว็บ
   * เพิ่มแถบแสดงสถานะสด (Live Progress Card) ครอบคลุมการสำรองข้อมูลทั้ง 3 รูปแบบ พร้อมสถิติเวลาที่ใช้ไป (`elapsed_seconds`), เปอร์เซ็นต์, และชื่อไฟล์ที่กำลังบีบอัด
   * ปรับปรุงสเต็ปการรันแบบที่ 3 (Full Backup) ให้แสดงแถบต่อเนื่องครบทั้ง 3 ขั้นตอน (สกัดฐานข้อมูล MariaDB ➔ สแกนไฟล์ Storage ➔ บันทึกแพ็กเกจ ZIP 20+ GB) จนถึง 100%
   * ปรับปรุงการสร้างไฟล์ ZIP ด้วยโหมด `ZipArchive::CM_STORE` ข้ามการบีบอัดซ้ำสำหรับไฟล์ PDF และภาพถ่าย เพิ่มความเร็วการแพ็กเกจขึ้นกว่า 300%
3. **ระบบคำนวณ Checksum ความเร็วสูง (Sub-Millisecond Checksum Sampling):**
   * เปลี่ยนจากการรัน `md5_file()` ทั้งก้อนขนาด 20+ GB มาเป็นระบบ 64KB Header/Middle/Footer Cryptographic Fingerprint ร่วมกับถาวรแคช
   * ลดเวลาการเปิดหรือรีโหลดหน้าเว็บศูนย์สำรองข้อมูลจาก **46.63 วินาที** เหลือเพียง **0.0011 วินาที** (เร็วขึ้น 42,000 เท่า)
4. **แก้ไขการลบไฟล์สำรองแบบ Idempotent:**
   * เปลี่ยนการส่งฟอร์มลบไฟล์ผ่านเส้นทาง POST Body (`/backend/system-backup/delete`) ป้องกันปัญหา Browser Download Manager ดักจับนามสกุล `.zip`/`.sql.gz` ท้าย URL
   * ป้องกันการส่งคำสั่งลบซ้ำซ้อน และคืนค่าความสำเร็จถูกต้องเสมอเมื่อไฟล์ถูกลบไปแล้ว

### อัปเดตประจำวันที่ 6 พฤศจิกายน 2569 (Release 2026.11.06)
1. **เล่มรายงานสรุปภาพรวมผู้บริหารและศูนย์จัดการข้อมูลชุดใหญ่ (Executive Report & Bulk Operations Hub):**
   * **เล่มรายงานผู้บริหารเชิงยุทธศาสตร์ (`/backend/reports/executive-summary`)**: รายงาน 5 มิติ (Executive KPI Strip 3x2, แหล่งทุนวิจัยหลัก 6 กลุ่มตาม `rdb_project_types_master`, คุณภาพ Scopus Q1-Q4 / TCI 1-2, งบประมาณรายคณะ/หน่วยงาน, และมิติการนำไปใช้ประโยชน์ 4 ด้าน) พร้อมโหมดสั่งพิมพ์ A4 / บันทึก PDF คลิกเดียว และแถบสัดส่วน 3px (Progress Share Bars)
   * **ศูนย์จัดการข้อมูลชุดใหญ่ (Bulk Operations)**: Floating Action Toolbar สำหรับจัดการข้อมูลโครงการวิจัยและผลงานตีพิมพ์ เปลี่ยนสถานะ กำหนดสิทธิ์เอกสาร และ Export ข้อมูลขนาดใหญ่เป็นกลุ่ม
2. **การปรับแต่งดัชนีฐานข้อมูลและทดสอบสมรรถนะขั้นสูง (Database Optimization & Stress Testing):**
   * เพิ่ม Composite Indexes: `rdb_published(data_show, pub_date)`, `rdb_published(data_show, year_id)`, `rdb_project(data_show, pro_date_start)`, `rdb_project(data_show, year_id)`, `rdb_dip(data_show)`, `rdb_project_utilize(data_show)` ขจัด `filesort` และ Full Table Scan
   * **ผลการทดสอบ Stress & Concurrency ด้วย Apache Bench (`ab.exe`)**:
     - รองรับผู้ใช้งานสืบค้นพร้อมกัน 500 Concurrency (5,000 requests) อัตราความผิดพลาด **0.00% (Failed requests: 0)**
     - ความเร็วประมวลผล **194.12 Requests per second (RPS)**
     - ส่งถ่ายข้อมูลมหาศาล **1.08 GB** (Transfer rate 41 MB/s) ปลอดภัยไร้คอขวด
3. **การทดสอบเจาะช่องโหว่ความปลอดภัยแบบอัตโนมัติ (Automated Security Penetration Audit):**
   * สร้าง Feature Test [`tests/Feature/SecurityPenetrationAuditTest.php`](tests/Feature/SecurityPenetrationAuditTest.php) ผ่าน 6/6 การทดสอบ (67 assertions)
   * ครอบคลุมการป้องกัน SQL Injection Fuzzing, XSS Payload Escaping, CSRF Token Mismatch Enforcement (HTTP 419), API Rate Limiting (HTTP 429), และ IDOR Prevention (HTTP 403)
4. **การยกระดับขึ้นสู่ Laravel 13.40.0 และการทดสอบบูรณาการทั้งระบบ (Full-System Integration Assurance):**
   * อัปเกรด Core Framework สู่ **Laravel 13.40.0** พร้อมแพ็กเกจใน Ecosystem: `laravel/tinker:^3.0`, `nunomaduro/collision:^8.9`, `doctrine/dbal:^4.5` บนสภาพแวดล้อม PHP 8.5
   * สร้างชุดทดสอบบูรณาการ [`tests/Feature/Laravel13FullSystemAuditTest.php`](tests/Feature/Laravel13FullSystemAuditTest.php) ครอบคลุม 7 มิติ (หน้าบ้านทุกโมดูล, หน้าดีเทลพร้อมเมทาดาทา, หลังบ้าน 17 หน้าจอ, การส่งออก CSV BOM ทุกโมดูล, Bulk Operations, REST API 3 ระดับ, และ Artisan CLI commands)
   * ปรับปรุง PHPUnit Assertion ใน `FileUploadServiceTest` สู่มาตรฐานสมัยใหม่ (`assertMatchesRegularExpression`)
   * ผ่านการทดสอบระดับทั้งระบบรวม **103 เทสต์เคส 715 assertions ผ่าน 100%** ไร้ข้อผิดพลาด
   * สร้างจุดสำรองถาวรก่อนอัปเกรด: Branch `backup/laravel-12-lts` และ Tag `v12-stable-backup` บน GitHub รองรับการย้อนกลับได้ทันทีตลอดเวลา

### อัปเดตประจำวันที่ 5 พฤศจิกายน 2569 (Release 2026.11.05)
1. **สถาปัตยกรรมระบบแปลงรูปภาพทั้งระบบเป็น WebP (Universal WebP Image Processing Pipeline):**
   * ปรับปรุง `FileUploadService::storeImageAsWebp()` ให้แปลงไฟล์รูปภาพที่เข้าสู่ระบบทั้งหมด (JPEG, PNG, GIF, WebP) เป็นฟอร์แมต `.webp` คุณภาพ 85 พร้อม Auto-resize ไม่เกิน 1024px รักษาความโปร่งแสง (Alpha Transparency) และจัดเก็บด้วย UUID ปลอดภัย
   * ครอบคลุมจุดนำเข้าภาพ 3 โมดูลหลัก:
     - **ข้อมูลนักวิจัย (`RdbResearcher`)**: อัปโหลดภาพโปรไฟล์ผ่านฟอร์มหลักและฟอร์มเปลี่ยนภาพด่วน
     - **ข่าวทุนวิจัย (`ResearchNews`)**: ดึงภาพผ่าน NRIIS API และอัปโหลดผ่านแบบฟอร์ม
     - **งานประชุมวิชาการ (`ResearchConference`)**: ดึงภาพโปสเตอร์ผ่าน Facebook โพสต์ และอัปโหลดผ่านแบบฟอร์ม
   * รันคำสั่งแปลงภาพเดิมย้อนหลังทั้งระบบ: `php artisan rdb:convert-images-webp` (แปลงภาพนักวิจัยสำเร็จ 147 รายการ และภาพข่าว/งานประชุมเดิม)
   * แปลงโลโก้ระบบ `logo.png` (234 KB) เป็น `logo.webp` (51 KB) ลดขนาดลง 78% พร้อมใช้แท็ก `<picture>` ในหน้า Login
   * Database Migration ขยายขนาดคอลัมน์ชื่อไฟล์รูปภาพ `researcher_picture` และ `con_img` เป็น `varchar(255)` เพื่อรองรับชื่อไฟล์ WebP ได้อย่างสมบูรณ์
2. **ระบบศูนย์ข่าวทุนวิจัย NRIIS และงานประชุมวิชาการ Facebook:**
   * **เครื่องมือทดสอบดึงข้อมูลจำลอง (Dry-Run Inspector)**: เพิ่มหน้า `/backend/research_news/test-sync` และ `/backend/research_conference/test-sync` สำหรับทดสอบเชื่อมต่อ API ดึงข้อมูลสด ดูโครงสร้าง JSON, รายการไฟล์แนบ, และข้อความจำลอง Telegram โดยไม่บันทึกลงฐานข้อมูลจริง
   * **หน้ารายงานสรุปข่าวประจำเดือน**: แยกเมนูย่อย "รายงานประจำเดือน" ที่ `/backend/research_news/reports` ในกลุ่มเมนู "ข่าว/กิจกรรม" ทั้ง Top Navigation และ Left Sidebar
   * **ระบบถังขยะและการลบถาวร (Soft Delete / Force Delete)**: รองรับการย้ายข่าวไปถังขยะ (`data_show = 0`), กู้คืน (`restore`), และการลบถาวร (`forceDelete`) พร้อมลบไฟล์ภาพจริงใน Storage ป้องกันไฟล์ขยะตกค้าง
3. **ระบบ Open Public GET APIs สำหรับข่าวสารและงานประชุมวิชาการ:**
   * เปิด RESTful GET Endpoints สาธารณะสำหรับหน่วยงานภายนอกหรือเว็บไซต์คณะโดยไม่ต้องยืนยันตัวตนด้วย Bearer Token:
     - `GET /api/v1/news` และ `GET /api/v1/news/{id}` (รวมทั้ง alias `/api/news`)
     - `GET /api/v1/conferences` และ `GET /api/v1/conferences/{id}` (รวมทั้ง alias `/api/conferences`)
   * ผนวกรวมเข้าสู่ **Tier 3 (Open Public API)** ในหน้า `/backend/api-management#endpointsTab` พร้อมแสดงตัวอย่าง JSON Response
   * ปรับปรุงปุ่ม **"คัดลอก JSON"** และ **"คัดลอกคำสั่ง cURL"** ด้วยฟังก์ชัน `copyTextToClipboardSafe` รองรับเบราว์เซอร์ทั้งในสภาพแวดล้อม HTTP (`laravel_rdb.test`) และ HTTPS

### อัปเดตประจำวันที่ 5 พฤศจิกายน 2569 (Release 2026.11.05)
1. **การปรับปรุงโครงสร้างโฟลเดอร์ไฟล์อัปโหลดให้ตรงกับระบบเดิม 100% (Legacy 9 Upload Folders Alignment):**
   * จัดระเบียบโฟลเดอร์ใน `storage/app/public/uploads/` ให้ตรงกับระบบเดิม (Yii2 `frontend/web/`) ทั้ง 9 โฟลเดอร์ (`datafiles`, `datapublished`, `datadip`, `datautilize`, `dataresearcher`, `datapts`, `datacoferenceinthai`, `datanewsimg`, `datajournalstatus`)
   * อัปเดตพาธในฐานข้อมูลให้ชี้ไปยังชื่อโฟลเดอร์มาตรฐานใหม่ 8,822 รายการ และปรับปรุง Eloquent Trait `HasDualFilePath` ให้ค้นหาจากโฟลเดอร์ใหม่เป็นลำดับแรกสุด ควบคู่กับ Fallback โฟลเดอร์เดิม
2. **ระบบศูนย์รวมคู่มือการใช้งานระบบและการนำทางอัจฉริยะ (System Manuals Documentation Hub & Seamless Navigation):**
   * พัฒนา Controller ควบคุมการเสิร์ฟคู่มือระบบ (`ManualController`) แยกสิทธิ์การเข้าถึงระหว่างคู่มือหน้าบ้าน (Public) และคู่มือหลังบ้าน (Protected Authenticated Access)
   * ปรับแก้ค่า `APP_URL=http://laravel_rdb.test` ขจัดปัญหา URL Scheme Mismatch และป้องกัน Session Cookie หลุดข้าม Scheme
   * ติดตั้งกลไกฉีด `<base href="http://laravel_rdb.test/manuals/">` อัตโนมัติในหน้าสารบัญรวม (`index.html`) และพาธฐาน ป้องกันปัญหาลิงก์และรูปภาพในคู่มือเกิดข้อผิดพลาด 404
   * **ระบบนำทางแบบแยกสิทธิ์อัจฉริยะ (Role-Aware Seamless Manual Navigation)**:
     - ผู้เยี่ยมชมทั่วไป (Guest): แยกเฉพาะเล่มที่ 1 (ระบบหน้าบ้าน) ปิดกั้นลิงก์คู่มือหลังบ้าน 100% พร้อมปุ่มเข้าสู่ระบบ
     - ผู้เข้าสู่ระบบแล้ว (Authenticated Users): แสดงแถบเมนูนำทางครบทุกเล่ม (สารบัญรวม, เล่ม 1, เล่ม 2, คู่มือย่อ, UAT) พร้อมปุ่ม **"ระบบหลังบ้าน"** ใน Topbar ทุกหน้า สลับอ่านได้ทันทีโดยไม่ต้องกดปุ่มย้อนกลับของเบราว์เซอร์
     - เพิ่มเมนูดรอปดาวน์ **"คู่มือระบบ"** ในแถบเมนูหน้าบ้าน (`<x-frontend-menu />`) สำหรับผู้ที่ล็อกอินแล้ว เปิดคู่มือหลังบ้านได้จากหน้าบ้านทันที
     - ไอคอนคู่มือข้างโลโก้ PCRU RDB ใน Topbar Navbar ทำงานสอดคล้องตามสถานะการล็อกอิน (`@auth`)
     - เพิ่มทางลัดคู่มือใน Profile Dropdown และแถบเมนูด้านซ้ายของระบบหลังบ้าน (Backend Sidebar)
   * ผ่านการตรวจรับด้วยชุดทดสอบ Feature Test: `ManualsAccessTest` (11 เคส 47 assertions) และ `BackendAuditTest` (47 เคส 422 assertions 100%)

### อัปเดตประจำวันที่ 4 พฤศจิกายน 2569 (Release 2026.11.04)
1. **แยกศูนย์สำรองและกู้คืนข้อมูลระบบ (Dedicated Disaster Recovery Hub — The Creator Only):**
   * แยกออกจากหน้าคำสั่งระบบเดิมไปเป็นหน้าจอเฉพาะที่ `/backend/system-backup` (เมนู: **การดูแลระบบ ➔ สำรองและกู้คืนระบบ**)
   * สงวนสิทธิ์ขั้นเด็ดขาดเฉพาะ **The Creator** เท่านั้น พร้อมระบบยืนยันรหัส OTP 6 หลัก และสร้าง Safety Snapshot ก่อนกู้คืนเสมอ
2. **ศูนย์จัดการระบบตั้งเวลาทำงานอัตโนมัติ (Automated Task Scheduler Hub):**
   * เพิ่มหน้าจอควบคุมระบบตั้งเวลาอัตโนมัติที่ `/backend/system-scheduler` (เมนู: **การดูแลระบบ ➔ ระบบตั้งเวลาอัตโนมัติ**)
   * แสดงตารางงานทั้ง 6 งานพร้อมคำนวณรอบการรันล่วงหน้า (Next Due Date พ.ศ. และระยะเวลาคงเหลือ)
   * ปุ่ม **"⚡ รันทันที (Run Now)"** สำหรับสั่งรันงานแบบ On-Demand ผ่านหน้าเว็บ พร้อมหน้าต่าง Terminal Console Output
3. **ระบบแจ้งเตือนผ่าน Telegram Bot อัตโนมัติ (Dual-Tier Telegram Notification System):**
   * **Super Admin Instant Audit Alerts**: เชื่อมต่อเข้ากับ `AuditLog` โดยตรงเพื่อส่งการแจ้งเตือนหาผู้ดูแลระบบสูงสุดทันทีเมื่อมีกิจกรรมสำคัญ (สร้างข้อมูลใหม่, แก้ไข, ลบข้อมูล, อัปโหลดไฟล์แนบ, สำรอง/กู้คืนฐานข้อมูล)
   * **Individual Researcher Alerts**: รองรับการแจ้งเตือนส่วนบุคคลของนักวิจัยผ่านช่องทาง Telegram ควบคู่กับ Email (`mail` + `telegram`) เช่น การอนุมัติเผยแพร่, การส่งกลับแก้ไข, และงานมอบหมายประเมิน
   * เพิ่มฟิลด์ `telegram_chat_id` ในตาราง `user` และรองรับการจัดการในหน้าผู้ใช้งาน
4. **ระบบตั้งเวลาทำงานอัตโนมัติ (Automated Task Scheduling & Crontab Integration):**
   * กำหนด Schedule ใน `routes/console.php` รองรับการทำงานเบื้องหลังอัตโนมัติ 6 งานหลัก:
     - สำรองฐานข้อมูลระบบอัตโนมัติทุกวัน เวลา 02:00 น. (`rdb:backup --type=db`)
     - สำรองข้อมูลเต็มระบบ (ฐานข้อมูล + ไฟล์ Storage) ทุกคืนวันอาทิตย์ เวลา 03:00 น. (`rdb:backup --type=all`)
     - ตรวจสอบและนับจำนวนหน้าเอกสาร PDF ตกค้างทุกวัน เวลา 04:00 น. (`rdb:count-pdf-pages`)
     - เรนเดอร์ภาพหน้าปก WebP ความเร็วสูงทุกวัน เวลา 04:30 น. (`rdb:generate-pdf-covers`)
     - ซิงค์ข่าวทุนวิจัย NRIIS ทุกเช้า เวลา 06:00 น. (`rdb:sync-nriis-news`)
     - ซิงค์ข่าวงานประชุมวิชาการจากเพจ Facebook ทุกเช้า เวลา 06:30 น. (`rdb:sync-facebook-conferences`)
   * ตั้งเวลาบนเซิร์ฟเวอร์บรรทัดเดียว: `* * * * * cd /path/to/laravel_rdb && php artisan schedule:run >> /dev/null 2>&1`
5. **ระบบส่งอีเมลแจ้งเตือนงานวิจัยตามกระบวนการทำงาน (Workflow Email Notifications via SMTP):**
   * ขยายขีดความสามารถของระบบแจ้งเตือนให้ส่งอีเมลคู่กับการแจ้งเตือนในระบบ (Dual Channel: `database` + `mail`):
     - ส่งอีเมลแจ้ง**นักวิจัย**ทันทีเมื่อผลงานวิจัยได้รับการอนุมัติเผยแพร่ (`ResearchItemApprovedNotification`) พร้อมปุ่มลิงก์เปิดดูหน้าบ้าน
     - ส่งอีเมลแจ้ง**นักวิจัย**พร้อมระบุข้อคิดเห็น/เหตุผลเมื่อผลงานถูกส่งกลับแก้ไข (`ResearchItemRejectedNotification`)
     - ส่งอีเมลแจ้ง**ผู้ทรงคุณวุฒิ (Reviewer)** ทันทีเมื่อได้รับมอบหมายให้ประเมินเล่มรายงานวิจัยฉบับสมบูรณ์ (`ReviewAssignedNotification`) พร้อมลิงก์เข้าสู่แบบฟอร์มประเมิน
     - ส่งอีเมลแจ้ง**นักวิจัย**เมื่อเล่มรายงานวิจัยผ่านการประเมินรอบสุดท้าย (`ReviewFinalApprovedNotification`)
   * ปลอดภัยด้วยสถาปัตยกรรม Non-blocking Exception Guard ไม่กระทบต่อการบันทึกข้อมูลหลักหากระบบเครือข่าย SMTP ติดขัด
6. **การ์ดวิเคราะห์สุขภาพเซิร์ฟเวอร์แบบเรียลไทม์ (Live Server Health & Diagnostics Strip):**
   * เพิ่มแถบตรวจสุขภาพระบบ 4 การ์ดในศูนย์คำสั่งระบบ (`/backend/system-commands`):
     - ตรวจสอบสถานะการเชื่อมต่อฐานข้อมูล MariaDB/MySQL และเวอร์ชันเอนจินแบบเรียลไทม์
     - ตรวจสอบคิวงานประมวลผลเบื้องหลัง (Queue Worker Driver, งานที่รอดำเนินการ, งานที่ผิดพลาด `failed_jobs`)
     - ตรวจสอบสถานะระบบตั้งเวลาอัตโนมัติ (6 Scheduled Tasks)
     - ตรวจสอบความพร้อมสำหรับการใช้งานจริง (PHP Version, Memory Limit, Environment และแจ้งเตือนสถานะ `APP_DEBUG`)
7. **ศูนย์บันทึกประวัติกิจกรรมและการแก้ไขระดับเรคคอร์ด (Record-Level Activity & Audit Trail Hub):**
   * ฝังกล่องประวัติกิจกรรม `<x-audit-trail>` ในหน้ารายละเอียดของทุกโมดูล (`rdb_project`, `rdb_published`, `rdb_dip`, `rdb_projectutilize`, `user`, `rdb_researcher`)
   * จัดระเบียบการแสดงผลแบบ **Compact Typography** ไม่แย่งสายตา พร้อมระบบแบ่งหน้า **Client-side Pagination หน้าละ 10 รายการ** รวดเร็วแบบเรียลไทม์ไม่ต้องโหลดหน้าเว็บใหม่
   * บันทึกกิจกรรมของ Sub-Resources อย่างละเอียด: เพิ่ม/แก้ไข/ลบ นักวิจัยร่วมโครงการ (`rdb_project_work`), ผู้เขียนผลงานตีพิมพ์ (`rdb_published_work`), และไฟล์แนบโครงการ (`rdb_project_files`)
   * นโยบาย **Log Hygiene & Noise Reduction**: แยกสถิติจำนวนครั้งการเข้าดู (View Counts) และการดาวน์โหลดไฟล์ (Download Counts) ออกจาก Event Audit Log อย่างเด็ดขาด พร้อมสคริปต์กวาดล้าง Log ขยะเดิม 2,369 รายการ
   * ระบบสิทธิ์ความเป็นส่วนตัวเข้มงวด: นักวิจัยดูได้เฉพาะงานของตนเองและถูกซ่อนข้อมูลเทคนิค (IP/User Agent) ส่วนหน้าบ้านถูกปิดกั้น 100%
8. **เกราะป้องกันความเข้ากันได้ของชื่อฟิลด์ฐานข้อมูล (Schema Integrity Defense Layer & Smart Accessors):**
   * สกัดพจนานุกรมฟิลด์ฉบับสมบูรณ์ 131 ตารางจากฐานข้อมูลจริงไว้ที่ `storage/framework/schema_dict.json`
   * ติดตั้ง Smart Accessors & Aliases บน 16 โมเดลหลัก ขจัดปัญหา **Silent Null** (ค่าว่าง/เครื่องหมายขีด `-` โดยไม่มีแจ้งเตือน) จากความสับสนระหว่างฟิลด์มรดก Yii2 (`*TH`, `*EN`, `ps_name`, `restatus_name`, `utz_typr_name`, `changwat_t`) กับรูปแบบ Laravel มาตรฐาน
   * ผ่านการตรวจรับด้วยชุดทดสอบ Feature Test: `SchemaIntegrityDefenseTest` (17 เคส 77 assertions) และ `RecordLevelAuditLogTest` (12 เคส 46 assertions) 100%
9. **แนวทางและข้อปฏิบัติสำหรับการนำระบบขึ้นใช้งานจริง (Production Deployment Readiness):**
   * เอกสารและคำแนะนำสำหรับการปรับแต่ง `.env` ในสภาวะ Production (`APP_DEBUG=false`, `QUEUE_CONNECTION=database`)
   * คำสั่งรัน Worker ในโหมด Daemon (`php artisan queue:work --tries=3 --timeout=90`) และการแคชระดับสูง (`php artisan optimize`)

### อัปเดตประจำวันที่ 3 พฤศจิกายน 2569 (Release 2026.11.03)
1. **ศูนย์สำรองและกู้คืนข้อมูลระบบ (Full System Backup & Safe Restore Hub — The Creator Only):**
   * พัฒนาระบบสำรองข้อมูล 3 รูปแบบ: เฉพาะฐานข้อมูล MariaDB (`.sql.gz`), เฉพาะไฟล์เอกสาร Storage (`.zip`), และสำรองทั้งระบบแบบเต็ม (Full Archive)
   * ระบบกู้คืนฐานข้อมูลผ่านหน้าเว็บที่ปลอดภัยสูงสุด (Military-Grade Safety): จำกัดสิทธิ์เฉพาะ **The Creator** เท่านั้น พร้อมระบบยืนยันรหัสความปลอดภัย OTP 6 หลักส่งตรงเข้า Email และสร้าง Safety Snapshot สำรองฐานข้อมูลปัจจุบันให้อัตโนมัติก่อนเริ่มกู้คืนเสมอ
   * คำสั่ง Artisan CLI: `php artisan rdb:backup` และ `php artisan rdb:restore` พร้อมคู่มือการตั้งเวลาซิงค์ขึ้น Google Drive อัตโนมัติด้วย Rclone
2. **การตั้งค่าระบบการส่งอีเมลของมหาวิทยาลัย (Mail & SMTP Configuration):**
   * จัดทำคู่มือและโครงสร้างการตั้งค่าระบบ Mail ใน `.env` รองรับทั้ง Google Workspace/Gmail (App Password 16 หลัก), Mail Server มหาวิทยาลัย และโหมด Local Log พร้อมคำสั่งทดสอบการส่งเมลผ่าน Terminal
3. **ปรับปรุงคอนทราสต์โหมดสว่าง (Light Theme Contrast Calibration):**
   * **แถบนำทางระบบหลังบ้าน (Backend Topbar Navbar)**: แก้ไขปุ่ม `[แดชบอร์ด]`, `[เปิดดูหน้าบ้าน]`, ไอคอนย่อ/ขยายเมนูด้านซ้าย และเส้นคั่น ให้แสดงสีคมชัด 100% ไม่กลืนหายเป็นสีขาวบนพื้นขาวในธีมสว่าง
   * **เล่มคู่มือการใช้งาน (Manuals Portal)**: แก้ไขไวยากรณ์ CSS และล็อกสีแถบด้านบนด้วย Academic Navy (`#1E3A5F`) พร้อมปุ่มสลับธีมสีแสดงไอคอนเรียลไทม์
4. **ศูนย์จัดการคำสั่งระบบและการบำรุงรักษา (System Commands & Maintenance Dashboard):**
   * เพิ่มหน้าจอควบคุมคำสั่งระบบในหลังบ้านที่ `/backend/system-commands` ให้ Super Admin สั่งรันคำสั่งบำรุงรักษาได้โดยตรงผ่านหน้าเว็บ
   * รองรับการรันสร้างภาพปก PDF, ตรวจนับจำนวนหน้าย้อนหลัง, ล้างแคชระบบ (Clear Cache), และตรวจเช็คสถานะเอนจินของเซิร์ฟเวอร์
   * แสดงกล่อง Terminal Output Log สวยงาม พร้อมบันทึกประวัติการรันลงใน Audit Trail อัตโนมัติ
5. **ระบบสร้างภาพหน้าปกจากหน้าแรกของไฟล์ PDF ทั้งหน้า (Full-Page PDF Cover Rasterization Engine):**
   * อัปเกรด `PdfCoverService` และคำสั่ง `php artisan rdb:generate-pdf-covers` ให้เรนเดอร์**หน้าแรกของไฟล์ PDF ทั้งหน้า (Full-Page Rasterization)** ด้วยเอนจิน **MuPDF (`PyMuPDF` + `Pillow`)** และ **Poppler `pdftoppm`** แทนการดึงเฉพาะรูปภาพย่อยที่ฝังในไฟล์
   * รองรับการประมวลผลแบบ Batch ความเร็วสูง (`chunkById(100)`) โดยไม่เกิดปัญหาหน่วยความจำเต็ม (`Memory Exhausted`) พร้อมระบบสร้างปกสำรองภาษาไทย (TrueType Font `Leelawadee UI Bold`) แสดงรายชื่อนักวิจัยครบทุกคน ไม่ตัดทอนด้วย "และคณะ"
   * เพิ่มปุ่ม **"ดึงภาพหน้าปกไฟล์ใหม่ อีกครั้ง"** ในหน้ารายละเอียดโครงการวิจัยและผลงานตีพิมพ์หลังบ้าน พร้อมระบบ Cache-Busting `?v=`
   * ประมวลผลสร้างภาพหน้าปกใหม่สำเร็จครบ 100% ทั้งระบบ: โครงการวิจัย 1,864 โครงการ และผลงานตีพิมพ์ 1,601 รายการ (รวม 3,465 รายการ 0 failed)
6. **สถาปัตยกรรม Academic SEO, Google Scholar Metadata & Dynamic XML Sitemaps:**
   * ฝัง Highwire Press (`citation_*`), Dublin Core (`DC.*`), Open Graph พร้อมภาพปก PDF `.webp` และ Schema.org JSON-LD
   * พัฒนาระบบไดนามิกไซต์แมปที่ `/sitemap.xml` แยก 5 ซับไซต์แมป พร้อมตัวกรอง `data_show = 1` เข้มงวด
7. **ปรับผังการแสดงผลหน้ารายละเอียดฝั่งหน้าบ้าน (Frontend Show Layout Optimization):**
   * **โครงการวิจัย (`/rdbproject/{id}`)**: ย้ายการ์ด `ไฟล์เอกสาร (Files)` พร้อมภาพปกหน้าแรกรายงานวิจัยขึ้นมาแสดงเป็นลำดับที่ 1 ด้านขวาบนสุด ก่อนการ์ด `คณะผู้วิจัย (Researchers)`
   * **ผลงานตีพิมพ์ (`/rdbpublished/{id}`)**: ย้ายการ์ด `ไฟล์เอกสาร (Files)` ขึ้นไว้ด้านขวาบนสุดก่อน `ผู้เขียนบทความ (Authors)` และย้ายการ์ด `โครงการวิจัยที่เกี่ยวข้อง` ไปไว้ด้านซ้ายก่อน `บทคัดย่อ (Abstract)`
   * **การนำไปใช้ประโยชน์ (`/rdbprojectutilize/{id}`) และทรัพย์สินทางปัญญา (`/rdbdip/{id}`)**: ย้ายการ์ด `โครงการวิจัยที่เกี่ยวข้อง` ไปไว้ด้านซ้ายก่อนการ์ดรายละเอียด/บทสรุปการประดิษฐ์
8. **ยกระดับมาตรฐาน Dashboard & Data Visualization (`ui-dashboard-visualization`):**
   * เพิ่มมาตรฐาน KPI Cards พร้อม YoY Delta, ตารางจัดอันดับพร้อมเส้นสัดส่วน `3px` (Slim Share Bars), กฎ Small Base (`< 5`), และเกณฑ์ตรวจสอบ 3 ความกว้างหน้าจอ (`1920px`, `1440px`, `375px`)

### อัปเดตประจำวันที่ 2 พฤศจิกายน 2569 (Release 2026.11.02)
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
