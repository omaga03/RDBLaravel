<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ระบบฐานข้อมูลงานวิจัย - มหาวิทยาลัยราชภัฏเพชรบูรณ์')</title>

    <!-- Google Fonts: Plus Jakarta Sans, Prompt & Sarabun for Academic Thai/English UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- TomSelect Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* ==========================================================================
           Academic Scholarly & Swiss Modernism 2.0 Design System
           Aesthetic Anchors: Stripe Docs & Linear (Editorial Precision, Tactile Depth)
           ========================================================================== */
        :root {
            /* Scholarly Color Palette - Token Locked */
            --rdb-primary: #1B365D;
            --rdb-primary-hover: #122645;
            --rdb-primary-subtle: #EDF4FC;
            --rdb-secondary: #1D4ED8;
            --rdb-secondary-hover: #1E40AF;
            --rdb-accent: #C87D16; /* Archival Citation Gold */
            --rdb-accent-hover: #A8640B;
            --rdb-success: #047857;
            --rdb-danger: #B91C1C;
            --rdb-warning: #C87D16;
            --rdb-info: #0369A1;

            /* Refined Radius & Tactile Hairline Shadows */
            --rdb-radius-sm: 6px;
            --rdb-radius-md: 10px;
            --rdb-radius-lg: 14px;
            --rdb-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.045), 0 0 0 1px rgba(15, 23, 42, 0.02);
            --rdb-shadow-md: 0 4px 12px -2px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.04);
            --rdb-shadow-lg: 0 12px 24px -4px rgba(15, 23, 42, 0.09), 0 4px 8px -4px rgba(15, 23, 42, 0.05);

            /* Grounded Academic Surfaces (No generic neon/multi-stop slop) */
            --rdb-gradient-primary: linear-gradient(180deg, #1E3A5F 0%, #152C4A 100%);
            --rdb-gradient-navy: linear-gradient(180deg, #162B49 0%, #0F1E36 100%);
            --rdb-gradient-gold: linear-gradient(180deg, #D97706 0%, #B45309 100%);
            --rdb-gradient-emerald: linear-gradient(180deg, #059669 0%, #047857 100%);
        }

        /* Base Typography Calibration for Thai & English Academic UI */
        html {
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        @media (min-width: 1400px) {
            html {
                font-size: 15px;
            }
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Prompt', 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.94rem;
            line-height: 1.58;
            letter-spacing: -0.004em;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Tabular Numerals for Academic Data, Budgets, Years, & Counts */
        .tabular-nums,
        .table td,
        .table th,
        .badge,
        .pagination .page-link,
        input[type="number"] {
            font-variant-numeric: tabular-nums;
        }

        /* Harmonious Scholarly Heading Hierarchy */
        h1, .h1 {
            font-size: 1.5rem !important;
            font-weight: 700;
            line-height: 1.35;
            letter-spacing: -0.018em;
        }

        h2, .h2 {
            font-size: 1.3rem !important;
            font-weight: 700;
            line-height: 1.35;
            letter-spacing: -0.014em;
        }

        h3, .h3, .page-title {
            font-size: 1.15rem !important;
            font-weight: 600;
            line-height: 1.4;
            letter-spacing: -0.01em;
        }

        h4, .h4 {
            font-size: 1.05rem !important;
            font-weight: 600;
            line-height: 1.45;
        }

        h5, .h5 {
            font-size: 0.95rem !important;
            font-weight: 600;
            line-height: 1.45;
        }

        h6, .h6 {
            font-size: 0.88rem !important;
            font-weight: 600;
            line-height: 1.45;
        }

        /* Card Headers & Title Scaling */
        .card-title {
            font-size: 1.08rem !important;
            font-weight: 600;
            line-height: 1.5;
        }

        .card-header h4, .card-header .h4 {
            font-size: 1.1rem !important;
            font-weight: 600;
            line-height: 1.5;
        }

        .card-header h5, .card-header .h5 {
            font-size: 0.95rem !important;
            font-weight: 600;
            margin-bottom: 0;
        }

        .card-header h6, .card-header .h6 {
            font-size: 0.88rem !important;
            font-weight: 600;
            margin-bottom: 0;
        }

        /* Clean Metadata Badges */
        .badge {
            font-size: 0.75rem !important;
            font-weight: 500;
            letter-spacing: 0.01em;
            padding: 0.3rem 0.58rem;
            border-radius: 5px;
        }

        /* Breadcrumbs */
        .breadcrumb {
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            font-size: 0.75rem;
        }

        /* Light Theme (Daylight Editorial Paper & Crisp Slate) */
        [data-bs-theme="light"] {
            --bs-body-bg: #F6F8FA;
            --bs-body-color: #111827;
            --bs-secondary-color: #4B5563;
            --bs-tertiary-bg: #EEF2F6;
            --bs-border-color: #DDE3EA;
            --bs-card-bg: #FFFFFF;
            --rdb-nav-bg: rgba(255, 255, 255, 0.94);
            --rdb-card-border: #DDE3EA;
            --rdb-card-inner-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.8);
            --rdb-table-head-bg: #183152;
            --rdb-table-head-border: #10223A;
            --rdb-table-stripe: #F9FAFB;
            --rdb-table-hover: #F0F4F9;
            --rdb-search-header-bg: #183152;
            --rdb-search-header-accent: #C87D16;
        }

        /* Dark Theme (Carbon-Navy Night Reading & Specular Borders) */
        [data-bs-theme="dark"] {
            --bs-body-bg: #0A101D;
            --bs-body-color: #E5E7EB;
            --bs-secondary-color: #9CA3AF;
            --bs-tertiary-bg: #111A2E;
            --bs-border-color: #1F2D47;
            --bs-card-bg: #10192C;
            --rdb-nav-bg: rgba(16, 25, 44, 0.92);
            --rdb-card-border: #223252;
            --rdb-card-inner-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.045);
            --rdb-table-head-bg: #162642;
            --rdb-table-head-border: #263B63;
            --rdb-table-stripe: rgba(17, 26, 46, 0.45);
            --rdb-table-hover: rgba(34, 50, 82, 0.45);
            --rdb-search-header-bg: #15243E;
            --rdb-search-header-accent: #D97706;
            --rdb-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.35);
            --rdb-shadow-md: 0 6px 16px -3px rgba(0, 0, 0, 0.45);
        }

        /* Dark Mode Scrollbar */
        [data-bs-theme="dark"] ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-track {
            background: #0A101D; 
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background-color: #243556;
            border-radius: 6px;
            border: 2px solid #0A101D;
        }
        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background-color: #354C78;
        }

        /* Global Card Refinement - Editorial Hairline + Subtle Specular Top Edge */
        .card {
            border-radius: var(--rdb-radius-md) !important;
            border: 1px solid var(--rdb-card-border) !important;
            box-shadow: var(--rdb-shadow-sm), var(--rdb-card-inner-highlight);
            background-color: var(--bs-card-bg);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .card:hover {
            box-shadow: var(--rdb-shadow-md), var(--rdb-card-inner-highlight);
        }
        .card-header {
            border-bottom: 1px solid var(--rdb-card-border);
            border-top-left-radius: calc(var(--rdb-radius-md) - 1px) !important;
            border-top-right-radius: calc(var(--rdb-radius-md) - 1px) !important;
            padding: 0.75rem 1.15rem;
        }

        /* Global Table Styling - Editorial Precision */
        .table thead th,
        .table-light,
        .table .thead-light th {
            background: var(--rdb-table-head-bg) !important;
            color: #F8FAFC !important;
            border-bottom: 2px solid var(--rdb-search-header-accent) !important;
            border-top: none !important;
            font-weight: 600;
            font-size: 0.86rem;
            letter-spacing: 0.015em;
            padding: 0.8rem 1rem;
            vertical-align: middle;
        }
        .table thead th:first-child {
            border-top-left-radius: 8px;
        }
        .table thead th:last-child {
            border-top-right-radius: 8px;
        }
        .table tbody tr {
            transition: background-color 0.12s ease;
        }
        .table tbody tr:hover {
            background-color: var(--rdb-table-hover) !important;
        }
        .table tbody td {
            padding: 0.82rem 1rem;
            vertical-align: middle;
            border-color: var(--bs-border-color);
        }

        /* Global Search Box Styling */
        .search-box .card-header,
        .card.search-box > .card-header,
        .card-header.search-header {
            background: var(--rdb-search-header-bg) !important;
            color: #F8FAFC !important;
            border-bottom: 2px solid var(--rdb-search-header-accent) !important;
            cursor: pointer;
            border-top-left-radius: calc(var(--rdb-radius-md) - 1px) !important;
            border-top-right-radius: calc(var(--rdb-radius-md) - 1px) !important;
        }
        .search-box .card-header h5,
        .search-box .card-header h6,
        .card-header.search-header h5,
        .card-header.search-header h6 {
            color: #F8FAFC !important;
            letter-spacing: 0.005em;
        }
        .search-box .btn-toggle-search,
        .btn-toggle-search {
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.22) !important;
            color: #FFFFFF !important;
            border-radius: var(--rdb-radius-sm);
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .search-box .btn-toggle-search:hover,
        .btn-toggle-search:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.35) !important;
        }
        .btn-toggle-search[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
            transition: transform 0.2s ease;
        }
        .btn-toggle-search[aria-expanded="false"] .bi-chevron-down {
            transform: rotate(0deg);
            transition: transform 0.2s ease;
        }

        /* Buttons Styling - Restraint over bouncy transforms */
        .btn {
            border-radius: var(--rdb-radius-sm);
            font-weight: 500;
            padding: 0.42rem 0.95rem;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        }
        .btn-sm {
            padding: 0.28rem 0.68rem;
            font-size: 0.82rem;
        }
        .btn-primary {
            background-color: var(--rdb-primary);
            border-color: var(--rdb-primary);
            color: #FFFFFF;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--rdb-primary-hover);
            border-color: var(--rdb-primary-hover);
            color: #FFFFFF;
        }
        [data-bs-theme="dark"] .btn-primary {
            background-color: #2563EB;
            border-color: #3B82F6;
        }
        [data-bs-theme="dark"] .btn-primary:hover,
        [data-bs-theme="dark"] .btn-primary:focus {
            background-color: #1D4ED8;
            border-color: #60A5FA;
        }
        .btn-success {
            background-color: var(--rdb-success);
            border-color: var(--rdb-success);
        }
        .btn-warning {
            background-color: var(--rdb-accent);
            border-color: var(--rdb-accent);
            color: #FFFFFF;
        }
        .btn-warning:hover {
            background-color: var(--rdb-accent-hover);
            border-color: var(--rdb-accent-hover);
            color: #FFFFFF;
        }

        /* Form Inputs */
        .form-control, .form-select {
            border-radius: var(--rdb-radius-sm);
            border: 1px solid var(--bs-border-color);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--rdb-secondary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15);
        }
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0D1526;
            border-color: #243556;
            color: #E5E7EB;
        }
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0F192D;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* TomSelect Highlighting & Dark Mode */
        .ts-dropdown .highlight {
            background: rgba(200, 125, 22, 0.12) !important;
            color: #B45309 !important;
            font-weight: 600 !important;
            padding: 0 2px !important;
            border-radius: 3px;
        }
        [data-bs-theme="dark"] .ts-dropdown .highlight {
            background: rgba(251, 191, 36, 0.15) !important;
            color: #FBBF24 !important;
            text-shadow: none !important;
        }
        .ts-wrapper .ts-control, 
        .ts-wrapper .ts-control input {
            color: var(--bs-body-color) !important;
            border-radius: var(--rdb-radius-sm);
        }
        [data-bs-theme="dark"] .ts-wrapper .ts-control,
        [data-bs-theme="dark"] .ts-wrapper .ts-control input {
            color: #E5E7EB !important;
            background-color: #0D1526 !important;
            border-color: #243556 !important;
        }
        [data-bs-theme="dark"] .ts-dropdown {
            background-color: #10192C !important;
            color: #E5E7EB !important;
            border-color: #243556 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
        }
        [data-bs-theme="dark"] .ts-dropdown .option {
            color: #E5E7EB !important;
        }
        [data-bs-theme="dark"] .ts-dropdown .active {
            background-color: #1E3A5F !important;
            color: #FFFFFF !important;
        }

        /* Navbar Styling */
        .rdb-navbar {
            background-color: var(--rdb-nav-bg) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--bs-border-color) !important;
            z-index: 1050 !important;
        }
        .rdb-navbar .dropdown-menu {
            z-index: 1055 !important;
            border-color: var(--rdb-card-border);
            box-shadow: var(--rdb-shadow-md);
        }
        .rdb-navbar .nav-link.active {
            color: var(--rdb-secondary, #1D4ED8) !important;
            font-weight: 600 !important;
        }
        .rdb-navbar .dropdown-item.active,
        .rdb-navbar .dropdown-item:active {
            background-color: rgba(37, 99, 235, 0.12) !important;
            color: var(--rdb-secondary, #1D4ED8) !important;
            font-weight: 600 !important;
        }
        [data-bs-theme="dark"] .rdb-navbar .dropdown-item.active,
        [data-bs-theme="dark"] .rdb-navbar .dropdown-item:active {
            background-color: rgba(37, 99, 235, 0.25) !important;
            color: #93C5FD !important;
            font-weight: 600 !important;
        }

        /* Modal & Backdrop Layering: Ensure Modals always sit above sticky navbars */
        .modal {
            z-index: 1075 !important;
        }
        .modal-backdrop {
            z-index: 1070 !important;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--bs-body-color) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-brand .brand-badge {
            background: linear-gradient(135deg, #183152 0%, #1d4ed8 100%);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #FFFFFF !important;
            font-size: 1.05rem;
            padding: 0.32rem 0.85rem;
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.04em;
            box-shadow: 0 2px 6px rgba(24, 49, 82, 0.25);
            display: inline-flex;
            align-items: center;
            line-height: 1.25;
            transition: all 0.2s ease-in-out;
        }
        .navbar-brand:hover .brand-badge {
            background: linear-gradient(135deg, #10223a 0%, #1e40af 100%);
            box-shadow: 0 4px 12px rgba(24, 49, 82, 0.35);
            transform: translateY(-1px);
        }
        [data-bs-theme="dark"] .navbar-brand .brand-badge {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            border-color: rgba(96, 165, 250, 0.3);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }
        [data-bs-theme="dark"] .navbar-brand:hover .brand-badge {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.5);
        }

        /* Theme Toggle Button */
        #theme-toggle {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        #theme-toggle:hover {
            background-color: var(--bs-tertiary-bg);
        }

        /* Abstract & Long-Text Typography & Box Styling */
        .abstract-box {
            border-radius: 10px;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .abstract-box:hover {
            box-shadow: var(--rdb-shadow-sm);
        }
        .abstract-content {
            font-size: 0.96rem;
            line-height: 1.88;
            letter-spacing: 0.012em;
            text-align: justify;
            text-justify: inter-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .abstract-indent {
            text-indent: 2.25rem;
        }
        .abstract-content p {
            margin-bottom: 0.85rem;
            text-indent: 2.25rem;
        }
        .abstract-content p:last-child {
            margin-bottom: 0;
        }
        [data-bs-theme="dark"] .abstract-content {
            color: #D1D5DB;
        }
        [data-bs-theme="light"] .abstract-content {
            color: #334155;
        }
        .copy-abstract-btn {
            transition: all 0.15s ease;
        }
        .copy-abstract-btn:hover {
            background-color: var(--bs-secondary-bg);
            color: var(--bs-body-color);
        }
    </style>
    <!-- IMask CDN -->
    <script src="https://unpkg.com/imask"></script>
    <!-- MathJax: render equations in abstracts -->
    <script>window.MathJax = { tex: { inlineMath: [['$', '$'], ['\\(', '\\)']] }, options: { skipHtmlTags: ['script','noscript','style','textarea','pre','code'] } };</script>
    <script async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js"></script>
    {{-- Page-specific meta tags (Google Scholar / Dublin Core / OpenGraph) --}}
    @stack('meta')
    @stack('styles')
</head>
<body>
    <div id="app">
        @if(session()->has('impersonator_id'))
            <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 px-4 d-flex justify-content-between align-items-center shadow-sm" style="position: sticky; top: 0; z-index: 1060; background: #fff3cd; color: #664d03;">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge {{ session()->has('impersonated_user_id') ? 'bg-danger text-white' : 'bg-warning text-dark' }} border fw-bold">
                        <i class="bi bi-person-fill-gear me-1"></i> {{ session()->has('impersonated_user_id') ? 'โหมดสวมสิทธิ์ผู้ใช้งาน (Login As)' : 'โหมดทดสอบบทบาท (Testing Role)' }}
                    </span>
                    <span>
                        คุณกำลังใช้งานในฐานะ <strong>{{ Auth::user()->username }}</strong> ({{ (Auth::user()->researcher && !empty(Auth::user()->researcher->full_name)) ? Auth::user()->researcher->full_name : 'นักวิจัย/ผู้ใช้งานระบบ' }})
                    </span>
                    <span class="text-muted small">
                        (บัญชีจริง: {{ session('impersonator_username', 'Admin') }})
                    </span>
                </div>
                <form action="{{ session()->has('impersonated_user_id') ? route('backend.user.stop_impersonate') : route('backend.rbac.stop_impersonate') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark shadow-sm text-nowrap">
                        <i class="bi bi-box-arrow-left me-1"></i> สิ้นสุดการสวมสิทธิ์ (กลับสู่บัญชีหลัก)
                    </button>
                </form>
            </div>
        @endif
        <nav class="navbar navbar-expand-md shadow-sm rdb-navbar sticky-top" style="z-index: 1050;">
            <div class="{{ request()->is('backend*') ? 'container-fluid px-4' : 'container' }}">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="https://research.pcru.ac.th/rdb/images/pcru.png" alt="PCRU Logo" style="height: 36px;" class="me-2">
                    <span class="brand-badge ms-1">PCRU RDB</span>
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
                                @if(Auth::user()->canAccessFullBackend())
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('backend.site.index') || request()->is('backend') ? 'active fw-bold' : '' }}" href="{{ route('backend.site.index') }}">
                                            <i class="bi bi-speedometer2 text-primary me-1"></i> แดชบอร์ด
                                        </a>
                                    </li>
                                @else
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('backend.onboarding.*') ? 'active fw-bold text-warning' : '' }}" href="{{ route('backend.onboarding.index') }}">
                                            <i class="bi bi-person-check-fill text-warning me-1"></i> ยืนยันตัวตนนักวิจัย
                                        </a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link text-body-secondary" href="{{ route('home') }}" target="_blank" title="เปิดหน้าเว็บไซต์ส่วนสาธารณะ (เปิดแท็บใหม่)">
                                        <i class="bi bi-box-arrow-up-right text-info me-1"></i> ดูหน้าบ้าน
                                    </a>
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
                            <!-- Notifications Bell -->
                            <li class="nav-item dropdown me-2">
                                <a class="nav-link position-relative p-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="การแจ้งเตือน">
                                    <i class="bi bi-bell fs-5"></i>
                                    @php
                                        $unreadNotifCount = Auth::user()->unreadNotifications()->count();
                                    @endphp
                                    @if($unreadNotifCount > 0)
                                        <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                            {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                                        </span>
                                    @endif
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow-lg py-0 border-0 rounded-3" style="width: 320px; max-height: 420px; overflow-y: auto;">
                                    <div class="p-3 bg-body-tertiary border-bottom d-flex align-items-center justify-content-between">
                                        <h6 class="mb-0 fw-bold small"><i class="bi bi-bell-fill text-primary me-1"></i> การแจ้งเตือน</h6>
                                        @if($unreadNotifCount > 0)
                                            <form action="{{ route('notifications.read_all') }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none small">อ่านทั้งหมด</button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="list-group list-group-flush small">
                                        @forelse(Auth::user()->notifications()->take(6)->get() as $n)
                                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="list-group-item list-group-item-action py-2.5 px-3 border-bottom text-start {{ $n->read_at ? 'text-muted' : 'bg-primary-subtle' }}">
                                                    <div class="fw-semibold text-body">{{ $n->data['title'] ?? 'การแจ้งเตือน' }}</div>
                                                    <div class="small text-secondary text-truncate" style="max-width: 280px;">{{ $n->data['message'] ?? '' }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $n->created_at ? $n->created_at->diffForHumans() : '' }}</div>
                                                </button>
                                            </form>
                                        @empty
                                            <div class="p-4 text-center text-muted small">
                                                <i class="bi bi-bell-slash fs-4 d-block mb-1 text-secondary opacity-50"></i>
                                                ไม่มีการแจ้งเตือนในขณะนี้
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </li>

                            <!-- User Profile & Multi-Role Dropdown -->
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center gap-1.5" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle"></i>
                                    <span>{{ Auth::user()->username ?? Auth::user()->name }}</span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarDropdown" style="min-width: 260px;">
                                    <div class="dropdown-header small text-body-secondary fw-bold">
                                        <div class="text-body d-flex align-items-center">
                                            <i class="bi bi-person-fill text-primary me-1.5"></i>
                                            <span>{{ (Auth::user()->researcher && !empty(Auth::user()->researcher->full_name)) ? Auth::user()->researcher->full_name : Auth::user()->username }}</span>
                                        </div>
                                        @php
                                            $activeScope = Auth::user()->getActiveRoleScope();
                                        @endphp
                                        <div class="text-primary small mt-1 d-flex align-items-center">
                                            <i class="bi bi-shield-check text-success me-1"></i>
                                            <span>{{ $activeScope ? $activeScope->label : (Auth::user()->roles->first()->item_name ?? 'User') }}</span>
                                        </div>
                                    </div>

                                    @if(Auth::user()->roleScopes && Auth::user()->roleScopes->count() > 1)
                                        <div class="dropdown-divider"></div>
                                        <div class="dropdown-header small text-body-secondary fw-bold text-uppercase py-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-arrow-repeat me-1 text-primary"></i> สลับบทบาทการทำงาน (Multi-Role)
                                        </div>
                                        @foreach(Auth::user()->roleScopes as $scope)
                                            @php
                                                $isActive = ($activeScope && $activeScope->id === $scope->id);
                                            @endphp
                                            <form action="{{ route('backend.rbac.switch_scope') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="scope_id" value="{{ $scope->id }}">
                                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-1.5 {{ $isActive ? 'active bg-primary text-white' : '' }}">
                                                    <span class="text-truncate me-2 small d-flex align-items-center">
                                                        <i class="bi bi-person-badge me-1.5 {{ $isActive ? 'text-white' : 'text-secondary' }}"></i>
                                                        {{ $scope->label }}
                                                    </span>
                                                    @if($isActive)
                                                        <i class="bi bi-check2"></i>
                                                    @endif
                                                </button>
                                            </form>
                                        @endforeach
                                    @endif

                                    <div class="dropdown-divider"></div>
                                    @if(request()->is('backend*'))
                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('home') }}" target="_blank">
                                            <i class="bi bi-box-arrow-up-right me-2 text-info"></i> ไปหน้าบ้าน (Public Portal)
                                        </a>
                                    @else
                                        @if(Auth::user()->canAccessFullBackend())
                                            <a class="dropdown-item d-flex align-items-center" href="{{ route('backend.site.index') }}">
                                                <i class="bi bi-speedometer2 me-2 text-primary"></i> จัดการข้อมูลหลังบ้าน
                                            </a>
                                        @else
                                            <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('backend.onboarding.*') ? 'active' : '' }}" href="{{ route('backend.onboarding.index') }}">
                                                <i class="bi bi-person-check-fill me-2 text-warning"></i> ยืนยันตัวตน / ขอสิทธิ์หลังบ้าน
                                            </a>
                                        @endif
                                    @endif
                                    @if(Auth::user()->hasRole('The Creator') || Auth::user()->hasRole('Admin') || Auth::user()->hasPermission('User Manager'))
                                        <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('backend.user.index') ? 'active' : '' }}" href="{{ route('backend.user.index') }}">
                                            <i class="bi bi-person-gear me-2 text-primary"></i> จัดการผู้ใช้งาน (Users)
                                        </a>
                                    @endif
                                    <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('backend.user.show') && request()->route('id') == Auth::id() ? 'active' : '' }}" href="{{ route('backend.user.show', Auth::id()) }}">
                                        <i class="bi bi-person-circle me-2 text-primary"></i> บัญชีของฉัน
                                    </a>
                                    <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('backend.user.edit') && request()->route('id') == Auth::id() ? 'active' : '' }}" href="{{ route('backend.user.edit', Auth::id()) }}">
                                        <i class="bi bi-key me-2 text-warning"></i> เปลี่ยนรหัสผ่าน
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger d-flex align-items-center" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2 text-danger"></i> {{ __('Logout') }}
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
            </div>
        </nav>

        <!-- Row 2: Subnav Menu (Backend vs Frontend) -->
        @if(request()->is('backend*'))
            @auth
            <x-backend-menu />
            @endauth
        @else
            <x-frontend-menu />
        @endif

        <main class="py-4">
            <div class="container-fluid px-4">
                <!-- Alerts handled by SweetAlert2 Toast now -->
                <div id="flash-messages" style="display: none;" 
                     data-success="{{ session('success') }}" 
                     data-error="{{ session('error') }}"
                     data-errors="{{ $errors->any() ? json_encode($errors->all()) : '' }}">
                </div>

                @yield('content')
            </div>
        </main>

        @unless(request()->is('backend*'))
        <footer class="border-top mt-4 py-4 bg-body-tertiary">
            <div class="container text-center small text-body-secondary">
                <div class="fw-semibold text-body mb-1">สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</div>
                <div>83 หมู่ 11 ต.สะเดียง อ.เมือง จ.เพชรบูรณ์ 67000 · research.pcru.ac.th</div>
                <div class="mt-2">
                    <a href="{{ route('privacy') }}" class="text-decoration-none me-3">นโยบายความเป็นส่วนตัว</a>
                    <a href="{{ route('cookies') }}" class="text-decoration-none">นโยบายคุกกี้</a>
                </div>
                <div class="mt-2">&copy; {{ date('Y') }} PCRU Research &amp; Development Institute</div>
            </div>
        </footer>
        @endunless
        
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
                    const isExpanded = sidebarCol.classList.contains('col-md-4') || sidebarCol.classList.contains('col-md-3');

                    if (isExpanded) {
                        // Shrink Sidebar
                        sidebarCol.classList.remove('col-md-4', 'col-lg-3', 'col-xl-2', 'col-md-3', 'col-lg-2');
                        sidebarCol.classList.add('col-auto');
                        
                        // Expand Main Content to fill space
                        mainContentCol.classList.remove('col-md-8', 'col-lg-9', 'col-xl-10', 'col-md-9', 'col-lg-10');
                        mainContentCol.classList.add('col');
                        
                        // Hide Texts
                        sidebarTexts.forEach(el => el.classList.add('d-none'));
                        
                        // Change Icon to "Expand" arrow
                        sidebarToggle.innerHTML = '<i class="bi bi-arrow-bar-right"></i>';
                    } else {
                        // Expand Sidebar
                        sidebarCol.classList.remove('col-auto');
                        sidebarCol.classList.add('col-md-4', 'col-lg-3', 'col-xl-2');
                        
                        // Restore Main Content Width
                        mainContentCol.classList.remove('col');
                        mainContentCol.classList.add('col-md-8', 'col-lg-9', 'col-xl-10');
                        
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
                window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
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
    
    <!-- TomSelect JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
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

            // 1. Delete Confirmation with SweetAlert2 (Delegated)
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.delete-form');
                if (!form || form.dataset.confirmed === 'true') return;

                e.preventDefault();
                
                Swal.fire({
                    title: 'ยืนยันการลบข้อมูล?',
                    text: "หากลบแล้วจะไม่สามารถกู้คืนข้อมูลนี้ได้!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash me-1"></i> ยืนยันการลบ',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังลบข้อมูล...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
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

            // 4. Global Bootstrap Tooltips Initialization
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
                }
            });
        });

        // Global Copy Text & Abstract Helpers
        function copyTextToClipboard(text, onSuccess, onError) {
            if (!text) return;
            text = text.trim();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    if (typeof onSuccess === 'function') onSuccess();
                }).catch(() => {
                    execFallback(text, onSuccess, onError);
                });
            } else {
                execFallback(text, onSuccess, onError);
            }

            function execFallback(str, successCb, errorCb) {
                let textarea = document.createElement('textarea');
                textarea.value = str;
                textarea.style.position = 'fixed';
                textarea.style.top = '0';
                textarea.style.left = '0';
                textarea.style.width = '2em';
                textarea.style.height = '2em';
                textarea.style.padding = '0';
                textarea.style.border = 'none';
                textarea.style.outline = 'none';
                textarea.style.boxShadow = 'none';
                textarea.style.background = 'transparent';
                textarea.style.opacity = '0';
                textarea.setAttribute('readonly', '');
                document.body.appendChild(textarea);

                textarea.focus();
                textarea.select();

                let successful = false;
                try {
                    successful = document.execCommand('copy');
                } catch (err) {
                    console.error('execCommand copy failed:', err);
                }
                document.body.removeChild(textarea);

                if (successful) {
                    if (typeof successCb === 'function') successCb();
                } else {
                    if (typeof errorCb === 'function') errorCb();
                }
            }
        }

        function copyAbstractText(target, triggerBtn) {
            let btn = (triggerBtn && triggerBtn.nodeType) ? triggerBtn : (target && target.nodeType ? target : null);
            let elId = (typeof target === 'string') ? target : (typeof triggerBtn === 'string' ? triggerBtn : null);
            if (!elId) return;
            const el = document.getElementById(elId);
            if (!el) return;
            const text = el.innerText || el.textContent || '';

            copyTextToClipboard(text, () => {
                if (!btn) return;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> คัดลอกแล้ว';
                btn.classList.add('border-success');
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('border-success');
                }, 2000);
            }, () => {
                window.prompt("กด Ctrl+C เพื่อคัดลอกข้อความ:", text);
            });
        }
    </script>
