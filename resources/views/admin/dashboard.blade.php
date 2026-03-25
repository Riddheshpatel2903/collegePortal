@extends('layouts.app')

@section('header_title', 'Universal Admin Nexus')

@section('content')
    <div x-data="{
        noticeModalOpen: false,
        activeNotice: { title: '', content: '', date: '' },
        openNotice(notice) {
            this.activeNotice = notice;
            this.noticeModalOpen = true;
        },
        closeNotice() {
            this.noticeModalOpen = false;
        }
    }" @keydown.escape.window="closeNotice()">
    
    <!-- ─── Universal Nexus Header ─── -->
    <div class="bg-gradient-to-r from-violet-950 via-indigo-900 to-slate-900 rounded-3xl p-10 mb-8 relative overflow-hidden shadow-2xl shadow-violet-500/10">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div>
                <h2 class="text-3xl font-black text-white mb-2 tracking-tight">Universal Admin Nexus</h2>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-2 px-3 py-1 bg-violet-500/20 rounded-full text-[10px] font-black text-violet-200 uppercase tracking-widest border border-violet-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse"></span>
                        Global Synchronization Active
                    </span>
                    <span class="text-violet-300/60 text-xs font-bold uppercase tracking-tighter">Institution Command Center</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                @canPage('admin.timetable-auto.index')
                <a href="{{ route('admin.timetable-auto.index') }}" class="btn-primary-gradient px-6 py-3 h-auto text-xs shadow-lg shadow-violet-500/20">
                    <i class="bi bi-magic mr-2"></i> Timetable Engine
                </a>
                @endcanPage
                @canPage('admin.settings.index')
                <a href="{{ route('admin.settings.index') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 backdrop-blur-md rounded-2xl text-white text-xs font-black border border-white/10 transition-all">
                    System Configuration
                </a>
                @endcanPage
            </div>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-violet-500/10 rounded-full blur-3xl"></div>
        <div class="absolute right-40 -bottom-20 w-48 h-48 bg-fuchsia-500/10 rounded-full blur-3xl"></div>
        <div class="absolute left-1/2 top-0 w-px h-full bg-gradient-to-b from-transparent via-white/5 to-transparent"></div>
    </div>

    <!-- ─── Global Statistics ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach([
            ['route' => 'admin.students.index', 'label' => 'Total Students', 'value' => number_format($studentCount), 'icon' => 'bi-people-fill', 'tone' => 'violet', 'desc' => 'Active Population'],
            ['route' => 'admin.teachers.index', 'label' => 'Total Teachers', 'value' => number_format($teacherCount), 'icon' => 'bi-mortarboard-fill', 'tone' => 'indigo', 'desc' => 'Faculty Nodes'],
            ['route' => 'admin.courses.index', 'label' => 'Active Courses', 'value' => number_format($courseCount), 'icon' => 'bi-journal-bookmark-fill', 'tone' => 'blue', 'desc' => 'Academic Programs'],
            ['route' => 'admin.fees.index', 'label' => 'Fiscal Health', 'value' => '₹' . number_format($feeTotal), 'icon' => 'bi-safe2-fill', 'tone' => 'emerald', 'desc' => 'Revenue Flow'],
        ] as $stat)
            @canPage($stat['route'])
                <a href="{{ route($stat['route']) }}" class="glass-card p-6 group hover:translate-y-[-4px] transition-all duration-300 border-b-4 border-{{ $stat['tone'] }}-500 shadow-xl shadow-slate-200/50">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-2xl bg-{{ $stat['tone'] }}-50 text-{{ $stat['tone'] }}-600 flex items-center justify-center text-xl group-hover:bg-{{ $stat['tone'] }}-600 group-hover:text-white transition-all duration-300 shadow-sm">
                            <i class="bi {{ $stat['icon'] }}"></i>
                        </div>
                        <i class="bi bi-arrow-up-right text-slate-300 group-hover:text-{{ $stat['tone'] }}-500 transition-colors"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stat['value'] }}</h3>
                    <p class="text-[9px] font-bold text-{{ $stat['tone'] }}-600 uppercase tracking-tighter mt-1">{{ $stat['desc'] }}</p>
                </a>
            @endcanPage
        @endforeach
    </div>

    <!-- ─── Activity Hub ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Notices Hub -->
        @canPage('admin.notices.index')
            <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/40">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="font-black text-slate-800 text-lg flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center text-sm">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>
                            Communication Hub
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 ml-11">Latest Broadcasts</p>
                    </div>
                    <a href="{{ route('admin.notices.index') }}" class="btn-outline px-4 py-2 text-[10px] font-black h-auto">Publish Notice</a>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($notices as $notice)
                        <button type="button"
                            @click="openNotice({
                                title: @js($notice->title),
                                content: @js($notice->content),
                                date: @js($notice->created_at->format('M d, Y h:i A'))
                            })"
                            class="w-full text-left flex items-start gap-4 group p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all">
                            <div class="mt-1 h-3 w-3 rounded-full bg-violet-500 ring-4 ring-violet-50 flex-shrink-0 animate-pulse"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3 mb-1">
                                    <h4 class="text-sm font-black text-slate-700 group-hover:text-violet-600 transition-colors truncate">{{ $notice->title }}</h4>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ $notice->created_at->format('M d') }}</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium line-clamp-1 italic">"{{ Str::limit($notice->content, 80) }}"</p>
                            </div>
                        </button>
                    @empty
                        <div class="py-16 flex flex-col items-center justify-center text-slate-300">
                            <i class="bi bi-inbox text-5xl mb-4 opacity-20"></i>
                            <p class="text-xs font-black uppercase tracking-widest opacity-50">Synchrony Quiet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcanPage

        <!-- Events Hub -->
        @canPage('admin.events.index')
            <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/40">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="font-black text-slate-800 text-lg flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            Scholastic Events
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 ml-11">Institutional Calendar</p>
                    </div>
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full uppercase tracking-widest border border-emerald-100 shadow-sm">Upcoming</span>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($events as $event)
                        <div class="flex items-center gap-5 p-4 rounded-2xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                            <div class="h-16 w-16 flex flex-col items-center justify-center bg-slate-900 rounded-2xl text-white group-hover:scale-105 transition-transform flex-shrink-0 shadow-lg shadow-slate-200">
                                <span class="text-[10px] font-black uppercase tracking-tighter opacity-70">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                                <span class="text-xl font-black leading-none mt-0.5">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-black text-slate-800 group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ $event->title }}</h4>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1.5">
                                        <i class="bi bi-geo-alt-fill text-emerald-500"></i> {{ $event->location }}
                                    </p>
                                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                    <p class="text-[10px] text-slate-400 font-bold flex items-center gap-1.5 uppercase">
                                        <i class="bi bi-clock-fill text-slate-300"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-16 flex flex-col items-center justify-center text-slate-300">
                            <i class="bi bi-calendar-x text-5xl mb-4 opacity-20"></i>
                            <p class="text-xs font-black uppercase tracking-widest opacity-50">Calendar Clear</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcanPage
    </div>

    <!-- ─── Notice Modal ─── -->
    <div x-show="noticeModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md"
             x-show="noticeModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeNotice()"></div>

        <div class="min-h-screen px-4 py-8 flex items-center justify-center">
            <div x-show="noticeModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                 class="relative w-full max-w-2xl rounded-[2.5rem] border border-slate-200 bg-white shadow-2xl overflow-hidden">
                <div class="px-8 py-8 border-b border-slate-100 flex items-start justify-between gap-6 bg-slate-50/50">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight" x-text="activeNotice.title"></h3>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-[9px] font-black uppercase tracking-widest border border-violet-200">Broadcast</span>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]" x-text="activeNotice.date"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeNotice()" class="h-12 w-12 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 hover:border-slate-300 transition-all shadow-sm">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="px-10 py-10">
                    <p class="text-base leading-relaxed text-slate-600 font-medium whitespace-pre-line text-justify" x-text="activeNotice.content"></p>
                </div>
                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="button" @click="closeNotice()" class="btn-primary-gradient px-12 py-3 h-auto text-sm">Acknowledge Notice</button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
