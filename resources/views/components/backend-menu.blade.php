<nav class="navbar navbar-expand-md shadow-sm border-bottom py-0" style="z-index: 1050;">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#backendMenuContent" aria-controls="backendMenuContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="font-size: 0.8rem;"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="backendMenuContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!-- 1. โครงการ -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_project*') || request()->is('backend/rdbgroupproject*') || request()->is('backend/rdbprojecttype*') || request()->is('backend/rdbprojectwork*') || request()->is('backend/rdbprojectposition*') || request()->is('backend/rdbstrategic*') || request()->is('backend/rdbyear*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-folder2-open"></i> โครงการ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_project.index') }}"><i class="bi bi-list-ul"></i> รายการโครงการ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbyear.index') }}">ปีงบประมาณ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbstrategic.index') }}">ยุทธศาสตร์</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbgroupproject.index') }}">กลุ่มโครงการ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojecttype.index') }}">ประเภททุน</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectwork.index') }}">งานวิจัย</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectposition.index') }}">ตำแหน่ง</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectfiles.index') }}">ประเภทไฟล์แนบ</a></li>
                        </ul>
                    </li>

                    <!-- 2. ตีพิมพ์ -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_published*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-journal-text"></i> ตีพิมพ์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_published.index') }}"><i class="bi bi-file-earmark-text"></i> รายการตีพิมพ์</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item disabled" href="#">ประเภทผลงาน</a></li>
                            <li><a class="dropdown-item disabled" href="#">สถานะ</a></li>
                        </ul>
                    </li>

                    <!-- 3. การใช้ประโยชน์ -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbprojectutilize*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-rocket-takeoff"></i> การใช้ประโยชน์
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprojectutilize.index') }}"><i class="bi bi-graph-up-arrow"></i> รายการการใช้ฯ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item disabled" href="#">ประเภท</a></li>
                        </ul>
                    </li>

                    <!-- 4. ทรัพย์สินฯ -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_dip*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-lightbulb"></i> ทรัพย์สินฯ
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_dip.index') }}"><i class="bi bi-award"></i> รายการทรัพย์สินฯ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item disabled" href="#">ประเภท</a></li>
                            <li><a class="dropdown-item disabled" href="#">สถานะ</a></li>
                        </ul>
                    </li>

                    <!-- 5. นักวิจัย -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdb_researcher*') || request()->is('backend/rdbprefix*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people"></i> นักวิจัย
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdb_researcher.index') }}"><i class="bi bi-person-lines-fill"></i> รายการนักวิจัย</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><div class="dropdown-header text-uppercase small fw-bold">ข้อมูลพื้นฐาน</div></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbprefix.index') }}">คำนำหน้าชื่อ</a></li>
                        </ul>
                    </li>

                    <!-- 6. ข่าว/กิจกรรม -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/research_news*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-newspaper"></i> ข่าว/กิจกรรม
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.research_news.index') }}"><i class="bi bi-megaphone"></i> ข่าวประชาสัมพันธ์</a></li>
                            <li><a class="dropdown-item disabled" href="#"><i class="bi bi-calendar-event"></i> ข่าวการประชุม/อบรม</a></li>
                        </ul>
                    </li>

                    <!-- 7. องค์กร -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->is('backend/rdbdepartment*') || request()->is('backend/rdbdepmajor*') || request()->is('backend/rdbbranch*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-building"></i> องค์กร
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepartment.index') }}">หน่วยงาน/คณะ</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepartmenttype.index') }}">ประเภทหน่วยงาน</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbdepmajor.index') }}">สาขาวิชา</a></li>
                            <li><a class="dropdown-item" href="{{ route('backend.rdbbranch.index') }}">สาขาการวิจัย</a></li>
                        </ul>
                    </li>
            </ul>
        </div>
    </div>
</nav>
