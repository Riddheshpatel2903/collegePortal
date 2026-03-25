@extends('layouts.app')

@section('header_title', 'Leave Management Overview')

@section('content')
    <x-page-header 
        title="Leave Nexus" 
        subtitle="Manage faculty and staff absence requests" 
        tag="HR Operations"
        icon="bi-calendar-check"
    />

    <!-- Stats Hub -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-slate-400">
            <div class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-list-task"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Registry</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-amber-500">
            <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Pending Sync</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['pending'] }}</h3>
            </div>
        </div>
        <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-emerald-500">
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-check2-all"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Approved Nodes</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['approved'] }}</h3>
            </div>
        </div>
        <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-rose-500">
            <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Rejected</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['rejected'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter Nexus -->
    <div class="glass-card p-6 mb-8 border border-white/60">
        <form method="GET" action="{{ route('admin.leaves.index') }}" id="leaveFilterForm" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="input-label text-[10px]">Registry Search</label>
                <div class="relative group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                        class="input-premium pl-10 h-11" placeholder="Name, email, or department index...">
                </div>
            </div>
            <div>
                <label class="input-label text-[10px]">Role Filter</label>
                <select name="role" class="input-premium h-11" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="student" @selected(($filters['role'] ?? '') === 'student')>Student</option>
                    <option value="teacher" @selected(($filters['role'] ?? '') === 'teacher')>Teacher</option>
                    <option value="hod" @selected(($filters['role'] ?? '') === 'hod')>HOD</option>
                </select>
            </div>
            <div>
                <label class="input-label text-[10px]">Status Filter</label>
                <select name="status" class="input-premium h-11" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending Sync</option>
                    <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
                    <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Declined</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full h-11 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-900/10">
                    Execute Query
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left: Trends -->
        <div class="lg:col-span-1">
            <div class="glass-card p-6 sticky top-6">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-50 pb-4">Request Volume Index</h3>
                <div class="space-y-6">
                    @forelse($trends as $trend)
                        <div class="group">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[11px] font-black text-slate-600 uppercase tracking-tight group-hover:text-violet-600 transition-colors">{{ \Carbon\Carbon::parse($trend->date)->format('D, d M') }}</span>
                                <span class="text-[11px] font-black text-violet-600 bg-violet-50 px-2 py-0.5 rounded shadow-sm">{{ $trend->count }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                <div class="h-full bg-slate-700 rounded-full group-hover:bg-violet-600 transition-all" 
                                     style="width: {{ (int) (($trend->count / max(1, (int) $trends->max('count'))) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <i class="bi bi-graph-down text-4xl text-slate-100 mb-4 block"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No trend data found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Registry Table -->
        <div class="lg:col-span-3">
            <div class="glass-card overflow-hidden">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Faculty Node</th>
                            <th>Identity Hub</th>
                            <th>Absence Window</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Operations</th>
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
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 tracking-tight">{{ $applicant?->name ?? 'Unknown' }}</span>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic mt-0.5">{{ $roleLabel }} Node</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-tight">{{ $applicant?->email ?? 'N/A' }}</span>
                                        <span class="text-[9px] text-violet-500 font-bold uppercase tracking-widest mt-0.5">{{ $departmentName }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs font-black text-slate-700 tracking-tighter">
                                        {{ $leave->start_date?->format('d M') }} - {{ $leave->end_date?->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $leave->status === 'approved' ? 'text-emerald-700 bg-emerald-100' : ($leave->status === 'pending' ? 'text-amber-700 bg-amber-50' : 'text-rose-700 bg-rose-50') }}">
                                        {{ $leave->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <form action="{{ route('admin.leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Hard delete this leave record index?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="h-8 w-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-32 text-center">
                                    <i class="bi bi-calendar-x text-6xl text-slate-100 mb-6 block"></i>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No matching registry records found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-50">
                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const leaveFilterForm = document.getElementById('leaveFilterForm');
            let leaveSearchTimer = null;
            document.querySelectorAll('#leaveFilterForm input[type=\"text\"]').forEach((input) => {
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
