@extends('layouts.app')

@section('header_title', 'Add Subject')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Add Subject</h2>
        <a href="{{ route('admin.subjects.index') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card p-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Subject
                    Name</label>
                <input type="text" name="name" placeholder="e.g. Data Structures" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <div>
                <label
                    class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Credits</label>
                <input type="number" name="credits" placeholder="e.g. 4" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Weekly Hours</label>
                <input type="number" name="weekly_hours" min="1" max="30" value="4" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_lab" value="1" id="isLabCreate" class="rounded border-slate-300">
                    Lab Subject
                </label>
                <div>
                    <input type="number" name="lab_block_hours" id="labBlockCreate" min="2" max="3" value="2" disabled
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Course</label>
                <select name="course_id" id="courseSelect" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" data-total-semesters="{{ max(1, (int) $course->duration_years * max(1, (int) $course->semesters_per_year)) }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label
                    class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Semester</label>
                <select name="semester_number" id="semesterSelect" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    <option value="">Select Semester</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Rule: each semester can contain maximum {{ config('timetable.subjects_per_semester', 8) }} subjects.</p>
            </div>
            <div>
                <label
                    class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Teacher</label>
                <select name="teacher_id" required
                    class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-4 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    <option value="">Select Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? 'N/A' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-8 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
                Create Subject
            </button>
        </form>
    </div>
    @push('scripts')
    <script>
        const courseSelect = document.getElementById('courseSelect');
        const semesterSelect = document.getElementById('semesterSelect');
        const oldSemester = @json(old('semester_number'));

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

        const isLabCreate = document.getElementById('isLabCreate');
        const labBlockCreate = document.getElementById('labBlockCreate');
        isLabCreate?.addEventListener('change', () => {
            labBlockCreate.disabled = !isLabCreate.checked;
        });
    </script>
    @endpush
@endsection
