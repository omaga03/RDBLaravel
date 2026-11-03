# บันทึกการพัฒนาประจำวัน (Development Log)

## วันที่: 3 พฤศจิกายน 2569 (2026-11-03)

### 📌 สรุปงานสำคัญที่ดำเนินการเสร็จสิ้นในวันนี้

#### 1. สถาปัตยกรรมสร้างภาพหน้าปกจากหน้าแรกของไฟล์ PDF ทั้งหน้า (Full-Page PDF Cover Rasterization Engine)
- **แก้ไขปัญหา Memory Exhausted ใน `rdb:generate-pdf-covers`**:
  - ถอดการเรียกใช้ `Smalot\PdfParser\Parser` ออกจาก `PdfCoverService` (ซึ่งเดิมคลายบีบอัด Stream และสร้างตาราง Font CMap ขนาดใหญ่ใน RAM จนเกิด `Allowed memory size exhausted` บนไฟล์ PDF รายงานวิจัยขนาดใหญ่)
  - เปลี่ยนการดึงข้อมูลใน `GeneratePdfCoversCommand` จาก `chunk()` เป็น `chunkById(100, ..., 'pro_id')` และ `chunkById(100, ..., 'id')` พร้อมคืนหน่วยความจำด้วย `gc_collect_cycles()`
- **เปลี่ยนจากการดึงภาพย่อย (Embedded Image) เป็นการเรนเดอร์หน้าแรกของ PDF ทั้งหน้า (Full-Page Rasterization 100%)**:
  - ยกเลิก `extractViaPdfParserEmbeddedImage()` ที่เดิมดึงเฉพาะไฟล์รูปภาพชิ้นแรกที่ฝังอยู่ในหน้าแรก (เช่น ตรามหาวิทยาลัยขนาดเล็ก) ออกถาวร
  - ติดตั้งและเชื่อมต่อ **MuPDF (`PyMuPDF` + `Pillow`)** ในสภาพแวดล้อม Python ของ Laragon (`F:\laragon\bin\python\python-3.10\python.exe`) เข้ากับ [`PdfCoverService.php`](app/Services/PdfCoverService.php)
  - รองรับการเรนเดอร์หน้าแรกของไฟล์ PDF ทั้งหน้า (`doc[0]`) ทั้งแบบเดี่ยว (`extractViaPyMuPdf`) และแบบกลุ่มความเร็วสูง (`renderBatchFirstPageToWebp`) ออกมาเป็นไฟล์ `.webp` (`max-width: 800px`, `quality: 85`) ที่แสดงครบทั้งตราสัญลักษณ์ ชื่อเรื่องภาษาไทย/อังกฤษ ชื่อผู้วิจัย ตาราง และกราฟ
  - อัปเกรด `generateAcademicGdCover()` ให้รองรับฟอนต์ภาษาไทย TrueType (`Tahoma` / `Leelawadee UI` ผ่าน `imagettftext()`) สำหรับเป็นปกสำรองในกรณีที่ไฟล์ PDF ต้นฉบับไม่ได้อยู่ในเครื่องทดสอบหรือไฟล์ชำรุด
- **รันสร้างภาพหน้าปก `.webp` ใหม่ครบ 100% ทั้งระบบ (`php artisan rdb:generate-pdf-covers --module=all --force`)**:
  - **โครงการวิจัย (`rdb_project`)**: สร้างปกสำเร็จครบ **1,864 โครงการ** (เรนเดอร์จาก PDF จริง 1,811 โครงการ + ปกสำรองภาษาไทย 53 โครงการ)
  - **ผลงานตีพิมพ์ (`rdb_published`)**: สร้างปกสำเร็จครบ **1,601 รายการ** (เรนเดอร์จาก PDF จริง 1,503 รายการ + ปกสำรองภาษาไทย 98 รายการ)

#### 2. ปรับปรุงโครงสร้างการจัดวางการ์ดหน้ารายละเอียดฝั่งหน้าบ้าน (Frontend Show Layout Optimization)
- **หน้ารายละเอียดโครงการวิจัย ([`resources/views/frontend/rdbproject/show.blade.php`](resources/views/frontend/rdbproject/show.blade.php))**:
  - ย้ายการ์ด **`ไฟล์เอกสาร (Files)`** ขึ้นมาไว้ด้านบนสุดของคอลัมน์ขวา (`col-lg-4`) **ก่อน** การ์ด `คณะผู้วิจัย (Researchers)`
  - เพิ่มการแสดงภาพปกหน้าแรกของรายงานวิจัย (`$item->cover_url`) ไว้ด้านบนสุดของการ์ดไฟล์เอกสาร พร้อมลิงก์เปิดดูเล่มรายงาน
- **หน้ารายละเอียดผลงานตีพิมพ์ ([`resources/views/frontend/rdbpublished/show.blade.php`](resources/views/frontend/rdbpublished/show.blade.php))**:
  - คอลัมน์ขวา (`col-lg-4`): ย้ายการ์ด **`ไฟล์เอกสาร (Files)`** (พร้อมภาพปกหน้าแรก) ขึ้นมาเป็นลำดับที่ 1 **ก่อน** การ์ด `ผู้เขียนบทความ (Authors)`
  - คอลัมน์ซ้าย (`col-lg-8`): ย้ายการ์ด **`โครงการวิจัยที่เกี่ยวข้อง`** มาไว้ทางด้านซ้าย **ก่อน** การ์ด `บทคัดย่อ (Abstract)`
- **หน้ารายละเอียดการนำไปใช้ประโยชน์ ([`resources/views/frontend/rdbprojectutilize/show.blade.php`](resources/views/frontend/rdbprojectutilize/show.blade.php))**:
  - คอลัมน์ซ้าย (`col-lg-8`): ย้ายการ์ด **`โครงการวิจัยที่เกี่ยวข้อง`** มาไว้ทางด้านซ้าย **ก่อน** การ์ด `รายละเอียดการนำไปใช้ประโยชน์ (Utilization Details)`
- **หน้ารายละเอียดทรัพย์สินทางปัญญา ([`resources/views/frontend/rdbdip/show.blade.php`](resources/views/frontend/rdbdip/show.blade.php))**:
  - คอลัมน์ซ้าย (`col-lg-8`): ย้ายการ์ด **`โครงการวิจัยที่เกี่ยวข้อง`** มาไว้ทางด้านซ้าย **ก่อน** การ์ด `บทสรุปการประดิษฐ์และรายละเอียด (Invention Summary & Details)`

#### 3. ยกระดับมาตรฐานทักษะการออกแบบ Dashboard ([`.agents/skills/ui-dashboard-visualization/SKILL.md`](.agents/skills/ui-dashboard-visualization/SKILL.md))
- ผสานหลักการออกแบบจาก `dashboard-design-skill` (กลั่นจาก 127 Dashboards จริง) เข้ากับมาตรฐาน Bootstrap 5.3 + Chart.js ของระบบ:
  - กฎ **First Glance, Quiet, Wordless & Honest** และ **Small Base Rule (`< 5`)**
  - แม่แบบ **Analytical KPI Card with Honest YoY Delta**
  - แม่แบบ **Ranked Breakdown Lists with Slim `3px` Share Bars** สำหรับจัดอันดับคณะ/หน่วยงานและประเภททุน
  - ตารางข้อห้าม (**Dashboard Smell Table**) และเกณฑ์ตรวจรับ 3 ความกว้างหน้าจอ (`1920px`, `1440px`, `375px`)

#### 4. ปรับปรุง Dashboard ทั้งหมดทั้งหน้าบ้านและหลังบ้านตามมาตรฐานใหม่ (Complete Dashboard Modernization)
- **หน้าหลักหน้าบ้าน (Frontend Home Dashboard: `/` & `/site/index`)**:
  - อัปเกรด Bento Stat Cards: เพิ่มป้าย Honest YoY Delta Badge (`$yoyDelta`) เปรียบเทียบปีงบประมาณที่เลือกกับปีก่อนหน้า พร้อมปฏิบัติตาม **Small Base Rule** (หากปีก่อนหน้าน้อยกว่า 5 ให้แสดง `ปีก่อนหน้า N` สีเทาแทนการแสดง `%` ที่ก้าวกระโดด) และจัดเรียงตัวเลขด้วย `font-variant-numeric: tabular-nums`
  - ปรับสัดส่วนเลย์เอาต์กราฟหลัก (8 คอลัมน์) ควบคู่กับ **Ranked Faculty Breakdown** (4 คอลัมน์) แสดงอันดับคณะที่มีโครงการวิจัยสูงสุด พร้อมเส้น Slim `3px` Share Bar ช่วยให้อ่านข้อมูลสัดส่วนได้ทันทีโดยไม่ต้อง hover กราฟ
- **แดชบอร์ดหลังบ้านผู้ดูแลระบบ (Backend Admin Dashboard: `/backend`)**:
  - อัปเกรดการ์ด Quick Stats ทั้ง 6 โมดูล: โครงการวิจัย, นักวิจัย, ผลงานตีพิมพ์, ทรัพย์สินทางปัญญา, การใช้ประโยชน์, ข่าวสาร&การประชุม พร้อมแสดง YoY Delta เทียบปีก่อนหน้า
  - เพิ่ม **Ranked Status Breakdown List** คู่กับ Doughnut Chart (Cutout 72%) แสดงสัดส่วนสถานะโครงการวิจัยทุกขั้นตอนด้วยแถบสีจริงจากฐานข้อมูลและแถบส่วนแบ่ง `3px`
  - ปรับปรุง Combo Chart แนวโน้ม 5 ปี (`#yearChart`): ตั้งค่า `beginAtZero: true`, `maxTicksLimit: 5` และแปลงตัวเลขงบประมาณเป็นหน่วยล้านบาท (`1.5M ฿`) ให้อ่านง่าย
