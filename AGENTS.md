# Project Development Guidelines & Rules

## 1. Core Architecture & Standards (laravel-php)
- ใช้ PHP 8.x และ Laravel 12 Conventions เสมอ
- ยึดหลัก PSR-12 และ Clean Architecture
- ห้ามเขียน Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่มีการสร้างหรือแก้ไข Feature ใหม่
- คำนึงถึงความปลอดภัย (Authorization Gates, File Upload UUID Renaming, Security Headers, Rate Limiting)
- รายละเอียด: `.agents/skills/laravel-php/SKILL.md`

## 2. UI, Colors & Dark Mode Standards (ui-dark-mode-and-responsive & ui-standard-components)
- **ระบบสี Semantic Tokens**: ห้ามฮาร์ดโค้ดสี `#fff`, `#000`, หรือสีขอบเข้มเด็ดขาด เพื่อรองรับทั้ง Light และ Dark Mode (`data-bs-theme="dark"` / `"light"`)
  - ข้อความ: ใช้ `text-body`, `text-body-secondary`, `text-body-emphasis`
  - พื้นหลัง: ใช้ `bg-body`, `bg-body-secondary`, `bg-body-tertiary`
  - เส้นขอบ: ใช้ `border`, `border-body-secondary`, `border-subtle`
- **ป้ายสถานะ (Status Badges)**: ใช้รูปแบบ `-subtle` พร้อมไอคอนเสมอ เช่น:
  - สำเร็จ/เผยแพร่: `badge bg-success-subtle text-success-emphasis border border-success-subtle`
  - ดำเนินการ/รอตรวจ: `badge bg-warning-subtle text-warning-emphasis border border-warning-subtle`
  - ยกเลิก/ซ่อน: `badge bg-danger-subtle text-danger-emphasis border border-danger-subtle`
- **โครงสร้างการแสดงผล**: ใช้ `<x-card>` และ Empty State ที่มีไอคอนเมื่อไม่พบข้อมูล
- รายละเอียด: `.agents/skills/ui-dark-mode-and-responsive/SKILL.md` และ `.agents/skills/ui-standard-components/SKILL.md`

## 3. UI Forms, Plugins & Visualizations (ui-form-controls-and-plugins, ui-dashboard-visualization & ui-ux-pro-max)
- **ฟอร์มและการค้นหา**: TomSelect AJAX, Autocomplete ค้นหากลุ่มคำ, Multiple Select ปลอดภัย
- **Dashboard & กราฟ**: ใช้ Stat Cards พร้อม Watermark Icons และโทนสีกราฟ Chart.js ที่อ่านง่ายทั้งสองโหมด
- **ดีไซน์ระบบและชุดสีอัจฉริยะ**: พาเลทสี, การจัดช่องว่าง, การจับคู่ฟอนต์ภาษาไทยและอังกฤษ
- รายละเอียด: `.agents/skills/ui-form-controls-and-plugins/SKILL.md`, `.agents/skills/ui-dashboard-visualization/SKILL.md` และ `.agents/skills/ui-ux-pro-max/SKILL.md`

## 4. File Storage & Multi-Path Fallback (legacy-data-migration)
- จัดเก็บไฟล์ใหม่แยกตามโฟลเดอร์โมดูลใน `storage/app/public/{module}/` (UUID ปลอดภัย)
- มีระบบ Smart Multi-Fallback ค้นหาไฟล์เก่าอัตโนมัติ 3 ลำดับชั้น (ไฟล์ใหม่ -> โฟลเดอร์ `uploads/` -> โฟลเดอร์เดิม Yii2)
- การดาวน์โหลดและเปิดดูไฟล์ทุกโมดูลต้องวิ่งผ่าน Controller Route เสมอ (ห้ามฮาร์ดโค้ด `asset('storage/...')` ใน Blade)
- หากไม่พบไฟล์บนเซิร์ฟเวอร์ ต้องแสดงผลผ่าน Custom 404 Template ที่เป็นมิตร
- รายละเอียด: `.agents/skills/legacy-data-migration/SKILL.md`

## 5. Security, Hardening & Academic Sync (security-and-hardening, thai-reporting-export & scopus-orcid-integration)
- ป้องกัน CSRF, XSS, IDOR และ CSV Formula Injection เสมอ
- การ Export ข้อมูลขนาดใหญ่ต้องใช้ Stream พร้อม `\xEF\xBB\xBF` (UTF-8 BOM) เพื่อให้ Microsoft Excel แสดงภาษาไทยถูกต้อง 100%
- การเชื่อมโยง Scopus API, ORCID และการตรวจสอบรหัสประจำตัวประชาชนของนักวิจัย
- รายละเอียด: `.agents/skills/security-and-hardening/SKILL.md`, `.agents/skills/thai-reporting-export/SKILL.md` และ `.agents/skills/scopus-orcid-integration/SKILL.md`

## 6. Automated Test Assurance (backend-test-assurance)
- รันและรักษาชุดทดสอบหลัก `php artisan test --filter=BackendAuditTest` และ Feature tests ให้ผ่าน 100% ทุกครั้งที่มีการแก้โค้ดสำคัญ
- รายละเอียด: `.agents/skills/backend-test-assurance/SKILL.md`
