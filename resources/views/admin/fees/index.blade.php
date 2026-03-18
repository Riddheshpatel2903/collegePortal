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
            <x-card class="border-l-4 border-l-violet-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center text-xl">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                    <x-badge>Total Expected</x-badge>
                </div>
                <div id="statTotal" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
            </x-card>
            <x-card class="border-l-4 border-l-emerald-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <x-badge>Collected</x-badge>
                </div>
                <div id="statCollected" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
            </x-card>
            <x-card class="border-l-4 border-l-amber-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <x-badge>Pending</x-badge>
                </div>
                <div id="statPending" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
            </x-card>
            <x-card class="border-l-4 border-l-rose-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <x-badge>Unpaid</x-badge>
                </div>
                <div id="statUnpaid" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
            </x-card>
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
            <tbody id="feesTableBody">
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-10" id="feesLoadingRow">
                        <div class="flex items-center justify-center gap-2">
                            <span class="h-4 w-4 rounded-full border-2 border-violet-500 border-t-transparent animate-spin"></span>
                            Fetching fee records...
                        </div>
                    </td>
                </tr>
            </tbody>
        </x-table>
        <div class="mt-4 flex justify-between items-center" id="feesPagination"></div>
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
        const feesTableBody = document.getElementById('feesTableBody');
        const feesPagination = document.getElementById('feesPagination');
        const feeSearch = document.getElementById('feeSearch');
        const statusButtons = document.querySelectorAll('.filter-pill');

        let currentPage = 1;
        let currentStatus = 'all';
        let rows = [];

        function setSummary(summary) {
            document.getElementById('statTotal').textContent = `₹${Number(summary.total).toLocaleString()}`;
            document.getElementById('statCollected').textContent = `₹${Number(summary.collected).toLocaleString()}`;
            document.getElementById('statPending').textContent = `₹${Number(summary.pending).toLocaleString()}`;
            document.getElementById('statUnpaid').textContent = `₹${Number(summary.unpaid).toLocaleString()}`;
        }

        function createFeeRow(fee) {
            const tr = document.createElement('tr');
            tr.dataset.status = fee.status || 'unknown';

            const studentName = fee.student?.user?.name || 'N/A';
            const rollNo = fee.student?.roll_number || '-';
            const courseName = fee.student?.course?.name || '-';
            const semesterName = fee.student?.semester?.name || '-';
            const total = Number(fee.total_amount || 0).toLocaleString();
            const paid = Number(fee.paid_amount || 0).toLocaleString();
            const pending = Number(fee.pending_amount || 0).toLocaleString();
            const status = fee.status || 'pending';

            const badgeType = {
                paid: 'success',
                partial: 'warning',
                pending: 'danger',
                overdue: 'danger'
            }[status] || 'default';

            tr.innerHTML = `
                <td>
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(studentName)}&background=ede9fe&color=7c3aed&size=32" class="h-9 w-9 rounded-xl ring-2 ring-violet-50" alt="">
                        <div>
                            <div class="text-sm font-bold text-slate-800">${studentName}</div>
                            <div class="text-[10px] text-slate-400 font-bold tracking-tight">${rollNo}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-xs font-bold text-slate-700 leading-none mb-1">${courseName}</div>
                    <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">${semesterName}</div>
                </td>
                <td><span class="text-sm font-black text-slate-700">₹${total}</span></td>
                <td><span class="text-sm font-black text-emerald-600">₹${paid}</span></td>
                <td><span class="text-sm font-black ${Number(fee.pending_amount || 0) > 0 ? 'text-rose-500' : 'text-slate-400'}">₹${pending}</span></td>
                <td class="text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${badgeType === 'success' ? 'text-emerald-700 bg-emerald-100' : badgeType === 'warning' ? 'text-amber-700 bg-amber-100' : badgeType === 'danger' ? 'text-rose-700 bg-rose-100' : 'text-slate-600 bg-slate-100'}">${status}</span></td>
            `;
            return tr;
        }

        async function fetchFees(page = 1) {
            feesTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-10">Loading fee records...</td></tr>`;
            try {
                const params = new URLSearchParams();
                params.set('page', page);
                params.set('per_page', 20);
                if (feeSearch.value.trim()) {
                    params.set('search', feeSearch.value.trim());
                }
                if (currentStatus !== 'all') {
                    params.set('status', currentStatus);
                }
                const response = await fetch(`/admin/fees/data?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error('Unable to load fee data.');
                }

                const payload = await response.json();
                if (payload.status !== 'success') {
                    throw new Error(payload.message || 'Unexpected server response.');
                }

                rows = payload.data.fees;
                setSummary(payload.data.summary);
                renderTable(rows);
                renderPagination(payload.data.meta);

            } catch (error) {
                feesTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-red-500">${error.message}</td></tr>`;
                console.error(error);
            }
        }

        function renderTable(records) {
            if (!records.length) {
                feesTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-10">No Fee records found</td></tr>`;
                return;
            }

            feesTableBody.innerHTML = '';
            records.forEach(fee => feesTableBody.appendChild(createFeeRow(fee)));
        }

        function renderPagination(meta) {
            feesPagination.innerHTML = '';
            if (!meta || meta.last_page <= 1) {
                return;
            }

            const prev = document.createElement('button');
            prev.className = 'btn btn-sm';
            prev.textContent = 'Previous';
            prev.disabled = meta.current_page === 1;
            prev.onclick = () => { currentPage = Math.max(1, meta.current_page - 1); fetchFees(currentPage); };

            const next = document.createElement('button');
            next.className = 'btn btn-sm';
            next.textContent = 'Next';
            next.disabled = meta.current_page === meta.last_page;
            next.onclick = () => { currentPage = Math.min(meta.last_page, meta.current_page + 1); fetchFees(currentPage); };

            feesPagination.appendChild(prev);
            const info = document.createElement('span');
            info.className = 'text-sm text-slate-500';
            info.textContent = `Page ${meta.current_page} of ${meta.last_page} (${meta.total} rows)`;
            feesPagination.appendChild(info);
            feesPagination.appendChild(next);
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchFees();

            feeSearch.addEventListener('input', () => {
                currentPage = 1;
                fetchFees();
            });

            statusButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    statusButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentStatus = this.dataset.status;
                    currentPage = 1;
                    fetchFees();
                });
            });
        });

        function exportCSV() {
            if (!rows.length) {
                alert('No records available for export.');
                return;
            }

            const headers = ['Student', 'Roll Number', 'Course', 'Semester', 'Total', 'Paid', 'Pending', 'Status'];
            const lines = [headers.join(',')];

            rows.forEach(item => {
                const csvRow = [
                    `"${(item.student?.user?.name || '').replace(/"/g, '""')}"`,
                    `"${(item.student?.roll_number || '').replace(/"/g, '""')}"`,
                    `"${(item.student?.course?.name || '').replace(/"/g, '""')}"`,
                    `"${(item.student?.semester?.name || '').replace(/"/g, '""')}"`,
                    item.total_amount,
                    item.paid_amount,
                    item.pending_amount,
                    item.status,
                ];
                lines.push(csvRow.join(','));
            });

            const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `fees_export_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        window.exportCSV = exportCSV;
    </script>
@endsection
