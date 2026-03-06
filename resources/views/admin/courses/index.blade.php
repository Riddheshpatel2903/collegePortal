@extends('layouts.app')

@section('header_title', 'Course Management')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">All Courses</h2>
            <p class="text-sm text-slate-400 mt-1">Manage academic programs, departments, and curriculum structure.</p>
        </div>
        <a href="{{ route('admin.courses.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Add Course
        </a>
    </div>

    <!-- Search -->
    <form method="GET" class="glass-card p-5 mb-6 flex flex-col md:flex-row gap-4 items-end" id="courseSearchForm">
        <div class="flex-1 w-full relative">
            <div class="relative">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by course name or department..."
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 pl-11 pr-4 text-sm text-slate-700 placeholder-slate-300 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
        </div>
        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-700 transition-colors">
            Search
        </button>
    </form>

    <!-- Table -->
    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Department</th>
                    <th class="text-center">Duration</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                    <tr class="group">
                        <td>
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($course->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-800">{{ $course->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span
                                class="gradient-badge bg-indigo-50 text-indigo-600">{{ $course->department->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-sm font-semibold text-slate-600">{{ $course->duration_years }} Years</span>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.courses.edit', $course->id) }}"><button
                                        class="h-8 w-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center hover:bg-violet-600 hover:text-white transition-all text-sm"><i
                                            class="bi bi-pencil-fill"></i></button></a>
                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all text-sm"><i
                                            class="bi bi-trash3-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-sm text-slate-400 py-8">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $courses->links() }}</div>
@endsection
