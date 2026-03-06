@extends('layouts.app')

@section('header_title', 'My Results')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Academic Results</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $student->user->name }} | {{ $student->course->name ?? 'Course' }}</p>
                </div>
                @if($student->gtu_enrollment_no)
                    <a class="btn-secondary whitespace-nowrap"
                        href="https://www.students.gtu.ac.in/Default.aspx?enrollmentno={{ urlencode($student->gtu_enrollment_no) }}"
                        target="_blank" rel="noopener">View on GTU Portal</a>
                @endif
            </div>
        </div>

        @forelse($results as $result)
            <div class="glass-card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-800">Semester {{ $result->semester_number }}</h3>
                        <p class="text-xs text-slate-500">SPI {{ number_format((float) $result->sgpa, 2) }} | CPI {{ number_format((float) $result->cgpa, 2) }}</p>
                    </div>
                    <x-badge :type="$result->result_status === 'pass' ? 'success' : 'danger'">{{ strtoupper($result->result_status) }}</x-badge>
                </div>

                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($result->resultSubjects as $subjectResult)
                            <tr>
                                <td>{{ $subjectResult->subject->name ?? 'N/A' }}</td>
                                <td>{{ $subjectResult->internal_marks }}</td>
                                <td>{{ $subjectResult->external_marks }}</td>
                                <td>{{ $subjectResult->total_marks }}</td>
                                <td>{{ $subjectResult->grade }}</td>
                                <td>
                                    <x-badge :type="$subjectResult->subject_status === 'pass' ? 'success' : 'danger'">{{ strtoupper($subjectResult->subject_status) }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-6 text-slate-500">No subject marks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="glass-card p-8 text-center text-slate-500">No published results available yet.</div>
        @endforelse

        <div>{{ $results->links() }}</div>
    </div>
@endsection
