@extends('layouts.app')

@section('header_title', 'Auto Timetable Generator')

@section('content')
    @php
        $portalAccess = app(\App\Services\PortalAccessService::class);
        $canEditTimetable = $portalAccess->featureEnabled('timetable_edit_enabled', true) && !$portalAccess->featureEnabled('semester_lock', false);
    @endphp
    <div class="space-y-6">
        <div class="glass-card p-6 space-y-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Timetable Auto Generation</h2>
                <p class="text-sm text-slate-500">Generate odd/even semester timetables with lab-first conflict-safe scheduling.</p>
            </div>

            <form method="GET" class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="input-label">Course</label>
                    <select name="course_id" class="input-premium" required>
                        <option value="">Select course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id', $selectedCourseId) === (int) $course->id)>
                                {{ $course->name }} ({{ $course->department?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label">Semester Type</label>
                    <select name="semester_type" class="input-premium" required>
                        <option value="odd" @selected(old('semester_type', $selectedSemesterType) === 'odd')>Odd</option>
                        <option value="even" @selected(old('semester_type', $selectedSemesterType) === 'even')>Even</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-outline w-full">Load Context</button>
                </div>
            </form>

            @if($context)
                <form method="POST" action="{{ route('admin.timetable-auto.generate') }}" class="space-y-4 border-t border-slate-100 pt-4">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $context['course']->id }}">
                    <input type="hidden" name="semester_type" value="{{ $context['semester_type'] }}">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="glass-card p-4">
                            <label class="input-label">Select Active Classes (Years)</label>
                            <div class="space-y-2">
                                @foreach($context['years'] as $year)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="selected_years[]"
                                            value="{{ $year }}"
                                            @checked(collect(old('selected_years', $context['years']->all()))->contains($year))
                                            class="rounded border-slate-300">
                                        <span>Year {{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-card p-4">
                            <label class="input-label">Select Active Teachers</label>
                            <div class="max-h-48 overflow-y-auto space-y-2">
                                @foreach($context['teachers'] as $teacher)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="selected_teacher_ids[]"
                                            value="{{ $teacher->id }}"
                                            @checked(collect(old('selected_teacher_ids', $context['teachers']->pluck('id')->all()))->contains($teacher->id))
                                            class="rounded border-slate-300">
                                        <span>{{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="glass-card p-4">
                            <label class="input-label">Fixed Lecture Classrooms (Year-wise)</label>
                            <div class="max-h-48 overflow-y-auto space-y-2">
                                @foreach($context['years'] as $year)
                                    @php $room = $context['lecture_rooms'][(int) $year] ?? null; @endphp
                                    @if($room)
                                        <p class="text-sm">Year {{ $year }}: <span class="font-semibold">{{ $room->name }}</span></p>
                                    @else
                                        <p class="text-sm text-rose-600">Year {{ $year }}: No fixed lecture classroom assigned.</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-card p-4">
                            <label class="input-label">Select Lab Classrooms</label>
                            <div class="max-h-48 overflow-y-auto space-y-2">
                                @forelse($context['lab_rooms'] as $room)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="selected_classroom_ids[]"
                                            value="{{ $room->id }}"
                                            @checked(collect(old('selected_classroom_ids', $context['lab_rooms']->pluck('id')->all()))->contains($room->id))
                                            class="rounded border-slate-300">
                                        <span>{{ $room->name }} (LAB)</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-500">No lab classrooms available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>



                    @error('generator')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror

                    @if($portalAccess->featureEnabled('timetable_edit_enabled', true))
                        <button type="submit" class="btn-primary-gradient">Generate Timetable</button>
                    @else
                        <p class="text-sm text-amber-700">Timetable generation is disabled by feature setting.</p>
                    @endif
                </form>
            @endif
        </div>

        @if($gridData)
            <div class="glass-card p-4">
                <h3 class="font-black text-slate-800">Editable Timetable Grid</h3>
                <p class="text-sm text-slate-500">Semester type: <span class="font-semibold uppercase">{{ $gridData['semester_type'] }}</span></p>
            </div>

            @foreach($gridData['years'] as $year)
                <div class="glass-card p-4 space-y-3">
                    <h4 class="font-black text-slate-800">Year {{ $year }}</h4>

                    <div class="overflow-x-auto">
                        <table class="table-premium min-w-[980px]">
                            <thead>
                                <tr>
                                    <th>Slot</th>
                                    @foreach($gridData['days'] as $day)
                                        <th>{{ ucfirst($day) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gridData['slots'] as $slot)
                                    <tr>
                                        <td class="font-semibold">
                                            <div class="text-sm">Slot {{ $slot }}</div>
                                            <div class="text-[10px] text-slate-500 font-normal">
                                                {{ $gridData['time_slots']->get($slot - 1) }}
                                            </div>
                                        </td>
                                        @foreach($gridData['days'] as $day)
                                            @php $entry = $gridData['grid'][(int) $year][$day][$slot] ?? null; @endphp
                                            <td class="align-top min-w-[240px] break-words">
                                                @if($entry)
                                                    <form method="POST" action="{{ route('admin.timetable-auto.entries.update', $entry) }}" class="space-y-2">
                                                        @csrf
                                                        @method('PUT')

                                                        <select name="subject_id" class="input-premium text-xs" @disabled(!$canEditTimetable)>
                                                            @foreach($gridData['subjects_by_year'][(int) $year] as $subject)
                                                                <option value="{{ $subject->id }}" @selected((int) $entry->subject_id === (int) $subject->id)>
                                                                    {{ $subject->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <select name="teacher_id" class="input-premium text-xs" @disabled(!$canEditTimetable)>
                                                            @foreach($gridData['teachers'] as $teacher)
                                                                <option value="{{ $teacher->id }}" @selected((int) $entry->teacher_id === (int) $teacher->id)>
                                                                    {{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <select name="classroom_id" class="input-premium text-xs" @disabled(!$canEditTimetable)>
                                                            @foreach($gridData['classrooms_by_year'][(int) $year] as $room)
                                                                <option value="{{ $room->id }}" @selected((int) $entry->classroom_id === (int) $room->id)>
                                                                    {{ $room->name }} ({{ strtoupper($room->type) }})
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                        <div class="grid grid-cols-2 gap-1">
                                                            <select name="day" class="input-premium text-xs" @disabled(!$canEditTimetable)>
                                                                @foreach($gridData['days'] as $d)
                                                                    <option value="{{ $d }}" @selected($entry->day === $d)>{{ ucfirst($d) }}</option>
                                                                @endforeach
                                                            </select>
                                                            <select name="slot_number" class="input-premium text-xs" @disabled(!$canEditTimetable)>
                                                                @foreach($gridData['slots'] as $s)
                                                                    <option value="{{ $s }}" @selected((int) $entry->slot_number === (int) $s)>{{ $s }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        @error('entry_'.$entry->id)
                                                            <p class="text-[11px] text-rose-600">{{ $message }}</p>
                                                        @enderror

                                                        @if($canEditTimetable)
                                                            <button class="btn-outline text-xs px-2 py-1 w-full">Save</button>
                                                        @else
                                                            <p class="text-[11px] text-amber-700">Editing disabled by settings.</p>
                                                        @endif
                                                    </form>
                                                @else
                                                    <span class="text-xs text-slate-400">Free</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    </div>

@endsection
