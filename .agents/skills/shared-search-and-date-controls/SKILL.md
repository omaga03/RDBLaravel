---
name: shared-search-and-date-controls
description: มาตรฐานระบบการค้นหาชื่อโครงการวิจัย, ชื่อนักวิจัย, ปฏิทิน พ.ศ., การตรวจสอบช่วงวันที่ (Date Range Validation) และตัวกรองค้นหาที่ใช้ร่วมกันทั้งหน้าบ้านและหลังบ้าน
---

# มาตรฐานระบบค้นหาและปฏิทิน (Shared Search & Date Controls)

ใช้ Skill นี้เป็นแนวทางปฏิบัติมาตรฐานทุกครั้งที่มีการสร้าง, ปรับปรุง หรือตรวจสอบ:
1. การค้นหาและ Autocomplete **ชื่อโครงการวิจัย**
2. การค้นหาและ Autocomplete **ชื่อนักวิจัย**
3. ปฏิทินไทยแบบ พ.ศ. และการแปลงวันที่ (**Flatpickr & Thai Buddhist Era**)
4. การตรวจสอบความสัมพันธ์ช่วงวันที่ (**Date Range Validation**) ทั้งฝั่ง Client และ Server
5. ส่วนประกอบกล่องค้นหาและตัวกรองที่ใช้ร่วมกัน (**Shared Filter Bar & Controls**)

---

## 1. มาตรฐานการค้นหาชื่อโครงการวิจัย (Research Project Search)

### 1.1 โครงสร้าง JSON Response มาตรฐาน (API Endpoint)
ทุก Controller ที่มี Endpoint สำหรับค้นหาโครงการวิจัย (เช่น TomSelect AJAX) ต้องส่งผลลัพธ์ที่มีทั้ง `id` และ `value` พร้อมระบุ **รายชื่อนักวิจัยทั้งหมดที่เกี่ยวข้องในโครงการ (ทั้งหัวหน้าโครงการและผู้ร่วมวิจัย)**:

```json
{
  "results": [
    {
      "id": 1052,
      "value": 1052,
      "text": "[2568] โครงการพัฒนานวัตกรรมชุมชน... โดย: ดร.สมชาย ใจดี (หัวหน้า) และคณะ 3 คน [คณะวิทยาการจัดการ]",
      "pro_nameTH": "โครงการพัฒนานวัตกรรมชุมชน...",
      "pro_nameEN": "Community Innovation Development Project...",
      "pro_code": "PRO-68-001",
      "year_name": "2568",
      "department_name": "คณะวิทยาการจัดการ",
      "head_researcher": "ดร.สมชาย ใจดี",
      "all_researchers_text": "ดร.สมชาย ใจดี (หัวหน้า), ผศ.วิภา รักวิชา (ผู้ร่วม), อ.เกียรติศักดิ์ มีสุข (ผู้ร่วม)",
      "researchers": [
        {
          "id": 88,
          "name": "ดร.สมชาย ใจดี",
          "position": "หัวหน้าโครงการ",
          "ratio": 60
        },
        {
          "id": 92,
          "name": "ผศ.วิภา รักวิชา",
          "position": "ผู้ร่วมวิจัย",
          "ratio": 25
        },
        {
          "id": 105,
          "name": "อ.เกียรติศักดิ์ มีสุข",
          "position": "ผู้ร่วมวิจัย",
          "ratio": 15
        }
      ]
    }
  ]
}
```

### 1.2 Query Logic ฝั่ง Server (ค้นหาครอบคลุมนักวิจัยทุกคน)
เมื่อมีการค้นหาด้วยข้อความในช่องค้นหาชื่อโครงการ:
1. ค้นหาในตารางโครงการ: `pro_nameTH`, `pro_nameEN`, `pro_code`, `pro_keyword`, `pro_abstract`
2. **ค้นหาในนักวิจัยทุกคนที่เกี่ยวข้อง (ทั้งหัวหน้าและผู้ร่วมโครงการ)** ผ่าน Relation `rdbProjectWorks.researcher`:
```php
$query->orWhereHas('rdbProjectWorks.researcher', function($rq) use ($searchTerm, $searchWords) {
    $rq->where(function($wq) use ($searchTerm, $searchWords) {
        $wq->where('researcher_fname', 'like', "%{$searchTerm}%")
           ->orWhere('researcher_lname', 'like', "%{$searchTerm}%")
           ->orWhereRaw("CONCAT(researcher_fname, ' ', researcher_lname) LIKE ?", ["%{$searchTerm}%"]);

        foreach ($searchWords as $word) {
            if (empty($word)) continue;
            $wq->orWhere('researcher_fname', 'like', "%{$word}%")
               ->orWhere('researcher_lname', 'like', "%{$word}%");
        }
    });
});
```

