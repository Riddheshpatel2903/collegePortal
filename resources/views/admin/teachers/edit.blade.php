@extends('layouts.app')

@section('header_title', 'Edit Teacher')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Edit Teacher</h2>
            <p class="text-sm text-slate-400 mt-1">Update faculty member details.</p>
        </div>
        <a href="{{ route('admin.teachers.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-sm text-rose-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Update Faculty</h3>
                        <p class="text-xs text-slate-400">Modify teacher details and department.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Full
                            Name</label>
                        <input type="text" name="name" value="{{ old('name', $teacher->user->name) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $teacher->user->email) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                        <select name="department_id"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $teacher->department_id == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Qualification</label>
                        <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $teacher->phone) }}"
                            class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.teachers.index') }}"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</a>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-teal-500 to-teal-600 rounded-xl hover:shadow-lg hover:shadow-teal-500/25 transition-all">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection