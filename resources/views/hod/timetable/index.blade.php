@extends('layouts.app')

@section('header_title', 'Timetable Management')

@section('content')
    @php
        $portalAccess = app(\App\Services\PortalAccessService::class);
        $canEditTimetable = $portalAccess->featureEnabled('timetable_edit_enabled', true) && !$portalAccess->featureEnabled('semester_lock', false);
    @endphp

    <x-page-header 
        title="Departmental Timetable" 
        subtitle="Manage academic schedules, optimize faculty availability, and oversee classroom allocation for your department."
        icon="bi-calendar3"
    />

    <div class="mt-8 space-y-8" x-data="{ showAvailability: false }">
        {{-- Header Actions --}}
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hod.teacher-assignments.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    <i class="bi bi-person-workspace"></i> Faculty Assignments
                </a>
                <button @click="showAvailability = !showAvailability" class="px-5 py-2.5 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 flex items-center gap-2">
                    <i class="bi bi-clock-history"></i>
                    <span x-text="showAvailability ? 'Close Faculty Rules' : 'Configure Availability'"></span>
                </button>
            </div>
            @if($gridData)
                <div class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl border border-indigo-100 text-[10px] font-black uppercase tracking-widest">
                    Live Session Matrix: {{ strtoupper($gridData['semester_type']) }} Cycle
                </div>
            @endif
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">Schedule Selection Parameters</h3>
            <form method="GET" class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Academic Program</label>
                    <select name="course_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600" required>
                        <option value="">Select Course...</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id', $selectedCourseId) === (int) $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Semester Cycle</label>
                    <select name="semester_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600" required>
                        <option value="odd" @selected(old('semester_type', $selectedSemesterType) === 'odd')>Odd Semester</option>
                        <option value="even" @selected(old('semester_type', $selectedSemesterType) === 'even')>Even Semester</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-100">
                        Load Timetable Grid
                    </button>
                </div>
            </form>
        </div>

        {{-- Availability Rules --}}
        <div x-show="showAvailability" x-cloak x-transition class="space-y-6 bg-slate-50/50 p-8 rounded-3xl border border-slate-200">
            <div class="grid lg:grid-cols-2 gap-8">
                {{-- Add Rule --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h4 class="font-bold text-slate-800">New Faculty Rule</h4>
                    </div>
                    <form method="POST" action="{{ route('hod.timetable.availability.store') }}" class="grid grid-cols-2 gap-4">
                        @csrf
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Faculty Member</label>
                            <select name="teacher_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                                <option value="">Select Teacher...</option>
                                @if($context)
                                    @foreach($context['teachers'] as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? 'N/A' }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Day</label>
                            <select name="day_of_week" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">From</label>
                                <input type="time" name="start_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">To</label>
                                <input type="time" name="end_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                            </div>
                        </div>
                        <div class="col-span-2 pt-2">
                            <button class="w-full h-11 bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-md">
                                Save Availability Rule
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Rule Registry --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col h-[380px]">
                    <h4 class="font-bold text-slate-800 mb-6 flex items-center justify-between">
                        Rule Registry
                        <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-500 uppercase tracking-widest">Active Constraints</span>
                    </h4>
                    <div class="flex-1 overflow-y-auto space-y-4 pr-2">
                        @if($context)
                            @foreach($context['teachers'] as $teacher)
                                @php 
                                    $availabilities = app(\App\Models\TeacherAvailability::class)
                                        ->where('teacher_id', $teacher->id)
                                        ->orderBy('day_of_week')
                                        ->get();
                                @endphp
                                @if($availabilities->isNotEmpty())
                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p class="text-[11px] font-black text-slate-800 mb-3 truncate border-l-4 border-indigo-500 pl-3 uppercase">{{ $teacher->user?->name }}</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach($availabilities as $slot)
                                                <div class="flex items-center justify-between text-[10px] bg-white border border-slate-100 px-2.5 py-1.5 rounded-lg shadow-sm">
                                                    <span class="text-indigo-600 font-black uppercase tracking-tighter">{{ substr($slot->day_of_week, 0, 3) }}</span>
                                                    <span class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                                    <form method="POST" action="{{ route('hod.timetable.availability.destroy', $slot) }}">
                                                        @csrf @method('DELETE')
                                                        <button class="text-rose-400 hover:text-rose-600 transition-colors"><i class="bi bi-trash-fill"></i></button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="py-20 text-center opacity-30">
                                <i class="bi bi-shield-lock text-4xl mb-2 block"></i>
                                <p class="text-[10px] font-bold uppercase tracking-widest">Load course context to manage rules</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($context)
            {{-- Automation Section --}}
            <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="bi bi-magic text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Academic Schedule Generator</h3>
                        <p class="text-xs text-slate-400 font-medium">Auto-generate schedules based on predefined faculty rules and room availability.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('hod.timetable.generate') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $context['course']->id }}">
                    <input type="hidden" name="semester_type" value="{{ $context['semester_type'] }}">

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 border-l-4 border-l-indigo-500">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Target Years</label>
                            <div class="space-y-3">
                                @foreach($context['years'] as $year)
                                    <label class="flex items-center gap-3 text-sm cursor-pointer group">
                                        <input type="checkbox" name="selected_years[]" value="{{ $year }}"
                                            @checked(collect(old('selected_years', $context['years']->all()))->contains($year))
                                            class="w-5 h-5 rounded border-slate-200 text-indigo-600 focus:ring-0">
                                        <span class="font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">Academic Year {{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 border-l-4 border-l-teal-500">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Faculty Pool</label>
                            <div class="max-h-48 overflow-y-auto space-y-3 pr-2 scrollbar-thin">
                                @foreach($context['teachers'] as $teacher)
                                    <label class="flex items-center gap-3 text-sm cursor-pointer group">
                                        <input type="checkbox" name="selected_teacher_ids[]" value="{{ $teacher->id }}"
                                            @checked(collect(old('selected_teacher_ids', $context['teachers']->pluck('id')->all()))->contains($teacher->id))
                                            class="w-5 h-5 rounded border-slate-200 text-teal-600 focus:ring-0">
                                        <span class="font-medium text-slate-700 group-hover:text-teal-600 transition-colors truncate">{{ $teacher->user?->name ?? 'N/A' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 border-l-4 border-l-amber-500">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Lecture Allocation</label>
                            <div class="space-y-4">
                                @foreach($context['years'] as $year)
                                    @php $room = $context['lecture_rooms'][(int) $year] ?? null; @endphp
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-slate-500 uppercase">Year {{ $year }}</span>
                                        @if($room)
                                            <span class="text-xs font-black text-slate-800 bg-white border border-slate-200 px-2 py-0.5 rounded shadow-sm">{{ $room->name }}</span>
                                        @else
                                            <span class="text-[9px] font-black text-rose-500 uppercase bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Required</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 border-l-4 border-l-rose-500">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Lab Resources</label>
                            <div class="max-h-48 overflow-y-auto space-y-3 pr-2 scrollbar-thin">
                                @forelse($context['lab_rooms'] as $room)
                                    <label class="flex items-center gap-3 text-sm cursor-pointer group">
                                        <input type="checkbox" name="selected_classroom_ids[]" value="{{ $room->id }}"
                                            @checked(collect(old('selected_classroom_ids', $context['lab_rooms']->pluck('id')->all()))->contains($room->id))
                                            class="w-5 h-5 rounded border-slate-200 text-rose-600 focus:ring-0">
                                        <span class="font-medium text-slate-700 group-hover:text-rose-600 transition-colors truncate">{{ $room->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-[10px] text-slate-400 italic">No lab resources identified.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @error('generator')
                        <div class="p-5 bg-rose-50 border border-rose-100 rounded-2xl text-[11px] text-rose-700 font-black uppercase tracking-widest flex items-center gap-3">
                            <i class="bi bi-exclamation-octagon-fill text-lg"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="flex items-center justify-between pt-8 border-t border-slate-100">
                        <div class="flex items-center gap-3 text-slate-400">
                            <i class="bi bi-shield-lock-fill text-xl"></i>
                            <p class="text-[10px] font-bold uppercase tracking-widest leading-relaxed max-w-xs">
                                Generator actions are irreversible. Pre-existing slots for selected years will be overwritten.
                            </p>
                        </div>
                        @if($canEditTimetable)
                            <button type="submit" class="px-12 py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-2">
                                Launch Generator <i class="bi bi-caret-right-fill"></i>
                            </button>
                        @else
                            <div class="px-8 py-3 bg-slate-100 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 italic">
                                Generation Locked — Restricted Phase
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        @if($gridData)
            {{-- Timetable Grid --}}
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Departmental Schedule Grid</h3>
                        <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mt-1">{{ strtoupper($gridData['semester_type']) }} Semester Cycle Active</p>
                    </div>
                </div>

                <div class="p-8 space-y-16">
                    @foreach($gridData['years'] as $year)
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-slate-800 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-slate-200">
                                    {{ $year }}
                                </div>
                                <h4 class="font-black text-slate-800 text-lg uppercase tracking-tight">Academic Year {{ $year }} Schedule</h4>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                                <table class="w-full text-left border-collapse min-w-[1200px]">
                                    <thead>
                                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                                            <th class="w-32 py-5 text-center border-r border-slate-200 font-black">Time Slot</th>
                                            @foreach($gridData['days'] as $day)
                                                <th class="px-6 py-5 border-r border-slate-200 last:border-0">{{ ucfirst($day) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($gridData['slots'] as $slot)
                                            <tr>
                                                <td class="bg-slate-50/50 text-center border-r border-slate-200 py-8">
                                                    <span class="text-xs font-black text-slate-700 block mb-1">Slot {{ $slot }}</span>
                                                    <span class="text-[9px] font-bold text-slate-400 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-xs">
                                                        {{ $gridData['time_slots']->get($slot - 1) }}
                                                    </span>
                                                </td>
                                                @foreach($gridData['days'] as $day)
                                                    @php $entry = $gridData['grid'][(int) $year][$day][$slot] ?? null; @endphp
                                                    <td class="p-4 align-top border-r border-slate-200 last:border-0 min-w-[240px]">
                                                        @if($entry)
                                                            <form method="POST" action="{{ route('hod.timetable.update', $entry) }}" class="space-y-3 p-3 bg-white border border-slate-200 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group">
                                                                @csrf
                                                                @method('PUT')

                                                                <div class="space-y-1.5">
                                                                    <div class="flex items-center justify-between px-1">
                                                                        <label class="text-[8px] font-black text-indigo-400 uppercase tracking-widest">Active Subject</label>
                                                                    </div>
                                                                    <select name="subject_id" class="w-full text-[11px] h-9 font-bold border-slate-200 rounded-xl bg-slate-50 focus:ring-0 focus:border-indigo-500 transition-all" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['subjects_by_year'][(int) $year] as $sbj)
                                                                            <option value="{{ $sbj->id }}" @selected((int) $entry->subject_id === (int) $sbj->id)>
                                                                                {{ $sbj->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="space-y-1.5">
                                                                    <div class="flex items-center justify-between px-1">
                                                                        <label class="text-[8px] font-black text-emerald-400 uppercase tracking-widest">Faculty Member</label>
                                                                    </div>
                                                                    <select name="teacher_id" class="w-full text-[11px] h-9 font-bold border-slate-200 rounded-xl bg-slate-50 focus:ring-0 focus:border-emerald-500 transition-all" @disabled(!$canEditTimetable)>
                                                                        @foreach($context['teachers'] as $t)
                                                                            <option value="{{ $t->id }}" @selected((int) $entry->teacher_id === (int) $t->id)>
                                                                                {{ $t->user?->name ?? 'N/A' }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="space-y-1.5">
                                                                    <div class="flex items-center justify-between px-1">
                                                                        <label class="text-[8px] font-black text-amber-400 uppercase tracking-widest">Spatial Allocation</label>
                                                                    </div>
                                                                    <select name="classroom_id" class="w-full text-[11px] h-9 font-bold border-slate-200 rounded-xl bg-slate-50 focus:ring-0 focus:border-amber-500 transition-all" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['classrooms_by_year'][(int) $year] as $rm)
                                                                            <option value="{{ $rm->id }}" @selected((int) $entry->classroom_id === (int) $rm->id)>
                                                                                {{ $rm->name }} ({{ strtoupper($rm->type) }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="pt-2 flex items-center gap-2">
                                                                    <div class="grid grid-cols-2 gap-1 flex-1">
                                                                        <select name="day" class="text-[9px] h-8 px-1 font-black rounded-lg border-slate-200 bg-slate-50" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['days'] as $d)
                                                                                <option value="{{ $d }}" @selected($entry->day === $d)>{{ strtoupper(substr($d,0,3)) }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <select name="slot_number" class="text-[9px] h-8 px-1 font-black rounded-lg border-slate-200 bg-slate-50" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['slots'] as $s)
                                                                                <option value="{{ $s }}" @selected((int) $entry->slot_number === (int) $s)>S-{{ $s }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if($canEditTimetable)
                                                                        <button class="bg-indigo-600 text-white h-8 w-8 rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-50 flex items-center justify-center" title="Save Modifications">
                                                                            <i class="bi bi-check-lg text-xs"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                                @error('entry_'.$entry->id)
                                                                    <p class="text-[9px] text-rose-600 font-black mt-1 uppercase tracking-tighter leading-tight">{{ $message }}</p>
                                                                @enderror
                                                            </form>
                                                        @else
                                                            <div class="h-32 border-2 border-dashed border-slate-100 rounded-2xl flex items-center justify-center text-slate-200 transition-all hover:bg-slate-50 hover:text-slate-300">
                                                                <span class="text-[10px] font-black uppercase tracking-widest">Unallocated Period</span>
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
            <div class="bg-white border border-slate-200 rounded-3xl p-32 flex flex-col items-center justify-center text-center space-y-6 shadow-sm">
                <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 shadow-inner">
                    <i class="bi bi-calendar4-week text-slate-300 text-4xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xl">Initialization Required</h3>
                    <p class="text-sm text-slate-400 max-w-sm mx-auto font-medium leading-relaxed">Select a specific course and semester cycle above to access the department's scheduling management dashboard.</p>
                </div>
            </div>
        @endif
    </div>
@endsection
