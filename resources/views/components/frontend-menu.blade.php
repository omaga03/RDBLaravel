<nav class="navbar navbar-expand-lg shadow-sm border-bottom py-0 frontend-subnav" style="z-index: 1020; background-color: var(--bs-tertiary-bg);">
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
            z-index: 1025 !important;
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
        .frontend-subnav .dropdown-item.active,
        .frontend-subnav .dropdown-item:active {
            background-color: rgba(37, 99, 235, 0.12) !important;
            color: var(--rdb-secondary, #1D4ED8) !important;
            font-weight: 600 !important;
        }
        [data-bs-theme="dark"] .frontend-subnav .dropdown-item:hover {
            background-color: rgba(37, 99, 235, 0.2);
            color: #60A5FA;
        }
        [data-bs-theme="dark"] .frontend-subnav .dropdown-item.active,
        [data-bs-theme="dark"] .frontend-subnav .dropdown-item:active {
            background-color: rgba(37, 99, 235, 0.25) !important;
            color: #93C5FD !important;
            font-weight: 600 !important;
        }
    </style>
    <div class="container-fluid px-4">
        <button class="navbar-toggler py-1 px-2 my-1 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#frontendMenuContent" aria-controls="frontendMenuContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="d-inline-flex align-items-center gap-1 small fw-semibold text-body">
                <i class="bi bi-list fs-5 me-1.5"></i> เมนูฐานข้อมูล
            </span>
        </button>
        
        <div class="collapse navbar-collapse" id="frontendMenuContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <!-- 1. แดชบอร์ดงานวิจัย (Option B: 6 แดชบอร์ด) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('dashboard*') || request()->routeIs('frontend.dashboard.*') || request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-speedometer2 text-primary me-1.5" style="font-size: 0.95rem;"></i> แดชบอร์ดงานวิจัย
                    </a>
                    <ul class="dropdown-menu shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.index') || request()->routeIs('dashboard') || (request()->is('dashboard') && !request()->is('dashboard/*')) ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.index') }}">
                                <i class="bi bi-speedometer2 text-primary me-2"></i> ภาพรวมยุทธศาสตร์
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.projects') || request()->is('dashboard/projects*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.projects') }}">
                                <i class="bi bi-folder2-open text-success me-2"></i> โครงการ & งบประมาณ
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.publications') || request()->is('dashboard/publications*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.publications') }}">
                                <i class="bi bi-journal-richtext text-info me-2"></i> ผลงานตีพิมพ์ & สากล
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.utilizations') || request()->is('dashboard/utilizations*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.utilizations') }}">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i> การใช้ประโยชน์ & แผนที่ GIS
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.ip') || request()->is('dashboard/ip*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.ip') }}">
                                <i class="bi bi-lightbulb-fill text-warning me-2"></i> ทรัพย์สินทางปัญญา
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('frontend.dashboard.researchers') || request()->is('dashboard/researchers*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.researchers') }}">
                                <i class="bi bi-people-fill text-primary me-2"></i> ศักยภาพนักวิจัย & คณะ
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between {{ request()->routeIs('frontend.dashboard.qa') || request()->is('dashboard/qa*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.dashboard.qa') }}">
                                <span class="d-flex align-items-center"><i class="bi bi-shield-check text-success me-2"></i> แดชบอร์ดประกันคุณภาพ (QA)</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 ms-2" style="font-size: 0.7rem;">QA</span>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- 2. โครงการวิจัย (Dropdown: โครงการวิจัย, แผนงานวิจัย, นักวิจัย) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ (request()->is('*rdbproject*') && !request()->is('*rdbprojectutilize*')) || request()->is('*rdbprojectgroup*') || request()->is('*rdbresearcher*') || (request()->routeIs('frontend.rdbproject.*') && !request()->routeIs('frontend.rdbprojectutilize.*')) || request()->routeIs('frontend.rdbprojectgroup.*') || request()->routeIs('frontend.rdbresearcher.*') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-folder2-open text-success me-1.5" style="font-size: 0.95rem;"></i> โครงการวิจัย
                    </a>
                    <ul class="dropdown-menu shadow-sm">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ (request()->is('*rdbproject*') && !request()->is('*rdbprojectgroup*') && !request()->is('*rdbprojectutilize*')) || (request()->routeIs('frontend.rdbproject.*') && !request()->routeIs('frontend.rdbprojectgroup.*') && !request()->routeIs('frontend.rdbprojectutilize.*')) ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.rdbproject.index') }}">
                                <i class="bi bi-folder2-open text-success me-2"></i> โครงการวิจัย
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*rdbprojectgroup*') || request()->routeIs('frontend.rdbprojectgroup.*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.rdbprojectgroup.index') }}">
                                <i class="bi bi-diagram-3 text-info me-2"></i> แผนงานวิจัย
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*rdbresearcher*') || request()->routeIs('frontend.rdbresearcher.*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.rdbresearcher.index') }}">
                                <i class="bi bi-people-fill text-primary me-2"></i> นักวิจัย
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 3. ผลงานตีพิมพ์ -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbpublished*') || request()->routeIs('frontend.rdbpublished.*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbpublished.index') }}">
                        <i class="bi bi-journal-text text-info me-1.5" style="font-size: 0.95rem;"></i> งานตีพิมพ์
                    </a>
                </li>

                <!-- 4. ทรัพย์สินทางปัญญา -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*rdbdip*') || request()->routeIs('frontend.rdbdip.*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbdip.index') }}">
                        <i class="bi bi-lightbulb-fill text-warning me-1.5" style="font-size: 0.95rem;"></i> ทรัพย์สินทางปัญญา
                    </a>
                </li>

                <!-- 5. การใช้ประโยชน์ -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('*projectutilize*') || request()->routeIs('frontend.rdbprojectutilize.*') ? 'active' : '' }}" 
                       href="{{ route('frontend.rdbprojectutilize.index') }}">
                        <i class="bi bi-rocket-takeoff-fill text-danger me-1.5" style="font-size: 0.95rem;"></i> การใช้ประโยชน์
                    </a>
                </li>

                <!-- 6. ประชาสัมพันธ์ (Dropdown) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('*researchnews*') || request()->is('*researchcoferenceinthai*') || request()->routeIs('frontend.researchnews.*') || request()->routeIs('frontend.researchcoferenceinthai.*') ? 'active' : '' }}" 
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-megaphone-fill text-warning me-1.5" style="font-size: 0.95rem;"></i> ข่าวประชาสัมพันธ์
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*researchnews*') || request()->routeIs('frontend.researchnews.*') ? 'active fw-bold' : '' }}" 
                               href="{{ route('frontend.researchnews.index') }}">
                                <i class="bi bi-newspaper text-primary me-2"></i> ข่าวกิจกรรมงานวิจัย
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center {{ request()->is('*researchcoferenceinthai*') || request()->routeIs('frontend.researchcoferenceinthai.*') ? 'active fw-bold' : '' }}" 
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
