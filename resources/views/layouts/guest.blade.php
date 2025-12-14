<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background-color: #1a202c;">
            <div class="mb-6 text-center">
                <a href="/">
                    <img src="{{ asset('images/logo.png') }}" class="w-20 h-auto mx-auto mb-4 drop-shadow-md" alt="PCRU Logo">
                </a>
                <h1 class="text-4xl font-bold text-white mb-2 tracking-wide" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    ระบบฐานข้อมูลงานวิจัย
                </h1>
                <p class="text-gray-300 text-lg font-light">
                    สถาบันวิจัยและพัฒนา มหาวิทยาลัยราชภัฏเพชรบูรณ์
                </p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
