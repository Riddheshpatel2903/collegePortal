@extends('layouts.app')

@section('header_title', 'My Attendance')

@section('content')
    {{-- Header --}}
    <div class="space-y-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-person-badge mr-1"></i> Student Portal
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Attendance <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-600">Overview</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium tracking-tight">Your subject-wise attendance record and analytics.</p>
            </div>
            
            {{-- Circular Progress --}}
            <x-card class="!p-4 bg-white/50 border-white">
                <div class="flex items-center gap-6">
                    <div class="relative h-20 w-20">
                        <svg class="h-20 w-20 -rotate-90" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="#f1f5f9" stroke-width="8"/>
                            <circle cx="40" cy="40" r="34" fill="none"
                                stroke="{{ $overallPercent >= 75 ? '#10b981' : '#f43f5e' }}"
                                stroke-width="8" stroke-linecap="round"
                                stroke-dasharray="{{ 2 * 3.14159 * 34 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 34 * (1 - $overallPercent / 100) }}"
                                class="transition-all duration-1000 ease-out"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-black {{ $overallPercent >= 75 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $overallPercent }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Overall Presence</p>
                        <x-badge type="{{ $overallPercent >= 75 ? 'success' : 'danger' }}" class="text-[10px] font-black uppercase">
                            {{ $overallPercent >= 75 ? 'Meets Requirement' : 'Below 75% Threshold' }}
                        </x-badge>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $statGrid = [
                    ['Total Classes', $totalCount, 'bi-calendar3', 'violet', 'primary'],
                    ['Present', $presentCount, 'bi-check-circle-fill', 'emerald', 'success'],
                    ['Absent', $absentCount, 'bi-x-circle-fill', 'rose', 'danger'],
                    ['Attendance %', $overallPercent . '%', 'bi-graph-up', 'amber', 'warning'],
                ];
            @endphp
            @foreach($statGrid as $stat)
                <x-card class="border-l-4 border-l-{{ $stat[3] }}-500">
                    <div class="flex justify-between items-start mb-4">
                        <div class="h-11 w-11 rounded-xl bg-{{ $stat[3] }}-500/10 text-{{ $stat[3] }}-600 flex items-center justify-center text-xl">
                            <i class="bi {{ $stat[2] }}"></i>
                        </div>
                        <x-badge type="{{ $stat[4] }}">{{ $stat[0] }}</x-badge>
                    </div>
                    <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $stat[1] }}</div>
                </x-card>
            @endforeach
        </div>

        {{-- Calendar + Subject Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Calendar --}}
            <div class="lg:col-span-2 space-y-4">
                <x-card class="overflow-hidden !p-0">
                    <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <x-button variant="outline" size="sm" onclick="changeMonth(-1)" icon="bi-chevron-left" class="!px-3"></x-button>
                        <h3 class="text-base font-black text-slate-800 tracking-tight" id="calTitle">Month Year</h3>
                        <x-button variant="outline" size="sm" onclick="changeMonth(1)" icon="bi-chevron-right" class="!px-3"></x-button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="scal-table">
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
                            <tbody id="calGrid">
                                {{-- JS-rendered --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Legend --}}
                    <div class="px-8 py-4 border-t border-slate-100 flex items-center gap-6">
                        <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm"></span><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Present</span></div>
                        <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-rose-500 shadow-sm"></span><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Absent</span></div>
                        <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No Record</span></div>
                    </div>
                </x-card>
            </div>

            {{-- Monthly Breakdown sidebar --}}
            <div class="space-y-6">
                <x-card class="border-t-4 border-t-violet-600">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-10 w-10 rounded-2xl bg-violet-600 text-white flex items-center justify-center text-lg shadow-lg shadow-violet-200">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Monthly Recap</h3>
                    </div>

                    <div class="space-y-6" id="monthSummary">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Classes</p>
                                <p class="text-xl font-extrabold text-slate-700" id="mClasses">0</p>
                            </div>
                            <div class="bg-emerald-50 rounded-2xl p-4 text-center border border-emerald-100/50">
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                                <p class="text-xl font-extrabold text-emerald-600" id="mPresent">0</p>
                            </div>
                            <div class="bg-rose-50 rounded-2xl p-4 text-center border border-rose-100/50">
                                <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Absent</p>
                                <p class="text-xl font-extrabold text-rose-500" id="mAbsent">0</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="relative h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                                <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(16,185,129,0.2)]" id="mBar" style="width:0%"></div>
                            </div>
                            <p class="text-center text-[10px] font-black text-slate-500 uppercase tracking-widest" id="mPct">0% monthly attendance</p>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Subject-wise Breakdown --}}
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <div class="h-1px flex-1 bg-slate-100"></div>
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Course Analytics</h3>
                <div class="h-1px flex-1 bg-slate-100"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($subjectWise as $row)
                    @php
                        $color = $row['percent'] >= 90 ? 'emerald' : ($row['percent'] >= 75 ? 'amber' : 'rose');
                        $badgeType = $row['percent'] >= 90 ? 'success' : ($row['percent'] >= 75 ? 'warning' : 'danger');
                    @endphp
                    <x-card class="group hover:-translate-y-2 transition-all duration-500 border-b-4 border-b-{{ $color }}-500">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-12 w-12 rounded-2xl bg-{{ $color }}-500/10 text-{{ $color }}-600 flex items-center justify-center text-sm font-black shadow-inner">
                                {{ strtoupper(substr($row['subject']->name ?? 'N/A', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-black text-slate-900 truncate tracking-tight">{{ $row['subject']->name ?? 'N/A' }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <x-badge type="{{ $badgeType }}" class="text-[9px] px-2">{{ $row['percent'] }}%</x-badge>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $row['total'] }} Sessions</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex-1 bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Present</p>
                                <p class="text-lg font-black text-slate-700 leading-none">{{ $row['present'] }}</p>
                            </div>
                            <div class="flex-1 bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                                <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1">Absent</p>
                                <p class="text-lg font-black text-slate-700 leading-none">{{ $row['absent'] }}</p>
                            </div>
                        </div>

                        <div class="relative h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-{{ $color }}-500 rounded-full transition-all duration-700" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </x-card>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-card class="py-20 text-center border-dashed border-2">
                            <div class="h-20 w-20 rounded-3xl bg-slate-50 text-slate-300 flex items-center justify-center text-3xl mx-auto mb-6">
                                <i class="bi bi-calendar-x"></i>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 mb-2">No Records Yet</h3>
                            <p class="text-sm text-slate-400 font-medium">Your attendance records will appear here once faculty marks them.</p>
                        </x-card>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .scal-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .scal-table thead th {
            padding: 1.25rem 0.5rem; text-align: center; font-size: 10px; font-weight: 900;
            color: #94a3b8; text-transform: uppercase; letter-spacing: 0.15em;
            border-bottom: 1px solid #f1f5f9; background: #f8fafc;
        }
        .scal-table thead th.sun-header { color: #f43f5e; background: #fff1f2/50; }
        .scal-table td {
            height: 100px; vertical-align: top; border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9; padding: 0.75rem; position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scal-table td:last-child { border-right: none; }
        .scal-table tr:last-child td { border-bottom: none; }
        .scal-table td:hover:not(.empty) { background: #fdfdfd; }
        .scal-table td.today { background: #ede9fe/30; }
        .scal-table td.today .sday-num {
            background: #7c3aed; color: white; width: 24px; height: 24px;
            display: flex; align-items: center; justify-content: center; border-radius: 8px; margin-left: auto;
            box-shadow: 0 4px 6px -1px rgba(124, 58, 237, 0.3); font-size: 11px;
        }
        .scal-table td.empty { background: #fafafa; }
        .scal-table td.sunday { background: #fff1f2/20; }
        .scal-table .sday-num { text-align: right; font-size: 12px; font-weight: 800; color: #64748b; }
        .scal-table td.sunday .sday-num { color: #f43f5e; }
        .scal-table .holiday-tag {
            font-size: 8px; font-weight: 900; color: #f43f5e; background: #fff1f2;
            border-radius: 6px; padding: 2px 5px; margin-top: 6px; display: inline-block;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .scal-table .dots { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 8px; }
        .scal-table .dot { width: 5px; height: 5px; border-radius: 50%; box-shadow: 0 0 4px rgba(0,0,0,0.1); }
        .dot-present { background: #10b981; }
        .dot-absent { background: #f43f5e; }

        /* Modern Tooltip */
        .scal-table .tooltip-box {
            opacity: 0; pointer-events: none; position: absolute; bottom: calc(100% + 12px); left: 50%;
            transform: translateX(-50%) translateY(10px); background: #1e293b; border-radius: 12px; padding: 10px 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); z-index: 50; min-width: 160px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scal-table .tooltip-box::after {
            content: ''; position: absolute; top: 100%; left: 50%; transform: translateX(-50%);
            border: 6px solid transparent; border-top-color: #1e293b;
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