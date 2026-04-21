@extends('layouts.app')

@section('header_title', 'Notices & Announcements')

@section('content')
    <x-page-header 
        title="Bulletin Board" 
        subtitle="Post and manage campus-wide announcements and targeted faculty/student notices." 
        icon="bi-megaphone"
    >
        <x-slot:action>
            <a href="{{ route('admin.notices.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                <i class="bi bi-plus-lg"></i> Post New Notice
            </a>
        </x-slot:action>
    </x-page-header>

    <div class="space-y-6 mt-8">
        @forelse($notices as $notice)
            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm group hover:border-indigo-100 transition-all">
                <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center text-2xl border border-slate-100 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all duration-300">
                            <i class="bi bi- megaphone"></i>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-3 mb-3">
                            <span class="inline-flex items-center px-2 py-1 rounded bg-indigo-50 text-indigo-600 text-[9px] font-bold uppercase tracking-wider border border-indigo-100">
                                {{ $notice->target_role === 'all' ? 'Universal' : ucfirst($notice->target_role) }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="bi bi-calendar3"></i> Posted {{ $notice->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <h4 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-indigo-600 transition-colors">
                            {{ $notice->title }}
                        </h4>
                        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed font-medium">
                            {{ $notice->content }}
                        </p>
                        
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 font-bold border border-slate-200 uppercase">
                                {{ substr($notice->user->name ?? 'S', 0, 1) }}
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Publisher: <span class="text-slate-600">{{ $notice->user->name ?? 'System Admin' }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('admin.notices.destroy', $notice->id) }}" onsubmit="return confirm('Delete this notice permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="h-11 w-11 flex items-center justify-center bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm border border-rose-100">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 border-dashed rounded-3xl py-24 text-center">
                <div class="h-20 w-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                    <i class="bi bi-broadcast"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">No Active Notices</h3>
                <p class="text-sm text-slate-500 font-medium">Create a new post to broadcast information to the campus.</p>
            </div>
        @endforelse
    </div>
@endsection
