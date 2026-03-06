@extends('layouts.app')

@section('header_title', 'Departments')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Departments</h2>
            <p class="text-sm text-slate-400 mt-1">Manage academic departments and their scope.</p>
        </div>
        <a href="{{ route('admin.departments.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Add Department
        </a>
    </div>

    <form method="GET" class="glass-card p-4 mb-6" id="deptSearchForm">
        <label class="input-label">Search</label>
        <input type="search" name="search" value="{{ request('search') }}" class="input-premium" placeholder="Search department..." data-debounce>
    </form>

    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Description</th>
                    <th class="text-center">Teachers</th>
                    <th class="text-center">Courses</th>
                    <th class="text-center">Students</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr class="group">
                        <td>
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ strtoupper(substr($dept->name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-bold text-slate-800">{{ $dept->name }}</span>
                            </div>
                        </td>
                        <td class="text-sm text-slate-400">{{ $dept->description ?? 'No description' }}</td>
                        <td class="text-center">
                            <span class="gradient-badge bg-violet-50 text-violet-600">{{ $dept->teachers_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="gradient-badge bg-teal-50 text-teal-600">{{ $dept->courses_count }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.students.index', ['department_id' => $dept->id]) }}"
                                class="gradient-badge bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all">
                                {{ $dept->students_count }} 
                            </a>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.departments.edit', $dept->id) }}"
                                    class="h-8 w-8 rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white transition-all flex items-center justify-center text-sm"><i
                                        class="bi bi-pencil-square"></i></a>
                                <form method="POST" action="{{ route('admin.departments.destroy', $dept->id) }}" class="inline"
                                    onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-sm"><i
                                            class="bi bi-trash3-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-sm text-slate-400 py-8">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $departments->links() }}</div>

    @push('scripts')
    <script>
        const deptSearchForm = document.getElementById('deptSearchForm');
        let deptDebounce = null;
        document.querySelectorAll('[data-debounce]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(deptDebounce);
                deptDebounce = setTimeout(() => deptSearchForm.submit(), 400);
            });
        });
    </script>
    @endpush
@endsection
