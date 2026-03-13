@extends('layouts.app')

@section('header_title', 'Fees Management')

@section('content')
    {{-- Page Header --}}
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-wallet2 mr-1"></i> Financial Control
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Fees <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Nexus</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium">Track payments, manage collections, and monitor outstanding dues.</p>
            </div>
            
            <x-button variant="outline" onclick="exportCSV()" icon="bi-download">
                Export CSV
            </x-button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $statsConfig = [
                    ['Total Expected', '₹' . number_format($summary['total']), 'bi-wallet-fill', 'violet'],
                    ['Collected', '₹' . number_format($summary['collected']), 'bi-check-circle-fill', 'emerald'],
                    ['Pending', '₹' . number_format($summary['pending']), 'bi-clock-fill', 'amber'],
                    ['Unpaid', '₹' . number_format($summary['unpaid']), 'bi-exclamation-triangle-fill', 'rose'],
                ];
            @endphp
            @foreach($statsConfig as $stat)
                <x-card class="border-l-4 border-l-{{ $stat[3] }}-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-11 w-11 rounded-xl bg-{{ $stat[3] }}-500/10 text-{{ $stat[3] }}-600 flex items-center justify-center text-xl">
                            <i class="bi {{ $stat[2] }}"></i>
                        </div>
                        <x-badge>{{ $stat[0] }}</x-badge>
                    </div>
                    <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $stat[1] }}</div>
                </x-card>
            @endforeach
        </div>

        {{-- Filters & Controls --}}
        <x-card class="p-2 border border-white/60 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="relative flex-1 min-w-[300px] h-12 group">
                    <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                    <input type="text" id="feeSearch" placeholder="Search by name or roll number..."
                        class="input-premium pl-12 h-full">
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button class="filter-pill active" data-status="all">All</button>
                    <button class="filter-pill" data-status="paid">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 inline-block"></span> Paid
                    </button>
                    <button class="filter-pill" data-status="partial">
                        <span class="h-2 w-2 rounded-full bg-amber-500 inline-block"></span> Partial
                    </button>
                    <button class="filter-pill" data-status="pending">
                        <span class="h-2 w-2 rounded-full bg-rose-500 inline-block"></span> Unpaid
                    </button>
                </div>
            </div>
        </x-card>

        {{-- Fee Table --}}
        <x-table :headers="['Student', 'Course & Sem', 'Total', 'Paid', 'Pending', 'Status']">
            @forelse($fees as $fee)
                <tr data-status="{{ $fee->status }}" class="group/row">
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($fee->student->user->name ?? 'F') }}&background=ede9fe&color=7c3aed&size=32"
                                class="h-9 w-9 rounded-xl ring-2 ring-violet-50" alt="">
                            <div>
                                <div class="text-sm font-bold text-slate-800">{{ $fee->student->user->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400 font-bold tracking-tight">{{ $fee->student->roll_number ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-xs font-bold text-slate-700 leading-none mb-1">{{ $fee->student->course->name ?? '—' }}</div>
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">{{ $fee->student->semester->name ?? '—' }}</div>
                    </td>
                    <td>
                        <span class="text-sm font-black text-slate-700">₹{{ number_format($fee->total_amount) }}</span>
                    </td>
                    <td>
                        <span class="text-sm font-black text-emerald-600">₹{{ number_format($fee->paid_amount) }}</span>
                    </td>
                    <td>
                        <span class="text-sm font-black {{ $fee->pending_amount > 0 ? 'text-rose-500' : 'text-slate-400' }}">
                            ₹{{ number_format($fee->pending_amount) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php
                            $badgeType = match($fee->status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                'pending', 'overdue' => 'danger',
                                default => 'default',
                            };
                        @endphp
                        <x-badge :type="$badgeType">
                            {{ ucfirst($fee->status) }}
                        </x-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-sm text-slate-400 py-12">
                        <i class="bi bi-inbox text-4xl block mb-2 opacity-20"></i>
                        No fee records found.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>



    @push('styles')
    <style>
        .filter-pill {
            @apply px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border border-slate-200 text-slate-500 bg-white transition-all cursor-pointer flex items-center gap-2;
        }
        .filter-pill:hover {
            @apply border-violet-200 text-violet-600 shadow-lg shadow-violet-500/10;
        }
        .filter-pill.active {
            @apply bg-slate-900 text-white border-slate-900 shadow-xl shadow-slate-900/20;
        }
    </style>
    @endpush

    <script>
        // ── Search ──
        document.getElementById('feeSearch').addEventListener('input', applyFilters);

        // ── Status Filter ──
        let activeStatus = 'all';
        document.querySelectorAll('.filter-pill').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeStatus = this.dataset.status;
                applyFilters();
            });
        });

        function applyFilters() {
            const query = document.getElementById('feeSearch').value.toLowerCase();
            let visible = 0;

            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.cells[0]?.textContent.toLowerCase() || '';
                const status = row.dataset.status;

                const matchSearch = text.includes(query);
                const matchStatus = activeStatus === 'all' || status === activeStatus;

                const show = matchSearch && matchStatus;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
        }



        // ── CSV Export ──
        function exportCSV() {
            const rows = [['Student', 'Roll Number', 'Course', 'Semester', 'Total', 'Paid', 'Pending', 'Status']];
            document.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                const cells = row.querySelectorAll('td');
                if (cells.length < 6) return;
                rows.push([
                    cells[0].querySelector('.text-sm.font-bold')?.textContent.trim() || '',
                    cells[0].querySelector('.text-[10px]')?.textContent.trim() || '',
                    cells[1].querySelector('.text-xs')?.textContent.trim() || '',
                    cells[1].querySelector('.text-[9px]')?.textContent.trim() || '',
                    cells[2].textContent.trim().replace('₹', '').replace(/,/g, ''),
                    cells[3].textContent.trim().replace('₹', '').replace(/,/g, ''),
                    cells[4].textContent.trim().replace('₹', '').replace(/,/g, ''),
                    cells[5].textContent.trim(),
                ]);
            });

            const csv = rows.map(r => r.map(c => '"' + c.replace(/"/g, '""') + '"').join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'fees_report_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
@endsection
