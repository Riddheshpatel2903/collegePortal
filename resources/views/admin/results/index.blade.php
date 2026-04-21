@extends('layouts.app')

@section('header_title', 'Result Management')

@section('content')
    <x-page-header 
        title="Examination Results" 
        subtitle="Manage student academic outcomes, SPI/CPI tracking, and examination record synchronization." 
        icon="bi-trophy"
    />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-8">
        <!-- ─── Import Section ─── -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm sticky top-24">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Data Import</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Bulk Upload Results</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.results.import') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Examination Term</label>
                        <select name="semester_number" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-11" required>
                            <option value="">Select Semester...</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" @selected(old('semester_number') == $i)>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Result Manifest</label>
                        <div class="relative group">
                            <input type="file" name="result_file" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept=".csv,.xlsx,.pdf" required
                                onchange="this.nextElementSibling.querySelector('.file-label').textContent = this.files[0].name">
                            <div class="h-24 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center gap-2 group-hover:border-indigo-300 transition-all">
                                <i class="bi bi-file-earmark-arrow-up text-xl text-slate-300 group-hover:text-indigo-400"></i>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest file-label px-4 text-center">Click to Upload</span>
                            </div>
                        </div>
                    </div>

                    <button class="w-full py-3 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                        Record & Lock
                    </button>
                    
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 mt-2">
                        <p class="text-[9px] text-amber-900/60 font-medium leading-relaxed">
                            <i class="bi bi-exclamation-triangle mr-1"></i> Critical: Ensure student GTU enrollment numbers match the records in the portal before final sync.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- ─── Registry Section ─── -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Search Registry</label>
                        <div class="relative group h-11">
                            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                class="w-full h-full bg-slate-50 border-slate-100 rounded-xl pl-11 text-sm font-medium focus:border-indigo-500 focus:ring-0"
                                placeholder="Name, ID, or Enrollment...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Filter Program</label>
                        <select name="course_id" class="w-full bg-slate-50 border-slate-100 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-11 text-slate-600">
                            <option value="">All Branches</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="h-11 bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-slate-900 transition-all shadow-md">
                        Update View
                    </button>
                </form>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="px-6 py-4">Student Profile</th>
                                <th class="px-6 py-4">Enrollment</th>
                                <th class="px-6 py-4">SPI / CPI</th>
                                <th class="px-6 py-4">Backlogs</th>
                                <th class="px-6 py-4 text-center">Result Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($results as $result)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700 leading-tight">{{ $result->student?->user?->name ?? 'Unknown' }}</span>
                                            <span class="text-[9px] text-indigo-500 font-bold uppercase tracking-widest mt-1">Semester {{ $result->semester_number }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-slate-500 tracking-tight font-mono">{{ $result->student?->gtu_enrollment_no ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <span class="text-[8px] font-bold text-slate-400 uppercase block leading-none mb-1">SPI</span>
                                                <span class="text-sm font-black text-indigo-600 tracking-tight">{{ number_format((float) $result->sgpa, 2) }}</span>
                                            </div>
                                            <div class="w-px h-6 bg-slate-100"></div>
                                            <div>
                                                <span class="text-[8px] font-bold text-slate-400 uppercase block leading-none mb-1">CPI</span>
                                                <span class="text-sm font-black text-slate-900 tracking-tight">{{ number_format((float) $result->cgpa, 2) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $result->backlog_subjects > 0 ? 'text-rose-600 bg-rose-50 border-rose-100' : 'text-slate-400 bg-slate-50 border-slate-100' }}">
                                            {{ $result->backlog_subjects }} BL
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider border {{ $result->result_status === 'pass' ? 'text-emerald-700 bg-emerald-50 border-emerald-100' : ($result->result_status === 'pending' ? 'text-amber-700 bg-amber-50 border-amber-100' : 'text-rose-700 bg-rose-50 border-rose-100') }}">
                                            {{ $result->result_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                            @if(!$result->locked_at)
                                                <form method="POST" action="{{ route('admin.results.lock', $result) }}" class="inline">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition-all shadow-indigo-100">Lock</button>
                                                </form>
                                            @else
                                                <div class="h-8 w-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center border border-slate-200" title="Record Locked">
                                                    <i class="bi bi-lock-fill"></i>
                                                </div>
                                            @endif
                                            
                                            @if($result->student?->gtu_enrollment_no)
                                                <a class="h-8 w-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm"
                                                    href="https://www.students.gtu.ac.in/Default.aspx?enrollmentno={{ urlencode($result->student->gtu_enrollment_no) }}"
                                                    target="_blank" rel="noopener" title="Verify on GTU Portal">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-24 text-center">
                                        <div class="flex flex-col items-center opacity-30">
                                            <i class="bi bi-journal-x text-5xl mb-4"></i>
                                            <p class="text-[10px] font-bold uppercase tracking-widest">No Results Found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($results->hasPages())
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                        {{ $results->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
