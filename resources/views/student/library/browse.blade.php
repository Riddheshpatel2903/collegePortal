@extends('layouts.app')

@section('header_title', 'Browse Books')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Browse Library</h2>
                <p class="text-sm text-slate-500">Search by title, author, category, or ISBN.</p>
            </div>
            <div class="flex gap-3">
                <input class="input-premium" placeholder="Search books...">
                <button class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl">Filter</button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3 lg:grid-cols-4">
            @forelse($books as $book)
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-xl shadow-slate-200/40">
                    <div class="h-40 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300">
                        <i class="bi bi-book text-3xl"></i>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">{{ $book->title }}</h3>
                    <p class="text-sm text-slate-500">{{ $book->author }}</p>
                    <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                        <span>{{ $book->category ?? 'General' }}</span>
                        <span>{{ $book->available_copies }} available</span>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button class="flex-1 rounded-xl bg-slate-900 text-white text-xs font-semibold py-2">Borrow</button>
                        <button class="flex-1 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 py-2">Reserve</button>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-slate-400">No books available.</div>
            @endforelse
        </div>
        @if(method_exists($books, 'links') && $books->hasPages())
            <div class="border-t border-slate-100 pt-4">
                {{ $books->links() }}
            </div>
        @endif
    </div>
@endsection
