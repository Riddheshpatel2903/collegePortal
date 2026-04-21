<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'College Management Portal') }} | Institutional Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --slate-bg: #f8fafc;
            --indigo-primary: #4f46e5;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--slate-bg);
            background-image:
                radial-gradient(at 100% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .hero-title {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.04em;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }
        }

        .action-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-card:hover {
            transform: translateY(-8px);
            border-color: #c7d2fe;
            box-shadow: 0 25px 50px -12px rgba(79, 70, 229, 0.1);
        }

        .floating {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>

<body class="antialiased min-h-screen">
    <!-- Navigation -->
    <nav
        class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-100 px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div
                class="w-11 h-11 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-slate-200">
                <i class="bi bi-mortarboard-fill text-xl"></i>
            </div>
            <span class="text-xl font-black tracking-tighter text-slate-800 uppercase">College Management Portal<span
                    class="text-indigo-600">Core</span></span>
        </div>

        <div class="flex items-center gap-8">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 hover:text-indigo-600 transition-colors">Personal
                        Workspace</a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-900 transition-colors">Portal
                        Access</a>
                @endauth
            @endif
        </div>
    </nav>

    <!-- Main Entry Context -->
    <main class="relative px-8 pt-24 pb-48 overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-24">
            <div class="lg:w-1/2 space-y-12">
                <div
                    class="inline-flex items-center gap-3 px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-[0.2em] border border-indigo-100">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                    </span>
                    Institutional Infrastructure v2.0
                </div>

                <h1 class="hero-title text-slate-900">
                    Elevating <br>
                    <span class="text-indigo-600">Academic</span> <br>
                    Governance.
                </h1>

                <p class="text-xl text-slate-500 font-medium max-w-lg leading-relaxed">
                    A streamlined management architecture designed for modern departments. Unify your instructional
                    faculty, students, and curriculum records.
                </p>

                <div class="flex flex-wrap items-center gap-6">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="h-16 px-12 bg-slate-900 text-white rounded-[1.25rem] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 flex items-center transition-all hover:bg-black active:scale-95">
                            Enter Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="h-16 px-12 bg-indigo-600 text-white rounded-[1.25rem] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-indigo-200 flex items-center transition-all hover:bg-indigo-700 active:scale-95">
                            Institutional Login
                        </a>
                    @endauth
                    <div class="flex items-center gap-3 text-slate-400">
                        <span class="h-1 w-8 bg-slate-200"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest">Digital Registry Active</span>
                    </div>
                </div>
            </div>

            <div class="lg:w-1/2 relative">
                <div class="action-card p-12 floating relative z-10 shadow-2xl shadow-slate-200">
                    <div class="grid grid-cols-2 gap-8">
                        <div
                            class="aspect-square rounded-[2rem] bg-indigo-50 flex flex-col items-center justify-center gap-4 text-indigo-600 p-8 border border-indigo-100">
                            <i class="bi bi-people-fill text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em]">Enrolled Users</span>
                            <span class="text-4xl font-black tracking-tighter">12.5k</span>
                        </div>
                        <div
                            class="aspect-square rounded-[2rem] bg-slate-900 flex flex-col items-center justify-center gap-4 text-white p-8">
                            <i class="bi bi-journal-check text-4xl text-indigo-400"></i>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em]">Active Modules</span>
                            <span class="text-4xl font-black tracking-tighter">482</span>
                        </div>
                        <div
                            class="aspect-square rounded-[2rem] bg-slate-50 flex flex-col items-center justify-center gap-4 text-slate-800 p-8 border border-slate-100">
                            <i class="bi bi-mortarboard-fill text-4xl text-indigo-600"></i>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em]">Faculty Staff</span>
                            <span class="text-4xl font-black tracking-tighter">1.2k</span>
                        </div>
                        <div
                            class="aspect-square rounded-[2rem] bg-indigo-600 flex flex-col items-center justify-center gap-4 text-white p-8">
                            <i class="bi bi-calendar-event text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em]">Live Sessions</span>
                            <span class="text-4xl font-black tracking-tighter">24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Operational Highlighting -->
    <section class="max-w-7xl mx-auto px-8 pb-48">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            @foreach([
                    ['icon' => 'bi-diagram-3', 'title' => 'Structural Clarity', 'desc' => 'Organize departments, courses, and semesters with absolute instructional precision.'],
                    ['icon' => 'bi-calendar-range', 'title' => 'Dynamic Scheduling', 'desc' => 'Automated timetable generation optimized for faculty availability and room constraints.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Secure Governance', 'desc' => 'Strict role-based access control protecting academic records across the entire institution.']
                ] as $feature)
                <div class="action-card p-12 space-y-8 flex flex-col">
                    <div class="w-16 h-16 bg-slate-50 text-indigo-600 rounded-3xl flex items-center justify-cente
                            r border border-slate-100 shadow-sm">
                        <i class="bi {{ $feature['icon'] }} text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-4 uppercase">{{ $feature['title'] }}</h3>
                        <p class="text-slate-500 font-medium leading-relaxed italic text-sm">"{{ $feature['desc'] }}"</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Global Footer -->
    <footer class="max-w-7xl mx-auto px-8 py-20 border-t border-slate-100 text-center">
        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-400">
            &copy; {{ date('Y') }} College Management Portal Core Systems. Operational Excellence Guaranteed.
        </p>
    </footer>
</body>

</html>