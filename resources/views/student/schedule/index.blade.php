@extends('layouts.app')

@section('header_title', 'My Timetable')

@section('content')
<div class="space-y-8 animate-fade-in">
    <!-- ─── Page Header ─── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Class Timetable</h2>
            <p class="text-sm text-slate-500">Weekly schedule for your current semester academic sessions.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold border border-indigo-100 uppercase tracking-wider">Student View</span>
        </div>
    </div>

    <!-- ─── Weekly Grid ─── -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="bi bi-calendar3 text-indigo-500"></i> Weekly Overview
            </h3>
            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <i class="bi bi-grid-3x3"></i> Session Matrix
            </div>
        </div>
        <div class="p-6">
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
    </div>


</div>
@endsection
