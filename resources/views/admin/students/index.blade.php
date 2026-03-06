@extends('layouts.app')

@section('header_title', 'Student Management')

@section('content')
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <x-badge type="primary" class="mb-4">
                        <i class="bi bi-shield-check mr-1"></i> Enterprise Admin
                    </x-badge>
                    <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Student <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Intelligence</span>
                    </h2>
                    <p class="text-lg text-slate-400 font-medium">Real-time enrollment monitoring and student data
                        management.</p>
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
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">{{ number_format($stats['active']) }}
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
                    <div class="text-3xl font-black text-slate-800 mb-1 tracking-tight">{{ number_format($stats['inactive']) }}
                    </div>
                </x-card>
            </div>

            <div class="sticky top-20 z-30">
                <x-card class="p-2 border border-white/60 shadow-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative flex-1 min-w-[300px] h-12 group">
                            <i
                                class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors"></i>
                            <input type="text" id="search" placeholder="Search by ID, name, roll number, or GTU enrollment..."
                                class="input-premium pl-12 h-full">
                            <div id="searchLoader" class="hidden absolute right-5 top-1/2 -translate-y-1/2">
                                <div class="animate-spin h-4 w-4 border-2 border-violet-500 border-t-transparent rounded-full">
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

                        <x-button variant="outline" id="resetFilters" icon="bi-arrow-counterclockwise" class="h-12 w-12 !p-0">
                        </x-button>
                    </div>
                </x-card>
            </div>

            <div id="studentDataContainer" class="min-h-[400px] transition-opacity duration-300">
                <div class="flex flex-col items-center justify-center py-20 space-y-4">
                    <div class="h-12 w-12 border-4 border-violet-500/10 border-t-violet-600 rounded-full animate-spin"></div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Loading records...</p>
                </div>
            </div>
        </div>
    </div>

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
                    } catch {
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

                container.addEventListener('click', function (event) {
                    const link = event.target.closest('a[href]');
                    if (!link) return;

                    const href = link.getAttribute('href') || '';
                    const isPaginatorClick = link.closest('.pagination-ajax') || href.includes('page=');
                    if (!isPaginatorClick) return;

                    event.preventDefault();
                    fetchStudents(link.href);
                    window.scrollTo({ top: container.offsetTop - 100, behavior: 'smooth' });
                });

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
@endsection
