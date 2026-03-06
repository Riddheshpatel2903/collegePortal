@php
    $isEdit = isset($schedule);
    $formAction = $isEdit ? route('admin.schedules.update', $schedule) : route('admin.schedules.store');

    $portalAccess = app(\App\Services\PortalAccessService::class);
    $availableDays = $portalAccess->workingDays();
    $slotBlocks = collect(config('timetable.slot_blocks', []))->take($portalAccess->slotsPerDay())->all();
@endphp

<form method="POST" action="{{ $formAction }}" class="glass-card p-6 space-y-5">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div id="scheduleFormNotice" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"></div>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="input-label">Department</label>
            <select name="department_id" id="departmentSelect" class="input-premium" required>
                <option value="">Select department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id', $selectedDepartmentId ?? null) == $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Course</label>
            <select name="course_id" id="courseSelect" class="input-premium" required>
                <option value="">Select course</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id', $selectedCourseId ?? null) == $course->id)>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
            @error('course_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Year</label>
            <select name="academic_year" id="yearSelect" class="input-premium" required>
                <option value="">Select year</option>
                @foreach($years as $year)
                    <option value="{{ $year['id'] }}" @selected(old('academic_year', $selectedAcademicYear ?? null) == $year['id'])>
                        {{ $year['name'] }}
                    </option>
                @endforeach
            </select>
            @error('academic_year')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Subject</label>
            <select name="subject_id" id="subjectSelect" class="input-premium" required>
                <option value="">Select subject</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $schedule->subject_id ?? null) == $subject->id)>
                        {{ $subject->name }} (Sem {{ $subject->semester_sequence }})
                    </option>
                @endforeach
            </select>
            @error('subject_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Teacher</label>
            <select name="teacher_id" id="teacherSelect" class="input-premium" required>
                <option value="">Select teacher</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected(old('teacher_id', $schedule->teacher_id ?? null) == $teacher->id)>
                        {{ $teacher->user->name ?? 'Teacher '.$teacher->id }}
                    </option>
                @endforeach
            </select>
            @error('teacher_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Classroom</label>
            <select name="classroom_id" class="input-premium" required>
                <option value="">Select room</option>
                @foreach($classrooms as $room)
                    <option value="{{ $room->id }}" @selected(old('classroom_id', $schedule->classroom_id ?? null) == $room->id)>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
            @error('classroom_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="input-label">Day</label>
            <select name="day_of_week" class="input-premium" required>
                @foreach($availableDays as $day)
                    <option value="{{ $day }}" @selected(old('day_of_week', $schedule->day_of_week ?? null) === $day)>
                        {{ ucfirst($day) }}
                    </option>
                @endforeach
            </select>
            @error('day_of_week')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>

        @php
            // $slotBlocks already computed above to respect slots-per-day
            $defaultSlot = old('start_time', isset($schedule) ? substr($schedule->start_time,0,5) : '09:00')
                . '-'
                . old('end_time', isset($schedule) ? substr($schedule->end_time,0,5) : '10:00');
        @endphp
        <div class="md:col-span-2">
            <label class="input-label">Time Slot</label>
            <select id="slotBlockSelect" class="input-premium" required>
                @foreach($slotBlocks as $block)
                    @php $value = $block[0].'-'.$block[1]; @endphp
                    <option value="{{ $value }}" @selected($defaultSlot === $value)>{{ $block[0] }} - {{ $block[1] }}</option>
                @endforeach
            </select>
            <input type="hidden" name="start_time" id="startTimeField" value="{{ old('start_time', isset($schedule) ? substr($schedule->start_time,0,5) : '09:00') }}">
            <input type="hidden" name="end_time" id="endTimeField" value="{{ old('end_time', isset($schedule) ? substr($schedule->end_time,0,5) : '10:00') }}">
            @error('start_time')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            @error('end_time')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.schedules.index') }}" class="btn-outline">Cancel</a>
        <button type="submit" class="btn-primary-gradient">{{ $isEdit ? 'Update Slot' : 'Add Slot' }}</button>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        try {
            const departmentSelect = document.getElementById('departmentSelect');
            const courseSelect = document.getElementById('courseSelect');
            const yearSelect = document.getElementById('yearSelect');
            const subjectSelect = document.getElementById('subjectSelect');
            const teacherSelect = document.getElementById('teacherSelect');
            const notice = document.getElementById('scheduleFormNotice');

            const selectedSubjectId = @json(old('subject_id', $schedule->subject_id ?? null));
            const selectedTeacherId = @json(old('teacher_id', $schedule->teacher_id ?? null));
            const selectedCourseId = @json(old('course_id', $selectedCourseId ?? null));
            const selectedAcademicYear = @json(old('academic_year', $selectedAcademicYear ?? null));

            const showNotice = (message) => {
                if (!notice) return;
                notice.textContent = message;
                notice.classList.remove('hidden');
            };

            const hideNotice = () => {
                if (!notice) return;
                notice.textContent = '';
                notice.classList.add('hidden');
            };

            const setLoading = (select, placeholder) => {
                if (!select) return;
                select.disabled = true;
                select.innerHTML = `<option value="">${placeholder}</option>`;
            };

            const fillSelect = (select, items, placeholder, valueKey = 'id', labelKey = 'name', selectedValue = null) => {
                if (!select) return;
                select.innerHTML = `<option value="">${placeholder}</option>`;
                (items || []).forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item[valueKey];
                    option.textContent = item[labelKey];
                    if (String(option.value) === String(selectedValue)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                select.disabled = false;
            };

            const loadCourses = async () => {
                const departmentId = departmentSelect?.value;
                setLoading(courseSelect, 'Loading courses...');
                setLoading(yearSelect, 'Select year');
                setLoading(subjectSelect, 'Select subject');
                setLoading(teacherSelect, 'Select teacher');
                if (!departmentId) {
                    fillSelect(courseSelect, [], 'Select course');
                    return;
                }

                try {
                    hideNotice();
                    const data = await window.portalSafeFetch(`/admin/schedules/departments/${departmentId}/courses`);
                    fillSelect(courseSelect, data, 'Select course', 'id', 'name', selectedCourseId);
                } catch (error) {
                    showNotice('Unable to load courses. Please retry.');
                    fillSelect(courseSelect, [], 'Select course');
                    console.error('[ScheduleForm][LoadCoursesError]', error);
                }
            };

            const loadYears = async () => {
                const courseId = courseSelect?.value;
                setLoading(yearSelect, 'Loading years...');
                setLoading(subjectSelect, 'Select subject');
                setLoading(teacherSelect, 'Select teacher');
                if (!courseId) {
                    fillSelect(yearSelect, [], 'Select year');
                    return;
                }

                try {
                    hideNotice();
                    const data = await window.portalSafeFetch(`/admin/schedules/courses/${courseId}/years`);
                    fillSelect(yearSelect, data, 'Select year', 'id', 'name', selectedAcademicYear);
                } catch (error) {
                    showNotice('Unable to load years for this course.');
                    fillSelect(yearSelect, [], 'Select year');
                    console.error('[ScheduleForm][LoadYearsError]', error);
                }
            };

            const loadSubjects = async () => {
                const courseId = courseSelect?.value;
                const year = yearSelect?.value;
                setLoading(subjectSelect, 'Loading subjects...');
                setLoading(teacherSelect, 'Select teacher');
                if (!courseId || !year) {
                    fillSelect(subjectSelect, [], 'Select subject');
                    return;
                }

                try {
                    hideNotice();
                    const data = await window.portalSafeFetch(`/admin/schedules/courses/${courseId}/years/${year}/subjects`);
                    const normalized = (data || []).map((item) => ({
                        id: item.id,
                        name: `${item.name || 'Subject'} (Sem ${item.semester_sequence || '-'})`,
                    }));
                    fillSelect(subjectSelect, normalized, 'Select subject', 'id', 'name', selectedSubjectId);
                } catch (error) {
                    showNotice('Unable to load subjects for selected year.');
                    fillSelect(subjectSelect, [], 'Select subject');
                    console.error('[ScheduleForm][LoadSubjectsError]', error);
                }
            };

            const loadTeachers = async () => {
                const subjectId = subjectSelect?.value;
                setLoading(teacherSelect, 'Loading teachers...');
                if (!subjectId) {
                    fillSelect(teacherSelect, [], 'Select teacher');
                    return;
                }

                try {
                    hideNotice();
                    const data = await window.portalSafeFetch(`/admin/schedules/subjects/${subjectId}/teachers`);
                    fillSelect(teacherSelect, data, 'Select teacher', 'id', 'name', selectedTeacherId);
                } catch (error) {
                    showNotice('Unable to load teachers for selected subject.');
                    fillSelect(teacherSelect, [], 'Select teacher');
                    console.error('[ScheduleForm][LoadTeachersError]', error);
                }
            };

            departmentSelect?.addEventListener('change', loadCourses);
            courseSelect?.addEventListener('change', loadYears);
            yearSelect?.addEventListener('change', loadSubjects);
            subjectSelect?.addEventListener('change', loadTeachers);

            const slotBlockSelect = document.getElementById('slotBlockSelect');
            const startTimeField = document.getElementById('startTimeField');
            const endTimeField = document.getElementById('endTimeField');

            const syncSlotFields = () => {
                if (!slotBlockSelect?.value || !startTimeField || !endTimeField) return;
                const parts = slotBlockSelect.value.split('-');
                if (parts.length !== 2) return;
                startTimeField.value = parts[0];
                endTimeField.value = parts[1];
            };

            slotBlockSelect?.addEventListener('change', syncSlotFields);
            syncSlotFields();
        } catch (error) {
            console.error('[ScheduleForm][ScriptInitError]', error);
        }
    })();
</script>
@endpush
