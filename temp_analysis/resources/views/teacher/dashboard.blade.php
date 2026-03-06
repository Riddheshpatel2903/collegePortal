@extends('layouts.app')

@section('header_title', 'Teacher Dashboard')

@section('content')
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 rounded-2xl p-8 mb-8 relative overflow-hidden shadow-xl shadow-violet-500/20 animate-fade-in">
        <div class="relative z-10">
            <h2 class="text-2xl font-extrabold text-white mb-1">Welcome back, {{ explode(' ', $user->name)[0] }}</h2>
            <p class="text-violet-200 text-sm font-medium">
                {{ $teacher->department->name ?? 'Department' }} Faculty <span class="mx-2">•</span> {{ $todayClasses }} Classes Today
            </p>
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute right-20 -bottom-10 w-32 h-32 bg-fuchsia-400/20 rounded-full blur-3xl"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 animate-fade-in" style="animation-delay: 100ms;">
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-book-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned Subjects</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $subjects->count() }}</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assignments</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $assignments->count() }}</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Today Classes</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $todayClasses }}</h3>
        </div>
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Internal Marks</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Active</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in" style="animation-delay: 200ms;">
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-book-fill text-violet-500"></i> My Subjects
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($subjects as $subject)
                <div class="flex items-start gap-4 group p-3 rounded-xl hover:bg-violet-50/50 transition-colors">
                    <div class="mt-0.5 h-10 w-10 rounded-xl bg-violet-100/50 text-violet-600 flex items-center justify-center font-bold text-sm tracking-tighter flex-shrink-0">
                        S{{ $subject->semester_sequence }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-slate-700 group-hover:text-violet-600 transition-colors truncate">
                            {{ $subject->name }}
                        </h4>
                        <p class="text-xs text-slate-400 mt-1">{{ $subject->course->name ?? 'N/A' }} <span class="mx-1">•</span> Sem {{ $subject->semester_sequence }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No assigned subjects.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-megaphone-fill text-rose-500"></i> Faculty Notices
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($notices as $notice)
                <div class="flex items-start gap-4 group p-3 rounded-xl hover:bg-rose-50/50 transition-colors">
                    <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-rose-500 ring-4 ring-rose-100 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <h4 class="text-sm font-semibold text-slate-700 group-hover:text-rose-600 transition-colors truncate">
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
    </div>
@endsection
