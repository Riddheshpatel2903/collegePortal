<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'College Management Portal') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/fevicon.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Early Toast Initialization
        window.toast = {
            success: (m) => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: m } })),
            error: (m) => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: m } })),
            warning: (m) => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'warning', message: m } })),
            info: (m) => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: m } }))
        };
    </script>

    <!-- Theme loaded via Vite from resources/css/theme.css -->
    @stack('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased" x-data="{ sidebarOpen: false }">
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

    <div class="min-h-screen flex bg-slate-50">

        @include('layouts.partials.sidebar')

        <!-- ─── Main Content ─── -->
        <div class="flex-1 flex flex-col min-w-0">

            @include('layouts.partials.header')

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-6">
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
            </main>
        </div>
    </div>
    <!-- ─── Global Toast Component ─── -->
    <div x-data="{ 
        toasts: [], 
        add(toast) { 
            toast.id = Date.now();
            this.toasts.push(toast); 
            setTimeout(() => this.remove(toast.id), 5000); 
        }, 
        remove(id) { 
            this.toasts = this.toasts.filter(t => t.id !== id); 
        } 
    }" @toast.window="add($event.detail)" class="fixed bottom-8 right-8 z-[999] space-y-3 w-80 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.id" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                class="pointer-events-auto bg-white border border-slate-200 rounded-2xl p-5 shadow-2xl flex items-start gap-4 animate-in">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0 border" :class="{
                        'bg-emerald-50 text-emerald-600 border-emerald-100': toast.type === 'success',
                        'bg-rose-50 text-rose-600 border-rose-100': toast.type === 'error',
                        'bg-amber-50 text-amber-600 border-amber-100': toast.type === 'warning',
                        'bg-sky-50 text-sky-600 border-sky-100': toast.type === 'info'
                    }">
                    <i class="bi" :class="{
                        'bi-check-circle-fill': toast.type === 'success',
                        'bi-exclamation-circle-fill': toast.type === 'error',
                        'bi-exclamation-triangle-fill': toast.type === 'warning',
                        'bi-info-circle-fill': toast.type === 'info'
                    }"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-black text-slate-400 capitalize" x-text="toast.type"></p>
                    <p class="text-sm font-bold text-slate-800 leading-tight mt-1" x-text="toast.message"></p>
                </div>
                <button @click="remove(toast.id)" class="text-slate-300 hover:text-slate-500 transition-colors">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </template>
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