@extends('layouts.app')

@section('header_title', 'Events')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Campus Events</h2>
            <p class="text-sm text-slate-400 mt-1">Manage upcoming events and campus activities.</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-rose-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Schedule Event
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($events as $event)
            <div
                class="glass-card p-6 flex items-center gap-6 group border-l-4 border-l-rose-500 hover:-translate-y-0.5 transition-all">
                <div class="flex-shrink-0 relative">
                    <div
                        class="h-20 w-16 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 flex flex-col items-center justify-center text-white shadow-lg shadow-rose-500/20 group-hover:scale-105 transition-transform">
                        <span
                            class="text-[9px] font-bold uppercase tracking-wider opacity-80">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                        <span
                            class="text-2xl font-extrabold leading-none">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="gradient-badge bg-rose-50 text-rose-600 mb-2 inline-block">Campus Event</span>
                    <h4 class="text-base font-bold text-slate-800 group-hover:text-rose-600 transition-colors truncate">
                        {{ $event->title }}</h4>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $event->description }}</p>
                    <div class="flex gap-5 mt-3">
                        <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1"><i
                                class="bi bi-geo-alt-fill text-rose-400"></i> {{ $event->location }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 pl-4 border-l border-slate-100">
                    <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}"
                        onsubmit="return confirm('Delete this event?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-9 w-9 rounded-lg bg-rose-50 text-rose-400 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-sm"><i
                                class="bi bi-trash3-fill"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card p-8 text-center">
                <p class="text-sm text-slate-400">No events found.</p>
            </div>
        @endforelse
    </div>
@endsection