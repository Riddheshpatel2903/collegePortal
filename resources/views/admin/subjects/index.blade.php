@extends('layouts.app')

@section('header_title', 'Curriculum Nexus')

@section('content')
    <x-page-header 
        title="Curriculum Nexus" 
        subtitle="Manage academic subjects, credit allocations, and specialized faculty assignments."
        icon="bi-journal-bookmark-fill"
        actionIcon="bi-plus-lg"
        actionLabel="Integrate Subject"
        actionRoute="{{ route('admin.subjects.create') }}"
    >
        <x-slot name="action">
            <div class="flex items-center gap-2">
                <form action="{{ route('admin.subjects.delete-all') }}" method="POST" onsubmit="return confirm('Purge ALL curriculum data? This action is irreversible.')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-50 text-rose-600 text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-600 hover:text-white transition-all border border-rose-100">
                        <i class="bi bi-trash3"></i> Purge Nexus
                    </button>
                </form>
                <button onclick="document.getElementById('import_curriculum_modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                    <i class="bi bi-file-earmark-arrow-up"></i> Bulk Import
                </button>
                <x-button href="{{ route('admin.subjects.create') }}" icon="bi-plus-lg" variant="primary-gradient" class="text-[11px] font-black uppercase tracking-widest">
                    Add Subject
                </x-button>
            </div>
        </x-slot>
    </x-page-header>

    <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Subject Identifier</th>
                        <th>Academic Context</th>
                        <th>Faculty Lead</th>
                        <th class="text-center">Weekly Load</th>
                        <th class="text-center">Modality</th>
                        <th class="text-right">Nexus Control</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $sub)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 flex items-center justify-center text-xs font-black shadow-sm border border-indigo-100">
                                        {{ strtoupper(substr($sub->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="text-sm font-black text-slate-800 block group-hover:text-indigo-600 transition-colors">{{ $sub->name }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: #{{ str_pad($sub->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-md text-[10px] font-black uppercase tracking-tighter w-fit border border-indigo-100 mb-1">{{ $sub->course->name ?? 'N/A' }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">Semester {{ (int) $sub->semester_sequence }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] ring-2 ring-white">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <span class="text-xs text-slate-700 font-bold group-hover:text-slate-900 transition-colors">{{ $sub->teacher->user->name ?? 'Unassigned' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                    {{ (int) ($sub->weekly_hours ?? $sub->credits) }} Hrs / Cycle
                                </span>
                            </td>
                            <td class="text-center">
                                @if($sub->is_lab ?? false)
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100">Practical</span>
                                @else
                                    <span class="px-3 py-1 bg-sky-50 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-100">Theoretical</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 pr-2">
                                    <a href="{{ route('admin.subjects.edit', $sub->id) }}"
                                        class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100"><i
                                            class="bi bi-pencil-square text-sm"></i></a>
                                    <form action="{{ route('admin.subjects.destroy', $sub->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-9 w-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100"><i
                                                class="bi bi-trash3-fill text-sm"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center opacity-30">
                                <div class="flex flex-col items-center">
                                    <i class="bi bi-journal-x text-5xl mb-4"></i>
                                    <p class="text-[11px] font-black uppercase tracking-widest">Nexus Curriculum Clear</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            {{ $subjects->links() }}
        </div>
    </div>

    <!-- ─── Import Curriculum Modal ─── -->
    <div id="import_curriculum_modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" onclick="document.getElementById('import_curriculum_modal').classList.add('hidden')"></div>
        
        <div class="min-h-screen px-4 py-8 flex items-center justify-center relative pointer-events-none">
            <div class="w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden pointer-events-auto">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Bulk Curriculum Integration</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Automated Data Sync</p>
                    </div>
                    <button onclick="document.getElementById('import_curriculum_modal').classList.add('hidden')" class="h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-slate-900 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <div class="p-8">
                    <form method="POST" action="{{ route('admin.subjects.import') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Target Course Program</label>
                            <select name="course_id" required class="input-premium h-14">
                                <option value="">Identify Program...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-3 block">Integration Modality</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="type" value="pdf" checked class="peer sr-only">
                                    <div class="p-5 border-2 border-slate-100 rounded-3xl text-center group-hover:border-indigo-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/30 transition-all shadow-sm">
                                        <div class="h-12 w-12 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="bi bi-file-earmark-pdf text-xl"></i>
                                        </div>
                                        <p class="text-sm font-black text-slate-800">GTU PDF</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Auto-Parser</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="type" value="csv" class="peer sr-only">
                                    <div class="p-5 border-2 border-slate-100 rounded-3xl text-center group-hover:border-violet-200 peer-checked:border-violet-600 peer-checked:bg-violet-50/30 transition-all shadow-sm">
                                        <div class="h-12 w-12 bg-violet-100 text-violet-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="bi bi-file-earmark-spreadsheet text-xl"></i>
                                        </div>
                                        <p class="text-sm font-black text-slate-800">Custom CSV</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Batch Upload</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Source Document</label>
                            <div class="relative h-14 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex items-center px-5 group hover:border-indigo-300 transition-colors">
                                <i class="bi bi-cloud-arrow-up text-indigo-400 mr-3 text-lg"></i>
                                <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer">
                                <span class="text-xs font-bold text-slate-400 group-hover:text-slate-600 transition-colors">Select PDF or CSV source...</span>
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 p-5 rounded-3xl border border-indigo-100/50 flex gap-4">
                            <div class="h-8 w-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <div class="text-[10px] text-indigo-900/60 leading-relaxed font-medium">
                                <strong class="text-indigo-900 block mb-1 uppercase tracking-widest font-black text-[9px]">Integration Intelligence:</strong>
                                <i class="bi bi-dot"></i> GTU Parser extracts L-T-P, credits and assessment metrics.<br>
                                <i class="bi bi-dot"></i> CSV structure: Name, Code, Type, L, T, P, Credits, Marks, Semester.
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-gradient py-4 w-full text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20">
                            Execute Integration Cycle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
