@extends('layouts.app')

@section('header_title', 'Book Requests')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Book Requests</h2>
            <p class="text-sm text-slate-500">Request new books that are not available.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <input class="input-premium" placeholder="Book Title">
                <input class="input-premium" placeholder="Author">
                <textarea class="input-premium md:col-span-2" rows="3" placeholder="Reason for request"></textarea>
                <button class="btn-primary-gradient md:col-span-2 py-2">Submit Request</button>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Book</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Requested</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($requests as $request)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $request->title }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600">{{ ucfirst($request->status) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $request->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-400">No requests yet.</td>
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
