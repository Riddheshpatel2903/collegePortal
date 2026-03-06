@extends('layouts.app')

@section('header_title', 'Edit Subject')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Edit Subject</h2>
            <p class="text-sm text-slate-400 mt-1">Update subject details, credit hours, and faculty assignment.</p>
        </div>
        <a href="{{ route('admin.subjects.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-sm text-rose-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Update Subject</h3>
                        <p class="text-xs text-slate-400">Modify subject information and assignments.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Subject
                            Name</label>
                        <input type="text" name="name" value="{{ old('name', $subject->name) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Credits</label>
                        <input type="number" name="credits" value="{{ old('credits', $subject->credits) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Weekly Hours</label>
                        <input type="number" name="weekly_hours" min="1" max="30" value="{{ old('weekly_hours', $subject->weekly_hours ?? 4) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Lab Settings</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_lab" value="1" id="isLabEdit" class="rounded border-slate-300" {{ old('is_lab', $subject->is_lab) ? 'checked' : '' }}>
                                Lab Subject
                            </label>
                            <input type="number" name="lab_block_hours" id="labBlockEdit" min="2" max="3"
                                value="{{ old('lab_block_hours', $subject->lab_block_hours ?? 2) }}"
                                class="w-full border border-slate-200 rounded-xl py-2 px-3 text-sm text-slate-700 bg-slate-50/50">
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Course</label>
                        <select name="course_id" id="courseSelect"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" data-total-semesters="{{ max(1, (int) $course->duration_years * max(1, (int) $course->semesters_per_year)) }}" {{ old('course_id', $subject->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Semester</label>
                        <select name="semester_number" id="semesterSelect"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                            <option value="">Select Semester</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Rule: each semester can contain maximum {{ config('timetable.subjects_per_semester', 8) }} subjects.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Teacher</label>
                        <select name="teacher_id"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $subject->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.subjects.index') }}"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</a>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl hover:shadow-lg hover:shadow-teal-500/25 transition-all">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
    <script>
        const isLabEdit = document.getElementById('isLabEdit');
        const labBlockEdit = document.getElementById('labBlockEdit');
        const courseSelect = document.getElementById('courseSelect');
        const semesterSelect = document.getElementById('semesterSelect');
        const oldSemester = @json(old('semester_number', $subject->semester_number ?? $subject->semester_sequence));

        function loadSemesters() {
            if (!courseSelect || !semesterSelect) return;
            const total = Number(courseSelect.options[courseSelect.selectedIndex]?.dataset?.totalSemesters || 0);
            semesterSelect.innerHTML = '<option value="">Select Semester</option>';
            for (let i = 1; i <= total; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Semester ${i}`;
                if (String(oldSemester) === String(i)) option.selected = true;
                semesterSelect.appendChild(option);
            }
        }

        courseSelect?.addEventListener('change', loadSemesters);
        loadSemesters();

        function toggleLabBlock() {
            if (!isLabEdit || !labBlockEdit) return;
            labBlockEdit.disabled = !isLabEdit.checked;
        }
        isLabEdit?.addEventListener('change', toggleLabBlock);
        toggleLabBlock();
    </script>
    @endpush
@endsection
