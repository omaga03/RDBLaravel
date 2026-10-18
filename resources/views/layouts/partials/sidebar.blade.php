<style>
    .sidebar-menu-item {
        font-size: 0.82rem !important;
        font-weight: 500;
        border-radius: var(--rdb-radius-sm) !important;
        margin-bottom: 2px;
        border: none !important;
        padding: 0.48rem 0.75rem !important;
        color: var(--bs-body-color) !important;
        line-height: 1.35;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar-menu-item:hover {
        background-color: var(--rdb-primary-subtle) !important;
        color: var(--rdb-primary) !important;
        transform: translateX(3px);
    }
    [data-bs-theme="dark"] .sidebar-menu-item:hover {
        background-color: rgba(37, 99, 235, 0.15) !important;
        color: #60A5FA !important;
    }
    .sidebar-menu-item.active {
        background: var(--rdb-gradient-primary) !important;
        color: #FFFFFF !important;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(15, 39, 68, 0.25);
    }
    .sidebar-menu-item.active i {
        color: #FFFFFF !important;
    }
    .sidebar-section-header {
        position: relative;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--bs-secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-top: 1rem;
        margin-bottom: 0.35rem;
        padding-left: 0.75rem;
        display: flex;
        align-items: center;
    }
    .sidebar-section-header::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--bs-border-color);
        margin-left: 10px;
    }
</style>

<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-secondary border-0" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-arrow-bar-left"></i>
    </button>
</div>

<div class="list-group list-group-flush shadow-sm rounded-3 p-2 bg-body border" style="border-radius: var(--rdb-radius-md) !important;">
    <a href="{{ route('frontend.site.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active' : '' }} d-flex align-items-center" title="Dashboard">
        <i class="bi bi-speedometer2 me-2.5 text-primary" style="font-size: 0.95rem;"></i> <span class="sidebar-text">แดชบอร์ดภาพรวม</span>
    </a>
    <a href="{{ route('frontend.rdbproject.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbproject.*') ? 'active' : '' }} d-flex align-items-center" title="Projects">
        <i class="bi bi-folder2-open me-2.5 text-success" style="font-size: 0.95rem;"></i> <span class="sidebar-text">โครงการวิจัย</span>
    </a>
    <a href="{{ route('frontend.rdbpublished.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbpublished.*') ? 'active' : '' }} d-flex align-items-center" title="Publications">
        <i class="bi bi-journal-text me-2.5 text-info" style="font-size: 0.95rem;"></i> <span class="sidebar-text">งานตีพิมพ์</span>
    </a>
    <a href="{{ route('frontend.rdbdip.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbdip.*') ? 'active' : '' }} d-flex align-items-center" title="IP">
        <i class="bi bi-lightbulb-fill me-2.5 text-warning" style="font-size: 0.95rem;"></i> <span class="sidebar-text">ทรัพย์สินทางปัญญา</span>
    </a>
    <a href="{{ route('frontend.rdbresearcher.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbresearcher.*') ? 'active' : '' }} d-flex align-items-center" title="Researchers">
        <i class="bi bi-people-fill me-2.5 text-primary" style="font-size: 0.95rem;"></i> <span class="sidebar-text">นักวิจัย</span>
    </a>
    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active' : '' }} d-flex align-items-center" title="Utilization">
        <i class="bi bi-rocket-takeoff-fill me-2.5 text-danger" style="font-size: 0.95rem;"></i> <span class="sidebar-text">การใช้ประโยชน์</span>
    </a>

    <div class="sidebar-section-header">
        ประชาสัมพันธ์
    </div>

    <a href="{{ route('frontend.researchnews.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.researchnews.*') ? 'active' : '' }} d-flex align-items-center" title="Research News">
        <i class="bi bi-newspaper me-2.5 text-primary" style="font-size: 0.95rem;"></i> <span class="sidebar-text">ข่าวกิจกรรมงานวิจัย</span>
    </a>
    <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.researchcoferenceinthai.*') ? 'active' : '' }} d-flex align-items-center" title="Research Conference">
        <i class="bi bi-megaphone-fill me-2.5 text-warning" style="font-size: 0.95rem;"></i> <span class="sidebar-text">การประชุมวิชาการ</span>
    </a>
</div>

{{-- Sidebar Footer --}}
<div class="mt-auto pt-4 shadow-sm border-0 d-none d-lg-block sidebar-footer">
    <div class="card-body bg-body-tertiary rounded-3 text-center p-3 border" style="border-radius: var(--rdb-radius-sm) !important;">
        <small class="text-body-secondary d-block sidebar-text fw-bold" style="font-size: 0.72rem; letter-spacing: 0.03em;">ระบบฐานข้อมูลงานวิจัย</small>
        <div class="mt-1 text-body-secondary small" style="font-size: 0.7rem;">
            © {{ date('Y') }} <span class="sidebar-text">PCRU RDB</span>
        </div>
    </div>
</div>
