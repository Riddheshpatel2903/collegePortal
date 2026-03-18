@extends('layouts.app')

@section('header_title', 'Department Timetable')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-slate-800">HOD Timetable Workspace</h2>
                <p class="text-sm text-slate-500 mt-1">Flow: 1) Assign Teachers 2) Generate Timetable 3) Review and Edit.</p>
            </div>
            <a href="{{ route('hod.teacher-assignments.index', ['course_id' => $selectedCourseId, 'academic_year' => $selectedYear]) }}" class="btn-outline">
                1. Assign Teachers
            </a>
        </div>

        <form method="GET" action="{{ route('hod.timetable.index') }}" class="glass-card p-4 grid md:grid-cols-3 gap-3">
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
                <button class="btn-outline w-full">Load Timetable</button>
            </div>
        </form>

        <form method="POST" action="{{ route('hod.timetable.generate') }}" class="glass-card p-4 grid md:grid-cols-4 gap-3">
            @csrf
            <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
            <input type="hidden" name="academic_year" value="{{ $selectedYear }}">

            <div class="md:col-span-2">
                <p class="text-sm text-slate-700">2. Generate using current teacher assignments, weekly hours, availability, and room availability.</p>
                @if($subjects->isNotEmpty())
                    <p class="text-xs text-slate-500 mt-1">Subjects loaded: {{ $subjects->count() }} | Assigned: {{ $assignmentMap->count() }}</p>
                @endif
            </div>
            <div class="flex items-center">
                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="clear_existing" value="1" class="rounded border-slate-300">
                    Clear previous timetable
                </label>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary-gradient w-full">Generate Timetable</button>
            </div>
            @error('generator')
                <p class="md:col-span-4 text-sm text-rose-700 bg-rose-50 border border-rose-100 rounded px-3 py-2 whitespace-pre-line">{{ $message }}</p>
            @enderror
        </form>

        <div class="glass-card p-5 space-y-4">
            <h3 class="font-black text-slate-800">Teacher Availability (Used by generator)</h3>
            <form method="POST" action="{{ route('hod.timetable.availability.store') }}" class="grid md:grid-cols-5 gap-3">
                @csrf
                <div>
                    <label class="input-label">Teacher</label>
                    <select name="teacher_id" class="input-premium" required>
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">Day</label>
                    <select name="day_of_week" class="input-premium" required>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                    </select>
                </div>
                <div>
                    <label class="input-label">Start</label>
                    <input type="time" name="start_time" class="input-premium" required>
                </div>
                <div>
                    <label class="input-label">End</label>
                    <input type="time" name="end_time" class="input-premium" required>
                </div>
                <div class="flex items-end">
                    <button class="btn-outline w-full">Save Availability</button>
                </div>
            </form>

            <div class="grid md:grid-cols-2 gap-3">
                @forelse($teachers as $teacher)
                    <div class="border border-slate-200 rounded-xl p-3">
                        <p class="font-semibold text-slate-700">{{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}</p>
                        <div class="mt-2 space-y-2">
                            @forelse(($availabilities[$teacher->id] ?? collect()) as $slot)
                                <div class="flex items-center justify-between text-xs bg-slate-50 rounded px-2 py-1">
                                    <span>{{ ucfirst($slot->day_of_week) }} | {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                    <form method="POST" action="{{ route('hod.timetable.availability.destroy', $slot) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 font-semibold">Remove</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400">No availability configured.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No teachers in department.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-black text-slate-800">3. Review and Edit Timetable (Click any slot)</h3>
            </div>
            <x-weekly-timetable-grid
                :days="$days"
                :timeSlots="$timeSlots"
                :grid="$grid"
                :showTeacher="true"
                :showRoom="true"
                :showSemester="true"
                :colorBySubject="true"
                slotEditRoute="{{ $portalAccess->featureEnabled('edit_button_enabled', true) ? 'hod.timetable.edit' : null }}"
                emptyText="No timetable generated for this class."
            />
        </div>

        <div class="glass-card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-black text-slate-800">List View (Editable)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Classroom</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($listSchedules as $slot)
                            <tr>
                                <td>{{ ucfirst($slot->day_of_week) }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td>{{ $slot->subject?->name ?? 'N/A' }}</td>
                                <td>{{ $slot->teacher?->user?->name ?? 'N/A' }}</td>
                                <td>{{ $slot->classroom?->name ?? 'N/A' }}</td>
                                <td>
                                    @featureEnabled('edit_button_enabled')
                                    <a href="{{ route('hod.timetable.edit', $slot) }}" class="text-indigo-600 text-xs font-semibold">Edit Slot</a>
                                    @endfeatureEnabled
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-8 text-slate-500">No slots available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
