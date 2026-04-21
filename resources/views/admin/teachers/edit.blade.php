@extends('layouts.app')

@section('header_title', 'Edit Faculty')

@section('content')
    <x-page-header 
        title="Edit Faculty Profile" 
        subtitle="Update academic records, qualifications, and department assignment for this faculty member."
        icon="bi-pencil-square"
        back="{{ route('admin.teachers.index') }}"
    />

    <div class="max-w-4xl mx-auto mt-8">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Faculty Member Identity</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Faculty ID: {{ $teacher->id }}</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>

            <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-[10px] font-bold text-rose-600 uppercase tracking-widest space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $teacher->user->name) }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" 
                            required pattern="[a-zA-Z\s.]+" title="Only characters, spaces and dots are allowed">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $teacher->user->email) }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Department</label>
                        <select name="department_id"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $teacher->department_id == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Qualification</label>
                        <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Contact Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $teacher->phone) }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            pattern="\d{10}" maxlength="10" title="Exactly 10 digits are required">
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.teachers.index') }}" class="px-6 py-3.5 bg-white border border-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Cancel</a>
                    <button type="submit" class="px-10 py-3.5 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                        <span>Save Changes</span>
                        <i class="bi bi-check-circle-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection