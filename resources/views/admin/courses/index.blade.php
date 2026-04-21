@extends('layouts.app')

@section('header_title', 'Course Management')

@section('content')
    <x-page-header 
        title="Course Management" 
        subtitle="Manage academic degrees, course durations, and department affiliations."
        icon="bi-journal-check"
        actionLabel="Add Course"
        actionIcon="bi-plus-lg"
        actionRoute="{{ route('admin.courses.create') }}"
    />

    <!-- ─── Search & Filters ─── -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-8 sticky top-20 z-30">
        <form method="GET" id="courseSearchForm" class="flex flex-wrap items-center gap-4">
            <div class="relative flex-1 min-w-[300px] h-11 group">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search by course name, code, or department..."
                    class="w-full h-full bg-slate-50 border-slate-100 rounded-xl pl-11 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium" autocomplete="off">
            </div>
            
            <button type="button" onclick="window.location.href='{{ route('admin.courses.index') }}'" class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all shadow-sm" title="Reset Filters">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>
    </div>

    <!-- ─── Courses Table ─── -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                        <th class="px-6 py-4">Academic Program</th>
                        <th class="px-6 py-4">Department</th>
                        <th class="px-6 py-4 text-center">Duration</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-black border border-indigo-100">
                                        {{ strtoupper(substr($course->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-slate-700 block group-hover:text-indigo-600 transition-colors">{{ $course->name }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: #{{ str_pad($course->id, 3, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-600 rounded-md text-[10px] font-bold uppercase border border-indigo-100 italic">
                                    <i class="bi bi-building mr-1.5"></i> {{ $course->department->name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-bold uppercase border border-slate-100">
                                    {{ $course->duration_years }} Years
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 pr-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Delete this program?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-24 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <i class="bi bi-journal-x text-5xl mb-4"></i>
                                    <p class="text-[10px] font-bold uppercase tracking-widest">No Courses Found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($courses->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $courses->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        let searchDebounce = null;
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = document.getElementById('courseSearchForm');
        
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    </script>
    @endpush
@endsection
