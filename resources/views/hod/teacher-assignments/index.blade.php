@extends('layouts.app')

@section('header_title', 'Departmental Faculty Allocation')

@section('content')
    <x-page-header 
        title="Faculty Subject Assignment" 
        subtitle="Map department teachers to specific subjects for the academic session to initialize schedules."
        icon="bi-person-badge"
    />

    <div class="mt-8 space-y-8">
        {{-- Filters --}}
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <form method="GET" action="{{ route('hod.teacher-assignments.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Academic Program</label>
                    <select name="course_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" onchange="this.form.submit()">
                        <option value="">Select Course...</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected($selectedCourseId == $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Academic Year / Group</label>
                    <input type="number" name="academic_year" min="1" max="8" value="{{ $selectedYear }}" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" onchange="this.form.submit()">
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-bold h-11 rounded-xl transition-all shadow-lg shadow-slate-200 text-xs uppercase tracking-widest">
                        Refresh Subjects
                    </button>
                    <a href="{{ route('hod.timetable.index', ['course_id' => $selectedCourseId, 'academic_year' => $selectedYear]) }}" 
                        class="flex-1 bg-indigo-50 text-indigo-700 border border-indigo-100 hover:bg-indigo-100 font-bold h-11 rounded-xl transition-all flex items-center justify-center text-xs uppercase tracking-widest gap-2">
                        <i class="bi bi-calendar3"></i> Timetable
                    </a>
                </div>
            </form>
        </div>

        @if($subjects->isEmpty())
            <div class="bg-white border border-slate-200 p-24 rounded-2xl flex flex-col items-center justify-center text-center space-y-4 opacity-50">
                <i class="bi bi-journal-x text-6xl text-slate-200"></i>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No subjects found for the selected criteria</p>
            </div>
        @else
            @php $requiredSubjects = (int) config('timetable.subjects_per_semester', 8); @endphp
            @if(($semesterCounts ?? collect())->contains(fn ($row) => (int) ($row['count'] ?? 0) !== $requiredSubjects))
                <div class="bg-amber-50 border border-amber-200 p-5 rounded-2xl flex items-start gap-4 shadow-sm shadow-amber-50">
                    <div class="h-10 w-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900">Curriculum Compliance Check</h4>
                        <p class="text-[11px] text-amber-700 mt-1 leading-relaxed font-medium">
                            Standard departmental policy requires {{ $requiredSubjects }} subjects per semester. Discrepancies found:
                            <span class="ml-2">
                                @foreach(($semesterCounts ?? collect()) as $row)
                                    <span class="inline-block bg-white/50 px-2 py-0.5 rounded border border-amber-100 ml-1">Sem {{ $row['semester'] }}: {{ $row['count'] }}</span>
                                @endforeach
                            </span>
                        </p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('hod.teacher-assignments.store') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-12">
                @csrf
                <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                <input type="hidden" name="academic_year" value="{{ $selectedYear }}">

                <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Faculty Allocation Registry</h3>
                    <button type="submit" class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Commit All Assignments
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                                <th class="px-8 py-5">Subject Details</th>
                                <th class="px-8 py-5">Academic Load</th>
                                <th class="px-8 py-5">Faculty Assignment</th>
                                <th class="px-8 py-5">Administrative Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subjects as $subject)
                                @php
                                    $existing = $assignmentMap->get($subject->id);
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 leading-tight">{{ $subject->name }}</span>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100 uppercase tracking-widest">Sem {{ $subject->semester_sequence }}</span>
                                                <span class="text-[9px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 uppercase tracking-widest">{{ $subject->code ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-2">
                                            <i class="bi bi-clock-history text-slate-300"></i>
                                            <span class="text-sm font-bold text-slate-600">{{ $subject->weekly_hours ?? $subject->credits ?? 4 }} <span class="text-[10px] text-slate-400 font-bold uppercase ml-1">Hrs/Week</span></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <select name="subject_teacher_map[{{ $subject->id }}]" 
                                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-700" required>
                                            <option value="">Assign Faculty...</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected((int) ($existing->teacher_id ?? 0) === (int) $teacher->id)>
                                                    {{ $teacher->user?->name ?? 'N/A' }}{{ $teacher->qualification ? ' ('.$teacher->qualification.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("subject_teacher_map.$subject->id")
                                            <p class="text-[10px] text-rose-600 font-bold mt-1.5 uppercase tracking-tight">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-[10px] text-slate-400 bg-slate-50/50 p-3 rounded-xl border border-dashed border-slate-200 font-medium italic">
                                            Please verify faculty expertise for the {{ $subject->name }} syllabus.
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex justify-end gap-6">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="bi bi-info-circle text-indigo-500"></i> Review all mappings before committing
                    </p>
                    <button type="submit" class="px-12 py-3.5 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Update All Assignments
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection
