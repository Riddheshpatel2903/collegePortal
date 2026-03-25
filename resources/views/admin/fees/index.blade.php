@extends('layouts.app')

@section('header_title', 'Fees Management')

@section('content')
    {{-- Page Header --}}
    <div class="space-y-8">
    <x-page-header 
        title="Fees Nexus" 
        subtitle="Manage financial collections and student dues" 
        tag="Financial Control"
        icon="bi-wallet2"
    >
        <x-slot:actions>
            <x-button variant="outline" onclick="exportCSV()" icon="bi-download">
                Export CSV
            </x-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card p-6 border-l-4 border-l-violet-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-wallet-fill"></i>
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Target</span>
            </div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Expected</div>
            <div id="statTotal" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-emerald-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Success</span>
            </div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Collected</div>
            <div id="statCollected" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-amber-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending</span>
            </div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Partial/Waiting</div>
            <div id="statPending" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
        </div>

        <div class="glass-card p-6 border-l-4 border-l-rose-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-12 w-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Critical</span>
            </div>
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Unpaid Dues</div>
            <div id="statUnpaid" class="text-2xl font-black text-slate-800 tracking-tight">₹0</div>
        </div>
    </div>

    {{-- Filters & Controls --}}
    <div class="glass-card p-4 border border-white/60 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="relative flex-1 group">
                <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                <input type="text" id="feeSearch" placeholder="Search by student name or roll number..."
                    class="input-premium pl-12 h-12">
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button class="filter-pill active" data-status="all">All Records</button>
                <button class="filter-pill" data-status="paid">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span> Paid
                </button>
                <button class="filter-pill" data-status="partial">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 inline-block"></span> Partial
                </button>
                <button class="filter-pill" data-status="pending">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500 inline-block"></span> Unpaid
                </button>
            </div>
        </div>
    </div>

    {{-- Fee Table --}}
    <div class="glass-card overflow-hidden">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Student Profile</th>
                    <th>Academic Context</th>
                    <th>Total Pay</th>
                    <th>Paid Amount</th>
                    <th>Remaining</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody id="feesTableBody">
                <tr>
                    <td colspan="6" class="text-center text-slate-400 py-20" id="feesLoadingRow">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <span class="h-8 w-8 rounded-full border-4 border-violet-500 border-t-transparent animate-spin"></span>
                            <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Synchronizing Ledgers...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4" id="feesPagination"></div>
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
            tr.className = 'group hover:bg-slate-50/50 transition-colors';
            tr.dataset.status = fee.status || 'unknown';

            const studentName = fee.student?.user?.name || 'N/A';
            const rollNo = fee.student?.roll_number || '-';
            const courseName = fee.student?.course?.name || '-';
            const semesterName = fee.student?.semester?.name || '-';
            const total = Number(fee.total_amount || 0).toLocaleString();
            const paid = Number(fee.paid_amount || 0).toLocaleString();
            const pending = Number(fee.pending_amount || 0).toLocaleString();
            const status = fee.status || 'pending';

            const badgeClasses = {
                paid: 'text-emerald-700 bg-emerald-100',
                partial: 'text-amber-700 bg-amber-50',
                pending: 'text-rose-700 bg-rose-50',
                overdue: 'text-rose-700 bg-rose-100'
            }[status] || 'text-slate-600 bg-slate-100';

            tr.innerHTML = `
                <td>
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(studentName)}&background=f8fafc&color=64748b&size=32" class="h-9 w-9 rounded-xl border border-slate-200" alt="">
                        <div>
                            <div class="text-sm font-bold text-slate-800">${studentName}</div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">${rollNo}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-xs font-bold text-slate-700 leading-none mb-1">${courseName}</div>
                    <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest leading-none">${semesterName}</div>
                </td>
                <td><span class="text-sm font-black text-slate-700 tracking-tight">₹${total}</span></td>
                <td><span class="text-sm font-black text-emerald-600 tracking-tight">₹${paid}</span></td>
                <td><span class="text-sm font-black ${Number(fee.pending_amount || 0) > 0 ? 'text-rose-500' : 'text-slate-400'} tracking-tight">₹${pending}</span></td>
                <td class="text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest ${badgeClasses}">
                        ${status}
                    </span>
                </td>
            `;
            return tr;
        }

        async function fetchFees(page = 1) {
            feesTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-20">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <span class="h-8 w-8 rounded-full border-4 border-slate-200 border-t-violet-500 animate-spin"></span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Refreshing Data...</span>
                        </div>
                    </td>
                </tr>`;
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

                if (!response.ok) throw new Error('Network error');
                const payload = await response.json();
                if (payload.status !== 'success') throw new Error(payload.message);

                rows = payload.data.fees;
                setSummary(payload.data.summary);
                renderTable(rows);
                renderPagination(payload.data.meta);
            } catch (error) {
                feesTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-20 text-rose-500 font-bold text-sm uppercase tracking-widest">Link Failure: ${error.message}</td></tr>`;
                console.error(error);
            }
        }

        function renderTable(records) {
            if (!records.length) {
                feesTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-32">
                            <i class="bi bi-inbox text-4xl text-slate-200 mb-4 block"></i>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">No matching records synchronization</span>
                        </td>
                    </tr>`;
                return;
            }
            feesTableBody.innerHTML = '';
            records.forEach(fee => feesTableBody.appendChild(createFeeRow(fee)));
        }

        function renderPagination(meta) {
            feesPagination.innerHTML = '';
            if (!meta || meta.last_page <= 1) return;

            const container = document.createElement('div');
            container.className = 'flex items-center gap-2';

            const prev = document.createElement('button');
            prev.className = 'h-10 px-4 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-all';
            prev.innerHTML = '<i class="bi bi-chevron-left mr-2"></i>Previous';
            prev.disabled = meta.current_page === 1;
            prev.onclick = () => { currentPage = Math.max(1, meta.current_page - 1); fetchFees(currentPage); };

            const next = document.createElement('button');
            next.className = 'h-10 px-4 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-all';
            next.innerHTML = 'Next<i class="bi bi-chevron-right ml-2"></i>';
            next.disabled = meta.current_page === meta.last_page;
            next.onclick = () => { currentPage = Math.min(meta.last_page, meta.current_page + 1); fetchFees(currentPage); };

            feesPagination.appendChild(prev);
            const info = document.createElement('span');
            info.className = 'text-[10px] font-black text-slate-400 uppercase tracking-widest';
            info.textContent = `Node ${meta.current_page} of ${meta.last_page} • ${meta.total} Indices`;
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
