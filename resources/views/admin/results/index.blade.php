@extends('layouts.app')

@section('header_title', 'Results')

@section('content')
    <x-page-header 
        title="Results Nexus" 
        subtitle="GTU Examination outcomes and global registry" 
        tag="Academic Sync"
        icon="bi-trophy"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Import Panel --}}
        <div class="lg:col-span-1">
            <div class="glass-card p-8 border-l-4 border-l-violet-500 sticky top-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">GTU Batch Import</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-0.5">Automated Result Injection</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.results.import') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label class="input-label">Academic Semester</label>
                        <select name="semester_number" class="input-premium h-12" required>
                            <option value="">Select Target Term</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" @selected(old('semester_number') == $i)>Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="input-label">Result Manifest (CSV/XLSX/PDF)</label>
                        <div class="relative group">
                            <input type="file" name="result_file" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept=".csv,.xlsx,.pdf" required
                                onchange="this.nextElementSibling.querySelector('.file-label').textContent = this.files[0].name">
                            <div class="input-premium h-24 flex flex-col items-center justify-center gap-2 group-hover:border-violet-300 transition-all border-dashed">
                                <i class="bi bi-file-earmark-arrow-up text-2xl text-slate-300 group-focus-within:text-violet-500"></i>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest file-label text-center px-4">Select or Drop Manifest</span>
                            </div>
                        </div>
                    </div>

                    <button class="w-full py-4 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:shadow-xl hover:shadow-violet-500/20 active:scale-[0.98] transition-all">
                        Synchronize & Lock Results
                    </button>
                </form>
            </div>
        </div>

        {{-- Global Registry --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-card p-6 border border-white/60">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2">
                        <label class="input-label text-[10px]">Registry Search</label>
                        <div class="relative group">
                            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="input-premium pl-10 h-11"
                            placeholder="Student ID, Name, GTU Enrollment...">
                        </div>
                    </div>
                    <div>
                        <label class="input-label text-[10px]">Filter Course</label>
                        <select name="course_id" class="input-premium h-11">
                            <option value="">All Branches</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected((int) request('course_id') === (int) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="input-label text-[10px]">Semester</label>
                        <select name="semester_number" class="input-premium h-11">
                            <option value="">All Terms</option>
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" @selected((int) request('semester_number') === $i)>Sem {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="lg:col-span-2 flex items-end">
                        <button class="w-full h-11 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">
                            Execute Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="glass-card overflow-hidden">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Student Scholar</th>
                            <th>GTU Enrollment</th>
                            <th>Term</th>
                            <th>SPI</th>
                            <th>CPI</th>
                            <th>Backlogs</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800">{{ $result->student?->user?->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">ID: #{{ $result->student?->id }}</span>
                                    </div>
                                </td>
                                <td><span class="text-xs font-bold text-slate-600 tracking-tighter">{{ $result->student?->gtu_enrollment_no ?? '-' }}</span></td>
                                <td><span class="text-[10px] font-black text-violet-600 uppercase tracking-widest">Sem {{ $result->semester_number }}</span></td>
                                <td><span class="text-sm font-black text-slate-700 tracking-tight">{{ number_format((float) $result->sgpa, 2) }}</span></td>
                                <td><span class="text-sm font-black text-slate-900 tracking-tight">{{ number_format((float) $result->cgpa, 2) }}</span></td>
                                <td>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black {{ $result->backlog_subjects > 0 ? 'text-rose-600 bg-rose-50' : 'text-slate-400 bg-slate-50' }}">
                                        {{ $result->backlog_subjects }} BL
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $result->result_status === 'pass' ? 'text-emerald-700 bg-emerald-100' : ($result->result_status === 'pending' ? 'text-amber-700 bg-amber-50' : 'text-rose-700 bg-rose-50') }}">
                                        {{ $result->result_status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if(!$result->locked_at)
                                            <form method="POST" action="{{ route('admin.results.lock', $result) }}" class="inline">
                                                @csrf
                                                <button class="h-8 px-3 rounded-lg bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">Lock</button>
                                            </form>
                                        @endif
                                        @if($result->student?->gtu_enrollment_no)
                                            <a class="h-8 px-3 rounded-lg bg-white border border-slate-200 text-slate-400 text-[9px] font-black uppercase tracking-widest hover:text-violet-600 hover:border-violet-200 flex items-center transition-all"
                                                href="https://www.students.gtu.ac.in/Default.aspx?enrollmentno={{ urlencode($result->student->gtu_enrollment_no) }}"
                                                target="_blank" rel="noopener">GTU Portal</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-20 text-slate-400 font-bold uppercase tracking-widest text-[10px]">Registry is currently empty</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $results->links() }}
            </div>
        </div>
    </div>
@endsection

