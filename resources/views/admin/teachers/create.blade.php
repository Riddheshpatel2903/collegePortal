@extends('layouts.app')

@section('header_title', 'Add Faculty')

@section('content')
    <x-page-header 
        title="Register Faculty" 
        subtitle="Onboard a new faculty member with their academic credentials and department assignment."
        icon="bi-person-badge"
        back="{{ route('admin.teachers.index') }}"
    />

    <div class="max-w-4xl mx-auto mt-8">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Faculty Member Information</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Academic & Access Configuration</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-mortarboard"></i>
                </div>
            </div>

            <form action="{{ route('admin.teachers.store') }}" method="POST" class="p-8 space-y-8">
                @csrf

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
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Legal Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="e.g. Dr. Robert Wilson" required pattern="[a-zA-Z\s.]+" title="Only characters, spaces and dots are allowed">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Official Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="r.wilson@college.edu" required>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Academic Department</label>
                        <select name="department_id"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600" required>
                            <option value="">Select Department...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Academic Qualification</label>
                        <input type="text" name="qualification" value="{{ old('qualification') }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="e.g. Ph.D. in Computer Science">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Contact Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="e.g. 9876543210" pattern="\d{10}" maxlength="10" title="Exactly 10 digits are required">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Account Password</label>
                        <input type="password" name="password"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="••••••••" required>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex items-center justify-between gap-6">
                    <div class="max-w-xs">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
                            Once registered, the faculty member can log in to manage schedules, assignments, and student academic results.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="reset" class="px-6 py-3 bg-white border border-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Reset Form</button>
                        <button type="submit" class="px-10 py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                            <span>Register Faculty</span>
                            <i class="bi bi-person-plus-fill"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection