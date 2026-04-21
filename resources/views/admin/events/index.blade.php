@extends('layouts.app')

@section('header_title', 'Event Management')

@section('content')
    <x-page-header 
        title="Event Management" 
        subtitle="Schedule and oversee institutional activities, seminars, and campus events." 
        icon="bi-calendar-event"
    >
        <x-slot:action>
            <a href="{{ route('admin.events.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                <i class="bi bi-calendar-plus"></i> Schedule New Event
            </a>
        </x-slot:action>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-8">
        @forelse($events as $event)
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm group hover:border-indigo-100 transition-all relative overflow-hidden">
                <div class="absolute top-4 right-4 z-10">
                    <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}"
                        onsubmit="return confirm('Delete this event permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="h-9 w-9 rounded-lg bg-rose-50 text-rose-500 opacity-0 group-hover:opacity-100 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center border border-rose-100 shadow-sm">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>

                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="h-20 w-16 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all duration-300">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-60 leading-none mb-1 group-hover:opacity-100">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                            <span class="text-3xl font-black leading-none tracking-tighter">{{ \Carbon\Carbon::parse($event->event_date)->format('d') }}</span>
                        </div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-indigo-50 text-indigo-600 border-indigo-100">Institutional</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($event->event_date)->format('h:i A') }}
                            </span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">
                            {{ $event->title }}
                        </h4>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1 font-medium leading-relaxed">{{ $event->description }}</p>
                        
                        <div class="mt-4 pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1.5 min-w-0 pr-2">
                                <i class="bi bi-geo-alt-fill text-indigo-400"></i> <span class="truncate">{{ $event->location }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border-2 border-dashed border-slate-100 rounded-3xl py-24 text-center">
                <div class="h-20 w-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">No Scheduled Events</h3>
                <p class="text-sm text-slate-500 font-medium">Academic calendar is currently clear. Schedule a new event to begin.</p>
            </div>
        @endforelse
    </div>
@endsection