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
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Finance <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Command Control</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium">Actively process incoming fees, monitor arrears, and print official receipts.</p>
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
        <x-table :headers="['Student', 'Course & Sem', 'Total', 'Paid', 'Pending', 'Status', 'Action']">
            @forelse($fees as $fee)
                @php
                    $student = $fee->student;
                    $status = $fee->status;
                    $badgeType = match($status) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'pending' => 'danger',
                        'overdue' => 'danger',
                        default => 'default',
                    };
                @endphp
                <tr data-status="{{ $status }}" class="group/row">
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->user->name ?? 'F') }}&background=ede9fe&color=7c3aed&size=32"
                                class="h-9 w-9 rounded-xl ring-2 ring-violet-50" alt="">
                            <div>
                                <div class="text-sm font-bold text-slate-800">{{ $student->user->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400 font-bold tracking-tight">{{ $student->roll_number ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-xs font-bold text-slate-700 leading-none mb-1">{{ $student->course->name ?? '—' }}</div>
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-widest">{{ $student->semester->name ?? '—' }}</div>
                    </td>
                    <td>
                        <span class="text-sm font-black text-slate-700">₹{{ number_format($fee->total_amount) }}</span>
                    </td>
                    <td>
                        <span class="text-sm font-black text-emerald-600">₹{{ number_format($fee->paid_amount) }}</span>
                    </td>
                    <td>
                        <span class="text-sm font-black {{ $fee->pending_amount > 0 ? 'text-rose-500' : 'text-slate-400' }}">₹{{ number_format($fee->pending_amount) }}</span>
                    </td>
                    <td class="text-center">
                        <x-badge :type="$badgeType">{{ ucfirst($status) }}</x-badge>
                    </td>
                    <td class="text-center">
                        <x-button type="button" class="js-fee-action" data-fee-id="{{ $fee->id }}" data-name="{{ addslashes($student->user->name ?? 'N/A') }}" data-total="{{ $fee->total_amount }}" data-paid="{{ $fee->paid_amount }}" variant="secondary" size="sm" icon="bi-cash-stack">
                            Pay
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-sm text-slate-400 py-12">
                        <i class="bi bi-inbox text-4xl block mb-2 opacity-20"></i>
                        No fee records found.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-5">
            {{ $fees->links() }}
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden" x-data="{ open: false }">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300" onclick="closeEditModal()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white/95 backdrop-blur-xl shadow-2xl flex flex-col transform transition-transform duration-500 translate-x-full" id="modalContent">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-200">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Record Payment</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest" id="modalStudentName">Student Name</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto p-8 space-y-8">
                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Commitment</span>
                        <span class="text-sm font-black text-slate-700" id="modalTotal">₹0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Paid Amount</span>
                        <span class="text-sm font-black text-emerald-600" id="modalPaid">₹0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Outstanding</span>
                        <span class="text-sm font-black text-rose-500" id="modalDue">₹0</span>
                    </div>
                    <div class="relative h-2.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-700 ease-out shadow-[0_0_10px_rgba(16,185,129,0.3)]" id="modalProgressBar" style="width:0%"></div>
                    </div>
                </div>

                <form id="editForm" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="payment_amount" id="hiddenPaymentAmount">
                    <input type="hidden" name="paid_amount" id="hiddenPaidAmount">

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Payment Amount</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-lg group-focus-within:text-emerald-600 transition-colors">₹</span>
                            <input type="number" name="payment_amount" id="modalPaymentInput" placeholder="0.00"
                                class="w-full pl-10 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl text-lg font-black text-slate-700 focus:bg-white focus:ring-[6px] focus:ring-emerald-500/5 focus:border-emerald-200 transition-all outline-none"
                                min="0.1" step="0.1" required>
                        </div>
                        <div class="flex justify-between items-center px-1">
                            <span class="text-[10px] text-slate-400 font-bold">Projected Balance</span>
                            <span id="modalRemaining" class="text-xs font-black text-rose-500">₹0</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Payment Mode</label>
                        <select name="payment_mode" id="modalPaymentMode" class="input-premium w-full">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Receipt Number (Optional)</label>
                        <input type="text" name="receipt_number" class="input-premium" placeholder="RCT-20260301-001">
                    </div>
                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Remarks</label>
                        <input type="text" name="remarks" class="input-premium" placeholder="Paid at college counter">
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-slate-900 text-white text-sm font-bold rounded-2xl hover:bg-emerald-600 hover:shadow-2xl hover:shadow-emerald-500/30 transition-all duration-300 flex items-center justify-center gap-3 group active:scale-[0.98]">
                        <i class="bi bi-shield-check text-lg transition-transform group-hover:scale-110"></i>
                         Authorize Payment
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .filter-pill {
            @apply px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl border border-slate-200 text-slate-500 bg-white transition-all cursor-pointer flex items-center gap-2;
        }
        .filter-pill:hover {
            @apply border-emerald-200 text-emerald-600 shadow-lg shadow-emerald-500/10;
        }
        .filter-pill.active {
            @apply bg-slate-900 text-white border-slate-900 shadow-xl shadow-slate-900/20;
        }
    </style>
    @endpush

    <script>
        // ── Search ──
        if (document.getElementById('feeSearch')) {
            document.getElementById('feeSearch').addEventListener('input', applyFilters);
        }

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

        // ── Edit Modal ──
        let currentTotal = 0;
        let currentPaid = 0;
        let currentDue = 0;

        window.openEditModal = function(id, name, total, paid) {
            currentTotal = total;
            currentPaid = paid;
            currentDue = total - paid;

            const modalStudentName = document.getElementById('modalStudentName');
            const modal = document.getElementById('editModal')
                || (modalStudentName && modalStudentName.closest('#editModal'))
                || document.querySelector('#editModal')
                || document.querySelector('.fixed.inset-0.z-50');

            const content = document.getElementById('modalContent')
                || (modal && modal.querySelector('#modalContent'))
                || (modal && modal.querySelector('.absolute.right-0'));

            const modalTotal = document.getElementById('modalTotal');
            const modalPaid = document.getElementById('modalPaid');
            const modalDue = document.getElementById('modalDue');
            const modalProgressBar = document.getElementById('modalProgressBar');
            const modalRemaining = document.getElementById('modalRemaining');
            const modalPaymentInput = document.getElementById('modalPaymentInput');
            const hiddenPaidAmount = document.getElementById('hiddenPaidAmount');
            const editForm = document.getElementById('editForm');

            if (!modal || !content || !modalStudentName || !modalTotal || !modalPaid || !modalDue || !modalProgressBar || !modalRemaining || !modalPaymentInput || !hiddenPaidAmount || !editForm) {
                console.error('Fee modal DOM element missing, attempting to fallback', {
                    modal,
                    content,
                    modalStudentName,
                    modalTotal,
                    modalPaid,
                    modalDue,
                    modalProgressBar,
                    modalRemaining,
                    modalPaymentInput,
                    hiddenPaidAmount,
                    editForm,
                });

                if (modalStudentName && !modal) {
                    const fallbackModal = document.createElement('div');
                    fallbackModal.id = 'editModal';
                    fallbackModal.className = 'fixed inset-0 z-50 hidden';
                    fallbackModal.innerHTML = `
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300" onclick="window.closeEditModal()"></div>
                        <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white/95 backdrop-blur-xl shadow-2xl flex flex-col transform transition-transform duration-500 translate-x-full" id="modalContent"></div>
                    `;
                    document.body.appendChild(fallbackModal);
                    const view = document.getElementById('editModal');
                    const contentView = document.getElementById('modalContent');
                    if (view && contentView) {
                        console.warn('Fallback fee modal created');
                        // we won't restore full structure, but at least avoid crash.
                    }
                }

                return;
            }

            modalStudentName.textContent = name;
            modalTotal.textContent = '₹' + Number(total).toLocaleString('en-IN');
            modalPaid.textContent = '₹' + Number(paid).toLocaleString('en-IN');
            modalDue.textContent = '₹' + Number(currentDue).toLocaleString('en-IN');

            const progressPercent = total > 0 ? Math.min(100, (paid / total) * 100) : 0;
            modalProgressBar.style.width = progressPercent + '%';

            modalRemaining.textContent = '₹' + Number(currentDue).toLocaleString('en-IN');
            modalPaymentInput.value = '';
            modalPaymentInput.max = Math.max(0, currentDue);
            document.getElementById('modalPaymentMode').value = 'cash';
            hiddenPaidAmount.value = paid;
            document.getElementById('hiddenPaymentAmount').value = '';
            editForm.action = '/accountant/fees/' + id;

            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('translate-x-full');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        window.closeEditModal = function() {
            const content = document.getElementById('modalContent');
            if (!content) return;
            content.classList.add('translate-x-full');
            setTimeout(() => {
                document.getElementById('editModal').classList.add('hidden');
            }, 500);
            document.body.style.overflow = '';
        }

        document.getElementById('modalPaymentInput').addEventListener('input', function () {
            const payment = parseFloat(this.value) || 0;
            const newTotalPaid = currentPaid + payment;
            const newRemaining = Math.max(0, currentTotal - newTotalPaid);

            document.getElementById('hiddenPaymentAmount').value = payment;
            document.getElementById('hiddenPaidAmount').value = Math.min(newTotalPaid, currentTotal);
            document.getElementById('modalRemaining').textContent = '₹' + newRemaining.toLocaleString('en-IN');
            document.getElementById('modalProgressBar').style.width = Math.min(100, (newTotalPaid / currentTotal * 100)) + '%';
        });

        // Close on Escape
        document.addEventListener('keydown', e => { if (e.key === 'Escape') window.closeEditModal(); });

        // ── Click delegation fallback for Pay/Edit
        document.addEventListener('click', function(event) {
            const button = event.target.closest('.js-fee-action');
            if (!button) return;

            event.preventDefault();

            const feeId = button.getAttribute('data-fee-id');
            const studentName = button.getAttribute('data-name') || 'N/A';
            const total = Number(button.getAttribute('data-total') || 0);
            const paid = Number(button.getAttribute('data-paid') || 0);

            if (typeof window.openEditModal !== 'function') {
                console.error('openEditModal not defined');
                return;
            }

            window.openEditModal(feeId, studentName, total, paid);
        });

        // ── CSV Export ──
        function exportCSV() {
            const rows = [['Student', 'Roll Number', 'Course', 'Semester', 'Total', 'Paid', 'Pending', 'Status']];
            document.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                const cells = row.querySelectorAll('td');
                if (cells.length < 7) return;
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
