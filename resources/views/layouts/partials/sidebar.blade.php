<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-secondary border-0" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-arrow-bar-left"></i>
    </button>
</div>

<div class="list-group list-group-flush shadow-sm rounded-3">
    <a href="{{ route('frontend.site.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-3 d-flex align-items-center rounded-2 mb-1" title="Dashboard">
        <i class="bi bi-speedometer2 me-3 fs-5"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('frontend.rdbproject.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbproject.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-3 d-flex align-items-center rounded-2 mb-1" title="Projects">
        <i class="bi bi-bar-chart-line-fill me-3 fs-5"></i> <span class="sidebar-text">โครงการวิจัย</span>
    </a>
    <a href="{{ route('frontend.rdbpublished.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbpublished.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-3 d-flex align-items-center rounded-2 mb-1" title="Publications">
        <i class="bi bi-file-earmark-text-fill me-3 fs-5"></i> <span class="sidebar-text">งานตีพิมพ์</span>
    </a>
    <a href="{{ route('frontend.rdbdip.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbdip.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-3 d-flex align-items-center rounded-2 mb-1" title="IP">
        <i class="bi bi-lightbulb-fill me-3 fs-5"></i> <span class="sidebar-text">ทรัพย์สินทางปัญญา</span>
    </a>
    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active bg-primary border-primary fw-bold text-white' : '' }} py-3 d-flex align-items-center rounded-2 mb-1" title="Utilization">
        <i class="bi bi-rocket-takeoff-fill me-3 fs-5"></i> <span class="sidebar-text">การใช้ประโยชน์</span>
    </a>
</div>

{{-- Add some additional links or info if needed --}}
<div class="mt-auto pt-4 shadow-sm border-0 d-none d-lg-block sidebar-footer">
    <div class="card-body bg-light rounded-3 text-center p-2">
        <small class="text-muted d-block sidebar-text">Research Database System</small>
        <div class="mt-1 text-muted small">
            © {{ date('Y') }} <span class="sidebar-text">RDB</span>
        </div>
    </div>
</div>
