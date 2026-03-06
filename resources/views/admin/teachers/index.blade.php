@extends('layouts.app')

@section('header_title', 'Faculty Management')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Faculty Directory</h2>
            <p class="text-sm text-slate-400 mt-1">View and manage teaching staff profiles and assignments.</p>
        </div>
        <a href="{{ route('admin.teachers.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-person-plus-fill"></i> Add Teacher
        </a>
    </div>
    <!-- Search Bar -->
    <form method="GET" class="mb-6" id="teacherSearchForm">
        <div class="relative max-w-md">
            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
            <input type="text" name="search" id="teacherSearch" value="{{ request('search') }}" placeholder="Search by name..."
                class="w-full pl-11 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 bg-white focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                autocomplete="off">
        </div>
    </form>

    <!-- Faculty Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="teacherGrid">
        @forelse($teachers as $teacher)
            <div class="glass-card p-6 group" data-name="{{ strtolower($teacher->user->name ?? '') }}">
                <div class="flex flex-col items-center text-center mb-5">
                    <div
                        class="h-20 w-20 rounded-2xl bg-gradient-to-br from-violet-100 to-purple-50 text-violet-600 flex items-center justify-center font-extrabold text-xl mb-4 group-hover:scale-110 transition-transform ring-4 ring-white shadow-lg">
                        {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}{{ strtoupper(substr(strrchr($teacher->user->name ?? 'T', ' ') ?: '', 1, 1)) }}
                    </div>
                    <h4 class="text-base font-bold text-slate-800 group-hover:text-violet-600 transition-colors">
                        {{ $teacher->user->name ?? 'N/A' }}
                    </h4>
                    <p class="text-xs font-medium text-violet-500 mt-0.5">{{ $teacher->department->name ?? 'N/A' }}</p>
                    <span class="gradient-badge bg-amber-50 text-amber-600 mt-2">{{ $teacher->qualification }}</span>
                </div>

                <div class="space-y-2 mb-5">
                    <div class="flex items-center justify-center gap-2 py-2 bg-slate-50 rounded-lg text-xs text-slate-500">
                        <i class="bi bi-envelope-fill text-slate-300"></i>
                        <span class="truncate">{{ $teacher->user->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <span class="gradient-badge bg-teal-50 text-teal-600">{{ $teacher->phone }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="flex flex-col items-center py-3 bg-violet-50/60 rounded-xl">
                        <span class="text-lg font-extrabold text-violet-600">{{ $teacher->subjects_count }}</span>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">Subjects</span>
                    </div>
                    <div class="flex flex-col items-center py-3 bg-amber-50/60 rounded-xl">
                        <span class="text-lg font-extrabold text-amber-600">{{ $teacher->assignments_count }}</span>
                        <span
                            class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">Assignments</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}"
                        class="py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 hover:bg-slate-700 transition-colors">
                        <i class="bi bi-pencil-fill text-[10px]"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}"
                        onsubmit="return confirm('Remove this teacher?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-2 bg-white border border-rose-200 text-rose-500 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all">
                            <i class="bi bi-trash3-fill text-[10px]"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-sm text-slate-400">No teachers found.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $teachers->links() }}</div>

    <script>
        let teacherDebounce = null;
        document.getElementById('teacherSearch').addEventListener('input', function () {
            clearTimeout(teacherDebounce);
            teacherDebounce = setTimeout(() => document.getElementById('teacherSearchForm').submit(), 400);
        });
    </script>
@endsection
