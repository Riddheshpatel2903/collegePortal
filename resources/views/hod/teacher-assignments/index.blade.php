@extends('layouts.app')

@section('header_title', 'Department Teacher Assignments')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-slate-800">Department -> Teacher Assignments</h2>
                <p class="text-sm text-slate-500 mt-1">Assign one teacher per subject for the selected class context.</p>
            </div>
            <a href="{{ route('hod.timetable.index', ['course_id' => $selectedCourseId, 'academic_year' => $selectedYear]) }}" class="btn-outline">Go To Timetable</a>
        </div>

        <form method="GET" action="{{ route('hod.teacher-assignments.index') }}" class="glass-card p-4 grid md:grid-cols-3 gap-3">
            <div>
                <label class="input-label">Course</label>
                <select name="course_id" class="input-premium" onchange="this.form.submit()">
                    <option value="">Select course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourseId == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Year / Semester Group</label>
                <input type="number" name="academic_year" min="1" max="8" value="{{ $selectedYear }}" class="input-premium">
            </div>
            <div class="flex items-end">
                <button class="btn-outline w-full">Load Subjects</button>
            </div>
        </form>

        @if($subjects->isEmpty())
            <div class="glass-card p-5">
                <p class="text-sm text-slate-500">No subjects found for selected class.</p>
            </div>
        @else
            @php $requiredSubjects = (int) config('timetable.subjects_per_semester', 8); @endphp
            @if(($semesterCounts ?? collect())->contains(fn ($row) => (int) ($row['count'] ?? 0) !== $requiredSubjects))
                <div class="glass-card p-4 border border-amber-200 bg-amber-50">
                    <p class="text-sm text-amber-800 font-semibold">Each semester must have exactly {{ $requiredSubjects }} subjects.</p>
                    <p class="text-xs text-amber-700 mt-1">
                        @foreach(($semesterCounts ?? collect()) as $row)
                            Sem {{ $row['semester'] }}: {{ $row['count'] }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            @endif
            <form method="POST" action="{{ route('hod.teacher-assignments.store') }}" class="glass-card overflow-hidden">
                @csrf
                <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                <input type="hidden" name="academic_year" value="{{ $selectedYear }}">

                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-black text-slate-800">Subject Assignment Table</h3>
                    <button type="submit" class="btn-primary-gradient">Save Assignments</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Weekly Hours</th>
                                <th>Assigned Teacher</th>
                                <th>Specialization Hint</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                @php
                                    $existing = $assignmentMap->get($subject->id);
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-semibold">{{ $subject->name }}</p>
                                        <p class="text-xs text-slate-400">Sem {{ $subject->semester_sequence }}</p>
                                    </td>
                                    <td>{{ $subject->weekly_hours ?? $subject->credits ?? 4 }}</td>
                                    <td>
                                        <select name="subject_teacher_map[{{ $subject->id }}]" class="input-premium" required>
                                            <option value="">Select teacher</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected((int) ($existing->teacher_id ?? 0) === (int) $teacher->id)>
                                                    {{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}{{ $teacher->qualification ? ' - '.$teacher->qualification : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("subject_teacher_map.$subject->id")
                                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="text-xs text-slate-500">
                                        Preferred: teacher qualification matching "{{ $subject->name }}"
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    </div>
@endsection
