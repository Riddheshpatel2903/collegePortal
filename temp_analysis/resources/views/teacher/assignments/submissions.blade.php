@extends('layouts.app')

@section('header_title', 'Assignment Submissions')

@section('content')
    <div class="animate-fade-in" x-data="gradingHandler()">
        <!-- Header Info -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.assignments.index') }}"
                    class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-violet-600 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $assignment->title }}</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        {{ $assignment->subject->name }} | {{ $assignment->submissions->count() }} Submissions
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-slate-100 rounded-xl">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Marks</span>
                    <span class="text-sm font-black text-slate-800">{{ $assignment->total_marks }}</span>
                </div>
                <div class="px-4 py-2 bg-violet-50 rounded-xl">
                    <span class="text-[10px] font-bold text-violet-400 uppercase tracking-widest block">Due Date</span>
                    <span
                        class="text-sm font-black text-violet-600">{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Filters & Bulk (Placeholder) -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <button
                    class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:border-violet-200 transition-all flex items-center gap-2">
                    <i class="bi bi-filter"></i> All Submissions
                </button>
                <div class="h-6 w-px bg-slate-200 mx-2"></div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grading Progress:
                    {{ $submissions->where('status', 'graded')->count() }}/{{ $submissions->count() }}</p>
            </div>
            <button class="text-xs font-bold text-violet-600 hover:underline">Download All (ZIP)</button>
        </div>

        <!-- Submissions Table -->
        <div class="glass-card overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Student Information</th>
                        <th>Submission Details</th>
                        <th>Status</th>
                        <th>Marks / {{ $assignment->total_marks }}</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                                    <tr class="group" id="submission-row-{{ $submission->id }}">
                                        <td>
                                            <div class="flex items-center gap-4">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($submission->student->user->name) }}&background=f8fafc&color=475569"
                                                    class="h-10 w-10 rounded-xl" alt="">
                                                <div>
                                                    <p class="text-sm font-black text-slate-800">{{ $submission->student->user->name }}</p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase">
                                                        {{ $submission->student->roll_number }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                                                    class="text-xs font-bold text-violet-600 hover:underline flex items-center gap-2">
                                                    <i class="bi bi-file-earmark-arrow-down"></i>
                                                    View Submitted File
                                                </a>
                                                <span class="text-[10px] text-slate-400 font-bold mt-1 uppercase">
                                                    Submitted {{ \Carbon\Carbon::parse($submission->submitted_at)->diffForHumans() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClasses = [
                                                    'submitted' => 'bg-blue-50 text-blue-600',
                                                    'late' => 'bg-amber-50 text-amber-600',
                                                    'graded' => 'bg-emerald-50 text-emerald-600'
                                                ];
                                            @endphp
                        <span
                                                class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest {{ $statusClasses[$submission->status] ?? 'bg-slate-50 text-slate-500' }}">
                                                {{ $submission->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <input type="number" x-model="row_marks_{{ $submission->id }}" placeholder="--"
                                                    class="w-16 h-8 text-center bg-slate-50 border-0 rounded-lg text-xs font-black text-slate-800 focus:ring-2 focus:ring-violet-500/20 transition-all"
                                                    value="{{ $submission->marks_obtained }}">
                                                <span class="text-xs font-bold text-slate-300">/ {{ $assignment->total_marks }}</span>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <button @click="openGradeModal({{ $submission->toJson() }})"
                                                class="h-9 px-4 rounded-xl bg-violet-600 text-white text-[10px] font-extrabold uppercase tracking-widest hover:bg-violet-700 transition-all shadow-md shadow-violet-600/10">
                                                {{ $submission->status === 'graded' ? 'Edit Grade' : 'Grade' }}
                                            </button>
                                        </td>
                                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-20 text-slate-400">No submissions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Grading Modal -->
        <template x-if="showGradeModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div @click="showGradeModal = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-in">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-lg font-black tracking-tight text-slate-800">Grade Submission</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1"
                                x-text="currentSubmission.student.user.name"></p>
                        </div>
                        <button @click="showGradeModal = false" class="text-slate-400 hover:text-slate-600"><i
                                class="bi bi-x-lg"></i></button>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6">
                            <!-- Marks -->
                            <div>
                                <label class="input-label">Marks Obtained (Out of {{ $assignment->total_marks }})</label>
                                <input type="number" x-model="gradingMarks" class="input-premium"
                                    :max="{{ $assignment->total_marks }}" min="0">
                            </div>

                            <!-- Feedback -->
                            <div>
                                <label class="input-label">Teacher Feedback</label>
                                <textarea x-model="gradingFeedback" rows="4" class="input-premium resize-none"
                                    placeholder="Enter constructive feedback..."></textarea>
                            </div>

                            <div class="flex gap-3">
                                <button @click="showGradeModal = false"
                                    class="flex-1 px-6 py-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-extrabold uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    Cancel
                                </button>
                                <button @click="submitGrade()"
                                    class="flex-1 px-6 py-3 rounded-xl bg-violet-600 text-white text-xs font-extrabold uppercase tracking-widest transition-all shadow-xl shadow-violet-600/20 flex items-center justify-center gap-2">
                                    <span x-show="!submitting">Submit Grade</span>
                                    <span x-show="submitting"
                                        class="animate-spin h-4 w-4 border-2 border-white/30 border-t-white rounded-full"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function gradingHandler() {
            return {
                showGradeModal: false,
                currentSubmission: null,
                gradingMarks: '',
                gradingFeedback: '',
                submitting: false,
                openGradeModal(submission) {
                    this.currentSubmission = submission;
                    this.gradingMarks = submission.marks_obtained || '';
                    this.gradingFeedback = submission.feedback || '';
                    this.showGradeModal = true;
                },
                async submitGrade() {
                    if (this.gradingMarks > {{ $assignment->total_marks }}) {
                        alert('Marks cannot exceed total marks');
                        return;
                    }

                    this.submitting = true;
                    try {
                        const response = await fetch(`/teacher/assignments/submissions/${this.currentSubmission.id}/grade`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                marks_obtained: this.gradingMarks,
                                feedback: this.gradingFeedback
                            })
                        });

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            const data = await response.json();
                            alert(data.error || 'Something went wrong');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error saving grade');
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
@endsection