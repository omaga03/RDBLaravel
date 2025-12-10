<div class="d-flex justify-content-end mb-2">
    <button class="btn btn-sm btn-outline-secondary border-0" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-arrow-bar-left"></i>
    </button>
</div>

<div class="list-group list-group-flush shadow-sm rounded-3">
    <a href="{{ route('frontend.site.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active' : '' }} py-3 d-flex align-items-center" title="Dashboard">
        <i class="bi bi-speedometer2 me-2 fs-5"></i> <span class="sidebar-text">หน้าหลัก (Dashboard)</span>
    </a>
    <a href="{{ route('frontend.rdbproject.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbproject.*') ? 'active' : '' }} py-3 d-flex align-items-center" title="Projects">
        <span class="me-2 fs-5">📊</span> <span class="sidebar-text">โครงการวิจัย</span>
    </a>
    <a href="{{ route('frontend.rdbpublished.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbpublished.*') ? 'active' : '' }} py-3 d-flex align-items-center" title="Publications">
        <span class="me-2 fs-5">📑</span> <span class="sidebar-text">ผลงานตีพิมพ์</span>
    </a>
    <a href="{{ route('frontend.rdbdip.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbdip.*') ? 'active' : '' }} py-3 d-flex align-items-center" title="IP">
        <span class="me-2 fs-5">💡</span> <span class="sidebar-text">ทรัพย์สินทางปัญญาล่าสุด</span>
    </a>
    <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active' : '' }} py-3 d-flex align-items-center" title="Utilization">
        <span class="me-2 fs-5">🚀</span> <span class="sidebar-text">การนำไปใช้ประโยชน์</span>
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
