@extends('layouts.app')

@section('header_title', 'Automated Timetable')

@section('content')
    @php
        $portalAccess = app(\App\Services\PortalAccessService::class);
        $canEditTimetable = $portalAccess->featureEnabled('timetable_edit_enabled', true) && !$portalAccess->featureEnabled('semester_lock', false);
    @endphp

    <div class="space-y-6">
        <!-- ─── Page Header ─── -->
        <x-page-header 
            title="Automated Timetable" 
            subtitle="Generate and manage academic schedules with automated conflict detection and room allocation."
            icon="bi-calendar-range"
        >
            <x-slot name="action">
                @if($context)
                    <div class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest">Active: {{ $context['course']->name }}</span>
                    </div>
                @endif
            </x-slot>
        </x-page-header>

        <!-- ─── Context Selection ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-50 pb-3">Course & Semester Selection</h3>
            <form method="GET" class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Academic Program</label>
                    <select name="course_id" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                        <option value="">Select course...</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) old('course_id', $selectedCourseId) === (int) $course->id)>
                                {{ $course->name }} ({{ $course->department?->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Semester Type</label>
                    <select name="semester_type" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" required>
                        <option value="odd" @selected(old('semester_type', $selectedSemesterType) === 'odd')>Odd Semester (1, 3, 5, 7)</option>
                        <option value="even" @selected(old('semester_type', $selectedSemesterType) === 'even')>Even Semester (2, 4, 6, 8)</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-11 bg-slate-800 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-900 transition-all shadow-md">
                        Load Configuration
                    </button>
                </div>
            </form>
        </div>

        @if($context)
            <!-- ─── Generation Settings ─── -->
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm relative overflow-hidden">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-100">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Generation Engine</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Automated Scheduling Logic</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.timetable-auto.generate') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $context['course']->id }}">
                    <input type="hidden" name="semester_type" value="{{ $context['semester_type'] }}">

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Years -->
                        <div class="bg-slate-50 border border-slate-100 p-5 rounded-2xl">
                            <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4">Target Years</label>
                            <div class="space-y-3">
                                @foreach($context['years'] as $year)
                                    <label class="flex items-center gap-3 p-2 bg-white rounded-xl border border-slate-100 cursor-pointer hover:border-indigo-200 transition-all">
                                        <input type="checkbox" name="selected_years[]" value="{{ $year }}"
                                            @checked(collect(old('selected_years', $context['years']->all()))->contains($year))
                                            class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-0">
                                        <span class="text-xs font-bold text-slate-700">Year {{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Teachers -->
                        <div class="bg-slate-50 border border-slate-100 p-5 rounded-2xl">
                            <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4">Available Faculty</label>
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                @foreach($context['teachers'] as $teacher)
                                    <label class="flex items-center gap-3 p-2 bg-white rounded-xl border border-slate-100 cursor-pointer hover:border-emerald-200 transition-all">
                                        <input type="checkbox" name="selected_teacher_ids[]" value="{{ $teacher->id }}"
                                            @checked(collect(old('selected_teacher_ids', $context['teachers']->pluck('id')->all()))->contains($teacher->id))
                                            class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-0">
                                        <span class="text-[11px] font-bold text-slate-700 truncate">{{ $teacher->user?->name ?? 'Teacher '.$teacher->id }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Classrooms -->
                        <div class="bg-slate-50 border border-slate-100 p-5 rounded-2xl">
                            <label class="block text-[10px] font-black text-amber-600 uppercase tracking-widest mb-4">Lecture Venues</label>
                            <div class="space-y-2">
                                @foreach($context['years'] as $year)
                                    @php $room = $context['lecture_rooms'][(int) $year] ?? null; @endphp
                                    <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-400">Y{{ $year }}</span>
                                        @if($room)
                                            <span class="text-[11px] font-black text-slate-800 uppercase italic">{{ $room->name }}</span>
                                        @else
                                            <span class="text-[9px] font-bold text-rose-500 uppercase">Unassigned</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Labs -->
                        <div class="bg-slate-50 border border-slate-100 p-5 rounded-2xl">
                            <label class="block text-[10px] font-black text-rose-600 uppercase tracking-widest mb-4">Lab Facilities</label>
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                @forelse($context['lab_rooms'] as $room)
                                    <label class="flex items-center gap-3 p-2 bg-white rounded-xl border border-slate-100 cursor-pointer hover:border-rose-200 transition-all">
                                        <input type="checkbox" name="selected_classroom_ids[]" value="{{ $room->id }}"
                                            @checked(collect(old('selected_classroom_ids', $context['lab_rooms']->pluck('id')->all()))->contains($room->id))
                                            class="h-5 w-5 rounded border-slate-300 text-rose-600 focus:ring-0">
                                        <span class="text-[11px] font-bold text-slate-700 truncate">{{ $room->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-[10px] text-slate-400 italic text-center py-8">No labs discovered.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @error('generator')
                        <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl flex items-center gap-3 text-rose-600">
                            <i class="bi bi-exclamation-octagon text-lg"></i>
                            <div class="text-xs font-bold uppercase tracking-tight">{{ $message }}</div>
                        </div>
                    @enderror

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-6 border-t border-slate-100">
                        <div class="max-w-md">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Scheduling Philosophy</h4>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">System prioritizes laboratory blocks and faculty rest periods before filling remaining lecture slots with conflict-free classroom assignments.</p>
                        </div>
                        @if($canEditTimetable)
                            <button type="submit" class="inline-flex items-center gap-3 px-10 py-4 bg-indigo-600 text-white rounded-xl text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">
                                <i class="bi bi-lightning-charge-fill"></i>
                                Start Auto-Generation
                            </button>
                        @else
                            <div class="px-8 py-4 bg-slate-100 rounded-xl flex items-center gap-3 text-slate-400 grayscale opacity-60">
                                <i class="bi bi-lock-fill"></i>
                                <span class="text-[10px] font-bold uppercase tracking-widest">Controls Locked</span>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        @if($gridData)
            <!-- ─── Timetable Grid ─── -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
                <div class="flex items-center justify-between mb-10 border-b border-slate-50 pb-8">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Active Schedule Grid</h3>
                        <div class="flex items-center gap-3 mt-3">
                            <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-bold uppercase border border-emerald-100">
                                <i class="bi bi-check2-circle mr-1.5"></i> Conflict Checked
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cycle: {{ ucfirst($gridData['semester_type']) }} Term</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-16">
                    @foreach($gridData['years'] as $year)
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-md">
                                    {{ $year }}
                                </div>
                                <h4 class="text-xl font-bold text-slate-800 tracking-tight">Year {{ $year }} Timetable</h4>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                <table class="w-full text-left border-collapse min-w-[1000px]">
                                    <thead>
                                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                            <th class="px-6 py-4 w-32 text-center border-r border-slate-100">Timing</th>
                                            @foreach($gridData['days'] as $day)
                                                <th class="px-6 py-4 border-r border-slate-100 last:border-0">{{ $day }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($gridData['slots'] as $slot)
                                            <tr>
                                                <td class="px-4 py-8 bg-slate-50/30 text-center border-r border-slate-100">
                                                    <span class="text-xs font-bold text-slate-800 block mb-1">Slot-{{ $slot }}</span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase">{{ $gridData['time_slots']->get($slot - 1) }}</span>
                                                </td>
                                                @foreach($gridData['days'] as $day)
                                                    @php $entry = $gridData['grid'][(int) $year][$day][$slot] ?? null; @endphp
                                                    <td class="p-4 align-top border-r border-slate-100 last:border-0">
                                                        @if($entry)
                                                            <form method="POST" action="{{ route('admin.timetable-auto.entries.update', $entry) }}" class="space-y-3 group/entry">
                                                                @csrf @method('PUT')
                                                                
                                                                <div class="space-y-1.5">
                                                                    <select name="subject_id" class="w-full h-8 bg-white border border-slate-200 rounded-lg py-1 px-2 text-[10px] font-bold focus:ring-0 focus:border-indigo-500 text-slate-700" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['subjects_by_year'][(int) $year] as $sbj)
                                                                            <option value="{{ $sbj->id }}" @selected((int) $entry->subject_id === (int) $sbj->id)>{{ $sbj->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    
                                                                    <select name="teacher_id" class="w-full h-8 bg-white border border-slate-200 rounded-lg py-1 px-2 text-[9px] font-medium focus:ring-0 focus:border-indigo-500 text-slate-500" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['teachers'] as $t)
                                                                            <option value="{{ $t->id }}" @selected((int) $entry->teacher_id === (int) $t->id)>{{ $t->user?->name ?? 'Teacher '.$t->id }}</option>
                                                                        @endforeach
                                                                    </select>

                                                                    <select name="classroom_id" class="w-full h-8 bg-white border border-slate-200 rounded-lg py-1 px-2 text-[9px] font-medium focus:ring-0 focus:border-indigo-500 text-slate-500" @disabled(!$canEditTimetable)>
                                                                        @foreach($gridData['classrooms_by_year'][(int) $year] as $rm)
                                                                            <option value="{{ $rm->id }}" @selected((int) $entry->classroom_id === (int) $rm->id)>{{ $rm->name }} ({{ strtoupper($rm->type) }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                @if($canEditTimetable)
                                                                    <div class="flex items-center gap-1.5 pt-2 opacity-0 group-hover/entry:opacity-100 transition-opacity">
                                                                        <div class="grid grid-cols-2 gap-1 flex-1">
                                                                            <select name="day" class="h-7 bg-slate-50 border-slate-100 rounded text-[8px] font-bold uppercase tracking-widest px-1">
                                                                                @foreach($gridData['days'] as $d)
                                                                                    <option value="{{ $d }}" @selected($entry->day === $d)>{{ substr($d,0,3) }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <select name="slot_number" class="h-7 bg-slate-50 border-slate-100 rounded text-[8px] font-bold uppercase tracking-widest px-1">
                                                                                @foreach($gridData['slots'] as $s)
                                                                                    <option value="{{ $s }}" @selected((int) $entry->slot_number === (int) $s)>S{{ $s }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <button class="h-7 w-7 bg-indigo-600 text-white rounded flex items-center justify-center hover:bg-indigo-700 transition-all shadow-sm">
                                                                            <i class="bi bi-save2 text-[10px]"></i>
                                                                        </button>
                                                                    </div>
                                                                @endif

                                                                @error('entry_'.$entry->id)
                                                                    <p class="text-[8px] font-bold text-rose-500 bg-rose-50 px-2 py-1 rounded border border-rose-100">{{ $message }}</p>
                                                                @enderror
                                                            </form>
                                                        @else
                                                            <div class="h-32 bg-slate-50 border-2 border-dashed border-slate-100 rounded-2xl flex flex-col items-center justify-center text-slate-300 group hover:bg-white hover:border-slate-200 transition-all">
                                                                <i class="bi bi-plus-circle-dotted text-xl mb-1 opacity-20 group-hover:opacity-100"></i>
                                                                <span class="text-[9px] font-bold uppercase tracking-widest opacity-40 group-hover:opacity-100">Empty Slot</span>
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
            <!-- ─── Empty State ─── -->
            <div class="bg-white border border-slate-200 border-dashed rounded-3xl py-32 text-center">
                <div class="h-24 w-24 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center text-5xl mx-auto mb-8">
                    <i class="bi bi-layers-half"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Configure Schedule Context</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto font-medium">Select a course and semester to initiate the automated generation engine.</p>
                <div class="flex justify-center gap-4 mt-10">
                    <div class="px-4 py-2 bg-slate-100 rounded-xl text-[9px] font-bold text-slate-400 uppercase tracking-widest border border-slate-200">Constraint-Aware</div>
                    <div class="px-4 py-2 bg-slate-100 rounded-xl text-[9px] font-bold text-slate-400 uppercase tracking-widest border border-slate-200">Conflict-Free</div>
                    <div class="px-4 py-2 bg-slate-100 rounded-xl text-[9px] font-bold text-slate-400 uppercase tracking-widest border border-slate-200">Load-Balanced</div>
                </div>
            </div>
        @endif
    </div>
@endsection
