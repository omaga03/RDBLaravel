<div class="mb-4">
    <!-- Breadcrumb & Header Title -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('frontend.dashboard.index') }}" class="text-decoration-none">แดชบอร์ดงานวิจัย</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle ?? 'แดชบอร์ด' }}</li>
                </ol>
            </nav>
            <h3 class="mb-0 fw-bold text-body d-flex align-items-center gap-2">
                <i class="{{ $pageIcon ?? 'bi bi-speedometer2' }} text-primary"></i>
                <span>{{ $pageTitle ?? 'แดชบอร์ดสารสนเทศงานวิจัย' }}</span>
                @if(isset($selectedYear) && $selectedYear)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 fw-normal px-2.5 py-1">
                        ปีงบประมาณ {{ $selectedYear->year_name }}
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle fs-6 fw-normal px-2.5 py-1">
                        ข้อมูลสะสมทุกปีงบประมาณ
                    </span>
                @endif
            </h3>
        </div>

        <!-- Header Controls & Timestamp -->
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="badge bg-body-tertiary text-body-secondary border px-3 py-2 fw-medium">
                <i class="bi bi-clock-history text-primary me-1"></i> ปรับปรุงข้อมูลล่าสุด: {{ date('d/m/Y') }}
            </span>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1 shadow-xs" title="พิมพ์รายงานสรุป">
                <i class="bi bi-printer me-1.5"></i> พิมพ์
            </button>
        </div>
    </div>

    <!-- 6 Domain Navigation Pills -->
    <div class="card border shadow-sm p-1.5 bg-body-tertiary" style="border-radius: var(--rdb-radius-md) !important;">
        @php
            $navParams = array_filter(request()->only(['year_id', 'department_id']));
        @endphp
        <ul class="nav nav-pills nav-fill flex-nowrap overflow-x-auto gap-1" id="dashboardNavTabs" style="scrollbar-width: thin;">
            <!-- 1. Executive Overview -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.index') || request()->routeIs('dashboard') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.index', $navParams) }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>ภาพรวมยุทธศาสตร์</span>
                </a>
            </li>

            <!-- 2. Projects & Budget -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.projects') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.projects', $navParams) }}">
                    <i class="bi bi-folder2-open"></i>
                    <span>โครงการ & งบประมาณ</span>
                </a>
            </li>

            <!-- 3. Academic Publications -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.publications') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.publications', $navParams) }}">
                    <i class="bi bi-journal-richtext"></i>
                    <span>ผลงานตีพิมพ์ & สากล</span>
                </a>
            </li>

            <!-- 4. Utilization & GIS Map -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.utilizations') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.utilizations', $navParams) }}">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span>การใช้ประโยชน์ & แผนที่ GIS</span>
                </a>
            </li>

            <!-- 5. Intellectual Property -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.ip') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.ip', $navParams) }}">
                    <i class="bi bi-lightbulb-fill text-warning"></i>
                    <span>ทรัพย์สินทางปัญญา</span>
                </a>
            </li>

            <!-- 6. Researchers & Faculty -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.researchers') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.researchers', $navParams) }}">
                    <i class="bi bi-people-fill text-info"></i>
                    <span>ศักยภาพนักวิจัย & คณะ</span>
                </a>
            </li>

            <!-- 7. Quality Assurance (QA & Accreditations) -->
            <li class="nav-item">
                <a class="nav-link py-2 px-3 text-nowrap d-flex align-items-center justify-content-center gap-2 {{ request()->routeIs('frontend.dashboard.qa') ? 'active shadow-sm fw-semibold' : 'text-body-secondary' }}" 
                   href="{{ route('frontend.dashboard.qa', $navParams) }}">
                    <i class="bi bi-shield-check text-success"></i>
                    <span>ประกันคุณภาพ (QA)</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Intelligence Filter Bar (4-5 Contextual Filters per Domain) -->
    <div class="mt-3">
        @include('frontend.dashboard._filter_bar')
    </div>
</div>

