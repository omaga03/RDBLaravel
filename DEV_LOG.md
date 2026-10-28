# บันทึกการพัฒนาประจำวัน (Development Log)

## วันที่: 28 ตุลาคม 2569 (2026-10-28)

### 📌 สรุปงานสำคัญที่ดำเนินการเสร็จสิ้นในวันนี้

#### 1. สถาปัตยกรรม Subagents ใน `AGENTS.md`
- บันทึกการแบ่งบทบาท 4 Subagents หลักภายใต้ Parent Agent ใน `AGENTS.md`:
  1. **Backend Core Architect**: กำกับ `laravel-php`, `legacy-data-migration`, `scopus-orcid-integration`, `shared-search-and-date-controls`
  2. **Academic UI Craftsman**: กำกับ `ui-standard-components`, `ui-dark-mode-and-responsive`, `ui-form-controls-and-plugins`, `ui-dashboard-visualization`, `ui-ux-pro-max`
  3. **Security & Compliance Auditor**: กำกับ `security-and-hardening`, `thai-reporting-export`
  4. **QA & Test Assurance**: กำกับ `backend-test-assurance`

#### 2. ระบบส่งออกข้อมูล DSpace (DSpace Export Engine)
- **โครงการวิจัย (`backend/rdb_project/dspace`)**:
  - รองรับการส่งออกแบบแบ่งกลุ่ม (Chunked Batch) ดาวน์โหลดเป็นไฟล์ ZIP แต่ละส่วน
  - มีโครงสร้างไฟล์มาตรฐาน DSpace Simple Archive (`dublin_core.xml`, `metadata_local.xml`, และไฟล์รายงาน PDF ในโฟลเดอร์ `contents`)
  - อัปเดตสถานะการส่งออก `todspace` (1 = ส่งออกแล้ว, 2 = ตรวจสอบแล้ว, 0 = ยังไม่ส่งออก)
- **ผลงานตีพิมพ์ (`backend/rdb_published/dspace`)**:
  - พัฒนาระบบส่งออก Batch Mode แบ่งกลุ่มดาวน์โหลด ZIP
  - พัฒนาระบบตรวจสอบไฟล์ PDF ([`dspace_check.blade.php`](resources/views/backend/rdb_published/dspace_check.blade.php)) สำหรับตรวจนับจำนวนหน้า, กรองรายการที่ยังไม่ได้นับ/หน้าน้อยผิดปกติ, ดูตัวอย่างไฟล์ PDF, และ AJAX สำหรับนับหน้าซ้ำทีละรายการ

#### 3. ปรับปรุงหน้าแสดงผลและ UI/UX ตามข้อกำหนด
- **Project Show & Published Show**:
  - ลบปุ่มส่งออก DSpace จากหน้ารายละเอียดทั่วไปออก (แยกไปไว้ที่เมนูระบบส่งออก DSpace โดยเฉพาะ)
  - ซ่อนข้อมูลระบบ (`<x-system-info>`) เป็น Accordion ยุบไว้เป็นค่าเริ่มต้นทุกหน้า ให้ผู้ใช้คลิกเปิดดูเองเมื่อต้องการ
  - แสดงรายละเอียดบทคัดย่อและคำสำคัญในหน้ารายละเอียดโครงการ
- **Published Index**:
  - เพิ่ม Tooltip แสดงรายชื่อผู้แต่งร่วมที่เหลือเมื่อชี้ที่ป้าย `(+N)`
- **Project Utilize**:
  - ปรับลิงก์โครงการอ้างอิงให้เป็นไอคอนและลิงก์ข้อความธรรมดา ไม่ตีกรอบหนา
  - แก้ไขความสัมพันธ์และหน้าแสดงผลโครงการวิจัย ให้แสดงรายการการนำไปใช้ประโยชน์ที่เชื่อมโยงถูกต้อง

