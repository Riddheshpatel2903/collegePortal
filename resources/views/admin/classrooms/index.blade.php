@extends('layouts.app')

@section('header_title', 'Spatial Nexus')

@section('content')
    <div x-data="{ 
        showAddModal: @entangle('showAddModal').defer ?? false, 
        editingRoom: null,
        showAssignModal: false,
        assignData: { course_id: '', year: '', course_name: '' }
    }" class="space-y-8">
        
        <x-page-header 
            title="Spatial Nexus" 
            subtitle="Coordinate physical learning environments, capacity metrics, and departmental room allocations."
            icon="bi-building-fill-gear"
            actionLabel="Integrate Room"
            actionIcon="bi-plus-lg"
            actionRoute="javascript:void(0)"
            @click="showAddModal = true; editingRoom = null"
        />

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- ─── Room Architecture Architecture ─── -->
            <div class="lg:col-span-8 space-y-6">
                <div class="glass-card overflow-hidden shadow-xl shadow-slate-200/50">
                    <div class="overflow-x-auto">
                        <table class="table-premium">
                            <thead>
                                <tr>
                                    <th>Room Identifier</th>
                                    <th>Modality</th>
                                    <th class="text-center">Capacity</th>
                                    <th>Allocation Status</th>
                                    <th class="text-right">Nexus Control</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($classrooms as $room)
                                    <tr class="group hover:bg-slate-50/50 transition-colors">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 text-indigo-600 flex items-center justify-center text-xs font-black shadow-sm border border-indigo-100 italic">
                                                    {{ $room->name }}
                                                </div>
                                                <span class="text-sm font-black text-slate-800 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $room->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full {{ $room->type === 'lecture' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-fuchsia-50 text-fuchsia-600 border-fuchsia-100' }} text-[9px] font-black uppercase tracking-widest border">
                                                {{ $room->type === 'lecture' ? 'Theoretical' : 'Practical' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs font-black text-slate-600">{{ $room->capacity }} Nodes</span>
                                        </td>
                                        <td>
                                            @if($room->course_id)
                                                <div class="flex items-center gap-2 group/status">
                                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                                                        {{ $room->course->name }} - Y{{ $room->year_number }}
                                                    </span>
                                                    <form action="{{ route('admin.classrooms.unassign', $room) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="h-6 w-6 rounded-md bg-white text-rose-300 hover:text-rose-600 hover:bg-rose-50 border border-slate-100 transition-all flex items-center justify-center opacity-0 group-hover/status:opacity-100">
                                                            <i class="bi bi-x-circle text-[10px]"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] italic">Available Node</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="flex justify-end gap-2 pr-4">
                                                <button @click="editingRoom = {{ json_encode($room) }}; showAddModal = true"
                                                    class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100">
                                                    <i class="bi bi-pencil-square text-sm"></i>
                                                </button>
                                                <form action="{{ route('admin.classrooms.destroy', $room) }}" method="POST"
                                                    onsubmit="return confirm('Purge this spatial node?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="h-9 w-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-rose-100">
                                                        <i class="bi bi-trash3-fill text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-20 text-center opacity-30">
                                            <div class="flex flex-col items-center">
                                                <i class="bi bi-geo text-5xl mb-4"></i>
                                                <p class="text-[11px] font-black uppercase tracking-widest">Spatial Nexus Virtualized</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ─── Batch Synchrony Analysis ─── -->
            <div class="lg:col-span-4 space-y-6">
                <div class="glass-card p-6 shadow-2xl relative overflow-hidden border-indigo-100">
                    <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                        <i class="bi bi-cpu-fill text-[8rem]"></i>
                    </div>
                    
                    <div class="relative z-10">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            Batch Synchrony Analysis
                        </h3>
                        
                        <div class="space-y-4 max-h-[640px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($batches as $batch)
                                <div class="group p-5 rounded-3xl border border-slate-100 bg-white hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-tight group-hover:text-indigo-600 transition-colors truncate">
                                                {{ $batch['course_name'] }}
                                            </h4>
                                            <span class="text-[9px] font-black text-indigo-500/50 uppercase tracking-widest">Phase: Year {{ $batch['year'] }}</span>
                                        </div>
                                        <button
                                            @click="assignData = { course_id: '{{ $batch['course_id'] }}', year: '{{ $batch['year'] }}', course_name: '{{ $batch['course_name'] }}' }; showAssignModal = true"
                                            class="px-2 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[8px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                            SYNC ROOM
                                        </button>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-end">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Integration Load</span>
                                            <span class="text-xs font-black text-slate-800 italic">{{ $batch['lecture_hours'] + $batch['lab_hours'] }} Hrs/Week</span>
                                        </div>
                                        
                                        <div class="flex gap-2">
                                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full bg-indigo-500" style="width: {{ ($batch['lecture_hours'] / ($batch['lecture_hours'] + $batch['lab_hours'] + 1)) * 100 }}%"></div>
                                            </div>
                                            <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full bg-fuchsia-500" style="width: {{ ($batch['lab_hours'] / ($batch['lecture_hours'] + $batch['lab_hours'] + 1)) * 100 }}%"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-4 pt-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="h-1.5 w-1.5 rounded-full bg-indigo-500"></div>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Theory: {{ $batch['lecture_hours'] }}h</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <div class="h-1.5 w-1.5 rounded-full bg-fuchsia-500"></div>
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Practical: {{ $batch['lab_hours'] }}h</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Integration Hubs (Modals) ─── -->
        <!-- Add/Edit Modal -->
        <div x-show="showAddModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" x-cloak>
            
            <div class="w-full max-w-lg bg-white rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden" @click.away="showAddModal = false">
                <div class="px-10 py-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight" x-text="editingRoom ? 'Modify Spatial Node' : 'Integrate Spatial Node'"></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Spatial Architecture Configuration</p>
                    </div>
                    <button @click="showAddModal = false" class="h-11 w-11 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <div class="p-10">
                    <form :action="editingRoom ? `/admin/classrooms/${editingRoom.id}` : '{{ route('admin.classrooms.store') }}'" method="POST" class="space-y-8">
                        @csrf
                        <template x-if="editingRoom">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-3 block italic">Node Identification</label>
                                <input type="text" name="name" :value="editingRoom ? editingRoom.name : ''"
                                    class="input-premium h-14" placeholder="e.g. CORE-101" required>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-3 block italic">Environment Type</label>
                                <select name="type" class="input-premium h-14" required>
                                    <option value="lecture" :selected="editingRoom && editingRoom.type === 'lecture'">Theoretical Hall</option>
                                    <option value="lab" :selected="editingRoom && editingRoom.type === 'lab'">Practical Laboratory</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-3 block italic">Occupancy Limit</label>
                                <input type="number" name="capacity" :value="editingRoom ? editingRoom.capacity : '60'"
                                    class="input-premium h-14" required min="1">
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 p-6 rounded-[2rem] border border-indigo-100/50">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-sm border border-indigo-200 shrink-0">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>
                                <div class="text-[10px] font-medium text-indigo-900/60 leading-relaxed uppercase tracking-tighter">
                                    Spatial nodes are fundamental to the <strong class="text-indigo-900 font-black">Nexus Engine</strong>. Precise capacity prevents allocation overlaps during automated generation cycles.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-gradient py-5 w-full text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20">
                            Execute Node Synchronisation
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div x-show="showAssignModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md" x-cloak>
            
            <div class="w-full max-w-lg bg-white rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden" @click.away="showAssignModal = false">
                <div class="px-10 py-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Allocate Spatial Node</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1" x-text="`Binding: ${assignData.course_name} - Phase ${assignData.year}`"></p>
                    </div>
                    <button @click="showAssignModal = false" class="h-11 w-11 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all flex items-center justify-center shadow-sm">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <div class="p-10">
                    <form action="{{ route('admin.classrooms.assign') }}" method="POST" class="space-y-8">
                        @csrf
                        <input type="hidden" name="course_id" :value="assignData.course_id">
                        <input type="hidden" name="year_number" :value="assignData.year">

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-3 block italic">Available Spatial Inventory</label>
                            <select name="classroom_id" class="input-premium h-14" required>
                                <option value="">Identify spatial node...</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }} ({{ strtoupper($room->type === 'lecture' ? 'Theory' : 'Practical') }}) - Cap: {{ $room->capacity }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-amber-50/50 p-6 rounded-[2rem] border border-amber-100/50 flex gap-4">
                            <div class="h-10 w-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-sm border border-amber-200 shrink-0">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="text-[10px] font-bold text-amber-900/60 leading-relaxed uppercase tracking-tighter">
                                Assigning a fixed room locks this spatial node for all cycles of this batch. Ensure the capacity aligns with the batch population node.
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-gradient py-5 w-full text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20">
                            Confirm Spatial Binding
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
```