@extends('layouts.app')

@section('header_title', 'Add Semester')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Add Semester</h2>
        <a href="{{ route('admin.semesters.index') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card p-8 max-w-3xl">
        <form method="POST" action="{{ route('admin.semesters.store') }}" class="space-y-5">
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
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Name</label>
                <input type="text" name="name" placeholder="e.g. Semester 1" required
                    value="{{ old('name') }}"
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Course</label>
                    <select name="course_id" required
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Academic Session</label>
                    <select name="academic_session_id" required
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}"
                                @selected(old('academic_session_id', $currentSessionId) == $session->id)>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($sessions->isEmpty())
                        <p class="text-xs text-amber-600 mt-2">No academic sessions found. Create a session first.</p>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Semester Number</label>
                    <input type="number" name="semester_number" placeholder="e.g. 1" min="1" required
                        value="{{ old('semester_number') }}"
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" required
                        value="{{ old('start_date') }}"
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" required
                        value="{{ old('end_date') }}"
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" required
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <option value="upcoming" @selected(old('status') === 'upcoming')>Upcoming</option>
                        <option value="active" @selected(old('status') === 'active')>Active</option>
                        <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_current" value="1"
                        class="rounded border-slate-300"
                        @checked(old('is_current'))>
                    Mark as current semester
                </label>
            </div>
            <button type="submit"
                class="px-8 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
                Create Semester
            </button>
        </form>
    </div>
@endsection
