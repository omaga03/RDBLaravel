---
name: ui-standard-components
description: Design system guidelines, Blade components, buttons, status badges, file view links, and empty state standards for laravel_rdb.
---

# UI Standard Components & Design System (laravel_rdb)

Use this skill whenever designing or modifying Blade views across backend and frontend to ensure a uniform look and feel.

## 1. Card Component (`<x-card>`)
Always wrap content sections in standard cards with semantic color themes and Bootstrap Icons:
```blade
<x-card title="ข้อมูลโครงการวิจัย" icon="bi-info-circle" color="primary">
    <!-- Card Body Content -->
</x-card>
```
Supported colors: `primary`, `info`, `success`, `warning`, `danger`, `secondary`.

## 2. Action Buttons Standards
Maintain uniform button sizes and spacing across all views:
- **Index Tables**: Use `btn-sm` with icons:
  ```blade
  <a href="{{ route('backend.rdb_project.show', $item->id) }}" class="btn btn-sm btn-outline-info" title="ดูรายละเอียด">
      <i class="bi bi-eye"></i>
  </a>
  <a href="{{ route('backend.rdb_project.edit', $item->id) }}" class="btn btn-sm btn-outline-warning" title="แก้ไข">
      <i class="bi bi-pencil-square"></i>
  </a>
  ```
- **Form Action Buttons**: Keep Save and Delete buttons uniform with min-width and alignment:
  ```blade
  <button type="submit" class="btn btn-primary d-inline-flex justify-content-center align-items-center" style="min-width: 120px;">
      <i class="bi bi-save me-2"></i> บันทึกข้อมูล
  </button>
  <button type="button" class="btn btn-danger d-inline-flex justify-content-center align-items-center" style="min-width: 120px;" data-bs-toggle="modal" data-bs-target="#deleteModal">
      <i class="bi bi-trash me-2"></i> ลบข้อมูล
  </button>
  ```

## 3. Status Badges Standards
Always use Bootstrap 5.3 subtle badge classes with border for clean contrast in both light and dark themes:
- **เสร็จสิ้น / อนุมัติ / เผยแพร่**:
  `<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle"><i class="bi bi-check-circle me-1"></i> ดำเนินการเสร็จสิ้น</span>`
- **กำลังดำเนินการ / รอตรวจสอบ**:
  `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-hourglass-split me-1"></i> อยู่ระหว่างดำเนินการ</span>`
- **ยกเลิก / คืนเงิน / ไม่แสดงผล**:
  `<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle"><i class="bi bi-x-circle me-1"></i> ยกเลิกโครงการ</span>`
- **Public Visible Badge**:
  `<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle"><i class="bi bi-globe me-1"></i> เผยแพร่สาธารณะ (Public)</span>`

## 4. Current File Preview Standard
Whenever a form displays an existing uploaded file, use this standard pill preview with a `target="_blank"` inline view button:
```blade
@if($isEdit && $item->file_name)
    <div class="mb-2 p-2 bg-body-tertiary rounded border d-inline-flex align-items-center gap-2">
        <span class="badge bg-secondary-subtle text-secondary-emphasis border">
            <i class="bi bi-file-earmark-pdf text-danger me-1"></i> {{ $item->file_name }}
        </span>
        <a href="{{ route('backend.module.download', ['id' => $item->id, 'filename' => $item->file_name]) }}" 
           target="_blank" 
           class="btn btn-sm btn-outline-primary py-0 px-2 d-inline-flex align-items-center"
           title="เปิดดูไฟล์ในแท็บใหม่">
            <i class="bi bi-box-arrow-up-right me-1"></i> เปิดดูไฟล์
        </a>
    </div>
@endif
```

## 5. Standard Empty States
When a table or list has no records, display a helpful empty state with icon instead of an empty white space:
```blade
<tr>
    <td colspan="10" class="text-center py-5 text-body-secondary">
        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
        <span>ไม่พบข้อมูลในระบบ</span>
    </td>
</tr>
```
