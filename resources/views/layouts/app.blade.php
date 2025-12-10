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
        [data-bs-theme="light"] body {
            background-color: #f8f9fa;
            color: #212529;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
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
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('backend.site.index') }}">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('backend.rdb_project.index') }}">Projects</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('backend.rdb_researcher.index') }}">Researchers</a>
                            </li>
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                        <!-- Theme Toggle -->
                        <li class="nav-item me-2">
                            <button class="btn btn-link nav-link" id="theme-toggle" title="Toggle theme">
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

        <main class="py-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Sidebar (Desktop only) -->
                    <div class="col-md-3 col-lg-2 d-none d-md-block" id="sidebar-col">
                        <div class="sticky-top d-flex flex-column" style="top: 20px; height: calc(100vh - 100px);">
                            @include('layouts.partials.sidebar')
                        </div>
                    </div>
                    
                    <!-- Main Content -->
                    <div class="col-md-9 col-lg-10" id="main-content-col">
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
    
    @stack('scripts')
</body>
</html>
