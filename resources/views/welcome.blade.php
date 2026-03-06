<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'EduPortal') }} | Premium Academic Experience</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --premium-bg: #f8fafc;
            --indigo-accent: #4361ee;
            --rose-accent: #f72585;
            --emerald-accent: #06d6a0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--premium-bg);
            background-image:
                radial-gradient(at 0% 0%, rgba(67, 97, 238, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(247, 37, 133, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(6, 214, 160, 0.08) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(67, 97, 238, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .hero-mesh {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hero-title {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #1e293b 0%, #4361ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .premium-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 40px 80px -15px rgba(67, 97, 238, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }

        .btn-glow {
            position: relative;
            transition: all 0.3s;
        }

        .btn-glow:hover {
            box-shadow: 0 0 30px rgba(67, 97, 238, 0.4);
            transform: scale(1.05);
        }

        .floating {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body class="antialiased">
    <!-- Navbar -->
    <nav class="glass-nav sticky top-0 z-50 px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="bi bi-mortarboard-fill text-xl"></i>
            </div>
            <span class="text-xl font-extrabold tracking-tighter text-slate-800 uppercase">EduPortal<span
                    class="text-indigo-600">Pro</span></span>
        </div>

        <div class="flex items-center gap-6">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="text-sm font-black uppercase tracking-widest text-slate-500 hover:text-indigo-600 transition-colors">Workspace</a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-black uppercase tracking-widest text-slate-500 hover:text-indigo-600 transition-colors">Access
                        Hub</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl">Join
                            Nexus</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="relative px-8 pt-20 pb-40 overflow-hidden">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-20">
            <div class="lg:w-1/2 space-y-10">
                <div
                    class="inline-flex items-center gap-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black uppercase tracking-widest border border-indigo-100">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Next Generation Learning
                </div>

                <h1 class="hero-title">
                    Empowering <br>
                    <span class="text-slate-800">Academic</span> <br>
                    Excellence.
                </h1>

                <p class="text-xl text-slate-500 font-medium max-w-lg leading-relaxed">
                    A premium management ecosystem designed for modern institutions. Seamlessly bridge the gap between
                    administration, educators, and scholars.
                </p>

                <div class="flex items-center gap-6">
                    <a href="{{ route('login') }}"
                        class="btn-glow px-10 py-5 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl shadow-indigo-500/40">
                        Primary Initialization
                    </a>
                    <a href="#"
                        class="px-10 py-5 bg-white text-slate-800 rounded-2xl text-xs font-black uppercase tracking-widest border border-slate-100 hover:bg-slate-50 transition-all shadow-sm">
                        View Matrix
                    </a>
                </div>
            </div>

            <div class="lg:w-1/2 relative">
                <div class="premium-card p-10 floating relative z-10">
                    <div class="grid grid-cols-2 gap-6">
                        <div
                            class="aspect-square rounded-3xl bg-indigo-50 flex flex-col items-center justify-center gap-4 text-indigo-600 p-8 border border-indigo-100">
                            <i class="bi bi-people-fill text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Scholars</span>
                            <span class="text-3xl font-black">12.5k</span>
                        </div>
                        <div
                            class="aspect-square rounded-3xl bg-rose-50 flex flex-col items-center justify-center gap-4 text-rose-600 p-8 border border-rose-100">
                            <i class="bi bi-journal-check text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Courses</span>
                            <span class="text-3xl font-black">482</span>
                        </div>
                        <div
                            class="aspect-square rounded-3xl bg-emerald-50 flex flex-col items-center justify-center gap-4 text-emerald-600 p-8 border border-emerald-100">
                            <i class="bi bi-award-fill text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Faculty</span>
                            <span class="text-3xl font-black">1.2k</span>
                        </div>
                        <div
                            class="aspect-square rounded-3xl bg-amber-50 flex flex-col items-center justify-center gap-4 text-amber-600 p-8 border border-amber-100">
                            <i class="bi bi-calendar-event-fill text-4xl"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">Events</span>
                            <span class="text-3xl font-black">94</span>
                        </div>
                    </div>
                </div>

                <!-- Decorative Blobs -->
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-rose-200/30 rounded-full blur-[100px] -z-10"></div>
                <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-indigo-200/30 rounded-full blur-[100px] -z-10">
                </div>
            </div>
        </div>
    </main>

    <!-- Content Sections -->
    <section class="max-w-7xl mx-auto px-8 pb-40">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="premium-card p-10 space-y-6">
                <div
                    class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center border border-indigo-100">
                    <i class="bi bi-command text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight leading-none">Unified Interface
                </h3>
                <p class="text-slate-500 font-medium">Single digital identity for all institutional needs. Command and
                    control with absolute precision.</p>
            </div>

            <div class="premium-card p-10 space-y-6">
                <div
                    class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center border border-rose-100">
                    <i class="bi bi-shield-check text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight leading-none">Encrypted Core</h3>
                <p class="text-slate-500 font-medium">Enterprise grade security protocols protecting academic integrity
                    and scholar privacy.</p>
            </div>

            <div class="premium-card p-10 space-y-6">
                <div
                    class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center border border-emerald-100">
                    <i class="bi bi-lightning-charge-fill text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight leading-none">Instant Analytics
                </h3>
                <p class="text-slate-500 font-medium">Real-time data streams providing deep insights into institutional
                    performance metrics.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="max-w-7xl mx-auto px-8 py-20 border-t border-slate-100 text-center">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400">
            &copy; {{ date('Y') }} EduPortal Pro Engineering. All Systems Nominal.
        </p>
    </footer>
</body>

</html>