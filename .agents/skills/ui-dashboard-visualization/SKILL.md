---
name: ui-dashboard-visualization
description: Design patterns for dashboard stat cards, KPI strips with honest deltas, Ranked Lists with 3px share bars, Chart.js multi-type visualizations, dynamic fiscal year filtering, and 3-width visual QA for laravel_rdb.
---

# Dashboard & Data Visualization Standards (laravel_rdb)

Use this skill whenever designing, building, redesigning, or critiquing dashboards (`/` Frontend Home, `/backend` Admin Dashboard, `/backend/dashboard/portfolio`, `/backend/dashboard/department`, `/backend/dashboard/journal`, or any analytics/statistics screen).

---

## 1. Core Philosophy: First Glance, Quiet, Wordless & Honest
*Inspired by 127 shipped dashboards (Stephen Few, Edward Tufte, Cleveland & McGill) and adapted for PCRU RDB's Scholarly Editorial System:*

1. **First Glance (เข้าใจใน 3 วินาทีแรก)**:
   - หนึ่งหน้าจอต้องตอบคำถามหลักได้ทันทีโดยไม่ต้องเลื่อนเมาส์ (เช่น *"ปีงบประมาณนี้มีผลงานวิจัยและงบประมาณเท่าไร เติบโตจากปีก่อนหรือไม่ และมีงานใดรอตรวจอนุมัติ?"*)
   - ลำดับสายตา (Visual Hierarchy): `Header & Fiscal Year Filter` → `KPI Cards / Strip` → `Main Trend Chart` → `Ranked Breakdowns (คณะ/แหล่งทุน/วารสาร)` → `Actionable Queues`
2. **Quiet & Colour Follows the Entity (สงบ สุขุม ใช้สีตามความหมายของข้อมูล)**:
   - **การ์ดและกรอบต้องมีสีพื้นจาง ๆ (Subtle Tinted Surface)**: หากใช้สีขาวล้วนหรือดำเรียบเสมอกันทุกการ์ด การ์ดจะดูกลืนกันไปหมดและขาดมิติ (Visual Separation) ให้ผสมผสาน **เส้นขอบบน 3px Hairline** ควบคู่กับ **สีพื้นหลังจาง ๆ 4% ใน Light Mode (`rgba(..., 0.04)`) หรือ 8% ใน Dark Mode (`rgba(..., 0.08)`)** ตามเฉดสีประจำโมดูล:
     - **โครงการวิจัย (Projects)**: Royal Blue `#2563EB` → `background-color: rgba(37, 99, 235, 0.04);`
     - **งบประมาณ (Budget)**: Academic Emerald `#059669` → `background-color: rgba(5, 150, 105, 0.04);`
     - **ผลงานตีพิมพ์ (Publications)**: Scholarly Teal `#0D9488` → `background-color: rgba(13, 148, 136, 0.04);`
     - **นักวิจัยและกำลังคน (Researchers)**: Deep Indigo `#4F46E5` → `background-color: rgba(79, 70, 229, 0.04);`
     - **ทรัพย์สินทางปัญญา (IP / DIP)**: Citation Amber `#D97706` → `background-color: rgba(217, 119, 6, 0.04);`
     - **การใช้ประโยชน์ (Utilization)**: Crimson Coral `#DC2626` → `background-color: rgba(220, 38, 38, 0.04);`
     - **ข่าวสารและการประชุม (News & Conf)**: Slate Grey `#64748B` → `background-color: rgba(100, 116, 139, 0.04);`
3. **Wordless (กระชับ ไม่เขียนประโยคเยิ่นเย้อใต้ตัวเลข)**:
   - บนการ์ดแสดงเฉพาะ **ชื่อตัวชี้วัด**, **ตัวเลขหลัก (`tabular-nums`)**, **หน่วยนับ**, และ **ค่าเปรียบเทียบสั้นๆ (1–2 คำ)** เช่น `▲ +14.2% จากปี 2568`
   - คำอธิบายยาวๆ หรือสูตรคำนวณให้ใส่ไว้ใน `title` / Bootstrap Tooltip เท่านั้น
