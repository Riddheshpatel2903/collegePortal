@extends('layouts.app')

@section('header_title', 'Leave Management Overview')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card"><p class="text-xs uppercase text-slate-500">Total</p><p class="text-2xl font-black mt-2">{{ $stats['total'] }}</p></div>
            <div class="stat-card"><p class="text-xs uppercase text-slate-500">Pending</p><p class="text-2xl font-black mt-2 text-amber-600">{{ $stats['pending'] }}</p></div>
            <div class="stat-card"><p class="text-xs uppercase text-slate-500">Approved</p><p class="text-2xl font-black mt-2 text-emerald-600">{{ $stats['approved'] }}</p></div>
            <div class="stat-card"><p class="text-xs uppercase text-slate-500">Rejected</p><p class="text-2xl font-black mt-2 text-rose-600">{{ $stats['rejected'] }}</p></div>
        </div>

        <form method="GET" action="{{ route('admin.leaves.index') }}" id="leaveFilterForm" class="glass-card p-4 grid md:grid-cols-6 gap-3">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="glass-card p-4">
                <h3 class="font-black text-slate-800 mb-4">Request Trend (Filtered)</h3>
                <div class="space-y-3">
                    @forelse($trends as $trend)
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-slate-600">
                                <span>{{ \Carbon\Carbon::parse($trend->date)->format('D, d M') }}</span>
                                <span>{{ $trend->count }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden mt-1">
                                <div class="h-full bg-slate-700 rounded-full" style="width: {{ (int) (($trend->count / max(1, (int) $trends->max('count'))) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No trend data for current filters.</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card overflow-hidden lg:col-span-2">
                <div class="overflow-x-auto">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                @php
                                    $applicant = $leave->leaveable?->user;
                                    $departmentName = $leave->leaveable?->department?->name ?? 'N/A';
                                    $roleLabel = str_contains((string) $leave->leaveable_type, 'Teacher')
                                        ? 'Teacher'
                                        : (str_contains((string) $leave->leaveable_type, 'Student') ? 'Student' : strtoupper($leave->requested_by_role ?? '-'));
                                @endphp
                                <tr>
                                    <td class="font-semibold">{{ $applicant?->name ?? 'Unknown' }}</td>
                                    <td>{{ $applicant?->email ?? 'N/A' }}</td>
                                    <td>{{ $roleLabel }}</td>
                                    <td>{{ $departmentName }}</td>
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
                                    <td>
                                        <form action="{{ route('admin.leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Delete this leave record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-600 text-xs font-semibold">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-500">No leaves found for current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100">
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const leaveFilterForm = document.getElementById('leaveFilterForm');
            let leaveSearchTimer = null;
            document.querySelectorAll('#leaveFilterForm [data-debounce]').forEach((input) => {
                input.addEventListener('input', () => {
                    clearTimeout(leaveSearchTimer);
                    leaveSearchTimer = setTimeout(() => leaveFilterForm.submit(), 400);
                });
            });

            document.querySelectorAll('#leaveFilterForm input[type=\"date\"]').forEach((input) => {
                input.addEventListener('change', () => leaveFilterForm.submit());
            });
        </script>
    @endpush
@endsection
