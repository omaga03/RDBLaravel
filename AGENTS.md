# Project Development Guidelines & Rules

## 1. Core Architecture & Standards (laravel-php)
- ใช้ PHP 8.x และ Laravel 12 Conventions เสมอ
- ยึดหลัก PSR-12, Clean Architecture และ Thin Controllers (ย้าย Validation ไปที่ FormRequest เสมอ)
- **Senior Minimalist Mindset (YAGNI & Anti-Over-Engineering)**: 
  - ยึดหลัก "The best code is the code you never wrote"
  - ใช้ซ้ำ (Reuse) Blade Components (`<x-card>`, `<x-search-bar>`), Classes, และ Helpers เดิมก่อนเสมอ
  - ใช้ฟีเจอร์ Built-in ของ Laravel 12, PHP 8 และ Bootstrap 5.3 ห้ามติดตั้ง Composer/npm packages ใหม่โดยไม่จำเป็น
- **Eloquent Optimization & N+1 Prevention**: บังคับใช้ Eager Loading (`with()`, `load()`) ทุกครั้งที่มีการดึง Relation ไปแสดงผลใน Blade ห้าม query ซ้ำซ้อนใน loop
- ห้ามเขียน Complex Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่มีการสร้างหรือแก้ไข Feature ใหม่
- คำนึงถึงความปลอดภัย (Authorization Gates, File Upload UUID Renaming, Security Headers, Rate Limiting)
- รายละเอียด: `.agents/skills/laravel-php/SKILL.md`

## 2. UI, Colors & Dark Mode Standards (ui-dark-mode-and-responsive & ui-standard-components)
- **ระบบสี Semantic Tokens & Token Locking**: ห้ามฮาร์ดโค้ดสี `#fff`, `#000`, หรือ `.text-dark` เด็ดขาด เพื่อรองรับทั้ง Light Mode (Daylight Editorial Slate) และ Dark Mode (Carbon-Navy Night Reading)
  - ข้อความ: ใช้ `text-body`, `text-body-secondary`, `text-body-emphasis`
  - พื้นหลัง: ใช้ `bg-body`, `bg-body-secondary`, `bg-body-tertiary`
  - เส้นขอบ: ใช้ `border`, `border-body-secondary`, `border-subtle` (เส้นคมชัดระดับ 1px Hairline)
- **Anti-AI-Slop & Scholarly Editorial Craftsmanship**:
  - ห้ามใช้สีไล่เฉดม่วง-ชมพู/นีออนเรืองแสง หรือเงาฟุ้งกระจายแบบเว็บ AI สำเร็จรูป ให้ใช้โทนสีวิชาการ (Deep Academic Navy `#1E3A5F`, Royal Blue `#2563EB`, Citation Amber `#D97706`, Emerald `#059669`)
  - ตัวเลขงบประมาณ ปี พ.ศ. รหัสโครงการ และตารางสถิติ ต้องใช้ `font-variant-numeric: tabular-nums` เสมอเพื่อให้หลักตัวเลขตรงแนวเดียวกัน
- **ป้ายสถานะ (Status Badges)**: ใช้รูปแบบ `-subtle` พร้อมไอคอนเสมอ เช่น:
  - สำเร็จ/เผยแพร่: `badge bg-success-subtle text-success-emphasis border border-success-subtle`
  - ดำเนินการ/รอตรวจ: `badge bg-warning-subtle text-warning-emphasis border border-warning-subtle`
  - ยกเลิก/ซ่อน: `badge bg-danger-subtle text-danger-emphasis border border-danger-subtle`
- **โครงสร้างการแสดงผล**: ใช้ `<x-card>` และ Empty State ที่มีไอคอนเมื่อไม่พบข้อมูล
- รายละเอียด: `.agents/skills/ui-dark-mode-and-responsive/SKILL.md` และ `.agents/skills/ui-standard-components/SKILL.md`

## 3. UI Forms, Plugins & Visualizations (ui-form-controls-and-plugins, ui-dashboard-visualization & ui-ux-pro-max)
- **ฟอร์มและการค้นหา**: TomSelect AJAX, Autocomplete ค้นหากลุ่มคำ, Multiple Select ปลอดภัย
- **Multi-Step Form (Wizard UI)**: การแบ่งฟอร์มงานวิจัยที่มีรายละเอียดมากด้วย Bootstrap Nav-Pills ลด Cognitive Load
- **PDF Preview & File Upload UX**: แสดงตัวอย่าง PDF ใน Modal พร้อมป้ายสถานะไฟล์ก่อนดาวน์โหลด
- **Dashboard & กราฟ**: ใช้ Stat Cards พร้อม Watermark Icons และโทนสีกราฟ Chart.js ที่อ่านง่ายทั้งสองโหมด
- **ดีไซน์ระบบและชุดสีอัจฉริยะ**: พาเลทสี, การจัดช่องว่าง, การจับคู่ฟอนต์ภาษาไทยและอังกฤษ
- รายละเอียด: `.agents/skills/ui-form-controls-and-plugins/SKILL.md`, `.agents/skills/ui-dashboard-visualization/SKILL.md` และ `.agents/skills/ui-ux-pro-max/SKILL.md`

