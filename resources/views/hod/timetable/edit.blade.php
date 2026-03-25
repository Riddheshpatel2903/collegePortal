@extends('layouts.app')

@section('header_title', 'Edit Timetable Slot')

@section('content')
    <div class="max-w-4xl space-y-6">
        <!-- ─── Page Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Edit Timetable Slot</h2>
                <p class="text-sm text-slate-500">Modify faculty, room, or timing details for this specific session.</p>
            </div>
            <a href="{{ route('hod.timetable.index', ['course_id' => $course?->id, 'academic_year' => $year]) }}" class="btn-outline">
                <i class="bi bi-arrow-left mr-2"></i> Back to Grid
            </a>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- ─── Form Section ─── -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('hod.timetable.update', $schedule) }}" class="glass-card p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="input-label">Subject</label>
                            <select name="subject_id" class="input-premium" required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((int) old('subject_id', $schedule->subject_id) === (int) $subject->id)>
                                        {{ $subject->name }} (Sem {{ $subject->semester_sequence }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[10px] text-slate-400 font-medium uppercase tracking-tight">Only subjects from the course curriculum are listed.</p>
                        </div>

                        <div>
                            <label class="input-label">Teacher / Faculty</label>
                            <select name="teacher_id" class="input-premium" required>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $schedule->teacher_id) === (int) $teacher->id)>
                                        {{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="input-label">Classroom / Laboratory</label>
                            <select name="classroom_id" class="input-premium" required>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" @selected((int) old('classroom_id', $schedule->classroom_id) === (int) $classroom->id)>
                                        {{ $classroom->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $portalAccess = app(\App\Services\PortalAccessService::class);
                            $availableDays = $portalAccess->workingDays();
                            $slotBlocks = collect(config('timetable.slot_blocks', []))->take($portalAccess->slotsPerDay())->all();
                            $currentSlot = old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) . '-' . old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i'));
                        @endphp

                        <div>
                            <label class="input-label">Day of Week</label>
                            <select name="day_of_week" class="input-premium" required>
                                @foreach($availableDays as $day)
                                    <option value="{{ $day }}" @selected(old('day_of_week', $schedule->day_of_week) === $day)>
                                        {{ ucfirst($day) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="input-label">Time Block</label>
                            <select id="slotBlockSelect" class="input-premium" required>
                                @foreach($slotBlocks as $block)
                                    @php $value = $block[0].'-'.$block[1]; @endphp
                                    <option value="{{ $value }}" @selected($currentSlot === $value)>
                                        {{ $block[0] }} - {{ $block[1] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="start_time" id="startTimeField" value="{{ old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}">
                            <input type="hidden" name="end_time" id="endTimeField" value="{{ old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}">
                        </div>
                    </div>

                    @if($errors->any())
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 space-y-1">
                            <p class="font-bold flex items-center gap-2"><i class="bi bi-exclamation-triangle-fill"></i> Validation Errors</p>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <a href="{{ route('hod.timetable.index', ['course_id' => $course?->id, 'academic_year' => $year]) }}" class="btn-outline px-6">Cancel</a>
                        <button type="submit" class="btn-primary-gradient px-10">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- ─── Side Info Card ─── -->
            <div class="space-y-6">
                <div class="glass-card p-5 bg-indigo-50/30 border-indigo-100">
                    <h4 class="font-black text-slate-800 text-sm mb-3">Conflict Checking</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        When you save this slot, the system automatically verifies:
                        <br>• Faculty availability vs. other assignments.
                        <br>• Room occupancy for the selected time.
                        <br>• Student group (class) concurrent sessions.
                    </p>
                </div>

                <div class="glass-card p-5 border-amber-100">
                    <h4 class="font-black text-amber-800 text-sm mb-3">Context Insight</h4>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Course / Department</span>
                            <span class="text-sm font-semibold text-slate-700">{{ $course?->name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Academic Year</span>
                            <span class="text-sm font-semibold text-slate-700">Year {{ $year }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                const slotBlockSelect = document.getElementById('slotBlockSelect');
                const startTimeField = document.getElementById('startTimeField');
                const endTimeField = document.getElementById('endTimeField');

                function syncSlotFields() {
                    if (!slotBlockSelect?.value) return;
                    const parts = slotBlockSelect.value.split('-');
                    if (parts.length !== 2) return;
                    startTimeField.value = parts[0];
                    endTimeField.value = parts[1];
                }

                slotBlockSelect?.addEventListener('change', syncSlotFields);
                syncSlotFields();
            })();
        </script>
    @endpush
@endsection
