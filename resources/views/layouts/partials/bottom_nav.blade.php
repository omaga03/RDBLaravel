<nav class="navbar navbar-dark bg-dark navbar-expand fixed-bottom d-md-none border-top shadow-lg" style="height: 60px; z-index: 1040;">
    <ul class="navbar-nav nav-justified w-100">
        <li class="nav-item">
            <a href="{{ route('frontend.site.index') }}" class="nav-link {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') ? 'active text-primary fw-bold' : '' }} d-flex flex-column align-items-center justify-content-center h-100">
                <i class="bi bi-speedometer2 fs-5"></i>
                <span style="font-size: 0.7rem;">Home</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('frontend.rdbproject.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbproject.*') ? 'active text-success fw-bold' : '' }} d-flex flex-column align-items-center justify-content-center h-100">
                <span class="fs-5">📊</span>
                <span style="font-size: 0.7rem;">Projects</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('frontend.rdbpublished.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbpublished.*') ? 'active text-info fw-bold' : '' }} d-flex flex-column align-items-center justify-content-center h-100">
                <span class="fs-5">📑</span>
                <span style="font-size: 0.7rem;">Pubs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('frontend.rdbdip.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbdip.*') ? 'active text-warning fw-bold' : '' }} d-flex flex-column align-items-center justify-content-center h-100">
                <span class="fs-5">💡</span>
                <span style="font-size: 0.7rem;">IP</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('frontend.rdbprojectutilize.index') }}" class="nav-link {{ request()->routeIs('frontend.rdbprojectutilize.*') ? 'active text-danger fw-bold' : '' }} d-flex flex-column align-items-center justify-content-center h-100">
                <span class="fs-5">🚀</span>
                <span style="font-size: 0.7rem;">Util</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Spacer to prevent content from being hidden behind bottom nav -->
<div class="d-md-none" style="height: 60px;"></div>
