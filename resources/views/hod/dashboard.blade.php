@extends('layouts.app')

@section('header_title', 'HOD Dashboard')

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
    
    <!-- ─── Welcome Header ─── -->
    <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 rounded-2xl p-8 mb-8 relative overflow-hidden shadow-xl shadow-indigo-500/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-2xl font-extrabold text-white mb-1">Welcome, Head of Department</h2>
                <div class="flex items-center gap-3">
                    <span class="text-indigo-100 text-sm font-medium">{{ $department->name }} Division</span>
                    <span class="h-1 w-1 rounded-full bg-indigo-300"></span>
                    <span class="text-indigo-100 text-sm font-medium">Academic Management Workspace</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @canPage('hod.timetable.index')
                <a href="{{ route('hod.timetable.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-xl text-white text-xs font-bold border border-white/20 transition-all flex items-center gap-2">
                    <i class="bi bi-calendar3"></i> Timetable Manager
                </a>
                @endcanPage
                @canPage('hod.teacher-assignments.index')
                <a href="{{ route('hod.teacher-assignments.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-xl text-white text-xs font-bold border border-white/20 transition-all flex items-center gap-2">
                    <i class="bi bi-person-badge"></i> Faculty Allocations
                </a>
                @endcanPage
            </div>
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute right-20 -bottom-10 w-32 h-32 bg-blue-400/20 rounded-full blur-3xl"></div>
    </div>

    <!-- ─── Statistics Grid ─── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach([
            ['label' => 'Total Faculty', 'value' => $stats['teachers'], 'icon' => 'bi-mortarboard-fill', 'tone' => 'blue', 'route' => 'hod.teacher-assignments.index'],
            ['label' => 'Total Students', 'value' => $stats['students'], 'icon' => 'bi-people-fill', 'tone' => 'violet', 'route' => null],
            ['label' => 'Pending Leaves', 'value' => $stats['pending_leaves'], 'icon' => 'bi-calendar2-x-fill', 'tone' => 'amber', 'route' => 'hod.leaves.index'],
            ['label' => 'Active Notices', 'value' => $stats['active_notices'], 'icon' => 'bi-megaphone-fill', 'tone' => 'emerald', 'route' => 'hod.notices.index'],
        ] as $stat)
            <div class="stat-card group">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-11 w-11 rounded-xl bg-{{ $stat['tone'] }}-50 text-{{ $stat['tone'] }}-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $stat['value'] }}</h3>
                    @if($stat['route'] && Auth::user()->canPage($stat['route']))
                        <a href="{{ route($stat['route']) }}" class="text-[10px] font-bold text-{{ $stat['tone'] }}-600 hover:underline">Manage</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- ─── Main Content Grid ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ─── Notices Section ─── -->
        <div class="lg:col-span-1 glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-megaphone-fill text-indigo-500"></i> Department Notices
                </h3>
                <a href="{{ route('hod.notices.index') }}" class="text-[11px] font-bold text-indigo-600 hover:underline">New Notice</a>
            </div>
            <div class="p-4 space-y-4">
                @forelse($notices as $notice)
                    <button type="button" 
                        @click="openNotice({
                            title: @js($notice->title),
                            content: @js($notice->content),
                            date: @js($notice->created_at->format('M d, Y h:i A'))
                        })"
                        class="w-full text-left p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-100 transition-all group">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 truncate">{{ $notice->title }}</h4>
                            <span class="text-[9px] font-bold text-slate-400 whitespace-nowrap">{{ $notice->created_at->format('M d') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 line-clamp-1 italic">"{{ Str::limit($notice->content, 60) }}"</p>
                    </button>
                @empty
                    <div class="py-8 flex flex-col items-center opacity-30">
                        <i class="bi bi-journal-x text-4xl"></i>
                        <p class="text-[11px] font-bold uppercase mt-2">No active notices</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ─── Pending Leaves Section ─── -->
        <div class="lg:col-span-2 glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-calendar2-check-fill text-amber-500"></i> Pending Approvals
                </h3>
                @canPage('hod.leaves.index')
                <a href="{{ route('hod.leaves.index') }}" class="text-[11px] font-bold text-amber-600 hover:underline">View All Requests</a>
                @endcanPage
            </div>
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Request Period</th>
                            <th>Reason Preview</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLeaves as $leave)
                            <tr>
                                <td>
                                    <p class="font-black text-slate-800 leading-tight">{{ $leave->leaveable?->user?->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $leave->leave_type }}</p>
                                </td>
                                <td>
                                    <p class="text-[11px] font-bold text-slate-600">{{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d') }}</p>
                                    <p class="text-[10px] text-slate-400 italic">Duration: {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} Days</p>
                                </td>
                                <td class="text-xs text-slate-500 line-clamp-1 max-w-[200px]">
                                    {{ $leave->reason }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('hod.leaves.index') }}" class="btn-outline py-1 px-3 text-[10px] font-black h-auto">Quick Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center opacity-30">
                                    <i class="bi bi-shield-check text-4xl"></i>
                                    <p class="text-[11px] font-bold uppercase mt-2">All leave requests processed</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ─── Notice Modal ─── -->
    <div x-show="noticeModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
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
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="relative w-full max-w-2xl rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-900" x-text="activeNotice.title"></h3>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mt-1" x-text="activeNotice.date"></p>
                    </div>
                    <button type="button" @click="closeNotice()" class="h-10 w-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <p class="text-sm leading-7 text-slate-600 whitespace-pre-line" x-text="activeNotice.content"></p>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                    <button type="button" @click="closeNotice()" class="btn-primary-gradient px-8">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
