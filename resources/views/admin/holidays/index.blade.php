@extends('layouts.app')

@section('header_title', 'Holiday Management')

@section('content')
    <div x-data="{ showModal: false, isEdit: false, holiday: { id: '', name: '', date: '', description: '', is_recurring: false }, action: '' }">
        <x-page-header 
            title="Holiday Management" 
            subtitle="Manage institutional holidays and academic calendar events."
            icon="bi-calendar-event"
        >
            <x-slot name="action">
                <div class="flex items-center gap-3">
                    <form action="{{ route('admin.holidays.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="csv_file" class="hidden" id="csv_file" onchange="this.form.submit()">
                        <button type="button" onclick="document.getElementById('csv_file').click()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                            <i class="bi bi-upload"></i> Import CSV
                        </button>
                    </form>
                    <button @click="showModal = true; isEdit = false; holiday = { id: '', name: '', date: '', description: '', is_recurring: false }; action = '{{ route('admin.holidays.store') }}'" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md">
                        <i class="bi bi-plus-lg"></i> Add Holiday
                    </button>
                </div>
            </x-slot>
        </x-page-header>

        <!-- ─── Holiday List ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                        <th class="px-6 py-4">Holiday Name</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Frequency</th>
                        <th class="px-6 py-4">Context / Description</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($holidays as $h)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4 font-bold text-slate-700">{{ $h->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                                    <i class="bi bi-calendar-check text-indigo-500"></i>
                                    {{ $h->date->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($h->is_recurring)
                                    <span class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-600 rounded-md text-[9px] font-bold uppercase border border-indigo-100 italic">Recurring Yearly</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 bg-slate-50 text-slate-500 rounded-md text-[9px] font-bold uppercase border border-slate-100">One-time Event</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 italic font-medium">
                                {{ $h->description ?? 'No additional context provided' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <button type="button" @click="showModal = true; isEdit = true; holiday = { id: '{{ $h->id }}', name: '{{ addslashes($h->name) }}', date: '{{ $h->date->format('Y-m-d') }}', description: '{{ addslashes($h->description) }}', is_recurring: {{ $h->is_recurring ? 'true' : 'false' }} }; action = '{{ route('admin.holidays.update', $h->id) }}'" class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center shadow-sm border border-indigo-100">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form action="{{ route('admin.holidays.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Delete this holiday?')">
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
                                    <i class="bi bi-calendar-x text-5xl mb-4"></i>
                                    <p class="text-[10px] font-bold uppercase tracking-widest">No Holidays Logged</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ─── Modal ─── -->
        <div x-show="showModal" 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">
            
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
            
            <div class="min-h-screen px-4 py-8 flex items-center justify-center">
                <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg relative z-10 overflow-hidden" @click.away="showModal = false">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800" x-text="isEdit ? 'Update Holiday' : 'Register Holiday'"></h3>
                        <button @click="showModal = false" class="text-slate-400 hover:bg-slate-100 h-8 w-8 rounded-lg flex items-center justify-center transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form :action="action" method="POST" class="p-8 space-y-6">
                        @csrf
                        <template x-if="isEdit">
                            @method('PUT')
                        </template>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Event Name</label>
                            <input type="text" name="name" x-model="holiday.name" required class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0" placeholder="e.g., Independence Day">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Date</label>
                                <input type="date" name="date" x-model="holiday.date" required class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Frequency</label>
                                <label class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer group hover:bg-indigo-50/50 transition-colors">
                                    <input type="hidden" name="is_recurring" value="0">
                                    <input type="checkbox" name="is_recurring" value="1" x-model="holiday.is_recurring" class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600">Recurring Yearly</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 ml-1">Notes (Optional)</label>
                            <textarea name="description" x-model="holiday.description" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 p-4" placeholder="Brief context about this holiday..."></textarea>
                        </div>

                        <div class="flex gap-4 pt-4 border-t border-slate-100">
                            <button type="button" @click="showModal = false" class="flex-1 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100" x-text="isEdit ? 'Update Event' : 'Register Event'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
