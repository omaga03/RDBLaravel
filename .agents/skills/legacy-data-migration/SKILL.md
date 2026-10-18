---
name: legacy-data-migration
description: Procedures and standards for handling legacy Yii2 database structures, dual-path storage resolution, and Buddhist/Gregorian year conversion.
---

# Legacy Data Migration & Compatibility (laravel_rdb)

Use this skill when dealing with tables, files, or dates migrated from the old Yii2 system.

## 1. Dual-Path Storage Architecture (`HasDualFilePath`)
All models with files that may originate from either the legacy Yii2 directory (`public/uploads/{module}/`) or the new UUID storage (`storage/app/public/{module}/`) MUST use `App\Models\Traits\HasDualFilePath`:

```php
use App\Models\Traits\HasDualFilePath;

class RdbProject extends Model
{
    use HasDualFilePath;

    public function getProjectFilePath(): ?string
    {
        return $this->resolveDualFilePath('pro_file_path', 'pro_file', 'uploads/projects');
    }
}
```

### Resolution Order:
1. Check new field (`$newField`, e.g. `pro_file_path`) in `Storage::disk('public')` and `public_path()`.
2. Fallback to legacy field (`$oldField`, e.g. `pro_file`) under `$oldDir` (e.g. `uploads/projects/filename.pdf`).
3. Return relative path if found on disk, or `null` if file does not exist.

## 2. Dual-Path Image Accessors
For images (such as conference covers, news photos, or researcher pictures), provide accessor attributes that return ready-to-use asset URLs:
```php
public function getImageUrlAttribute(): ?string
{
    if (!empty($this->news_path)) {
        return asset('storage/' . $this->news_path);
    }
    if (!empty($this->news_img)) {
        return asset('storage/uploads/news/' . $this->news_img);
    }
    return null;
}
```

## 3. Buddhist Era (B.E.) vs Gregorian (C.E.) Dates
- Database tables historically store Buddhist Era years in `year_name` (e.g. `2568`).
- Gregorian calendar year is calculated by `year_name - 543` (e.g. `2568 - 543 = 2025`).
- When storing standard SQL dates (`DATE` / `DATETIME`), always store in Gregorian format (`YYYY-MM-DD`).
- Display dates to users in Thai Buddhist format via Carbon or Thai locale helpers:
  `{{ \Carbon\Carbon::parse($item->pro_date)->addYears(543)->format('d/m/Y') }}`

## 4. File Integrity Command
Verify file existence across both storage systems using the Artisan verification command:
```bash
php artisan rdb:verify-files
```
Use this before performing batch migrations or archiving old uploaded folders.
