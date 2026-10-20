---
name: legacy-data-migration
description: Procedures and standards for handling legacy Yii2 database structures, file storage directory standards, multi-path fallback resolution, and Buddhist/Gregorian year conversion.
---

# Legacy Data Migration & File Storage Standards (laravel_rdb)

Use this skill when dealing with tables, files, or dates migrated from the old Yii2 system, as well as managing file access architecture across Laravel frontend and backend.

---

## 1. มาตรฐานตำแหน่งจัดเก็บไฟล์ (Directory Standards)

ไฟล์ใหม่ทั้งหมดที่อัปโหลดผ่านระบบ Laravel จะถูกจัดเก็บลงโฟลเดอร์ตามโมดูลภายใต้ `storage/app/public/` โดยตรง (ไม่มี `uploads/` นำหน้า) พร้อมมีระบบ Fallback ไปยังโฟลเดอร์นำเข้าและโฟลเดอร์เดิมของ Yii2 อัตโนมัติ:

| หมวดหมู่ | โฟลเดอร์ระบบใหม่ (Laravel New Uploads) | โฟลเดอร์ระบบเดิม (Legacy / Migrated Fallback) | ฟิลด์ฐานข้อมูล (ใหม่ / เดิม) |
| :--- | :--- | :--- | :--- |
| **เล่มรายงานวิจัย** | `storage/app/public/reports/` | `uploads/projects/` และ `datafiles/{id}/` | `pro_file_path` / `pro_file` |
| **บทคัดย่อ** | `storage/app/public/abstracts/` | `uploads/projects/` และ `datafiles/{id}/` | `pro_abstract_file_path` / `pro_abstract_file` |
| **เอกสารแนบโครงการ** | `storage/app/public/project_files/` | `uploads/project_files/` | `prf_path` / `rf_files` |
| **ผลงานตีพิมพ์** | `storage/app/public/published/` | `uploads/rdb_published/` และ `datapublished/{id}/` | `pf_path` / `pub_file` |
| **ทรัพย์สินทางปัญญา (DIP)** | `storage/app/public/dips/` | `uploads/dips/` และ `datadip/{id}/` | `dip_path` / `dip_files` |
| **การนำไปใช้ประโยชน์** | `storage/app/public/utilize/` | `uploads/utilize/` และ `datautilize/{id}/` | `uf_path` / `utz_files` |
| **ประเภทย่อยโครงการ** | `storage/app/public/project_types_sub/` | `uploads/project_types_sub/` | `pts_path` / `pts_file` |
| **รูปถ่ายนักวิจัย** | `storage/app/public/researchers/` | `uploads/researchers/` และ `dataresearcher/` | `rf_path` / `researcher_picture` |
| **ข่าวสาร / การประชุม** | `storage/app/public/news/`, `conference/` | `uploads/news/`, `uploads/conference/` | `news_path`, `con_path` |

---

## 2. โมเดลและฟังก์ชันค้นหาพาธ (Model Path Resolution)

ปรับปรุง Method ในทุก Model ให้ตรวจสอบหลายชั้นอย่างชาญฉลาด (Smart Multi-Fallback):
1. ตรวจสอบพาธระบบใหม่ก่อน (เช่น `dip_path`, `pro_file_path`, `pf_path`, `uf_path`, `pts_path`)
2. ตรวจสอบพาธนำเข้า `uploads/...`
3. ตรวจสอบพาธโครงสร้างเดิมจาก Yii2 (เช่น `datadip/{id}/{file}`, `datapublished/{id}/{file}`, `datafiles/{id}/{file}`, `datautilize/{id}/{file}`, `dataresearcher/{file}`)
4. หากยังไม่มีไฟล์จริงบนดิสก์ (กรณีทดสอบฐานข้อมูลก่อนนำไฟล์ก้อนใหญ่มาลง) จะส่งคืนพาธที่คาดหวัง เพื่อให้ปุ่มบน UI แสดงผลตามปกติ และส่งต่อไปยัง Controller

