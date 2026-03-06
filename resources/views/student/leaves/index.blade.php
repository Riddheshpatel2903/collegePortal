@extends('layouts.app')

@section('header_title', 'Apply Leave')

@section('content')
    <div class="animate-fade-in" x-data="leaveForm()">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Apply for Leave</h2>
                <p class="text-sm text-slate-400 mt-1">Submit your request for leave and track your history.</p>
            </div>
        </div>

        @if($errors->has('error'))
            <div class="mb-6 px-5 py-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-lg text-rose-500"></i>
                <span class="text-sm font-semibold">{{ $errors->first('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Leave Form -->
            <div class="lg:col-span-1">
                <div class="glass-card p-6 sticky top-24">
                    <form action="{{ route('student.leaves.store') }}" method="POST" id="leaveApplicationForm">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="input-label">Leave Type</label>
                                <select name="leave_type" class="input-premium appearance-none" required>
                                    <option value="" disabled selected>Select Type</option>
                                    <option value="sick">Sick</option>
                                    <option value="casual">Casual</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="input-label">Start Date</label>
                                    <input type="date" name="start_date" x-model="startDate" @change="calculateDays()"
                                        class="input-premium" required min="{{ date('Y-m-d') }}">
                                </div>
                                <div>
                                    <label class="input-label">End Date</label>
                                    <input type="date" name="end_date" x-model="endDate" @change="calculateDays()"
                                        class="input-premium" required :min="startDate">
                                </div>
                            </div>

                            <div class="p-4 bg-violet-50 rounded-xl border border-violet-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-violet-600 uppercase tracking-wider">Total Duration</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl font-black text-violet-700" x-text="totalDays">0</span>
                                    <span class="text-xs font-bold text-violet-400 uppercase">Days</span>
                                </div>
                            </div>

                            <div>
                                <label class="input-label">Reason</label>
                                <textarea name="reason" rows="4" class="input-premium resize-none" placeholder="Provide a detailed reason..." required></textarea>
                            </div>

                            <button type="submit"
                                class="w-full btn-primary-gradient py-3.5 flex items-center justify-center gap-2 shadow-xl shadow-violet-500/20">
                                <i class="bi bi-send-fill"></i>
                                Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leave History -->
            <div class="lg:col-span-2">
                <div class="glass-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-widest">Your Leave History</h3>
                        <span class="px-3 py-1 bg-white border border-slate-200 text-slate-500 text-[10px] font-bold rounded-full">
                            {{ $leaves->count() }} RECORDS
                        </span>
                    </div>
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Type & Date</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800">{{ $leave->leave_type }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                                {{ $leave->total_days }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">Days</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($leave->status == 'pending')
                                            <span class="gradient-badge bg-amber-50 text-amber-600 border border-amber-100 flex items-center gap-1.5 w-fit">
                                                <span class="h-1 w-1 rounded-full bg-amber-400 animate-pulse"></span>
                                                PENDING
                                            </span>
                                        @elseif($leave->status == 'approved')
                                            <span class="gradient-badge bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center gap-1.5 w-fit">
                                                <i class="bi bi-check-circle-fill text-[8px]"></i>
                                                APPROVED
                                            </span>
                                        @else
                                            <span class="gradient-badge bg-rose-50 text-rose-600 border border-rose-100 flex items-center gap-1.5 w-fit">
                                                <i class="bi bi-x-circle-fill text-[8px]"></i>
                                                REJECTED
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($leave->approval_remarks)
                                            <p class="text-[11px] text-slate-500 italic line-clamp-2 max-w-[180px]">"{{ $leave->approval_remarks }}"</p>
                                            <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase truncate">- {{ $leave->approver->name ?? 'Faculty' }}</p>
                                        @else
                                            <span class="text-[10px] text-slate-300 font-medium">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-12">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300">
                                                <i class="bi bi-calendar-x text-2xl"></i>
                                            </div>
                                            <p class="text-sm text-slate-400 font-medium">No leave records found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function leaveForm() {
            return {
                startDate: '',
                endDate: '',
                totalDays: 0,
                calculateDays() {
                    if (this.startDate && this.endDate) {
                        const start = new Date(this.startDate);
                        const end = new Date(this.endDate);
                        if (end >= start) {
                            const diffTime = Math.abs(end - start);
                            this.totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        } else {
                            this.totalDays = 0;
                        }
                    } else {
                        this.totalDays = 0;
                    }
                }
            }
        }
    </script>
@endsection
