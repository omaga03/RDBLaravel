@php
    $currentUser = auth()->user();
    $isSuperAdmin = $currentUser && ($currentUser->hasRole('The Creator') || $currentUser->hasRole('Admin') || $currentUser->hasRole('Admin Research'));
    $canDspaceProject = $isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Project', 'Admin Library']) || $currentUser->hasPermission('DSpace.Export')));
    $canDspacePublished = $isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Published', 'Admin Library']) || $currentUser->hasPermission('DSpace.Export')));
    $canManageResearchers = $isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Researcher', 'Admin Position']) || ($currentUser->hasPermission('RdbResearcher.View') && !$currentUser->hasRole('Researcher') && !$currentUser->researcher_id && !$currentUser->hasRole('Faculty Coordinator'))));
    $myPendingReviewsCount = 0;
    if ($currentUser) {
        try {
            $myPendingReviewsCount = \App\Models\RdbReviewAssignment::where(function ($q) use ($currentUser) {
                $q->where('reviewer_id', $currentUser->id);
                if (!empty($currentUser->researcher_id)) {
                    $q->orWhere('researcher_id', $currentUser->researcher_id);
                }
            })->whereIn('status', ['pending', 'accepted'])->count();
        } catch (\Throwable $e) {
            $myPendingReviewsCount = 0;
        }
    }

    // Active section states for auto-expanding the current module group & master data sub-groups
    $isProjectMasterActive = request()->is('backend/rdbgroupproject*') || request()->is('backend/rdbprojecttypesgroup*') || request()->is('backend/rdbprojecttypesmaster*') || request()->is('backend/rdbprojecttype*') || request()->is('backend/rdbprojecttypesub*') || request()->is('backend/rdbprojectposition*') || request()->is('backend/rdbprojectpersonnel*') || request()->is('backend/rdbyear*') || (request()->is('backend/rdbbranch*') && !request()->is('backend/rdbpublished*'));
    $isProjectActive = request()->is('backend/rdb_project*') || request()->is('backend/project-reviews*') || request()->routeIs('backend.project_review.*') || $isProjectMasterActive;

    $isPublishedMasterActive = request()->is('backend/rdbpublishedtype*') || request()->is('backend/rdbpublishedbranchper*') || request()->is('backend/rdbpublishedpersonnel*');
    $isPublishedActive = request()->is('backend/rdb_published*') || $isPublishedMasterActive;

    $isUtilizeMasterActive = request()->is('backend/rdbprojectutilizetype*') || request()->is('backend/rdbchangwat*');
    $isUtilizeActive = request()->is('backend/rdbprojectutilize*') || $isUtilizeMasterActive;

    $isDipMasterActive = request()->is('backend/rdbdiptype*');
    $isDipActive = request()->is('backend/rdb_dip*') || $isDipMasterActive;

    $isResearcherMasterActive = request()->is('backend/rdbprefix*');
    $isResearcherActive = request()->is('backend/rdb_researcher*') || $isResearcherMasterActive;

    $isNewsActive = request()->is('backend/research_news*') || request()->is('backend/research_conference*');
    $isDeptActive = request()->is('backend/rdbdepartment*');
    $isAdminActive = request()->is('backend/user*') || request()->is('backend/rbac*') || request()->is('backend/settings*') || request()->is('backend/audit-logs*') || request()->is('backend/api-management*');
@endphp

<!-- Mobile Backdrop for Offcanvas Sidebar (< 992px) -->
<div class="rdb-sidebar-backdrop d-lg-none" id="rdbSidebarBackdrop"></div>

