@extends('layouts.app')

@section('header_title', 'Department Nexus')

@section('content')
    <x-page-header 
        title="Departmental Nexus" 
        subtitle="Orchestrate academic domains, faculty allocation nodes, and cross-departmental synchronisation."
        icon="bi-building-fill"
        actionLabel="Integrate Domain"
        actionIcon="bi-plus-lg"
        actionRoute="{{ route('admin.departments.create') }}"
    />

    <!-- ─── Search & Discovery ─── -->
    <div class="mb-8 sticky top-20 z-30">
        <x-card class="p-2 border border-white/60 shadow-2xl">
            <form method="GET" id="deptSearchForm" class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-[300px] h-12 group">
                    <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Scan departmental nodes by name or description..."
                        class="input-premium pl-12 h-full" autocomplete="off">
                </div>
                <x-button variant="outline" href="{{ route('admin.departments.index') }}" icon="bi-arrow-counterclockwise" class="h-12 w-12 !p-0"></x-button>
            </form>
        </x-card>
    </div>

    <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Departmental Node</th>
                        <th>Scope / Description</th>
                        <th class="text-center">Faculty</th>
                        <th class="text-center">Curriculum</th>
                        <th class="text-center">Population</th>
                        <th class="text-right">Nexus Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 flex items-center justify-center text-xs font-black shadow-sm border border-indigo-100">
                                        {{ strtoupper(substr($dept->name, 0, 2)) }}
                                    </div>
                                    <span class="text-sm font-black text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $dept->name }}</span>
                                </div>
                            </td>
                            <td class="text-xs text-slate-500 font-medium italic">
                                {{ Str::limit($dept->description ?? 'Global Academic Domain', 40) }}
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                                    {{ $dept->teachers_count }} Teachers
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="px-2.5 py-1 bg-teal-50 text-teal-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-teal-100">
                                    {{ $dept->courses_count }} Courses
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.students.index', ['department_id' => $dept->id]) }}"
                                    class="px-3 py-1 bg-violet-50 text-violet-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-violet-100 hover:bg-violet-600 hover:text-white hover:border-violet-600 transition-all">
                                    {{ $dept->students_count }} Students
                                </a>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 pr-4">
                                    <a href="{{ route('admin.departments.edit', $dept->id) }}"
                                        class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100"><i
                                            class="bi bi-pencil-square text-sm"></i></a>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $dept->id) }}" class="inline"
                                        onsubmit="return confirm('Disconnect this departmental node?')">
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
                            <td colspan="6" class="py-20 text-center opacity-30">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-building-dash text-5xl mb-4"></i>
                                    <p class="text-[11px] font-black uppercase tracking-widest">No Departmental Nodes Detected</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            {{ $departments->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        let deptDebounce = null;
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = document.getElementById('deptSearchForm');
        
        searchInput.addEventListener('input', function () {
            clearTimeout(deptDebounce);
            deptDebounce = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    </script>
    @endpush
@endsection
