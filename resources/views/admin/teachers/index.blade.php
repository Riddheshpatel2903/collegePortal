@extends('layouts.app')

@section('header_title', 'Faculty Nexus')

@section('content')
    <x-page-header 
        title="Faculty Nexus" 
        subtitle="Orchestrate teaching staff, specialized departments, and academic assignments."
        icon="bi-person-workspace"
        actionLabel="Integrate Faculty"
        actionIcon="bi-person-plus-fill"
        actionRoute="{{ route('admin.teachers.create') }}"
    />

    <!-- ─── Search Architecture ─── -->
    <div class="mb-8 sticky top-20 z-30">
        <x-card class="p-2 border border-white/60 shadow-2xl">
            <form method="GET" id="teacherSearchForm" class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-[300px] h-12 group">
                    <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" id="teacherSearch" value="{{ request('search') }}" 
                        placeholder="Identify faculty by name or specialization..."
                        class="input-premium pl-12 h-full" autocomplete="off">
                </div>
                <x-button variant="outline" href="{{ route('admin.teachers.index') }}" icon="bi-arrow-counterclockwise" class="h-12 w-12 !p-0"></x-button>
            </form>
        </x-card>
    </div>

    <!-- ─── Faculty Grid ─── -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="teacherGrid">
        @forelse($teachers as $teacher)
            <div class="glass-card p-6 group hover:translate-y-[-4px] transition-all duration-300 shadow-xl shadow-slate-200/50" data-name="{{ strtolower($teacher->user->name ?? '') }}">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="relative mb-5">
                        <div class="h-24 w-24 rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-50 text-indigo-600 flex items-center justify-center font-black text-2xl group-hover:scale-110 transition-transform ring-4 ring-white shadow-xl">
                            {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}{{ strtoupper(substr(strrchr($teacher->user->name ?? 'T', ' ') ?: '', 1, 1)) }}
                        </div>
                        <div class="absolute -bottom-2 -right-2 h-8 w-8 rounded-xl bg-white shadow-lg flex items-center justify-center text-indigo-600 border border-slate-100">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                    
                    <h4 class="text-lg font-black text-slate-800 group-hover:text-indigo-600 transition-colors leading-tight">
                        {{ $teacher->user->name ?? 'N/A' }}
                    </h4>
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mt-1">{{ $teacher->department->name ?? 'N/A' }} Node</p>
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[9px] font-black uppercase tracking-widest mt-3 border border-amber-100">
                        <i class="bi bi-mortarboard-fill"></i> {{ $teacher->qualification }}
                    </span>
                </div>

                <div class="space-y-2 mb-6">
                    <div class="flex items-center justify-center gap-2 py-2.5 bg-slate-50/50 rounded-xl text-[11px] text-slate-500 font-bold border border-slate-100/50">
                        <i class="bi bi-envelope-at-fill text-indigo-400"></i>
                        <span class="truncate">{{ $teacher->user->email ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-center gap-3">
                        <span class="px-3 py-1.5 bg-teal-50 text-teal-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-teal-100 italic">
                            <i class="bi bi-telephone-fill mr-1"></i> {{ $teacher->phone }}
                        </span>
                    </div>
                </div>

                <!-- Synchrony Metrics -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="flex flex-col items-center py-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 group-hover:bg-indigo-600 group-hover:border-indigo-600 transition-all duration-500">
                        <span class="text-xl font-black text-indigo-600 group-hover:text-white">{{ $teacher->subjects_count }}</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 group-hover:text-indigo-200">Active Subjects</span>
                    </div>
                    <div class="flex flex-col items-center py-4 bg-violet-50/50 rounded-2xl border border-violet-100/50 group-hover:bg-violet-600 group-hover:border-violet-600 transition-all duration-500">
                        <span class="text-xl font-black text-violet-600 group-hover:text-white">{{ $teacher->assignments_count }}</span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 group-hover:text-violet-200">Assignments</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}"
                        class="py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-indigo-600 transition-all shadow-lg shadow-slate-200">
                        <i class="bi bi-pencil-square"></i> Modify Node
                    </a>
                    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}"
                        onsubmit="return confirm('Disconnect this faculty node?')" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-3 bg-white border border-rose-200 text-rose-500 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all shadow-sm">
                            <i class="bi bi-trash3-fill"></i> Disconnect
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card py-20 text-center opacity-40">
                <i class="bi bi-people text-5xl mb-4"></i>
                <p class="text-xs font-black uppercase tracking-widest">No faculty nodes detected in the nexus.</p>
            </div>
        @endforelse
    </div>

    <!-- ─── Paging Hub ─── -->
    <div class="mt-8 flex justify-center">
        {{ $teachers->links() }}
    </div>

    @push('scripts')
    <script>
        let teacherDebounce = null;
        const searchInput = document.getElementById('teacherSearch');
        const searchForm = document.getElementById('teacherSearchForm');
        
        searchInput.addEventListener('input', function () {
            clearTimeout(teacherDebounce);
            teacherDebounce = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });

        // Focus effect for the nexus search
        searchInput.addEventListener('focus', () => {
             searchInput.parentElement.classList.add('scale-[1.01]');
        });
        searchInput.addEventListener('blur', () => {
             searchInput.parentElement.classList.remove('scale-[1.01]');
        });
    </script>
    @endpush
@endsection
