@extends('layouts.app')

@section('header_title', 'Edit Timetable Slot')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="glass-card p-5">
            <h2 class="text-xl font-black text-slate-800">Edit Timetable Slot</h2>
            <p class="text-sm text-slate-500 mt-1">Update teacher, classroom, and timing. Conflicts are re-validated on save.</p>
        </div>

        <form method="POST" action="{{ route('hod.timetable.update', $schedule) }}" class="glass-card p-5 grid md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="input-label">Subject</label>
                <select name="subject_id" class="input-premium" required>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((int) old('subject_id', $schedule->subject_id) === (int) $subject->id)>
                            {{ $subject->name }} (Sem {{ $subject->semester_sequence }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="input-label">Teacher</label>
                <select name="teacher_id" class="input-premium" required>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $schedule->teacher_id) === (int) $teacher->id)>
                            {{ $teacher->user?->name ?? ('Teacher '.$teacher->id) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="input-label">Classroom</label>
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
            @endphp
            <div>
                <label class="input-label">Day</label>
                <select name="day_of_week" class="input-premium" required>
                    @foreach($availableDays as $day)
                        <option value="{{ $day }}" @selected(old('day_of_week', $schedule->day_of_week) === $day)>
                            {{ ucfirst($day) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div></div>

            @php
                // $slotBlocks defined above
                $currentSlot = old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) . '-' . old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i'));
            @endphp
            <div class="md:col-span-2">
                <label class="input-label">Time Slot</label>
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

            @if($errors->any())
                <div class="md:col-span-2 rounded border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="md:col-span-2 flex justify-between">
                <a href="{{ route('hod.timetable.index', ['course_id' => $course?->id, 'academic_year' => $year]) }}" class="btn-outline">Back</a>
                <button class="btn-primary-gradient">Save Slot</button>
            </div>
        </form>
    </div>
    @push('scripts')
        <script>
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
        </script>
    @endpush
@endsection
