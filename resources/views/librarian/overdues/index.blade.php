@extends('layouts.app')

@section('header_title', 'Overdue Books')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Overdue Books</h2>
            <p class="text-sm text-slate-500">Identify overdue borrowings and send reminders.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Book</th>
                        <th class="px-4 py-3">Issue Date</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($overdues as $overdue)
                        <tr>
                            <td class="px-4 py-3 font-semibold">Student #{{ $overdue->student_id }}</td>
                            <td class="px-4 py-3">Book #{{ $overdue->library_book_id }}</td>
                            <td class="px-4 py-3">{{ $overdue->issue_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $overdue->due_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600">Overdue</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-sky-600 border border-sky-200 rounded-lg">Send Reminder</button>
                                    <button class="px-3 py-1.5 text-xs font-semibold text-emerald-600 border border-emerald-200 rounded-lg">Mark Returned</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">No overdue books.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($overdues, 'links') && $overdues->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $overdues->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
