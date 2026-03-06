@extends('layouts.app')

@section('header_title', 'Fine Management')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Fine Management</h2>
            <p class="text-sm text-slate-500">Track and reconcile overdue fines.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Issue</th>
                        <th class="px-4 py-3">Days Late</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($fines as $fine)
                        <tr>
                            <td class="px-4 py-3 font-semibold">Student #{{ $fine->student_id }}</td>
                            <td class="px-4 py-3">Issue #{{ $fine->library_issue_id }}</td>
                            <td class="px-4 py-3">{{ $fine->days_late }}</td>
                            <td class="px-4 py-3">₹{{ number_format($fine->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $fine->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                    {{ ucfirst($fine->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg">Record Payment</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-slate-400">No fines recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($fines, 'links') && $fines->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $fines->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
