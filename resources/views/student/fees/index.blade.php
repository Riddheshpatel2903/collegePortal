@extends('layouts.app')

@section('header_title', 'Fee Status')

@section('content')
    <div class="space-y-6">
        @php
            $hasOverdue = $fees->getCollection()->contains(fn($fee) => $fee->status === 'overdue');
            $hasPending = $fees->getCollection()->contains(fn($fee) => in_array($fee->status, ['pending', 'partial', 'overdue'], true));
        @endphp
        @if($hasPending)
            <div class="glass-card p-4 border-l-4 {{ $hasOverdue ? 'border-rose-500' : 'border-amber-500' }}">
                <p class="text-sm font-semibold {{ $hasOverdue ? 'text-rose-600' : 'text-amber-600' }}">
                    {{ $hasOverdue ? 'Fee overdue alert:' : 'Fee pending alert:' }} Please clear pending college fees before exam form processing.
                </p>
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-4">
            <div class="stat-card">
                <p class="text-xs text-slate-500 uppercase">Total</p>
                <p class="text-2xl font-black mt-2">Rs {{ number_format($totalAmount, 2) }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs text-slate-500 uppercase">Paid</p>
                <p class="text-2xl font-black mt-2 text-emerald-600">Rs {{ number_format($totalPaid, 2) }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs text-slate-500 uppercase">Pending</p>
                <p class="text-2xl font-black mt-2 text-rose-600">Rs {{ number_format($totalPending, 2) }}</p>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Pending</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                        <tr>
                            <td>{{ optional($fee->due_date)->format('d M Y') ?? '-' }}</td>
                            <td>Rs {{ number_format($fee->total_amount, 2) }}</td>
                            <td>Rs {{ number_format($fee->paid_amount, 2) }}</td>
                            <td>Rs {{ number_format($fee->pending_amount, 2) }}</td>
                            <td>
                                @php
                                    $status = $fee->status;
                                    $badgeType = $status === 'paid' ? 'success' : ($status === 'overdue' ? 'danger' : 'warning');
                                @endphp
                                <x-badge :type="$badgeType">{{ ucfirst($status) }}</x-badge>
                            </td>
                            <td><span class="text-xs text-slate-500">Pay at accounts office</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-slate-500">No fee records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $fees->links() }}</div>
    </div>
@endsection
