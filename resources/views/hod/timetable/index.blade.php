@extends('layouts.app')

@section('header_title', 'Department Timetable Nexus')

@section('content')
    @php
        $portalAccess = app(\App\Services\PortalAccessService::class);
        $canEditTimetable = $portalAccess->featureEnabled('timetable_edit_enabled', true) && !$portalAccess->featureEnabled('semester_lock', false);
    @endphp

    <div class="space-y-6" x-data="{ showAvailability: false }">
        <!-- ─── Academic Nexus Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Department Timetable Nexus</h2>
                <p class="text-sm text-slate-500">Manage academic synchronization with lab-first, conflict-safe logic.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hod.teacher-assignments.index') }}" class="btn-outline">
                    <i class="bi bi-person-badge mr-2"></i> Teacher Assignments
                </a>
                <button @click="showAvailability = !showAvailability" class="btn-primary-gradient">
                    <i class="bi bi-clock-history mr-2"></i>
                    <span x-text="showAvailability ? 'Hide Constraints' : 'Availability Workspace'"></span>
                </button>
            </div>
        </div>

        <!-- ─── Context Selection Filter ─── -->
        <div class="glass-card p-6">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Context Filtering</h3>
            <form method="GET" class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="input-label text-[10px] uppercase">Academic Course</label>
                    <select name="course_id" class="input-premium" required>
                        <option value="">Select Department Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id', $selectedCourseId) === (int) $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label text-[10px] uppercase">Semester Type</label>
                    <select name="semester_type" class="input-premium" required>
                        <option value="odd" @selected(old('semester_type', $selectedSemesterType) === 'odd')>Odd Semester</option>
                        <option value="even" @selected(old('semester_type', $selectedSemesterType) === 'even')>Even Semester</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-outline w-full py-3">Load Scholastic Context</button>
                </div>
            </form>
        </div>

        <!-- ─── Availability Constraints (HOD Specific) ─── -->
        <div x-show="showAvailability" x-cloak x-transition class="space-y-6 bg-slate-50/50 p-6 rounded-2xl border border-slate-200">
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Constraint Entry -->
                <div class="glass-card p-5 space-y-4">
                    <h4 class="font-bold text-slate-700">Add Availability Constraint</h4>
                    <form method="POST" action="{{ route('hod.timetable.availability.store') }}" class="grid grid-cols-2 gap-3">
                        @csrf
                        <div class="col-span-2">
                            <label class="input-label text-xs">Faculty Member</label>
                            <select name="teacher_id" class="input-premium text-sm" required>
                                <option value="">Select Teacher</option>
                                @if($context)
                                    @foreach($context['teachers'] as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? 'Teacher '.$teacher->id }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>Load course context first</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="input-label text-xs">Day of Week</label>
                            <select name="day_of_week" class="input-premium text-sm" required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                            </select>
                        </div>
                        <div class="flex gap-2 text-sm">
                            <div class="flex-1">
                                <label class="input-label text-xs">From</label>
                                <input type="time" name="start_time" class="input-premium text-sm" required>
                            </div>
                            <div class="flex-1">
                                <label class="input-label text-xs">To</label>
                                <input type="time" name="end_time" class="input-premium text-sm" required>
                            </div>
                        </div>
                        <div class="col-span-2 pt-2">
                            <button class="btn-outline w-full py-3 text-sm font-bold">Restrict Faculty Slot</button>
                        </div>
                    </form>
                </div>

                <!-- Snapshot of Constraints -->
                <div class="glass-card p-5 overflow-y-auto max-h-64">
                    <h4 class="font-bold text-slate-700 mb-4">Active Constraints Snapshot</h4>
                    <div class="space-y-2">
                        @if($context)
                            @foreach($context['teachers'] as $teacher)
                                @php 
                                    $availabilities = app(\App\Models\TeacherAvailability::class)
                                        ->where('teacher_id', $teacher->id)
                                        ->orderBy('day_of_week')
                                        ->get();
                                @endphp
                                @if($availabilities->isNotEmpty())
                                    <div class="p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                                        <p class="text-xs font-black text-slate-800 mb-2 truncate">{{ $teacher->user?->name }}</p>
                                        <div class="space-y-1">
                                            @foreach($availabilities as $slot)
                                                <div class="flex items-center justify-between text-[10px] bg-slate-50 px-2 py-1 rounded">
                                                    <span class="text-slate-500 uppercase font-black">{{ substr($slot->day_of_week, 0, 3) }}</span>
                                                    <span class="font-mono">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                                    <form method="POST" action="{{ route('hod.timetable.availability.destroy', $slot) }}">
                                                        @csrf @method('DELETE')
                                                        <button class="text-rose-500 hover:text-rose-700"><i class="bi bi-trash-fill"></i></button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400 italic">No course context loaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($context)
            <!-- ─── Generation Logic & Strategy ─── -->
            <div class="glass-card p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <i class="bi bi-cpu text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800">Auto-Generation Engine</h3>
                        <p class="text-xs text-slate-500">Configure parameters for the lab-first scheduling algorithm.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('hod.timetable.generate') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $context['course']->id }}">
                    <input type="hidden" name="semester_type" value="{{ $context['semester_type'] }}">

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="glass-card p-4 border-l-4 border-indigo-500">
                            <label class="input-label text-[10px] uppercase mb-3">Active Years</label>
                            <div class="space-y-2">
                                @foreach($context['years'] as $year)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-indigo-600 transition-colors">
                                        <input
                                            type="checkbox"
                                            name="selected_years[]"
                                            value="{{ $year }}"
                                            @checked(collect(old('selected_years', $context['years']->all()))->contains($year))
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-semibold">Year {{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-card p-4 border-l-4 border-emerald-500">
                            <label class="input-label text-[10px] uppercase mb-3">Active Faculty</label>
                            <div class="max-h-40 overflow-y-auto space-y-2 pr-2">
                                @foreach($context['teachers'] as $teacher)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-emerald-600 transition-colors">
                                        <input
                                            type="checkbox"
                                            name="selected_teacher_ids[]"
                                            value="{{ $teacher->id }}"
                                            @checked(collect(old('selected_teacher_ids', $context['teachers']->pluck('id')->all()))->contains($teacher->id))
                                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="truncate">{{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-card p-4 border-l-4 border-amber-500">
                            <label class="input-label text-[10px] uppercase mb-3">Fixed Lecture Rooms</label>
                            <div class="space-y-2 text-[11px] font-semibold text-slate-600">
                                @foreach($context['years'] as $year)
                                    @php $room = $context['lecture_rooms'][(int) $year] ?? null; @endphp
                                    <div class="flex justify-between items-center py-1 border-b border-slate-50 last:border-0">
                                        <span>Y{{ $year }}</span>
                                        @if($room)
                                            <span class="text-slate-800">{{ $room->name }}</span>
                                        @else
                                            <span class="text-rose-600 font-black">UNASSIGNED</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="glass-card p-4 border-l-4 border-rose-500">
                            <label class="input-label text-[10px] uppercase mb-3">Lab Classrooms</label>
                            <div class="max-h-40 overflow-y-auto space-y-2 pr-2">
                                @forelse($context['lab_rooms'] as $room)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-rose-600 transition-colors">
                                        <input
                                            type="checkbox"
                                            name="selected_classroom_ids[]"
                                            value="{{ $room->id }}"
                                            @checked(collect(old('selected_classroom_ids', $context['lab_rooms']->pluck('id')->all()))->contains($room->id))
                                            class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                        <span class="truncate">{{ $room->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-[10px] text-slate-400 italic">No lab nodes mapped.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @error('generator')
                        <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-xs text-rose-700 font-bold mb-4">
                            <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 max-w-sm">
                            <i class="bi bi-info-circle mr-1"></i>
                            The generator clears existing slots for the selected years before re-synchronizing based on constraints.
                        </p>
                        @if($canEditTimetable)
                            <button type="submit" class="btn-primary-gradient px-8 py-3 h-auto">
                                Run Generation Sequence
                            </button>
                        @else
                            <button disabled class="btn-outline opacity-50 cursor-not-allowed px-8 py-3">
                                Generation Locked
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        @if($gridData)
            <!-- ─── Interactive Timetable Grid ─── -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Master Synchrony Grid</h3>
                        <p class="text-xs text-slate-500 mt-1">Status: <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-black uppercase text-[9px]">Optimized</span></p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Semester Cycle</span>
                        <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-full font-black text-slate-700 uppercase tracking-tighter">
                            {{ $gridData['semester_type'] }}
                        </span>
                    </div>
                </div>

                <div class="space-y-12">
                    @foreach($gridData['years'] as $year)
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-slate-800 text-white flex items-center justify-center font-black text-sm">
                                    {{ $year }}
                                </span>
                                <h4 class="font-black text-slate-800 text-lg tracking-tight">Scholastic Year {{ $year }} Grid</h4>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                                <table class="table-premium min-w-[1000px] border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/50">
                                            <th class="w-24 text-center border-r border-slate-200">Session</th>
                                            @foreach($gridData['days'] as $day)
                                                <th class="border-r border-slate-200 last:border-0">{{ ucfirst($day) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gridData['slots'] as $slot)
                                            <tr class="group hover:bg-slate-50/30 transition-colors">
                                                <td class="bg-slate-50/30 text-center border-r border-slate-200 py-6">
                                                    <span class="text-xs font-black text-slate-700 block">S-{{ $slot }}</span>
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">
                                                        {{ $gridData['time_slots']->get($slot - 1) }}
                                                    </span>
                                                </td>
                                                @foreach($gridData['days'] as $day)
                                                    @php $entry = $gridData['grid'][(int) $year][$day][$slot] ?? null; @endphp
                                                    <td class="p-3 align-top border-r border-slate-200 last:border-0 min-w-[220px]">
                                                        @if($entry)
                                                            <form method="POST" action="{{ route('hod.timetable.update', $entry) }}" class="space-y-1.5 opacity-90 hover:opacity-100 transition-opacity">
                                                                @csrf
                                                                @method('PUT')

                                                                <div class="relative group/field">
                                                                    <select name="subject_id" class="input-premium text-[11px] py-1 pl-2 pr-6 h-8 font-bold border-l-4 border-indigo-500 rounded-lg shadow-sm" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['subjects_by_year'][(int) $year] as $sbj)
                                                                            <option value="{{ $sbj->id }}" @selected((int) $entry->subject_id === (int) $sbj->id)>
                                                                                {{ $sbj->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="relative group/field">
                                                                    <select name="teacher_id" class="input-premium text-[11px] py-1 pl-2 pr-6 h-8 font-semibold border-l-4 border-emerald-500 rounded-lg shadow-sm" @disabled(!$canEditTimetable)>
                                                                        @foreach($context['teachers'] as $t)
                                                                            <option value="{{ $t->id }}" @selected((int) $entry->teacher_id === (int) $t->id)>
                                                                                {{ $t->user?->name ?? ('Teacher '.$t->id) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="relative group/field">
                                                                    <select name="classroom_id" class="input-premium text-[11px] py-1 pl-2 pr-6 h-8 font-semibold border-l-4 border-amber-500 rounded-lg shadow-sm" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['classrooms_by_year'][(int) $year] as $rm)
                                                                            <option value="{{ $rm->id }}" @selected((int) $entry->classroom_id === (int) $rm->id)>
                                                                                {{ $rm->name }} ({{ strtoupper($rm->type) }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="hidden group-hover:flex items-center gap-1 pt-1">
                                                                    <div class="grid grid-cols-2 gap-1 flex-1">
                                                                        <select name="day" class="input-premium text-[9px] h-7 px-1 font-bold" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['days'] as $d)
                                                                                <option value="{{ $d }}" @selected($entry->day === $d)>{{ strtoupper(substr($d,0,3)) }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <select name="slot_number" class="input-premium text-[9px] h-7 px-1 font-bold" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['slots'] as $s)
                                                                                <option value="{{ $s }}" @selected((int) $entry->slot_number === (int) $s)>S-{{ $s }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if($canEditTimetable)
                                                                        <button class="bg-slate-800 text-white p-1.5 rounded-lg hover:bg-black transition-colors" title="Save Slot">
                                                                            <i class="bi bi-save text-[10px]"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                                @error('entry_'.$entry->id)
                                                                    <p class="text-[9px] text-rose-600 font-bold leading-tight">{{ $message }}</p>
                                                                @enderror
                                                            </form>
                                                        @else
                                                            <div class="h-24 border-2 border-dashed border-slate-100 rounded-xl flex items-center justify-center group/empty transition-all hover:border-slate-200">
                                                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest group-hover/empty:text-slate-400">Idle Node</span>
                                                            </div>
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
                </div>
            </div>
        @else
            <div class="glass-card p-20 flex flex-col items-center justify-center text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center">
                    <i class="bi bi-calendar4-week text-slate-300 text-3xl"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-lg">Nexus Cycle Standby</h3>
                    <p class="text-sm text-slate-400 max-w-xs mx-auto">Select a course and semester type to initiate the synchrony management module.</p>
                </div>
            </div>
        @endif
    </div>
@endsection
