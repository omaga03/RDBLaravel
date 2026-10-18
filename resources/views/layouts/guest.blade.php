<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ระบบฐานข้อมูลงานวิจัย - มหาวิทยาลัยราชภัฏเพชรบูรณ์') }}</title>

        <!-- Google Fonts: Plus Jakarta Sans & Prompt -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-800" style="font-family: 'Prompt', 'Plus Jakarta Sans', sans-serif;">
        <div class="min-h-screen flex flex-col justify-center items-center py-10 px-4 relative overflow-hidden" 
             style="background: radial-gradient(120% 120% at 50% 10%, #1e3a63 0%, #0d223d 45%, #071322 100%);">
            
            <!-- Ambient Background Glows -->
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <!-- Header Section -->
            <div class="mb-6 text-center z-10">
                <a href="{{ url('/') }}" class="inline-block transition-transform duration-300 hover:scale-105" title="กลับสู่หน้าหลัก">
                    <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl mb-3">
                        <img src="{{ asset('images/logo.png') }}" 
                             alt="PCRU Logo" 
                             style="width: 84px; height: 84px; max-width: 84px; max-height: 84px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));" />
                    </div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight" style="text-shadow: 0 2px 10px rgba(0,0,0,0.35);">
                    ระบบฐานข้อมูลงานวิจัย
                </h1>
                <p class="text-blue-200/90 text-sm sm:text-base font-light mt-1">
                    สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์
                </p>
            </div>

            <!-- Card Container -->
            <div class="w-full sm:max-w-md bg-white rounded-3xl p-6 sm:p-8 border border-white/40 z-10" 
                 style="box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2);">
                {{ $slot }}
            </div>
            
            <!-- Footer Section -->
            <div class="mt-8 text-center text-xs text-blue-200/70 space-y-1 z-10">
                <div>© {{ date('Y') }} Research Database System (PCRU RDB). All rights reserved.</div>
                <div>สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์</div>
            </div>
        </div>
    </body>
</html>
