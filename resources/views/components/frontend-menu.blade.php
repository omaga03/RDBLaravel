<nav class="navbar navbar-expand-lg shadow-sm border-bottom py-0 frontend-subnav" style="z-index: 1045; background-color: var(--bs-tertiary-bg);">
    <style>
        .frontend-subnav .nav-link {
            padding: 0.65rem 0.95rem !important;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--bs-body-color) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .frontend-subnav .nav-link:hover {
            color: var(--rdb-secondary) !important;
            background-color: rgba(37, 99, 235, 0.08);
        }
        .frontend-subnav .nav-link.active {
            color: var(--rdb-secondary) !important;
            font-weight: 600;
        }
        .frontend-subnav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0.75rem;
            right: 0.75rem;
            height: 2.5px;
            background: var(--rdb-secondary);
            border-radius: 2px 2px 0 0;
        }
        .frontend-subnav .dropdown-menu {
            border-radius: var(--rdb-radius-md);
            border: 1px solid var(--bs-border-color);
            box-shadow: var(--rdb-shadow-md);
            padding: 0.5rem;
            background-color: var(--bs-card-bg);
        }
        .frontend-subnav .dropdown-item {
            border-radius: var(--rdb-radius-sm);
            padding: 0.45rem 0.85rem;
            font-size: 0.85rem;
            transition: background-color 0.15s ease;
            color: var(--bs-body-color);
        }
        .frontend-subnav .dropdown-item:hover {
            background-color: var(--rdb-primary-subtle);
            color: var(--rdb-primary);
        }
        [data-bs-theme="dark"] .frontend-subnav .dropdown-item:hover {
            background-color: rgba(37, 99, 235, 0.2);
            color: #60A5FA;
        }
    </style>
    <div class="container-fluid px-4">
        <button class="navbar-toggler py-1 px-2 my-1 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#frontendMenuContent" aria-controls="frontendMenuContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="d-inline-flex align-items-center gap-1 small fw-semibold text-body">
                <i class="bi bi-list fs-5"></i> เมนูฐานข้อมูล
            </span>
        </button>
        
        <div class="collapse navbar-collapse" id="frontendMenuContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <!-- 1. หน้าหลัก -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('frontend.site.index') || request()->routeIs('home') || (request()->is('/') && !request()->is('dashboard*')) ? 'active' : '' }}" 
                       href="{{ route('home') }}">
                        <i class="bi bi-house-door-fill text-secondary me-1.5" style="font-size: 0.95rem;"></i> หน้าหลัก
                    </a>
                </li>

                <!-- 2. แดชบอร์ดงานวิจัย (Option B: 6 แดชบอร์ด) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('dashboard*') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-speedometer2 text-primary me-1.5" style="font-size: 0.95rem;"></i> แดชบอร์ดงานวิจัย
                    </a>
                    <ul class="dropdown-menu shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.index') || (request()->is('dashboard') && !request()->is('dashboard/*')) ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.index') }}">
                                <i class="bi bi-speedometer2 text-primary me-2"></i> ภาพรวมยุทธศาสตร์
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.projects') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.projects') }}">
                                <i class="bi bi-folder2-open text-success me-2"></i> โครงการ & งบประมาณ
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.publications') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.publications') }}">
                                <i class="bi bi-journal-richtext text-info me-2"></i> ผลงานตีพิมพ์ & สากล
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.utilizations') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.utilizations') }}">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i> การใช้ประโยชน์ & แผนที่ GIS
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.ip') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.ip') }}">
                                <i class="bi bi-lightbulb-fill text-warning me-2"></i> ทรัพย์สินทางปัญญา
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.researchers') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.researchers') }}">
                                <i class="bi bi-people-fill text-primary me-2"></i> ศักยภาพนักวิจัย & คณะ
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 2. โครงการวิจัย -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbproject*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbproject.index') }}">
                        <i class="bi bi-folder2-open text-success me-1.5" style="font-size: 0.95rem;"></i> โครงการวิจัย
                    </a>
                </li>

                <!-- 3. ผลงานตีพิมพ์ -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbpublished*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbpublished.index') }}">
                        <i class="bi bi-journal-text text-info me-1.5" style="font-size: 0.95rem;"></i> งานตีพิมพ์
                    </a>
                </li>

                <!-- 4. ทรัพย์สินทางปัญญา -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbdip*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbdip.index') }}">
                        <i class="bi bi-lightbulb-fill text-warning me-1.5" style="font-size: 0.95rem;"></i> ทรัพย์สินทางปัญญา
                    </a>
                </li>

                <!-- 5. นักวิจัย -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbresearcher*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbresearcher.index') }}">
                        <i class="bi bi-people-fill text-primary me-1.5" style="font-size: 0.95rem;"></i> นักวิจัย
                    </a>
                </li>

                <!-- 6. การใช้ประโยชน์ -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*projectutilize*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbprojectutilize.index') }}">
                        <i class="bi bi-rocket-takeoff-fill text-danger me-1.5" style="font-size: 0.95rem;"></i> การใช้ประโยชน์
                    </a>
                </li>

                <!-- 7. ประชาสัมพันธ์ (Dropdown) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('*researchnews*') || request()->is('*researchcoferenceinthai*') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-megaphone-fill text-warning me-1.5" style="font-size: 0.95rem;"></i> ข่าวประชาสัมพันธ์
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*researchnews*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.researchnews.index') }}">
                                <i class="bi bi-newspaper text-primary me-2"></i> ข่าวกิจกรรมงานวิจัย
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*researchcoferenceinthai*') ? 'active' : '' }}" 
                               href="{{ route('frontend.researchcoferenceinthai.index') }}">
                                <i class="bi bi-calendar-event text-info me-2"></i> การประชุมวิชาการ
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- Right side indicator -->
            <div class="d-none d-xl-flex align-items-center text-body-secondary small ms-auto">
                <span class="badge bg-body border text-body-secondary fw-normal px-2.5 py-1">
                    <i class="bi bi-database-check text-success me-1"></i> ฐานข้อมูลงานวิจัย มหาวิทยาลัยราชภัฏเพชรบูรณ์
                </span>
            </div>
        </div>
    </div>
</nav>
