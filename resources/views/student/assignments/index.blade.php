@extends('layouts.app')

@section('header_title', 'My Assignments')

@section('content')
    <div class="animate-fade-in" x-data="assignmentTracker()">
        <!-- Filter Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Active Assignments</h2>
                <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-widest">Track deadlines and submit your work</p>
            </div>
            <div class="flex items-center gap-2 p-1.5 bg-slate-100/50 rounded-2xl border border-slate-200/50">
                <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-white text-violet-600 shadow-sm' : 'text-slate-500'" class="px-5 py-2 rounded-xl text-[10px] font-extrabold uppercase tracking-widest transition-all">All</button>
                <button @click="filter = 'pending'" :class="filter === 'pending' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500'" class="px-5 py-2 rounded-xl text-[10px] font-extrabold uppercase tracking-widest transition-all">Pending</button>
                <button @click="filter = 'submitted'" :class="filter === 'submitted' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500'" class="px-5 py-2 rounded-xl text-[10px] font-extrabold uppercase tracking-widest transition-all">Submitted</button>
            </div>
        </div>

        <!-- Assignments Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($assignments as $assignment)
                @php
                    $submission = $assignment->submissions->first();
                    $isLate = \Carbon\Carbon::parse($assignment->due_date)->isPast();
                    $daysRemaining = \Carbon\Carbon::parse($assignment->due_date)->diffInDays(now());
                @endphp
                
                <div class="glass-card group flex flex-col h-full overflow-hidden relative" 
                    x-show="filter === 'all' || (filter === 'pending' && !{{ $submission ? 'true' : 'false' }}) || (filter === 'submitted' && {{ $submission ? 'true' : 'false' }})"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    
                    <!-- Card Top Decor -->
                    <div class="absolute top-0 left-0 w-full h-1 {{ $submission ? ($submission->status === 'graded' ? 'bg-emerald-500' : 'bg-blue-500') : ($isLate ? 'bg-rose-500' : 'bg-violet-500') }}"></div>

                    <div class="p-6 flex-1">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-3 py-1 rounded-lg bg-violet-50 text-violet-600 text-[10px] font-black uppercase tracking-widest">
                                {{ $assignment->subject->name }}
                            </span>
                            @if($submission)
                                <span class="flex items-center gap-1.5 text-emerald-600">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Submitted</span>
                                </span>
                            @elseif($isLate)
                                <span class="flex items-center gap-1.5 text-rose-500">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Overdue</span>
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ongoing</span>
                            @endif
                        </div>

                        <h3 class="text-lg font-black text-slate-800 leading-tight mb-2">{{ $assignment->title }}</h3>
                        <p class="text-xs font-bold text-slate-500 mb-6 line-clamp-2">{{ $assignment->description }}</p>

                        <!-- Meta Row -->
                        <div class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl mb-6">
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($assignment->teacher->user->name) }}&background=6366f1&color=ffffff" 
                                    class="h-8 w-8 rounded-lg shadow-sm" alt="">
                                <div>
                                    <p class="text-[10px] font-black text-slate-800">{{ $assignment->teacher->user->name }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Faculty</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-slate-800">{{ $assignment->total_marks }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic">Points</p>
                            </div>
                        </div>

                        <!-- Countdown / Status Info -->
                        @if(!$submission)
                            <div class="mb-6 p-4 rounded-2xl {{ $isLate ? 'bg-rose-50/50 border border-rose-100' : 'bg-violet-50/50 border border-violet-100' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-black {{ $isLate ? 'text-rose-600' : 'text-violet-600' }} uppercase tracking-widest">
                                        {{ $isLate ? 'Deadline Missed' : 'Time Remaining' }}
                                    </p>
                                    <i class="bi bi-clock-fill text-xs {{ $isLate ? 'text-rose-400' : 'text-violet-400' }}"></i>
                                </div>
                                <div class="text-lg font-black {{ $isLate ? 'text-rose-700' : 'text-violet-700' }} tracking-tighter"
                                    x-init="countdown('{{ $assignment->due_date }}', 'timer-{{ $assignment->id }}')"
                                    id="timer-{{ $assignment->id }}">
                                    <!-- Countdown values populated by JS -->
                                    {{ $isLate ? 'EXPIRED' : 'Calculating...' }}
                                </div>
                            </div>
                        @else
                            <div class="mb-6 p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                                @if($submission->status === 'graded')
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Results Declared</p>
                                        <div class="text-sm font-black text-emerald-700">
                                            {{ $submission->marks_obtained }} <span class="text-[10px] font-bold text-emerald-400">/ {{ $assignment->total_marks }}</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-emerald-600 italic font-bold">"{{ Str::limit($submission->feedback, 60) }}"</p>
                                @else
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Grade Pending</p>
                                    <p class="text-[9px] text-emerald-500 font-bold uppercase tracking-tight">Submitted on {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M, Y') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-5 bg-slate-50/30 border-t border-slate-100 mt-auto">
                        <div class="flex gap-2">
                            @if($assignment->attachment_path)
                                <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 h-11 rounded-xl bg-white border border-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-widest hover:border-violet-200 hover:text-violet-600 transition-all">
                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                    Materials
                                </a>
                            @endif

                            @if(!$submission && $assignment->isSubmissionAllowed())
                                <button @click="openSubmitModal({{ $assignment->toJson() }})"
                                    class="flex-[2] h-11 rounded-xl bg-violet-600 text-white text-[10px] font-extrabold uppercase tracking-widest hover:bg-violet-700 transition-all shadow-lg shadow-violet-600/20">
                                    Submit Now
                                </button>
                            @elseif($submission && $submission->status !== 'graded' && $assignment->isSubmissionAllowed())
                                <button @click="openResubmitModal({{ $assignment->toJson() }})"
                                    class="flex-[2] h-11 rounded-xl border border-violet-200 bg-white text-violet-600 text-[10px] font-extrabold uppercase tracking-widest hover:bg-violet-50 transition-all">
                                    Resubmit
                                </button>
                            @else
                                <div class="flex-[2] flex items-center justify-center h-11 rounded-xl bg-slate-100 text-slate-400 text-[10px] font-extrabold uppercase tracking-widest">
                                    {{ $submission ? 'Completed' : 'Locked' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-32 text-center">
                    <div class="h-20 w-20 bg-slate-100 rounded-3xl mx-auto flex items-center justify-center text-3xl text-slate-300 mb-6 font-black uppercase">
                        <i class="bi bi-inboxes-fill"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">All caught up!</h3>
                    <p class="text-sm font-bold text-slate-400 mt-2 uppercase tracking-widest">No active assignments directed to your class</p>
                </div>
            @endforelse
        </div>
        <div class="mt-4">
            {{ $assignments->links() }}
        </div>

        <!-- Submission Modal -->
        <template x-if="showModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div @click="showModal = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-in">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-violet-50/50">
                        <div>
                            <h3 class="text-lg font-black tracking-tight text-violet-900" x-text="mode === 'submit' ? 'Submit Work' : 'Update Work'"></h3>
                            <p class="text-xs font-bold text-violet-400 uppercase tracking-widest mt-1" x-text="currentAssignment.title"></p>
                        </div>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                    </div>

                    <form :action="mode === 'submit' ? `/student/assignments/${currentAssignment.id}/submit` : `/student/assignments/${currentAssignment.id}/resubmit`" 
                        method="POST" enctype="multipart/form-data" class="p-8">
                        @csrf
                        <div class="space-y-6">
                            <div class="p-6 border-2 border-dashed border-slate-200 rounded-2xl hover:border-violet-200 transition-all group relative bg-slate-50/50">
                                <input type="file" name="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                                <div class="text-center">
                                    <div class="h-12 w-12 bg-white rounded-2xl mx-auto flex items-center justify-center text-xl text-slate-400 group-hover:text-violet-600 shadow-sm transition-all mb-4">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <p class="text-xs font-black text-slate-700 tracking-tight">Drop your file or Click</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">PDF, DOCX, ZIP (MAX 20MB)</p>
                                </div>
                            </div>

                            <div class="px-4 py-3 rounded-xl bg-amber-50 border border-amber-100 flex items-center gap-3">
                                <i class="bi bi-info-circle-fill text-amber-500"></i>
                                <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Late submissions are tagged automatically.</p>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" @click="showModal = false" class="flex-1 px-6 py-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-extrabold uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 px-6 py-4 rounded-xl bg-violet-600 text-white text-xs font-extrabold uppercase tracking-widest transition-all shadow-xl shadow-violet-600/20">
                                    Publish Work
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <script>
        function assignmentTracker() {
            return {
                filter: 'all',
                showModal: false,
                mode: 'submit',
                currentAssignment: null,
                openSubmitModal(assignment) {
                    this.currentAssignment = assignment;
                    this.mode = 'submit';
                    this.showModal = true;
                },
                openResubmitModal(assignment) {
                    this.currentAssignment = assignment;
                    this.mode = 'resubmit';
                    this.showModal = true;
                },
                countdown(targetDate, elementId) {
                    const second = 1000,
                        minute = second * 60,
                        hour = minute * 60,
                        day = hour * 24;

                    const countDown = new Date(targetDate).getTime();
                    const x = setInterval(function() {
                        const now = new Date().getTime(),
                            distance = countDown - now;

                        const el = document.getElementById(elementId);
                        if (!el) { clearInterval(x); return; }

                        if (distance < 0) {
                            el.innerHTML = "EXPIRED";
                            clearInterval(x);
                        } else {
                            const d = Math.floor(distance / day),
                                h = Math.floor((distance % day) / hour),
                                m = Math.floor((distance % hour) / minute),
                                s = Math.floor((distance % minute) / second);

                            el.innerHTML = `${d}D ${h}H ${m}M ${s}S`;
                        }
                    }, 1000);
                }
            }
        }
    </script>
@endsection
