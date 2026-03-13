@extends('layouts.app')

@section('header_title', 'Holiday Management')

@section('content')
    <div class="space-y-8" x-data="{ showModal: false, isEdit: false, holiday: { id: '', name: '', date: '', description: '', is_recurring: false }, action: '' }">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-calendar-event mr-1"></i> Global Calendar
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Holidays <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-orange-600">Nexus</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium">Manage institutional holidays and calendar events.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.holidays.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="csv_file" class="hidden" id="csv_file" onchange="this.form.submit()">
                    <x-button type="button" variant="outline" onclick="document.getElementById('csv_file').click()" icon="bi-upload">
                        Import CSV
                    </x-button>
                </form>
                <x-button variant="primary" @click="showModal = true; isEdit = false; holiday = { id: '', name: '', date: '', description: '', is_recurring: false }; action = '{{ route('admin.holidays.store') }}'" icon="bi-plus-lg">
                    Add Holiday
                </x-button>
            </div>
        </div>

        {{-- Holiday List --}}
        <x-card class="overflow-hidden !p-0">
            <x-table :headers="['Holiday Name', 'Date', 'Type', 'Description', 'Actions']">
                @forelse($holidays as $h)
                    <tr>
                        <td class="font-bold text-slate-800">{{ $h->name }}</td>
                        <td class="text-sm text-slate-600 font-medium">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-calendar-check text-violet-500"></i>
                                {{ $h->date->format('M d, Y') }}
                            </div>
                        </td>
                        <td>
                            @if($h->is_recurring)
                                <x-badge type="info">Recurring Yearly</x-badge>
                            @else
                                <x-badge type="secondary">One-time</x-badge>
                            @endif
                        </td>
                        <td class="text-sm text-slate-400 italic">
                            {{ $h->description ?? 'No description' }}
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="p-2 text-slate-400 hover:text-violet-600 transition-colors"
                                    @click="showModal = true; isEdit = true; holiday = { id: '{{ $h->id }}', name: '{{ addslashes($h->name) }}', date: '{{ $h->date->format('Y-m-d') }}', description: '{{ addslashes($h->description) }}', is_recurring: {{ $h->is_recurring ? 'true' : 'false' }} }; action = '{{ route('admin.holidays.update', $h->id) }}'">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('admin.holidays.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Delete this holiday?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 transition-colors">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            No holidays found. Add your first holiday to get started.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>

        {{-- Modal --}}
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showModal = false"></div>
            <x-card class="relative w-full max-w-lg shadow-2xl animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-900" x-text="isEdit ? 'Edit Holiday' : 'Add New Holiday'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg"></i></button>
                </div>

                <form :action="action" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        @method('PUT')
                    </template>

                    <x-input label="Holiday Name" name="name" x-model="holiday.name" required placeholder="e.g., Independence Day" />
                    
                    <div class="grid grid-cols-2 gap-4">
                        <x-input type="date" label="Date" name="date" x-model="holiday.date" required />
                        <div class="flex flex-col">
                            <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Recurring Event</label>
                            <label class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-xl cursor-pointer">
                                <input type="hidden" name="is_recurring" value="0">
                                <input type="checkbox" name="is_recurring" value="1" x-model="holiday.is_recurring" class="h-5 w-5 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                                <span class="text-sm font-bold text-slate-700">Repeats Yearly</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Description (Optional)</label>
                        <textarea name="description" x-model="holiday.description" rows="3" class="input-premium p-4 min-h-[100px]" placeholder="Brief context about this holiday..."></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <x-button type="button" variant="outline" class="flex-1" @click="showModal = false">Cancel</x-button>
                        <x-button type="submit" variant="primary" class="flex-1" x-text="isEdit ? 'Update' : 'Create'"></x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
