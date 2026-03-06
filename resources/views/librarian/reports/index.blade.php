@extends('layouts.app')

@section('header_title', 'Reports & Analytics')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Reports & Analytics</h2>
            <p class="text-sm text-slate-500">Export insights on borrowing, overdue stats, and book categories.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Most Borrowed Books</h3>
                <div class="mt-4 h-40 rounded-2xl bg-slate-50"></div>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Monthly Borrow Activity</h3>
                <div class="mt-4 h-40 rounded-2xl bg-slate-50"></div>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Overdue Statistics</h3>
                <div class="mt-4 h-40 rounded-2xl bg-slate-50"></div>
            </div>
        </div>

        <div class="flex gap-3">
            <button class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl">Export PDF</button>
            <button class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl">Export Excel</button>
        </div>
    </div>
@endsection
