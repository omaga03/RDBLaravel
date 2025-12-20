<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        /* Custom styles to ensure readability in both modes */
        [data-bs-theme="dark"] body {
            background-color: #212529;
            color: #f8f9fa;
        }
        /* Dark Mode Scrollbar */
        [data-bs-theme="dark"] ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-track {
            background: #212529; 
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background-color: #495057;
            border-radius: 10px;
            border: 3px solid #212529;
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-corner {
            background: #212529;
        }

        [data-bs-theme="light"] body {
            background-color: #f8f9fa;
            color: #212529;
        }
        .navbar-brand {
            font-weight: bold;
        }

        /* --- Global Custom Styles for Dark Mode --- */
        
        /* 1. Dark Mode Tabs */
        [data-bs-theme="dark"] .nav-tabs .nav-link {
            color: #0d6efd; /* Default Blue for inactive */
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link:hover {
            border-color: #495057 #495057 #dee2e6;
        }
        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: #fff !important;
            background-color: #343a40 !important;
            border-color: #343a40 #343a40 #fff !important;
        }

        /* 2. Dark Mode Tables */
        /* Override .table-light to be dark in dark mode */
        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] .table .thead-light th {
            background-color: #343a40 !important;
            color: #fff !important;
            border-color: #495057;
        }
        /* Ensure standard thead is transparent or matches theme */
        [data-bs-theme="dark"] .table thead th {
            border-bottom-color: #495057;
        }
        /* Fix card header text in dark mode if needed */
        [data-bs-theme="dark"] .card-header {
            border-bottom-color: #495057;
        }
    </style>
    <!-- IMask CDN -->
    <script src="https://unpkg.com/imask"></script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm border-bottom">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Sidebar Toggle Button Removed from Navbar -->

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar (Row 1) -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(request()->is('backend*'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('backend.site.index') ? 'active fw-bold' : '' }}" href="{{ route('backend.site.index') }}"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar (Row 1) -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                        <!-- Theme Toggle -->
                        <li class="nav-item me-2">
                            <button type="button" class="btn btn-link nav-link" id="theme-toggle" title="Toggle theme">
                                <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                            </button>
                        </li>

                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->username ?? Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Row 2: Backend Menu Items -->
        @auth
            @if(request()->is('backend*'))
            <nav class="navbar navbar-expand-md shadow-sm border-bottom py-0" style="z-index: 1050;">
                <div class="container">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#backendMenuContent" aria-controls="backendMenuContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon" style="font-size: 0.8rem;"></span>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="backendMenuContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
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

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('backend/research_news*') ? 'active fw-bold' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-newspaper"></i> ข่าว/กิจกรรม
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('backend.research_news.index') }}"><i class="bi bi-megaphone"></i> ข่าวประชาสัมพันธ์</a></li>
                                        <li><a class="dropdown-item disabled" href="#"><i class="bi bi-calendar-event"></i> ข่าวการประชุม/อบรม</a></li>
                                    </ul>
                                </li>

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
            @endif
        @endauth

        <main class="py-4">
            <div class="container-fluid">
                <div class="row">
                    @if(!request()->is('backend*'))
                    <!-- Sidebar (Desktop only) -->
                    <div class="col-md-3 col-lg-2 d-none d-md-block" id="sidebar-col">
                        <div class="sticky-top d-flex flex-column" style="top: 20px; height: calc(100vh - 100px);">
                            @include('layouts.partials.sidebar')
                        </div>
                    </div>
                    @endif
                    
                    <!-- Main Content -->
                    <div class="@if(request()->is('backend*')) col-12 @else col-md-9 col-lg-10 @endif" id="main-content-col">
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Bottom Nav (Mobile only) -->
        @include('layouts.partials.bottom_nav')
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Sidebar Toggle Logic
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarCol = document.getElementById('sidebar-col');
            const mainContentCol = document.getElementById('main-content-col');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');

            if (sidebarToggle && sidebarCol && mainContentCol) {
                sidebarToggle.addEventListener('click', () => {
                    // Toggle Sidebar Width Logic
                    // Check if expanded (default col-md-3)
                    const isExpanded = sidebarCol.classList.contains('col-md-3');

                    if (isExpanded) {
                        // Shrink Sidebar
                        sidebarCol.classList.remove('col-md-3', 'col-lg-2');
                        sidebarCol.classList.add('col-auto');
                        
                        // Expand Main Content to fill space
                        mainContentCol.classList.remove('col-md-9', 'col-lg-10');
                        mainContentCol.classList.add('col');
                        
                        // Hide Texts
                        sidebarTexts.forEach(el => el.classList.add('d-none'));
                        
                        // Change Icon to "Expand" arrow
                        sidebarToggle.innerHTML = '<i class="bi bi-arrow-bar-right"></i>';
                    } else {
                        // Expand Sidebar
                        sidebarCol.classList.remove('col-auto');
                        sidebarCol.classList.add('col-md-3', 'col-lg-2');
                        
                        // Restore Main Content Width
                        mainContentCol.classList.remove('col');
                        mainContentCol.classList.add('col-md-9', 'col-lg-10');
                        
                        // Show Texts
                        sidebarTexts.forEach(el => el.classList.remove('d-none'));
                        
                        // Change Icon to "Collapse" arrow
                        sidebarToggle.innerHTML = '<i class="bi bi-arrow-bar-left"></i>';
                    }
                });
            }

            const toggleButton = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const htmlElement = document.documentElement;

            // Check localStorage or default to dark
            const savedTheme = localStorage.getItem('theme') || 'dark';
            htmlElement.setAttribute('data-bs-theme', savedTheme);
            updateIcon(savedTheme);

            toggleButton.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            function updateIcon(theme) {
                if (theme === 'dark') {
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-stars-fill');
                } else {
                    themeIcon.classList.remove('bi-moon-stars-fill');
                    themeIcon.classList.add('bi-sun-fill');
                }
            }
        });
    </script>
    
    @include('layouts.partials.flatpickr_setup')
    @stack('scripts')
</body>
</html>