## 4. File Storage, Multi-Path Fallback & PDF Processing Engine (legacy-data-migration)
- จัดเก็บไฟล์ใหม่แยกตามโฟลเดอร์โมดูลใน `storage/app/public/{module}/` (UUID ปลอดภัย)
- มีระบบ Smart Multi-Fallback ค้นหาไฟล์เก่าอัตโนมัติ 3 ลำดับชั้น (ไฟล์ใหม่ -> โฟลเดอร์ `uploads/` -> โฟลเดอร์เดิม Yii2)
- การดาวน์โหลดและเปิดดูไฟล์ทุกโมดูลต้องวิ่งผ่าน Controller Route เสมอ (ห้ามฮาร์ดโค้ด `asset('storage/...')` ใน Blade)
- หากไม่พบไฟล์บนเซิร์ฟเวอร์ ต้องแสดงผลผ่าน Custom 404 Template ที่เป็นมิตร
- **PDF Processing Engine & Page Counting Architecture (ความแม่นยำ 99.99%)**:
  - ใช้สถาปัตยกรรม 4-Tier Fallback ใน `DSpaceExportService`:
    1. **Tier 1 (Poppler `pdftotext`)**: ตรวจจับไบนารีระบบอัตโนมัติ (PATH, Laragon, Git for Windows, หรือ `PDFTOTEXT_PATH` ใน `.env`) รองรับเอกสาร PDF ทุกเวอร์ชันและไฟล์ที่มีการเข้ารหัสความปลอดภัย (AES-128 / Protected PDF) นับหน้าด้วย form feed `\x0c` ได้แม่นยำ 99.99%
    2. **Tier 2 (`Smalot\PdfParser\Parser`)**: Pure PHP Parser สำหรับเซิร์ฟเวอร์ที่ไม่มี poppler-utils
    3. **Tier 3 (`setasign\Fpdi\Fpdi`)**: Parser แบบโครงสร้าง Stream
    4. **Tier 4 (Fast Regex)**: สแกนโครงสร้าง `/Type\s*\/Page[^s]`
  - **การติดตั้งบนเซิร์ฟเวอร์ Production (SysAdmin Guide)**:
    - Ubuntu / Debian: `sudo apt update && sudo apt install -y poppler-utils`
    - CentOS / RHEL / AlmaLinux: `sudo dnf install -y poppler-utils`
    - กำหนดพาธเองใน `.env`: `PDFTOTEXT_PATH="/usr/bin/pdftotext"`
  - **การจัดเก็บจำนวนหน้าอัตโนมัติ (Automated Storage)**:
    - ทุกครั้งที่มีการอัปโหลดไฟล์ PDF รายงานฉบับสมบูรณ์ของโครงการวิจัย (`pro_file`) ระบบจะนับและบันทึกจำนวนหน้าลง `rdb_project.pro_page_num` อัตโนมัติ
    - ทุกครั้งที่มีการอัปโหลดไฟล์ PDF ผลงานตีพิมพ์ (`pub_file`) ระบบจะนับและบันทึกจำนวนหน้าลง `rdb_published.pub_numfile` อัตโนมัติ
    - คำสั่งรันนับหน้าและบันทึกย้อนหลังทั้งระบบ: `php artisan rdb:count-pdf-pages` (ตัวเลือก `--module=all|project|published`, `--force`, `--limit=`)
- รายละเอียด: `.agents/skills/legacy-data-migration/SKILL.md`

## 5. Security, Hardening & Academic Sync (security-and-hardening, thai-reporting-export & scopus-orcid-integration)
- ป้องกัน CSRF, XSS (`HtmlContentHelper::clean()` สำหรับ `{!! !!}`), IDOR, Raw SQL Injection (บังคับ Parameter Binding `?`) และ CSV Formula Injection เสมอ
- **Structured Security Audit (Adversarial Verification)**: สำรวจ Trust Boundaries, ตรวจสอบ Role Scope / Mass Assignment, และพิสูจน์ย้อนแย้งตลอดเส้นทาง (`Route` -> `FormRequest` -> `Controller` -> `Policy` -> `Blade`) เพื่อขจัด False Positives
- การ Export ข้อมูลขนาดใหญ่ต้องใช้ Stream พร้อม `\xEF\xBB\xBF` (UTF-8 BOM) เพื่อให้ Microsoft Excel แสดงภาษาไทยถูกต้อง 100%
- การเชื่อมโยง Scopus API, ORCID และการตรวจสอบรหัสประจำตัวประชาชนของนักวิจัย
- รายละเอียด: `.agents/skills/security-and-hardening/SKILL.md`, `.agents/skills/thai-reporting-export/SKILL.md` และ `.agents/skills/scopus-orcid-integration/SKILL.md`

