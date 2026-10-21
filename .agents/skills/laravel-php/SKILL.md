---
name: laravel-php
description: Best practices, coding conventions, architectural rules, Eloquent optimization (N+1 prevention), Thin Controllers, and Senior Minimalist (YAGNI) mindset for Laravel 12 and PHP 8.
---

# Laravel PHP Development Skill

## 1. Guidelines & Architecture
- ใช้ PHP 8.x และ Laravel 12 Conventions เสมอ
- ยึดหลัก PSR-12 และ Clean Architecture
- **Thin Controllers**: Controller มีหน้าที่รับ Request, สั่งงาน Service/Model, และ Return View/Response เท่านั้น
- **FormRequest Validation**: แยกกฎ Validation ทั้งหมดไว้ใน Dedicated FormRequest (`Store...Request`, `Update...Request`) ห้ามเขียน `$request->validate([...])` ใน Controller
- ห้ามเขียน Complex Business Logic ไว้ใน Controller ให้ย้ายไปใส่ Service Class เสมอ
- เขียน PHPUnit Test (Feature/Unit Test) ควบคู่ทุกครั้งที่สร้างหรือแก้ไข Feature ใหม่
- ตรวจสอบความปลอดภัย: Gate/Policy Authorization, UUID Filename obfuscation, ป้องกัน SQL/CSV Injection

---

## 2. Senior Minimalist Mindset (YAGNI & Anti-Over-Engineering)
ยึดหลัก **"The best code is the code you never wrote"** เพื่อป้องกันโค้ดบวมและความซับซ้อนเกินจำเป็น:
1. **Reuse First**: ก่อนสร้าง UI, ฟังก์ชัน, หรือ Class ใหม่ ให้ตรวจสอบและนำของเดิมในระบบมาใช้ซ้ำก่อนเสมอ (เช่น `<x-card>`, `<x-search-bar>`, `<x-form-wrapper>`, Trait ที่มีอยู่)
2. **Native Framework Capabilities**: ใช้ความสามารถ Built-in ของ PHP 8, Laravel 12 Core, และ Bootstrap 5.3 ให้เต็มที่ก่อนคิดสร้าง Custom Solution ซับซ้อน
3. **No Unnecessary Dependencies**: **ห้ามสั่งติดตั้ง Composer package หรือ npm package ใหม่โดยไม่จำเป็น** หากสามารถแก้ปัญหาได้ด้วยเครื่องมือที่มีอยู่แล้ว
4. **Simplicity with Quality**: เขียนโค้ดให้กระชับ เข้าใจง่าย ไม่เพิ่ม Abstraction เกินจริง โดยยังคงยึดมั่นในความปลอดภัยและการทดสอบ Automated Tests 100%

---

## 3. Eloquent Optimization & N+1 Prevention
เพื่อป้องกันปัญหา Database Query บวม (N+1 Query Problem) ในระบบข้อมูลงานวิจัย:
- **Eager Loading เสมอ**: เมื่อจะนำ Relation ไปแสดงผลใน Blade (เช่น ลูปแสดงชื่อนักวิจัย, ประเภทโครงการ, สาขาวิชา) ต้องใช้ `with()` หรือ `$model->load()` ทุกครั้ง
  ```php
  // ตัวอย่างที่ถูกต้อง:
  $projects = RdbProject::with([
      'researcher:id,prefix,first_name,last_name,department_id',
      'department:id,dept_name',
      'projectType:id,type_name'
  ])->paginate(20);
  ```
- **ห้ามเรียก Relation ใน Loop**: ห้ามเข้าถึง Relation (เช่น `$item->researcher->name`) โดยไม่ได้ Eager Loading มาล่วงหน้า
- **Lazy Eager Loading ใน Show View**: ในหน้าแสดงรายละเอียด หากได้รับ Model ผ่าน Route Model Binding ให้เรียก `$item->load(['relations...'])` ก่อนส่งเข้า View
- **Column Pruning**: หากตารางมีฟิลด์เนื้อหาขนาดใหญ่ (เช่น `pro_detail`, `abstract_th`, `abstract_en`) ในหน้า Index ตารางสรุป ให้ใช้ `select()` เฉพาะคอลัมน์ที่จำเป็นเพื่อประหยัดหน่วยความจำ

---

## 4. Research Metadata & Relationships Standard
- **Many-to-Many Relationships**: ออกแบบและเรียกใช้งานความสัมพันธ์กลุ่มคน (เช่น งานวิจัย <-> ผู้ร่วมวิจัย) ผ่าน Pivot Table พร้อม Eager Loading ข้อมูลสัดส่วนการวิจัย
- **Academic Identifiers**: การจัดการข้อมูลเชิงวิชาการ (DOI, ISBN/ISSN, Citation, Keywords) ให้เก็บในรูปแบบ Clean String ที่ถูก Sanitized ป้องกัน XSS
- **Timestamp Handling**: โมเดลที่ตั้งค่า `$timestamps = true` ห้ามใส่ `created_at` และ `updated_at` ใน `$fillable` เด็ดขาด
