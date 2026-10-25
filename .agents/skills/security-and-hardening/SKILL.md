---
name: security-and-hardening
description: Security guidelines, authorization checks, file upload hardening, CSV sanitization, IDOR prevention, and multi-phase adversarial security auditing (cloudflare/security-audit-skill) for the Research Database system.
---

# Security, Hardening & Audit Guidelines (laravel_rdb)

Use these security procedures whenever creating, updating, or auditing controllers, file uploads, data exports, raw queries, or permission management.

## 1. Controller Authorization & Role Scope Standards
Every backend action MUST enforce authorization through Laravel Policies:
```php
// In Controller Methods
$this->authorize('viewAny', RdbProject::class);   // For index()
$this->authorize('view', $item);                   // For show(), download()
$this->authorize('create', RdbProject::class);     // For create(), store()
$this->authorize('update', $item);                 // For edit(), update()
$this->authorize('delete', $item);                 // For destroy()
```
- Never rely solely on route middleware; always verify object-level permission in the controller.
- Check `$admin->hasPermission('Permission.Name')` via `App\Services\RbacService` when handling custom permissions.
- **Role-Based Data Scoping**: Ensure `Faculty Coordinator` only accesses records within their assigned `department_id` (`getActiveRoleScope()`), and `Researcher` only modifies records they authored/created or where `workflow_status` is unlocked.

## 2. File Upload Security Standards
- Always process uploaded files through `App\Services\FileUploadService`:
  ```php
  $fileInfo = $this->fileUploadService->storeFile($request->file('upload_input'), 'directory_name');
  ```
- **Renaming**: File names must ALWAYS be converted to UUID v4 (`Str::uuid() . '.' . $extension`). Never store user-supplied file names directly on disk.
- **MIME Type Whitelist**: Accept only safe research file types (`.pdf`, `.doc`, `.docx`, `.xls`, `.xlsx`, `.ppt`, `.pptx`, `.zip`, `.rar`, `.jpg`, `.png`). Executable files (`.php`, `.exe`, `.sh`) must be rejected.
- **Database Storage**: Store `$fileInfo['safe_name']` (UUID string) in the database filename column to prevent MySQL `varchar` overflow on long Thai filenames.

## 3. Safe File Viewing & IDOR Prevention
- Never expose direct storage paths in public URLs.
- Always route file views through authorized controller methods (e.g. `downloadFile`, `viewReport`, `viewImage`):
  ```php
  public function viewFile($id)
  {
      $item = RdbProject::findOrFail($id);
      $this->authorize('view', $item);
      
      $filePath = $item->getProjectFilePath();
      if ($filePath && Storage::disk('public')->exists($filePath)) {
          return Storage::disk('public')->response($filePath);
      }
      abort(404, 'ไม่พบไฟล์');
  }
  ```
- Deliver files with `inline` disposition so browser can render PDFs and images safely.

## 4. CSV Formula Injection Prevention (CWE-1236)
When exporting data to CSV/Excel, always sanitize every cell value using `sanitizeCsvRow()`:
```php
protected function sanitizeCsvRow(array $row): array
{
    return array_map(function ($value) {
        if (is_null($value)) return '';
        $str = (string)$value;
        if (strlen($str) > 0 && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $str;
        }
        return $str;
    }, $row);
}
```

## 5. Rate Limiting on Export Routes
All export endpoints must be throttled in `routes/web.php` to prevent Denial of Service (DoS):
```php
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/rdb_project/export', ...);
});
```

---

## 6. Structured Security Audit Protocol (Inspired by `cloudflare/security-audit-skill`)
เมื่อทำการตรวจสอบความปลอดภัยของระบบ (Security Audit) ห้ามรายงานช่องโหว่จากการเดาหรือดูเพียงบรรทัดเดียว ให้ปฏิบัติตามกระบวนการ 3 ขั้นตอนเพื่อขจัด False Positives:

1. **Reconnaissance (สำรวจขอบเขตและจุดรับข้อมูล)**:
   - ระบุจุดรับ Input ทั้งหมด: Query Parameters (`request('search')`, `request('sort')`), `FormRequest`, File Uploads, และ AJAX Search Endpoints
   - ระบุขอบเขตสิทธิ์ (Trust Boundaries): หน้าบ้านสาธารณะ (`data_show = 1`) vs หลังบ้านตามบทบาท (`The Creator`, `Admin Project`, `Faculty Coordinator`, `Researcher`)
2. **Coverage-Led Hunting (รายการตรวจสอบจุดเสี่ยงสำคัญของ Laravel)**:
   - **SQL Injection ใน Raw Queries**: ตรวจสอบ `whereRaw()`, `selectRaw()`, `orderByRaw()`, `DB::select()` ทุกจุด ต้องส่งตัวแปรผ่าน Parameter Binding (`?` หรือ Named Bindings) เท่านั้น ห้ามเชื่อม String ตรงๆ เด็ดขาด:
     ```php
     // ถูกต้อง:
     $q->whereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$term}%"]);
     ```
   - **Stored/Reflected XSS ใน Blade `{!! ... !!}`**: ทุกจุดที่ใช้ `{!! ... !!}` แสดงผล HTML จากฐานข้อมูล (เช่น `pro_abstract`, `pro_detail`, `news_detail`) ต้องผ่านการทำความสะอาดด้วย `App\Helpers\HtmlContentHelper::clean()` หรือตัวกรอง HTML ที่ปลอดภัยเสมอ
   - **Mass Assignment & Workflow State Tampering**: ตรวจสอบการใช้ `$request->validated()` หรือ `$request->only(...)` ป้องกันไม่ให้ผู้ใช้ทั่วไปส่งค่าแก้ไขฟิลด์ควบคุมระบบ เช่น `workflow_status`, `is_locked`, `user_created`, หรือข้าม `department_id` ของตนเอง
3. **Adversarial Candidate Validation (พิสูจน์ย้อนแย้งก่อนสรุปผล)**:
   - ทุกช่องโหว่ที่สงสัย (Candidate Finding) ต้องไล่ตรวจสอบตลอดเส้นทาง `Route Middleware` -> `FormRequest` -> `Controller` -> `Policy` -> `Model/Blade` ว่ามีกลไกป้องกันอยู่แล้วหรือไม่
   - รายงานเฉพาะช่องโหว่ที่ยืนยันแล้วว่าสามารถเข้าถึงและเกิดผลกระทบได้จริง (Confirmed Exploitable) พร้อมระบุไฟล์และเลขบรรทัดที่ชัดเจน
