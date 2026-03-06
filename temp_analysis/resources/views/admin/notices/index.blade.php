@extends('layouts.app')

@section('header_title', 'Notices & Announcements')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Notices</h2>
            <p class="text-sm text-slate-400 mt-1">Post and manage campus-wide announcements and alerts.</p>
        </div>
        <a href="{{ route('admin.notices.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-megaphone-fill"></i> Post Notice
        </a>
    </div>

    <div class="space-y-4">
        @forelse($notices as $notice)
            <div class="glass-card p-6 border-l-4 border-l-violet-500 group">
                <div class="flex flex-col lg:flex-row lg:items-center gap-5">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center text-lg group-hover:bg-violet-600 group-hover:text-white transition-all">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="gradient-badge bg-amber-50 text-amber-600">{{ ucfirst($notice->target_role) }}</span>
                            <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                                <i class="bi bi-calendar-event text-violet-400"></i> {{ $notice->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-slate-800 mb-1.5 group-hover:text-violet-600 transition-colors">
                            {{ $notice->title }}
                        </h4>
                        <p class="text-sm text-slate-400 line-clamp-2 leading-relaxed">
                            {{ $notice->content }}
                        </p>
                        <div class="mt-3">
                            <span class="gradient-badge bg-slate-50 text-slate-500">Posted by:
                                {{ $notice->user->name ?? 'System' }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('admin.notices.destroy', $notice->id) }}"
                            onsubmit="return confirm('Delete this notice?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="h-9 w-9 flex items-center justify-center bg-white border border-slate-200 text-slate-400 rounded-lg hover:text-rose-500 hover:border-rose-200 transition-all"><i
                                    class="bi bi-trash3-fill text-sm"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card p-8 text-center">
                <p class="text-sm text-slate-400">No notices found.</p>
            </div>
        @endforelse
    </div>
@endsection