4. **Honest Numbers & Full YoY Delta Coverage (ครอบคลุมเปรียบเทียบทุกมิติวิชาการ)**:
   - ระบบต้องคำนวณและแสดงค่าเปรียบเทียบกับปีก่อนหน้า (YoY Delta) ให้ครอบคลุมทุกมิติหลัก: **โครงการวิจัย**, **งบประมาณ**, **ผลงานตีพิมพ์**, **นักวิจัย**, **ทรัพย์สินทางปัญญา**, และ **การใช้ประโยชน์**
   - ตัวเลขเดียวกันต้องตรงกันทุกจุดบนหน้าจอ (KPI Card = กราฟ = ตารางรวม)
   - **กฎฐานข้อมูลขนาดเล็ก (Small Base Rule)**: หากค่าปีก่อนหน้ามีน้อยกว่า 5 รายการ (`< 5`) ห้ามแสดงเปอร์เซ็นต์สีแดง/เขียวที่ดูหวือหวาเกินจริง (เช่น `1 → 0` ห้ามแสดง `▼ -100%` สีแดง หรือ `1 → 3` ห้ามแสดง `+200%`) ให้แสดงเป็นป้ายสีเทาสุขุม เช่น `ปีก่อนหน้า 1 โครงการ` (`was 1`)
   - ช่องที่ไม่มีข้อมูลให้แสดง `—` (Em-dash) สีเทาจาง ห้ามแสดง `$0.00`, `NaN`, หรือ `0.0%` ปลอม
   - ปีงบประมาณปัจจุบันที่ยังไม่สิ้นสุดปี ให้แสดงเส้นกราฟช่วงท้ายแบบเส้นประ (`borderDash: [5, 5]`) หรือกำกับชัดเจนว่าอยู่ระหว่างปีงบประมาณ

---

## 2. Stat Cards & KPI Cards Pattern

### 2.1 High-Impact Hero Stat Cards (พร้อม Watermark Icon)
สำหรับหน้าแรกหรือการ์ดสรุปภาพรวมหลัก 4 มิติ:

```blade
<div class="card stats-card h-100 text-white shadow-sm border-0 rounded-4" 
     style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
    <div class="card-body position-relative p-4 overflow-hidden">
        <i class="bi bi-file-earmark-text-fill stats-icon position-absolute end-0 bottom-0 opacity-25" 
           style="font-size: 5rem; transform: rotate(-15deg); margin-right: 10px; margin-bottom: -10px;"></i>
        <div class="position-relative" style="z-index: 1;">
            <h6 class="text-white-50 text-uppercase fw-bold mb-2">
                <i class="bi bi-calendar3 me-1.5"></i>ปีงบประมาณ {{ $yearName }}
            </h6>
            <h5 class="fw-bold mb-1">จำนวนโครงการวิจัย</h5>
            <div class="display-5 fw-bold" style="font-variant-numeric: tabular-nums;">
                {{ number_format($sumProject) }}
            </div>
            <small class="text-white-50 mt-1 d-inline-block">โครงการ</small>
        </div>
    </div>
</div>
```

### 2.2 Analytical KPI Card with Honest YoY Delta & Subtle Tint (สำหรับหน้า Intelligence Dashboard)
ใช้เส้นขอบบน `3px` และสีพื้นหลังจาง ๆ `rgba(..., 0.04)` พร้อมป้ายเปรียบเทียบปีงบประมาณก่อนหน้า (Delta Badge) ครบทุกมิติ:

```blade
<div class="card shadow-sm border h-100 position-relative overflow-hidden hover-card" 
     style="border-top: 3px solid #2563EB !important; background-color: rgba(37, 99, 235, 0.04); border-radius: var(--rdb-radius-md) !important;">
    <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative" style="z-index: 1;">
        <div>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-folder2-open me-1"></i> โครงการวิจัย
                </span>
                <div class="p-1.5 rounded-circle bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-folder-check fs-6"></i>
                </div>
            </div>
            <h3 class="fw-bold mb-0 text-primary" style="font-size: 1.45rem; font-variant-numeric: tabular-nums;">
                {{ number_format($currentCount) }}
            </h3>
            <small class="text-body-secondary d-block mt-1">โครงการทั้งหมด</small>
        </div>

        @if(!empty($prevYearName))
            @if($prevCount >= 5 && $deltaPercent !== null)
                <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                    <span class="text-body-secondary" style="font-size: 0.72rem;">เทียบปี {{ $prevYearName }}</span>
                    <span class="badge {{ $diff >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-variant-numeric: tabular-nums; font-size: 0.7rem;">
                        <i class="bi {{ $diff >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                        {{ $diff >= 0 ? '+' : '' }}{{ $deltaPercent }}%
                    </span>
                </div>
            @else
                <div class="mt-2 pt-2 border-top border-subtle d-flex align-items-center justify-content-between small">
                    <span class="text-body-secondary" style="font-size: 0.72rem;">ปีก่อน ({{ $prevYearName }})</span>
                    <span class="text-body-secondary fw-semibold" style="font-variant-numeric: tabular-nums; font-size: 0.72rem;">{{ number_format($prevCount) }} โครงการ</span>
                </div>
            @endif
        @endif
    </div>
    <i class="bi bi-folder position-absolute end-0 bottom-0 text-primary" style="font-size: 3.5rem; opacity: 0.07; transform: translate(6px, 6px); pointer-events: none;"></i>
</div>
```

