@extends('layouts.app')

@section('header_title', 'Payment History')

@section('content')
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-clock-history mr-1"></i> Transaction Log
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Payment <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">History</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium">Review and verify historical financial transactions and collections.</p>
            </div>
            
            <x-button variant="outline" href="{{ route('accountant.fees.index') }}" icon="bi-arrow-left">
                Back to Feedesk
            </x-button>
        </div>

        {{-- History Table --}}
        <x-card class="p-2 border border-white/60 shadow-xl">
            <x-table :headers="['Date & Time', 'Receipt No.', 'Student', 'Amount Paid', 'Mode', 'Remarks']">
                @forelse($payments as $payment)
                    <tr class="group/row">
                        <td>
                            <div class="text-sm font-bold text-slate-800">{{ $payment->created_at->format('d M, Y') }}</div>
                            <div class="text-[10px] text-slate-400 font-bold tracking-tight">{{ $payment->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <span class="text-xs font-black text-slate-600 tracking-widest">{{ $payment->receipt_number ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($payment->studentFee->student->user->name ?? 'F') }}&background=d1fae5&color=059669&size=32"
                                    class="h-9 w-9 rounded-xl ring-2 ring-emerald-50" alt="">
                                <div>
                                    <div class="text-sm font-bold text-slate-800">{{ $payment->studentFee->student->user->name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold tracking-tight">{{ $payment->studentFee->student->roll_number ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-sm font-black text-emerald-600">₹{{ number_format($payment->amount) }}</span>
                        </td>
                        <td>
                            <span class="text-[10px] uppercase font-black tracking-widest text-slate-500">{{ $payment->payment_mode }}</span>
                        </td>
                        <td>
                            <p class="text-xs text-slate-500 truncate max-w-[200px]" title="{{ $payment->remarks }}">{{ $payment->remarks ?? '—' }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-sm text-slate-400 py-12">
                            <i class="bi bi-inbox text-4xl block mb-2 opacity-20"></i>
                            No transaction records found.
                        </td>
                    </tr>
                @endforelse
            </x-table>
            
            @if($payments->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $payments->links() }}
                </div>
            @endif
        </x-card>
    </div>
@endsection
