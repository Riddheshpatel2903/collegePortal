@extends('layouts.app')

@section('header_title', 'My Teaching Schedule')

@section('content')
    <div class="space-y-6">
        <!-- ─── Page Header ─── -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-800">My Teaching Schedule</h2>
                <p class="text-sm text-slate-500">View and manage your weekly assigned classes and locations.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold px-2 py-1 bg-indigo-100 text-indigo-700 rounded-lg uppercase tracking-wider">Faculty View</span>
            </div>
        </div>

        <!-- ─── Weekly Grid ─── -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-black text-slate-800 text-lg">Weekly Overview</h3>
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <i class="bi bi-calendar-week"></i> Interactive Grid
                </div>
            </div>
            <x-weekly-timetable-grid 
                :days="$days" 
                :timeSlots="$timeSlots" 
                :grid="$grid" 
                :showTeacher="false" 
                :showRoom="true" 
                :showSemester="true" 
                :colorBySubject="true"
                emptyText="You have no teaching slots assigned for the current phase." 
            />
        </div>


    </div>
@endsection