## 6. Automated Test Assurance & Systematic Debugging (backend-test-assurance)
- **Systematic Root-Cause Debugging**: ห้ามเดาสุ่มแก้โค้ด (Trial-and-Error) ต้องสืบค้นต้นตอ (Backward Data Flow Tracing), เทียบกับโมดูลต้นแบบ, และแก้ที่ Root Cause เสมอ
- **Verification Before Completion**: ห้ามยืนยันว่างานเสร็จโดยไม่มีหลักฐานการรันตรวจสอบจริง (`php -l`, Route check, หรือ PHPUnit test)
- รันและรักษาชุดทดสอบหลัก `php artisan test --filter=BackendAuditTest` และ Feature tests ให้ผ่าน 100% ทุกครั้งที่มีการแก้โค้ดสำคัญ
- รายละเอียด: `.agents/skills/backend-test-assurance/SKILL.md`

## 7. Shared Search, Researcher & Thai Date Controls (shared-search-and-date-controls)
- **การค้นหาโครงการวิจัย**: ส่ง JSON ที่มีทั้ง `id` และ `value` พร้อมรายชื่อนักวิจัยทั้งหมดที่เกี่ยวข้องในโครงการ (`all_researchers_text`, `researchers`), รองรับการค้นหาแบบกลุ่มคำ (`title_keywords` โหมด `all`/`any`), ค้นหาครอบคลุมนักวิจัยทุกคน และแสดง Match Sources ไฮไลต์ชื่อ-ตำแหน่งนักวิจัยในหลังบ้าน
- **การค้นหานักวิจัย**: ตัดคำนำหน้าชื่อไทยอัตโนมัติ (`นาย|นาง|...`), ค้นหาแยกคำและชื่อภาษาอังกฤษ พร้อม Pre-population ผ่าน hidden input `researcher_name`
- **ปฏิทิน พ.ศ. และรูปแบบวันที่**: แสดงผล พ.ศ. เสมอ (เต็ม: `24 ตุลาคม 2569`, ย่อ: `24 ต.ค. 2569`), จัดเก็บ ISO `YYYY-MM-DD` (ค.ศ.), และใช้ชื่อตัวแปรมาตรฐาน `date_start` และ `date_end` ทั่วทั้งระบบ
- **การตรวจสอบช่วงวันที่ (Date Range Validation)**:
  - หน้ารายการค้นหา (Index Filter): หากระบุวันใดวันหนึ่งต้องระบุให้ครบทั้งสองวัน และ `date_end >= date_start` หากผิดพลาดให้ redirect กลับพร้อมแจ้งเตือนภาษาไทย
  - หน้าแบบฟอร์ม (FormRequest): ใช้กฎ `after_or_equal:pro_date_start` พร้อมแปลงค่าด้วย `ThaiDateHelper::toIsoDate()` ใน `prepareForValidation()`
- รายละเอียด: `.agents/skills/shared-search-and-date-controls/SKILL.md`

## 8. Subagents Architecture & Role Delegation
เมื่อมีงานพัฒนาฟีเจอร์ขนาดใหญ่ การแก้ไขเอกสาร หรือการ Refactor ให้ Parent/Orchestrator Agent กระจายงานไปยัง 5 Subagents หลักตามขอบเขตความเชี่ยวชาญ:

```
                                         ┌───────────────────────────────────┐
                                         │     Parent / Orchestrator Agent   │
                                         │ (คุมภาพรวม / วางแผน / สื่อสารกับ User)│
                                         └─────────────────┬─────────────────┘
           ┌─────────────────────┬─────────────────────────┼─────────────────────────┬─────────────────────┐
           ▼                     ▼                         ▼                         ▼                     ▼
┌────────────────────┐ ┌────────────────────┐   ┌────────────────────┐    ┌────────────────────┐ ┌────────────────────┐
│ 1. Backend Core    │ │ 2. Academic UI     │   │ 3. Security &      │    │ 4. QA & Test       │ │ 5. Documentation & │
│    Architect       │ │    Craftsman       │   │    Compliance      │    │    Assurance       │ │    Manual Curator  │
└────────────────────┘ └────────────────────┘   └────────────────────┘    └────────────────────┘ └────────────────────┘
```