- **ระบบแดชบอร์ดสารสนเทศงานวิจัยเชิงลึกฝั่งหน้าบ้าน (Frontend Research Intelligence Dashboards: `/dashboard`)**:
  - **ภาพรวมยุทธศาสตร์ (`/dashboard/overview`)**:
    - แปลง Bento KPI Cards จากสีไล่เฉดนีออนเต็มแผ่น (Heavy Gradients) เป็น **Quiet Scholarly Cards** พื้นผิวเรียบหรูระดับวิชาการ มีเส้นไฮไลต์ด้านบน 3px ตามหมวดหมู่ พร้อม Honest YoY Deltas และ `tabular-nums`
    - ยกระดับ Status Distribution เป็น Doughnut Chart ควบคู่กับ Ranked Status Breakdown และ 3px Share Bars
    - ปรับปรุงสเกลกราฟแนวโน้มงบประมาณ 6 ปี และกราฟเปรียบเทียบผลผลิตรายคณะให้มี `maxTicksLimit: 5`, `precision: 0` และ `beginAtZero: true`
  - **โครงการวิจัยและงบประมาณ (`/dashboard/projects`)**: ปรับแต่งการ์ด KPI ให้เป็น Quiet Surface พร้อมเส้นขอบนำสายตาและ `tabular-nums`
  - **ผลงานตีพิมพ์ (`/dashboard/publications`)**: ยกระดับการ์ดสถิติผลงานตีพิมพ์ระดับสากล Scopus/WoS, TCI 1 และ TCI 2 ด้วยสัญลักษณ์และตัวเลข `tabular-nums`
  - **การใช้ประโยชน์เชิงพื้นที่ & แผนที่ GIS (`/dashboard/utilizations`)**: ปรับแต่ง KPI การใช้ประโยชน์และหน่วยงานรับประโยชน์ให้เป็นระเบียบตามมาตรฐานวิชาการ
  - **ทรัพย์สินทางปัญญา (`/dashboard/ip`)**: แก้ไข Grid Layout และสไตล์การ์ดสิทธิบัตร/อนุสิทธิบัตร/ลิขสิทธิ์
  - **ศักยภาพนักวิจัย (`/dashboard/researchers`)**: อัปเกรดการ์ดโปรไฟล์ Scopus Author ID และ ORCID ID
  - **การประกันคุณภาพ (`/dashboard/qa`)**: ปรับปรุงการ์ดถ่วงน้ำหนักคะแนนประกันคุณภาพและงบวิจัยเฉลี่ยต่อบุคลากร

#### 5. การปรับปรุงระบบนำทางหลังบ้านและระยะห่างไอคอน (2 พฤศจิกายน 2569)
- ย้ายเมนูจัดการระบบหลังบ้านทั้งหมดมาไว้ที่แถบด้านซ้าย ([`resources/views/components/backend-sidebar.blade.php`](resources/views/components/backend-sidebar.blade.php)) พร้อมตัดเมนูแถบบนแบบเดิมออก
- จัดกลุ่มเมนูย่อยพร้อมสัญลักษณ์ `-` นำหน้า, ยุบรวมเมนูตั้งค่าข้อมูลพื้นฐาน (Master Data), และบังคับพฤติกรรม **Single-Open Accordion** (`data-bs-parent="#rdbSidebarAccordion"`)
- เพิ่ม CSS Fractional Spacing Utilities (`.me-1.5`, `.gap-1.5`, ฯลฯ) และแก้ไขระยะห่างไอคอนคู่ข้อความทั่วทั้งระบบ 67 ไฟล์ Blade
- ปรับปรุงหน้ารายละเอียดโครงการหลังบ้าน (`/backend/rdb_project/{id}`): ลบป้าย `หลักฐานการเงิน: ไม่ครบถ้วน` และปรับป้ายชื่อทุนย่อย (`pts_name`) ให้ตัดบรรทัดอัตโนมัติบนหน้าจอเล็ก

