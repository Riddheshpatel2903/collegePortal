@extends('layouts.app')

@section('header_title', 'Results')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-5">
            <h2 class="text-xl font-black text-slate-800">GTU Result Import</h2>
            <p class="text-sm text-slate-500 mt-1">Upload `CSV/XLSX/PDF` and match by GTU enrollment number.</p>
            <form method="POST" action="{{ route('admin.results.import') }}" enctype="multipart/form-data" class="grid md:grid-cols-4 gap-3 mt-4">
                @csrf
                <div>
                    <label class="input-label">Semester</label>
                    <select name="semester_number" class="input-premium" required>
                        <option value="">Select</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected(old('semester_number') == $i)>Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="input-label">Result File</label>
                    <input type="file" name="result_file" class="input-premium" accept=".csv,.xlsx,.pdf" required>
                </div>
                <div class="flex items-end">
                    <button class="btn-primary-gradient w-full">Import & Lock</button>
                </div>
            </form>
        </div>

        <div class="glass-card p-5">
            <form method="GET" class="grid md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="input-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-premium"
                        placeholder="Student ID / Name / GTU enrollment / Roll no">
                </div>
                <div>
                    <label class="input-label">Course</label>
                    <select name="course_id" class="input-premium">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">Semester</label>
                    <select name="semester_number" class="input-premium">
                        <option value="">All</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected((int) request('semester_number') === $i)>Sem {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button class="btn-secondary">Search</button>
                </div>
            </form>
        </div>

        <div class="glass-card overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>GTU Enrollment</th>
                        <th>Semester</th>
                        <th>SPI</th>
                        <th>CPI</th>
                        <th>Backlogs</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr>
                            <td>#{{ $result->student?->id }} - {{ $result->student?->user?->name ?? 'N/A' }}</td>
                            <td>{{ $result->student?->gtu_enrollment_no ?? '-' }}</td>
                            <td>Sem {{ $result->semester_number }}</td>
                            <td>{{ number_format((float) $result->sgpa, 2) }}</td>
                            <td>{{ number_format((float) $result->cgpa, 2) }}</td>
                            <td>{{ $result->backlog_subjects }}</td>
                            <td>
                                <x-badge :type="$result->result_status === 'pass' ? 'success' : ($result->result_status === 'pending' ? 'warning' : 'danger')">
                                    {{ strtoupper($result->result_status) }}
                                </x-badge>
                            </td>
                            <td class="space-x-1">
                                @if(!$result->locked_at)
                                    <form method="POST" action="{{ route('admin.results.lock', $result) }}" class="inline">
                                        @csrf
                                        <button class="btn-secondary px-3 py-1.5 text-xs">Lock</button>
                                    </form>
                                @endif
                                @if($result->student?->gtu_enrollment_no)
                                    <a class="btn-secondary px-3 py-1.5 text-xs"
                                        href="https://www.students.gtu.ac.in/Default.aspx?enrollmentno={{ urlencode($result->student->gtu_enrollment_no) }}"
                                        target="_blank" rel="noopener">View on GTU Portal</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-6 text-slate-500">No results found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $results->links() }}</div>
    </div>
@endsection

