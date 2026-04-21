@extends('layouts.app')

@section('header_title', 'Student Matrix')

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

    <!-- ─── Academic Identity Header ─── -->
    <div class="mb-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                <i class="bi bi-mortarboard text-[120px] text-indigo-500"></i>
            </div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-[0.2em] border border-indigo-100">
                    <span class="h-2 w-2 rounded-full bg-indigo-500 mr-2 animate-pulse"></span>
                    Student Portal Active
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">Welcome back, {{ explode(' ', $user->name)[0] }}</h2>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-slate-400">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-journal-bookmark text-indigo-400"></i>
                            <span class="text-xs font-bold">{{ $student->course->name ?? 'Academic Program' }}</span>
                        </div>
                        <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar3 text-indigo-400"></i>
                            <span class="text-xs font-bold text-slate-600">Year {{ $student->current_year }}</span>
                        </div>
                        <div class="h-1 w-1 rounded-full bg-slate-300"></div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-hash text-indigo-400"></i>
                            <span class="text-xs font-bold text-slate-600">Semester {{ $student->current_semester_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 relative z-10 lg:pl-10 lg:border-l border-slate-100">
                @canPage('student.schedule.index')
                <a href="{{ route('student.schedule.index') }}" class="h-14 px-8 bg-slate-900 hover:bg-black text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-100 flex items-center gap-3 active:scale-95">
                    <i class="bi bi-calendar-range text-lg"></i>
                    Academic Schedule
                </a>
                @endcanPage
                @canPage('student.attendance.index')
                <a href="{{ route('student.attendance.index') }}" class="h-14 px-8 bg-white hover:bg-slate-50 text-indigo-600 rounded-2xl text-[11px] font-black uppercase tracking-widest border border-indigo-100 transition-all shadow-sm flex items-center gap-3 active:scale-95">
                    <i class="bi bi-check2-all text-lg"></i>
                    Attendance Registry
                </a>
                @endcanPage
            </div>
        </div>
    </div>

    <!-- ─── Core Metrics Matrix ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @canPage('student.attendance.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-indigo-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform">
                <i class="bi bi-person-check text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Engagement Rate</p>
            <div class="flex items-center gap-4">
                <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $attendancePercent }}%</h3>
                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $attendancePercent }}%"></div>
                </div>
            </div>
        </div>
        @endcanPage

        @canPage('student.fees.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-amber-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform text-amber-500">
                <i class="bi bi-currency-rupee text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Financial Status</p>
            <h3 class="text-3xl font-black {{ $pendingFees > 0 ? 'text-amber-600' : 'text-slate-800' }} tracking-tighter">
                ₹{{ number_format($pendingFees, 0) }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Pending Obligations</p>
        </div>
        @endcanPage

        @canPage('student.assignments.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-rose-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform text-rose-500">
                <i class="bi bi-journal-text text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Academic Workload</p>
            <h3 class="text-3xl font-black {{ $assignmentsDue > 0 ? 'text-rose-600' : 'text-slate-800' }} tracking-tighter">
                {{ $assignmentsDue }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Pending Submissions</p>
        </div>
        @endcanPage

        @canPage('student.schedule.index')
        <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-teal-200 transition-all group relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform text-teal-500">
                <i class="bi bi-collection text-8xl"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Curriculum Load</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $subjectCount }}</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase">Active Subjects</p>
        </div>
        @endcanPage
    </div>

    <!-- ─── Secondary Operations ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Notices Grid --}}
        @canPage('student.notices.index')
        <div class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm">
            <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-black text-slate-800 tracking-tight leading-none mb-1">Administrative Bulletins</h4>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Latest announcements for your department</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <i class="bi bi-megaphone"></i>
                </div>
            </div>
            <div class="p-4 space-y-2">
                @forelse($notices as $notice)
                <button type="button"
                    data-title="{{ $notice->title }}"
                    data-content="{{ $notice->content }}"
                    data-date="{{ $notice->created_at->format('M d, Y h:i A') }}"
                    @click="openNotice({ title: $el.dataset.title, content: $el.dataset.content, date: $el.dataset.date })"
                    class="w-full text-left p-6 rounded-3xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                    <div class="flex items-center justify-between mb-2">
                        <h5 class="text-sm font-black text-slate-700 group-hover:text-indigo-600 transition-colors uppercase tracking-tight truncate mr-4">
                            {{ $notice->title }}
                        </h5>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="text-[10px] font-black text-slate-400">{{ $notice->created_at->format('d M') }}</span>
                            <span class="text-[8px] font-bold text-slate-300 uppercase">{{ $notice->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed font-medium">"{{ Str::limit($notice->content, 140) }}"</p>
                </button>
                @empty
                <div class="py-20 flex flex-col items-center justify-center text-center opacity-30">
                    <i class="bi bi-inbox text-5xl mb-4"></i>
                    <p class="text-xs font-black uppercase tracking-widest">No Bulletins Intercepted</p>
                </div>
                @endforelse
            </div>
            @if($notices->isNotEmpty())
            <div class="p-8 border-t border-slate-50 bg-slate-50/30">
                <a href="{{ route('student.notices.index') }}" class="flex items-center justify-center gap-3 text-[11px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors">
                    Access Notice Archives <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>
        @endcanPage

        {{-- Events Registry --}}
        @canPage('student.notices.index')
        <div class="bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-xl text-white">
            <div class="px-10 py-8 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-black tracking-tight leading-none mb-1">Campus Engagements</h4>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Upcoming academic & co-curricular events</p>
                </div>
                <div class="h-10 w-10 rounded-xl bg-white/10 text-indigo-400 flex items-center justify-center border border-white/5">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @forelse($events as $event)
                <div class="flex items-center gap-8 p-6 rounded-3xl bg-white/5 border border-white/5 hover:bg-white/10 transition-all group">
                    <div class="h-16 w-16 flex flex-col items-center justify-center bg-white rounded-2xl text-slate-900 shadow-lg shrink-0 group-hover:scale-105 transition-transform">
                        <span class="text-[10px] font-black uppercase leading-none text-indigo-600">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                        <span class="text-2xl font-black leading-none mt-1">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="text-base font-black truncate group-hover:text-indigo-400 transition-colors">
                            {{ $event->title }}</h5>
                        <div class="flex items-center gap-6 mt-3 text-slate-500">
                            <span class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-geo-alt-fill text-indigo-500"></i> {{ $event->location }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-20 flex flex-col items-center justify-center text-center opacity-20">
                    <i class="bi bi-calendar-x text-5xl mb-4"></i>
                    <p class="text-xs font-black uppercase tracking-widest text-white">Registry empty for upcoming dates</p>
                </div>
                @endforelse
            </div>
        </div>
        @endcanPage
    </div>

    <!-- ─── Notice Detailing Modal ─── -->
    <div x-show="noticeModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
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
                    <button type="button" @click="closeNotice()"
                        class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors flex items-center justify-center">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>
                <div class="px-10 py-10 max-h-[60vh] overflow-y-auto">
                    <p class="text-sm leading-8 text-slate-600 whitespace-pre-line font-medium" x-text="activeNotice.content"></p>
                </div>
                <div class="px-10 py-8 border-t border-slate-50 bg-slate-50/50 flex justify-end">
                    <button type="button" @click="closeNotice()"
                        class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-lg shadow-slate-100">
                        Dismiss Detail
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection