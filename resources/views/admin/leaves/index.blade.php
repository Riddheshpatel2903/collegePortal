@extends('layouts.app')

@section('header_title', 'Leave Management')

@section('content')
<div class="space-y-8 animate-fade-in">
    <!-- ─── Page Header ─── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Leave Requests</h2>
            <p class="text-sm text-slate-500">Manage and track employee and student leave requests.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center border border-slate-100">
                    <i class="bi bi-list-task text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100">
                    <i class="bi bi-clock-history text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pending</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                    <i class="bi bi-check2-all text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Approved</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $stats['approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <i class="bi bi-x-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Rejected</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $stats['rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
        <form method="GET" action="{{ route('admin.leaves.index') }}" id="leaveFilterForm" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Search</label>
                <div class="relative group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" 
                        placeholder="Name, email, or department...">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Role</label>
                <select name="role" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="student" @selected(($filters['role'] ?? '') === 'student')>Student</option>
                    <option value="teacher" @selected(($filters['role'] ?? '') === 'teacher')>Teacher</option>
                    <option value="hod" @selected(($filters['role'] ?? '') === 'hod')>HOD</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                    <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 rounded-xl transition-all shadow-lg shadow-slate-200 flex items-center justify-center gap-2">
                    <i class="bi bi-funnel"></i> Apply
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left: Trends -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm sticky top-6">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-6 pb-4 border-b border-slate-100 flex items-center gap-2">
                    <i class="bi bi-graph-up text-indigo-500"></i> Request Trends
                </h3>
                <div class="space-y-6">
                    @forelse($trends as $trend)
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold text-slate-600">{{ \Carbon\Carbon::parse($trend->date)->format('D, d M') }}</span>
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">{{ $trend->count }}</span>
                            </div>
                            <div class="h-2 bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                                <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" 
                                     style="width: {{ (int) (($trend->count / max(1, (int) $trends->max('count'))) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center opacity-30">
                            <i class="bi bi-graph-down text-3xl mb-2 block"></i>
                            <p class="text-[10px] font-bold uppercase">No records</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Table -->
        <div class="lg:col-span-3">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Applicant</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Details</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Dates</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($leaves as $leave)
                                @php
                                    $applicant = $leave->leaveable?->user;
                                    $departmentName = $leave->leaveable?->department?->name ?? 'N/A';
                                    $roleLabel = str_contains((string) $leave->leaveable_type, 'Teacher')
                                        ? 'Teacher'
                                        : (str_contains((string) $leave->leaveable_type, 'Student') ? 'Student' : strtoupper($leave->requested_by_role ?? '-'));
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ $applicant?->name ?? 'Unknown' }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $roleLabel }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-slate-600 font-medium">{{ $applicant?->email ?? 'N/A' }}</span>
                                            <span class="text-[10px] text-indigo-500 font-bold uppercase tracking-widest">{{ $departmentName }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-700">{{ $leave->start_date?->format('d M') }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium italic">to {{ $leave->end_date?->format('d M Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $statusStyle = match($leave->status) {
                                                'approved' => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                                                'pending' => 'text-amber-700 bg-amber-50 border-amber-100',
                                                'rejected' => 'text-rose-700 bg-rose-50 border-rose-100',
                                                default => 'text-slate-700 bg-slate-50 border-slate-100'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-black uppercase border {{ $statusStyle }}">
                                            {{ $leave->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <form action="{{ route('admin.leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Delete this leave record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 border border-rose-100 transition-colors flex items-center justify-center">
                                                    <i class="bi bi-trash3 text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-20 text-center opacity-30">
                                        <i class="bi bi-calendar-x text-4xl mb-3 block"></i>
                                        <p class="text-[11px] font-bold uppercase tracking-widest">No records found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($leaves->hasPages())
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                        {{ $leaves->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const leaveFilterForm = document.getElementById('leaveFilterForm');
        let leaveSearchTimer = null;
        document.querySelectorAll('#leaveFilterForm input[type="text"]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(leaveSearchTimer);
                leaveSearchTimer = setTimeout(() => leaveFilterForm.submit(), 400);
            });
        });

        document.querySelectorAll('#leaveFilterForm select').forEach((input) => {
            input.addEventListener('change', () => leaveFilterForm.submit());
        });
    </script>
@endpush
@endsection
