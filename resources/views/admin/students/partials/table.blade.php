@if($studentsPaginated->isEmpty())
    <div class="glass-card py-32 text-center border-dashed border-2 border-slate-200/60 bg-slate-50/30">
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
                <div class="h-10 w-1.5 bg-gradient-to-b from-indigo-600 to-violet-600 rounded-full shadow-lg shadow-indigo-500/20"></div>
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                        {{ $semesterName }}
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-200"></span>
                        <span class="text-indigo-600">{{ $students->count() }} IDENTITIES</span>
                    </h3>
                </div>
                <div class="flex-1 h-[1px] bg-slate-100"></div>
            </div>

            <x-table :headers="['GTU ID', 'Student Profile', 'Domain & Degree', 'Intake', 'Access Nexus', 'Operations']">
                @foreach($students as $student)
                    <tr class="group/row" data-student-id="{{ $student->id }}">
                        <td>
                            <div class="font-black text-slate-800">{{ $student->gtu_enrollment_no ?? 'N/A' }}</div>
                            <div class="text-[9px] text-slate-400 font-bold mt-0.5 uppercase tracking-tighter">ROLL {{ $student->roll_number }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <img class="h-10 w-10 rounded-xl ring-2 ring-white shadow-sm"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($student->user->name ?? 'Student') }}&background=6366f1&color=fff&bold=true"
                                        alt="">
                                    <span class="status-dot absolute -bottom-1 -right-1 h-3.5 w-3.5 border-2 border-white rounded-full
                                                {{ $student->is_active ? 'bg-emerald-500' : 'bg-rose-400' }}"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-slate-800 truncate">{{ $student->user->name ?? 'Unknown Student' }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium truncate">{{ $student->user->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-xs font-bold text-slate-700 leading-none mb-1">{{ $student->course?->name ?? 'N/A' }}</div>
                            <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">{{ $student->course?->department?->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <div class="px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg inline-block">
                                <span class="text-[10px] font-black text-slate-600">BATCH {{ $student->admission_year }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="access-badge">
                                @if($student->is_active)
                                    <x-status-badge status="active" />
                                @else
                                    <x-status-badge status="locked" />
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Edit Student">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <button type="button" data-action="toggle"
                                    data-url="{{ route('admin.students.toggle-status', $student->id) }}"
                                    data-active="{{ $student->is_active ? '1' : '0' }}" data-csrf="{{ csrf_token() }}"
                                    title="{{ $student->is_active ? 'Deactivate Student' : 'Activate Student' }}" 
                                    class="h-9 w-9 rounded-xl flex items-center justify-center transition-all shadow-sm {{ $student->is_active
                                        ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-600 hover:text-white'
                                        : 'bg-amber-50 text-amber-500 border border-amber-100 hover:bg-amber-500 hover:text-white' }}">
                                    <i class="bi {{ $student->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }} text-lg"></i>
                                </button>

                                <button type="button" data-action="delete"
                                    data-url="{{ route('admin.students.destroy', $student->id) }}"
                                    data-name="{{ $student->user->name ?? 'this student' }}" data-csrf="{{ csrf_token() }}"
                                    title="Delete Student" 
                                    class="h-9 w-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
    @empty
        <div class="glass-card py-20 text-center">
            <p class="text-slate-400 font-bold uppercase tracking-widest text-[11px]">No grouped records available</p>
        </div>
    @endforelse
@endif

@if($studentsGrouped->isNotEmpty())
    <div class="mt-12 glass-card p-6 border border-white/60 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 overflow-hidden">
        <div class="flex items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
            <span class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 shadow-inner">
                {{ $studentsPaginated->total() }}
            </span>
            Total Students Discovered
        </div>
        <div class="pagination-ajax shrink-0">
            {{ $studentsPaginated->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endif