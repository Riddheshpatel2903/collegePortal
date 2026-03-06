<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EduPortal') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 min-h-screen flex flex-col items-center justify-center p-6 antialiased">
    <div class="fixed inset-0 pointer-events-none">
        <div
            class="absolute w-96 h-96 bg-violet-500 rounded-full filter blur-[120px] opacity-10 -top-40 -left-40 animate-pulse">
        </div>
        <div class="absolute w-80 h-80 bg-purple-500 rounded-full filter blur-[120px] opacity-10 -bottom-32 -right-32 animate-pulse"
            style="animation-delay: 1s;"></div>
    </div>

    <div class="relative z-10 mb-6">
        <a href="/" class="flex items-center gap-3 text-white">
            <div
                class="h-12 w-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-500/30">
                <i class="bi bi-mortarboard-fill text-xl"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tight">EduPortal</span>
        </a>
    </div>

    <div
        class="w-full sm:max-w-md bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl shadow-black/20 p-8 relative z-10">
        {{ $slot }}
    </div>

    <p class="text-xs text-white/20 mt-6 relative z-10">&copy; 2026 EduPortal. All rights reserved.</p>
</body>

</html>