1. **Backend Core Architect** (สถาปัตยกรรมหลังบ้านและฐานข้อมูล)
   - **Skills ที่กำกับ**: `laravel-php`, `legacy-data-migration`, `scopus-orcid-integration`, `shared-search-and-date-controls`
   - **หน้าที่**: ออกแบบ Thin Controllers/FormRequest/Services, ป้องกัน N+1 Queries (`with()`), จัดการ Schema/Fallback ดึงไฟล์เดิม, คุมตรรกะค้นหาและแปลงวันที่ พ.ศ. ↔ ค.ศ.
2. **Academic UI Craftsman** (ส่วนติดต่อผู้ใช้สไตล์วิชาการ)
   - **Skills ที่กำกับ**: `ui-standard-components`, `ui-dark-mode-and-responsive`, `ui-form-controls-and-plugins`, `ui-dashboard-visualization`, `ui-ux-pro-max`
   - **หน้าที่**: สร้าง/แต่ง Blade Views ด้วย Semantic Tokens (Light/Dark Mode), ป้องกัน AI-Slop (คุมโทนสีวิชาการ Deep Navy / Amber), จัดการ `<x-card>`, Status Badges, `tabular-nums`, TomSelect และ Wizard UI
3. **Security & Compliance Auditor** (ผู้ตรวจความปลอดภัยและความถูกต้องข้อมูล)
   - **Skills ที่กำกับ**: `security-and-hardening`, `thai-reporting-export`
   - **หน้าที่**: ตรวจสอบความปลอดภัยเชิงลึก (CSRF, XSS ใน `{!! !!}`, IDOR, Parameter Binding, Policy Gates) และดูแลการ Export ภาษาไทยด้วย UTF-8 BOM (`\xEF\xBB\xBF`)
4. **QA & Test Assurance** (ผู้ทดสอบและสืบค้นบั๊กเชิงระบบ)
   - **Skills ที่กำกับ**: `backend-test-assurance`
   - **หน้าที่**: สืบสวนต้นตอบั๊ก (Root-Cause Debugging), ตรวจสอบ syntax (`php -l`), รันและดูแลชุดทดสอบ `php artisan test --filter=BackendAuditTest` และ `SystemAcceptancePlanExecutionTest` ให้ผ่าน 100% ก่อนส่งมอบงาน
5. **Documentation & Manual Curator** (ผู้ดูแลคู่มือติดตั้งระบบ `README.md` และชุดเล่มคู่มือการใช้งาน `docs/manuals/`)
   - **Skills ที่กำกับ**: `documentation-and-manuals` (`.agents/skills/documentation-and-manuals/SKILL.md`)
   - **หน้าที่**:
     - ดูแลและปรับปรุง `README.md` ให้เป็นคู่มือการติดตั้งและกู้คืนระบบจาก Git บน Laragon (`httpd`, `php-8.x`, `mariadb`) ที่ผู้เริ่มต้นทำตามได้ 100% พร้อมรายการ PHP Extensions ครบถ้วน
     - ดูแลและอัปเดตชุดเล่มคู่มือการใช้งานใน `docs/manuals/` ทั้งรูปแบบ `.md` และ `.html` ให้ตรงกับฟีเจอร์ของระบบเสมอ ได้แก่:
       - `VOLUME_1_FRONTEND_USER_MANUAL.{md,html}` (เล่ม 1: คู่มือระบบหน้าบ้าน 8 บท)
       - `VOLUME_2_BACKEND_ADMIN_RESEARCHER_MANUAL.{md,html}` (เล่ม 2: คู่มือระบบหลังบ้านและนักวิจัย 9 บท)
       - `RESEARCHER_QUICK_START_CHEATSHEET.{md,html}` (คู่มือฉบับย่อ 3 นาทีสำหรับนักวิจัย)
       - `SYSTEM_ACCEPTANCE_TEST_PLAN.{md,html}` (แผนและรายงานผลการตรวจรับระบบ UAT)
       - `USER_MANUAL_DEVELOPMENT_PLAN.{md,html}` และ `index.html` (หน้าสารบัญรวมศูนย์คู่มือ)
     - ควบคุมการจับภาพหน้าจอจริง (`docs/manuals/screenshots/*.png` ความละเอียด 1920x1080 พร้อมกรอบสีแดงและตัวเลขกำกับขั้นตอน `[1]`, `[2]`, `[3]`) และการคอมไพล์ HTML5 ด้วยสคริปต์ใน `.agents/skills/documentation-and-manuals/scripts/`

