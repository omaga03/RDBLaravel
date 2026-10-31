# 🏛️ คู่มือติดตั้งและเรียกคืนระบบ RDBLaravel สำหรับผู้เริ่มต้น (Zero to Hero)
> **ระบบบริหารจัดการฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์ (PCRU RDB)**  
> พัฒนาด้วย Laravel 12 ทำงานบน Laragon (Apache, MariaDB, PHP 8.5)

---

## 📌 สิ่งที่ต้องเตรียมไว้ในเครื่อง
ติดตั้งโปรแกรม **Laragon (Full)** ไว้ที่ `C:\laragon` ให้เรียบร้อย

---

## 🛠️ ขั้นตอนที่ 1: สลับเวอร์ชันใน Laragon ให้ตรงตามสเปก

เปิดโปรแกรม **Laragon** ขึ้นมา จากนั้นคลิกขวาที่พื้นที่ว่างในหน้าต่าง Laragon เพื่อเลือกเวอร์ชัน:

1. **เลือก Apache:** คลิกขวา -> **Apache** -> **Version** -> เลือก `httpd-2.4.69-261002-Win64-VS18`
2. **เลือก PHP:** คลิกขวา -> **PHP** -> **Version** -> เลือก `php-8.5.12-1-nts-Win32-vs17-x64`
3. **เลือก MariaDB:** คลิกขวา -> **MySQL** -> **Version** -> เลือก `mariadb-13.0.2-winx64`
4. **เปิด Extension ที่จำเป็นใน PHP:**
   * คลิกขวาที่ Laragon -> **PHP** -> **php.ini** (โปรแกรม Notepad จะเปิดขึ้นมา)
   * กด `Ctrl + F` ค้นหาคำว่า `;extension=zip`
   * **ลบเครื่องหมาย `;` ด้านหน้าออก** ให้เหลือเพียง `extension=zip`
   * ตรวจสอบว่า Extension เหล่านี้เปิดอยู่ด้วย: `pdo_mysql`, `mysqli`, `curl`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `bcmath`, `exif`
   * กดบันทึกไฟล์ (`Ctrl + S`) แล้วปิด Notepad
5. กดปุ่ม **"Stop"** แล้วกด **"Start All"** ในหน้าต่าง Laragon

---

## 💻 ขั้นตอนที่ 2: เปิด Terminal ของ Laragon

> ⚠️ **ข้อสำคัญมาก:** ห้ามเปิด CMD หรือ PowerShell ธรรมดาจาก Windows  
> ให้คลิกปุ่ม **"Terminal"** บนหน้าต่าง Laragon เท่านั้น (หน้าต่างสีดำจะเปิดขึ้นมาพร้อมสภาพแวดล้อมและ PATH ที่ตั้งค่าไว้เสร็จสรรพ)

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

# 2. นำเข้าข้อมูลจริงจากไฟล์ SQL Dump
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
*(ระบบจะตรวจความถูกต้องทั้งหมด 46 รายการ ผลลัพธ์ต้องแสดง **46 passed** สีเขียวทั้งหมด)*

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
  * **ชื่อผู้ใช้งาน:** `655169`
  * **รหัสผ่าน:** `password123`
