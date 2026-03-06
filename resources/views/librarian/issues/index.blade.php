@extends('layouts.app')

@section('header_title', 'Issue Book')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Issue Book</h2>
            <p class="text-sm text-slate-500">Search student and book details to issue a new borrowing.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Issue Form</h3>
                <div class="mt-4 space-y-4">
                    <input class="input-premium" placeholder="Search Student ID / Name">
                    <input class="input-premium" placeholder="Search Book Title / ISBN">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="date" class="input-premium">
                        <input type="date" class="input-premium">
                    </div>
                    <input type="number" class="input-premium" placeholder="Number of Copies">
                    <button class="btn-primary-gradient w-full py-2">Issue Book</button>
                </div>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Recent Issues</h3>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    @forelse($issues as $issue)
                        <div class="flex items-center justify-between border border-slate-100 rounded-2xl px-4 py-3">
                            <div>
                                <p class="font-semibold">Issue #{{ $issue->id }}</p>
                                <p class="text-xs text-slate-400">Due {{ $issue->due_date?->format('M d, Y') }}</p>
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ $issue->status }}</span>
                        </div>
                    @empty
                        <p class="text-slate-400">No recent issues.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
