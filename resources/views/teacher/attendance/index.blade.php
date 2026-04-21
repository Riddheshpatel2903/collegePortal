@extends('layouts.app')

@section('header_title', 'Mark Attendance')

@section('content')
<div class="space-y-8 animate-fade-in">
    <!-- ─── Page Header ─── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div>
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-widest mb-4 border border-indigo-100">
                <i class="bi bi-calendar-check mr-2"></i> Academic Session 2023-24
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Attendance Register</h2>
            <p class="text-sm text-slate-500 font-medium">Click a date to mark or edit attendance for your class.</p>
        </div>
        <div class="flex items-center gap-3">
            <select id="subjectSelect" class="w-full md:min-w-[280px] px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none">
                <option value="">Select Subject</option>
                @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" data-semester="{{ $sub->semester_id }}">
                        {{ $sub->name }} — {{ $sub->semester->name ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Month Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6" id="monthStats" style="display:none;">
        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm border-l-4 border-l-indigo-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg border border-indigo-100">
                    <i class="bi bi-calendar-check text-xl"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Sessions</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 tracking-tight" id="statSessions">0</div>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm border-l-4 border-l-emerald-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg border border-emerald-100">
                    <i class="bi bi-person-check text-xl"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Avg Present</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 tracking-tight" id="statPresent">0%</div>
        </div>

        <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm border-l-4 border-l-rose-500">
            <div class="flex justify-between items-start mb-4">
                <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg border border-rose-100">
                    <i class="bi bi-person-x text-xl"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Avg Absent</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 tracking-tight" id="statAbsent">0%</div>
        </div>
    </div>

    {{-- Calendar --}}
    <div id="calendarCard" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm" style="display:none;">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <button onclick="changeMonth(-1)" class="h-9 w-9 bg-white border border-slate-200 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center transition-all">
                <i class="bi bi-chevron-left"></i>
            </button>
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-widest" id="calendarTitle">Month Year</h3>
            <button onclick="changeMonth(1)" class="h-9 w-9 bg-white border border-slate-200 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center transition-all">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="cal-table w-full border-collapse" id="calendarTable">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sun</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mon</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tue</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Wed</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Thu</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fri</th>
                        <th class="px-4 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sat</th>
                    </tr>
                </thead>
                <tbody id="calendarGrid" class="divide-y divide-slate-100">
                    {{-- JS-rendered --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Placeholder --}}
    <div id="placeholder">
        <div class="py-24 text-center bg-white border-2 border-dashed border-slate-200 rounded-3xl">
            <div class="h-20 w-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center text-3xl mx-auto mb-6">
                <i class="bi bi-calendar3"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Select a Subject</h3>
            <p class="text-sm text-slate-500 font-medium max-w-sm mx-auto">Choose a subject from the dropdown above to manage its attendance register.</p>
        </div>
    </div>
</div>

{{-- Slide-in Attendance Panel --}}
<div id="attendancePanel" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300" onclick="closePanel()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col transform transition-transform duration-500 translate-x-full" id="panelContent">
        {{-- Panel Header --}}
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-lg shadow-indigo-100">
                    <i class="bi bi-person-check"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="panelTitle">Mark Attendance</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest" id="panelDate">Date</p>
                </div>
            </div>
            <button onclick="closePanel()" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Panel Stats --}}
        <div class="p-8 border-b border-slate-100">
            <div class="grid grid-cols-3 gap-6">
                <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total</p>
                    <p class="text-xl font-bold text-slate-700" id="panelTotal">0</p>
                </div>
                <div class="bg-emerald-50 rounded-2xl p-4 text-center border border-emerald-100/50">
                    <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                    <p class="text-xl font-bold text-emerald-600" id="panelPresent">0</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4 text-center border border-rose-100/50">
                    <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Absent</p>
                    <p class="text-xl font-bold text-rose-500" id="panelAbsent">0</p>
                </div>
            </div>
        </div>

        {{-- Mark All Buttons --}}
        <div class="px-8 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
            <button onclick="markAll('present')" class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-2 text-xs rounded-xl hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-100 transition-all flex items-center justify-center gap-2 shadow-sm">
                <i class="bi bi-check-all text-sm"></i> Mark All Present
            </button>
            <button onclick="markAll('absent')" class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-2 text-xs rounded-xl hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all flex items-center justify-center gap-2 shadow-sm">
                <i class="bi bi-x-circle text-sm"></i> Mark All Absent
            </button>
        </div>

        {{-- Student List --}}
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-4 custom-scrollbar" id="studentList">
            {{-- JS-rendered --}}
        </div>

        {{-- Save Button --}}
        <div class="px-8 py-6 border-t border-slate-100 bg-white">
            <button id="saveBtn" onclick="saveAttendance()" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-slate-200 flex items-center justify-center gap-2 text-sm uppercase tracking-widest">
                <i class="bi bi-shield-check"></i> Authorize & Save Register
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .cal-table td {
        height: 110px;
        vertical-align: top;
        border-right: 1px solid #f1f5f9;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .cal-table td:last-child {
        border-right: none;
    }

    .cal-table td:hover:not(.empty):not(.locked) {
        background: #f8fafc;
        border-radius: 0;
    }

    .cal-table td.today {
        background: #eff6ff;
    }
    
    .cal-table td.today .day-num {
        background: #3b82f6;
        color: white;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-left: auto;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
    }

    .cal-table td.sunday {
        background: #fffafa;
        cursor: default;
    }

    .cal-table td.sunday .day-num {
        color: #fca5a5;
    }

    .cal-table td.empty {
        background: #fafafa;
        cursor: default;
    }

    .cal-table td.locked {
        cursor: default;
        opacity: 0.6;
    }

    .cal-table td.locked .lock-icon {
        position: absolute;
        bottom: 0.75rem;
        right: 0.75rem;
        font-size: 10px;
        color: #e2e8f0;
    }

    .cal-table .day-num {
        text-align: right;
        font-size: 12px;
        font-bold: 800;
        color: #cbd5e1;
    }

    .cal-table .holiday-tag {
        font-size: 8px;
        font-bold: 900;
        color: #fca5a5;
        background: #fef2f2;
        border-radius: 4px;
        padding: 1px 5px;
        margin-top: 6px;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .cal-table .session-info {
        margin-top: 8px;
        font-size: 9px;
        font-bold: 800;
        color: #6366f1;
        background: #f5f3ff;
        border-radius: 6px;
        padding: 3px 6px;
        display: inline-block;
        border: 1px solid #e0e7ff;
    }

    .cal-table td.has-session::after {
        content: '';
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #6366f1;
    }

    .att-toggle {
        display: flex;
        align-items: center;
        padding: 3px;
        background: #f1f5f9;
        border-radius: 10px;
        gap: 2px;
    }

    .att-toggle button {
        padding: 5px 10px;
        font-size: 9px;
        font-bold: 800;
        border-radius: 8px;
        transition: all 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .att-toggle button.active-present {
        background: white;
        color: #059669;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .att-toggle button.active-absent {
        background: white;
        color: #e11d48;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .student-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-radius: 1rem;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    .student-row:hover {
        background: white;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@endsection

@push('scripts')
    <script>
        const CSRF = '{{ csrf_token() }}';
        const sessionsUrl = '{{ route("teacher.attendance.sessions") }}';
        const storeUrl = '{{ route("teacher.attendance.store") }}';
        const studentsBySemester = {!! $studentsBySemesterJson !!};

        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth(); // 0-indexed
        let selectedSubject = null;
        let selectedSemester = null;
        let sessionsByDate = {};
        let selectedDate = null;
        let students = {}; // { student_id: { name, roll, status } }

        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // ── Subject Select ──
        document.getElementById('subjectSelect').addEventListener('change', function () {
            selectedSubject = this.value;
            const opt = this.options[this.selectedIndex];
            selectedSemester = opt?.dataset.semester || null;
            if (selectedSubject) {
                document.getElementById('placeholder').style.display = 'none';
                document.getElementById('calendarCard').style.display = '';
                document.getElementById('monthStats').style.display = '';
                loadSessions();
            } else {
                document.getElementById('placeholder').style.display = '';
                document.getElementById('calendarCard').style.display = 'none';
                document.getElementById('monthStats').style.display = 'none';
            }
        });

        // ── Calendar Navigation ──
        function changeMonth(delta) {
            currentMonth += delta;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            loadSessions();
        }

        // ── Load Sessions from Server ──
        function loadSessions() {
            fetch(`${sessionsUrl}?subject_id=${selectedSubject}&month=${currentMonth + 1}&year=${currentYear}`)
                .then(r => r.json())
                .then(data => {
                    sessionsByDate = {};
                    data.forEach(s => { sessionsByDate[s.date] = s; });
                    updateStats(data);
                    renderCalendar();
                });
        }

        function updateStats(data) {
            document.getElementById('statSessions').textContent = data.length;
            if (data.length > 0) {
                const totalStudents = data.reduce((sum, s) => sum + s.total, 0);
                const totalPresent = data.reduce((sum, s) => sum + s.present, 0);
                const pct = totalStudents > 0 ? Math.round((totalPresent / totalStudents) * 100) : 0;
                document.getElementById('statPresent').textContent = pct + '%';
                document.getElementById('statAbsent').textContent = (100 - pct) + '%';
            } else {
                document.getElementById('statPresent').textContent = '0%';
                document.getElementById('statAbsent').textContent = '0%';
            }
        }

        // ── Render Calendar ──
        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';
            document.getElementById('calendarTitle').textContent = `${months[currentMonth]} ${currentYear}`;

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date();

            let dayCounter = 1;
            const totalWeeks = Math.ceil((firstDay + daysInMonth) / 7);

            // Editable range: today and previous 3 days
            const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            const minEditable = new Date(todayDate);
            minEditable.setDate(minEditable.getDate() - 3);

            for (let week = 0; week < totalWeeks; week++) {
                const row = document.createElement('tr');

                for (let col = 0; col < 7; col++) {
                    const cell = document.createElement('td');
                    const cellIndex = week * 7 + col;

                    if (cellIndex < firstDay || dayCounter > daysInMonth) {
                        cell.className = 'empty';
                        cell.innerHTML = '';
                    } else {
                        const d = dayCounter;
                        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        const cellDate = new Date(currentYear, currentMonth, d);
                        const isSunday = col === 0;
                        const isToday = today.getFullYear() === currentYear && today.getMonth() === currentMonth && today.getDate() === d;
                        const isEditable = !isSunday && cellDate >= minEditable && cellDate <= todayDate;

                        if (isToday) cell.classList.add('today');

                        if (isSunday) {
                            cell.classList.add('sunday');
                            cell.innerHTML = `<div class="day-num">${d}</div><span class="holiday-tag">Holiday</span>`;
                        } else {
                            const session = sessionsByDate[dateStr];
                            if (session) {
                                cell.classList.add('has-session');
                                const pct = session.total > 0 ? Math.round((session.present / session.total) * 100) : 0;
                                cell.innerHTML = `<div class="day-num">${d}</div><span class="session-info">${pct}% present</span>`;
                            } else {
                                cell.innerHTML = `<div class="day-num">${d}</div>`;
                            }

                            if (isEditable) {
                                cell.onclick = () => openPanel(dateStr, session);
                            } else {
                                cell.classList.add('locked');
                                cell.innerHTML += `<span class="lock-icon"><i class="bi bi-lock-fill"></i></span>`;
                            }
                        }
                        dayCounter++;
                    }

                    row.appendChild(cell);
                }

                grid.appendChild(row);
            }
        }

        // ── Attendance Panel ──
        function openPanel(date, existingSession) {
            selectedDate = date;
            const formatted = new Date(date + 'T00:00:00').toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('panelDate').textContent = formatted;
            document.getElementById('panelTitle').textContent = existingSession ? 'Update Register' : 'Mark Attendance';
            
            const panel = document.getElementById('attendancePanel');
            const content = document.getElementById('panelContent');
            panel.classList.remove('hidden');
            setTimeout(() => content.classList.remove('translate-x-full'), 10);
            document.body.style.overflow = 'hidden';

            if (existingSession) {
                fetch(`/teacher/attendance/session/${existingSession.id}`)
                    .then(r => r.json())
                    .then(records => {
                        students = {};
                        records.forEach(r => { students[r.student_id] = { name: r.name, roll: r.roll, status: r.status }; });
                        renderStudentList();
                    });
            } else {
                students = {};
                const semStudents = studentsBySemester[selectedSemester] || [];
                semStudents.forEach(s => {
                    students[s.id] = { name: s.name, roll: s.roll, status: 'present' };
                });
                renderStudentList();
            }
        }

        function closePanel() {
            const content = document.getElementById('panelContent');
            content.classList.add('translate-x-full');
            setTimeout(() => document.getElementById('attendancePanel').classList.add('hidden'), 500);
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });

        function renderStudentList() {
            const list = document.getElementById('studentList');
            const entries = Object.entries(students);
            let html = '';

            entries.sort((a, b) => (a[1].roll || '').localeCompare(b[1].roll || ''));

            entries.forEach(([id, s]) => {
                const isPresent = s.status === 'present';
                html += `
                    <div class="student-row">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-2xl bg-violet-600 font-black text-white flex items-center justify-center text-xs shadow-lg shadow-violet-200 uppercase">
                                ${s.name.charAt(0)}
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-900 tracking-tight">${s.name}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">${s.roll}</div>
                            </div>
                        </div>
                        <div class="att-toggle">
                            <button class="${isPresent ? 'active-present' : 'text-slate-400'}" onclick="toggleStatus(${id}, 'present')">Present</button>
                            <button class="${!isPresent ? 'active-absent' : 'text-slate-400'}" onclick="toggleStatus(${id}, 'absent')">Absent</button>
                        </div>
                    </div>`;
            });

            if (entries.length === 0) {
                html = '<div class="text-center py-12"><i class="bi bi-people text-4xl text-slate-200 mb-4 block"></i><p class="text-sm text-slate-400 font-bold">No students assigned to this subject</h2></div>';
            }

            list.innerHTML = html;
            updatePanelStats();
        }

        function toggleStatus(studentId, status) {
            if (students[studentId]) {
                students[studentId].status = status;
                renderStudentList();
            }
        }

        function markAll(status) {
            Object.keys(students).forEach(id => { students[id].status = status; });
            renderStudentList();
        }

        function updatePanelStats() {
            const entries = Object.values(students);
            const total = entries.length;
            const present = entries.filter(s => s.status === 'present').length;
            const absent = total - present;
            document.getElementById('panelTotal').textContent = total;
            document.getElementById('panelPresent').textContent = present;
            document.getElementById('panelAbsent').textContent = absent;
        }

        // ── Save ──
        function saveAttendance() {
            const btn = document.getElementById('saveBtn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin mr-2"></i> Authorizing...';
            btn.disabled = true;

            const attendance = {};
            Object.entries(students).forEach(([id, s]) => attendance[id] = s.status);

            fetch(storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ subject_id: selectedSubject, date: selectedDate, attendance: attendance })
            })
            .then(r => r.json())
            .then(() => {
                btn.innerHTML = '<i class="bi bi-check2-circle mr-2"></i> Register Saved!';
                btn.classList.replace('bg-slate-900', 'bg-emerald-600');
                setTimeout(() => {
                    closePanel();
                    loadSessions();
                    btn.innerHTML = originalContent;
                    btn.classList.replace('bg-emerald-600', 'bg-slate-900');
                    btn.disabled = false;
                }, 1000);
            })
            .catch(() => {
                btn.innerHTML = '<i class="bi bi-exclamation-triangle mr-2"></i> Failed — Retry';
                btn.disabled = false;
            });
        }
    </script>
@endpush
