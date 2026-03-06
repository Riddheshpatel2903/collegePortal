@extends('layouts.app')

@section('header_title', 'Faculty Notices')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Faculty Notices</h2>
            <p class="text-sm text-slate-400 mt-1">Important announcements and bulletins for teaching staff.</p>
        </div>
        <a href="{{ route('teacher.notices.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Post Notice
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($notices as $notice)
            <div class="glass-card p-6 group relative overflow-hidden">
                <div
                    class="absolute -right-4 -top-4 w-20 h-20 bg-violet-50 rounded-full blur-2xl group-hover:scale-150 transition-transform">
                </div>

                <div class="flex justify-between items-start mb-5 relative z-10">
                    <div
                        class="h-12 w-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/20 group-hover:scale-110 transition-transform">
                        <i class="bi bi-megaphone-fill text-lg"></i>
                    </div>
                    <span class="gradient-badge bg-violet-50 text-violet-600">{{ ucfirst($notice->target_role) }}</span>
                </div>

                <h3
                    class="text-base font-bold text-slate-800 mb-2 line-clamp-2 group-hover:text-violet-600 transition-colors relative z-10">
                    {{ $notice->title }}
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-5 line-clamp-3 relative z-10">
                    {{ $notice->content }}
                </p>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between relative z-10">
                    <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                        <i class="bi bi-calendar3 text-violet-400"></i> {{ $notice->created_at->format('M d, Y') }}
                    </span>
                    <span class="text-xs font-bold text-slate-500">By {{ $notice->user->name ?? 'System' }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-sm text-slate-400">No notices available.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notices->links() }}</div>
@endsection