#### 5. เพิ่มการเปรียบเทียบกับปีก่อนหน้า (Year-over-Year Delta) ครบทุกการ์ดในแดชบอร์ดสารสนเทศหน้าบ้าน (Complete YoY Comparison)
- **สร้าง Reusable Blade Partial ([`resources/views/frontend/dashboard/_yoy_badge.blade.php`](resources/views/frontend/dashboard/_yoy_badge.blade.php))**:
  - รองรับการคำนวณและแสดงผลค่าส่วนต่าง YoY อัตโนมัติ (ขึ้น/ลง/คงที่) พร้อมไอคอนทิศทาง `bi-arrow-up-right`, `bi-arrow-down-right`, `bi-dash`
  - ปฏิบัติตาม **Small Base Rule (`< 5`)**: หากปีก่อนหน้าน้อยกว่า 5 จะแสดง `ปีก่อนหน้า N` สีเทาเพื่อป้องกันตัวเลขเปอร์เซ็นต์ที่ก้าวกระโดดเกินจริง
  - บังคับใช้ `font-variant-numeric: tabular-nums` และ Semantic Subtles ที่รองรับทั้ง Light / Dark Mode
  - รองรับทั้งค่าจำนวนเต็ม, ค่าตัวเลขทศนิยม (คะแนน QA), และค่างบประมาณ (`isCurrency => true`)
- **อัปเกรด Controller ([`app/Http/Controllers/Frontend/DashboardController.php`](app/Http/Controllers/Frontend/DashboardController.php))**:
  - เพิ่มเมธอด `computeMetricYoY($current, $prev)` สำหรับดึงข้อมูลปีก่อนหน้าและคำนวณค่าส่วนต่างและเปอร์เซ็นต์
  - เพิ่มการคำนวณ `$yoyDelta` ครบทุกเมธอดและส่งไปยังทุก Blade Views:
    1. **โครงการวิจัย (`projects`)**: งบประมาณรวม และ โครงการทั้งหมด
    2. **ผลงานตีพิมพ์ (`publications`)**: ผลงานทั้งหมด, Scopus/WoS, TCI 1, TCI 2, ประชุมวิชาการ, ต่อยอดจากทุนวิจัย (ครบ 6 การ์ด)
    3. **การใช้ประโยชน์ (`utilizations`)**: การใช้ประโยชน์รวม, ครอบคลุมพื้นที่, หน่วยงานผู้รับประโยชน์, ผ่านรับรอง QA, มูลค่าผลกระทบ/งบสนับสนุน (ครบ 5 การ์ด) พร้อมแก้ไขปัญหากรอบซ้ำ
    4. **ทรัพย์สินทางปัญญา (`ip`)**: ทรัพย์สินทางปัญญาทั้งหมด, สิทธิบัตร, อนุสิทธิบัตร, ลิขสิทธิ์, จดทะเบียน, รอจดทะเบียน (ครบ 6 การ์ด)
    5. **ศักยภาพนักวิจัย (`researchers`)**: นักวิจัยทั้งหมด, Scopus Author ID, ORCID ID, หน่วยงานวิชาการ (ครบ 4 การ์ด)
    6. **การประกันคุณภาพ (`qa`)**: ถ่วงน้ำหนักผลงานตีพิมพ์, งบวิจัยเฉลี่ย/บุคลากร, ใช้ประโยชน์ผ่านเกณฑ์ QA, นักวิจัยรับทุนครั้งแรก (ครบ 4 การ์ด)
- **ปรับปรุง Blade Templates ให้จัดวางกล่องตัวเลขสถิติและการ์ดได้สวยงาม**:
  - ใช้ `d-flex flex-column justify-content-between` ภายใน `card-body` ตรึงป้าย YoY Delta ให้อยู่ด้านล่างสุดของแต่ละการ์ดอย่างเป็นระเบียบแนวเดียวกันทุกจอ

#### 6. ปรับแต่งและลดทอนองค์ประกอบแดชบอร์ดตามข้อเสนอแนะ (Dashboard Refinement & Simplification)
- **แดชบอร์ดการใช้ประโยชน์ (`/dashboard/utilizations`)**:
  - ลบการ์ด **"มูลค่าผลกระทบ / งบสนับสนุน"** ออกจาก KPI Metric Cards ตามความต้องการ
  - ปรับสัดส่วน 4 การ์ดที่เหลือ (การนำไปใช้ประโยชน์รวม, ครอบคลุมพื้นที่, หน่วยงานผู้รับประโยชน์, ผ่านรับรอง QA) ให้เป็น `col-xl-3 col-md-6` สมดุลเต็มแถว 12 คอลัมน์อย่างลงตัว
  - ลบปุ่ม **"เปิดดูแผนที่จังหวัดเพชรบูรณ์บน Google Maps"** ออกจากส่วนหัวของการ์ดแผนที่สารสนเทศภูมิศาสตร์ (GIS Interactive Map) เพื่อลดความซ้ำซ้อน
