<header
    class="h-16 bg-white/70 backdrop-blur-xl border-b border-slate-200/50 flex items-center justify-between px-8 sticky top-0 z-40">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen"
            class="text-slate-400 hover:text-violet-600 lg:hidden transition-colors">
            <i class="bi bi-list text-2xl"></i>
        </button>
        <div class="flex flex-col">
            <h1 class="text-sm font-bold text-slate-800 tracking-tight leading-none mb-1">
                @yield('header_title', 'Dashboard')
            </h1>
            <div class="flex items-center gap-2">
                @php
                    $currentSession = \App\Models\AcademicSession::where('is_current', true)->first();
                @endphp
                @if($currentSession)
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-violet-50 text-violet-600 text-[9px] font-black uppercase tracking-widest border border-violet-100">
                        <i class="bi bi-calendar-event"></i>
                        {{ $currentSession->name }}
                    </span>
                @endif
                
                @if(auth()->user()->role === 'student' && auth()->user()->student && auth()->user()->student->currentSemester)
                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                        <i class="bi bi-mortarboard"></i>
                        {{ auth()->user()->student->currentSemester->name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-5">
        <!-- Notification Bell -->
        <button class="relative text-slate-400 hover:text-violet-600 transition-colors">
            <i class="bi bi-bell text-xl"></i>
            <span
                class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white animate-pulse"></span>
        </button>

        <!-- User Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 focus:outline-none group">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-slate-700 leading-none mb-1">{{ auth()->user()->name }}</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ auth()->user()->role }}</p>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff"
                    class="h-9 w-9 rounded-lg ring-2 ring-violet-100 group-hover:ring-violet-300 transition-all"
                    alt="User">
                <i
                    class="bi bi-chevron-down text-slate-400 text-xs group-hover:text-violet-600 transition-colors"></i>
            </button>

            <div x-show="open" @click.away="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 py-2 z-50"
                x-cloak>
                <div class="px-4 py-3 border-b border-slate-50">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Signed in as
                    </p>
                    <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->email }}</p>
                </div>
                @if(in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-violet-50 hover:text-violet-600 transition-colors">
                    <i class="bi bi-person-gear"></i> Profile Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                        <i class="bi bi-box-arrow-right"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
