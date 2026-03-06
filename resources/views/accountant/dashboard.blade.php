@extends('layouts.app')

@section('header_title', 'Accountant Dashboard')

@section('content')
    <div class="space-y-12">
        <section class="relative overflow-hidden rounded-[34px] border border-emerald-200/50 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 px-8 py-12 shadow-[0_40px_90px_-55px_rgba(15,23,42,0.85)]">
            <div class="relative z-10 grid gap-8 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.35em] text-emerald-200">
                        Accountant Suite
                    </div>
                    <h2 class="mt-4 text-4xl font-black tracking-tight text-white">Financial Operations Hub</h2>
                    <p class="mt-3 max-w-xl text-sm font-semibold text-emerald-100/80">
                        Premium control surface for collections, dues, and reconciliation. Built for speed and clarity.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        @canPage('accountant.fees.index')
                        <x-button variant="primary" href="{{ route('accountant.fees.index') }}" class="!bg-emerald-500 hover:!bg-emerald-400 !text-slate-900 border-none shadow-lg shadow-emerald-500/30">
                            Open Fee Gateway
                        </x-button>
                        @endcanPage
                        @canPage('accountant.fees.history')
                        <x-button variant="outline" href="{{ route('accountant.fees.history') }}" class="!border-emerald-200 !text-emerald-100 hover:!bg-emerald-500/10">
                            View History
                        </x-button>
                        @endcanPage
                    </div>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                    <p class="text-[11px] font-black uppercase tracking-[0.3em] text-emerald-200/70">Current Balance</p>
                    <p class="mt-2 text-3xl font-black text-white"> {{ number_format($totalCollected) }}</p>
                    <p class="mt-3 text-xs font-semibold text-emerald-100/70">Live collections verified</p>
                </div>
            </div>
            <div class="pointer-events-none absolute -right-10 -top-8 h-44 w-44 rounded-full bg-emerald-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-10 h-60 w-60 rounded-full bg-cyan-400/10 blur-[90px]"></div>
        </section>

        <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            @canPage('accountant.fees.index')
            @foreach([
                ['Expected Revenue', ' ' . number_format($totalExpected), 'bi-wallet2'],
                ['Total Collected', ' ' . number_format($totalCollected), 'bi-safe-fill'],
                ['Outstanding Dues', ' ' . number_format($totalPending), 'bi-exclamation-octagon-fill'],
            ] as $stat)
                <div class="group rounded-[28px] border border-slate-100 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(15,23,42,0.45)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_34px_80px_-40px_rgba(15,23,42,0.55)]">
                    <div class="flex items-start justify-between">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 via-emerald-500/10 to-transparent text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                            <i class="bi {{ $stat[2] }}"></i>
                        </div>
                        <span class="rounded-full bg-emerald-600 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-white">Live</span>
                    </div>
                    <p class="mt-5 text-[11px] font-black uppercase tracking-[0.28em] text-slate-400">{{ $stat[0] }}</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $stat[1] }}</h3>
                </div>
            @endforeach
            @endcanPage

            @canPage('accountant.fees.history')
            <div class="group rounded-[28px] border border-slate-100 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(15,23,42,0.45)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_34px_80px_-40px_rgba(15,23,42,0.55)]">
                <div class="flex items-start justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 via-emerald-500/10 to-transparent text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <span class="rounded-full bg-emerald-600 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-white">Live</span>
                </div>
                <p class="mt-5 text-[11px] font-black uppercase tracking-[0.28em] text-slate-400">This Month</p>
                <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900"> {{ number_format($thisMonthRevenue) }}</h3>
            </div>
            @endcanPage
        </section>

        @canPage('accountant.fees.history')
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2 h-50">
            <div class="relative rounded-[28px] bg-white p-6 shadow-[0_24px_70px_-45px_rgba(15,23,42,0.45)]">
                <h4 class="text-lg font-bold mb-4 text-slate-900">Monthly Revenue</h4>
                <div class="h-50 w-full"><canvas id="revenueChart" class="w-full h-full"></canvas></div>
            </div>
            <div class="relative rounded-[28px] bg-white p-6 shadow-[0_24px_70px_-45px_rgba(15,23,42,0.45)]">
                <h4 class="text-lg font-bold mb-4 text-slate-900">Collections Breakdown</h4>
                <div class="h-25 w-full"><canvas id="collectionsChart" class="w-full h-50" height="450px"></canvas></div>
            </div>
        </section>
        @endcanPage

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @canPage('accountant.fees.index')
            <div class="relative overflow-hidden rounded-[28px] border border-emerald-200/70 bg-white p-8 shadow-[0_30px_70px_-45px_rgba(16,185,129,0.4)]">
                <div class="absolute right-4 top-4 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.25em] text-emerald-600">Priority</div>
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Process Payments</h3>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Record fee transactions, issue receipts, and finalize collections in seconds.</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button variant="primary" href="{{ route('accountant.fees.index') }}" class="!bg-emerald-600 hover:!bg-emerald-500 !text-white border-none shadow-lg shadow-emerald-500/20">Open Fee Gateway</x-button>
                    @canPage('accountant.fees.history')
                    <x-button variant="outline" href="{{ route('accountant.fees.history') }}" class="!border-emerald-200 !text-emerald-700 hover:!bg-emerald-50">View Payment History</x-button>
                    @endcanPage
                </div>
            </div>
            @endcanPage

            @canPage('accountant.fees.index')
            <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 p-8 shadow-[0_30px_70px_-45px_rgba(15,23,42,0.35)]">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl shadow-lg shadow-slate-900/20"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Collection Snapshot</h3>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Focus follow-ups on highest outstanding balances and close the gap quickly.</p>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 text-sm font-bold text-slate-700">
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3"><p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Today</p><p class="mt-1 text-lg font-black text-slate-900"> {{ number_format($totalCollected) }}</p></div>
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3"><p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Pending</p><p class="mt-1 text-lg font-black text-rose-600"> {{ number_format($totalPending) }}</p></div>
                </div>
            </div>
            @endcanPage

            @canPage('accountant.fees.history')
            <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-slate-100 p-8 shadow-[0_30px_70px_-45px_rgba(15,23,42,0.35)]">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-600/20"><i class="bi bi-file-earmark-text"></i></div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Generate Reports</h3>
                        <p class="mt-2 text-sm font-semibold text-slate-500">Create detailed financial summaries for custom date ranges.</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button variant="primary" href="{{ route('accountant.fees.history') }}" class="!bg-emerald-600 hover:!bg-emerald-500 !text-white border-none shadow-lg shadow-emerald-500/20">View Reports</x-button>
                </div>
            </div>
            @endcanPage
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const revenueCanvas = document.getElementById('revenueChart');
            if (revenueCanvas) {
                const ctx = revenueCanvas.getContext('2d');
                const revenueLabels = @json($revenueLabels ?? []);
                const revenueData = @json($revenueData ?? []);
                if (revenueData.length > 0) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: revenueLabels,
                            datasets: [{
                                label: 'Monthly Revenue',
                                data: revenueData,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { backgroundColor: 'rgba(15,23,42,0.9)', titleColor: '#ffffff', bodyColor: '#ffffff', cornerRadius: 8 }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { color: '#64748b' } },
                                x: { grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { color: '#64748b' } }
                            }
                        }
                    });
                }
            }

            const collectionsCanvas = document.getElementById('collectionsChart');
            if (collectionsCanvas) {
                const ctx2 = collectionsCanvas.getContext('2d');
                const collectionLabels = @json($collectionLabels ?? []);
                const collectionData = @json($collectionData ?? []);
                if (collectionData.length > 0 && collectionData.some(d => d > 0)) {
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: collectionLabels,
                            datasets: [{ data: collectionData, backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0, hoverOffset: 10 }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { color: '#64748b', font: { size: 12 } } },
                                tooltip: { backgroundColor: 'rgba(15,23,42,0.9)', titleColor: '#ffffff', bodyColor: '#ffffff', cornerRadius: 8 }
                            },
                            cutout: '60%'
                        }
                    });
                }
            }
        });
    </script>
@endpush
