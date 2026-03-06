@extends('layouts.app')

@section('header_title', 'Library Dashboard')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.3em] text-violet-500">Librarian</p>
                <h2 class="text-3xl font-black text-slate-900">Library Operations Center</h2>
                <p class="text-sm text-slate-500">Monitor circulation, manage inventory, and resolve requests quickly.</p>
            </div>
            <div class="flex gap-3">
                @canPage('librarian.books.index')
                <a href="{{ route('librarian.books.index') }}" class="btn-primary-gradient px-5 py-2 text-sm">Manage Books</a>
                @endcanPage
                @canPage('librarian.issues.index')
                <a href="{{ route('librarian.issues.index') }}" class="px-5 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">Issue Book</a>
                @endcanPage
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            @canPage('librarian.books.index')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl"><i class="bi bi-journal-bookmark-fill"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Total Books</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($totalBooks) }}</p></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl"><i class="bi bi-archive-fill"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Books Available</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($booksAvailable) }}</p></div>
            @endcanPage
            @canPage('librarian.issues.index')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl"><i class="bi bi-box-arrow-up-right"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Books Issued</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($booksIssued) }}</p></div>
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl"><i class="bi bi-people-fill"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Active Borrowers</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($activeBorrowers) }}</p></div>
            @endcanPage
            @canPage('librarian.overdues.index')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl"><i class="bi bi-alarm-fill"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Overdue Books</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($overdueBooks) }}</p></div>
            @endcanPage
            @canPage('librarian.reservations.index')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40"><div class="flex items-center justify-between"><div class="h-12 w-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl"><i class="bi bi-bookmark-heart-fill"></i></div><span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Live</span></div><p class="mt-4 text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Reserved Books</p><p class="mt-2 text-3xl font-black text-slate-900">{{ number_format($reservedBooks) }}</p></div>
            @endcanPage
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Quick Actions</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @canPage('librarian.returns.index')
                    <a href="{{ route('librarian.returns.index') }}" class="px-4 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold">Process Return</a>
                    @endcanPage
                    @canPage('librarian.requests.index')
                    <a href="{{ route('librarian.requests.index') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Handle Requests</a>
                    @endcanPage
                    @canPage('librarian.reservations.index')
                    <a href="{{ route('librarian.reservations.index') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reservations Queue</a>
                    @endcanPage
                    @canPage('librarian.fines.index')
                    <a href="{{ route('librarian.fines.index') }}" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">Manage Fines</a>
                    @endcanPage
                </div>
            </div>
            @canPage('librarian.reports.index')
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
                <h3 class="text-lg font-bold text-slate-800">Reports & Analytics</h3>
                <p class="mt-2 text-sm text-slate-500">Use analytics to understand circulation trends and improve inventory planning.</p>
                <a href="{{ route('librarian.reports.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white">
                    <i class="bi bi-bar-chart-fill"></i> View Reports
                </a>
            </div>
            @endcanPage
        </div>
    </div>
@endsection
