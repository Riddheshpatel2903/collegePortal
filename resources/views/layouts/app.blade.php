<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EduPortal') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/fevicon.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Theme loaded via Vite from resources/css/theme.css -->
    @stack('styles')
</head>

<body class="bg-gradient-to-br from-slate-50 via-violet-50/30 to-slate-50 text-slate-900 antialiased">
    <noscript>
        <div class="mx-auto max-w-7xl p-4">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                JavaScript is disabled. Some dropdowns and dynamic filters will not work.
            </div>
        </div>
    </noscript>

    <div id="globalJsErrorBanner" class="hidden mx-auto max-w-7xl p-4">
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            A page script failed to load correctly. Basic page content is still available. Check browser console for
            details.
        </div>
    </div>

    <div class="flex min-h-screen" x-data="{ sidebarOpen: true }">

        @include('layouts.partials.sidebar')

        <!-- ─── Main Content ─── -->
        <div class="flex flex-1 flex-col overflow-hidden">

            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-8 max-w-[1600px] w-full mx-auto">
                    @if (session('success'))
                        <div
                            class="mb-6 px-5 py-4 bg-teal-50 border border-teal-100 text-teal-700 rounded-xl flex items-center gap-3">
                            <i class="bi bi-check-circle-fill text-lg text-teal-500"></i>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div
                            class="mb-6 px-5 py-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl flex items-center gap-3">
                            <i class="bi bi-exclamation-circle-fill text-lg text-rose-500"></i>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                </div>
            </main>
        </div>
    </div>
    <script>
        (function () {
            const banner = document.getElementById('globalJsErrorBanner');

            window.addEventListener('error', function (event) {
                if (banner) {
                    banner.classList.remove('hidden');
                }
                console.error('[Portal][WindowError]', event.error || event.message || event);
            });

            window.addEventListener('unhandledrejection', function (event) {
                if (banner) {
                    banner.classList.remove('hidden');
                }
                console.error('[Portal][WindowPromiseRejection]', event.reason || event);
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>