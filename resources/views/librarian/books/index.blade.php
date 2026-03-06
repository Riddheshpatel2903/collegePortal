@extends('layouts.app')

@section('header_title', 'Manage Books')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Library Inventory</h2>
                <p class="text-sm text-slate-500">Add, edit, and monitor all books in the library collection.</p>
            </div>
            <button class="btn-primary-gradient px-5 py-2 text-sm">Add New Book</button>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white shadow-xl shadow-slate-200/40 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Book ID</th>
                            <th class="px-6 py-4">Title</th>
                            <th class="px-6 py-4">Author</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">ISBN</th>
                            <th class="px-6 py-4">Quantity</th>
                            <th class="px-6 py-4">Available</th>
                            <th class="px-6 py-4">Shelf</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($books as $book)
                            <tr>
                                <td class="px-6 py-4 font-semibold">LIB-{{ str_pad($book->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $book->title }}</td>
                                <td class="px-6 py-4">{{ $book->author }}</td>
                                <td class="px-6 py-4">{{ $book->category ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $book->isbn ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $book->quantity }}</td>
                                <td class="px-6 py-4">{{ $book->available_copies }}</td>
                                <td class="px-6 py-4">{{ $book->shelf_location ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $book->status === 'available' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                        {{ ucfirst($book->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg">View</button>
                                        <button class="px-3 py-1.5 text-xs font-semibold text-violet-600 border border-violet-200 rounded-lg">Edit</button>
                                        <button class="px-3 py-1.5 text-xs font-semibold text-rose-600 border border-rose-200 rounded-lg">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-8 text-center text-sm text-slate-400">No books found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($books, 'links') && $books->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
