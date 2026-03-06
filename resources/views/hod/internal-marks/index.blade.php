@extends('layouts.app')

@section('header_title', 'Internal Marks Review')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-5">
            <h2 class="text-xl font-black text-slate-800">Internal Marks Review</h2>
            <form method="GET" class="grid md:grid-cols-4 gap-3 mt-4">
                <div class="md:col-span-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="input-premium"
                        placeholder="Student ID / Name / GTU enrollment / Roll no">
                </div>
                <div>
                    <select name="semester_number" class="input-premium">
                        <option value="">All Semesters</option>
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
                        <th>Course</th>
                        <th>Semester</th>
                        <th>SPI</th>
                        <th>CPI</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr>
                            <td>#{{ $result->student?->id }} - {{ $result->student?->user?->name ?? 'N/A' }}</td>
                            <td>{{ $result->student?->gtu_enrollment_no ?? '-' }}</td>
                            <td>{{ $result->student?->course?->name ?? '-' }}</td>
                            <td>Sem {{ $result->semester_number }}</td>
                            <td>{{ number_format((float) $result->sgpa, 2) }}</td>
                            <td>{{ number_format((float) $result->cgpa, 2) }}</td>
                            <td><x-badge :type="$result->result_status === 'pass' ? 'success' : ($result->result_status === 'pending' ? 'warning' : 'danger')">{{ strtoupper($result->result_status) }}</x-badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-6 text-slate-500">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $results->links() }}</div>
    </div>
@endsection

