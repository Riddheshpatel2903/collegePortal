@extends('layouts.app')

@section('header_title', 'Classroom Management')

@section('content')
    <div x-data="{ 
        showAddModal: @entangle('showAddModal').defer ?? false, 
        editingRoom: null,
        showAssignModal: false,
        assignData: { course_id: '', year: '', course_name: '' }
    }" class="space-y-8">
        
        <x-page-header 
            title="Classroom Management" 
            subtitle="Manage physical learning environments, room capacities, and batch-wise allocations."
            icon="bi-building"
        >
            <x-slot name="action">
                <button @click="showAddModal = true; editingRoom = null" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                    <i class="bi bi-plus-lg"></i> Add Classroom
                </button>
            </x-slot>
        </x-page-header>

        <div class="grid lg:grid-cols-12 gap-8 mt-8">
            <!-- ─── Classrooms Table ─── -->
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-6 py-4">Room Name</th>
                                    <th class="px-6 py-4">Type</th>
                                    <th class="px-6 py-4 text-center">Capacity</th>
                                    <th class="px-6 py-4">Current Allocation</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($classrooms as $room)
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-100 uppercase italic">
                                                    {{ $room->name }}
                                                </div>
                                                <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $room->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($room->type === 'lecture')
                                                <span class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-600 rounded-md text-[9px] font-bold uppercase border border-indigo-100">Lecture Hall</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-600 rounded-md text-[9px] font-bold uppercase border border-amber-100">Laboratory</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-xs font-bold text-slate-600">{{ $room->capacity }} Students</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($room->course_id)
                                                <div class="flex items-center gap-2 group/status">
                                                    <span class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-600 rounded-md text-[9px] font-bold uppercase border border-emerald-100">
                                                        {{ $room->course->name }} - Y{{ $room->year_number }}
                                                    </span>
                                                    <form action="{{ route('admin.classrooms.unassign', $room) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="h-6 w-6 rounded-md bg-white text-rose-300 hover:text-rose-600 hover:bg-rose-50 border border-slate-100 transition-all flex items-center justify-center opacity-0 group-hover/status:opacity-100" title="Remove Allocation">
                                                            <i class="bi bi-x-circle text-[10px]"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest italic">Available</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2 pr-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                                <button @click="editingRoom = {{ json_encode($room) }}; showAddModal = true" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('admin.classrooms.destroy', $room) }}" method="POST" onsubmit="return confirm('Delete this classroom permanently?')">
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
                                        <td colspan="5" class="py-24 text-center">
                                            <div class="flex flex-col items-center opacity-30">
                                                <i class="bi bi-geo text-5xl mb-4"></i>
                                                <p class="text-[10px] font-bold uppercase tracking-widest">No Classrooms Found</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ─── Batch Load Analysis ─── -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Batch Course Load Analysis
                    </h3>
                    
                    <div class="space-y-4 max-h-[640px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($batches as $batch)
                            <div class="group p-5 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-white hover:border-indigo-100 hover:shadow-lg transition-all duration-300">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="min-w-0 pr-4">
                                        <h4 class="text-sm font-bold text-slate-800 truncate group-hover:text-indigo-600 transition-colors">
                                            {{ $batch['course_name'] }}
                                        </h4>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Year {{ $batch['year'] }}</span>
                                    </div>
                                    <button
                                        @click="assignData = { course_id: '{{ $batch['course_id'] }}', year: '{{ $batch['year'] }}', course_name: '{{ $batch['course_name'] }}' }; showAssignModal = true"
                                        class="shrink-0 px-2 py-1 bg-white border border-slate-200 text-indigo-600 rounded-lg text-[8px] font-bold uppercase tracking-widest hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all shadow-sm">
                                        Allocate Room
                                    </button>
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="flex justify-between items-end">
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Weekly Load</span>
                                        <span class="text-xs font-bold text-slate-600 italic">{{ $batch['lecture_hours'] + $batch['lab_hours'] }} Hours</span>
                                    </div>
                                    
                                    <div class="flex gap-1.5">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ ($batch['lecture_hours'] / max(1, ($batch['lecture_hours'] + $batch['lab_hours']))) * 100 }}%"></div>
                                        </div>
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ ($batch['lab_hours'] / max(1, ($batch['lecture_hours'] + $batch['lab_hours']))) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 pt-1">
                                        <div class="flex items-center gap-1.5">
                                            <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Theory: {{ $batch['lecture_hours'] }}h</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Practical: {{ $batch['lab_hours'] }}h</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Modals ─── -->
        <!-- Add/Edit Modal -->
        <div x-show="showAddModal" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100"
            style="display: none;">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAddModal = false"></div>
            
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg relative z-10 overflow-hidden" @click.away="showAddModal = false">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 uppercase tracking-tight" x-text="editingRoom ? 'Update Classroom' : 'Register Classroom'"></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Physical Space Configuration</p>
                    </div>
                    <button @click="showAddModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-8">
                    <form :action="editingRoom ? `/admin/classrooms/${editingRoom.id}` : '{{ route('admin.classrooms.store') }}'" method="POST" class="space-y-6">
                        @csrf
                        <template x-if="editingRoom">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Room Identifier</label>
                            <input type="text" name="name" :value="editingRoom ? editingRoom.name : ''"
                                class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0" placeholder="e.g. CORE-101" required>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Room Type</label>
                                <select name="type" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 text-slate-600" required>
                                    <option value="lecture" :selected="editingRoom && editingRoom.type === 'lecture'">Lecture Hall</option>
                                    <option value="lab" :selected="editingRoom && editingRoom.type === 'lab'">Laboratory</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Max Capacity</label>
                                <input type="number" name="capacity" :value="editingRoom ? editingRoom.capacity : '60'"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0" required min="1">
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 flex gap-3">
                            <i class="bi bi-info-circle text-indigo-600 text-sm"></i>
                            <p class="text-[10px] text-indigo-900/60 font-medium leading-relaxed uppercase tracking-tighter">
                                Accurate room capacity is critical for automated timetable generation to prevent student overflow.
                            </p>
                        </div>

                        <button type="submit" class="w-full py-4 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            Save Classroom Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Allocation Modal -->
        <div x-show="showAssignModal" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100"
            style="display: none;">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAssignModal = false"></div>
            
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg relative z-10 overflow-hidden" @click.away="showAssignModal = false">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Allocate Space</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5" x-text="`Assigning for: ${assignData.course_name} - Year ${assignData.year}`"></p>
                    </div>
                    <button @click="showAssignModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-100 flex items-center justify-center transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-8">
                    <form action="{{ route('admin.classrooms.assign') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="course_id" :value="assignData.course_id">
                        <input type="hidden" name="year_number" :value="assignData.year">

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Select Available Room</label>
                            <select name="classroom_id" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 text-slate-600" required>
                                <option value="">Choose a classroom...</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }} ({{ strtoupper($room->type === 'lecture' ? 'Theory' : 'Lab') }}) - Capacity: {{ $room->capacity }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 flex gap-3">
                            <i class="bi bi-exclamation-triangle-fill text-amber-600 text-sm"></i>
                            <p class="text-[10px] font-bold text-amber-900/60 leading-relaxed uppercase tracking-tighter">
                                Allocating a fixed room will reserve this space exclusively for this batch across all academic cycles.
                            </p>
                        </div>

                        <button type="submit" class="w-full py-4 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100">
                            Confirm Space Allocation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection