@extends('layouts.app')

@section('header_title', 'Events')

@section('content')
    <x-page-header 
        title="Event Nexus" 
        subtitle="Orchestrate campus activities and schedules" 
        tag="Academic Life"
        icon="bi-calendar-event"
    >
        <x-slot:actions>
            <a href="{{ route('admin.events.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 hover:shadow-xl transition-all">
                <i class="bi bi-calendar-plus"></i> Schedule New Event
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($events as $event)
            <div class="glass-card p-6 flex items-center gap-6 group border-l-4 border-l-rose-500 hover:-translate-y-1 transition-all relative overflow-hidden">
                <div class="absolute top-0 right-0 p-3">
                    <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}"
                        onsubmit="return confirm('Hard delete this event entry?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-8 w-8 rounded-lg bg-rose-50 text-rose-400 opacity-0 group-hover:opacity-100 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-xs shadow-sm">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>

                <div class="flex-shrink-0 relative">
                    <div class="h-20 w-16 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 flex flex-col items-center justify-center text-white shadow-xl shadow-rose-500/20 group-hover:scale-110 transition-transform duration-500">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-80 leading-none mb-1">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                        <span class="text-3xl font-black leading-none tracking-tighter">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 border border-rose-100">Synchronized Event</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">• Nexus Hub</span>
                    </div>
                    <h4 class="text-base font-black text-slate-800 group-hover:text-rose-600 transition-colors truncate tracking-tight">
                        {{ $event->title }}
                    </h4>
                    <p class="text-xs text-slate-400 mt-1 line-clamp-1 font-medium leading-relaxed">{{ $event->description }}</p>
                    <div class="flex items-center gap-4 mt-4 py-2 border-t border-slate-50">
                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest flex items-center gap-1.5">
                            <i class="bi bi-geo-alt-fill text-rose-400"></i> {{ $event->location }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest flex items-center gap-1.5 ml-auto">
                            <i class="bi bi-clock-history text-rose-400"></i> {{ \Carbon\Carbon::parse($event->event_date)->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card p-20 text-center border-dashed border-2 border-slate-100">
                <i class="bi bi-calendar-x text-6xl text-slate-100 mb-6 block"></i>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No active scheduled transmissions</p>
            </div>
        @endforelse
    </div>
@endsection