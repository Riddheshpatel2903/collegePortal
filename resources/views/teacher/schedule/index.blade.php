@extends('layouts.app')

@section('header_title', 'My Timetable')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-black text-slate-800">Weekly Schedule</h2>
            <p class="text-sm text-slate-500">Read-only timetable for your assigned classes.</p>
        </div>

        <x-weekly-timetable-grid :days="$days" :timeSlots="$timeSlots" :grid="$grid" :showTeacher="false" :showRoom="true" :showSemester="true" emptyText="No timetable slots assigned." />

        
    </div>
@endsection
