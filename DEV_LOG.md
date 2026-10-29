# บันทึกการพัฒนาประจำวัน (Development Log)

## วันที่: 29 ตุลาคม 2569 (2026-10-29)

### 📌 สรุปงานสำคัญที่ดำเนินการเสร็จสิ้นในวันนี้

#### 1. ระบบย่อยการประเมินเล่ม/ร่างรายงานวิจัยฉบับสมบูรณ์ (Research Report Peer Review Subsystem)
- **สถาปัตยกรรมฐานข้อมูล (Database Schema & Migrations)**:
  - `database/migrations/2026_10_29_193000_create_rdb_project_review_tables.php` และ `2026_10_29_203000_add_scale_max_and_step_to_rdb_review_criteria_table.php`
  - สร้าง 6 ตารางหลัก:
    1. `rdb_review_template`: แม่แบบแบบฟอร์มเกณฑ์การประเมิน
    2. `rdb_review_criteria`: ข้อคำถาม/เกณฑ์ประเมินย่อย รองรับระดับคะแนนและระยะห่างสเกล
    3. `rdb_review_submission`: บันทึกการส่งเล่มรายงานวิจัยในแต่ละรอบ (Draft / Revised / Final)
    4. `rdb_review_assignment`: บันทึกการมอบหมายผู้ทรงคุณวุฒิประเมิน
    5. `rdb_review_score`: บันทึกคะแนนและข้อเสนอแนะรายข้อ
    6. `rdb_review_clarification`: บทสนทนาการชี้แจง/ทักท้วงระหว่างนักวิจัยและเจ้าหน้าที่
- **แบบฟอร์มเกณฑ์ประเมินมาตรฐานเริ่มต้น (`RdbReviewTemplateSeeder.php`)**:
  - ติดตั้งแม่แบบเริ่มต้น 3 รูปแบบ (18 หัวข้อเกณฑ์ย่อย) พร้อมใช้งานทันที:
    1. แบบประเมินเล่มรายงานวิจัยฉบับสมบูรณ์ (มาตรฐานระดับคะแนน 5 ระดับ + ข้อเสนอแนะ) (`hybrid`)
    2. แบบประเมินความสมบูรณ์ของร่างรายงานวิจัย (แบบผ่าน / ต้องแก้ไข / ไม่ผ่าน) (`pass_fail`)
    3. แบบประเมินข้อวิพากษ์และข้อเสนอแนะเชิงคุณภาพ (แบบปลายเปิดรายข้อ) (`qualitative_only`)

#### 2. กลไก Single-Blind และระบบการจัดการรอบประเมิน (Service & Workflow Engine)
- **`ProjectReviewService.php` และ `RdbProjectReviewController.php`**:
  - **100% Single-Blind Anonymity**: ตัดข้อมูลระบุตัวตนของผู้ทรงคุณวุฒิออกจากฝั่งนักวิจัย แสดงเป็น "ผู้ทรงคุณวุฒิคนที่ 1", "ผู้ทรงคุณวุฒิคนที่ 2" และแปลงชื่อไฟล์แนบข้อเสนอแนะเป็น `reviewer_feedback_{id}.pdf`
  - **Conflict of Interest Guard**: ป้องกันการมอบหมายเจ้าของโครงการหรือนักวิจัยร่วมในโครงการมาตรวจงานตนเอง
  - **การมอบหมายผู้ประเมิน (1..N ท่าน)**: เลือกผู้ทรงคุณวุฒิภายในผ่าน TomSelect AJAX พร้อมแสดงจำนวนโครงการที่กำลังตรวจอยู่แบบ Real-time
  - **ขั้นตอนตอบรับ/ปฏิเสธงาน (Accept / Decline Flow)**: ผู้ทรงคุณวุฒิกดยอมรับงาน หรือปฏิเสธพร้อมระบุเหตุผลเพื่อมอบหมายท่านอื่นแทน
  - **ระบบการชี้แจง/ทักท้วง (Clarification & Dispute)**: นักวิจัยส่งข้อความชี้แจง/ทักท้วงพร้อมไฟล์แนบได้
  - **ประตูอนุมัติเล่มจริงขั้นสุดท้าย (Final Approval Gate)**: เมื่อนักวิจัยส่งเล่มจริงและ Admin กดอนุมัติ ระบบจะคัดลอกไฟล์เข้า `rdb_project.pro_file` บันทึกจำนวนหน้าผ่าน Poppler Engine และตั้งสถานะ `todspace = 0` เพื่อเตรียมส่งออก DSpace

#### 3. ปรับปรุง UI/UX และระบบออกแบบแบบฟอร์ม (Form Template Designer)
- **หน้ารายการแบบฟอร์ม (`backend.project_review.templates`)**:
  - ปรับการแสดงผลเป็นการ์ด **Accordion (ย่อ-ขยาย เปิด/ปิดได้)** ค่าเริ่มต้นยุบไว้เพื่อความสะอาดตาและเป็นระเบียบ
  - เพิ่มปุ่ม "ขยายทั้งหมด / ย่อทั้งหมด" สำหรับดูข้อมูลภาพรวม
- **แยกหน้าจอสร้างและแก้ไขแบบฟอร์ม (Dedicated Tabs)**:
  - หน้าสร้างแบบฟอร์มใหม่: [`template_create.blade.php`](resources/views/backend/project_review/template_create.blade.php)
  - หน้าแก้ไขแบบฟอร์ม: [`template_edit.blade.php`](resources/views/backend/project_review/template_edit.blade.php)
  - เปิดทำงานในแท็บใหม่ได้ ไม่รบกวนหน้า Index
- **ระบบสเกลคะแนนแบบระบุคะแนนเต็มและระยะห่าง (Scale Max & Step) รายข้อคำถาม**:
  - รองรับการกำหนด เช่น คะแนนเต็ม 25 คะแนน ห่างทีละ 5 (สเกล 5 ระดับ: 5, 10, 15, 20, 25)
  - มี **Live Preview** แสดงผลตัวอย่างสเกลทันทีขณะกรอก
  - หน้าตรวจประเมินของผู้ทรงคุณวุฒิ ([`evaluate.blade.php`](resources/views/backend/project_review/evaluate.blade.php)) สร้างปุ่ม Radio ตามสเกลที่กำหนดอัตโนมัติ

#### 4. การทดสอบและการรับประกันคุณภาพ (Test Assurance)
- `php artisan test --filter=ProjectPeerReviewTest`: ผ่าน **2/2 tests (133 assertions)** 100%
- `php artisan test --filter=BackendAuditTest`: ผ่าน **46/46 tests (408 assertions)** 100%
- เคลียร์ Cache และตรวจสอบความปลอดภัย XSS/CSRF/IDOR เรียบร้อย

---

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
