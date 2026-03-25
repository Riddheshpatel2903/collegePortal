@extends('layouts.app')

@section('header_title', 'Notices & Announcements')

@section('content')
    <x-page-header 
        title="Notice Nexus" 
        subtitle="Post and manage campus-wide announcements" 
        tag="Campus Hub"
        icon="bi-megaphone"
    >
        <x-slot:actions>
            <a href="{{ route('admin.notices.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 hover:shadow-xl transition-all">
                <i class="bi bi-plus-lg"></i> Post New Bulletin
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-4">
        @forelse($notices as $notice)
            <div class="glass-card p-6 border-l-4 border-l-violet-500 group relative hover:-translate-y-1 transition-all">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="h-14 w-14 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:bg-violet-600 group-hover:text-white transition-all duration-500">
                            <i class="bi bi-megaphone"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-violet-50 text-violet-600 border border-violet-100 italic">{{ ucfirst($notice->target_role) }}</span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <i class="bi bi-calendar3 text-violet-400"></i> {{ $notice->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <h4 class="text-base font-black text-slate-800 mb-1 group-hover:text-violet-600 transition-colors tracking-tight">
                            {{ $notice->title }}
                        </h4>
                        <p class="text-sm text-slate-400 line-clamp-2 leading-relaxed font-medium">
                            {{ $notice->content }}
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="h-5 w-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                {{ substr($notice->user->name ?? 'S', 0, 1) }}
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Post Origin: 
                                <span class="text-slate-600">{{ $notice->user->name ?? 'System' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('admin.notices.destroy', $notice->id) }}"
                            onsubmit="return confirm('Hard delete this bulletin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="h-10 w-10 flex items-center justify-center bg-white border border-slate-200 text-slate-400 rounded-xl hover:text-rose-500 hover:border-rose-200 transition-all shadow-sm">
                                <i class="bi bi-trash3 text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card p-20 text-center border-dashed border-2 border-slate-100">
                <i class="bi bi-broadcast text-6xl text-slate-100 mb-6 block"></i>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Signal Terminal Empty: No active notices</p>
            </div>
        @endforelse
    </div>
@endsection
