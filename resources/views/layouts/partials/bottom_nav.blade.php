<nav class="navbar navbar-expand fixed-bottom d-md-none shadow-lg rdb-bottom-nav" style="height: 64px; z-index: 1040; backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); background: var(--rdb-nav-bg); border-top: 1px solid var(--bs-border-color);">
    <ul class="navbar-nav nav-justified w-100 h-100">
        <li class="nav-item h-100">
            <a href="{{ route('frontend.site.index') }}" class="nav-link {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-speedometer2 fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">หน้าแรก</span>
            </a>
        </li>
        <li class="nav-item h-100">
            <a href="{{ route('frontend.rdbproject.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbproject.*') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-folder2-open fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">โครงการ</span>
            </a>
        </li>
        <li class="nav-item h-100">
            <a href="{{ route('frontend.rdbresearcher.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbresearcher.*') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-people-fill fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">นักวิจัย</span>
            </a>
        </li>
        <li class="nav-item h-100">
            <a href="{{ route('frontend.rdbpublished.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbpublished.*') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-journal-text fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">ตีพิมพ์</span>
            </a>
        </li>
        <li class="nav-item h-100">
            <a href="{{ route('frontend.rdbdip.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbdip.*') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-lightbulb-fill fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">ทรัพย์สินฯ</span>
            </a>
        </li>
        <li class="nav-item h-100">
            <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active text-primary fw-bold' : 'text-body-secondary' }} d-flex flex-column align-items-center justify-content-center h-100 py-1">
                <i class="bi bi-rocket-takeoff-fill fs-5"></i>
                <span style="font-size: 0.68rem;" class="mt-1">ใช้ประโยชน์</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Spacer to prevent content from being hidden behind bottom nav -->
<div class="d-md-none" style="height: 64px;"></div>
