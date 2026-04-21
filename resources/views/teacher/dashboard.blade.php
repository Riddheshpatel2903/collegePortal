@extends('layouts.app')

@section('header_title', 'Faculty Command')

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

    <!-- ─── Faculty Identity Header ─── -->
    <div class="mb-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                <i class="bi bi-person-workspace text-[120px] text-indigo-500"></i>
            </div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] border border-indigo-100">
                    <span class="h-2 w-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></span>
                    Faculty Session Active
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">Welcome Back, {{ explode(' ', $user->name)[0] }}</h2>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-slate-400">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-diagram-2 text-indigo-500"></i>
                            <span class="text-xs font-black uppercase text-slate-600">{{ $teacher->department->name ?? 'Academic Faculty' }}</span>
                        </div>
                        <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock-history text-indigo-400"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Instructional Engagement: {{ $todayClasses }} Classes Today</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 relative z-10 lg:pl-10 lg:border-l border-slate-100">
                @canPage('teacher.schedule.index')
                <a href="{{ route('teacher.schedule.index') }}" class="h-14 px-8 bg-slate-900 hover:bg-black text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-100 flex items-center gap-3 active:scale-95">
                    <i class="bi bi-calendar3 text-lg"></i>
                    Academic Schedule
                </a>
                @endcanPage
                @canPage('teacher.assignments.index')
                <a href="{{ route('teacher.assignments.index') }}" class="h-14 px-8 bg-white hover:bg-slate-50 text-indigo-600 rounded-2xl text-[11px] font-black uppercase tracking-widest border border-indigo-100 transition-all shadow-sm flex items-center gap-3 active:scale-95">
                    <i class="bi bi-journal-plus text-lg"></i>
                    New Assignment
                </a>
                @endcanPage
            </div>
        </div>
    </div>

    <!-- ─── Instructional Metrics ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @canPage('teacher.schedule.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-indigo-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform">
                <i class="bi bi-book text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Course Load</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $subjects->count() }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Assigned Subjects</p>
        </div>
        @endcanPage

        @canPage('teacher.assignments.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-teal-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform text-teal-500">
                <i class="bi bi-journal-text text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Assessments</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $assignments->count() }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Active Assignments</p>
        </div>
        @endcanPage

        @canPage('teacher.schedule.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-amber-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform text-amber-500">
                <i class="bi bi-calendar-check text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Daily Engagement</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $todayClasses }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Classes Today</p>
        </div>
        @endcanPage

        @canPage('teacher.results.index')
        <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-8 hover:border-indigo-500 transition-all group relative overflow-hidden text-white">
            <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform text-indigo-400">
                <i class="bi bi-award text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">System Status</p>
            <h3 class="text-3xl font-black tracking-tighter">Active</h3>
            <p class="text-[10px] font-bold text-indigo-400 mt-2 uppercase tracking-widest">Results Module Open</p>
        </div>
        @endcanPage
    </div>

    <!-- ─── Secondary Operations Grid ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Instructional Roster --}}
        @canPage('teacher.schedule.index')
        <div class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-1">Instructional Roster</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Your assigned academic modules</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($subjects as $subject)
                <div class="flex items-center gap-6 p-5 rounded-3xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-lg shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                        S{{ $subject->semester_sequence }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="text-base font-black text-slate-800 truncate group-hover:text-indigo-600 transition-colors">
                            {{ $subject->name }}
                        </h5>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">
                            {{ $subject->course->name ?? 'N/A' }} <span class="mx-2 text-slate-200">|</span> Sem {{ $subject->semester_sequence }}
                        </p>
                    </div>
                </div>
                @empty
                <div class="py-20 flex flex-col items-center justify-center text-center opacity-30">
                    <i class="bi bi-journal-x text-5xl mb-4"></i>
                    <p class="text-xs font-black uppercase tracking-widest">No Assigned Modules</p>
                </div>
                @endforelse
            </div>
            @if($subjects->isNotEmpty())
            <div class="p-8 border-t border-slate-50 bg-slate-50/30">
                <a href="{{ route('teacher.schedule.index') }}" class="flex items-center justify-center gap-3 text-[11px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors">
                    Access Full Schedule Matrix <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>
        @endcanPage

        {{-- Faculty Bulletins --}}
        @canPage('teacher.notices.index')
        <div class="rounded-[2.5rem] overflow-hidden shadow-2xl text-white border border-indigo-800/50" style="background-color: #1e1b4b !important;">
            <div class="px-10 py-8 border-b border-white/10 flex items-center justify-between bg-white/[0.02]">
                <div>
                    <h4 class="text-xl font-black tracking-tight leading-none mb-1">Faculty Bulletins</h4>
                    <p class="text-[10px] font-bold text-indigo-300/60 uppercase tracking-widest">Internal governance & alerts</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-white/10 text-indigo-300 flex items-center justify-center border border-white/10">
                    <i class="bi bi-megaphone"></i>
                </div>
            </div>
            <div class="p-6 space-y-3">
                @forelse($notices as $notice)
                <button type="button"
                        data-title="{{ $notice->title }}"
                        data-content="{{ $notice->content }}"
                        data-date="{{ $notice->created_at->format('M d, Y h:i A') }}"
                        @click="openNotice({ title: $el.dataset.title, content: $el.dataset.content, date: $el.dataset.date })"
                        class="w-full text-left p-6 rounded-[2rem] bg-white/[0.05] border border-white/5 hover:bg-white/[0.1] hover:border-white/10 transition-all group relative overflow-hidden focus:outline-none focus:ring-2 focus:ring-indigo-500/50" style="background-color: rgba(255, 255, 255, 0.05) !important;">
                    <div class="flex items-center justify-between mb-3 relative z-10">
                        <h5 class="text-sm font-black text-white group-hover:text-indigo-400 transition-colors truncate mr-4 uppercase tracking-tight">
                            {{ $notice->title }}
                        </h5>
                        <span class="px-2.5 py-1 bg-white/10 rounded-lg text-[9px] font-black text-indigo-200 shrink-0 uppercase tracking-widest">{{ $notice->created_at->format('d M') }}</span>
                    </div>
                    <p class="text-xs text-indigo-100/60 line-clamp-2 leading-relaxed font-medium relative z-10">"{{ Str::limit($notice->content, 120) }}"</p>
                </button>
                @empty
                <div class="py-24 flex flex-col items-center justify-center text-center opacity-20">
                    <i class="bi bi-megaphone-fill text-6xl mb-4"></i>
                    <p class="text-xs font-black uppercase tracking-widest text-white">Registry Synchronized: No Alerts</p>
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
                 class="relative w-full max-w-2xl rounded-[2.5rem] border border-slate-200 bg-white shadow-2xl overflow-hidden animate-in zoom-in duration-300">
                <div class="px-10 py-8 border-b border-slate-50 flex items-start justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight" x-text="activeNotice.title"></h3>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mt-2 flex items-center gap-2">
                            <i class="bi bi-clock"></i> Intercepted on <span x-text="activeNotice.date"></span>
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
                    <button type="button" @click="closeNotice()" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-lg shadow-slate-100">Dismiss Detail</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
