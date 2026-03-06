@extends('layouts.app')

@section('header_title', 'Book Requests')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Book Requests</h2>
            <p class="text-sm text-slate-500">Approve or reject student book requests.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Book Requested</th>
                        <th class="px-4 py-3">Request Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($requests as $request)
                        <tr>
                            <td class="px-4 py-3 font-semibold">Student #{{ $request->student_id }}</td>
                            <td class="px-4 py-3">{{ $request->title }}</td>
                            <td class="px-4 py-3">{{ $request->created_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600">{{ ucfirst($request->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-emerald-600 border border-emerald-200 rounded-lg">Approve</button>
                                    <button class="px-3 py-1.5 text-xs font-semibold text-rose-600 border border-rose-200 rounded-lg">Reject</button>
                                    <button class="px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg">Add Book</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($requests, 'links') && $requests->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