### ตัวอย่าง Model Helpers:
- **`RdbDip.php`**: `getDipFilePath(?string $field = 'dip_files')` รองรับทั้งไฟล์หลักและไฟล์แนบย่อย (`dip_data1_files`, `dip_data2_files_con`, `dip_data3_drawing_picture`, `dip_data_forms_request` ฯลฯ)
- **`RdbProject.php`**: `getReportFilePath()` และ `getAbstractFilePath()`
- **`RdbPublished.php`**: `getPublishedFilePath()`
- **`RdbProjectUtilize.php`**: `getUtilizeFilePath(?string $filename = null)`
- **`RdbProjectTypeSub.php`**: `getTypeSubFilePath()`
- **`RdbResearcher.php`**: `profile_image_url` (Accessor)
- **`ResearchConference.php` & `ResearchNews.php`**: `image_url` (Accessor)

---

## 3. Controller และ Route มาตรฐาน (Controller-Driven File Access)

ยกเลิกการฮาร์ดโค้ด URL ตรงใน Blade (`asset('storage/...')`) โดยเปลี่ยนมาวิ่งผ่าน Controller ทั้งหมดเพื่อตรวจสอบสิทธิ์ ความปลอดภัย และจัดการสตรีมไฟล์อย่างถูกต้อง:

### รายการ Route & Controller:
- **ทรัพย์สินทางปัญญา (DIP)**:
  - Frontend: `route('frontend.rdbdip.view_file', ['id' => $item->dip_id, 'field' => ...])` $\rightarrow$ `RdbdipController@viewFile`
  - Backend: `route('backend.rdb_dip.view_file', $item->dip_id)` $\rightarrow$ `RdbDipController@viewFile`
- **การนำไปใช้ประโยชน์ (Utilize)**:
  - Frontend: `route('frontend.rdbprojectutilize.view_file', $item->utz_id)` $\rightarrow$ `RdbprojectutilizeController@viewFile`
  - Backend: `route('backend.rdbprojectutilize.download', ...)` $\rightarrow$ `RdbProjectUtilizeController@downloadFile`
- **งานวิจัย (Research Project)**:
  - Frontend: `route('frontend.rdbproject.view_report', $id)` และ `view_abstract` $\rightarrow$ `Frontend\RdbprojectController`
  - Backend: `route('backend.rdb_project.view_report', $id)` และ `view_abstract` $\rightarrow$ `Backend\RdbProjectController`
- **ผลงานตีพิมพ์ (Published)**:
  - Frontend: `route('frontend.rdbpublished.view_file', $id)` $\rightarrow$ `Frontend\RdbpublishedController@viewFile`
  - Backend: `route('backend.rdb_published.view_file', $id)` $\rightarrow$ `Backend\RdbPublishedController@viewFile`
- **ประเภทย่อยโครงการ (Project Type Sub)**:
  - Backend: `route('backend.rdbprojecttypesub.view_file', $id)` $\rightarrow$ `Backend\RdbProjectTypeSubController@viewFile`

### ระบบแจ้งเตือนเมื่อไม่พบไฟล์ (Graceful 404):
หากไม่พบไฟล์จริงบนเซิร์ฟเวอร์ ระบบจะแสดงผลผ่าน Custom 404 Template ที่สุภาพ (`resources/views/errors/404.blade.php`) พร้อมปุ่มย้อนกลับ แทนที่จะปล่อยให้ Web Server ตอบกลับด้วยข้อความ Error 404/403 ดิบ

---

## 4. Buddhist Era (B.E.) vs Gregorian (C.E.) Dates
- Database tables historically store Buddhist Era years in `year_name` (e.g. `2568`).
- Gregorian calendar year is calculated by `year_name - 543` (e.g. `2568 - 543 = 2025`).
- When storing standard SQL dates (`DATE` / `DATETIME`), always store in Gregorian format (`YYYY-MM-DD`).
- Display dates to users in Thai Buddhist format via Carbon or Thai locale helpers:
  `{{ \Carbon\Carbon::parse($item->pro_date)->addYears(543)->format('d/m/Y') }}`
