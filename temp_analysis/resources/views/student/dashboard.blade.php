@extends('layouts.app')

@section('header_title', 'Student Dashboard')

@section('content')
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 rounded-2xl p-8 mb-8 relative overflow-hidden shadow-xl shadow-violet-500/20 animate-fade-in">
        <div class="relative z-10">
            <h2 class="text-2xl font-extrabold text-white mb-1">Welcome back, {{ explode(' ', $user->name)[0] }}</h2>
            <p class="text-violet-200 text-sm font-medium">
                {{ $student->course->name ?? 'Course' }} <span class="mx-2">•</span> Year {{ $student->current_year }} <span class="mx-2">•</span> Sem {{ $student->current_semester_number ?? 'N/A' }}
            </p>
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute right-20 -bottom-10 w-32 h-32 bg-fuchsia-400/20 rounded-full blur-3xl"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 animate-fade-in" style="animation-delay: 100ms;">
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Attendance</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $attendancePercent }}%</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Pending Fees</p>
            <h3 class="text-2xl font-extrabold {{ $pendingFees > 0 ? 'text-orange-600' : 'text-slate-800' }} tracking-tight">₹{{ number_format($pendingFees, 2) }}</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assignments Due</p>
            <h3 class="text-2xl font-extrabold {{ $assignmentsDue > 0 ? 'text-rose-600' : 'text-slate-800' }} tracking-tight">{{ $assignmentsDue }}</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-book-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Subjects Enrolled</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $subjectCount }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in" style="animation-delay: 200ms;">
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-megaphone-fill text-violet-500"></i> Department Notices
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($notices as $notice)
                <div class="flex items-start gap-4 group p-3 rounded-xl hover:bg-violet-50/50 transition-colors">
                    <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-violet-500 ring-4 ring-violet-100 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h4 class="text-sm font-semibold text-slate-700 group-hover:text-violet-600 transition-colors truncate">
                                {{ $notice->title }}
                            </h4>
                            <span class="text-[10px] font-semibold text-slate-400 whitespace-nowrap">{{ $notice->created_at->format('M d') }}</span>
                        </div>
                        <p class="text-xs text-slate-400 line-clamp-2">{{ $notice->content }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No notices available.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-calendar-event-fill text-rose-500"></i> Upcoming Events
                </h3>
                <span class="text-xs font-semibold text-slate-400">Campus</span>
            </div>
            <div class="p-5 space-y-3">
                @forelse($events as $event)
                <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-rose-50/50 transition-colors group">
                    <div class="h-14 w-14 flex flex-col items-center justify-center bg-rose-50 rounded-xl text-rose-600 group-hover:bg-rose-100 transition-colors flex-shrink-0">
                        <span class="text-[9px] font-bold uppercase leading-none">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                        <span class="text-lg font-extrabold leading-none">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-700 group-hover:text-rose-600 transition-colors">{{ $event->title }}</h4>
                        <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                            <i class="bi bi-geo-alt-fill text-rose-400"></i> {{ $event->location }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No events scheduled.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
