@extends('layouts.app')

@section('header_title', 'Library')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.3em] text-sky-500">Student Library</p>
                <h2 class="text-3xl font-black text-slate-900">Your Library Dashboard</h2>
                <p class="text-sm text-slate-500">Track your borrowings, due dates, and reservations.</p>
            </div>
            @canPage('student.library.browse')
            <a href="{{ route('student.library.browse') }}" class="btn-primary-gradient px-5 py-2 text-sm">Browse Books</a>
            @endcanPage
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            @canPage('student.library.borrowed')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="h-10 w-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg"><i class="bi bi-book-half"></i></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Borrowed Books</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($borrowed) }}</p></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i class="bi bi-alarm"></i></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Due Soon</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($dueSoon) }}</p></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="h-10 w-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg"><i class="bi bi-exclamation-triangle-fill"></i></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Overdue</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($overdue) }}</p></div>
            @endcanPage
            @canPage('student.library.reservations')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="h-10 w-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg"><i class="bi bi-bookmark-heart-fill"></i></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Reserved</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($reserved) }}</p></div>
            @endcanPage
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Quick Links</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @canPage('student.library.borrowed')
                    <a href="{{ route('student.library.borrowed') }}" class="px-4 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold">Borrowed Books</a>
                    @endcanPage
                    @canPage('student.library.reservations')
                    <a href="{{ route('student.library.reservations') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reservations</a>
                    @endcanPage
                    @canPage('student.library.requests')
                    <a href="{{ route('student.library.requests') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Requests</a>
                    @endcanPage
                    @canPage('student.library.fines')
                    <a href="{{ route('student.library.fines') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Fines & Penalties</a>
                    @endcanPage
                </div>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Notifications</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-500">
                    <li class="rounded-2xl border border-slate-100 px-4 py-3">No notifications yet.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