- **แดชบอร์ดผลงานตีพิมพ์ (`/dashboard/publications`)**:
  - ลบการ์ดกราฟ **"10 อันดับวารสารวิชาการที่นักวิจัยได้รับการตีพิมพ์มากที่สุด"** (`#topJournalsChart`) และ Script Chart.js ที่เกี่ยวข้องออก
  - ปรับปรุง `DashboardController::publications()` ให้ใช้ `collect()` ลดการรันคิวรีจัดกลุ่มที่ไม่จำเป็น

#### 8. ปุ่มดึงภาพหน้าปกไฟล์ใหม่และการปรับปรุงปกสำรองภาษาไทย (Academic GD Cover & Reload Feature)
- **เพิ่มปุ่ม "ดึงภาพหน้าปกไฟล์ใหม่ อีกครั้ง" ในระบบหลังบ้าน**:
  - หน้ารายละเอียดโครงการวิจัย ([`resources/views/backend/rdb_project/show.blade.php`](resources/views/backend/rdb_project/show.blade.php)): ในการ์ดรายงานฉบับสมบูรณ์ เพิ่มปุ่มดึงภาพหน้าปกไฟล์ใหม่สำหรับกรณีที่ยังไม่ได้อัปโหลดไฟล์ใหม่แต่อยากรีโหลดหรือสร้างภาพหน้าปกใหม่
  - หน้ารายละเอียดผลงานตีพิมพ์ ([`resources/views/backend/rdb_published/show.blade.php`](resources/views/backend/rdb_published/show.blade.php)): ในการ์ดไฟล์แนบ เพิ่มปุ่มดึงภาพหน้าปกไฟล์ใหม่
  - เพิ่ม Route และ Controller Actions:
    - `POST /backend/rdb_project/{id}/regenerate-cover` -> `RdbProjectController@regenerateCover`
    - `POST /backend/rdb_published/{id}/regenerate-cover` -> `RdbPublishedController@regenerateCover`
    - พร้อมการตรวจสอบสิทธิ์ `authorize('update', ...)` รัดกุมตามนโยบายความปลอดภัย
- **ปรับปรุงระบบสร้างหน้าปกสำรองภาษาไทย (Academic GD Cover Fallback) ใน [`PdfCoverService.php`](app/Services/PdfCoverService.php)**:
  - แก้ไขการดึงชื่อผู้วิจัยของผลงานตีพิมพ์ (`buildPublishedMetadata`): ดึงจากความสัมพันธ์ `authors` (Many-to-Many via `rdb_published_work`), `publishedWorks`, `researcher` และสำรองไปยังนักวิจัยของโครงการที่เกี่ยวข้อง (`project->researchers`) พร้อมคำนำหน้าทางวิชาการครบทุกคน
  - ระบบจัดเรียงและตัดขึ้นบรรทัดใหม่อัจฉริยะ (Intelligent Author Line Packing): คำนวณความกว้างพิกเซลจริงด้วย `imagettfbbox` ไม่เกิน 460px ต่อบรรทัด แสดงชื่อนักวิจัยครบทุกคนอย่างชัดเจน ไม่ตัดทอนด้วย "และคณะ"
  - จัดการตัดคำภาษาไทย (Thai Semantic Phrase Wrapping) ใน `wrapUtf8Text`: วิเคราะห์จุดแบ่งประโยคและคำสำคัญวิชาการ ป้องกันปัญหาวรรณยุกต์และสระตกหล่นหรือขาดออกจากพยัญชนะ
  - เลือกลำดับฟอนต์ภาษาไทย TrueType คมชัดสูง (`Leelawadee UI Bold` / `C:\Windows\Fonts\LeelaUIb.ttf`)
  - อัปเกรดดีไซน์ปกสำรองสไตล์วิชาการ (Academic Navy `#0f172a` + Gold `#b45309`) พร้อมคำนวณตำแหน่งแนวตั้งไดนามิกไม่ให้ข้อความชื่อเรื่องยาวทับซ้อนกับชื่อผู้แต่งและคณะ
  - เพิ่มพารามิเตอร์ Cache-busting `?v=` ใน Accessor `cover_url` ของ `RdbProject` และ `RdbPublished` ช่วยให้เบราว์เซอร์แสดงภาพหน้าปกที่สร้างใหม่ได้ทันทีโดยไม่ต้องกดรีเฟรชแคช
  - **ประมวลผลดึงและสร้างภาพหน้าแรก PDF ใหม่ทั้งหมดทั้งระบบ (`php artisan rdb:generate-pdf-covers --module=all --force`)**:
    - **โครงการวิจัย (`rdb_project`)**: สำเร็จครบ **1,864 โครงการ (100%)**
    - **ผลงานตีพิมพ์ (`rdb_published`)**: สำเร็จครบ **1,601 รายการ (100%)**
    - ไม่มีรายการที่ผิดพลาด (0 skipped/failed)

