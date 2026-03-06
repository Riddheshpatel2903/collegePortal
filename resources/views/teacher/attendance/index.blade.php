@extends('layouts.app')

@section('header_title', 'Mark Attendance')

@section('content')
    {{-- Page Header --}}
    <div class="space-y-8">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-calendar-check mr-1"></i> Academic Session
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Attendance <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Register</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium">Click a date to mark or edit attendance for your class.</p>
            </div>
            <div class="flex items-center gap-3">
                <select id="subjectSelect" class="bg-white border-slate-200 rounded-2xl py-3 px-6 text-sm font-bold text-slate-700 shadow-sm focus:ring-[6px] focus:ring-violet-500/5 focus:border-violet-200 transition-all outline-none min-w-[250px]">
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
            <x-card class="border-l-4 border-l-violet-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center text-xl">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <x-badge>Sessions This Month</x-badge>
                </div>
                <div class="text-2xl font-black text-slate-800 tracking-tight" id="statSessions">0</div>
            </x-card>

            <x-card class="border-l-4 border-l-emerald-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <x-badge type="success">Avg Present %</x-badge>
                </div>
                <div class="text-2xl font-black text-slate-800 tracking-tight" id="statPresent">0%</div>
            </x-card>

            <x-card class="border-l-4 border-l-rose-500">
                <div class="flex justify-between items-start mb-4">
                    <div class="h-11 w-11 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <x-badge type="danger">Avg Absent %</x-badge>
                </div>
                <div class="text-2xl font-black text-slate-800 tracking-tight" id="statAbsent">0%</div>
            </x-card>
        </div>

        {{-- Calendar --}}
        <x-card id="calendarCard" class="overflow-hidden !p-0" style="display:none;">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <x-button variant="outline" size="sm" onclick="changeMonth(-1)" icon="bi-chevron-left" class="!px-3"></x-button>
                <h3 class="text-base font-black text-slate-800 tracking-tight" id="calendarTitle">Month Year</h3>
                <x-button variant="outline" size="sm" onclick="changeMonth(1)" icon="bi-chevron-right" class="!px-3"></x-button>
            </div>

            <div class="overflow-x-auto">
                {{-- Calendar Table --}}
                <table class="cal-table" id="calendarTable">
                    <thead>
                        <tr>
                            <th class="sun-header">Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody id="calendarGrid">
                        {{-- JS-rendered --}}
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Placeholder --}}
        <div id="placeholder">
            <x-card class="py-24 text-center border-dashed border-2">
                <div class="h-20 w-20 rounded-3xl bg-violet-50 text-violet-400 flex items-center justify-center text-3xl mx-auto mb-6">
                    <i class="bi bi-calendar3"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Select a Subject</h3>
                <p class="text-sm text-slate-400 font-medium max-w-sm mx-auto">Choose a subject from the dropdown above to manage its attendance register.</p>
            </x-card>
        </div>
    </div>

    {{-- Slide-in Attendance Panel --}}
    <div id="attendancePanel" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity duration-300" onclick="closePanel()"></div>
        <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white/95 backdrop-blur-xl shadow-2xl flex flex-col transform transition-transform duration-500 translate-x-full" id="panelContent">
            {{-- Panel Header --}}
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-violet-600 text-white flex items-center justify-center text-xl shadow-lg shadow-violet-200">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight" id="panelTitle">Mark Attendance</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest" id="panelDate">Date</p>
                    </div>
                </div>
                <button onclick="closePanel()" class="h-10 w-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Panel Stats --}}
            <div class="p-8 border-b border-slate-100">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total</p>
                        <p class="text-xl font-extrabold text-slate-700" id="panelTotal">0</p>
                    </div>
                    <div class="bg-emerald-50 rounded-2xl p-4 text-center border border-emerald-100/50">
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                        <p class="text-xl font-extrabold text-emerald-600" id="panelPresent">0</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-4 text-center border border-rose-100/50">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Absent</p>
                        <p class="text-xl font-extrabold text-rose-500" id="panelAbsent">0</p>
                    </div>
                </div>
            </div>

            {{-- Mark All Buttons --}}
            <div class="px-8 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                <x-button variant="outline" size="sm" onclick="markAll('present')" icon="bi-check-all" class="flex-1 bg-white">
                    Mark All Present
                </x-button>
                <x-button variant="outline" size="sm" onclick="markAll('absent')" icon="bi-x-circle" class="flex-1 bg-white">
                    Mark All Absent
                </x-button>
            </div>

            {{-- Student List --}}
            <div class="flex-1 overflow-y-auto px-8 py-6 space-y-3 custom-scrollbar" id="studentList">
                {{-- JS-rendered --}}
            </div>

            {{-- Save Button --}}
            <div class="px-8 py-6 border-t border-slate-100 bg-white">
                <x-button variant="primary" id="saveBtn" onclick="saveAttendance()" icon="bi-shield-check" class="w-full !py-4 text-sm uppercase tracking-widest bg-slate-900">
                    Authorize & Save Register
                </x-button>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .cal-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cal-table thead th {
            padding: 1.25rem 0.5rem;
            text-align: center;
            font-size: 10px;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .cal-table thead th.sun-header {
            color: #f43f5e;
            background: #fff1f2/50;
        }

        .cal-table td {
            height: 120px;
            vertical-align: top;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .cal-table td:last-child {
            border-right: none;
        }

        .cal-table td:hover:not(.empty):not(.locked) {
            background: #f5f3ff;
            transform: scale(1.02);
            z-index: 10;
            box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.1);
            border-radius: 1rem;
        }

        .cal-table td.today {
            background: #ede9fe/30;
        }
        
        .cal-table td.today .day-num {
            background: #7c3aed;
            color: white;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-left: auto;
            box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3);
        }

        .cal-table td.sunday {
            background: #fff1f2/20;
            cursor: default;
        }

        .cal-table td.empty {
            background: #fafafa;
            cursor: default;
        }

        .cal-table td.locked {
            cursor: default;
            background: #fcfcfc;
        }

        .cal-table td.locked .lock-icon {
            position: absolute;
            bottom: 0.75rem;
            right: 0.75rem;
            font-size: 10px;
            color: #cbd5e1;
            opacity: 0.5;
        }

        .cal-table .day-num {
            text-align: right;
            font-size: 13px;
            font-weight: 800;
            color: #64748b;
        }

        .cal-table .holiday-tag {
            font-size: 9px;
            font-weight: 900;
            color: #f43f5e;
            background: #fff1f2;
            border-radius: 6px;
            padding: 2px 6px;
            margin-top: 8px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cal-table .session-info {
            margin-top: 8px;
            font-size: 10px;
            font-weight: 800;
            color: #7c3aed;
            background: #ede9fe;
            border-radius: 8px;
            padding: 4px 8px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(124, 58, 237, 0.05);
        }

        .cal-table td.has-session::after {
            content: '';
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #7c3aed;
        }

        .att-toggle {
            display: flex;
            align-items: center;
            padding: 3px;
            background: #f1f5f9;
            border-radius: 12px;
            gap: 2px;
        }

        .att-toggle button {
            padding: 6px 12px;
            font-size: 10px;
            font-weight: 800;
            border-radius: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .att-toggle button.active-present {
            background: white;
            color: #059669;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .att-toggle button.active-absent {
            background: white;
            color: #e11d48;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .student-row {
            @apply flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 transition-all duration-300;
        }
        .student-row:hover {
            @apply bg-white shadow-xl shadow-slate-200/50 -translate-y-0.5 border-violet-100;
        }
    </style>
    @endpush

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
@endsection