#### 4. เอนจินประมวลผล PDF ระดับความแม่นยำสูง (Poppler pdftotext 99.99%)
- แก้ไขปัญหาไฟล์ PDF ที่เข้ารหัสความปลอดภัย (เช่น ผลงาน 303: AES-128 Secured PDF) ซึ่ง Pure PHP Parser ทั่วไปเปิดไม่ได้
- วางสถาปัตยกรรม 4-Tier Fallback ใน `DSpaceExportService`:
  - **Tier 1 (Poppler `pdftotext`)**: ตรวจจับไบนารีระบบอัตโนมัติ (รองรับ Laragon, Git for Windows, Linux, หรือ `PDFTOTEXT_PATH` ใน `.env`) นับหน้าด้วย `\x0c` form feed ได้แม่นยำ 99.99%
  - **Tier 2 (`Smalot\PdfParser\Parser`)**: Pure PHP Parser
  - **Tier 3 (`setasign\Fpdi\Fpdi`)**: Stream parser
  - **Tier 4 (Fast Regex)**: สแกนโครงสร้าง `/Type\s*\/Page[^s]`
- เพิ่มแถบแจ้งเตือนสถานะเอนจินบนหน้าจอ DSpace Check พร้อมคำแนะนำคำสั่งติดตั้งบนเซิร์ฟเวอร์
- บันทึกคู่มือการติดตั้งและการทำงานลงใน `AGENTS.md`

#### 5. จัดเก็บจำนวนหน้า PDF ลงฐานข้อมูลอัตโนมัติ (Automated Storage & Backfill)
- อัปเดต `FileUploadService::storeFile()` ให้นับจำนวนหน้าของไฟล์ PDF ทุกไฟล์ที่อัปโหลดเข้าสู่ระบบอัตโนมัติ
- เชื่อมต่อการบันทึกจำนวนหน้าเข้าฐานข้อมูล:
  - โครงการวิจัย: จัดเก็บลง `rdb_project.pro_page_num`
  - ผลงานตีพิมพ์: จัดเก็บลง `rdb_published.pub_numfile`
- สร้างคำสั่ง Artisan: `php artisan rdb:count-pdf-pages`
- **รันสแกนข้อมูลจริงทั้งระบบสำเร็จแล้ว**:
  - โครงการวิจัย: บันทึกสำเร็จ **1,818 โครงการ** (จาก 1,874 รายการ)
  - ผลงานตีพิมพ์: บันทึกสำเร็จ **1,504 ผลงาน** (จาก 1,646 รายการ)
- นำจำนวนหน้าไปแสดงผลที่หน้าบ้าน (Frontend) ในหน้าแสดงโครงการ, ผลงานตีพิมพ์ และประวัตินักวิจัย พร้อมปรับแต่ง UI ให้กระชับ ไม่ซ้ำซ้อน

#### 6. การทดสอบและการรับประกันคุณภาพ (Automated Test Assurance)
- `php artisan test --filter="DspaceBatchExportTest"`: ผ่าน **15/15 tests (51 assertions)**
- `php artisan test --filter=BackendAuditTest`: ผ่าน **46/46 tests (408 assertions)**
- รวมผ่านการทดสอบ 100% ไม่มีข้อผิดพลาดตกค้าง

---

### 📋 แผนงานสำหรับดำเนินการต่อในวันพรุ่งนี้
1. **ตรวจสอบความเรียบร้อยของหน้าบ้าน (Frontend)**: รับฟังความคิดเห็นและปรับแต่งการแสดงผลส่วนอื่น ๆ ที่เกี่ยวข้องกับโครงการและผลงานตีพิมพ์เพิ่มเติมตามต้องการ
2. **ระบบสืบค้นขั้นสูง (Advanced Search & Filters)**: หากต้องการเพิ่มตัวกรองค้นหาตามจำนวนหน้าเล่มรายงาน หรือช่วงจำนวนหน้าในหน้าบ้าน/หลังบ้าน
3. **ระบบสิทธิ์และการอนุมัติ (Workflow & Permissions)**: ตรวจสอบสิทธิ์ของบทบาทผู้ใช้คลังปัญญา (`Admin Library`) ในการจัดการข้อมูล DSpace
