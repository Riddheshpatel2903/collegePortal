@extends('layouts.app')

@section('header_title', 'Return Book')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Return Book</h2>
            <p class="text-sm text-slate-500">Confirm returns, calculate fines, and close borrowings.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input class="input-premium" placeholder="Student ID">
                <input class="input-premium" placeholder="Book ID">
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-400">Issue Date</p>
                    <p class="text-sm font-bold">--</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-400">Due Date</p>
                    <p class="text-sm font-bold">--</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-400">Days Late</p>
                    <p class="text-sm font-bold">--</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs text-slate-400">Fine Amount</p>
                    <p class="text-sm font-bold">₹0</p>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <button class="btn-primary-gradient px-5 py-2 text-sm">Confirm Return</button>
                <button class="px-5 py-2 text-sm font-semibold text-rose-600 border border-rose-200 rounded-xl">Pay Fine</button>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <h3 class="text-lg font-bold text-slate-800">Recent Returns</h3>
            <div class="mt-4 space-y-3">
                @forelse($issues as $issue)
                    <div class="flex items-center justify-between border border-slate-100 rounded-2xl px-4 py-3 text-sm">
                        <div>
                            <p class="font-semibold">Issue #{{ $issue->id }}</p>
                            <p class="text-xs text-slate-400">Due {{ $issue->due_date?->format('M d, Y') }}</p>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ $issue->status }}</span>
                    </div>
                @empty
                    <p class="text-slate-400">No return activity.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
