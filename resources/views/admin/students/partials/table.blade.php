@if($studentsPaginated->isEmpty())
    <div class="aero-card py-32 text-center border-dashed border-2 border-slate-200/60 bg-slate-50/30">
        <div class="relative h-24 w-24 mx-auto mb-8">
            <div class="absolute inset-0 bg-slate-200/50 rounded-full animate-pulse"></div>
            <div class="relative h-full w-full rounded-full bg-white shadow-2xl flex items-center justify-center text-4xl text-slate-400">
                <i class="bi bi-search"></i>
            </div>
        </div>
        <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">No Records Detected</h3>
        <p class="text-slate-400 font-medium max-w-sm mx-auto">We couldn't find any students matching those parameters. Try resetting your search filters.</p>
    </div>
@else
@forelse($studentsGrouped as $semesterName => $students)
    <div class="mb-12 animate-fade-in group-container last:mb-0">
        <div class="flex items-center gap-4 mb-6 px-4">
            <div class="h-10 w-1.5 bg-gradient-to-b from-violet-600 to-indigo-600 rounded-full shadow-lg shadow-violet-500/20"></div>
            <div>
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    {{ $semesterName }}
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
                    <x-badge type="primary">{{ $students->count() }} RECORDS</x-badge>
                </h3>
            </div>
            <div class="flex-1 h-[1px] bg-slate-100"></div>
        </div>

        <x-table :headers="['GTU ID', 'Student Profile', 'Domain & Degree', 'Intake', 'Access', 'Nexus']">
            @foreach($students as $student)
                <tr class="group/row">
                    <td>
                        <x-badge type="primary">{{ $student->gtu_enrollment_no ?? 'N/A' }}</x-badge>
                        <div class="text-[9px] text-slate-400 font-bold mt-1">ROLL {{ $student->roll_number }}</div>
                    </td>
                    <td>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <img class="h-10 w-10 rounded-xl"
                                    src="https://ui-avatars.com/api/?name={{ urlencode($student->user->name ?? 'Student') }}&background=6366f1&color=fff&bold=true"
                                    alt="">
                                @if($student->is_active)
                                    <span class="absolute -bottom-1 -right-1 h-3 w-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                                @else
                                    <span class="absolute -bottom-1 -right-1 h-3 w-3 bg-rose-400 border-2 border-white rounded-full"></span>
                                @endif
                            </div>
                            @if($student->user)
                                <div>
                                    <div class="text-sm font-bold text-slate-800">{{ $student->user->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">{{ $student->user->email }}</div>
                                </div>
                            @else
                                <div>
                                    <div class="text-sm font-bold text-slate-800">Unknown Student</div>
                                    <div class="text-[10px] text-slate-400 font-medium">No User Linked</div>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="text-xs font-bold text-slate-700 leading-none mb-1">{{ $student->course?->name ?? '-' }}</div>
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">{{ $student->course?->department?->name ?? '-' }}</div>
                    </td>
                    <td class="text-center">
                        <x-badge>BATCH {{ $student->admission_year }}</x-badge>
                    </td>
                    <td class="text-center">
                        @if($student->is_active)
                            <x-badge type="success">Active</x-badge>
                        @else
                            <x-badge type="danger">Locked</x-badge>
                        @endif
                    </td>
                    <td>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.students.edit', $student->id) }}"
                                class="action-btn action-btn-edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" class="inline"
                                onsubmit="return confirm('Secure Lock this student record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">
                                    <i class="bi bi-shield-lock"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-table>

@empty
@endforelse
@endif

@if($studentsGrouped->isNotEmpty())
    <!-- Unified Centralized Pagination -->
    <div class="mt-12 aero-card p-6 border border-white/60 shadow-2xl shadow-slate-200/30 flex flex-col md:flex-row items-center justify-between gap-6 min-w-0 max-w-full">
        <div class="flex items-center gap-4 text-xs font-black text-slate-400 uppercase tracking-widest flex-shrink-0">
            <span class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 shadow-inner">
                {{ $studentsPaginated->total() }}
            </span>
            TOTAL ARCHIVES IDENTIFIED
        </div>
        <div class="pagination-ajax flex-1 min-w-0 w-full md:w-auto">
            {{ $studentsPaginated->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endif
