@extends('layouts.app')

@section('header_title', 'Fees Management')

@section('content')
    <div class="space-y-8 animate-fade-in">
        <x-page-header 
            title="Fees Management" 
            subtitle="Manage student fee collections, track dues, and monitor financial health." 
            icon="bi-wallet2"
        >
            <x-slot:action>
                <div class="flex items-center gap-3">
                    <button onclick="exportCSV()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                        <i class="bi bi-download"></i> Export Data
                    </button>
                </div>
            </x-slot:action>
        </x-page-header>

        <!-- ─── Financial Status ─── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100 group-hover:scale-110 transition-transform duration-300">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded border border-slate-100">Projected</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Total Expected</p>
                <h3 id="statTotal" class="text-2xl font-black text-slate-900 tracking-tight relative z-10">₹0</h3>
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            </div>

            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100 group-hover:scale-110 transition-transform duration-300">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Success</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Total Collected</p>
                <h3 id="statCollected" class="text-2xl font-black text-slate-900 tracking-tight relative z-10">₹0</h3>
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            </div>

            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl border border-amber-100 group-hover:scale-110 transition-transform duration-300">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <span class="text-[10px] font-bold text-amber-500 uppercase tracking-widest bg-amber-50 px-2 py-0.5 rounded border border-amber-100">Awaiting</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Partial Payments</p>
                <h3 id="statPending" class="text-2xl font-black text-slate-900 tracking-tight relative z-10">₹0</h3>
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-amber-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            </div>

            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm relative overflow-hidden group">
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl border border-rose-100 group-hover:scale-110 transition-transform duration-300">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Attention</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Total Overdue</p>
                <h3 id="statUnpaid" class="text-2xl font-black text-slate-900 tracking-tight relative z-10">₹0</h3>
                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-rose-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            </div>
        </div>

        <!-- ─── Search & Toggles ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-8 sticky top-20 z-30">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="relative flex-1 min-w-[300px] h-11 group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" id="feeSearch" placeholder="Search by student name or roll number..."
                        class="w-full h-full bg-slate-50 border-slate-100 rounded-xl pl-11 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium">
                </div>

                <div class="flex items-center gap-1 p-1 bg-slate-50 rounded-xl border border-slate-100">
                    <button class="filter-pill active" data-status="all">All</button>
                    <div class="w-px h-6 bg-slate-200 mx-1"></div>
                    <button class="filter-pill" data-status="paid">Paid</button>
                    <button class="filter-pill" data-status="partial">Partial</button>
                    <button class="filter-pill" data-status="pending">Unpaid</button>
                </div>
            </div>
        </div>

        <!-- ─── Ledger Table ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="px-6 py-4">Student Identity</th>
                            <th class="px-6 py-4">Academic Details</th>
                            <th class="px-6 py-4">Total Fee</th>
                            <th class="px-6 py-4">Paid</th>
                            <th class="px-6 py-4">Balance</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="feesTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-24">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div class="h-12 w-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Synchronizing Ledgers...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm" id="feesPagination"></div>
    </div>

    @push('styles')
    <style>
        .filter-pill {
            @apply px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-lg text-slate-500 transition-all cursor-pointer;
        }
        .filter-pill:hover {
            @apply text-indigo-600 bg-indigo-50/50;
        }
        .filter-pill.active {
            @apply bg-white text-indigo-700 shadow-sm border border-slate-100;
        }
    </style>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
                tr.className = 'hover:bg-slate-50/50 transition-colors group';
                
                const studentName = fee.student?.user?.name || 'Unknown';
                const rollNo = fee.student?.roll_number || 'N/A';
                const courseName = fee.student?.course?.name || 'N/A';
                const semesterName = fee.student?.semester?.name || 'N/A';
                const total = Number(fee.total_amount || 0).toLocaleString();
                const paid = Number(fee.paid_amount || 0).toLocaleString();
                const pending = Number(fee.pending_amount || 0);
                const status = fee.status || 'pending';

                const badgeClasses = {
                    paid: 'bg-emerald-50 text-emerald-600 border-emerald-100',
                    partial: 'bg-amber-50 text-amber-600 border-amber-100',
                    pending: 'bg-rose-50 text-rose-500 border-rose-100',
                    overdue: 'bg-rose-100 text-rose-700 border-rose-200'
                }[status] || 'bg-slate-50 text-slate-400 border-slate-100';

                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-bold text-slate-400 text-xs">
                                ${studentName.charAt(0)}
                            </div>
                            <div>
                                <span class="text-sm font-bold text-slate-700 block group-hover:text-indigo-600 transition-colors">${studentName}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 block">Roll: ${rollNo}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-600 block">${courseName}</span>
                        <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mt-0.5 block">${semesterName}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-black text-slate-900 tracking-tight">₹${total}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-black text-emerald-600 tracking-tight">₹${paid}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-black ${pending > 0 ? 'text-rose-500' : 'text-slate-300'} tracking-tight">₹${pending.toLocaleString()}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border ${badgeClasses}">
                            ${status}
                        </span>
                    </td>
                `;
                return tr;
            }

            async function fetchFees(page = 1) {
                feesTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-24">
                            <div class="flex flex-col items-center justify-center gap-4">
                                <div class="h-12 w-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Refreshing Ledger...</span>
                            </div>
                        </td>
                    </tr>`;
                try {
                    const params = new URLSearchParams();
                    params.set('page', page);
                    params.set('per_page', 20);
                    if (feeSearch.value.trim()) params.set('search', feeSearch.value.trim());
                    if (currentStatus !== 'all') params.set('status', currentStatus);
                    
                    const response = await fetch(`/admin/fees/data?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) throw new Error('Network synchronization failed');
                    const payload = await response.json();
                    
                    rows = payload.data.fees;
                    setSummary(payload.data.summary);
                    renderTable(rows);
                    renderPagination(payload.data.meta);
                } catch (error) {
                    feesTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-24 text-rose-500 font-bold text-xs uppercase tracking-widest">System Error: ${error.message}</td></tr>`;
                    console.error(error);
                }
            }

            function renderTable(records) {
                if (!records.length) {
                    feesTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-32">
                                <i class="bi bi-folder-x text-5xl text-slate-100 mb-4 block"></i>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No entries found matching criteria</span>
                            </td>
                        </tr>`;
                    return;
                }
                feesTableBody.innerHTML = '';
                records.forEach(fee => feesTableBody.appendChild(createFeeRow(fee)));
            }

            function renderPagination(meta) {
                feesPagination.innerHTML = '';
                if (!meta || meta.last_page <= 1) {
                    feesPagination.style.display = 'none';
                    return;
                }
                feesPagination.style.display = 'flex';

                const infoBox = document.createElement('div');
                infoBox.className = 'flex items-center gap-3';
                infoBox.innerHTML = `
                    <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center font-black text-xs border border-slate-100">${meta.total}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Ledgers Indexed</div>
                `;

                const btnContainer = document.createElement('div');
                btnContainer.className = 'flex items-center gap-2';

                const prev = document.createElement('button');
                prev.className = 'h-10 px-4 rounded-xl border border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-30 transition-all';
                prev.innerHTML = '<i class="bi bi-chevron-left mr-2"></i>Prev';
                prev.disabled = meta.current_page === 1;
                prev.onclick = () => { currentPage = Math.max(1, meta.current_page - 1); fetchFees(currentPage); };

                const next = document.createElement('button');
                next.className = 'h-10 px-4 rounded-xl border border-slate-200 text-[10px] font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50 disabled:opacity-30 transition-all';
                next.innerHTML = 'Next<i class="bi bi-chevron-right ml-2"></i>';
                next.disabled = meta.current_page === meta.last_page;
                next.onclick = () => { currentPage = Math.min(meta.last_page, meta.current_page + 1); fetchFees(currentPage); };

                btnContainer.appendChild(prev);
                btnContainer.appendChild(next);
                
                feesPagination.appendChild(infoBox);
                feesPagination.appendChild(btnContainer);
            }

            fetchFees();

            let searchTimer = null;
            feeSearch.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    currentPage = 1;
                    fetchFees();
                }, 400);
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

            window.exportCSV = function() {
                if (!rows.length) {
                    alert('No ledger entries available for offline export.');
                    return;
                }
                const headers = ['Student', 'Roll', 'Program', 'Term', 'Expected', 'Paid', 'Balance', 'Status'];
                const lines = [headers.join(',')];
                rows.forEach(item => {
                    lines.push([
                        `"${(item.student?.user?.name || '').replace(/"/g, '""')}"`,
                        `"${(item.student?.roll_number || '')}"`,
                        `"${(item.student?.course?.name || '')}"`,
                        `"${(item.student?.semester?.name || '')}"`,
                        item.total_amount,
                        item.paid_amount,
                        item.pending_amount,
                        item.status
                    ].join(','));
                });
                const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `fees_ledger_${new Date().toISOString().slice(0, 10)}.csv`;
                a.click();
                URL.revokeObjectURL(url);
            };
        });
    </script>
@endsection
