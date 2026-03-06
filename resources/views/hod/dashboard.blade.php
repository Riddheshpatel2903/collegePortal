@extends('layouts.app')

@section('header_title', 'HOD Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="glass-card p-6">
            <h2 class="text-xl font-black text-slate-800">{{ $department->name }} HOD Workspace</h2>
            <p class="text-sm text-slate-500 mt-1">Manage timetable, faculty allocations, leaves, and department notices.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @canPage('hod.teacher-assignments.index')
            <div class="stat-card"><p class="text-sm text-slate-500">Teachers</p><p class="text-xl font-bold">{{ $stats['teachers'] }}</p></div>
            <div class="stat-card"><p class="text-sm text-slate-500">Students</p><p class="text-xl font-bold">{{ $stats['students'] }}</p></div>
            @endcanPage
            @canPage('hod.leaves.index')
            <div class="stat-card"><p class="text-sm text-slate-500">Pending Leaves</p><p class="text-xl font-bold">{{ $stats['pending_leaves'] }}</p></div>
            @endcanPage
            @canPage('hod.notices.index')
            <div class="stat-card"><p class="text-sm text-slate-500">Active Notices</p><p class="text-xl font-bold">{{ $stats['active_notices'] }}</p></div>
            @endcanPage
        </div>

        <div class="glass-card p-5 flex flex-wrap gap-3">
            @canPage('hod.teacher-assignments.index')
            <a href="{{ route('hod.teacher-assignments.index') }}" class="btn-primary-gradient">Teacher Assignments</a>
            @endcanPage
            @canPage('hod.timetable.index')
            <a href="{{ route('hod.timetable.index') }}" class="btn-primary-gradient">Department Timetable</a>
            @endcanPage
            @canPage('hod.leaves.index')
            <a href="{{ route('hod.leaves.index') }}" class="btn-outline">Leave Approvals</a>
            @endcanPage
            @canPage('hod.notices.index')
            <a href="{{ route('hod.notices.index') }}" class="btn-outline">Notices</a>
            @endcanPage
        </div>
    </div>
@endsection
