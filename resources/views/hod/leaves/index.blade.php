@extends('layouts.app')

@section('header_title', 'Departmental Leave Oversight')

@section('content')
    @php $canApproveLeave = app(\App\Services\PortalAccessService::class)->featureEnabled('approve_leave_enabled', true); @endphp

    <x-page-header 
        title="Faculty & Student Leaves" 
        subtitle="Review and manage leave applications from department members. Oversight for academic continuity."
        icon="bi-calendar-check"
    />

    <div class="mt-8 space-y-8">
        {{-- Filters --}}
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
            <form method="GET" action="{{ route('hod.leaves.index') }}" id="hodLeaveFilterForm" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Search Applicant</label>
                    <div class="relative">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" 
                            class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0" 
                            placeholder="Name or Email..." data-debounce>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Role Type</label>
                    <select name="role" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="student" @selected(($filters['role'] ?? '') === 'student')>Students</option>
                        <option value="teacher" @selected(($filters['role'] ?? '') === 'teacher')>Teachers</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Current Status</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending Review</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                            <th class="px-6 py-5">Applicant & Identity</th>
                            <th class="px-6 py-5">Leave Parameters</th>
                            <th class="px-6 py-5">Duration Period</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            @if($canApproveLeave)
                                <th class="px-6 py-5 text-right">Review Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($leaves as $leave)
                            @php
                                $applicant = $leave->leaveable?->user;
                                $roleLabel = str_contains((string) $leave->leaveable_type, 'Teacher') ? 'Faculty' : 'Student';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 leading-tight">{{ $applicant?->name ?? 'N/A' }}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100 uppercase tracking-widest">{{ $roleLabel }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ $applicant?->email ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700">{{ ucfirst($leave->leave_type) }}</span>
                                        <p class="text-[10px] text-slate-400 line-clamp-1 mt-1 font-medium italic">"{{ $leave->reason }}"</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700">{{ $leave->start_date?->format('d M') }} - {{ $leave->end_date?->format('d M') }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} Working Days</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusClass = match($leave->status) {
                                            'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'pending'  => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'rejected' => 'bg-rose-50 text-rose-600 border-rose-100',
                                            default    => 'bg-slate-50 text-slate-600 border-slate-100'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded text-[9px] font-black uppercase border {{ $statusClass }}">
                                        {{ $leave->status }}
                                    </span>
                                </td>
                                @if($canApproveLeave)
                                    <td class="px-6 py-5 text-right">
                                        @if($leave->status === 'pending')
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" action="{{ route('hod.leaves.approve', $leave) }}" class="inline">
                                                    @csrf
                                                    <button class="h-8 px-3 bg-emerald-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-50">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('hod.leaves.reject', $leave) }}" class="inline">
                                                    @csrf
                                                    <button class="h-8 px-3 bg-rose-600 text-white rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-rose-700 transition-all shadow-lg shadow-rose-50">Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">Decision Final</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canApproveLeave ? 5 : 4 }}" class="py-24 text-center opacity-30">
                                    <i class="bi bi-calendar-x text-5xl mb-4"></i>
                                    <p class="text-[10px] font-bold uppercase tracking-widest">No leave applications recorded in your department</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaves->hasPages())
                <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
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

            document.querySelectorAll('#hodLeaveFilterForm input[type="date"]').forEach((input) => {
                input.addEventListener('change', () => hodLeaveFilterForm.submit());
            });
        </script>
    @endpush
@endsection