### 1.3 การค้นหาแบบกลุ่มคำ (Multi-Keyword Search)
ช่องค้นหาชื่อเรื่องแบบกลุ่มคำ `title_keywords` จะต้องรองรับตัวเลือกโหมด `title_keywords_mode`:
* `all` (AND - ต้องตรงทุกคำ เช่น "ข้าว หอมมะลิ เกษตร")
* `any` (OR - ตรงคำใดคำหนึ่ง)

### 1.4 การระบุแหล่งที่พบในหน้ารายการหลังบ้าน (Match Sources & Badges)
คำนวณและแสดง Badge ไฮไลต์ให้ผู้ใช้ทราบว่าคำค้นหาตรงกับฟิลด์ใด รวมถึง**ระบุชื่อและตำแหน่งของนักวิจัยที่ตรงกับคำค้นหา**:
* `[ชื่อโครงการ (ไทย)]` / `[ชื่อโครงการ (อังกฤษ)]`
* `[รหัสโครงการ]`
* `[คำสำคัญ]` / `[บทคัดย่อ]`
* `[ประเภททุน]` / `[ประเภทย่อย]`
* **`[นักวิจัย (หัวหน้าโครงการ): ดร.สมชาย ใจดี]`**
* **`[นักวิจัย (ผู้ร่วมวิจัย): ผศ.วิภา รักวิชา]`**

```php
if ($project->rdbProjectWorks && $project->rdbProjectWorks->isNotEmpty()) {
    foreach ($project->rdbProjectWorks as $work) {
        if ($work->researcher) {
            $fullName = trim($work->researcher->researcher_fname . ' ' . $work->researcher->researcher_lname);
            if (mb_stripos($fullName, $searchTerm) !== false) {
                $pos = $work->position ? $work->position->position_name : 'ผู้วิจัย';
                $matchSources[] = "นักวิจัย ({$pos}): {$fullName}";
            }
        }
    }
}
```

---

## 2. มาตรฐานการค้นหาชื่อนักวิจัย (Researcher Search)

### 2.1 โครงสร้าง JSON Response มาตรฐาน
```json
{
  "results": [
    {
      "id": 88,
      "value": 88,
      "text": "ดร.สมชาย ใจดี [คณะวิทยาการจัดการ]",
      "prefix": "ดร.",
      "fname": "สมชาย",
      "lname": "ใจดี",
      "department": "คณะวิทยาการจัดการ"
    }
  ]
}
```

### 2.2 Server-side Search & Clean Logic
* **ตัดคำนำหน้าชื่อภาษาไทยเสมอ**:
  ```php
  $cleanName = preg_replace('/^(นาย|นาง|นางสาว|ดร\.|ผศ\.|รศ\.|ศ\.)\s*/u', '', trim($request->researcher_name));
  ```
* **ค้นหาแบบแยกคำ**: ตัดคำด้วยช่องว่างเพื่อค้นหาทั้ง `researcher_fname`, `researcher_lname`, และ `CONCAT(researcher_fname, ' ', researcher_lname)` รวมถึงชื่อภาษาอังกฤษ (`researcher_fnameEN`, `researcher_lnameEN`)

### 2.3 UI & Pre-population Pattern
* ในแบบฟอร์มค้นหา ให้มีคู่ฟิลด์เสมอ:
  1. `<select id="search_researcher_id" name="researcher_id">`: จัดการผ่าน TomSelect
  2. `<input type="hidden" id="search_researcher_name" name="researcher_name" value="{{ request('researcher_name') }}">`: เก็บชื่อข้อความ เพื่อใช้แสดงผลค่าเดิมเมื่อ Reload หรือ Bookmark หน้า

---

## 3. มาตรฐานปฏิทินไทยและรูปแบบวันที่ (Thai Buddhist Datepicker & Format)

### 3.1 การตั้งชื่อ Parameter ค้นหา
* วันที่เริ่มต้น: ใช้ `date_start` เท่านั้น (ห้ามใช้ `s_date_start`, `search_date_start`, `start_date`, `pub_date_start`)
* วันที่สิ้นสุด: ใช้ `date_end` เท่านั้น (ห้ามใช้ `s_date_end`, `search_date_end`, `end_date`, `pub_date_end`)

### 3.2 รูปแบบการแสดงผลและจัดเก็บ
* **แสดงผลบนหน้าจอ (Display)**: เป็นปี พ.ศ. เสมอ (ค.ศ. + 543)
  * **แบบเต็ม**: `24 ตุลาคม 2569` (สำหรับหน้ารายละเอียด Show และแบบฟอร์มทางการ)
  * **แบบย่อ**: `24 ต.ค. 2569` (สำหรับตาราง Index Table และป้าย Badge)
* **จัดเก็บในฐานข้อมูล (Database)**: รูปแบบ ISO `YYYY-MM-DD` ค.ศ. เสมอ (`2026-10-24`) โดย Flatpickr จะใช้ `altInput: true` สลับแสดงผลอัตโนมัติ

### 3.3 Helper กลางสำหรับการจัดการวันที่ (`App\Helpers\ThaiDateHelper`)
* `ThaiDateHelper::format($date, $time = false, $short = false)`: แปลง ค.ศ. ใน DB -> ข้อความ พ.ศ. (`24 ตุลาคม 2569` หรือ `24 ต.ค. 2569`)
* `ThaiDateHelper::toIsoDate($thaiDateString)`: แปลงข้อความ พ.ศ. หรือ `d/m/Y` -> ISO `YYYY-MM-DD` (ค.ศ.)

---

## 4. มาตรฐานการตรวจสอบช่วงวันที่ (Date Range Validation)

### 4.1 Server-side Validation: หน้าตัวกรองค้นหา (Index Controller - GET Request)
ใน Controller ของหน้ารายการที่มีตัวกรองวันที่ ต้องใช้ตรรกะตรวจสอบดังนี้:
1. **หากระบุวันใดวันหนึ่ง ต้องระบุให้ครบทั้งสองวัน**
2. **วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น (`date_end >= date_start`)**
3. หากไม่ถูกต้อง ให้ `redirect()->back()->withInput()->with('error', 'ข้อความแจ้งเตือนภาษาไทย')`

```php
$hasDateStart = $request->filled('date_start');
$hasDateEnd   = $request->filled('date_end');

if ($hasDateStart || $hasDateEnd) {
    if (!$hasDateStart || !$hasDateEnd) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'กรุณาระบุทั้งวันที่เริ่มต้นและวันที่สิ้นสุดให้ครบถ้วน');
    }

    if ($request->date_end < $request->date_start) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
    }

    $query->whereDate('pro_date_start', '>=', $request->date_start)
          ->whereDate('pro_date_end', '<=', $request->date_end);
}
```

### 4.2 Server-side Validation: หน้าฟอร์มบันทึก/แก้ไข (FormRequest - POST/PUT Request)
ใน `FormRequest` ต้องแปลงค่าผ่าน `prepareForValidation()` และตรวจสอบกฎ `after_or_equal`:

```php
protected function prepareForValidation()
{
    if ($this->has('pro_date_start')) {
        $this->merge(['pro_date_start' => \App\Helpers\ThaiDateHelper::toIsoDate($this->pro_date_start)]);
    }
    if ($this->has('pro_date_end')) {
        $this->merge(['pro_date_end' => \App\Helpers\ThaiDateHelper::toIsoDate($this->pro_date_end)]);
    }
}

public function rules(): array
{
    return [
        'pro_date_start' => 'required|date',
        'pro_date_end'   => 'required|date|after_or_equal:pro_date_start',
    ];
}

public function messages(): array
{
    return [
        'pro_date_start.required'     => 'กรุณาระบุวันที่เริ่มต้น',
        'pro_date_end.required'       => 'กรุณาระบุวันที่สิ้นสุด',
        'pro_date_end.after_or_equal' => 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น',
    ];
}
```

### 4.3 Client-side Validation (Flatpickr Linkage)
สคริปต์หน้าเว็บต้องผูกความสัมพันธ์อัตโนมัติ:
* เมื่อเลือก `date_start` -> อัปเดต `minDate` ของ `date_end` ทันที
* หาก `date_end` ที่เลือกไว้เดิมมีค่าน้อยกว่า `date_start` ใหม่ -> สั่ง `clear()` วันที่สิ้นสุดออกทันที

---

## 5. มาตรฐานกล่องค้นหาและตัวกรองร่วม (Shared Filter Bar)

### 5.1 โครงสร้าง `<x-search-bar>`
* ใช้ `<x-search-bar :searchRoute="..." :exportRoute="...">` เป็นโครงหลักของทุกหน้า
* ประกอบด้วยแท็บ **ค้นหาทั่วไป** และแท็บ **ค้นหาละเอียด**
* มีปุ่ม "ล้างค่าตัวกรอง" และ "ส่งออก Excel" ที่คงค่า Query Parameters ปัจจุบันอัตโนมัติ

### 5.2 ตัวกรองร่วมอื่น ๆ
* **ปีงบประมาณ (`year_id`)**: เชื่อมโยงแบบ Cascading ไปยังประเภททุน (`pt_id`) และประเภทย่อย (`pts_id`)
* **หน่วยงาน/คณะ (`department_id`)**: ล็อกสิทธิ์อัตโนมัติสำหรับบทบาทผู้ประสานงานคณะ (Faculty Coordinator)
* **ช่วงงบประมาณ (`budget_min` / `budget_max`)**: ตรวจสอบ `budget_max >= budget_min` เสมอ
