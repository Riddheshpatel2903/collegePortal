@extends('layouts.app')

@section('header_title', 'My Attendance')

@section('content')
<div class="space-y-8 animate-fade-in">
    <!-- ─── Page Header ─── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div class="flex-1">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-widest mb-4 border border-indigo-100">
                <i class="bi bi-person-badge mr-2"></i> Student Portal
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Attendance Overview</h2>
            <p class="text-sm text-slate-500 font-medium">Your subject-wise attendance record and detailed analytics.</p>
        </div>
        
        <div class="flex items-center gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <div class="relative h-16 w-16">
                <svg class="h-16 w-16 -rotate-90" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="34" fill="none" stroke="#e2e8f0" stroke-width="8"/>
                    @php
                        $progressColor = $overallPercent >= 75 ? '#6366f1' : '#f43f5e';
                    @endphp
                    <circle cx="40" cy="40" r="34" fill="none"
                        stroke="{{ $progressColor }}"
                        stroke-width="8" stroke-linecap="round"
                        stroke-dasharray="213.6"
                        stroke-dashoffset="{{ 213.6 * (1 - $overallPercent / 100) }}"
                        class="transition-all duration-1000 ease-out"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-sm font-bold text-slate-700">{{ $overallPercent }}%</span>
                </div>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 text-center md:text-left">Overall Score</p>
                <div class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $overallPercent >= 75 ? 'text-emerald-700 bg-emerald-50 border border-emerald-100' : 'text-rose-700 bg-rose-50 border border-rose-100' }}">
                    {{ $overallPercent >= 75 ? 'Meets Requirement' : 'Below Threshold' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $stats = [
                ['label' => 'Total Classes', 'value' => $totalCount, 'icon' => 'bi-calendar3', 'color' => 'slate'],
                ['label' => 'Present', 'value' => $presentCount, 'icon' => 'bi-check-circle', 'color' => 'indigo'],
                ['label' => 'Absent', 'value' => $absentCount, 'icon' => 'bi-x-circle', 'color' => 'rose'],
                ['label' => 'Attendance %', 'value' => $overallPercent . '%', 'icon' => 'bi-graph-up', 'color' => 'emerald'],
            ];
        @endphp
        @foreach($stats as $stat)
            <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm border-b-4 border-b-{{ $stat['color'] === 'indigo' ? 'indigo' : ($stat['color'] === 'rose' ? 'rose' : ($stat['color'] === 'emerald' ? 'emerald' : 'slate')) }}-500">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center text-lg border border-slate-100">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $stat['label'] }}</span>
                </div>
                <div class="text-2xl font-bold text-slate-800 tracking-tight">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Calendar --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <button onclick="changeMonth(-1)" class="h-9 w-9 bg-white border border-slate-200 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center transition-all">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-widest" id="calTitle">Month Year</h3>
                    <button onclick="changeMonth(1)" class="h-9 w-9 bg-white border border-slate-200 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center transition-all">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="scal-table w-full border-collapse">
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
                        <tbody id="calGrid" class="divide-y divide-slate-100">
                            {{-- JS-rendered --}}
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-5 border-t border-slate-100 flex flex-wrap items-center gap-6 bg-slate-50/30">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 border border-emerald-100"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Present</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500 border border-rose-100"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Absent</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No Record</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Recap --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Monthly Recap</h3>
                </div>

                <div class="space-y-8" id="monthSummary">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Classes</p>
                            <p class="text-xl font-bold text-slate-700" id="mClasses">0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                            <p class="text-xl font-bold text-emerald-600" id="mPresent">0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1">Absent</p>
                            <p class="text-xl font-bold text-rose-500" id="mAbsent">0</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000 ease-out" id="mBar" style="width:0%"></div>
                        </div>
                        <p class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest" id="mPct">0% monthly presence</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subject-wise Breakdown --}}
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <div class="h-px flex-1 bg-slate-200"></div>
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Course Analytics</h3>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($subjectWise as $row)
                @php
                    $color = $row['percent'] >= 90 ? 'emerald' : ($row['percent'] >= 75 ? 'amber' : 'rose');
                    $bgColor = $row['percent'] >= 90 ? 'bg-emerald-50' : ($row['percent'] >= 75 ? 'bg-amber-50' : 'bg-rose-50');
                    $textColor = $row['percent'] >= 90 ? 'text-emerald-600' : ($row['percent'] >= 75 ? 'text-amber-600' : 'text-rose-600');
                    $borderColor = $row['percent'] >= 90 ? 'border-emerald-100' : ($row['percent'] >= 75 ? 'border-amber-100' : 'border-rose-100');
                @endphp
                <div class="bg-white border border-slate-200 p-8 rounded-2xl shadow-sm transition-all hover:shadow-md hover:border-slate-300 group">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-12 w-12 rounded-xl {{ $bgColor }} {{ $textColor }} flex items-center justify-center text-sm font-bold border {{ $borderColor }}">
                            {{ strtoupper(substr($row['subject']->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-slate-800 truncate">{{ $row['subject']->name ?? 'N/A' }}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $bgColor }} {{ $textColor }} border {{ $borderColor }}">
                                    {{ $row['percent'] }}%
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $row['total'] }} Sessions</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                            <p class="text-xl font-bold text-slate-800 leading-none">{{ $row['present'] }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                            <p class="text-[9px] font-bold text-rose-500 uppercase tracking-widest mb-1">Absent</p>
                            <p class="text-xl font-bold text-slate-800 leading-none">{{ $row['absent'] }}</p>
                        </div>
                    </div>

                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        @php
                            $barColor = $row['percent'] >= 90 ? 'bg-emerald-500' : ($row['percent'] >= 75 ? 'bg-amber-500' : 'bg-rose-500');
                        @endphp
                        <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width: {{ $row['percent'] }}%"></div>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3">
                    <div class="py-24 text-center bg-white border-2 border-dashed border-slate-200 rounded-3xl opacity-40">
                        <i class="bi bi-calendar-x text-5xl mb-4 block"></i>
                        <h3 class="text-lg font-bold text-slate-800">No Records Yet</h3>
                        <p class="text-sm font-medium">Your attendance records will appear here once marked by faculty.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('styles')
<style>
    .scal-table td {
        height: 100px;
        vertical-align: top;
        border-right: 1px solid #f1f5f9;
        padding: 1rem;
        position: relative;
        transition: all 0.2s ease;
    }
    .scal-table td:last-child { border-right: none; }
    .scal-table td:hover:not(.empty) { background: #f8fafc; }
    .scal-table td.today { background: #eff6ff; }
    .scal-table td.today .sday-num {
        background: #3b82f6;
        color: white;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-left: auto;
        font-size: 11px;
        font-bold: 800;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
    }
    .scal-table td.empty { background: #fafafa; }
    .scal-table td.sunday { background: #fffafa; }
    .scal-table .sday-num { text-align: right; font-size: 12px; font-weight: 800; color: #cbd5e1; }
    .scal-table td.sunday .sday-num { color: #fca5a5; }
    .scal-table .holiday-tag {
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
    .scal-table .dots { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 8px; }
    .scal-table .dot { width: 5px; height: 5px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.05); }
    .dot-present { background: #10b981; }
    .dot-absent { background: #f43f5e; }

    /* Modern Tooltip */
    .scal-table .tooltip-box {
        opacity: 0;
        pointer-events: none;
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background: #0f172a;
        border-radius: 10px;
        padding: 8px 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        z-index: 50;
        min-width: 140px;
        transition: all 0.2s ease;
        border: 1px solid #1e293b;
    }
    .scal-table .tooltip-box::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #0f172a;
    }
    .scal-table td:hover .tooltip-box { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@endpush

<script>
    const calendarData = {!! $calendarData !!};
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    let curYear = new Date().getFullYear();
    let curMonth = new Date().getMonth();

    function changeMonth(d) {
        curMonth += d;
        if (curMonth > 11) { curMonth = 0; curYear++; }
        if (curMonth < 0) { curMonth = 11; curYear--; }
        renderCal();
    }

    function renderCal() {
        const grid = document.getElementById('calGrid');
        grid.innerHTML = '';
        document.getElementById('calTitle').textContent = `${months[curMonth]} ${curYear}`;

        const firstDay = new Date(curYear, curMonth, 1).getDay();
        const daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
        const today = new Date();

        let monthPresent = 0, monthAbsent = 0;
        let dayCounter = 1;
        const totalWeeks = Math.ceil((firstDay + daysInMonth) / 7);

        for (let week = 0; week < totalWeeks; week++) {
            const row = document.createElement('tr');

            for (let col = 0; col < 7; col++) {
                const cell = document.createElement('td');
                const cellIndex = week * 7 + col;

                if (cellIndex < firstDay || dayCounter > daysInMonth) {
                    cell.className = 'empty';
                } else {
                    const d = dayCounter;
                    const dateStr = `${curYear}-${String(curMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    const isSunday = col === 0;
                    const isToday = today.getFullYear() === currentYear && today.getMonth() === currentMonth && today.getDate() === d;

                    if (isToday) cell.classList.add('today');

                    if (isSunday) {
                        cell.classList.add('sunday');
                        cell.innerHTML = `<div class="sday-num">${d}</div><span class="holiday-tag">Sunday</span>`;
                    } else {
                        let html = `<div class="sday-num">${d}</div>`;
                        const entries = calendarData[dateStr];
                        if (entries && entries.length) {
                            let hasHoliday = false;
                            let attendanceDots = '';
                            let tooltipHtml = '';

                            entries.forEach(e => {
                                if (e.type === 'holiday') {
                                    hasHoliday = true;
                                    html += `<span class="holiday-tag">${e.name}</span>`;
                                    tooltipHtml += `<div style="font-size:9px;font-bold:700;color:#fca5a5;display:flex;align-items:center;gap:6px;padding:2px 0;text-transform:uppercase;letter-spacing:0.05em;"><i class="bi bi-star-fill"></i> ${e.name}</div>`;
                                } else {
                                    const cls = e.status === 'present' ? 'dot-present' : 'dot-absent';
                                    attendanceDots += `<div class="dot ${cls}"></div>`;
                                    
                                    const icon = e.status === 'present' ? 'bi-check-circle text-emerald-400' : 'bi-x-circle text-rose-400';
                                    tooltipHtml += `<div style="font-size:9px;font-bold:700;color:white;display:flex;align-items:center;gap:6px;padding:2px 0;text-transform:uppercase;letter-spacing:0.05em;"><i class="bi ${icon}"></i> ${e.subject}</div>`;
                                    
                                    if (e.status === 'present') monthPresent++;
                                    else monthAbsent++;
                                }
                            });

                            if (attendanceDots) {
                                html += `<div class="dots">${attendanceDots}</div>`;
                            }

                            if (tooltipHtml) {
                                html += `<div class="tooltip-box">${tooltipHtml}</div>`;
                            }
                        }
                        cell.innerHTML = html;
                    }
                    dayCounter++;
                }

                row.appendChild(cell);
            }

            grid.appendChild(row);
        }

        // Month summary
        const totalMonth = monthPresent + monthAbsent;
        const pct = totalMonth > 0 ? Math.round((monthPresent / totalMonth) * 100) : 0;
        document.getElementById('mClasses').textContent = totalMonth;
        document.getElementById('mPresent').textContent = monthPresent;
        document.getElementById('mAbsent').textContent = monthAbsent;
        document.getElementById('mBar').style.width = pct + '%';
        document.getElementById('mPct').textContent = pct + '% presence this month';
    }

    renderCal();
</script>

    <script>
        const calendarData = {!! $calendarData !!};
        const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        let curYear = new Date().getFullYear();
        let curMonth = new Date().getMonth();

        function changeMonth(d) {
            curMonth += d;
            if (curMonth > 11) { curMonth = 0; curYear++; }
            if (curMonth < 0) { curMonth = 11; curYear--; }
            renderCal();
        }

        function renderCal() {
            const grid = document.getElementById('calGrid');
            grid.innerHTML = '';
            document.getElementById('calTitle').textContent = `${months[curMonth]} ${curYear}`;

            const firstDay = new Date(curYear, curMonth, 1).getDay();
            const daysInMonth = new Date(curYear, curMonth + 1, 0).getDate();
            const today = new Date();

            let monthPresent = 0, monthAbsent = 0;
            let dayCounter = 1;
            const totalWeeks = Math.ceil((firstDay + daysInMonth) / 7);

            for (let week = 0; week < totalWeeks; week++) {
                const row = document.createElement('tr');

                for (let col = 0; col < 7; col++) {
                    const cell = document.createElement('td');
                    const cellIndex = week * 7 + col;

                    if (cellIndex < firstDay || dayCounter > daysInMonth) {
                        cell.className = 'empty';
                    } else {
                        const d = dayCounter;
                        const dateStr = `${curYear}-${String(curMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                        const isSunday = col === 0;
                        const isToday = today.getFullYear() === curYear && today.getMonth() === curMonth && today.getDate() === d;

                        if (isToday) cell.classList.add('today');

                        if (isSunday) {
                            cell.classList.add('sunday');
                            cell.innerHTML = `<div class="sday-num">${d}</div><span class="holiday-tag">Sunday</span>`;
                        } else {
                            let html = `<div class="sday-num">${d}</div>`;
                            const entries = calendarData[dateStr];
                            if (entries && entries.length) {
                                let hasHoliday = false;
                                let attendanceDots = '';
                                let tooltipHtml = '';

                                entries.forEach(e => {
                                    if (e.type === 'holiday') {
                                        hasHoliday = true;
                                        html += `<span class="holiday-tag">${e.name}</span>`;
                                        tooltipHtml += `<div style="font-size:10px;font-weight:700;color:#f87171;display:flex;align-items:center;gap:8px;padding:3px 0;text-transform:uppercase;letter-spacing:0.05em;"><i class="bi bi-star-fill"></i> ${e.name}</div>`;
                                    } else {
                                        const cls = e.status === 'present' ? 'dot-present' : 'dot-absent';
                                        attendanceDots += `<div class="dot ${cls}"></div>`;
                                        
                                        const icon = e.status === 'present' ? 'bi-check-circle-fill text-emerald-400' : 'bi-x-circle-fill text-rose-400';
                                        tooltipHtml += `<div style="font-size:10px;font-weight:700;color:white;display:flex;align-items:center;gap:8px;padding:3px 0;text-transform:uppercase;letter-spacing:0.05em;"><i class="bi ${icon}"></i> ${e.subject}</div>`;
                                        
                                        if (e.status === 'present') monthPresent++;
                                        else monthAbsent++;
                                    }
                                });

                                if (attendanceDots) {
                                    html += `<div class="dots">${attendanceDots}</div>`;
                                }

                                if (tooltipHtml) {
                                    html += `<div class="tooltip-box">${tooltipHtml}</div>`;
                                }
                            }
                            cell.innerHTML = html;
                        }
                        dayCounter++;
                    }

                    row.appendChild(cell);
                }

                grid.appendChild(row);
            }

            // Month summary
            const totalMonth = monthPresent + monthAbsent;
            const pct = totalMonth > 0 ? Math.round((monthPresent / totalMonth) * 100) : 0;
            document.getElementById('mClasses').textContent = totalMonth;
            document.getElementById('mPresent').textContent = monthPresent;
            document.getElementById('mAbsent').textContent = monthAbsent;
            document.getElementById('mBar').style.width = pct + '%';
            document.getElementById('mPct').textContent = pct + '% attendance this month';
        }

        renderCal();
    </script>
@endsection