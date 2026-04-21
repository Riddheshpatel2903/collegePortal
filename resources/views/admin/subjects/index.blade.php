@extends('layouts.app')

@section('header_title', 'Subject Management')

@section('content')
    <div x-data="{ showImport: false }">
        <x-page-header 
            title="Subject Management" 
            subtitle="Manage academic subjects, credit allocations, and faculty assignments."
            icon="bi-journal-bookmark"
        >
            <x-slot name="action">
                <div class="flex flex-wrap items-center gap-3">
                    <form action="{{ route('admin.subjects.delete-all') }}" method="POST" onsubmit="return confirm('Permanently delete ALL subjects? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-rose-600 hover:text-white transition-all border border-rose-100">
                            <i class="bi bi-trash3"></i> Purge All
                        </button>
                    </form>
                    
                    <button @click="showImport = true" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                        <i class="bi bi-file-earmark-arrow-up"></i> Bulk Import
                    </button>
                    
                    <a href="{{ route('admin.subjects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                        <i class="bi bi-plus-lg"></i> Add Subject
                    </a>
                </div>
            </x-slot>
        </x-page-header>

        <!-- ─── Subjects Table ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-4">Subject Info</th>
                            <th class="px-6 py-4">Academic Context</th>
                            <th class="px-6 py-4">Faculty Assigned</th>
                            <th class="px-6 py-4 text-center">Load / Credits</th>
                            <th class="px-6 py-4 text-center">Type</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($subjects as $sub)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-black border border-indigo-100">
                                            {{ strtoupper(substr($sub->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-slate-700 block group-hover:text-indigo-600 transition-colors">{{ $sub->name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: #{{ str_pad($sub->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700">{{ $sub->course->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-indigo-500 font-bold uppercase">Semester {{ (int) $sub->semester_sequence }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($sub->teacher)
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-[10px]">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <span class="text-xs text-slate-600 font-bold">{{ $sub->teacher->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 italic uppercase">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-bold uppercase border border-emerald-100">
                                        {{ (int) ($sub->weekly_hours ?? $sub->credits) }} Units
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($sub->is_lab ?? false)
                                        <span class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-600 rounded-md text-[10px] font-bold uppercase border border-amber-100">Lab/Practical</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-sky-50 text-sky-600 rounded-md text-[10px] font-bold uppercase border border-sky-100">Theory</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 pr-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.subjects.edit', $sub->id) }}" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('admin.subjects.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Delete this subject?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="h-8 w-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-24 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i class="bi bi-journal-x text-5xl mb-4"></i>
                                        <p class="text-[10px] font-bold uppercase tracking-widest">No Subjects Found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($subjects->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    {{ $subjects->links() }}
                </div>
            @endif
        </div>

        <!-- ─── Import Modal ─── -->
        <div x-show="showImport" 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showImport = false"></div>
            
            <div class="min-h-screen px-4 py-12 flex items-center justify-center">
                <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg relative z-10 overflow-hidden" @click.away="showImport = false">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Bulk Subject Import</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Import from PDF or CSV</p>
                        </div>
                        <button @click="showImport = false" class="h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    
                    <div class="p-8">
                        <form method="POST" action="{{ route('admin.subjects.import') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Select Program</label>
                                <select name="course_id" required class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0">
                                    <option value="">Choose Course...</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="type" value="pdf" checked class="sr-only peer">
                                    <div class="p-4 border-2 border-slate-100 rounded-2xl text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                        <i class="bi bi-file-earmark-pdf text-xl text-indigo-400 mb-2 block group-hover:scale-110 transition-transform"></i>
                                        <span class="text-xs font-bold text-slate-700">GTU PDF</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="type" value="csv" class="sr-only peer">
                                    <div class="p-4 border-2 border-slate-100 rounded-2xl text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all">
                                        <i class="bi bi-file-earmark-spreadsheet text-xl text-indigo-400 mb-2 block group-hover:scale-110 transition-transform"></i>
                                        <span class="text-xs font-bold text-slate-700">Custom CSV</span>
                                    </div>
                                </label>
                            </div>

                            <div class="relative group">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Source File</label>
                                <div class="h-24 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center group-hover:border-indigo-300 transition-colors relative">
                                    <i class="bi bi-cloud-arrow-up text-2xl text-slate-300 group-hover:text-indigo-400"></i>
                                    <span class="text-[10px] font-bold text-slate-400 mt-2">Click or drag to upload</span>
                                    <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 flex gap-3">
                                <i class="bi bi-info-circle text-indigo-600 text-sm"></i>
                                <p class="text-[10px] text-indigo-900/60 font-medium leading-relaxed">
                                    Parser automatically extracts subjects and credits. CSV must include: Name, Code, Type, Credits, Semester.
                                </p>
                            </div>

                            <button type="submit" class="w-full py-3 bg-indigo-600 text-white text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                                Import Curriculum
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
