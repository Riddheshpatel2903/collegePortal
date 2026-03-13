@extends('layouts.app')

@section('header_title', 'Student Management')

@section('content')
    {{-- Toast Notification --}}
    <div id="ajaxToast" class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white
                               translate-y-20 opacity-0 transition-all duration-500 pointer-events-none"
        style="min-width:260px">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>

    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Students <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600 ">Management</span>
                    </h2>
                    <p class="text-lg text-slate-400 font-medium">Manage student records and information.</p>
                </div>

                <div class="flex items-center gap-4">
                    <x-button href="{{ route('admin.students.create') }}" icon="bi-person-plus-fill" size="lg">
                        Enroll Student
                    </x-button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-card class="border-l-4 border-l-violet-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-violet-500/10 text-violet-600 flex items-center justify-center text-2xl">
                            <i class="bi bi-people"></i>
                        </div>
                        <x-badge>Total Population</x-badge>
                    </div>
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">{{ number_format($stats['total']) }}
                    </div>
                </x-card>

                <x-card class="border-l-4 border-l-emerald-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-2xl">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <x-badge>Currently Active</x-badge>
                    </div>
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">
                        {{ number_format($stats['active']) }}
                    </div>
                </x-card>

                <x-card class="border-l-4 border-l-amber-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-2xl">
                            <i class="bi bi-stars"></i>
                        </div>
                        <x-badge>Monthly Growth</x-badge>
                    </div>
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">+{{ number_format($stats['new']) }}
                    </div>
                </x-card>

                <x-card class="border-l-4 border-l-rose-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="h-12 w-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-2xl">
                            <i class="bi bi-person-dash"></i>
                        </div>
                        <x-badge>Archived</x-badge>
                    </div>
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">
                        {{ number_format($stats['inactive']) }}
                    </div>
                </x-card>
            </div>

            <div class="sticky top-20 z-30">
                <x-card class="p-2 border border-white/60 shadow-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative flex-1 min-w-[300px] h-12 group">
                            <i
                                class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                            <input type="text" id="search"
                                placeholder="Search by ID, name, roll number, or GTU enrollment..."
                                class="input-premium pl-12 h-full">
                            <div id="searchLoader" class="hidden absolute right-5 top-1/2 -translate-y-1/2">
                                <div
                                    class="animate-spin h-4 w-4 border-2 border-violet-500 border-t-transparent rounded-full">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 p-1 bg-slate-50 rounded-xl border border-slate-100">
                            <select id="department_id"
                                class="bg-transparent border-none text-[13px] font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[150px]">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>

                            <div class="h-6 w-[1px] bg-slate-200"></div>

                            <select id="current_year"
                                class="bg-transparent border-none text-[13px] font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[150px]">
                                <option value="">All Years</option>
                            </select>

                            <div class="h-6 w-[1px] bg-slate-200"></div>

                            <select id="status"
                                class="bg-transparent border-none text-[13px] font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[120px]">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Locked</option>
                            </select>
                        </div>

                        <x-button variant="outline" id="resetFilters" icon="bi-arrow-counterclockwise"
                            class="h-12 w-12 !p-0">
                        </x-button>
                    </div>
                </x-card>
            </div>

            <div id="studentDataContainer" class="min-h-[400px] transition-opacity duration-300">
                <div class="flex flex-col items-center justify-center py-20 space-y-4">
                    <div class="h-12 w-12 border-4 border-violet-500/10 border-t-violet-600 rounded-full animate-spin">
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Loading records...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('studentDataContainer');
            const searchInput = document.getElementById('search');
            const searchLoader = document.getElementById('searchLoader');
            const deptSelect = document.getElementById('department_id');
            const yearSelect = document.getElementById('current_year');
            const statusSelect = document.getElementById('status');
            const resetBtn = document.getElementById('resetFilters');
            const fetchUrl = "{{ route('admin.students.fetch') }}";
            const semByDeptUrl = "{{ route('admin.students.semesters-by-dept') }}";

            let fetchTimeout = null;
            let activeRequest = null;

            // ─── Toast ────────────────────────────────────────────────
            const toast = document.getElementById('ajaxToast');
            const toastMsg = document.getElementById('ajaxToastMsg');
            const toastIcon = document.getElementById('ajaxToastIcon');
            let toastTimer = null;

            function showToast(message, type = 'success') {
                clearTimeout(toastTimer);
                toastMsg.textContent = message;
                toast.className = toast.className
                    .replace(/bg-\S+/g, '')
                    .trim();
                if (type === 'success') {
                    toast.classList.add('bg-emerald-500');
                    toastIcon.className = 'bi bi-check-circle-fill text-lg';
                } else if (type === 'error') {
                    toast.classList.add('bg-rose-500');
                    toastIcon.className = 'bi bi-exclamation-circle-fill text-lg';
                } else {
                    toast.classList.add('bg-violet-600');
                    toastIcon.className = 'bi bi-info-circle-fill text-lg';
                }
                toast.classList.remove('translate-y-20', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
                toast.style.pointerEvents = 'auto';
                toastTimer = setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-20', 'opacity-0');
                    toast.style.pointerEvents = 'none';
                }, 3500);
            }

            // ─── AJAX helpers ─────────────────────────────────────────
            async function ajaxRequest(url, method, csrf) {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                return res.json();
            }

            // ─── Toggle handler ───────────────────────────────────────
            async function handleToggle(btn) {
                const url = btn.dataset.url;
                const csrf = btn.dataset.csrf;
                const active = btn.dataset.active === '1';
                const label = active ? 'Deactivate' : 'Activate';
                if (!confirm(`${label} this student?`)) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin text-lg leading-none"></i>';

                try {
                    const data = await ajaxRequest(url, 'PATCH', csrf);
                    if (!data.success) throw new Error(data.message || 'Failed');

                    const isNowActive = data.is_active;
                    const row = btn.closest('tr');

                    // Update status dot
                    const dot = row.querySelector('.status-dot');
                    if (dot) {
                        dot.classList.toggle('bg-emerald-500', isNowActive);
                        dot.classList.toggle('bg-rose-400', !isNowActive);
                    }

                    // Update Access badge
                    const badgeWrap = row.querySelector('.access-badge');
                    if (badgeWrap) {
                        badgeWrap.innerHTML = isNowActive
                            ? '<span class="badge badge-success">Active</span>'
                            : '<span class="badge badge-danger">Locked</span>';
                    }

                    // Update toggle button itself
                    const activeClasses = ['bg-emerald-50', 'text-emerald-600', 'border-emerald-200', 'hover:bg-emerald-500', 'hover:text-white', 'hover:border-emerald-500'];
                    const inactiveClasses = ['bg-amber-50', 'text-amber-500', 'border-amber-200', 'hover:bg-amber-500', 'hover:text-white', 'hover:border-amber-500'];
                    if (isNowActive) {
                        btn.classList.remove(...inactiveClasses);
                        btn.classList.add(...activeClasses);
                    } else {
                        btn.classList.remove(...activeClasses);
                        btn.classList.add(...inactiveClasses);
                    }
                    btn.dataset.active = isNowActive ? '1' : '0';
                    btn.title = isNowActive ? 'Deactivate Student' : 'Activate Student';
                    btn.innerHTML = `<i class="bi ${isNowActive ? 'bi-toggle-on' : 'bi-toggle-off'} text-lg leading-none"></i>`;

                    showToast(data.message, 'success');
                } catch (err) {
                    showToast(err.message || 'Something went wrong.', 'error');
                    btn.innerHTML = `<i class="bi ${active ? 'bi-toggle-on' : 'bi-toggle-off'} text-lg leading-none"></i>`;
                } finally {
                    btn.disabled = false;
                }
            }

            // ─── Delete handler ───────────────────────────────────────
            async function handleDelete(btn) {
                const url = btn.dataset.url;
                const csrf = btn.dataset.csrf;
                const name = btn.dataset.name || 'this student';
                if (!confirm(`Permanently delete "${name}"? This cannot be undone.`)) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i>';

                try {
                    const data = await ajaxRequest(url, 'DELETE', csrf);
                    if (!data.success) throw new Error(data.message || 'Failed');

                    const row = btn.closest('tr');
                    row.style.transition = 'opacity 0.4s, transform 0.4s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 420);
                    showToast(data.message, 'success');
                } catch (err) {
                    showToast(err.message || 'Something went wrong.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-trash3"></i>';
                }
            }

            // ─── Delegated click on container ─────────────────────────
            container.addEventListener('click', function (event) {
                // Toggle
                const toggleBtn = event.target.closest('[data-action="toggle"]');
                if (toggleBtn) { handleToggle(toggleBtn); return; }

                // Delete
                const deleteBtn = event.target.closest('[data-action="delete"]');
                if (deleteBtn) { handleDelete(deleteBtn); return; }

                // Pagination
                const link = event.target.closest('a[href]');
                if (!link) return;
                const href = link.getAttribute('href') || '';
                const isPaginatorClick = link.closest('.pagination-ajax') || href.includes('page=');
                if (!isPaginatorClick) return;
                event.preventDefault();
                fetchStudents(link.href);
                window.scrollTo({ top: container.offsetTop - 100, behavior: 'smooth' });
            });

            function setLoading(loading) {
                if (loading) {
                    searchLoader.classList.remove('hidden');
                    container.classList.add('opacity-60');
                } else {
                    searchLoader.classList.add('hidden');
                    container.classList.remove('opacity-60');
                }
            }

            async function loadSemesters(deptId = '') {
                yearSelect.innerHTML = '<option value="">Loading...</option>';
                yearSelect.disabled = true;

                try {
                    const response = await fetch(`${semByDeptUrl}?department_id=${encodeURIComponent(deptId)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const semesters = await response.json();
                    yearSelect.innerHTML = '<option value="">All Years</option>';

                    semesters.forEach(sem => {
                        const option = document.createElement('option');
                        option.value = sem.id;
                        option.textContent = sem.name;
                        yearSelect.appendChild(option);
                    });
                } catch (e) {
                    yearSelect.innerHTML = '<option value="">All Years</option>';
                } finally {
                    yearSelect.disabled = false;
                }
            }

            async function fetchStudents(pageUrl = null) {
                const params = new URLSearchParams({
                    search: searchInput.value || '',
                    department_id: deptSelect.value || '',
                    current_year: yearSelect.value || '',
                    status: statusSelect.value || ''
                });

                const base = pageUrl || fetchUrl;
                const cleanBase = base.split('?')[0];
                const existing = new URLSearchParams(base.includes('?') ? base.split('?')[1] : '');
                if (existing.has('page')) {
                    params.set('page', existing.get('page'));
                }

                const url = `${cleanBase}?${params.toString()}`;

                if (activeRequest) {
                    activeRequest.abort();
                }
                activeRequest = new AbortController();

                setLoading(true);
                try {
                    const response = await fetch(url, {
                        signal: activeRequest.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`Server returned ${response.status}`);
                    }

                    container.innerHTML = await response.text();
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        container.innerHTML = `
                                                    <div class="glass-card py-20 text-center border-rose-100 bg-rose-50/20 mt-4">
                                                        <div class="h-16 w-16 bg-rose-500/10 text-rose-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                        </div>
                                                        <h3 class="text-rose-900 font-black text-lg mb-1">Failed to Load</h3>
                                                        <p class="text-rose-600/60 text-sm">${error.message}</p>
                                                    </div>
                                                `;
                    }
                } finally {
                    setLoading(false);
                }
            }

            searchInput.addEventListener('input', () => {
                clearTimeout(fetchTimeout);
                fetchTimeout = setTimeout(() => fetchStudents(), 350);
            });

            deptSelect.addEventListener('change', async () => {
                yearSelect.value = '';
                await loadSemesters(deptSelect.value);
                fetchStudents();
            });

            yearSelect.addEventListener('change', () => fetchStudents());
            statusSelect.addEventListener('change', () => fetchStudents());

            resetBtn.addEventListener('click', async () => {
                searchInput.value = '';
                deptSelect.value = '';
                yearSelect.value = '';
                statusSelect.value = '';
                await loadSemesters();
                fetchStudents();
            });

            loadSemesters().then(() => fetchStudents());
        });
    </script>
@endpush