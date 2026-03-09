@extends('layouts.app')

@section('header_title', 'Room Management')

@section('content')
    <div class="space-y-6" x-data="{ 
        showAddModal: false, 
        editingRoom: null,
        showAssignModal: false,
        assignData: { course_id: '', year: '', course_name: '' }
    }">
        <!-- Top Actions -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-slate-800">Classrooms</h2>
                <p class="text-sm text-slate-500">Manage rooms and batch assignments.</p>
            </div>
            <button @click="showAddModal = true; editingRoom = null" class="btn-premium">
                <i class="bi bi-plus-lg mr-2"></i> Add Room
            </button>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Room List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="glass-card overflow-hidden">
                    <table class="table-premium">
                        <thead>
                            <tr>
                                <th>Room Name</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Status / Assigned To</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classrooms as $room)
                                <tr>
                                    <td>
                                        <div class="font-bold text-slate-800">{{ $room->name }}</div>
                                    </td>
                                    <td>
                                        <span class="{{ $room->type === 'lecture' ? 'badge-indigo' : 'badge-fuchsia' }}">
                                            {{ ucfirst($room->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $room->capacity }}</td>
                                    <td>
                                        @if($room->course_id)
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold text-slate-600">
                                                    {{ $room->course->name }} - Y{{ $room->year_number }}
                                                </span>
                                                <form action="{{ route('admin.classrooms.unassign', $room) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic text-xs">Available</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="editingRoom = {{ json_encode($room) }}; showAddModal = true"
                                                class="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('admin.classrooms.destroy', $room) }}" method="POST"
                                                onsubmit="return confirm('Are you sure?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Batch Requirements Analysis -->
            <div class="glass-card p-5">
                <h3 class="font-black text-slate-800 mb-4 border-b pb-2">Batch Analysis</h3>
                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                    @foreach($batches as $batch)
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-black text-slate-700">{{ $batch['course_name'] }}</h4>
                                <button
                                    @click="assignData = { course_id: '{{ $batch['course_id'] }}', year: '{{ $batch['year'] }}', course_name: '{{ $batch['course_name'] }}' }; showAssignModal = true"
                                    class="text-[10px] font-black text-violet-600 hover:underline">
                                    ASSIGN ROOM
                                </button>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-500">Year {{ $batch['year'] }}</span>
                                    <span class="font-bold text-slate-700">{{ $batch['lecture_hours'] + $batch['lab_hours'] }}
                                        Hrs Req.</span>
                                </div>
                                <div class="flex gap-2">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold">L:
                                        {{ $batch['lecture_hours'] }}h</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-fuchsia-100 text-fuchsia-700 font-bold">P:
                                        {{ $batch['lab_hours'] }}h</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Add/Edit Room Modal -->
        <div x-show="showAddModal" x-transition
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div class="glass-card w-full max-w-md p-8 bg-white" @click.away="showAddModal = false">
                <h3 class="text-2xl font-black text-slate-800 mb-6" x-text="editingRoom ? 'Edit Room' : 'Add New Room'">
                </h3>
                <form
                    :action="editingRoom ? `/admin/classrooms/${editingRoom.id}` : '{{ route('admin.classrooms.store') }}'"
                    method="POST" class="space-y-5">
                    @csrf
                    <template x-if="editingRoom">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Room
                            Name</label>
                        <input type="text" name="name" :value="editingRoom ? editingRoom.name : ''"
                            class="input-premium w-full" placeholder="e.g. L-101" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Room
                            Type</label>
                        <select name="type" class="input-premium w-full" required>
                            <option value="lecture" :selected="editingRoom && editingRoom.type === 'lecture'">Lecture Hall
                            </option>
                            <option value="lab" :selected="editingRoom && editingRoom.type === 'lab'">Laboratory</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Capacity</label>
                        <input type="number" name="capacity" :value="editingRoom ? editingRoom.capacity : '60'"
                            class="input-premium w-full" required min="1">
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showAddModal = false" class="flex-1 btn-secondary">Cancel</button>
                        <button type="submit" class="flex-1 btn-premium"
                            x-text="editingRoom ? 'Update Room' : 'Create Room'"></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div x-show="showAssignModal" x-transition
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div class="glass-card w-full max-w-md p-8 bg-white" @click.away="showAssignModal = false">
                <h3 class="text-2xl font-black text-slate-800 mb-2">Assign Room</h3>
                <p class="text-sm text-slate-500 mb-6"
                    x-text="`Target: ${assignData.course_name} - Year ${assignData.year}`"></p>

                <form action="{{ route('admin.classrooms.assign') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="course_id" :value="assignData.course_id">
                    <input type="hidden" name="year_number" :value="assignData.year">

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Select
                            Classroom</label>
                        <select name="classroom_id" class="input-premium w-full" required>
                            <option value="">Choose an available room...</option>
                            @foreach($classrooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ strtoupper($room->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showAssignModal = false" class="flex-1 btn-secondary">Cancel</button>
                        <button type="submit" class="flex-1 btn-premium">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .badge-indigo {
            @apply px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-wider border border-indigo-100;
        }

        .badge-fuchsia {
            @apply px-2 py-0.5 rounded-full bg-fuchsia-50 text-fuchsia-600 text-[10px] font-black uppercase tracking-wider border border-fuchsia-100;
        }
    </style>
@endsection