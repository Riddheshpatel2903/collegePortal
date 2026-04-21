@extends('layouts.app')

@section('header_title', 'Staff Management')

@section('content')
    <div x-data="{
            loading: false,
            async reset() {
                window.location.href = '{{ route('admin.teachers.index') }}';
            }
        }">
        <x-page-header title="Faculty Management"
            subtitle="Manage academic staff, department allocations, and professional profiles." icon="bi-person-workspace"
            actionLabel="Add Faculty" actionIcon="bi-person-plus" actionRoute="{{ route('admin.teachers.create') }}" />

        <!-- ─── Search & Filters ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-8 sticky top-20 z-30">
            <form method="GET" id="teacherSearchForm" class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[300px] h-11 group">
                    <i
                        class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" id="teacherSearch" value="{{ request('search') }}"
                        placeholder="Search by faculty name, email, or department..."
                        class="w-full h-full bg-slate-50 border-slate-100 rounded-xl pl-11 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium"
                        autocomplete="off">
                </div>

                <button type="button" @click="reset()"
                    class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all shadow-sm"
                    title="Reset Search">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </form>
        </div>

        <!-- ─── Faculty Grid ─── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 text-slate-800" id="teacherGrid">
            @forelse($teachers as $teacher)
                <div class="bg-white border border-slate-200 rounded-2xl p-8 hover:shadow-xl hover:border-indigo-100 transition-all duration-300 group relative overflow-hidden"
                    data-name="{{ strtolower($teacher->user->name ?? '') }}">
                    <!-- Decoration -->
                    <div
                        class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50/50 rounded-full blur-3xl group-hover:bg-indigo-100/50 transition-colors">
                    </div>

                    <div class="flex flex-col items-center text-center mb-6 relative z-10">
                        <div class="relative inline-block mb-4">
                            <div
                                class="h-20 w-20 rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-600 flex items-center justify-center font-black text-3xl border border-indigo-100 shadow-sm group-hover:scale-105 transition-transform duration-300">
                                {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                            </div>
                        </div>

                        <h4 class="text-base font-bold text-slate-800 leading-tight mb-2">
                            {{ $teacher->user->name ?? 'N/A' }}
                        </h4>
                        <div
                            class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                            {{ $teacher->department->name ?? 'General' }}
                        </div>
                    </div>

                    <div class="space-y-3 mb-8 relative z-10">
                        <div
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl text-xs text-slate-600 font-medium border border-slate-50 hover:bg-slate-100 transition-colors">
                            <div
                                class="h-7 w-7 rounded-lg bg-white flex items-center justify-center text-indigo-400 border border-slate-100 shadow-sm">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                            <span class="truncate font-bold">{{ $teacher->qualification ?: 'Degree Unspecified' }}</span>
                        </div>
                        <div
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl text-xs text-slate-600 font-medium border border-slate-50 hover:bg-slate-100 transition-colors">
                            <div
                                class="h-7 w-7 rounded-lg bg-white flex items-center justify-center text-indigo-400 border border-slate-100 shadow-sm">
                                <i class="bi bi-envelope-at"></i>
                            </div>
                            <span class="truncate">{{ $teacher->user->email ?? 'N/A' }}</span>
                        </div>
                        <div
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl text-xs text-slate-600 font-medium border border-slate-50 hover:bg-slate-100 transition-colors">
                            <div
                                class="h-7 w-7 rounded-lg bg-white flex items-center justify-center text-indigo-400 border border-slate-100 shadow-sm">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <span class="truncate">{{ $teacher->phone ?: 'No Contact Provided' }}</span>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-2 gap-3 mb-6 pt-5 border-t border-slate-100">
                        <div
                            class="text-center bg-slate-50 group-hover:bg-indigo-50 p-3 rounded-xl transition-colors border border-slate-100">
                            <span
                                class="text-2xl font-black text-indigo-600 block leading-none">{{ $teacher->subjects_count }}</span>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Subjects</span>
                        </div>
                        <div
                            class="text-center bg-slate-50 group-hover:bg-indigo-50 p-3 rounded-xl transition-colors border border-slate-100">
                            <span
                                class="text-2xl font-black text-indigo-600 block leading-none">{{ $teacher->assignments_count }}</span>
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Assignments</span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('admin.teachers.edit', $teacher->id) }}"
                            class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>
                        <form method="POST" action="{{ route('admin.teachers.destroy', $teacher->id) }}"
                            onsubmit="return confirm('Permanently remove this staff member?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="h-11 w-11 bg-rose-50 border border-rose-100 text-rose-500 rounded-xl hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                <i class="bi bi-trash3 text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-40 text-center bg-white border border-slate-200 border-dashed rounded-3xl">
                    <div
                        class="h-20 w-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                        <i class="bi bi-person-slash"></i>
                    </div>
                    <h3 class="text-slate-800 font-bold">No Staff Found</h3>
                    <p class="text-slate-500 text-xs font-medium">Try searching for a different name or department.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            {{ $teachers->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            let searchDebounce = null;
            const searchInput = document.getElementById('teacherSearch');
            const searchForm = document.getElementById('teacherSearchForm');

            searchInput.addEventListener('input', function () {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            });
        </script>
    @endpush
@endsection