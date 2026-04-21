@php
    $roleStyles = [
        'super_admin' => 'indigo',
        'admin' => 'slate',
        'hod' => 'violet',
        'teacher' => 'sky',
        'student' => 'emerald',
        'accountant' => 'amber',
        'librarian' => 'rose'
    ];
@endphp

@if($studentsPaginated->isEmpty())
    <div class="bg-white border border-slate-200 border-dashed rounded-3xl py-24 text-center">
        <div class="h-20 w-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-3xl mx-auto mb-6 border border-slate-100">
            <i class="bi bi-search"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">No Students Found</h3>
        <p class="text-sm text-slate-500 font-medium max-w-xs mx-auto">Try adjusting your filters or search keywords to find the records you're looking for.</p>
    </div>
@else
    @foreach($studentsGrouped as $semesterName => $students)
        <div class="mb-12 last:mb-0 animate-fade-in">
            <div class="flex items-center gap-4 mb-6">
                <div class="h-8 px-4 rounded-lg bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest flex items-center shadow-sm">
                    {{ $semesterName }}
                </div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ $students->count() }} {{ Str::plural('Record', $students->count()) }}
                </div>
                <div class="flex-1 h-px bg-slate-100"></div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-4">Identification</th>
                            <th class="px-6 py-4">Student Profile</th>
                            <th class="px-6 py-4">Degree & Batch</th>
                            <th class="px-6 py-4 text-center">Account Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $student)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="text-xs font-black text-slate-800 block">{{ $student->gtu_enrollment_no ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight mt-1 inline-block bg-slate-100 px-1.5 py-0.5 rounded">Roll: {{ $student->roll_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <img class="h-10 w-10 rounded-xl bg-slate-100 object-cover border-2 border-white shadow-sm"
                                                src="https://ui-avatars.com/api/?name={{ urlencode($student->user->name ?? 'Student') }}&background=6366f1&color=fff&bold=true"
                                                alt="">
                                            <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 border-2 border-white rounded-full {{ $student->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-sm font-bold text-slate-700 block truncate">{{ $student->user->name ?? 'Unknown' }}</span>
                                            <span class="text-[10px] font-medium text-slate-400 block truncate">{{ $student->user->email ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-700 block truncate max-w-[180px]">{{ $student->course?->name ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mt-1 block">Batch of {{ $student->admission_year }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($student->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider border border-emerald-100">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-50 text-rose-500 text-[10px] font-bold uppercase tracking-wider border border-rose-100">
                                            Locked
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.students.edit', $student->id) }}" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Edit Student">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button type="button" data-action="toggle"
                                            data-url="{{ route('admin.students.toggle-status', $student->id) }}"
                                            data-active="{{ $student->is_active ? '1' : '0' }}"
                                            title="{{ $student->is_active ? 'Deactivate' : 'Activate' }}" 
                                            class="h-8 w-8 rounded-lg flex items-center justify-center transition-all shadow-sm {{ $student->is_active
                                                ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-600 hover:text-white'
                                                : 'bg-amber-50 text-amber-500 border border-amber-100 hover:bg-amber-500 hover:text-white' }}">
                                            <i class="bi {{ $student->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }} text-lg"></i>
                                        </button>

                                        <button type="button" data-action="delete"
                                            data-url="{{ route('admin.students.destroy', $student->id) }}"
                                            title="Delete Permanently" 
                                            class="h-8 w-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-xs border border-slate-100">
                {{ $studentsPaginated->total() }}
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Total Student Entities</div>
        </div>
        <div class="pagination-ajax shadow-sm rounded-xl overflow-hidden">
            {{ $studentsPaginated->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endif