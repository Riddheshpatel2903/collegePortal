@extends('layouts.app')

@section('header_title', 'Notice Board')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Notice Board</h2>
        <p class="text-sm text-slate-400 mt-1">Official announcements and campus updates.</p>
    </div>

    <div class="space-y-4">
        @forelse($notices as $notice)
            <div class="glass-card p-6 border-l-4 border-l-violet-500 flex flex-col md:flex-row gap-6 group">
                <!-- Date Block -->
                <div
                    class="flex-shrink-0 flex flex-col items-center justify-center w-20 py-4 bg-violet-50 rounded-xl text-center">
                    <span class="text-[10px] font-bold text-violet-500 uppercase">{{ $notice->created_at->format('M') }}</span>
                    <span
                        class="text-2xl font-extrabold text-slate-800 leading-none">{{ $notice->created_at->format('d') }}</span>
                    <span class="text-[9px] font-semibold text-slate-400 mt-0.5">{{ $notice->created_at->format('Y') }}</span>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="gradient-badge bg-violet-50 text-violet-600">{{ ucfirst($notice->target_role) }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-violet-600 transition-colors">
                        {{ $notice->title }}
                    </h3>
                    <p class="text-sm text-slate-400 leading-relaxed line-clamp-2">
                        {{ $notice->content }}
                    </p>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5">
                            <i class="bi bi-person-circle text-violet-500"></i> {{ $notice->user->name ?? 'System' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card p-8 text-center">
                <p class="text-sm text-slate-400">No notices available.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notices->links() }}</div>
@endsection
