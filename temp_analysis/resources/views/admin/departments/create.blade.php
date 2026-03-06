@extends('layouts.app')

@section('header_title', 'Add Department')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">New Department</h2>
            <p class="text-sm text-slate-400 mt-1">Create a new academic department.</p>
        </div>
        <a href="{{ route('admin.departments.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                        <ul class="text-sm text-rose-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2"><i class="bi bi-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Department
                        Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                        placeholder="e.g. Computer Science" required>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all resize-none"
                        placeholder="Brief description of the department">{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="reset"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Reset</button>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">Create
                        Department</button>
                </div>
            </form>
        </div>
    </div>
@endsection