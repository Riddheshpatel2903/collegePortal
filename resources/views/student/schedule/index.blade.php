@extends('layouts.app')

@section('header_title', 'My Timetable')

@section('content')
    <div class="space-y-6">
        <!-- ─── Page Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">Class Timetable</h2>
                <p class="text-sm text-slate-500">Weekly schedule for your current semester academic sessions.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-1 bg-indigo-100 text-indigo-700 rounded-lg uppercase tracking-wider">Student View</span>
            </div>
        </div>

        <!-- ─── Weekly Grid ─── -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-black text-slate-800 text-lg">Weekly Overview</h3>
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <i class="bi bi-grid-3x3"></i> Session Grid
                </div>
            </div>
            <x-weekly-timetable-grid 
                :days="$days" 
                :timeSlots="$timeSlots" 
                :grid="$grid" 
                :showTeacher="true" 
                :showRoom="true" 
                :showSemester="false" 
                :colorBySubject="true"
                emptyText="Your class schedule is not available yet." 
            />
        </div>

        <!-- ─── List View ─── -->
        <div class="glass-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-black text-slate-800">Schedule Details</h3>
                <i class="bi bi-card-checklist text-slate-400"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time Block</th>
                            <th>Subject</th>
                            <th>Instructor</th>
                            <th class="text-right">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $slot)
                            <tr>
                                <td class="font-bold text-slate-800">{{ ucfirst($slot->day) }}</td>
                                <td class="font-mono text-xs">{{ $slot->start_time }} - {{ $slot->end_time }}</td>
                                <td class="font-semibold text-slate-700">{{ $slot->subject_name }}</td>
                                <td class="whitespace-nowrap flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-50 flex items-center justify-center text-[10px] font-bold text-indigo-500">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    {{ $slot->teacher_name ?? 'N/A' }}
                                </td>
                                <td class="text-right">
                                    <span class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-lg text-slate-600 font-bold text-[11px]">
                                        {{ $slot->room_name ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="flex flex-col items-center opacity-40">
                                        <i class="bi bi-calendar3 text-5xl mb-2"></i>
                                        <p class="text-sm font-semibold">No sessions scheduled for your class.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
