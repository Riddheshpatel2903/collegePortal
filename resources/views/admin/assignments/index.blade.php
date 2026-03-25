@extends('layouts.app')

@section('header_title', 'Assignment Nexus')

@section('content')
    <x-page-header 
        title="Assignment Nexus" 
        subtitle="Global curriculum tracking and submission oversight" 
        tag="Academic Sync"
        icon="bi-journal-check"
    />

    <div class="animate-fade-in" x-data="adminAssignmentHandler()">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-violet-500">
                <div class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Assets</p>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-emerald-500">
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Now</p>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['active'] }}</h3>
                </div>
            </div>
            <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-rose-500">
                <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Overdue</p>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['late_submissions'] }}</h3>
                </div>
            </div>
            <div class="glass-card p-6 flex items-center gap-5 border-l-4 border-l-indigo-500 bg-slate-900 text-white border-0 shadow-xl shadow-slate-900/10">
                <div class="h-12 w-12 rounded-2xl bg-white/10 text-white flex items-center justify-center text-xl shadow-inner backdrop-blur-md">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1">Success Rate</p>
                    <h3 class="text-2xl font-black text-white tracking-tight">{{ $stats['submission_rate'] }}%</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left: Assignment Management -->
            <div class="lg:col-span-2">
                <div class="glass-card overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Assignment Registry</h3>
                        <div class="flex gap-2">
                            <button class="h-8 w-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-violet-600 transition-all shadow-sm">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button class="h-8 w-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-violet-600 transition-all shadow-sm">
                                <i class="bi bi-download"></i>
                            </button>
                        </div>
                    </div>
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Assignment & Mentor</th>
                                <th>Academic Scope</th>
                                <th class="text-center">Nodes</th>
                                <th class="text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr class="group hover:bg-slate-50/50 transition-colors">
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-800 tracking-tight">{{ $assignment->title }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-0.5">
                                                Prof. {{ $assignment->teacher?->user?->name ?? 'Unassigned' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-violet-600 uppercase tracking-widest leading-none mb-1">{{ $assignment->subject?->name ?? 'No Subject' }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                                                {{ $assignment->course?->name ?? 'No Course' }} • {{ $assignment->semester?->name ?? 'No Semester' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-50 text-slate-600 text-[10px] font-black">{{ $assignment->submissions_count }} SUB</span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openExtendModal({{ $assignment->toJson() }})"
                                                class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                Extend
                                            </button>
                                            <form action="{{ route('admin.assignments.force-close', $assignment->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="h-8 px-3 rounded-lg bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                    Close
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Hard delete this assignment?')">
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
                                    <td colspan="4" class="text-center py-32">
                                        <i class="bi bi-journal-x text-6xl text-slate-100 mb-6 block"></i>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No matching assignment indices found</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-50">
                        {{ $assignments->links() }}
                    </div>
                </div>
            </div>

            <!-- Right: Performance Snapshot -->
            <div class="space-y-6">
                <div class="glass-card p-8 border-l-4 border-l-violet-500">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">Curriculum Density</h3>
                    <div class="space-y-8">
                        @foreach($subjectPerformance as $item)
                            <div class="group">
                                <div class="flex justify-between items-center mb-2.5">
                                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest group-hover:text-violet-600 transition-colors">{{ $item->name }}</span>
                                    <span class="text-[10px] font-black text-violet-600 bg-violet-50 px-2 py-0.5 rounded">{{ $item->count }} Tasks</span>
                                </div>
                                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full bg-gradient-to-r from-violet-500 to-indigo-600 rounded-full group-hover:brightness-110 transition-all"
                                        style="width: {{ min(($item->count / 20) * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="glass-card p-8 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 text-white border-0 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -m-4 h-32 w-32 bg-violet-500/10 rounded-full blur-3xl group-hover:bg-violet-500/20 transition-all"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl border border-white/10 shadow-xl">
                                <i class="bi bi-shield-check text-indigo-400"></i>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-300">System Core</h4>
                                <p class="text-sm font-black text-white leading-none mt-1">Assignments Shield</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed mb-8 font-medium">Global synchronization across all departmental nodes is active. Repository health is optimal.</p>
                        <button class="w-full py-4 rounded-2xl bg-violet-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-violet-700 transition-all shadow-xl shadow-violet-900/50 active:scale-[0.98]">
                            Generate Security Audit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend Deadline Modal -->
        <template x-if="showExtendModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div @click="showExtendModal = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-hidden animate-fade-in border border-white/20">
                    <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-emerald-50/30">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black tracking-tight text-slate-800">Deadline Adjustment</h3>
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-1" x-text="currentAssignment.title"></p>
                            </div>
                        </div>
                        <button @click="showExtendModal = false" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all">
                            <i class="bi bi-x-lg text-lg"></i>
                        </button>
                    </div>

                    <form :action="`/admin/assignments/${currentAssignment.id}/extend`" method="POST" class="p-10">
                        @csrf
                        <div class="space-y-8">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="input-label mb-0">Synchronized Terminal Target</label>
                                    <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded uppercase tracking-widest">Active Link</span>
                                </div>
                                <input type="datetime-local" name="new_due_date" class="input-premium h-14 text-sm" required>
                                <div class="flex items-center gap-2 mt-4">
                                    <i class="bi bi-info-circle text-slate-300"></i>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">
                                        Current Endpoint: <span class="text-slate-600" x-text="currentAssignment.due_date"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button type="button" @click="showExtendModal = false"
                                    class="flex-1 h-14 rounded-2xl bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    Abort Operation
                                </button>
                                <button type="submit"
                                    class="flex-1 h-14 rounded-2xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-900/10 active:scale-[0.98]">
                                    Confirm Sync
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
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
