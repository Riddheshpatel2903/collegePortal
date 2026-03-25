@extends('layouts.app')

@section('header_title', 'Teacher Assignments')

@section('content')
    <div class="space-y-6">
        <!-- ─── Page Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Teacher Assignments</h2>
                <p class="text-sm text-slate-500">Assign faculty members to subjects for course scheduling.</p>
            </div>
            <a href="{{ route('hod.timetable.index', ['course_id' => $selectedCourseId, 'academic_year' => $selectedYear]) }}" class="btn-primary-gradient">
                <i class="bi bi-calendar3 mr-2"></i> Go To Timetable
            </a>
        </div>

        <!-- ─── Filter Row ─── -->
        <form method="GET" action="{{ route('hod.teacher-assignments.index') }}" class="glass-card p-4 grid md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="input-label">Course</label>
                <select name="course_id" class="input-premium" onchange="this.form.submit()">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourseId == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Year / Semester Group</label>
                <input type="number" name="academic_year" min="1" max="8" value="{{ $selectedYear }}" class="input-premium" onchange="this.form.submit()">
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="btn-outline px-6">Load Subjects</button>
            </div>
        </form>

        @if($subjects->isEmpty())
            <div class="glass-card p-12 flex flex-col items-center opacity-40">
                <i class="bi bi-journal-x text-5xl mb-2"></i>
                <p class="text-sm font-semibold">No subjects found for the selected class context.</p>
            </div>
        @else
            @php $requiredSubjects = (int) config('timetable.subjects_per_semester', 8); @endphp
            @if(($semesterCounts ?? collect())->contains(fn ($row) => (int) ($row['count'] ?? 0) !== $requiredSubjects))
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm text-amber-800 font-bold tracking-tight">Curriculum Check Pending</p>
                        <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                            Each semester typically requires {{ $requiredSubjects }} subjects. Current counts:
                            @foreach(($semesterCounts ?? collect()) as $row)
                                <span class="font-bold underline ml-1">Sem {{ $row['semester'] }} ({{ $row['count'] }})</span>@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('hod.teacher-assignments.store') }}" class="glass-card overflow-hidden">
                @csrf
                <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                <input type="hidden" name="academic_year" value="{{ $selectedYear }}">

                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="font-black text-slate-800">Faculty Mapping Workspace</h3>
                    <button type="submit" class="btn-primary-gradient px-8">Save All Assignments</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Subject Detail</th>
                                <th>Weekly Load</th>
                                <th>Assigned Faculty Member</th>
                                <th>Guidance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                @php
                                    $existing = $assignmentMap->get($subject->id);
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-black text-slate-800">{{ $subject->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded uppercase">Sem {{ $subject->semester_sequence }}</span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded uppercase tracking-tighter">{{ $subject->code ?? 'NO-CODE' }}</span>
                                        </div>
                                    </td>
                                    <td class="font-mono text-sm font-bold text-slate-600">
                                        {{ $subject->weekly_hours ?? $subject->credits ?? 4 }} Hrs/Week
                                    </td>
                                    <td>
                                        <select name="subject_teacher_map[{{ $subject->id }}]" class="input-premium" required>
                                            <option value="">Select Teacher</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected((int) ($existing->teacher_id ?? 0) === (int) $teacher->id)>
                                                    {{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}{{ $teacher->qualification ? ' ('.$teacher->qualification.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("subject_teacher_map.$subject->id")
                                            <p class="text-[10px] text-rose-600 font-bold mt-1 uppercase tracking-tight">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td>
                                        <div class="text-[10px] text-slate-400 bg-slate-50 p-2 rounded-lg border border-slate-100 italic">
                                            "Ensure faculty specialization aligns with {{ $subject->name }} syllabus."
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="btn-primary-gradient px-12 py-3 h-auto">Update All Assignments</button>
                </div>
            </form>
        @endif
    </div>
@endsection
