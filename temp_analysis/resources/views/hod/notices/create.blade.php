@extends('layouts.app')

@section('header_title', 'Create Notice')

@section('content')
    <form method="POST" action="{{ route('hod.notices.store') }}" class="glass-card p-6 space-y-4">
        @csrf
        <div><label>Title</label><input name="title" class="w-full border rounded p-2" required></div>
        <div><label>Content</label><textarea name="content" class="w-full border rounded p-2" rows="5" required></textarea></div>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label>Target Role</label>
                <select name="target_role" class="w-full border rounded p-2">
                    <option value="all">All</option><option value="student">Student</option><option value="teacher">Teacher</option><option value="hod">HOD</option>
                </select>
            </div>
            <div>
                <label>Priority</label>
                <select name="priority" class="w-full border rounded p-2">
                    <option value="medium">Medium</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option>
                </select>
            </div>
            <div><label>Expiry Date</label><input type="date" name="expiry_date" class="w-full border rounded p-2"></div>
        </div>
        <button class="px-4 py-2 bg-slate-900 text-white rounded">Publish</button>
    </form>
@endsection
