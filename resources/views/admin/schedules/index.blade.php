@extends('layouts.app')

@section('header_title', 'Timetable Management')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Timetable</h2>
                <p class="text-sm text-slate-500">Manage all slots with conflict-safe scheduling.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.schedules.create') }}" class="btn-primary-gradient">Add Slot</a>
            </div>
        </div>

        <form method="GET" class="glass-card p-4 grid md:grid-cols-5 gap-3" id="scheduleFilters">
            <div>
                <label class="input-label">Department</label>
                <select name="department_id" id="filterDepartment" class="input-premium">
                    <option value="">All</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Course</label>
                <select name="course_id" id="filterCourse" class="input-premium">
                    <option value="">All</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(($filters['course_id'] ?? null) == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Year</label>
                <input type="number" name="academic_year" min="1" max="8" value="{{ $filters['academic_year'] ?? '' }}" class="input-premium" placeholder="Year">
            </div>
            <div class="md:col-span-2">
                <label class="input-label">Search</label>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="input-premium" placeholder="Search subject, teacher, classroom..." data-debounce>
            </div>
        </form>
        <div id="schedulePageNotice" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"></div>

        <x-weekly-timetable-grid :days="$days" :timeSlots="$timeSlots" :grid="$grid" :showTeacher="true" :showRoom="true" :showSemester="false" emptyText="No timetable slots found." />

        <div class="glass-card overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-black text-slate-800">List View</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Department</th>
                            <th>Course / Sem</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $slot)
                            <tr>
                                <td class="whitespace-nowrap">{{ ucfirst($slot->day_of_week) }}</td>
                                <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td class="break-words">{{ $slot->semester?->course?->department?->name ?? 'N/A' }}</td>
                                <td class="break-words">{{ $slot->semester?->course?->name ?? 'N/A' }} / Sem {{ $slot->semester?->semester_number ?? 'N/A' }}</td>
                                <td class="break-words">{{ $slot->subject->name ?? 'N/A' }}</td>
                                <td class="break-words">{{ $slot->teacher->user->name ?? 'N/A' }}</td>
                                <td class="break-words">{{ $slot->classroom->name ?? 'N/A' }}</td>
                                <td class="whitespace-nowrap">
                                    <div class="flex gap-2">
                                    @featureEnabled('edit_button_enabled')
                                    <a href="{{ route('admin.schedules.edit', $slot) }}" class="text-indigo-600 text-xs">Edit</a>
                                    @endfeatureEnabled
                                    <form method="POST" action="{{ route('admin.schedules.destroy', $slot) }}" onsubmit="return confirm('Delete this slot?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 text-xs">Delete</button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-8 text-slate-500">No timetable slots found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $schedules->links() }}</div>
    </div>

    @push('scripts')
    <script>
        (function () {
            try {
                const filterDepartment = document.getElementById('filterDepartment');
                const filterCourse = document.getElementById('filterCourse');
                const filtersForm = document.getElementById('scheduleFilters');
                const notice = document.getElementById('schedulePageNotice');
                let debounceTimer = null;

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

                filterDepartment?.addEventListener('change', async () => {
                    const departmentId = filterDepartment.value;
                    if (filterCourse) {
                        filterCourse.innerHTML = '<option value="">All</option>';
                        filterCourse.disabled = true;
                    }

                    if (!departmentId) {
                        filtersForm?.submit();
                        return;
                    }

                    try {
                        hideNotice();
                        const courses = await window.portalSafeFetch(`/admin/schedules/departments/${departmentId}/courses`);
                        (courses || []).forEach((course) => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.name || `Course ${course.id}`;
                            filterCourse?.appendChild(option);
                        });
                        filtersForm?.submit();
                    } catch (error) {
                        showNotice('Unable to load courses right now. Please try again.');
                        console.error('[Schedules][LoadCoursesError]', error);
                    } finally {
                        if (filterCourse) {
                            filterCourse.disabled = false;
                        }
                    }
                });

                filterCourse?.addEventListener('change', () => filtersForm?.submit());

                document.querySelectorAll('[data-debounce]').forEach((input) => {
                    input.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => filtersForm?.submit(), 400);
                    });
                });
            } catch (error) {
                console.error('[Schedules][ScriptInitError]', error);
            }
        })();

    </script>
    @endpush
@endsection
