@extends('layouts.app')

@section('header_title', 'Schedule Event')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">New Event</h2>
            <p class="text-sm text-slate-400 mt-1">Schedule a new campus event or activity.</p>
        </div>
        <a href="{{ route('admin.events.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-sm text-rose-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Event Details</h3>
                        <p class="text-xs text-slate-400">Fill in the event information.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Event
                            Title</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Annual Tech Fest 2024" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Event
                            Date</label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Main Auditorium">
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all resize-none"
                            placeholder="Describe the event...">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="reset"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Reset</button>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-rose-500 to-rose-600 rounded-xl hover:shadow-lg hover:shadow-rose-500/25 transition-all">Create
                        Event</button>
                </div>
            </form>
        </div>
    </div>
@endsection