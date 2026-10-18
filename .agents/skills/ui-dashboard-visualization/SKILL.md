---
name: ui-dashboard-visualization
description: Design patterns for dashboard stat cards, Chart.js multi-type charts (Bar/Line), and dynamic fiscal year filtering.
---

# Dashboard & Data Visualization Standards (laravel_rdb)

Use this skill whenever designing or modifying dashboards (frontend `/` or backend `/backend`) and Chart.js visualizations.

## 1. Stat Cards with Floating Watermark Icons
Stat cards on dashboards use vibrant gradients with a subtle rotated background icon for quick visual recognition:

```blade
<div class="card stats-card h-100 text-white shadow border-0 rounded-4" 
     style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
    <div class="card-body position-relative p-4 overflow-hidden">
        <i class="bi bi-file-earmark-text-fill stats-icon position-absolute end-0 bottom-0 opacity-25" 
           style="font-size: 5rem; transform: rotate(-15deg); margin-right: 10px; margin-bottom: -10px;"></i>
        <div class="position-relative" style="z-index: 1;">
            <h6 class="text-white-50 text-uppercase fw-bold mb-2">📅 ปีงบประมาณ {{ $yearName }}</h6>
            <h5 class="fw-bold mb-1">จำนวนโครงการวิจัย</h5>
            <div class="display-5 fw-bold">{{ number_format($sumProject) }}</div>
            <small class="text-white-50 mt-1">โครงการ</small>
        </div>
    </div>
</div>
```

### Color Palette for System Cards:
- **จำนวนโครงการ (Projects)**: Green Gradient `linear-gradient(135deg, #11998e 0%, #38ef7d 100%)`
- **งบประมาณ (Budget)**: Orange Gradient `linear-gradient(135deg, #FF8008 0%, #FFC837 100%)`
- **นักวิจัย (Researchers)**: Blue Gradient `linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)`
- **ผลงานตีพิมพ์ (Publications)**: Purple Gradient `linear-gradient(135deg, #8A2387 0%, #E94057 100%)`

## 2. Dynamic Fiscal Year Filter
Dashboards must include a fiscal year dropdown that submits via GET and filters all dashboard statistics, tables, and charts:
```blade
<form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2" id="dashboardYearForm">
    <label for="year_id" class="me-2 fw-bold text-nowrap"><i class="bi bi-calendar3 me-1"></i> ปีงบประมาณ:</label>
    <select name="year_id" id="year_id" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">-- ทุกปีงบประมาณ --</option>
        @foreach($years as $y)
            <option value="{{ $y->year_id }}" {{ request('year_id') == $y->year_id ? 'selected' : '' }}>
                {{ $y->year_name }}
            </option>
        @endforeach
    </select>
</form>
```

## 3. Chart.js Dual Bar & Line Chart Standard
When presenting multi-year research trends, display project counts as Bars on the primary Y-axis (left) and budget amounts as a smooth Line on the secondary Y-axis (right):
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
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'งบประมาณ (บาท)',
                data: budgets,
                borderColor: 'rgba(255, 159, 64, 1)',
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderWidth: 3,
                tension: 0.3,
                fill: false,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                position: 'left',
                title: { display: true, text: 'จำนวนโครงการ' },
                ticks: { precision: 0 }
            },
            y1: {
                type: 'linear',
                position: 'right',
                title: { display: true, text: 'งบประมาณ (บาท)' },
                grid: { drawOnChartArea: false },
                ticks: {
                    callback: (value) => value.toLocaleString('th-TH')
                }
            }
        }
    }
});
```
