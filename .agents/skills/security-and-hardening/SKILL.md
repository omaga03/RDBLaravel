---
name: security-and-hardening
description: Security guidelines, authorization checks, file upload hardening, CSV sanitization, and IDOR prevention for the Research Database system.
---

# Security & Hardening Guidelines (laravel_rdb)

Use these security procedures whenever creating or updating controllers, file uploads, data exports, or permission management.

## 1. Controller Authorization Standards
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
