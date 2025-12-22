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

        /* 2. Global Table Header Styling - Gradient Blue */
        .table thead th,
        .table-light,
        .table .thead-light th {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%) !important;
            color: #fff !important;
            border-color: #1a237e !important;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        /* Both themes use the same gradient header */
        [data-bs-theme="dark"] .table thead th,
        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] .table .thead-light th,
        [data-bs-theme="light"] .table thead th,
        [data-bs-theme="light"] .table-light,
        [data-bs-theme="light"] .table .thead-light th {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%) !important;
            color: #fff !important;
            border-color: #1a237e !important;
        }
        
        /* Table border adjustments */
        [data-bs-theme="dark"] .table thead th {
            border-bottom-color: #3949ab !important;
        }
        [data-bs-theme="light"] .table thead th {
            border-bottom-color: #3949ab !important;
        }
        
        /* 3. Global Search Box Styling - Collapsible with Gradient Header */
        .search-box .card-header,
        .card.search-box > .card-header,
        .card-header.search-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%) !important;
            color: #fff !important;
            border-color: #1a237e !important;
            cursor: pointer;
        }
        .search-box .card-header h5,
        .search-box .card-header h6,
        .card-header.search-header h5,
        .card-header.search-header h6 {
            color: #fff !important;
        }
        .search-box .btn-toggle-search,
        .btn-toggle-search {
            background: rgba(255,255,255,0.15) !important;
            border: 1px solid rgba(255,255,255,0.3) !important;
            color: #fff !important;
            transition: all 0.2s ease;
        }
        .search-box .btn-toggle-search:hover,
        .btn-toggle-search:hover {
            background: rgba(255,255,255,0.25) !important;
        }
        /* Rotate chevron when expanded */
        .btn-toggle-search[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
            transition: transform 0.2s ease;
        }
        .btn-toggle-search[aria-expanded="false"] .bi-chevron-down {
            transform: rotate(0deg);
            transition: transform 0.2s ease;
        }
        /* Search box tabs styling */
        .search-box .nav-tabs .nav-link,
        .card-header.search-header .nav-tabs .nav-link {
            color: rgba(255,255,255,0.8) !important;
            border: none;
        }
        .search-box .nav-tabs .nav-link.active,
        .card-header.search-header .nav-tabs .nav-link.active {
            background-color: #fff !important;
            color: #1a237e !important;
        }
        .search-box .nav-tabs .nav-link:hover:not(.active),
        .card-header.search-header .nav-tabs .nav-link:hover:not(.active) {
            color: #fff !important;
            background-color: rgba(255,255,255,0.1) !important;
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
            <x-backend-menu />
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
                        
                        <!-- Alerts handled by SweetAlert2 Toast now -->
                        <div id="flash-messages" style="display: none;" 
                             data-success="{{ session('success') }}" 
                             data-error="{{ session('error') }}"
                             data-errors="{{ $errors->any() ? json_encode($errors->all()) : '' }}">
                        </div>

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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Global UX Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 0. SweetAlert2 Toast Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Check for Flash Messages
            const flashDiv = document.getElementById('flash-messages');
            if (flashDiv) {
                const successMsg = flashDiv.dataset.success;
                const errorMsg = flashDiv.dataset.error;
                const errorsData = flashDiv.dataset.errors;

                if (successMsg) {
                    Toast.fire({
                        icon: 'success',
                        title: successMsg
                    });
                }

                if (errorMsg) {
                    Toast.fire({
                        icon: 'error',
                        title: errorMsg
                    });
                }

                // Handle Validation Errors (Array)
                if (errorsData) {
                    try {
                        const errors = JSON.parse(errorsData);
                        if (errors.length > 0) {
                            // Show first error as toast, or list them? 
                            // Standard toast is small, maybe just say "Validation Error" + first msg
                            // Or use a regular Swal for multiple errors if huge.
                            // Let's stick to Toast for consistency, using first error or generic msg.
                            Toast.fire({
                                icon: 'error',
                                title: errors[0] // Show the first error
                            });
                        }
                    } catch (e) {
                        console.error("Error parsing validation errors", e);
                    }
                }
            }

            // 1. Delete Confirmation with SweetAlert2
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'ยืนยันการลบข้อมูล?',
                        text: "หากลบแล้วจะไม่สามารถกู้คืนได้!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'ลบข้อมูล',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading on delete
                            Swal.fire({
                                title: 'กำลังลบข้อมูล...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            this.submit();
                        }
                    });
                });
            });

            // 2. Loading State on Form Submit
            document.querySelectorAll('form:not(.delete-form):not(.no-loading)').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!this.checkValidity()) return; // Skip if HTML5 validation fails
                    
                    const btn = this.querySelector('button[type="submit"]');
                    if (btn && !btn.classList.contains('disabled')) {
                        const originalText = btn.innerHTML;
                        btn.classList.add('disabled');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...';
                        
                        // Optional: Restore after timeout in case of back-button or error (safety net)
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.remove('disabled');
                            btn.disabled = false;
                        }, 10000); 
                    }
                });
            });

            // 3. Global Auto-Focus & Select on Click
            // Helps users quickly overwrite data in textboxes
            document.addEventListener('click', function(e) {
                // Check if target is a relevant input
                if (e.target.matches('input[type="text"].form-control, input[type="number"].form-control, textarea.form-control')) {
                    // Use timeout to ensure selection happens AFTER browser handles the click event
                    // This fixes the issue where clicking "deselects" the text immediately
                    setTimeout(() => {
                        e.target.focus();
                        if (typeof e.target.select === 'function') {
                            e.target.select();
                        }
                    }, 50);
                }
            });
        });
    </script>
