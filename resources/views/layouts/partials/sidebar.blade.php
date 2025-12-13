<style>
    .sidebar-menu-item {
        font-size: 0.85rem !important; /* Smaller size as requested */
    }
    .sidebar-section-header {
        position: relative;
        font-size: 0.75rem;
        font-weight: 800;
        color: #adb5bd;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        padding-left: 1rem;
        display: flex;
        align-items: center;
    }
    .sidebar-section-header::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e9ecef;
        margin-left: 10px;
    }
</style>

<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-secondary border-0" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-arrow-bar-left"></i>
    </button>
</div>

<div class="list-group list-group-flush shadow-sm rounded-3">
    <a href="{{ route('frontend.site.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Dashboard">
        <i class="bi bi-speedometer2 me-3 fs-6"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('frontend.rdbproject.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbproject.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Projects">
        <i class="bi bi-bar-chart-line-fill me-3 fs-6"></i> <span class="sidebar-text">โครงการวิจัย</span>
    </a>
    <a href="{{ route('frontend.rdbpublished.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbpublished.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Publications">
        <i class="bi bi-file-earmark-text-fill me-3 fs-6"></i> <span class="sidebar-text">งานตีพิมพ์</span>
    </a>
    <a href="{{ route('frontend.rdbdip.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbdip.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="IP">
        <i class="bi bi-lightbulb-fill me-3 fs-6"></i> <span class="sidebar-text">ทรัพย์สินทางปัญญา</span>
    </a>
    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Utilization">
        <i class="bi bi-rocket-takeoff-fill me-3 fs-6"></i> <span class="sidebar-text">การใช้ประโยชน์</span>
    </a>

    <div class="sidebar-section-header">
        ประชาสัมพันธ์
    </div>

    <a href="{{ route('frontend.researchnews.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.researchnews.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Research News">
        <i class="bi bi-newspaper me-3 fs-6"></i> <span class="sidebar-text">ข่าวกิจกรรมงานวิจัย</span>
    </a>
    <a href="{{ route('frontend.researchcoferenceinthai.index') }}" class="list-group-item list-group-item-action sidebar-menu-item {{ request()->routeIs('frontend.researchcoferenceinthai.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-2 d-flex align-items-center rounded-2 mb-1" title="Research Conference">
        <i class="bi bi-megaphone-fill me-3 fs-6"></i> <span class="sidebar-text">การประชุมวิชาการ</span>
    </a>
</div>

{{-- Add some additional links or info if needed --}}
<div class="mt-auto pt-4 shadow-sm border-0 d-none d-lg-block sidebar-footer">
    <div class="card-body bg-light rounded-3 text-center p-2">
        <small class="text-muted d-block sidebar-text" style="font-size: 0.7rem;">Research Database System</small>
        <div class="mt-1 text-muted small" style="font-size: 0.7rem;">
            © {{ date('Y') }} <span class="sidebar-text">RDB</span>
        </div>
    </div>
</div>
