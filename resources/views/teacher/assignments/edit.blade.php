@extends('layouts.app')

@section('header_title', 'Edit Assignment')

@section('content')
    <div class="animate-fade-in max-w-4xl mx-auto">
        <!-- Breadcrumbs & Back -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('teacher.assignments.index') }}"
                class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-violet-600 hover:border-violet-100 transition-all shadow-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Assignment</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Update parameters and requirements
                </p>
            </div>
        </div>

        <form action="{{ route('teacher.assignments.update', $assignment->id) }}" method="POST"
            enctype="multipart/form-data" class="space-y-8" x-data="formLogic()">
            @csrf
            @method('PUT')

            <div class="glass-card p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Title -->
                    <div class="md:col-span-2">
                        <label class="input-label">Assignment Title</label>
                        <input type="text" name="title" class="input-premium"
                            placeholder="e.g. Advanced Data Structures - Project 1"
                            value="{{ old('title', $assignment->title) }}" required>
                        @error('title') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Subject & Total Marks -->
                    <div class="flex flex-col gap-2">
                        <label class="input-label">Target Subject & Class</label>
                        <select name="subject_id" class="input-premium appearance-none" required>
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->course->name }} - {{ $subject->semester_label ?? ('Semester '.$subject->semester_sequence) }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="input-label">Total Marks</label>
                        <div class="relative">
                            <input type="number" name="total_marks" class="input-premium pl-12" placeholder="100"
                                value="{{ old('total_marks', $assignment->total_marks) }}" required>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="bi bi-award-fill"></i>
                            </div>
                        </div>
                        @error('total_marks') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="input-label">Requirement Details (Description)</label>
                        <textarea name="description" rows="6" class="input-premium resize-none"
                            placeholder="Provide clear instructions for the students..."
                            required>{{ old('description', $assignment->description) }}</textarea>
                        @error('description') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date & Submission Logic -->
                    <div class="flex flex-col gap-2">
                        <label class="input-label">Due Date & Time</label>
                        <input type="datetime-local" name="due_date" class="input-premium"
                            value="{{ old('due_date', \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d\TH:i')) }}"
                            required>
                        @error('due_date') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="input-label">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="file-input-premium">
                        @if($assignment->attachment_path)
                            <p class="text-[9px] text-emerald-600 font-bold mt-1 uppercase tracking-tight italic">Current:
                                Attached</p>
                        @else
                            <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase tracking-tight italic">PDF, DOCX, ZIP
                                (MAX 10MB)</p>
                        @endif
                        @error('attachment') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Advance Options -->
            <div class="glass-card p-8 group overflow-hidden relative">
                <div class="absolute -right-16 -top-16 h-48 w-48 bg-violet-600/5 blur-3xl rounded-full"></div>

                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Advanced Submission Logic</h3>

                <div class="space-y-6">
                    <div
                        class="flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                        <div class="flex items-center gap-4">
                            <div
                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-800">Allow Late Submission</h4>
                                <p
                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5 whitespace-nowrap">
                                    Penalty logic applies</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="allow_late_submission" value="0">
                            <input type="checkbox" name="allow_late_submission" value="1" class="sr-only peer" {{ old('allow_late_submission', $assignment->allow_late_submission) ? 'checked' : '' }}
                                @change="showLateDate = $el.checked">
                            <div
                                class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600">
                            </div>
                        </label>
                    </div>

                    <div x-show="showLateDate" x-transition class="animate-fade-in">
                        <label class="input-label">Late Until (Hard Deadline)</label>
                        <input type="datetime-local" name="late_until" class="input-premium"
                            value="{{ old('late_until', $assignment->late_until ? \Carbon\Carbon::parse($assignment->late_until)->format('Y-m-d\TH:i') : '') }}">
                        @error('late_until') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="input-label mb-3">Visibility Status</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="status" value="published" class="sr-only peer" {{ old('status', $assignment->status) == 'published' ? 'checked' : '' }}>
                                <div
                                    class="px-6 py-4 rounded-2xl border-2 border-slate-100 group-hover:border-slate-200 peer-checked:border-violet-600 peer-checked:bg-violet-50/50 transition-all flex flex-col items-center gap-2">
                                    <i class="bi bi-broadcast text-xl text-slate-400 peer-checked:text-violet-600"></i>
                                    <span
                                        class="text-[11px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-violet-700">Published</span>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="status" value="draft" class="sr-only peer" {{ old('status', $assignment->status) == 'draft' ? 'checked' : '' }}>
                                <div
                                    class="px-6 py-4 rounded-2xl border-2 border-slate-100 group-hover:border-slate-200 peer-checked:border-slate-800 peer-checked:bg-slate-50 transition-all flex flex-col items-center gap-2">
                                    <i class="bi bi-pencil text-xl text-slate-400 peer-checked:text-slate-800"></i>
                                    <span
                                        class="text-[11px] font-black uppercase tracking-widest text-slate-500 peer-checked:text-slate-800">Saved
                                        as Draft</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pb-12">
                <a href="{{ route('teacher.assignments.index') }}"
                    class="px-8 py-4 rounded-2xl bg-slate-100 text-slate-600 font-extrabold text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">
                    Discard Changes
                </a>
                <button type="submit" class="button-premium px-10 py-4 shadow-xl">
                    <span class="relative z-10 flex items-center gap-2">
                        <i class="bi bi-check-lg"></i>
                        Update Assignment Details
                    </span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function formLogic() {
            return {
                showLateDate: {{ old('allow_late_submission', $assignment->allow_late_submission) ? 'true' : 'false' }}
                }
        }
    </script>
@endsection
