@extends('layouts.app')

@section('header_title', 'Teacher Dashboard')

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
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-600 rounded-2xl p-8 mb-8 relative overflow-hidden shadow-xl shadow-violet-500/20 animate-fade-in">
        <div class="relative z-10">
            <h2 class="text-2xl font-extrabold text-white mb-1">Welcome back, {{ explode(' ', $user->name)[0] }}</h2>
            <p class="text-violet-200 text-sm font-medium">
                {{ $teacher->department->name ?? 'Department' }} Faculty <span class="mx-2">â€¢</span> {{ $todayClasses }} Classes Today
            </p>
        </div>
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute right-20 -bottom-10 w-32 h-32 bg-fuchsia-400/20 rounded-full blur-3xl"></div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8 animate-fade-in" style="animation-delay: 100ms;">
        @canPage('teacher.schedule.index')
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-book-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assigned Subjects</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $subjects->count() }}</h3>
        </div>
        @endcanPage

        @canPage('teacher.assignments.index')
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Assignments</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $assignments->count() }}</h3>
        </div>
        @endcanPage

        @canPage('teacher.schedule.index')
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Today Classes</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $todayClasses }}</h3>
        </div>
        @endcanPage

        @canPage('teacher.results.index')
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-4">
                <div class="h-11 w-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="bi bi-award-fill"></i>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Internal Marks</p>
            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Active</h3>
        </div>
        @endcanPage
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in" style="animation-delay: 200ms;">
        @canPage('teacher.schedule.index')
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
                        <p class="text-xs text-slate-400 mt-1">{{ $subject->course->name ?? 'N/A' }} <span class="mx-1">â€¢</span> Sem {{ $subject->semester_sequence }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No assigned subjects.</p>
                @endforelse
            </div>
        </div>
        @endcanPage

        @canPage('teacher.notices.index')
        <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-megaphone-fill text-rose-500"></i> Faculty Notices
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($notices as $notice)
                <button type="button"
                        @click="openNotice({
                            title: @js($notice->title),
                            content: @js($notice->content),
                            date: @js($notice->created_at->format('M d, Y h:i A'))
                        })"
                        class="w-full text-left flex items-start gap-4 group p-3 rounded-xl hover:bg-rose-50/50 transition-colors">
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
                </button>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No notices available.</p>
                @endforelse
            </div>
        </div>
        @endcanPage
    </div>

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
                    <button type="button"
                            @click="closeNotice()"
                            class="h-10 w-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-6">
                    <p class="text-sm leading-7 text-slate-600 whitespace-pre-line" x-text="activeNotice.content"></p>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end">
                    <button type="button"
                            @click="closeNotice()"
                            class="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
