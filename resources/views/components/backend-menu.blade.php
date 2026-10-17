<nav class="navbar navbar-expand-md shadow-sm border-bottom py-0" style="z-index: 1050;">
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
                    <!-- 1. โครงการ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole('Admin Project') || $currentUser->hasPermission(['RdbProject.View', 'Project', 'MasterData.Manage']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_project*') || request()->is('backend/rdbgroupproject*') || request()->is('backend/rdbprojecttype*') || request()->is('backend/rdbprojectwork*') || request()->is('backend/rdbprojectposition*') || request()->is('backend/rdbstrategic*') || request()->is('backend/rdbyear*') || request()->is('backend/rdbbranch*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-folder2-open"></i> โครงการ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_project.index') }}"><i class="bi bi-list-ul"></i> รายการโครงการ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbyear.index') }}">ปีงบประมาณ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbstrategic.index') }}">ยุทธศาสตร์</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbgroupproject.index') }}">กลุ่มโครงการ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojecttypesgroup.index') }}">กลุ่มประเภททุน</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojecttype.index') }}">ประเภททุน</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojecttypesub.index') }}">ประเภทโครงการทุนย่อย</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectwork.index') }}">ประเภทงานวิจัย</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectposition.index') }}">ตำแหน่งในโครงการ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectfiles.index') }}">ไฟล์แนบ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbbranch.index') }}">สาขาการวิจัย</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 2. ตีพิมพ์ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole('Admin Published') || $currentUser->hasPermission(['RdbPublished.View', 'Published']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_published*') || request()->is('backend/rdbpublishedtype*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-journal-text"></i> ตีพิมพ์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_published.index') }}"><i class="bi bi-file-earmark-text"></i> รายการตีพิมพ์</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbpublishedtype.index') }}">ประเภทผลงาน</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 3. การใช้ประโยชน์ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole('Admin Utilize') || $currentUser->hasPermission(['RdbProjectUtilize.View', 'Utilize']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbprojectutilize*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-rocket-takeoff"></i> การใช้ประโยชน์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectutilize.index') }}"><i class="bi bi-graph-up-arrow"></i> รายการการใช้ฯ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectutilizetype.index') }}">ประเภทการนำไปใช้ฯ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 4. ทรัพย์สินฯ -->
                    @if($isSuperAdmin || ($currentUser && ($currentUser->hasRole('Admin Dip') || $currentUser->hasPermission(['RdbDip.View', 'Dip']))))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_dip*') || request()->is('backend/rdbdiptype*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightbulb"></i> ทรัพย์สินฯ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_dip.index') }}"><i class="bi bi-award"></i> รายการทรัพย์สินฯ</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdiptype.index') }}">ประเภททรัพย์สินฯ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 5. นักวิจัย -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('RdbResearcher.View')))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_researcher*') || request()->is('backend/rdbprefix*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people"></i> นักวิจัย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_researcher.index') }}"><i class="bi bi-person-lines-fill"></i> รายการนักวิจัย</a></li>
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('MasterData.Manage')))
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprefix.index') }}">คำนำหน้าชื่อ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 6. ข่าว/กิจกรรม -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['news', 'Event'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/research_news*') || request()->is('backend/research_conference*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-newspaper"></i> ข่าว/กิจกรรม
                        </a>
                        <ul class="dropdown-menu">
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('news')))
                            <li><a class="dropdown-item" href="{{ route('backend.research_news.index') }}"><i class="bi bi-megaphone"></i> ข่าวทุนวิจัย</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Event', 'MasterData.Manage'])))
                            <li><a class="dropdown-item {{ request()->is('backend/research_conference*') ? 'active' : '' }}" href="{{ route('backend.research_conference.index') }}"><i class="bi bi-calendar-event"></i> ข่าวงานประชุมวิชาการ</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- 7. องค์กร -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Department', 'MasterData.Manage'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbdepartment*') || request()->is('backend/rdbdepmajor*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> องค์กร
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepartment.index') }}">หน่วยงาน/คณะ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepartmenttype.index') }}">ประเภทหน่วยงาน</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepmajor.index') }}">สาขาวิชา</a></li>
                        </ul>
                    </li>
                    @endif

                    <!-- 8. ผู้ดูแลระบบ -->
                    @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['User Manager', 'Rbac.Management', 'RBAC Manager'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/user*') || request()->is('backend/rbac*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock"></i> ผู้ดูแลระบบ
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission('User Manager')))
                            <li><a class="dropdown-item {{ request()->is('backend/user*') ? 'active' : '' }}" href="{{ route('backend.user.index') }}"><i class="bi bi-people-gear me-2"></i> จัดการผู้ใช้งาน (Users)</a></li>
                            @endif
                            @if($isSuperAdmin || ($currentUser && $currentUser->hasPermission(['Rbac.Management', 'RBAC Manager'])))
                            <li><a class="dropdown-item {{ request()->is('backend/rbac*') ? 'active' : '' }}" href="{{ route('backend.rbac.index') }}"><i class="bi bi-shield-check me-2"></i> จัดการบทบาทและสิทธิ์ (RBAC)</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
            </ul>
        </div>
    </div>
</nav>
