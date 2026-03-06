@extends('layouts.app')

@section('header_title', 'Assignment Overview')

@section('content')
    <div class="animate-fade-in" x-data="adminAssignmentHandler()">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card flex items-center gap-5">
                <div
                    class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Total</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="stat-card flex items-center gap-5">
                <div
                    class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Active</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ $stats['active'] }}</h3>
                </div>
            </div>
            <div class="stat-card flex items-center gap-5">
                <div
                    class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Late</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ $stats['late_submissions'] }}</h3>
                </div>
            </div>
            <div class="stat-card flex items-center gap-5 border-2 border-violet-100/50">
                <div
                    class="h-12 w-12 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-violet-200">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Avg. Rate</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ $stats['submission_rate'] }}%</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left: Assignment Management -->
            <div class="lg:col-span-2">
                <div class="glass-card overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Global Assignment Registry
                        </h3>
                        <div class="flex gap-2">
                            <button
                                class="h-8 w-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-slate-600"><i
                                    class="bi bi-arrow-clockwise"></i></button>
                            <button
                                class="h-8 w-8 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-slate-600"><i
                                    class="bi bi-download"></i></button>
                        </div>
                    </div>
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Assignment & Teacher</th>
                                <th>Target</th>
                                <th class="text-center">Submissions</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr class="group">
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-800">{{ $assignment->title }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-0.5">
                                                By Prof. {{ $assignment->teacher->user->name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[10px] font-black text-violet-600 uppercase">{{ $assignment->subject->name }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold">{{ $assignment->course->name }} -
                                                {{ $assignment->semester->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="text-xs font-black text-slate-700">{{ $assignment->submissions_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        <div
                                            class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openExtendModal({{ $assignment->toJson() }})"
                                                class="h-8 px-3 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all">
                                                Extend
                                            </button>
                                            <form action="{{ route('admin.assignments.force-close', $assignment->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="h-8 px-3 rounded-lg bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all">
                                                    Close
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.assignments.destroy', $assignment->id) }}"
                                                method="POST" onsubmit="return confirm('Hard delete this assignment?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="h-8 w-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 hover:text-rose-600 transition-all">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-20 text-slate-400">No data records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-50">
                        {{ $assignments->links() }}
                    </div>
                </div>
            </div>

            <!-- Right: Performance Snapshot -->
            <div class="space-y-6">
                <div class="glass-card p-6">
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-6">Subject Performance</h3>
                    <div class="space-y-6">
                        @foreach($subjectPerformance as $item)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span
                                        class="text-[11px] font-black text-slate-600 uppercase tracking-tight">{{ $item->name }}</span>
                                    <span class="text-[11px] font-black text-violet-600">{{ $item->count }}</span>
                                </div>
                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-violet-600 rounded-full"
                                        style="width: {{ min(($item->count / 20) * 100, 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="glass-card p-6 bg-gradient-to-br from-slate-900 to-indigo-900 text-white border-0">
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="h-10 w-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl">
                            <i class="bi bi-lightning-fill text-amber-400"></i>
                        </div>
                        <h4 class="text-sm font-black uppercase tracking-widest">System Health</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6 font-medium">All modules of the Assignment System
                        are currently operational and synchronized.</p>
                    <button
                        class="w-full py-3 rounded-xl bg-violet-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-violet-700 transition-all shadow-lg shadow-violet-900/50">
                        Generate Audit Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Extend Deadline Modal -->
        <template x-if="showExtendModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div @click="showExtendModal = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-in">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-emerald-50/50">
                        <div>
                            <h3 class="text-lg font-black tracking-tight text-emerald-900">Extend Deadline</h3>
                            <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mt-1"
                                x-text="currentAssignment.title"></p>
                        </div>
                        <button @click="showExtendModal = false" class="text-slate-400 hover:text-slate-600"><i
                                class="bi bi-x-lg"></i></button>
                    </div>

                    <form :action="`/admin/assignments/${currentAssignment.id}/extend`" method="POST" class="p-8">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label class="input-label">New Deadline Date & Time</label>
                                <input type="datetime-local" name="new_due_date" class="input-premium" required>
                                <p class="text-[9px] text-emerald-600 font-bold mt-2 uppercase">Current: <span
                                        x-text="currentAssignment.due_date"></span></p>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showExtendModal = false"
                                    class="flex-1 px-6 py-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-extrabold uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="flex-1 px-6 py-3 rounded-xl bg-emerald-600 text-white text-xs font-extrabold uppercase tracking-widest transition-all shadow-xl shadow-emerald-600/20">
                                    Confirm Extension
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