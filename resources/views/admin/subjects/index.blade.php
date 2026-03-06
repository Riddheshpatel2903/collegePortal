@extends('layouts.app')

@section('header_title', 'Subjects')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Subjects</h2>
            <p class="text-sm text-slate-400 mt-1">Manage academic subjects, credit hours, and faculty assignments.</p>
        </div>
        <a href="{{ route('admin.subjects.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
            <i class="bi bi-plus-lg"></i> Add Subject
        </a>
    </div>

    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Course / Semester</th>
                    <th>Lead Educator</th>
                    <th class="text-center">Hours</th>
                    <th class="text-center">Type</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $sub)
                    <tr class="group">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($sub->name, 0, 2)) }}
                                </div>
                                <span class="text-sm font-bold text-slate-800">{{ $sub->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="gradient-badge bg-indigo-50 text-indigo-600">{{ $sub->course->name ?? 'N/A' }} - Sem {{ (int) $sub->semester_sequence }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="h-7 w-7 rounded-md bg-slate-100 text-slate-400 flex items-center justify-center text-xs">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span class="text-sm text-slate-600 font-medium">{{ $sub->teacher->user->name ?? 'Unassigned' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="gradient-badge bg-teal-50 text-teal-600 font-bold">{{ (int) ($sub->weekly_hours ?? $sub->credits) }} / week</span>
                        </td>
                        <td class="text-center">
                            <x-badge :type="($sub->is_lab ?? false) ? 'warning' : 'info'">{{ ($sub->is_lab ?? false) ? 'Lab' : 'Theory' }}</x-badge>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.subjects.edit', $sub->id) }}"
                                    class="h-8 w-8 rounded-lg bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white transition-all flex items-center justify-center text-sm"><i
                                        class="bi bi-pencil-square"></i></a>
                                <form action="{{ route('admin.subjects.destroy', $sub->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-sm"><i
                                            class="bi bi-trash3-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-sm text-slate-400 py-8">No subjects found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 mx-auto">
            {{ $subjects->links() }}
        </div>
    </div>
@endsection
