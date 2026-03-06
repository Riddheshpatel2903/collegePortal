@extends('layouts.app')

@section('header_title', 'Reservations')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-black text-slate-900">Reservations</h2>
            <p class="text-sm text-slate-500">Track reserved books and queue positions.</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-200/40">
            <table class="w-full text-left">
                <thead class="text-xs uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Book</th>
                        <th class="px-4 py-3">Reserved At</th>
                        <th class="px-4 py-3">Queue</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($reservations as $reservation)
                        <tr>
                            <td class="px-4 py-3 font-semibold">Student #{{ $reservation->student_id }}</td>
                            <td class="px-4 py-3">Book #{{ $reservation->library_book_id }}</td>
                            <td class="px-4 py-3">{{ $reservation->reserved_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ $reservation->queue_position ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600">{{ ucfirst($reservation->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400">No reservations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(method_exists($reservations, 'links') && $reservations->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