---

## 3. Ranked Breakdown Lists with Slim 3px Share Bars
เมื่อต้องการแสดงการจัดอันดับคณะ/หน่วยงาน (`rdb_department`) หรือประเภททุน (`rdb_project_type`) **ห้ามใช้ Pie/Donut Chart ที่มีเกิน 4 ส่วน** และ **ห้ามใส่แถบสีทึบเต็มพื้นหลังแถว** ให้ใช้รายการจัดอันดับพร้อม **แถบสัดส่วนบาง `3px` ด้านล่างแถว**:

```blade
<div class="list-group list-group-flush">
    @foreach($departments as $dept)
        @php
            $pct = $maxCount > 0 ? round(($dept->projects_count / $maxCount) * 100, 1) : 0;
        @endphp
        <div class="list-group-item bg-transparent px-3 py-2.5 border-bottom">
            <div class="d-flex align-items-center justify-content-between mb-1.5 gap-2">
                <span class="text-body fw-medium text-truncate" title="{{ $dept->department_nameTH }}">
                    {{ $dept->department_nameTH }}
                </span>
                <div class="d-flex align-items-center gap-2 flex-shrink-0" style="font-variant-numeric: tabular-nums;">
                    <span class="fw-bold text-body">{{ $dept->projects_count > 0 ? number_format($dept->projects_count) : '—' }}</span>
                    <small class="text-body-secondary" style="min-width: 3.2rem; text-align: right;">
                        {{ $totalCount > 0 ? number_format(($dept->projects_count / $totalCount) * 100, 1) . '%' : '—' }}
                    </small>
                </div>
            </div>
            <div class="w-100 rounded-pill bg-body-tertiary overflow-hidden" style="height: 3px;">
                <div class="h-100 rounded-pill bg-primary" style="width: {{ $pct }}%;"></div>
            </div>
        </div>
    @endforeach
</div>
```

---

## 4. Dynamic Fiscal Year Filter
ทุกหน้า Dashboard ต้องมีตัวกรองปีงบประมาณ พ.ศ. ที่ส่งค่าแบบ GET และกรองข้อมูลทั้งการ์ด ตาราง และกราฟให้ตรงกันทั้งหมด:

```blade
<form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2" id="dashboardYearForm">
    <label for="year_id" class="me-1.5 fw-bold text-nowrap">
        <i class="bi bi-calendar3 me-1.5 text-primary"></i>ปีงบประมาณ:
    </label>
    <select name="year_id" id="year_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">-- ทุกปีงบประมาณ --</option>
        @foreach($years as $y)
            <option value="{{ $y->year_id }}" {{ request('year_id') == $y->year_id ? 'selected' : '' }}>
                {{ $y->year_name }}
            </option>
        @endforeach
    </select>
</form>
```

---

## 5. Chart.js Standards (Clean Axes, Round Ticks & Dual-Mode Support)

1. **แกน Y (Y-Axis)**:
   - เริ่มต้นที่ `0` เสมอ (`beginAtZero: true`)
   - ใช้จำนวนขีดสเกลเพียง `4–5` ขีด (`ticks: { maxTicksLimit: 5, precision: 0 }`) และจัดรูปแบบตัวเลขย่อเมื่อหลักล้านขึ้นไป (เช่น `1.5M`, `500k`) ส่วนใน Tooltip แสดงตัวเลขเต็มพร้อมทศนิยม `toLocaleString('th-TH')`
2. **การแยกประเภทกราฟ**:
   - **ข้อมูลตามเวลา (Time Series / ปีงบประมาณ)**: ใช้กราฟแท่งแนวตั้ง (Vertical Bar) หรือกราฟเส้น (Line)
   - **ข้อมูลตามหมวดหมู่/หน่วยงาน (Categories / คณะ)**: ใช้กราฟแท่งแนวนอน (`indexAxis: 'y'`) หรือตารางจัดอันดับพร้อม Share Bar `3px` เพื่อให้ชื่อคณะภาษาไทยอ่านง่ายโดยไม่ต้องเอียงคอ
