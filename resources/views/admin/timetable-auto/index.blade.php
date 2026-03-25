@extends('layouts.app')

@section('header_title', 'Universal Timetable Nexus')

@section('content')
    @php
        $portalAccess = app(\App\Services\PortalAccessService::class);
        $canEditTimetable = $portalAccess->featureEnabled('timetable_edit_enabled', true) && !$portalAccess->featureEnabled('semester_lock', false);
    @endphp

    <div class="space-y-6">
        <!-- ─── Academic Nexus Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Universal Timetable Nexus</h2>
                <p class="text-sm text-slate-500">Global synchronization engine for course schedules and faculty allocation.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($context)
                    <div class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-indigo-700 uppercase tracking-tighter">Context Active: {{ $context['course']->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- ─── Context Selection Filter ─── -->
        <div class="glass-card p-6">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Scholastic Context Discovery</h3>
            <form method="GET" class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="input-label text-[10px] uppercase">Academic Course</label>
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
                    <label class="input-label text-[10px] uppercase">Semester Cycle</label>
                    <select name="semester_type" class="input-premium" required>
                        <option value="odd" @selected(old('semester_type', $selectedSemesterType) === 'odd')>Odd Semester</option>
                        <option value="even" @selected(old('semester_type', $selectedSemesterType) === 'even')>Even Semester</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-outline w-full py-3">Load Context

                    </button>
                </div>
            </form>
        </div>

        @if($context)
            <!-- ─── Generation Logic & Strategy ─── -->
            <div class="glass-card p-6 border-t-4 border-indigo-500">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-slate-950 flex items-center justify-center">
                        <i class="bi bi-cpu text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-lg">Synchrony Generation Engine</h3>
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-tighter">Algorithmic Constraints & Allocation</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.timetable-auto.generate') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $context['course']->id }}">
                    <input type="hidden" name="semester_type" value="{{ $context['semester_type'] }}">

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Year Nodes -->
                        <div class="glass-card p-4 bg-indigo-50/30 border-l-4 border-indigo-500">
                            <label class="input-label text-[10px] uppercase mb-4 text-indigo-700 font-black">Scholastic Nodes (Years)</label>
                            <div class="space-y-2">
                                @foreach($context['years'] as $year)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white p-1 rounded-lg transition-all">
                                        <input
                                            type="checkbox"
                                            name="selected_years[]"
                                            value="{{ $year }}"
                                            @checked(collect(old('selected_years', $context['years']->all()))->contains($year))
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-bold text-slate-700">Year {{ $year }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Faculty Nodes -->
                        <div class="glass-card p-4 bg-emerald-50/30 border-l-4 border-emerald-500">
                            <label class="input-label text-[10px] uppercase mb-4 text-emerald-700 font-black">Active Faculty Nodes</label>
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-2">
                                @foreach($context['teachers'] as $teacher)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white p-1 rounded-lg transition-all">
                                        <input
                                            type="checkbox"
                                            name="selected_teacher_ids[]"
                                            value="{{ $teacher->id }}"
                                            @checked(collect(old('selected_teacher_ids', $context['teachers']->pluck('id')->all()))->contains($teacher->id))
                                            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        <span class="truncate font-semibold text-slate-700">{{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Lecture Grid -->
                        <div class="glass-card p-4 bg-amber-50/30 border-l-4 border-amber-500">
                            <label class="input-label text-[10px] uppercase mb-4 text-amber-700 font-black">Fixed Lecture Mapping</label>
                            <div class="space-y-2">
                                @foreach($context['years'] as $year)
                                    @php $room = $context['lecture_rooms'][(int) $year] ?? null; @endphp
                                    <div class="flex justify-between items-center bg-white/50 p-2 rounded-lg border border-amber-100 shadow-sm">
                                        <span class="text-[10px] font-black text-amber-800">Y{{ $year }}</span>
                                        @if($room)
                                            <span class="text-xs font-bold text-slate-700">{{ $room->name }}</span>
                                        @else
                                            <span class="text-[9px] font-black text-rose-600 uppercase tracking-tighter">Unmapped</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Lab Nodes -->
                        <div class="glass-card p-4 bg-rose-50/30 border-l-4 border-rose-500">
                            <label class="input-label text-[10px] uppercase mb-4 text-rose-700 font-black">Lab Facility Nodes</label>
                            <div class="max-h-48 overflow-y-auto space-y-2 pr-2">
                                @forelse($context['lab_rooms'] as $room)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-white p-1 rounded-lg transition-all">
                                        <input
                                            type="checkbox"
                                            name="selected_classroom_ids[]"
                                            value="{{ $room->id }}"
                                            @checked(collect(old('selected_classroom_ids', $context['lab_rooms']->pluck('id')->all()))->contains($room->id))
                                            class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                        <span class="truncate font-semibold text-slate-700">{{ $room->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-[10px] text-slate-400 italic font-medium p-4 text-center">No lab facility nodes discovered.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @error('generator')
                        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-700">
                            <i class="bi bi-shield-lock-fill text-xl"></i>
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest">Generation Halt</p>
                                <p class="text-sm font-bold">{{ $message }}</p>
                            </div>
                        </div>
                    @enderror

                    <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                        <div class="max-w-md">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-relaxed mb-1">Algorithmic Strategy</p>
                            <p class="text-xs text-slate-500 font-medium">Lab sessions are prioritized first to ensure maximum facility utilization, followed by conflict-safe lecture slotting and faculty workload balancing.</p>
                        </div>
                        @if($canEditTimetable)
                            <button type="submit" class="btn-primary-gradient px-10 py-4 h-auto text-sm">
                                <i class="bi bi-lightning-charge mr-2"></i>
                                Initiate Global Synchrony
                            </button>
                        @else
                            <div class="px-6 py-3 bg-slate-100 rounded-xl flex items-center gap-2 text-slate-400 cursor-not-allowed">
                                <i class="bi bi-lock-fill"></i>
                                <span class="text-xs font-black uppercase tracking-tighter">System Locked</span>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        @if($gridData)
            <!-- ─── Master Synchrony Grid ─── -->
            <div class="glass-card p-6 border-t-4 border-slate-800">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Global Synchrony Grid</h3>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[9px] font-black uppercase tracking-tighter shadow-sm">
                                <i class="bi bi-check-circle-fill"></i>
                                Real-time Optimized
                            </span>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[9px] font-black uppercase tracking-tighter shadow-sm">
                                Cycle: {{ $gridData['semester_type'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-16">
                    @foreach($gridData['years'] as $year)
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-slate-800 text-white flex items-center justify-center font-black text-lg transition-transform hover:scale-110 shadow-lg shadow-slate-200">
                                    {{ $year }}
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-800 text-xl tracking-tight leading-none">Year {{ $year }} Grid</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Academic Node Performance</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-3xl border border-slate-200 shadow-xl shadow-slate-100/50">
                                <table class="table-premium min-w-[1000px] border-collapse bg-white">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200">
                                            <th class="w-32 text-center border-r border-slate-200 py-6">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Chronos</span>
                                            </th>
                                            @foreach($gridData['days'] as $day)
                                                <th class="border-r border-slate-200 last:border-0 py-6">
                                                    <span class="text-xs font-black text-slate-700 uppercase tracking-widest">{{ $day }}</span>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gridData['slots'] as $slot)
                                            <tr class="group hover:bg-slate-50/50 transition-all duration-300">
                                                <td class="bg-slate-50/30 text-center border-r border-slate-200 py-8 relative">
                                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-slate-200 group-hover:bg-slate-800 transition-colors"></div>
                                                    <span class="text-sm font-black text-slate-800 block mb-0.5">S-{{ $slot }}</span>
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">
                                                        {{ $gridData['time_slots']->get($slot - 1) }}
                                                    </span>
                                                </td>
                                                @foreach($gridData['days'] as $day)
                                                    @php $entry = $gridData['grid'][(int) $year][$day][$slot] ?? null; @endphp
                                                    <td class="p-4 align-top border-r border-slate-200 last:border-0 min-w-[240px]">
                                                        @if($entry)
                                                            <form method="POST" action="{{ route('admin.timetable-auto.entries.update', $entry) }}" class="space-y-2 opacity-95 hover:opacity-100 transition-opacity">
                                                                @csrf
                                                                @method('PUT')

                                                                <div class="space-y-1">
                                                                    <!-- Subject Node -->
                                                                    <div class="relative">
                                                                        <select name="subject_id" class="input-premium text-[11px] py-1.5 pl-3 h-9 font-black border-l-4 border-indigo-500 rounded-xl shadow-sm bg-white" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['subjects_by_year'][(int) $year] as $sbj)
                                                                                <option value="{{ $sbj->id }}" @selected((int) $entry->subject_id === (int) $sbj->id)>
                                                                                    {{ $sbj->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <!-- Faculty Node -->
                                                                    <div class="relative">
                                                                        <select name="teacher_id" class="input-premium text-[10px] py-1.5 pl-3 h-9 font-bold border-l-4 border-emerald-500 rounded-xl shadow-sm bg-white" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['teachers'] as $t)
                                                                                <option value="{{ $t->id }}" @selected((int) $entry->teacher_id === (int) $t->id)>
                                                                                    {{ $t->user?->name ?? ('Teacher '.$t->id) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <!-- Location Node -->
                                                                    <div class="relative">
                                                                        <select name="classroom_id" class="input-premium text-[10px] py-1.5 pl-3 h-9 font-bold border-l-4 border-amber-500 rounded-xl shadow-sm bg-white" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['classrooms_by_year'][(int) $year] as $rm)
                                                                                <option value="{{ $rm->id }}" @selected((int) $entry->classroom_id === (int) $rm->id)>
                                                                                    {{ $rm->name }} ({{ strtoupper($rm->type) }})
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5 pt-2">
                                                                    <div class="grid grid-cols-2 gap-1.5 flex-1">
                                                                        <select name="day" class="input-premium text-[9px] h-8 px-2 font-black uppercase tracking-tighter" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['days'] as $d)
                                                                                <option value="{{ $d }}" @selected($entry->day === $d)>{{ substr($d,0,3) }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        <select name="slot_number" class="input-premium text-[9px] h-8 px-2 font-black uppercase tracking-tighter" @disabled(!$canEditTimetable)>
                                                                            @foreach($gridData['slots'] as $s)
                                                                                <option value="{{ $s }}" @selected((int) $entry->slot_number === (int) $s)>S-{{ $s }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    @if($canEditTimetable)
                                                                        <button class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100" title="Sync Node">
                                                                            <i class="bi bi-arrow-repeat text-xs"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                                @error('entry_'.$entry->id)
                                                                    <p class="text-[9px] text-rose-600 font-bold bg-rose-50 px-2 py-1 rounded-lg border border-rose-100 mt-1">{{ $message }}</p>
                                                                @enderror
                                                            </form>
                                                        @else
                                                            <div class="h-32 border-2 border-dashed border-slate-100 rounded-3xl flex flex-col items-center justify-center group/empty transition-all hover:border-slate-300 hover:bg-slate-50/50">
                                                                <i class="bi bi-unlock text-slate-200 text-xl group-hover/empty:text-slate-400 mb-2"></i>
                                                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest group-hover/empty:text-slate-500">Idle Node</span>
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
            <div class="glass-card py-24 flex flex-col items-center justify-center text-center space-y-6">
                <div class="relative">
                    <div class="w-24 h-24 rounded-full bg-indigo-50 flex items-center justify-center">
                        <i class="bi bi-layers-half text-indigo-200 text-5xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">Synchrony Standby</h3>
                    <p class="text-sm text-slate-400 max-w-sm mx-auto font-medium mt-2">Initialize the global synchrony grid by discovering an academic course and semester cycle.</p>
                </div>
                <div class="flex gap-4 pt-4">
                    <div class="px-4 py-2 bg-slate-50 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-100">Conflict-Safe</div>
                    <div class="px-4 py-2 bg-slate-50 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-100">Lab-Optimized</div>
                    <div class="px-4 py-2 bg-slate-50 rounded-xl text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-100">Faculty-Aware</div>
                </div>
            </div>
        @endif
    </div>
@endsection
