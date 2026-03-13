@extends('layouts.app')

@section('header_title', 'Subjects')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Subjects</h2>
            <p class="text-sm text-slate-400 mt-1">Manage academic subjects, credit hours, and faculty assignments.</p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.subjects.delete-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ALL subjects? This action cannot be undone.')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-50 text-rose-600 text-sm font-semibold rounded-xl hover:bg-rose-600 hover:text-white transition-all">
                    <i class="bi bi-trash3"></i> Delete All
                </button>
            </form>
            <button onclick="document.getElementById('import_curriculum_modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all">
                <i class="bi bi-file-earmark-arrow-up"></i> Bulk Import
            </button>
            <a href="{{ route('admin.subjects.create') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-violet-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">
                <i class="bi bi-plus-lg"></i> Add Subject
            </a>
        </div>
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
    </div>

    <!-- Import Curriculum Modal -->
    <div id="import_curriculum_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass-card max-w-lg w-full p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800">Bulk Import Subjects</h3>
                <button onclick="document.getElementById('import_curriculum_modal').classList.add('hidden')" class="p-2 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('admin.subjects.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="input-label">Target Course</label>
                    <select name="course_id" required class="input-premium">
                        <option value="">Select course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="input-label">Import Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="pdf" checked class="peer sr-only">
                            <div class="p-4 border-2 border-slate-100 rounded-2xl text-center hover:border-violet-200 peer-checked:border-violet-600 peer-checked:bg-violet-50/50 transition-all">
                                <div class="h-10 w-10 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-800">GTU PDF</p>
                                <p class="text-[10px] text-slate-500">Auto-extract</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="csv" class="peer sr-only">
                            <div class="p-4 border-2 border-slate-100 rounded-2xl text-center hover:border-violet-200 peer-checked:border-violet-600 peer-checked:bg-violet-50/50 transition-all">
                                <div class="h-10 w-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-800">Custom CSV</p>
                                <p class="text-[10px] text-slate-500">Bulk upload</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="input-label">Select File</label>
                    <input type="file" name="file" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                </div>

                <div class="bg-blue-50 p-3 rounded-xl border border-blue-100">
                    <p class="text-[10px] text-blue-700 leading-relaxed">
                        <i class="bi bi-info-circle-fill"></i> <strong>GTU Parser:</strong> Extracts code, name, L-T-P, credits and marks from official GTU PDFs.<br>
                        <i class="bi bi-info-circle-fill"></i> <strong>CSV Format:</strong> Name, Code, Type, L, T, P, Credits, Int Marks, Ext Marks, Total, Semester.
                    </p>
                </div>

                <button type="submit" class="p-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-bold w-full hover:shadow-lg transition-all">
                    Start Processing
                </button>
            </form>
        </div>
    </div>
@endsection
