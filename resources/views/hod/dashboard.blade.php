@extends('layouts.app')

@section('header_title', 'Departmental Oversight')

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
    
    <!-- ─── Departmental Identity Header ─── -->
    <div class="mb-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8 bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                <i class="bi bi-shield-shaded text-[120px] text-indigo-500"></i>
            </div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em]">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                    Faculty Governance Active
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2">Welcome, {{ explode(' ', auth()->user()->name)[0] }}</h2>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-slate-400">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-diagram-3 text-indigo-500"></i>
                            <span class="text-xs font-black uppercase text-slate-600">{{ $department->name }} Administration</span>
                        </div>
                        <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <i class="bi bi-check-circle-fill text-emerald-500"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Strategic Oversight Role</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 relative z-10 lg:pl-10 lg:border-l border-slate-100">
                @canPage('hod.timetable.index')
                <a href="{{ route('hod.timetable.index') }}" class="h-14 px-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-100 flex items-center gap-3 active:scale-95">
                    <i class="bi bi-calendar3 text-lg"></i>
                    Schedule Matrix
                </a>
                @endcanPage
                @canPage('hod.teacher-assignments.index')
                <a href="{{ route('hod.teacher-assignments.index') }}" class="h-14 px-8 bg-white hover:bg-slate-50 text-slate-700 rounded-2xl text-[11px] font-black uppercase tracking-widest border border-slate-200 transition-all shadow-sm flex items-center gap-3 active:scale-95">
                    <i class="bi bi-people text-lg"></i>
                    Faculty Allocation
                </a>
                @endcanPage
            </div>
        </div>
    </div>

    <!-- ─── Strategic Metric Matrix ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        @foreach([
            ['label' => 'Academic Faculty', 'value' => $stats['teachers'], 'icon' => 'bi-mortarboard', 'color' => 'indigo', 'route' => 'hod.teacher-assignments.index'],
            ['label' => 'Department Students', 'value' => $stats['students'], 'icon' => 'bi-people', 'color' => 'slate', 'route' => null],
            ['label' => 'Leave Requests', 'value' => $stats['pending_leaves'], 'icon' => 'bi-calendar-minus', 'color' => 'amber', 'route' => 'hod.leaves.index'],
            ['label' => 'Strategic Notices', 'value' => $stats['active_notices'], 'icon' => 'bi-megaphone', 'color' => 'rose', 'route' => 'hod.notices.index'],
        ] as $stat)
            <div class="bg-white border border-slate-200 rounded-[2rem] p-8 hover:border-{{ $stat['color'] }}-200 transition-all group relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-5 group-hover:scale-110 transition-transform">
                    <i class="bi {{ $stat['icon'] }} text-8xl"></i>
                </div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-3">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ $stat['value'] }}</h3>
                    @if($stat['route'] && Auth::user()->canPage($stat['route']))
                        <a href="{{ route($stat['route']) }}" class="text-[9px] font-black text-indigo-500 uppercase tracking-tight hover:underline ml-auto">Control Center</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- ─── Operational Grid ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Departmental Bulletins --}}
        <div class="lg:col-span-1 bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-sm flex flex-col">
            <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                <div>
                    <h4 class="text-xl font-black text-slate-800 tracking-tight">Bulletins</h4>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Live announcements</p>
                </div>
                <a href="{{ route('hod.notices.index') }}" class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-100 hover:scale-105 transition-transform">
                    <i class="bi bi-plus-lg"></i>
                </a>
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
                            <h5 class="text-sm font-black text-slate-700 group-hover:text-indigo-600 transition-colors truncate mr-4">{{ $notice->title }}</h5>
                            <span class="text-[10px] font-black text-slate-400 shrink-0">{{ $notice->created_at->format('d M') }}</span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-1 italic font-medium opacity-60">"{{ Str::limit($notice->content, 80) }}"</p>
                    </button>
                @empty
                    <div class="py-20 flex flex-col items-center justify-center text-center opacity-30">
                        <i class="bi bi-journal-x text-5xl mb-4"></i>
                        <p class="text-[11px] font-black uppercase tracking-widest">Registry Clear</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Strategic Approvals --}}
        <div class="lg:col-span-2 bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-xl text-white">
            <div class="px-10 py-8 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h4 class="text-xl font-black tracking-tight text-white">Pending Approvals</h4>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">Faculty & resource requests</p>
                </div>
                @canPage('hod.leaves.index')
                <a href="{{ route('hod.leaves.index') }}" class="text-[10px] font-black text-indigo-400 uppercase tracking-widest hover:underline">Full Registry</a>
                @endcanPage
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-white/5 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] border-b border-white/5">
                            <th class="px-10 py-6">Applicant Entity</th>
                            <th class="px-10 py-6">Operational Period</th>
                            <th class="px-10 py-6">Rationale Preview</th>
                            <th class="px-10 py-6 text-right">Engagement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($pendingLeaves as $leave)
                            <tr class="hover:bg-white/10 transition-colors">
                                <td class="px-10 py-6 text-white font-bold text-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center text-xs font-black border border-white/10 uppercase">
                                            {{ substr($leave->leaveable?->user?->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="block truncate max-w-[150px]">{{ $leave->leaveable?->user?->name }}</span>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-indigo-400">{{ $leave->leave_type }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-6">
                                    <span class="text-xs font-black text-slate-300">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }}</span>
                                    <span class="block text-[10px] text-slate-500 font-bold uppercase mt-1">{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} Working Days</span>
                                </td>
                                <td class="px-10 py-6 text-xs text-slate-400 font-medium truncate max-w-[200px]">
                                    {{ $leave->reason }}
                                </td>
                                <td class="px-10 py-6 text-right">
                                    <a href="{{ route('hod.leaves.index') }}" class="inline-flex h-10 px-6 bg-white text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-400 hover:text-white transition-all items-center">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-24 text-center opacity-20">
                                    <i class="bi bi-shield-check text-6xl block mb-4"></i>
                                    <p class="text-xs font-black uppercase tracking-widest text-white">All Strategic Requests Synchronized</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── Notice Detailer Overlay ─── -->
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
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-tight" x-text="activeNotice.title"></h3>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500 mt-2 flex items-center gap-2">
                            <i class="bi bi-clock"></i> Disseminated on <span x-text="activeNotice.date"></span>
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
                    <button type="button" @click="closeNotice()" class="h-14 px-10 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg active:scale-95">Dismiss Detail</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
