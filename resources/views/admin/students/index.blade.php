@extends('layouts.app')

@section('header_title', 'Student Management')

@section('content')
    <div x-data="{
        loading: false,
        async reset() {
            document.getElementById('resetFilters').click();
        }
    }">
        <x-page-header 
            title="Student Management" 
            subtitle="Manage student enrollments, academic records, and portal access."
            icon="bi-person-badge"
            actionLabel="Add Student"
            actionIcon="bi-person-plus"
            actionRoute="{{ route('admin.students.create') }}"
        />

        <!-- ─── Statistics ─── -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stat-card 
                label="Total Students" 
                value="{{ number_format($stats['total']) }}" 
                icon="bi-people" 
                tone="indigo" 
                trend="+{{ $stats['new'] }} this month"
            />
            <x-stat-card 
                label="Active Students" 
                value="{{ number_format($stats['active']) }}" 
                icon="bi-person-check" 
                tone="emerald" 
            />
            <x-stat-card 
                label="New Admissions" 
                value="{{ number_format($stats['new']) }}" 
                icon="bi-plus-circle" 
                tone="amber" 
            />
            <x-stat-card 
                label="Locked Accounts" 
                value="{{ number_format($stats['inactive']) }}" 
                icon="bi-lock" 
                tone="rose" 
            />
        </div>

        <!-- ─── Filters & Search ─── -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-8 sticky top-20 z-30">
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 min-w-[300px] h-11 group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" id="search"
                        placeholder="Search by name, ID, or enrollment number..."
                        class="w-full h-full bg-slate-50 border-slate-100 rounded-xl pl-11 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all font-medium">
                    <div id="searchLoader" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                        <div class="animate-spin h-4 w-4 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1 p-1 bg-slate-50 rounded-xl border border-slate-100">
                    <select id="department_id" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[140px] px-3">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    
                    <div class="w-px h-6 bg-slate-200 mx-1"></div>

                    <select id="current_year" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[120px] px-3">
                        <option value="">All Semesters</option>
                    </select>

                    <div class="w-px h-6 bg-slate-200 mx-1"></div>

                    <select id="status" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 cursor-pointer min-w-[100px] px-3">
                        <option value="">Status</option>
                        <option value="1">Active Only</option>
                        <option value="0">Locked Only</option>
                    </select>
                </div>

                <button id="resetFilters" class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 transition-all shadow-sm" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
            </div>
        </div>

        <div id="studentDataContainer" class="min-h-[400px] transition-opacity duration-300">
            <div class="flex flex-col items-center justify-center py-32 space-y-4">
                <div class="h-12 w-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest animate-pulse">Loading Student Records...</p>
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

            function setLoading(loadingStatus) {
                if (loadingStatus) {
                    searchLoader?.classList.remove('hidden');
                    container?.classList.add('opacity-60');
                } else {
                    searchLoader?.classList.add('hidden');
                    container?.classList.remove('opacity-60');
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
                    yearSelect.innerHTML = '<option value="">All Semesters</option>';

                    semesters.forEach(sem => {
                        const option = document.createElement('option');
                        option.value = sem.id;
                        option.textContent = sem.name;
                        yearSelect.appendChild(option);
                    });
                } catch (e) {
                    yearSelect.innerHTML = '<option value="">All Semesters</option>';
                    console.error('Failed to load semesters:', e);
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
                if (existing.has('page')) params.set('page', existing.get('page'));

                const url = `${cleanBase}?${params.toString()}`;

                if (activeRequest) activeRequest.abort();
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

                    if (!response.ok) throw new Error(`Server returned ${response.status}`);
                    container.innerHTML = await response.text();
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        container.innerHTML = `
                            <div class="bg-white border border-slate-200 rounded-2xl p-20 text-center shadow-sm">
                                <i class="bi bi-exclamation-triangle text-4xl text-rose-500 mb-4 block"></i>
                                <h3 class="text-slate-800 font-bold mb-1">Failed to Load Data</h3>
                                <p class="text-slate-500 text-sm font-medium">${error.message}</p>
                                <button onclick="window.location.reload()" class="mt-6 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-all">Retry Load</button>
                            </div>
                        `;
                    }
                } finally {
                    setLoading(false);
                }
            }

            // Click delegation for table actions
            container.addEventListener('click', async function (e) {
                const toggleBtn = e.target.closest('[data-action="toggle"]');
                const deleteBtn = e.target.closest('[data-action="delete"]');
                const paginationLink = e.target.closest('.pagination-ajax a, .pagination a');

                if (toggleBtn) {
                    const url = toggleBtn.dataset.url;
                    const isActive = toggleBtn.dataset.active === '1';
                    if (!confirm(`${isActive ? 'Deactivate' : 'Activate'} this student?`)) return;
                    
                    toggleBtn.disabled = true;
                    try {
                        const response = await fetch(url, {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) fetchStudents();
                    } catch (err) { alert('Operation failed.'); }
                    finally { toggleBtn.disabled = false; }
                }

                if (deleteBtn) {
                    const url = deleteBtn.dataset.url;
                    if (!confirm('Permanently delete this student? Use with caution.')) return;
                    
                    deleteBtn.disabled = true;
                    try {
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) fetchStudents();
                    } catch (err) { alert('Deletion failed.'); }
                    finally { deleteBtn.disabled = false; }
                }

                if (paginationLink) {
                    e.preventDefault();
                    fetchStudents(paginationLink.href);
                    window.scrollTo({ top: container.offsetTop - 120, behavior: 'smooth' });
                }
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