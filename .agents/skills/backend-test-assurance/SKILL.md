---
name: backend-test-assurance
description: Test-driven procedures, systematic root-cause debugging (obra/superpowers), verification-before-completion protocol, and automated regression testing using BackendAuditTest.
---

# Automated Test Assurance, Systematic Debugging & Verification (laravel_rdb)

Use this skill whenever debugging errors, modifying backend controllers, Blade views, routes, models, or verifying task completion.

## 1. Systematic Debugging Protocol (Inspired by `obra/superpowers`)
ห้ามเดาสุ่มแก้โค้ด (Trial-and-Error Patching) เมื่อเกิด Bug หรือ Error 500 ให้ปฏิบัติตาม 4 ขั้นตอนนี้อย่างเคร่งครัด:

1. **Root Cause Investigation (สืบค้นต้นตอ)**:
   - อ่าน Exception Message, Stack Trace, และบรรทัดที่เกิดปัญหาอย่างละเอียด
   - ไล่สายข้อมูลย้อนกลับ (Backward Data Flow Tracing): จาก `Blade View` -> `Controller` -> `FormRequest` -> `Model / Relation / DB Column`
   - ตรวจสอบว่าเป็นปัญหาจาก Missing Import (เช่น `use Illuminate\Support\Facades\Storage`), ชื่อคอลัมน์ใน DB ไม่ตรง, หรือ N+1 / Null Relation
2. **Pattern Analysis (เปรียบเทียบกับโมดูลที่ทำงานได้ปกติ)**:
   - ค้นหาโมดูลต้นแบบที่ทำงานสำเร็จในระบบ (เช่น เทียบกับ `RdbProjectController` หรือ `RdbPublishedController`) เพื่อดูรูปแบบมาตรฐานที่ใช้ในโปรเจกต์
3. **Hypothesis & Minimal Fix (ตั้งสมมติฐานและแก้ที่ต้นเหตุ)**:
   - แก้ไขที่ต้นเหตุจริง (Root Cause) เท่านั้น ห้ามแก้ปลายเหตุด้วยการใส่ `@` ปิด error หรือซ่อนข้อมูลโดยไม่ตรวจสอบ
   - หากลองแก้เกิน 2 ครั้งแล้วยังไม่ผ่าน ให้หยุดและทบทวนโครงสร้างสถาปัตยกรรมใหม่ทันที
4. **Verification Before Completion (หลักฐานยืนยันก่อนจบงาน)**:
   - **ห้ามรายงานว่า "แก้ไขเสร็จแล้ว" โดยไม่มีหลักฐานการรันจริง**
   - ต้องรันคำสั่งตรวจสอบไวยากรณ์ (`php -l`), ทดสอบเรียก Route/CLI, หรือรัน PHPUnit Test และอ่านผลลัพธ์จริงทุกครั้ง

---

## 2. Running the Automated Test Suite
Always execute the comprehensive audit test suite before committing code:
```bash
php artisan test --filter=BackendAuditTest
```

## 3. Test Coverage Checklist
Whenever a new feature or fix is introduced, verify and write tests for:
1. **Authorization Gate**: Guest or unauthorized role receives HTTP 403 or redirect.
2. **Success Case**: Admin receives HTTP 200 with expected UI elements and data.
3. **Validation Failure**: Submitting invalid data redirects with errors in session.
4. **Multi-Select Input (`old()`)**: Ensure arrays flashed in `old(...)` do not cause `TypeError htmlspecialchars`.
5. **Download / Inline Streaming**: Assert Content-Disposition header contains `inline` and correct filename.

## 4. Template for Testing Authenticated Routes
```php
public function test_example_feature()
{
    $admin = \App\Models\User::find(129); // Admin user
    $this->assertNotNull($admin);

    $response = $this->actingAs($admin)->get(route('backend.rdb_project.index'));
    $response->assertStatus(200);
    $response->assertSee('โครงการวิจัย');
}
```

## 5. Cache Clearing Protocol
After tests pass, clear compiled views and route caches:
```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
```
