<nav class="navbar navbar-expand-md shadow-sm border-bottom py-0 backend-subnav" style="z-index: 1030; background-color: var(--bs-tertiary-bg);">
    <style>
        .backend-subnav .nav-link {
            padding: 0.65rem 0.95rem !important;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--bs-body-color) !important;
            transition: all 0.2s ease;
            position: relative;
        }
        .backend-subnav .nav-link:hover {
            color: var(--rdb-secondary) !important;
            background-color: rgba(37, 99, 235, 0.08);
        }
        .backend-subnav .nav-link.active {
            color: var(--rdb-secondary) !important;
            font-weight: 600;
        }
        .backend-subnav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0.75rem;
            right: 0.75rem;
            height: 2px;
            background: var(--rdb-secondary);
        }
        .backend-subnav .dropdown-menu {
            border-radius: var(--rdb-radius-md);
            border: 1px solid var(--bs-border-color);
            box-shadow: var(--rdb-shadow-md);
            padding: 0.5rem;
            z-index: 1040 !important;
        }
        .backend-subnav .dropdown-item {
            border-radius: var(--rdb-radius-sm);
            padding: 0.45rem 0.85rem;
            font-size: 0.85rem;
            transition: background-color 0.15s ease;
        }
        .backend-subnav .dropdown-item:hover {
            background-color: var(--rdb-primary-subtle);
            color: var(--rdb-primary);
        }
        [data-bs-theme="dark"] .backend-subnav .dropdown-item:hover {
            background-color: rgba(37, 99, 235, 0.2);
            color: #60A5FA;
        }
    </style>
    <div class="container-fluid px-4">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#backendMenuContent" aria-controls="backendMenuContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="font-size: 0.8rem;"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="backendMenuContent">
            @php
                $currentUser = auth()->user();
                $isSuperAdmin = $currentUser && ($currentUser->hasRole('The Creator') || $currentUser->hasRole('Admin'));
            @endphp
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- 0. แดชบอร์ด (Dashboard) -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('backend.site.index') ? 'active fw-bold' : '' }}" href="{{ route('backend.site.index') }}">
                            <i class="bi bi-speedometer2 text-primary me-1.5"></i> แดชบอร์ด
                        </a>
                    </li>

                    <!-- 0.1 คิวตรวจรับผลงาน (Review Queue) -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Project', 'Admin Published', 'Admin Dip', 'Admin Utilize', 'Faculty Coordinator', 'Sub Admin']) || $currentUser->hasPermission(['Project', 'Published', 'Dip', 'Utilize']))))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('backend/workflow*') ? 'active fw-bold' : '' }}" href="{{ route('backend.workflow.queue') }}">
                            <i class="bi bi-inbox-fill text-warning me-1.5"></i> คิวตรวจรับผลงาน
                        </a>
                    </li>
                    @endif

                    <!-- 1. โครงการ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Project', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbProject.View', 'Project', 'MasterData.Manage']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_project*') || request()->is('backend/rdbgroupproject*') || request()->is('backend/rdbprojecttypesgroup*') || request()->is('backend/rdbprojecttypesmaster*') || request()->is('backend/rdbprojecttype*') || request()->is('backend/rdbprojecttypesub*') || request()->is('backend/rdbprojectposition*') || request()->is('backend/rdbprojectpersonnel*') || request()->is('backend/rdbstrategic*') || request()->is('backend/rdbyear*') || request()->is('backend/rdbbranch*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-folder2-open text-primary me-1.5"></i> โครงการ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdb_project.index') }}"><i class="bi bi-list-ul me-2 text-primary"></i>รายการโครงการทั้งหมด</a></li>
                            <li><a class="dropdown-item d-flex align-items-center text-warning" href="{{ route('backend.rdb_project.index', ['workflow_status' => 'unapproved']) }}"><i class="bi bi-clock-history me-2 text-warning"></i>ฉบับร่าง / รออนุมัติ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold d-flex align-items-center"><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i> ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbyear.index') }}"><i class="bi bi-calendar3 me-2 text-primary"></i>ปีงบประมาณ</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbstrategic.index') }}"><i class="bi bi-bullseye me-2 text-danger"></i>ยุทธศาสตร์</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbbranch.index') }}"><i class="bi bi-diagram-3 me-2 text-info"></i>สาขาการวิจัย</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbgroupproject.index') }}"><i class="bi bi-collection me-2 text-success"></i>กลุ่มโครงการ / แผนงาน</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojecttypesgroup.index') }}"><i class="bi bi-tags me-2 text-secondary"></i>กลุ่มประเภททุน</a></li>
                            <li><a class="dropdown-item d-flex align-items-center fw-semibold text-primary" href="{{ route('backend.rdbprojecttypesmaster.index') }}"><i class="bi bi-tag-fill me-2 text-primary"></i>หมวดหมู่ทุนหลัก (Master Category)</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojecttype.index') }}"><i class="bi bi-bookmark-star me-2 text-warning"></i>ประเภททุน</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojecttypesub.index') }}"><i class="bi bi-bookmark-dash me-2 text-secondary"></i>ประเภทโครงการทุนย่อย</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojectposition.index') }}"><i class="bi bi-person-badge me-2 text-success"></i>ตำแหน่งในโครงการ</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojectpersonnel.index') }}"><i class="bi bi-people me-2 text-info"></i>จำนวนคนในคณะ รายปี</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 2. ตีพิมพ์ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Published', 'Published', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbPublished.View', 'Published']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_published*') || request()->is('backend/rdbpublishedtype*') || request()->is('backend/rdbbranch*') || request()->is('backend/rdbpublishedbranchper*') || request()->is('backend/rdbpublishedpersonnel*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-journal-text text-info me-1.5"></i> ตีพิมพ์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdb_published.index') }}"><i class="bi bi-file-earmark-text me-2 text-info"></i>รายการตีพิมพ์ทั้งหมด</a></li>
                            <li><a class="dropdown-item d-flex align-items-center text-warning" href="{{ route('backend.rdb_published.index', ['workflow_status' => 'unapproved']) }}"><i class="bi bi-clock-history me-2 text-warning"></i>ฉบับร่าง / รออนุมัติ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold d-flex align-items-center"><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i> ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbpublishedtype.index') }}"><i class="bi bi-journal-bookmark me-2 text-primary"></i>ประเภทผลงาน</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbbranch.index') }}"><i class="bi bi-diagram-3 me-2 text-info"></i>สาขางานตีพิมพ์</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbpublishedbranchper.index') }}"><i class="bi bi-pie-chart me-2 text-success"></i>สัดส่วนสาขาการวิจัย รายปี</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbpublishedpersonnel.index') }}"><i class="bi bi-people me-2 text-secondary"></i>จำนวนคนในคณะ รายปี</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 3. การใช้ประโยชน์ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Utilize', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbProjectUtilize.View', 'Utilize']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbprojectutilize*') || request()->is('backend/rdbprojectutilizetype*') || request()->is('backend/rdbchangwat*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-rocket-takeoff text-danger me-1.5"></i> การใช้ประโยชน์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojectutilize.index') }}"><i class="bi bi-graph-up-arrow me-2 text-danger"></i>รายการการใช้ฯ ทั้งหมด</a></li>
                            <li><a class="dropdown-item d-flex align-items-center text-warning" href="{{ route('backend.rdbprojectutilize.index', ['workflow_status' => 'unapproved']) }}"><i class="bi bi-clock-history me-2 text-warning"></i>ฉบับร่าง / รออนุมัติ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold d-flex align-items-center"><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i> ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprojectutilizetype.index') }}"><i class="bi bi-tag-fill me-2 text-danger"></i>ประเภทการนำไปใช้ฯ</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbchangwat.index') }}"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>พื้นที่และพิกัด (ต. อ. จ.)</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 4. ทรัพย์สินฯ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Dip', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbDip.View', 'Dip']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_dip*') || request()->is('backend/rdbdiptype*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-award text-warning me-1.5"></i> ทรัพย์สินฯ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdb_dip.index') }}"><i class="bi bi-award me-2 text-warning"></i>รายการทรัพย์สินฯ ทั้งหมด</a></li>
                            <li><a class="dropdown-item d-flex align-items-center text-warning" href="{{ route('backend.rdb_dip.index', ['workflow_status' => 'unapproved']) }}"><i class="bi bi-clock-history me-2 text-warning"></i>ฉบับร่าง / รออนุมัติ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold d-flex align-items-center"><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i> ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbdiptype.index') }}"><i class="bi bi-patch-check-fill me-2 text-warning"></i>ประเภททรัพย์สินฯ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 5. นักวิจัย -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasPermission('RdbResearcher.View') || $currentUser->hasRole(['Researcher', 'Admin Researcher']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_researcher*') || request()->is('backend/rdbprefix*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people text-success me-1.5"></i> นักวิจัย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdb_researcher.index') }}"><i class="bi bi-person-lines-fill me-2 text-success"></i> รายการนักวิจัย</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold d-flex align-items-center"><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i> ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbprefix.index') }}"><i class="bi bi-card-text me-2 text-info"></i>คำนำหน้าชื่อ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 6. ข่าว/กิจกรรม -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['news', 'Event'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/research_news*') || request()->is('backend/research_conference*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-newspaper text-info me-1.5"></i> ข่าว/กิจกรรม
                        </a>
                        <ul class="dropdown-menu">
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('news')))
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.research_news.index') }}"><i class="bi bi-megaphone-fill me-2 text-warning"></i> ข่าวทุนวิจัย</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Event', 'MasterData.Manage'])))
                            <li><a class="dropdown-item d-flex align-items-center {{ request()->is('backend/research_conference*') ? 'active' : '' }}" href="{{ route('backend.research_conference.index') }}"><i class="bi bi-calendar-event-fill me-2 text-primary"></i> ข่าวงานประชุมวิชาการ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 7. องค์กร -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Department', 'MasterData.Manage'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbdepartment*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building text-secondary me-1.5"></i> องค์กร
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbdepartment.index') }}"><i class="bi bi-buildings-fill me-2 text-primary"></i> หน่วยงาน/คณะ</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('backend.rdbdepartmenttype.index') }}"><i class="bi bi-diagram-2-fill me-2 text-secondary"></i> ประเภทหน่วยงาน</a></li>
                        </ul>
                    </li>
                    @endif

                    <!-- 8. ผู้ดูแลระบบ -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['User Manager', 'Rbac.Management', 'RBAC Manager'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/user*') || request()->is('backend/rbac*') || request()->is('backend/settings*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock text-danger me-1.5"></i> ผู้ดูแลระบบ
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('User Manager')))
                            <li><a class="dropdown-item d-flex align-items-center {{ request()->is('backend/user*') ? 'active' : '' }}" href="{{ route('backend.user.index') }}"><i class="bi bi-person-gear me-2 text-primary"></i> จัดการผู้ใช้งาน (Users)</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Rbac.Management', 'RBAC Manager'])))
                            <li><a class="dropdown-item d-flex align-items-center {{ request()->is('backend/rbac*') ? 'active' : '' }}" href="{{ route('backend.rbac.index') }}"><i class="bi bi-shield-check me-2 text-success"></i> จัดการบทบาทและสิทธิ์ (RBAC)</a></li>
                            @endif
                            @if($isSuperAdmin)
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item d-flex align-items-center {{ request()->is('backend/settings/access-control*') ? 'active' : '' }}" href="{{ route('backend.settings.access_control') }}"><i class="bi bi-shield-lock-fill me-2 text-warning"></i> ควบคุมล็อกอิน & นโยบายดาวน์โหลด</a></li>
                            <li><a class="dropdown-item d-flex align-items-center {{ request()->is('backend/audit-logs*') ? 'active' : '' }}" href="{{ route('backend.audit_log.index') }}"><i class="bi bi-clock-history me-2 text-info"></i> ประวัติการใช้งานระบบ (Audit Trail)</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
            </ul>
        </div>
    </div>
</nav>