<aside class="rdb-sidebar border-end" id="rdbBackendSidebar" aria-label="เมนูจัดการระบบหลังบ้าน">
    <!-- Sidebar Top Header -->
    <div class="rdb-sidebar-header d-flex align-items-center justify-content-between px-3 py-2.5 border-bottom">
        <div class="d-flex align-items-center gap-2 overflow-hidden sidebar-brand-wrap">
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2 py-1 rounded-2 fw-bold" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                <i class="bi bi-grid-1x2-fill me-1"></i>BACKEND
            </span>
            <span class="sidebar-text fw-semibold text-body-secondary small text-truncate">เมนูจัดการระบบ</span>
        </div>
        <!-- Desktop Collapse/Expand Pin Button -->
        <button type="button" class="btn btn-sm btn-link text-body-secondary p-1 d-none d-lg-inline-flex align-items-center justify-content-center rounded-2 sidebar-collapse-btn" id="rdbSidebarCollapseBtn" title="ย่อ/ขยายแถบเมนูด้านข้าง">
            <i class="bi bi-layout-sidebar-inset" id="rdbSidebarCollapseIcon" style="font-size: 1rem;"></i>
        </button>
        <!-- Mobile Close Drawer Button -->
        <button type="button" class="btn btn-sm btn-link text-body-secondary p-1 d-lg-none" id="rdbSidebarCloseBtn" aria-label="ปิดเมนู">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="rdb-sidebar-body px-2.5 py-2" id="rdbSidebarAccordion">
        @if($currentUser && !$currentUser->canAccessFullBackend())
            <!-- Onboarding Restricted State -->
            <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle mb-3">
                <div class="d-flex align-items-center gap-2 fw-bold text-warning-emphasis mb-1.5 small">
                    <i class="bi bi-shield-exclamation fs-6"></i>
                    <span class="sidebar-text">ยืนยันตัวตนนักวิจัย</span>
                </div>
                <p class="text-body-secondary mb-2 sidebar-text" style="font-size: 0.78rem; line-height: 1.45;">
                    กรุณาเลือกสังกัด/คณะ และกรอกข้อมูลส่วนตัวเพื่อขอรับการอนุมัติสิทธิ์นักวิจัยจากผู้ดูแลระบบ
                </p>
                <div class="sidebar-text">
                    @if($currentUser->onboarding_status === 'pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle w-100 py-1.5">
                            <i class="bi bi-hourglass-split me-1"></i> รอ Admin ปลดล็อก
                        </span>
                    @elseif($currentUser->onboarding_status === 'rejected')
                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle w-100 py-1.5">
                            <i class="bi bi-x-circle me-1"></i> ไม่ผ่านอนุมัติ (แก้ไข)
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle w-100 py-1.5">
                            <i class="bi bi-pencil-square me-1"></i> ยังไม่ได้ส่งข้อมูล
                        </span>
                    @endif
                </div>
            </div>
            <ul class="nav flex-column rdb-sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link rdb-side-link {{ request()->routeIs('backend.onboarding.*') ? 'active' : '' }}" href="{{ route('backend.onboarding.index') }}" title="ยืนยันตัวตนนักวิจัย">
                        <i class="bi bi-person-check-fill text-warning rdb-side-icon"></i>
                        <span class="sidebar-text">แบบฟอร์มยืนยันตัวตน</span>
                    </a>
                </li>
            </ul>
        @else
            <!-- 1. หมวดภาพรวมและงานด่วน (Overview) -->
            <div class="rdb-sidebar-section-title">
                <span class="sidebar-text">ภาพรวม &amp; งานด่วน</span>
            </div>
            <ul class="nav flex-column rdb-sidebar-nav mb-2">
                <li class="nav-item">
                    <a class="nav-link rdb-side-link {{ request()->routeIs('backend.site.index') || request()->is('backend') ? 'active' : '' }}" href="{{ route('backend.site.index') }}" title="แดชบอร์ดหลังบ้าน">
                        <i class="bi bi-speedometer2 text-primary rdb-side-icon"></i>
                        <span class="sidebar-text">แดชบอร์ดภาพรวม</span>
                    </a>
                </li>

                @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Project', 'Admin Published', 'Admin Dip', 'Admin Utilize', 'Faculty Coordinator', 'Sub Admin']) || $currentUser->hasPermission(['Project', 'Published', 'Dip', 'Utilize']))))
                <li class="nav-item">
                    <a class="nav-link rdb-side-link {{ request()->is('backend/workflow*') || request()->routeIs('backend.workflow.*') ? 'active' : '' }}" href="{{ route('backend.workflow.queue') }}" title="คิวตรวจรับผลงาน">
                        <i class="bi bi-inbox-fill text-warning rdb-side-icon"></i>
                        <span class="sidebar-text">คิวตรวจรับผลงาน</span>
                    </a>
                </li>
                @endif

                @if($myPendingReviewsCount > 0)
                <li class="nav-item">
                    <a class="nav-link rdb-side-link d-flex align-items-center justify-content-between {{ request()->routeIs('backend.project_review.my_reviews') ? 'active' : '' }}" href="{{ route('backend.project_review.my_reviews') }}" title="งานประเมินเล่มที่ได้รับมอบหมาย">
                        <span class="d-flex align-items-center overflow-hidden">
                            <i class="bi bi-clipboard2-check-fill text-danger rdb-side-icon"></i>
                            <span class="sidebar-text text-truncate">งานประเมินเล่มของฉัน</span>
                        </span>
                        <span class="badge rounded-pill bg-danger ms-1 tabular-nums sidebar-badge">{{ $myPendingReviewsCount }}</span>
                    </a>
                </li>
                @endif
            </ul>

            <!-- 2. หมวดจัดการผลงานวิจัย (Academic Modules) -->
            <div class="rdb-sidebar-section-title">
                <span class="sidebar-text">จัดการผลงานวิจัย</span>
            </div>
            <ul class="nav flex-column rdb-sidebar-nav mb-2">
                <!-- 2.1 โครงการวิจัย -->
                @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Project', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbProject.View', 'Project', 'MasterData.Manage']))))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isProjectActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuProject"
                       role="button"
                       aria-expanded="{{ $isProjectActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuProject"
                       title="โครงการวิจัย">
                        <i class="bi bi-folder2-open text-primary rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">โครงการวิจัย</span>
                        @if($myPendingReviewsCount > 0)
                            <span class="badge rounded-pill bg-danger me-1 tabular-nums sidebar-text" style="font-size: 0.65rem;">{{ $myPendingReviewsCount }}</span>
                        @endif
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isProjectActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuProject" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <!-- โซนที่ 1: ข้อมูลโครงการ -->
                            <li class="rdb-sub-header"><i class="bi bi-folder-check text-primary me-1.5"></i> ข้อมูลโครงการ</li>
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_project.index') && !request('workflow_status') && !request('trashed') ? 'active' : '' }}" href="{{ route('backend.rdb_project.index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-list-ul me-1.5 text-primary"></i>รายการโครงการทั้งหมด
                                </a>
                            </li>
                            <li>
                                <a class="nav-link rdb-sub-link text-warning-emphasis {{ request()->routeIs('backend.rdb_project.index') && request('workflow_status') === 'unapproved' ? 'active' : '' }}" href="{{ route('backend.rdb_project.index', ['workflow_status' => 'unapproved']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-clock-history me-1.5 text-warning"></i>ฉบับร่าง / รออนุมัติ
                                </a>
                            </li>
                            @if($isSuperAdmin)
                            <li>
                                <a class="nav-link rdb-sub-link text-danger-emphasis {{ request()->routeIs('backend.rdb_project.index') && request('trashed') === 'only' ? 'active' : '' }}" href="{{ route('backend.rdb_project.index', ['trashed' => 'only']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-trash3 me-1.5 text-danger"></i>ถังขยะและกู้คืนข้อมูล
                                </a>
                            </li>
                            @endif

                            <!-- โซนที่ 2: ระบบประเมินเล่มรายงาน -->
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-clipboard2-check text-success me-1.5"></i> ประเมินเล่มรายงาน</li>
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.project_review.index') ? 'active' : '' }}" href="{{ route('backend.project_review.index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-clipboard2-check me-1.5 text-primary"></i>ระบบประเมินเล่มรายงานวิจัย
                                </a>
                            </li>
                            <li>
                                <a class="nav-link rdb-sub-link d-flex align-items-center justify-content-between {{ request()->routeIs('backend.project_review.my_reviews') ? 'active' : '' }}" href="{{ route('backend.project_review.my_reviews') }}">
                                    <span class="text-truncate"><span class="rdb-sub-dash">-</span><i class="bi bi-person-check me-1.5 text-success"></i>งานประเมินที่ได้รับมอบหมาย</span>
                                    @if($myPendingReviewsCount > 0)
                                        <span class="badge rounded-pill bg-danger ms-1 tabular-nums">{{ $myPendingReviewsCount }}</span>
                                    @endif
                                </a>
                            </li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasRole('Admin Project')))
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.project_review.templates*') ? 'active' : '' }}" href="{{ route('backend.project_review.templates') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-ui-checks-grid me-1.5 text-info"></i>จัดการแบบฟอร์มเกณฑ์ประเมิน
                                </a>
                            </li>
                            @endif

                            <!-- โซนที่ 3: การส่งออกข้อมูล -->
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-download text-info me-1.5"></i> การส่งออกข้อมูล</li>
                            @if($canDspaceProject)
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_project.dspace*') ? 'active' : '' }}" href="{{ route('backend.rdb_project.dspace_index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-box-arrow-up-right me-1.5 text-info"></i>ส่งออก DSpace (คลังปัญญา)
                                </a>
                            </li>
                            @endif
                            <li>
                                <a class="nav-link rdb-sub-link" href="{{ route('backend.rdb_project.export') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-spreadsheet me-1.5 text-success"></i>ส่งออกข้อมูลโครงการ (CSV)
                                </a>
                            </li>

                            <!-- โซนที่ 4: ข้อมูลพื้นฐานโครงการ (พับเก็บย่อยได้) -->
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li class="rdb-sub-divider"></li>
                            <li>
                                <a class="rdb-sub-collapse-btn {{ $isProjectMasterActive ? '' : 'collapsed' }}"
                                   data-bs-toggle="collapse"
                                   href="#sidebarMasterProject"
                                   role="button"
                                   aria-expanded="{{ $isProjectMasterActive ? 'true' : 'false' }}"
                                   aria-controls="sidebarMasterProject">
                                    <span><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i>ตั้งค่าข้อมูลพื้นฐาน (9)</span>
                                    <i class="bi bi-chevron-down rdb-side-chevron"></i>
                                </a>
                                <div class="collapse {{ $isProjectMasterActive ? 'show' : '' }} pt-1" id="sidebarMasterProject">
                                    <ul class="nav flex-column">
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbyear*') ? 'active' : '' }}" href="{{ route('backend.rdbyear.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-calendar3 me-1.5 text-primary"></i>ปีงบประมาณ</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbbranch*') ? 'active' : '' }}" href="{{ route('backend.rdbbranch.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-diagram-3 me-1.5 text-info"></i>สาขาการวิจัย</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbgroupproject*') ? 'active' : '' }}" href="{{ route('backend.rdbgroupproject.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-collection me-1.5 text-success"></i>กลุ่มโครงการ / แผนงาน</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojecttypesmaster*') ? 'active' : '' }}" href="{{ route('backend.rdbprojecttypesmaster.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-tag-fill me-1.5 text-primary"></i>หมวดหมู่ทุนหลัก (Master)</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojecttypesgroup*') ? 'active' : '' }}" href="{{ route('backend.rdbprojecttypesgroup.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-tags me-1.5 text-secondary"></i>กลุ่มประเภททุน</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojecttype') || request()->is('backend/rdbprojecttype/*') ? 'active' : '' }}" href="{{ route('backend.rdbprojecttype.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-bookmark-star me-1.5 text-warning"></i>ประเภททุน</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojecttypesub*') ? 'active' : '' }}" href="{{ route('backend.rdbprojecttypesub.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-bookmark-dash me-1.5 text-secondary"></i>ประเภทโครงการทุนย่อย</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojectposition*') ? 'active' : '' }}" href="{{ route('backend.rdbprojectposition.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-person-badge me-1.5 text-success"></i>ตำแหน่งในโครงการ</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojectpersonnel*') ? 'active' : '' }}" href="{{ route('backend.rdbprojectpersonnel.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-people me-1.5 text-info"></i>จำนวนคนในคณะ รายปี</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- 2.2 ผลงานตีพิมพ์ -->
                @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Published', 'Published', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbPublished.View', 'Published']))))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isPublishedActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuPublished"
                       role="button"
                       aria-expanded="{{ $isPublishedActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuPublished"
                       title="ผลงานตีพิมพ์">
                        <i class="bi bi-journal-text text-info rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">ผลงานตีพิมพ์</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isPublishedActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuPublished" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <!-- โซนที่ 1: ข้อมูลตีพิมพ์ -->
                            <li class="rdb-sub-header"><i class="bi bi-journal-text text-info me-1.5"></i> ข้อมูลผลงานตีพิมพ์</li>
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_published.index') && !request('workflow_status') && !request('trashed') ? 'active' : '' }}" href="{{ route('backend.rdb_published.index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-text me-1.5 text-info"></i>รายการตีพิมพ์ทั้งหมด
                                </a>
                            </li>
                            <li>
                                <a class="nav-link rdb-sub-link text-warning-emphasis {{ request()->routeIs('backend.rdb_published.index') && request('workflow_status') === 'unapproved' ? 'active' : '' }}" href="{{ route('backend.rdb_published.index', ['workflow_status' => 'unapproved']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-clock-history me-1.5 text-warning"></i>ฉบับร่าง / รออนุมัติ
                                </a>
                            </li>
                            @if($isSuperAdmin)
                            <li>
                                <a class="nav-link rdb-sub-link text-danger-emphasis {{ request()->routeIs('backend.rdb_published.index') && request('trashed') === 'only' ? 'active' : '' }}" href="{{ route('backend.rdb_published.index', ['trashed' => 'only']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-trash3 me-1.5 text-danger"></i>ถังขยะและกู้คืนข้อมูล
                                </a>
                            </li>
                            @endif

                            <!-- โซนที่ 2: การส่งออกข้อมูล -->
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-download text-info me-1.5"></i> การส่งออกข้อมูล</li>
                            @if($canDspacePublished)
                            <li><a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_published.dspace_index') ? 'active' : '' }}" href="{{ route('backend.rdb_published.dspace_index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-box-arrow-up-right me-1.5 text-info"></i>ส่งออก DSpace (คลังปัญญา)</a></li>
                            <li><a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_published.dspace_check') ? 'active' : '' }}" href="{{ route('backend.rdb_published.dspace_check') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-check me-1.5 text-primary"></i>ตรวจสอบจำนวนหน้า DSpace</a></li>
                            @endif
                            <li><a class="nav-link rdb-sub-link" href="{{ route('backend.rdb_published.export') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-spreadsheet me-1.5 text-success"></i>ส่งออกข้อมูลตีพิมพ์ (CSV)</a></li>

                            <!-- โซนที่ 3: ข้อมูลพื้นฐานตีพิมพ์ (พับเก็บย่อยได้) -->
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li class="rdb-sub-divider"></li>
                            <li>
                                <a class="rdb-sub-collapse-btn {{ $isPublishedMasterActive ? '' : 'collapsed' }}"
                                   data-bs-toggle="collapse"
                                   href="#sidebarMasterPublished"
                                   role="button"
                                   aria-expanded="{{ $isPublishedMasterActive ? 'true' : 'false' }}"
                                   aria-controls="sidebarMasterPublished">
                                    <span><i class="bi bi-gear-wide-connected me-1.5 text-secondary"></i>ตั้งค่าข้อมูลพื้นฐาน (4)</span>
                                    <i class="bi bi-chevron-down rdb-side-chevron"></i>
                                </a>
                                <div class="collapse {{ $isPublishedMasterActive ? 'show' : '' }} pt-1" id="sidebarMasterPublished">
                                    <ul class="nav flex-column">
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbpublishedtype*') ? 'active' : '' }}" href="{{ route('backend.rdbpublishedtype.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-journal-bookmark me-1.5 text-primary"></i>ประเภทผลงาน</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbbranch*') ? 'active' : '' }}" href="{{ route('backend.rdbbranch.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-diagram-3 me-1.5 text-info"></i>สาขางานตีพิมพ์</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbpublishedbranchper*') ? 'active' : '' }}" href="{{ route('backend.rdbpublishedbranchper.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-pie-chart me-1.5 text-success"></i>สัดส่วนสาขาการวิจัย รายปี</a></li>
                                        <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbpublishedpersonnel*') ? 'active' : '' }}" href="{{ route('backend.rdbpublishedpersonnel.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-people me-1.5 text-secondary"></i>จำนวนคนในคณะ รายปี</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- 2.3 การนำไปใช้ประโยชน์ -->
                @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Utilize', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbProjectUtilize.View', 'Utilize']))))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isUtilizeActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuUtilize"
                       role="button"
                       aria-expanded="{{ $isUtilizeActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuUtilize"
                       title="การนำไปใช้ประโยชน์">
                        <i class="bi bi-rocket-takeoff text-danger rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">การใช้ประโยชน์</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isUtilizeActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuUtilize" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <li class="rdb-sub-header"><i class="bi bi-rocket-takeoff text-danger me-1.5"></i> ข้อมูลการใช้ประโยชน์</li>
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdbprojectutilize.index') && !request('workflow_status') && !request('trashed') ? 'active' : '' }}" href="{{ route('backend.rdbprojectutilize.index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-graph-up-arrow me-1.5 text-danger"></i>รายการการใช้ฯ ทั้งหมด
                                </a>
                            </li>
                            <li>
                                <a class="nav-link rdb-sub-link text-warning-emphasis {{ request()->routeIs('backend.rdbprojectutilize.index') && request('workflow_status') === 'unapproved' ? 'active' : '' }}" href="{{ route('backend.rdbprojectutilize.index', ['workflow_status' => 'unapproved']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-clock-history me-1.5 text-warning"></i>ฉบับร่าง / รออนุมัติ
                                </a>
                            </li>
                            @if($isSuperAdmin)
                            <li>
                                <a class="nav-link rdb-sub-link text-danger-emphasis {{ request()->routeIs('backend.rdbprojectutilize.index') && request('trashed') === 'only' ? 'active' : '' }}" href="{{ route('backend.rdbprojectutilize.index', ['trashed' => 'only']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-trash3 me-1.5 text-danger"></i>ถังขยะและกู้คืนข้อมูล
                                </a>
                            </li>
                            @endif

                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-download text-info me-1.5"></i> การส่งออกข้อมูล</li>
                            <li><a class="nav-link rdb-sub-link" href="{{ route('backend.rdbprojectutilize.export') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-spreadsheet me-1.5 text-success"></i>ส่งออกข้อมูลการใช้ประโยชน์ (CSV)</a></li>

                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-gear-wide-connected text-secondary me-1.5"></i> ข้อมูลพื้นฐาน</li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprojectutilizetype*') ? 'active' : '' }}" href="{{ route('backend.rdbprojectutilizetype.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-tag-fill me-1.5 text-danger"></i>ประเภทการนำไปใช้ฯ</a></li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbchangwat*') ? 'active' : '' }}" href="{{ route('backend.rdbchangwat.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-geo-alt-fill me-1.5 text-danger"></i>พื้นที่และพิกัด (ต. อ. จ.)</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- 2.4 ทรัพย์สินทางปัญญา -->
                @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole(['Admin Dip', 'Researcher', 'Faculty Coordinator']) || $currentUser->researcher_id || $currentUser->hasPermission(['RdbDip.View', 'Dip']))))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isDipActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuDip"
                       role="button"
                       aria-expanded="{{ $isDipActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuDip"
                       title="ทรัพย์สินทางปัญญา">
                        <i class="bi bi-award text-warning rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">ทรัพย์สินทางปัญญา</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isDipActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuDip" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <li class="rdb-sub-header"><i class="bi bi-award text-warning me-1.5"></i> ข้อมูลทรัพย์สินฯ</li>
                            <li>
                                <a class="nav-link rdb-sub-link {{ request()->routeIs('backend.rdb_dip.index') && !request('workflow_status') && !request('trashed') ? 'active' : '' }}" href="{{ route('backend.rdb_dip.index') }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-award me-1.5 text-warning"></i>รายการทรัพย์สินฯ ทั้งหมด
                                </a>
                            </li>
                            <li>
                                <a class="nav-link rdb-sub-link text-warning-emphasis {{ request()->routeIs('backend.rdb_dip.index') && request('workflow_status') === 'unapproved' ? 'active' : '' }}" href="{{ route('backend.rdb_dip.index', ['workflow_status' => 'unapproved']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-clock-history me-1.5 text-warning"></i>ฉบับร่าง / รออนุมัติ
                                </a>
                            </li>
                            @if($isSuperAdmin)
                            <li>
                                <a class="nav-link rdb-sub-link text-danger-emphasis {{ request()->routeIs('backend.rdb_dip.index') && request('trashed') === 'only' ? 'active' : '' }}" href="{{ route('backend.rdb_dip.index', ['trashed' => 'only']) }}">
                                    <span class="rdb-sub-dash">-</span><i class="bi bi-trash3 me-1.5 text-danger"></i>ถังขยะและกู้คืนข้อมูล
                                </a>
                            </li>
                            @endif

                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-download text-info me-1.5"></i> การส่งออกข้อมูล</li>
                            <li><a class="nav-link rdb-sub-link" href="{{ route('backend.rdb_dip.export') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-spreadsheet me-1.5 text-success"></i>ส่งออกข้อมูลทรัพย์สินฯ (CSV)</a></li>

                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-gear-wide-connected text-secondary me-1.5"></i> ข้อมูลพื้นฐาน</li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbdiptype*') ? 'active' : '' }}" href="{{ route('backend.rdbdiptype.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-patch-check-fill me-1.5 text-warning"></i>ประเภททรัพย์สินฯ</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif
            </ul>

            <!-- 3. หมวดบุคลากรและเครือข่าย (People & Organizations) -->
            @if($canManageResearchers || $isSuperAdmin || ($currentUser && $currentUser->hasPermission(['news', 'Event', 'Department', 'MasterData.Manage'])))
            <div class="rdb-sidebar-section-title">
                <span class="sidebar-text">บุคลากร &amp; หน่วยงาน</span>
            </div>
            <ul class="nav flex-column rdb-sidebar-nav mb-2">
                <!-- 3.1 นักวิจัย -->
                @if($canManageResearchers)
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isResearcherActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuResearcher"
                       role="button"
                       aria-expanded="{{ $isResearcherActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuResearcher"
                       title="ทำเนียบนักวิจัย">
                        <i class="bi bi-people text-success rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">นักวิจัย</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isResearcherActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuResearcher" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdb_researcher*') ? 'active' : '' }}" href="{{ route('backend.rdb_researcher.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-person-lines-fill me-1.5 text-success"></i>รายการนักวิจัยทั้งหมด</a></li>
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-download text-info me-1.5"></i> การส่งออกข้อมูล</li>
                            <li><a class="nav-link rdb-sub-link" href="{{ route('backend.rdb_researcher.export') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-file-earmark-spreadsheet me-1.5 text-success"></i>ส่งออกข้อมูลนักวิจัย (CSV)</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-gear-wide-connected text-secondary me-1.5"></i> ข้อมูลพื้นฐาน</li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbprefix*') ? 'active' : '' }}" href="{{ route('backend.rdbprefix.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-card-text me-1.5 text-info"></i>คำนำหน้าชื่อ</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- 3.2 ข่าว/กิจกรรม -->
                @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['news', 'Event'])))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isNewsActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuNews"
                       role="button"
                       aria-expanded="{{ $isNewsActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuNews"
                       title="ข่าวประชาสัมพันธ์และงานประชุม">
                        <i class="bi bi-newspaper text-info rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">ข่าว/กิจกรรม</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isNewsActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuNews" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('news')))
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/research_news*') ? 'active' : '' }}" href="{{ route('backend.research_news.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-megaphone-fill me-1.5 text-warning"></i>ข่าวทุนวิจัย</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Event', 'MasterData.Manage'])))
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/research_conference*') ? 'active' : '' }}" href="{{ route('backend.research_conference.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-calendar-event-fill me-1.5 text-primary"></i>ข่าวงานประชุมวิชาการ</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                <!-- 3.3 องค์กร -->
                @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Department', 'MasterData.Manage'])))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isDeptActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuDept"
                       role="button"
                       aria-expanded="{{ $isDeptActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuDept"
                       title="หน่วยงานและคณะ">
                        <i class="bi bi-building text-secondary rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">องค์กร / คณะ</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isDeptActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuDept" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbdepartment') || request()->is('backend/rdbdepartment/*') ? 'active' : '' }}" href="{{ route('backend.rdbdepartment.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-buildings-fill me-1.5 text-primary"></i>หน่วยงาน/คณะ</a></li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rdbdepartmenttype*') ? 'active' : '' }}" href="{{ route('backend.rdbdepartmenttype.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-diagram-2-fill me-1.5 text-secondary"></i>ประเภทหน่วยงาน</a></li>
                        </ul>
                    </div>
                </li>
                @endif
            </ul>
            @endif

            <!-- 4. หมวดผู้ดูแลระบบและความปลอดภัย (System Administration) -->
            @if($isSuperAdmin || ($currentUser && ($currentUser->hasPermission(['User Manager', 'Rbac.Management', 'RBAC Manager']) || $currentUser->tokens()->exists())))
            <div class="rdb-sidebar-section-title">
                <span class="sidebar-text">การดูแลระบบ</span>
            </div>
            <ul class="nav flex-column rdb-sidebar-nav mb-2">
                @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['User Manager', 'Rbac.Management', 'RBAC Manager'])))
                <li class="nav-item rdb-side-group">
                    <a class="nav-link rdb-side-link rdb-side-toggle {{ $isAdminActive ? 'active-parent' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarMenuAdmin"
                       role="button"
                       aria-expanded="{{ $isAdminActive ? 'true' : 'false' }}"
                       aria-controls="sidebarMenuAdmin"
                       title="ผู้ดูแลระบบและความปลอดภัย">
                        <i class="bi bi-shield-lock text-danger rdb-side-icon"></i>
                        <span class="sidebar-text flex-grow-1">ผู้ดูแลระบบ</span>
                        <i class="bi bi-chevron-down rdb-side-chevron sidebar-text"></i>
                    </a>
                    <div class="collapse {{ $isAdminActive ? 'show' : '' }} rdb-side-submenu" id="sidebarMenuAdmin" data-bs-parent="#rdbSidebarAccordion">
                        <ul class="nav flex-column">
                            <li class="rdb-sub-header"><i class="bi bi-person-gear text-primary me-1.5"></i> บริหารจัดการผู้ใช้</li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('User Manager')))
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/user*') ? 'active' : '' }}" href="{{ route('backend.user.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-people-fill me-1.5 text-primary"></i>จัดการผู้ใช้งาน (Users)</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Rbac.Management', 'RBAC Manager'])))
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/rbac*') ? 'active' : '' }}" href="{{ route('backend.rbac.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-shield-check me-1.5 text-success"></i>จัดการบทบาทและสิทธิ์ (RBAC)</a></li>
                            @endif

                            @if($isSuperAdmin)
                            <li class="rdb-sub-divider"></li>
                            <li class="rdb-sub-header"><i class="bi bi-shield-lock-fill text-warning me-1.5"></i> ความปลอดภัยและระบบ</li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/api-management*') ? 'active' : '' }}" href="{{ route('backend.api_management.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-braces-asterisk me-1.5 text-primary"></i>จัดการ API &amp; Bearer Token</a></li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/settings/access-control*') ? 'active' : '' }}" href="{{ route('backend.settings.access_control') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-shield-lock-fill me-1.5 text-warning"></i>ควบคุมล็อกอิน &amp; นโยบายดาวน์โหลด</a></li>
                            <li><a class="nav-link rdb-sub-link {{ request()->is('backend/audit-logs*') ? 'active' : '' }}" href="{{ route('backend.audit_log.index') }}"><span class="rdb-sub-dash">-</span><i class="bi bi-clock-history me-1.5 text-info"></i>ประวัติการใช้งานระบบ (Audit Trail)</a></li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                @if($isSuperAdmin || ($currentUser && $currentUser->tokens()->exists()))
                <li class="nav-item">
                    <a class="nav-link rdb-side-link {{ request()->is('backend/api-management*') || request()->routeIs('backend.api_management.*') ? 'active' : '' }}" href="{{ route('backend.api_management.index') }}" title="ศูนย์บริการ API และคู่มือการเชื่อมต่อ">
                        <i class="bi bi-braces-asterisk text-primary rdb-side-icon"></i>
                        <span class="sidebar-text">บริการ API</span>
                    </a>
                </li>
                @endif
            </ul>
            @endif
        @endif
    </div>

    <!-- Sidebar Sticky Bottom Footer -->
    <div class="rdb-sidebar-footer border-top p-2.5 mt-auto">
        <a href="{{ route('home') }}" target="_blank" class="nav-link rdb-side-link py-1.5 text-body-secondary mb-0" title="เปิดหน้าเว็บไซต์ส่วนสาธารณะ (แท็บใหม่)">
            <i class="bi bi-box-arrow-up-right text-info rdb-side-icon"></i>
            <span class="sidebar-text small">ไปหน้าบ้าน (Public Portal)</span>
        </a>
    </div>
</aside>
