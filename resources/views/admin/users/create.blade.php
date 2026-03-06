@extends('layouts.app')

@section('header_title', 'Create User')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">New User</h2>
            <p class="text-sm text-slate-400 mt-1">Add a new member to the system.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-sm text-rose-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Account Details Header --}}
                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Account Details</h3>
                        <p class="text-xs text-slate-400">Configure access credentials and role.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="e.g. Christopher Nolan" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="c.nolan@college.edu" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Role</label>
                        <select name="role" id="roleSelect"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                @php
                                    $label = \Illuminate\Support\Str::title(str_replace('_', ' ', $role->name));
                                @endphp
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                        <input type="password" name="password"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="••••••••" required>
                    </div>
                </div>

                {{-- Student Fields --}}
                <div id="studentFields" class="hidden">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100 mt-6">
                        <div class="h-10 w-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Student Information</h3>
                            <p class="text-xs text-slate-400">Academic and enrollment details.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Roll Number</label>
                            <input type="text" name="roll_number" value="{{ old('roll_number') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="CS2024001">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">GTU Enrollment No</label>
                            <input type="text" name="gtu_enrollment_no" value="{{ old('gtu_enrollment_no') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="GTU2024XXXXXX">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Course</label>
                            <select name="course_id" id="studentCourseSelect"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-total-semesters="{{ max(1, (int) $course->duration_years * max(1, (int) $course->semesters_per_year)) }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Current Semester</label>
                            <select name="semester_number" id="studentSemesterSelect"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                                <option value="">Select Semester</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Admission Year</label>
                            <input type="number" name="admission_year" value="{{ old('admission_year', date('Y')) }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="2024">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="+91 xxxxxxxxxx">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="Full address">
                        </div>
                    </div>
                </div>

                {{-- Teacher Fields --}}
                <div id="teacherFields" class="hidden">
                    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100 mt-6">
                        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Faculty Information</h3>
                            <p class="text-xs text-slate-400">Department and qualification details.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                            <select name="department_id"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Qualification</label>
                            <input type="text" name="qualification" value="{{ old('qualification') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="Ph.D. Computer Science">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                                placeholder="+91 xxxxxxxxxx">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="reset"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Reset</button>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">Create
                        User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSelect = document.getElementById('roleSelect');
            const studentFields = document.getElementById('studentFields');
            const teacherFields = document.getElementById('teacherFields');
            const studentCourseSelect = document.getElementById('studentCourseSelect');
            const studentSemesterSelect = document.getElementById('studentSemesterSelect');
            const oldSemesterNumber = @json(old('semester_number'));

            function loadSemesterOptions() {
                if (!studentCourseSelect || !studentSemesterSelect) return;
                const selectedOption = studentCourseSelect.options[studentCourseSelect.selectedIndex];
                const total = Number(selectedOption?.dataset?.totalSemesters || 0);
                studentSemesterSelect.innerHTML = '<option value="">Select Semester</option>';

                for (let i = 1; i <= total; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Semester ${i}`;
                    if (String(oldSemesterNumber) === String(i)) {
                        option.selected = true;
                    }
                    studentSemesterSelect.appendChild(option);
                }
            }

            function toggleFields() {
                const role = roleSelect.value;

                // Hide both and disable their inputs
                studentFields.classList.add('hidden');
                teacherFields.classList.add('hidden');
                studentFields.querySelectorAll('input, select').forEach(el => el.disabled = true);
                teacherFields.querySelectorAll('input, select').forEach(el => el.disabled = true);

                // Show and enable the relevant section
                if (role === 'student') {
                    studentFields.classList.remove('hidden');
                    studentFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                } else if (role === 'teacher') {
                    teacherFields.classList.remove('hidden');
                    teacherFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
                }
            }

            roleSelect.addEventListener('change', toggleFields);
            studentCourseSelect?.addEventListener('change', loadSemesterOptions);

            // Trigger on load in case old() restored a role
            toggleFields();
            loadSemesterOptions();
        });
    </script>
@endsection