3. **ตัวอย่าง Dual Bar & Line Chart (จำนวนโครงการคู่กับงบประมาณ)**:

```javascript
const ctx = document.getElementById('projectTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: chartYears,
        datasets: [
            {
                type: 'bar',
                label: 'จำนวนโครงการ (โครงการ)',
                data: projectCounts,
                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderRadius: 4,
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'งบประมาณ (บาท)',
                data: budgets,
                borderColor: 'rgba(217, 119, 6, 1)',
                backgroundColor: 'rgba(217, 119, 6, 0.15)',
                borderWidth: 2.5,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: false,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                title: { display: true, text: 'จำนวนโครงการ' },
                ticks: { maxTicksLimit: 5, precision: 0 }
            },
            y1: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                title: { display: true, text: 'งบประมาณ (บาท)' },
                grid: { drawOnChartArea: false },
                ticks: {
                    maxTicksLimit: 5,
                    callback: (value) => value >= 1000000
                        ? (value / 1000000).toLocaleString('th-TH', { maximumFractionDigits: 1 }) + 'M'
                        : value.toLocaleString('th-TH')
                }
            }
        }
    }
});
```

---

## 6. Dashboard Smell Table (ข้อห้ามและสิ่งที่ต้องใช้แทน)

| สิ่งที่ห้ามทำ (UI Smell) | สิ่งที่ต้องทำแทน (Standard Solution) |
|---|---|
| พื้นหลังสีทึบซ้อนหลังแถวรายการทุกแถว | ใช้พื้นหลังโปร่งใส คั่นด้วยเส้นบาง และใช้แถบสัดส่วน `3px` ด้านล่างแถว |
| แสดง `▼ -100%` สีแดงเมื่อค่าลดจาก `1 → 0` | แสดงป้ายสีเทา `ปีก่อนหน้า 1` (เมื่อฐาน `< 5`) |
| ใช้กราฟวงกลม (Pie/Donut) กับข้อมูลเกิน 4 หมวดหมู่ | ใช้ตารางจัดอันดับพร้อม Share Bar `3px` หรือกราฟแท่งแนวนอน |
| การ์ดกว้างแต่มีตัวเลขเล็กๆ ตัวเดียวอยู่กลางพื้นที่ว่าง | ยุบรวมเข้าแถว KPI Cards (4 คอลัมน์ต่อแถวบนจอใหญ่) |
| ตัวเลขงบประมาณหรือจำนวนโครงการไม่ตรงแนวกันในตาราง | บังคับชิดขวา (`text-end`) และใส่ `font-variant-numeric: tabular-nums` เสมอ |
| ประโยคบรรยายยาวๆ ใต้ตัวเลขใน KPI Card | ย้ายคำอธิบายไปไว้ใน Tooltip (`data-bs-toggle="tooltip"`) |
| สีเรืองแสง/ม่วงชมพูแบบเว็บ AI สำเร็จรูป | ใช้โทนสีวิชาการ Deep Navy (`#1E3A5F`), Royal Blue (`#2563EB`), Emerald (`#059669`), Amber (`#D97706`) |

---

## 7. Verification & 3-Width QA Checklist
ก่อนส่งมอบงานหน้า Dashboard ทุกครั้ง ต้องตรวจสอบ:
1. **Honesty Check**: ยอดรวมบน KPI Card ตรงกับผลรวมในตารางและกราฟ 100% และไม่มีคำว่า `NaN`, `null`, หรือ `undefined` โผล่บนหน้าจอ
2. **3-Width Responsive Check**:
   - **Desktop Wide (`1920px`)**: การ์ดเรียงเต็มความกว้างอย่างสมดุล ไม่เหลือการ์ดครึ่งว่างครึ่งเต็ม
   - **Laptop (`1440px`)**: ตารางและกราฟไม่เบียดกัน ชื่อคณะ/หน่วยงานไม่ล้นขอบการ์ด
   - **Mobile (`375px`)**: ไม่มี Horizontal Scroll เกินหน้าจอ (`scrollWidth === innerWidth`) และป้าย Badge ยาวๆ ตัดบรรทัด (`text-wrap`) อย่างสวยงาม
3. **Dual-Theme Check**: ตรวจสอบทั้ง **Light Mode** และ **Dark Mode** ว่าข้อความและเส้นกราฟอ่านชัดเจนทั้งสองโหมด
