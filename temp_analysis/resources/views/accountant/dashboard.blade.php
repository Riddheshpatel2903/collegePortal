@extends('layouts.app')

@section('header_title', 'Financial Command Center')

@section('content')
<div class="space-y-12">
    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-2xl p-10 overflow-hidden shadow-2xl text-white">
        <div class="relative z-10">
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Financial Command Center</h2>
            <p class="mt-2 text-lg opacity-90">Quick overview of revenue, collections & pending dues</p>
        </div>
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <!-- subtle abstract pattern for premium feel -->
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" fill="none" viewBox="0 0 800 600">
                <path d="M0 0L800 600" stroke="rgba(255,255,255,0.1)" stroke-width="200" />
            </svg>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach([
            ['Expected Revenue', '₹' . number_format($totalExpected), 'bi-wallet2', 'emerald'],
            ['Total Collected', '₹' . number_format($totalCollected), 'bi-safe-fill', 'emerald'],
            ['Outstanding Dues', '₹' . number_format($totalPending), 'bi-exclamation-octagon-fill', 'emerald'],
        ] as $stat)
        <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-lg hover:shadow-xl transition-shadow">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-{{ $stat[3] }}-100 text-{{ $stat[3] }}-600 flex items-center justify-center text-xl">
                    <i class="bi {{ $stat[2] }}"></i>
                </div>
                <p class="ml-4 text-sm font-semibold text-slate-500 uppercase tracking-wide">{{ $stat[0] }}</p>
            </div>
            <h3 class="text-2xl font-bold text-slate-900">{{ $stat[1] }}</h3>
        </div>
        @endforeach
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="p-6 bg-white rounded-3xl shadow-lg">
            <h4 class="text-lg font-bold mb-4">Monthly Revenue</h4>
            <canvas id="revenueChart" class="w-full h-64"></canvas>
        </div>
        <div class="p-6 bg-white rounded-3xl shadow-lg">
            <h4 class="text-lg font-bold mb-4">Collections Breakdown</h4>
            <canvas id="collectionsChart" class="w-full h-64"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-lg hover:shadow-xl">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <h3 class="ml-4 text-lg font-bold text-slate-900">Process Payments</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">Access fee roster to log incoming transactions and authorize student payments instantly.</p>
            <x-button variant="primary" href="{{ route('accountant.fees.index') }}">Open Fee Gateway &rarr;</x-button>
        </div>
        <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-lg hover:shadow-xl">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <h3 class="ml-4 text-lg font-bold text-slate-900">Generate Reports</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">Create detailed financial summaries for custom date ranges.</p>
            <x-button variant="primary" href="#">View Reports &rarr;</x-button>
        </div>
        <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-lg hover:shadow-xl">
            <div class="flex items-center mb-4">
                <div class="h-12 w-12 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h3 class="ml-4 text-lg font-bold text-slate-900">Analytics</h3>
            </div>
            <p class="text-sm text-slate-500 mb-4">Dive deeper into trends and performance over time.</p>
            <x-button variant="primary" href="#">Explore Charts &rarr;</x-button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- charts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('revenueChart')) {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($revenueLabels ?? []),
                        datasets: [{
                            label: 'Revenue',
                            data: @json($revenueData ?? []),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
            if (document.getElementById('collectionsChart')) {
                const ctx2 = document.getElementById('collectionsChart').getContext('2d');
                new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: @json($collectionLabels ?? []),
                        datasets: [{
                            data: @json($collectionData ?? []),
                            backgroundColor: ['#10b981','#06b6d4','#818cf8'],
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        });
    </script>
@endpush
