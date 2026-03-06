@extends('layouts.app')

@section('header_title', 'Department Leaves')

@section('content')
    @php $canApproveLeave = app(\App\Services\PortalAccessService::class)->featureEnabled('approve_leave_enabled', true); @endphp
    <div class="space-y-5">
        <form method="GET" action="{{ route('hod.leaves.index') }}" id="hodLeaveFilterForm" class="glass-card p-4 grid md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="input-label">Search</label>
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    class="input-premium"
                    placeholder="Name, email, or department"
                    data-debounce
                >
            </div>
            <div>
                <label class="input-label">Role</label>
                <select name="role" class="input-premium" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="student" @selected(($filters['role'] ?? '') === 'student')>Student</option>
                    <option value="teacher" @selected(($filters['role'] ?? '') === 'teacher')>Teacher</option>
                    <option value="hod" @selected(($filters['role'] ?? '') === 'hod')>HOD</option>
                </select>
            </div>
            <div>
                <label class="input-label">Status</label>
                <select name="status" class="input-premium" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                    <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div>
                <label class="input-label">From</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="input-premium">
            </div>
            <div>
                <label class="input-label">To</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="input-premium">
            </div>
        </form>

        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Status</th>
                            @if($canApproveLeave)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            @php
                                $applicant = $leave->leaveable?->user;
                                $departmentName = $leave->leaveable?->department?->name ?? 'N/A';
                                $roleLabel = str_contains((string) $leave->leaveable_type, 'Teacher') ? 'Teacher' : 'Student';
                            @endphp
                            <tr>
                                <td class="font-semibold">{{ $applicant?->name ?? 'N/A' }}</td>
                                <td>{{ $applicant?->email ?? 'N/A' }}</td>
                                <td>{{ $roleLabel }}</td>
                                <td>{{ $departmentName }}</td>
                                <td>{{ ucfirst($leave->leave_type) }}</td>
                                <td>{{ $leave->start_date?->format('d M Y') }} - {{ $leave->end_date?->format('d M Y') }}</td>
                                <td>
                                    @if($leave->status === 'pending')
                                        <x-badge type="warning">Pending</x-badge>
                                    @elseif($leave->status === 'approved')
                                        <x-badge type="success">Approved</x-badge>
                                    @else
                                        <x-badge type="danger">Rejected</x-badge>
                                    @endif
                                </td>
                                @if($canApproveLeave)
                                    <td class="whitespace-nowrap">
                                        @if($leave->status === 'pending')
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('hod.leaves.approve', $leave) }}">
                                                    @csrf
                                                    <button class="px-3 py-1 bg-emerald-600 text-white rounded text-xs font-semibold">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('hod.leaves.reject', $leave) }}">
                                                    @csrf
                                                    <button class="px-3 py-1 bg-rose-600 text-white rounded text-xs font-semibold">Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">No actions</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canApproveLeave ? 8 : 7 }}" class="text-center py-8 text-slate-500">No leaves found in your department.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>{{ $leaves->links() }}</div>
    </div>

    @push('scripts')
        <script>
            const hodLeaveFilterForm = document.getElementById('hodLeaveFilterForm');
            let hodLeaveSearchTimer = null;

            document.querySelectorAll('#hodLeaveFilterForm [data-debounce]').forEach((input) => {
                input.addEventListener('input', () => {
                    clearTimeout(hodLeaveSearchTimer);
                    hodLeaveSearchTimer = setTimeout(() => hodLeaveFilterForm.submit(), 400);
                });
            });

            document.querySelectorAll('#hodLeaveFilterForm input[type=\"date\"]').forEach((input) => {
                input.addEventListener('change', () => hodLeaveFilterForm.submit());
            });
        </script>
    @endpush
@endsection
