@extends('layouts.app')

@section('header_title', 'My Timetable')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-black text-slate-800">Class Timetable</h2>
            <p class="text-sm text-slate-500">Read-only weekly schedule for your current academic year.</p>
        </div>

        <x-weekly-timetable-grid :days="$days" :timeSlots="$timeSlots" :grid="$grid" :showTeacher="true" :showRoom="true" :showSemester="false" emptyText="No timetable available." />
    </div>
@endsection