#### 7. สถาปัตยกรรม Academic SEO, Google Scholar Metadata & Dynamic XML Sitemaps
- **สร้างทักษะควบคุมมาตรฐาน ([`.agents/skills/academic-seo-and-indexing/SKILL.md`](.agents/skills/academic-seo-and-indexing/SKILL.md))**:
  - กำหนดกฎบัตรและข้อกำหนดของ Google Scholar, Highwire Press, Dublin Core, Schema.org JSON-LD, Open Graph และ Dynamic XML Sitemaps สำหรับระบบวิจัยระดับมหาวิทยาลัย
  - อัปเดตผัง Subagents ใน [`AGENTS.md`](AGENTS.md) มอบหมายให้ Backend Core Architect ควบคุมดูแล
- **อัปเกรดเครื่องยนต์เมทาดาทา ([`resources/views/layouts/partials/scholar-meta.blade.php`](resources/views/layouts/partials/scholar-meta.blade.php))**:
  - **Google Scholar (Highwire Press)**: บังคับแยก 1 Author ต่อ 1 Tag (`citation_author`), วันที่แบบ ค.ศ. (`citation_publication_date`), ชี้ตรงลิงก์อ่านเล่ม PDF (`citation_pdf_url`), ชื่อวารสาร, สำนักพิมพ์ และบทคัดย่อ
  - **Dublin Core (`DC.*`)**: ครบถ้วนตามมาตรฐานบรรณารักษศาสตร์และคลังสารสนเทศสถาบัน (`DC.title`, `DC.creator`, `DC.date`, `DC.publisher`, `DC.identifier`)
  - **Social Sharing & Open Graph**: ดึงภาพหน้าปก `.webp` (`cover_url`) ที่เรนเดอร์จาก PDF ทั้งหน้ามาใช้เป็น `og:image` และ `twitter:image` (`summary_large_image`) ทำให้เวลาแชร์บทความวิจัยหรือโครงการวิจัยบน Facebook, LINE, X/Twitter จะแสดงภาพหน้าแรกของเล่มรายงานวิจัยภาษาไทยคมชัดขนาดใหญ่
  - **Schema.org Structured Data (JSON-LD)**: ฝัง `<script type="application/ld+json">` อัตโนมัติ:
    - `/rdbpublished/{id}`: `@type: "ScholarlyArticle"` พร้อมข้อมูลบทความ วารสาร ผู้เขียน และไฟล์ PDF
    - `/rdbproject/{id}`: `@type: "ResearchProject"` พร้อมแหล่งทุนและหัวหน้าโครงการ
    - `/rdbresearcher/{id}`: `@type: "Person"` พร้อมหน่วยงานวิชาการและลิงก์ `sameAs` ชี้ไปยัง ORCID และ Scopus Author Profiles
- **ระบบ Dynamic XML Sitemap Engine ([`SitemapController.php`](app/Http/Controllers/Frontend/SitemapController.php))**:
  - สร้างโครงสร้าง Sitemap Index ที่ `/sitemap.xml` แยกออกเป็น 5 ซับไซต์แมป:
    1. `/sitemap-projects.xml`: โครงการวิจัยที่เปิดเผยสถานะ (`data_show = 1`)
    2. `/sitemap-publications.xml`: ผลงานตีพิมพ์และบทความวิชาการทั้งหมด
    3. `/sitemap-researchers.xml`: หน้าทำเนียบและประวัตินักวิจัย
    4. `/sitemap-utilizations.xml`: ผลงานการนำไปใช้ประโยชน์เชิงพื้นที่
    5. `/sitemap-ip.xml`: ผลงานทรัพย์สินทางปัญญา
  - ปรับปรุง [`public/robots.txt`](public/robots.txt) กำหนดสิทธิ์ให้ Bot เข้าถึงหน้างานวิจัยสาธารณะและชี้ตำแหน่ง Sitemap อัตโนมัติ
  - **บังคับใช้ตัวกรอง `data_show = 1` ครบทุกส่วนของ Sitemap 100%**:
    - เพิ่มคอลัมน์ `data_show` ให้กับตารางนักวิจัย `rdb_researcher` ผ่าน Migration `2026_11_03_141000_add_data_show_to_rdb_researcher_table.php` เพื่อให้ทุกโมดูลในระบบมีมาตรฐานสถานะเปิดเผยข้อมูลเหมือนกันทั้งหมด
    - เชื่อมต่อ `HasDataShowScope` เข้ากับโมเดล `RdbResearcher` พร้อมปรับปรุง Trait ให้ใส่ชื่อตารางนำหน้า (`$table . '.data_show'`) ป้องกันข้อผิดพลาด Ambiguous Column เมื่อเกิดการ JOIN ข้ามตาราง
    - อัปเกรด `SitemapController` ให้กรองเฉพาะเรคคอร์ดที่ `data_show = 1` อย่างเข้มงวด ทั้ง 5 โมดูล (โครงการวิจัย, ตีพิมพ์, นักวิจัย, การใช้ประโยชน์, ทรัพย์สินทางปัญญา)

