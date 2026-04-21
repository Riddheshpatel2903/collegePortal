@extends('layouts.app')

@section('header_title', 'Central Command')

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
}" @keydown.escape.window="closeNotice()" class="space-y-10">

    <!-- ─── System Orchestration Header ─── -->
    <div class="mb-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                <i class="bi bi-cpu text-[120px] text-indigo-500"></i>
            </div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em]">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    Operational Core Optimized
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">Systems Overview</h2>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Administrative Context: {{ auth()->user()->name }} <span class="mx-3 opacity-20">|</span> {{ now()->format('l, M d Y') }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 relative z-10 lg:pl-10 lg:border-l border-slate-100">
                @canPage('admin.timetable-auto.index')
                <a href="{{ route('admin.timetable-auto.index') }}" class="h-14 px-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-100 flex items-center gap-3 active:scale-95">
                    <i class="bi bi-magic text-lg"></i>
                    Schedule Optimizer
                </a>
                @endcanPage
                @canPage('admin.settings.index')
                <a href="{{ route('admin.settings.index') }}" class="h-14 px-8 bg-white hover:bg-slate-50 text-slate-700 rounded-2xl text-[11px] font-black uppercase tracking-widest border border-slate-200 transition-all shadow-sm flex items-center gap-3 active:scale-95">
                    <i class="bi bi-gear-wide-connected text-lg"></i>
                    Control Center
                </a>
                @endcanPage
            </div>
        </div>
    </div>

    <!-- ─── Infrastructure Metrics ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @foreach([
            ['route' => 'admin.students.index', 'label' => 'Total Scholars', 'value' => number_format($studentCount), 'icon' => 'bi-people', 'color' => 'indigo'],
            ['route' => 'admin.teachers.index', 'label' => 'Academic Faculty', 'value' => number_format($teacherCount), 'icon' => 'bi-mortarboard', 'color' => 'slate'],
            ['route' => 'admin.courses.index', 'label' => 'Curriculum Units', 'value' => number_format($courseCount), 'icon' => 'bi-diagram-3', 'color' => 'indigo'],
            ['route' => 'admin.fees.index', 'label' => 'Registry Revenue', 'value' => '₹' . number_format($feeTotal), 'icon' => 'bi-bank', 'color' => 'slate'],
        ] as $stat)
            @canPage($stat['route'])
                <a href="{{ route($stat['route']) }}" class="bg-white border border-slate-200 p-8 rounded-[2rem] shadow-sm hover:border-indigo-200 transition-all group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform">
                        <i class="bi {{ $stat['icon'] }} text-8xl"></i>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="h-12 w-12 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center border border-slate-100 transition-transform group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600">
                            <i class="bi {{ $stat['icon'] }} text-xl"></i>
                        </div>
                        <i class="bi bi-arrow-up-right text-slate-200 group-hover:text-indigo-400 transition-colors"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $stat['label'] }}</p>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $stat['value'] }}</h3>
                </a>
            @endcanPage
        @endforeach
    </div>

    <!-- ─── Operational Command Grid ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Internal Bulletins --}}
        @canPage('admin.notices.index')
            <div class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm flex flex-col">
                <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-1 uppercase">Notices</h3>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Broadcast & Internal Comms</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <i class="bi bi-megaphone"></i>
                    </div>
                </div>
                <div class="p-4 space-y-2 flex-1">
                    @forelse($notices as $notice)
                        <button type="button"
                            data-title="{{ $notice->title }}"
                            data-content="{{ $notice->content }}"
                            data-date="{{ $notice->created_at->format('M d, Y h:i A') }}"
                            @click="openNotice({ title: $el.dataset.title, content: $el.dataset.content, date: $el.dataset.date })"
                            class="w-full text-left p-6 rounded-3xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-black text-slate-700 group-hover:text-indigo-600 transition-colors truncate mr-4 uppercase tracking-tight">{{ $notice->title }}</h4>
                                <span class="text-[10px] font-black text-slate-400 shrink-0">{{ $notice->created_at->format('M d') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 line-clamp-1 italic font-medium opacity-60">"{{ Str::limit($notice->content, 120) }}"</p>
                        </button>
                    @empty
                        <div class="py-24 text-center opacity-20">
                            <i class="bi bi-inbox text-5xl mb-4 block"></i>
                            <p class="text-[11px] font-black uppercase tracking-widest">No active notices intercepted</p>
                        </div>
                    @endforelse
                </div>
                @if($notices->isNotEmpty())
                    <div class="p-8 border-t border-slate-50 bg-slate-50/50">
                        <a href="{{ route('admin.notices.index') }}" class="flex items-center justify-center gap-3 text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] hover:text-indigo-800 transition-colors">
                            Manage Transmissions <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        @endcanPage

        {{-- Engagement Schedule --}}
        @canPage('admin.events.index')
            <div class="bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-xl text-white">
                <div class="px-10 py-8 border-b border-white/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-white tracking-tight leading-none mb-1 uppercase">Campus Events</h3>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-white/10 text-indigo-400 flex items-center justify-center border border-white/5">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($events as $event)
                        <div class="flex items-center gap-8 p-6 rounded-3xl bg-white/5 border border-white/5 hover:bg-white/10 transition-all group">
                            <div class="h-16 w-16 flex flex-col items-center justify-center bg-white rounded-2xl text-slate-900 shadow-xl shrink-0 transition-transform group-hover:scale-105">
                                <span class="text-[10px] font-black uppercase tracking-widest leading-none text-indigo-600">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                                <span class="text-2xl font-black leading-tight mt-1">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-black truncate group-hover:text-indigo-400 transition-colors uppercase tracking-tight">{{ $event->title }}</h4>
                                <div class="flex items-center gap-6 mt-3 text-slate-500">
                                    <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                        <i class="bi bi-geo-alt-fill text-indigo-500"></i> {{ $event->location }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-24 text-center opacity-20">
                            <i class="bi bi-calendar-x text-5xl mb-4 block"></i>
                            <p class="text-[11px] font-black uppercase tracking-widest text-white">Registry clear for upcoming events</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endcanPage
    </div>

    <!-- ─── Bulletin Detailing Modal ─── -->
    <div x-show="noticeModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity"
             x-show="noticeModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeNotice()"></div>

        <div class="min-h-screen px-4 py-12 flex items-center justify-center">
            <div x-show="noticeModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                 class="relative w-full max-w-2xl rounded-[2.5rem] border border-slate-200 bg-white shadow-2xl overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-50 flex items-start justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight uppercase" x-text="activeNotice.title"></h3>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mt-2 flex items-center gap-2">
                            <i class="bi bi-clock"></i> Captured on <span x-text="activeNotice.date"></span>
                        </p>
                    </div>
                    <button type="button" @click="closeNotice()" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors flex items-center justify-center">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>
                <div class="px-10 py-10 max-h-[60vh] overflow-y-auto font-medium text-slate-600 leading-8 text-sm whitespace-pre-line">
                    <p x-text="activeNotice.content"></p>
                </div>
                <div class="px-10 py-8 border-t border-slate-50 bg-slate-50/50 flex justify-end">
                    <button type="button" @click="closeNotice()" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-lg shadow-slate-100">Dismiss Insight</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
