@extends('layouts.app')

@section('header_title', 'Post Notice')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Post a Notice</h2>
            <p class="text-sm text-slate-400 mt-1">Create an announcement for students and staff.</p>
        </div>
        <a href="{{ route('teacher.notices.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form action="{{ route('teacher.notices.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
                        <i class="bi bi-megaphone-fill"></i></div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Notice Details</h3>
                        <p class="text-xs text-slate-400">Write and publish your announcement.</p>
                    </div>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Title</label>
                    <input type="text" name="title"
                        class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                        placeholder="Enter notice title" required>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Category</label>
                    <select name="category"
                        class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <option>Academic</option>
                        <option>Assignment</option>
                        <option>General</option>
                    </select>
                </div>
                <div>
                    <label
                        class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Content</label>
                    <textarea name="description" rows="6"
                        class="w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all resize-none"
                        placeholder="Write your notice..." required></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="reset"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Clear</button>
                    <button type="submit"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all">Publish</button>
                </div>
            </form>
        </div>
    </div>
@endsection
