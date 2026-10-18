---
name: thai-reporting-export
description: Standards for streaming large CSV and Excel exports in Thai language without memory exhaustion or encoding issues.
---

# Thai Reporting & Data Export Standards (laravel_rdb)

Use this skill whenever creating or modifying export endpoints for research projects, publications, researchers, or intellectual property.

## 1. UTF-8 BOM for Microsoft Excel Compatibility
Microsoft Excel on Windows requires a UTF-8 Byte Order Mark (BOM) at the very beginning of a CSV file, otherwise Thai characters will be garbled (MOJIBAKE).

Always write the UTF-8 BOM before any header rows:
```php
$handle = fopen('php://output', 'w');
// Write UTF-8 BOM
fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
```

## 2. Memory-Safe Chunking & Streaming
Never load the entire dataset into memory with `Model::all()` or `get()`. Always use `StreamedResponse` and `chunk(250)`:

```php
public function export(Request $request)
{
    $this->authorize('viewAny', RdbProject::class);
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="rdb_projects_' . date('Ymd_His') . '.csv"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0',
    ];

    $query = RdbProject::query()->with(['year', 'type', 'department']);
    // Apply filters...

    return response()->stream(function () use ($query) {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header Row
        fputcsv($handle, ['รหัส', 'ปีงบประมาณ', 'ชื่อโครงการวิจัย', 'งบประมาณ', 'หน่วยงาน']);

        // Data Chunking
        $query->orderBy('pro_id', 'desc')->chunk(250, function ($projects) use ($handle) {
            foreach ($projects as $item) {
                fputcsv($handle, $this->sanitizeCsvRow([
                    $item->pro_id,
                    $item->year->year_name ?? '-',
                    $item->pro_nameTH,
                    number_format($item->pro_budget ?? 0, 2),
                    $item->department->department_nameTH ?? '-',
                ]));
            }
        });

        fclose($handle);
    }, 200, $headers);
}
```

## 3. CSV Injection Protection
Every row passed to `fputcsv()` MUST be sanitized via `$this->sanitizeCsvRow(...)` to prefix any formula triggers (`=`, `+`, `-`, `@`) with a single quote (`'`).
