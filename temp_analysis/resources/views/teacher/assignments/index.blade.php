@extends('layouts.app')

@section('header_title', 'Manage Assignments')

@section('content')
    <div class="animate-fade-in">
        <!-- Header Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Your Assignments</h2>
                <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-widest">Create, track, and grade
                    submissions</p>
            </div>
            <a href="{{ route('teacher.assignments.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3.5 bg-violet-600 text-white rounded-2xl font-extrabold text-xs uppercase tracking-widest hover:bg-violet-700 transition-all shadow-lg shadow-violet-600/20 group">
                <i class="bi bi-plus-lg group-hover:rotate-90 transition-transform"></i>
                Create New Assignment
            </a>
        </div>

        <!-- Assignment List -->
        <div class="glass-card overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Assignment Details</th>
                        <th>Subject & Class</th>
                        <th>Deadline</th>
                        <th>Submissions</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                                    <tr class="group">
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-black text-slate-800">{{ $assignment->title }}</span>
                                                <span
                                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mt-0.5 line-clamp-1">
                                                    {{ Str::limit($assignment->description, 50) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-700">{{ $assignment->subject->name }}</span>
                                                <span class="text-[10px] text-violet-500 font-bold uppercase tracking-tight">
                                                    {{ $assignment->course->name }} - Semester {{ $assignment->semester_number }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <i class="bi bi-calendar-event text-slate-400"></i>
                                                <span class="text-xs font-bold text-slate-600">
                                                    {{ \Carbon\Carbon::parse($assignment->due_date)->format('d M, Y') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('teacher.assignments.submissions', $assignment->id) }}"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg group-hover:bg-violet-50 transition-colors">
                                                <span
                                                    class="text-xs font-black text-slate-700 group-hover:text-violet-700">{{ $assignment->submissions_count }}</span>
                                                <span
                                                    class="text-[10px] font-bold text-slate-400 group-hover:text-violet-400 uppercase tracking-widest">Submissions</span>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClasses = [
                                                    'draft' => 'bg-slate-100 text-slate-500',
                                                    'published' => 'bg-emerald-50 text-emerald-600',
                                                    'closed' => 'bg-rose-50 text-rose-600'
                                                ];
                                                if ($assignment->status === 'published' && \Carbon\Carbon::parse($assignment->due_date)->isPast()) {
                                                    $statusLabel = 'Closed';
                                                    $statusClass = $statusClasses['closed'];
                                                } else {
                                                    $statusLabel = ucfirst($assignment->status);
                                                    $statusClass = $statusClasses[$assignment->status] ?? $statusClasses['draft'];
                                                }
                                            @endphp
                        <span
                                                class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2">
                                                @featureEnabled('edit_button_enabled')
                                                <a href="{{ route('teacher.assignments.edit', $assignment->id) }}"
                                                    class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-violet-50 hover:text-violet-600 transition-all">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                @endfeatureEnabled
                                                <form action="{{ route('teacher.assignments.destroy', $assignment->id) }}" method="POST"
                                                    onsubmit="return confirm('Delete this assignment permanently?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="h-9 w-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="h-16 w-16 rounded-3xl bg-slate-50 flex items-center justify-center text-3xl text-slate-200 mb-4">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No assignments found
                                    </p>
                                    <a href="{{ route('teacher.assignments.create') }}"
                                        class="text-violet-600 font-black text-sm mt-2 hover:underline">Create your first now
                                        →</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
@endsection
