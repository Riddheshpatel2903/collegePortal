@extends('layouts.app')

@section('header_title', 'Teacher Leave Applications')

@section('content')
    <form method="POST" action="{{ route('teacher.leaves.store') }}" class="glass-card p-6 mb-6 space-y-4">
        @csrf
        <div class="grid md:grid-cols-4 gap-4">
            <div><label>Type</label><select name="leave_type" class="w-full border rounded p-2"><option value="sick">Sick</option><option value="casual">Casual</option><option value="emergency">Emergency</option><option value="other">Other</option></select></div>
            <div><label>Start Date</label><input type="date" name="start_date" class="w-full border rounded p-2" required></div>
            <div><label>End Date</label><input type="date" name="end_date" class="w-full border rounded p-2" required></div>
            <div><label>Reason</label><input type="text" name="reason" class="w-full border rounded p-2" required></div>
        </div>
        <button class="px-4 py-2 bg-slate-900 text-white rounded">Submit to HOD</button>
    </form>

    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead><tr><th>Type</th><th>Dates</th><th>Status</th><th>Stage</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($leaves as $leave)
                <tr>
                    <td>{{ ucfirst($leave->leave_type) }}</td>
                    <td>{{ $leave->start_date?->format('d M Y') }} - {{ $leave->end_date?->format('d M Y') }}</td>
                    <td>{{ ucfirst($leave->status) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($leave->current_stage)) }}</td>
                    <td>{{ $leave->approval_remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-6">No leave records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $leaves->links() }}</div>
@endsection
