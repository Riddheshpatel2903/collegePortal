@extends('layouts.app')

@section('header_title', 'Assignment Management')

@section('content')
    <x-page-header 
        title="Assignment Overview" 
        subtitle="Manage academic assignments, track student submissions, and oversee curriculum progress."
        icon="bi-journal-check"
    />

    <div class="mt-8" x-data="adminAssignmentHandler()">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white border border-slate-200 p-6 rounded-2xl flex items-center gap-5 shadow-sm">
                <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Total Created</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-2xl flex items-center gap-5 shadow-sm">
                <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Active Now</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $stats['active'] }}</h3>
                </div>
            </div>
            <div class="bg-white border border-slate-200 p-6 rounded-2xl flex items-center gap-5 shadow-sm">
                <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl border border-rose-100">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Late Submissions</p>
                    <h3 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $stats['late_submissions'] }}</h3>
                </div>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl flex items-center gap-5 shadow-xl shadow-slate-200">
                <div class="h-12 w-12 rounded-xl bg-white/10 text-white flex items-center justify-center text-xl border border-white/10 shadow-inner">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Submission Rate</p>
                    <h3 class="text-2xl font-bold text-white tracking-tight">{{ $stats['submission_rate'] }}%</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left: Assignment Management -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-8 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Assignment Registry</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-4">Assignment / Faculty</th>
                                    <th class="px-6 py-4">Academic Scope</th>
                                    <th class="px-6 py-4 text-center">Submissions</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($assignments as $assignment)
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-800 leading-tight">{{ $assignment->title }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                                    Prof. {{ $assignment->teacher?->user?->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-1">{{ $assignment->subject?->name ?? 'No Subject' }}</span>
                                                <span class="text-[9px] text-slate-400 font-bold uppercase">
                                                    {{ $assignment->course?->name ?? 'N/A' }} • Sem {{ $assignment->semester?->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded bg-slate-100 text-slate-600 text-[10px] font-bold border border-slate-200">
                                                {{ $assignment->submissions_count }} SUB
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-all">
                                                <button @click="openExtendModal({{ $assignment->toJson() }})"
                                                    class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-bold uppercase tracking-widest border border-emerald-100 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                    Extend
                                                </button>
                                                <form action="{{ route('admin.assignments.force-close', $assignment->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="h-8 px-3 rounded-lg bg-rose-50 text-rose-600 text-[9px] font-bold uppercase tracking-widest border border-rose-100 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                        Close
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Delete this assignment permanently?')" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="h-8 w-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-24 text-center opacity-30">
                                            <i class="bi bi-journal-x text-5xl mb-4"></i>
                                            <p class="text-[10px] font-bold uppercase tracking-widest">No active assignments found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($assignments->hasPages())
                        <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-100">
                            {{ $assignments->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Subject-wise Density -->
            <div class="space-y-6">
                <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-sm">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
                        <i class="bi bi-bar-chart-line text-indigo-500"></i> Curriculum Workload
                    </h3>
                    <div class="space-y-8">
                        @foreach($subjectPerformance as $item)
                            <div class="group">
                                <div class="flex justify-between items-center mb-2.5">
                                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">{{ $item->name }}</span>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">{{ $item->count }} Assignments</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full transition-all duration-700"
                                        style="width: {{ min(($item->count / 10) * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-indigo-600 p-8 rounded-2xl text-white relative overflow-hidden group shadow-xl shadow-indigo-100">
                    <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none group-hover:scale-125 transition-transform">
                        <i class="bi bi-shield-lock-fill text-6xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-11 w-11 rounded-xl bg-white/10 flex items-center justify-center text-xl border border-white/20">
                                <i class="bi bi-check2-all text-white"></i>
                            </div>
                            <h4 class="text-sm font-bold uppercase tracking-widest">Admin Control</h4>
                        </div>
                        <p class="text-[11px] text-indigo-100 leading-relaxed mb-8 font-medium">Global synchronization for academic tasks is active across all departments. System health is optimal.</p>
                        <button class="w-full py-3.5 bg-white text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-lg active:scale-[0.98]">
                            View Detailed Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Deadline Modal -->
        <div x-show="showExtendModal" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showExtendModal = false"></div>
            
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg relative z-10 overflow-hidden" @click.away="showExtendModal = false">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between font-bold text-slate-800">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <h3 class="text-lg">Extend Deadline</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="currentAssignment?.title"></p>
                        </div>
                    </div>
                    <button @click="showExtendModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition-all">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form :action="`/admin/assignments/${currentAssignment?.id}/extend`" method="POST" class="p-8 space-y-8">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">New Submission Deadline</label>
                        <input type="datetime-local" name="new_due_date" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 px-4" required>
                        <div class="flex items-center gap-2 mt-4 text-[10px] font-medium text-slate-400">
                            <i class="bi bi-info-circle text-indigo-400"></i>
                            <span>Current deadline for this task: <strong class="text-slate-600" x-text="currentAssignment?.due_date"></strong></span>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="showExtendModal = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            Update Deadline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function adminAssignmentHandler() {
            return {
                showExtendModal: false,
                currentAssignment: null,
                openExtendModal(assignment) {
                    this.currentAssignment = assignment;
                    this.showExtendModal = true;
                }
            }
        }
    </script>
@endsection