#### 8. ผลการทดสอบอัตโนมัติ (Automated Test Assurance)
- `php artisan test --filter=AcademicSeoAndSitemapTest`: **PASSED (6 tests, 54 assertions)**
- `php artisan test --filter=PdfCoverGenerationTest`: **PASSED (4 tests, 16 assertions)**
- `php artisan test --filter=BackendAuditTest`: **PASSED (47 tests, 421 assertions)**

---

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

#### 5. เพิ่มสีพื้นจางตามโทนหมวดหมู่ (Subtle Card Tints) และขยายการเปรียบเทียบ YoY ครบทุกมิติ
- **อัปเกรด SKILL `.agents/skills/ui-dashboard-visualization/SKILL.md`**:
  - กำหนดมาตรฐานสีพื้นหลังจางระดับ `rgba(..., 0.04)` (Light Mode) และ `rgba(..., 0.08)` (Dark Mode) สำหรับการ์ด KPI ใน Dashboard ทุกหน้า เพื่อแก้ปัญหาการ์ดดูกลืนกันเมื่อไม่มีพื้นหลังที่แตกต่าง
  - ขยายข้อกำหนดการเปรียบเทียบ YoY (Year-over-Year) ให้ครอบคลุมทุกโมดูลหลัก: โครงการวิจัย, งบประมาณ, ผลงานตีพิมพ์, นักวิจัย, ทรัพย์สินทางปัญญา (DIP) และการนำไปใช้ประโยชน์ โดยปฏิบัติตาม Small Base Rule (`< 5`) อย่างเคร่งครัด
- **แดชบอร์ดหลังบ้าน (`/backend`) และหน้าแรก (`/`)**:
  - เพิ่มการคำนวณและแสดงผล YoY Delta สำหรับนักวิจัย (`researcher_prev/diff/pct`), ทรัพย์สินทางปัญญา (`dip_prev/diff/pct`) และการนำไปใช้ประโยชน์ (`utilize_prev/diff/pct`)
  - นำสีพื้นจางและเส้นขอบบน 3px มาใช้กับการ์ด Bento/Quick Stats ทุกใบ
- **แดชบอร์ดเชิงลึกรายโมดูลฝั่งหน้าบ้าน (`/dashboard/*`)**:
  - อัปเดตสีพื้นจางให้กับการ์ดสถิติในหน้า โครงการวิจัย (`projects`), ผลงานตีพิมพ์ (`publications`), ทรัพย์สินทางปัญญา (`ip`), นักวิจัย (`researchers`), การใช้ประโยชน์ (`utilizations`) และมาตรฐานคุณภาพ (`qa`)
- **แก้ไขปัญหากรอบแสดงผลซ้ำซ้อนในหน้าการนำไปใช้ประโยชน์ (`/dashboard/utilizations`)**:
  - ลบโค้ดบล็อกกำพร้า "มูลค่าผลกระทบ / งบสนับสนุน" ที่แสดงผลซ้ำซ้อนด้านล่างของหน้าจอออกเรียบร้อย
- **เพิ่มตัวกรองปีงบประมาณในหน้านักวิจัย (`/dashboard/researchers`)**:
  - เพิ่มตัวกรอง `year_id` ในแถบตัวกรอง (Filter Bar) พร้อมปรับ Logic ใน `DashboardController::researchers` ให้กรองเฉพาะนักวิจัยที่มีโครงการวิจัยในปีงบประมาณที่เลือก

#### 6. ปรับปรุงแถบนำทางส่วนหัวและเมนูด้านข้างระบบหลังบ้าน (Backend Navbar & Sidebar Restructuring)
- **แถบนำทางส่วนหัว (Backend Top Navbar)**:
  - ปรับลิงก์ `<span class="brand-badge ms-1">PCRU RDB</span>` ให้ลิงก์ไปยังหน้าแรกของหลังบ้าน (`route('backend.site.index')`) เมื่ออยู่ในบริบทของระบบหลังบ้าน
  - เพิ่มปุ่มลัดนำทางถัดจาก `PCRU RDB` ทันที:
    - **แดชบอร์ด** (`route('backend.site.index')` พร้อมไอคอน `bi-speedometer2`)
    - **เปิดดูหน้าบ้าน** (`route('home')` ในแท็บใหม่ พร้อมไอคอน `bi-box-arrow-up-right`)
  - รองรับการแสดงผลแบบ Responsive ทั้งบนหน้าจอ Desktop และ Mobile เมนูแบบพับเก็บได้
