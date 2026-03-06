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

    <div class="glass-card p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.semesters.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Name</label>
                <input type="text" name="name" placeholder="e.g. Semester 1" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Course</label>
                <select name="course_id" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Year</label>
                <input type="number" name="year" placeholder="e.g. 1" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <button type="submit"
                class="px-8 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
                Create Semester
            </button>
        </form>
    </div>
@endsection