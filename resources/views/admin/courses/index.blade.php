@extends('layouts.app')

@section('header_title', 'Program Nexus')

@section('content')
    <x-page-header 
        title="Program Nexus" 
        subtitle="Orchestrate academic degrees, departmental affiliations, and long-term curriculum trajectories."
        icon="bi-journal-bookmark-fill"
        actionLabel="Integrate Program"
        actionIcon="bi-plus-lg"
        actionRoute="{{ route('admin.courses.create') }}"
    />

    <!-- ─── Search & Discovery ─── -->
    <div class="mb-8 sticky top-20 z-30">
        <x-card class="p-2 border border-white/60 shadow-2xl">
            <form method="GET" id="courseSearchForm" class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-[300px] h-12 group">
                    <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Scan programs by name, code, or department..."
                        class="input-premium pl-12 h-full" autocomplete="off">
                </div>
                <x-button variant="outline" href="{{ route('admin.courses.index') }}" icon="bi-arrow-counterclockwise" class="h-12 w-12 !p-0"></x-button>
            </form>
        </x-card>
    </div>

    <!-- ─── Program Architecture Table ─── -->
    <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Academic Program</th>
                        <th>Departmental Node</th>
                        <th class="text-center">Lifecycle Duration</th>
                        <th class="text-right">Nexus Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 flex items-center justify-center text-xs font-black shadow-sm border border-indigo-100">
                                        {{ strtoupper(substr($course->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="text-sm font-black text-slate-800 block group-hover:text-indigo-600 transition-colors">{{ $course->name }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Code: #PRG-{{ str_pad($course->id, 3, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-100 italic">
                                    <i class="bi bi-building mr-1"></i> {{ $course->department->name ?? 'Global Nexus' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-200">
                                    {{ $course->duration_years }} Academic Cycles
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 pr-4">
                                    <a href="{{ route('admin.courses.edit', $course->id) }}"
                                        class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100"><i
                                            class="bi bi-pencil-square text-sm"></i></a>
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Purge this academic program?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-9 w-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100"><i
                                                class="bi bi-trash3-fill text-sm"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-20 text-center opacity-30">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-journal-x text-5xl mb-4"></i>
                                    <p class="text-[11px] font-black uppercase tracking-widest">No Programs Materialised</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            {{ $courses->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        let courseDebounce = null;
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = document.getElementById('courseSearchForm');
        
        searchInput.addEventListener('input', function () {
            clearTimeout(courseDebounce);
            courseDebounce = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    </script>
    @endpush
@endsection