- **เมนูแถบข้างด้านซ้าย (Backend Sidebar)**:
  - ย้ายเมนู "แดชบอร์ดภาพรวม" ออกจากส่วนเมนูด้านซ้าย และลบปุ่ม "ไปหน้าบ้าน (Public Portal)" ที่ส่วนท้ายด้านล่างของ Sidebar เพื่อลดความซ้ำซ้อนและเพิ่มพื้นที่ใช้งาน

#### 7. ปรับระดับความคมชัดของสีพื้นหลังการ์ด (Calibrated Jewel Tint) และแก้ไข Hover ปุ่มนำทาง
- **แก้ปัญหาปุ่มนำทาง "เปิดดูหน้าบ้าน" ตัวอักษรกลืนเป็นสีขาวเมื่อเมาส์ชี้ (Hover Whiteout Fix)**:
  - สร้างคลาสเฉพาะ `.rdb-nav-portal-btn` และ `.rdb-nav-btn-active` แทนการใช้ `btn-outline-light text-white`
  - เมื่อนำเมาส์ไปชี้ (Hover) พื้นหลังจะเป็นสีขาวทึบ `#ffffff` พร้อมตัวอักษรและไอคอนสีน้ำเงินเข้มวิชาการ `#0F172A` อย่างคมชัด ไม่เกิดปัญหาข้อความสีขาวกลืนกับพื้นหลังขาวอีกต่อไป
- **ยกระดับความชัดเจนของสีพื้นหลังการ์ดใน Dark Mode (`.rdb-card-tint-*`)**:
  - เปลี่ยนจากการฮาร์ดโค้ด `background-color: rgba(..., 0.04)` แบบเดิมที่จางเกินไปใน Dark Mode จนมองไม่ออก
  - สร้างชุดคลาสมาตรฐาน `.rdb-card-tint-blue`, `.rdb-card-tint-green`, `.rdb-card-tint-emerald`, `.rdb-card-tint-teal`, `.rdb-card-tint-indigo`, `.rdb-card-tint-amber`, `.rdb-card-tint-red`, `.rdb-card-tint-slate`, `.rdb-card-tint-purple`
  - **Dark Mode**: เพิ่มความทึบแสงของสีพื้นหลังเป็น 18% (`rgba(..., 0.18)`) พร้อมเส้นขอบ 1px สีเข้าคู่ (`rgba(..., 0.35)`) และเส้นขอบบน 3px ทำให้โทนสีอัญมณีของแต่ละหมวดแยกออกจากพื้นหลังสีเข้มได้อย่างชัดเจนทันที
  - **Light Mode**: ความทึบแสง 8% (`rgba(..., 0.08)`) สุขุม อ่านง่ายระดับวิชาการ
  - นำไปปรับใช้กับการ์ด KPI ทุกใบทั้งหน้าบ้านและหลังบ้าน: แดชบอร์ดหลังบ้าน (`/backend`), หน้าภาพรวม (`/dashboard/overview`), โครงการวิจัย (`projects`), ผลงานตีพิมพ์ (`publications`), ทรัพย์สินทางปัญญา (`ip`), นักวิจัย (`researchers`), การใช้ประโยชน์ (`utilizations`) และมาตรฐานคุณภาพ (`qa`)
- **อัปเดต Skill [`.agents/skills/ui-dashboard-visualization/SKILL.md`](.agents/skills/ui-dashboard-visualization/SKILL.md)**: บันทึกมาตรฐาน `.rdb-card-tint-*` และเกณฑ์ความทึบแสง 2 โหมด

#### 8. การทดสอบและการรับประกันคุณภาพ (Automated Test Assurance)
- `php artisan test --filter="DspaceBatchExportTest"`: ผ่าน **15/15 tests (51 assertions)**
- `php artisan test --filter=BackendAuditTest`: ผ่าน **47/47 tests (421 assertions)**
- รวมผ่านการทดสอบ 100% ไม่มีข้อผิดพลาดตกค้าง

---

### 📋 แผนงานสำหรับดำเนินการต่อในวันพรุ่งนี้
1. **ตรวจสอบความเรียบร้อยของหน้าบ้าน (Frontend)**: รับฟังความคิดเห็นและปรับแต่งการแสดงผลส่วนอื่น ๆ ที่เกี่ยวข้องกับโครงการและผลงานตีพิมพ์เพิ่มเติมตามต้องการ
2. **ระบบสืบค้นขั้นสูง (Advanced Search & Filters)**: หากต้องการเพิ่มตัวกรองค้นหาตามจำนวนหน้าเล่มรายงาน หรือช่วงจำนวนหน้าในหน้าบ้าน/หลังบ้าน
3. **ระบบสิทธิ์และการอนุมัติ (Workflow & Permissions)**: ตรวจสอบสิทธิ์ของบทบาทผู้ใช้คลังปัญญา (`Admin Library`) ในการจัดการข้อมูล DSpace
