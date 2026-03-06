@extends('layouts.app')

@section('header_title', 'Manage Results')

@section('content')

    <div class="space-y-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-badge type="info" class="mb-4">
                    <i class="bi bi-shield-check mr-1"></i> Examination Office
                </x-badge>
                <h2 class="text-4xl font-black text-slate-900 tracking-tight leading-none mb-3">Result <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Management</span>
                </h2>
                <p class="text-lg text-slate-400 font-medium tracking-tight">Enter marks, review performance and publish official results.</p>
            </div>
        </div>

        {{-- ════════════ A. SELECTION PANEL ════════════ --}}
        <x-card class="relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="bi bi-gear-wide-connected text-8xl"></i>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row gap-6 items-end">
                <div class="flex-1 w-full">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Academic Semester</label>
                    <div class="relative group/select">
                        <select id="semesterSelect" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 appearance-none focus:bg-white focus:border-violet-500 transition-all outline-none group-hover/select:border-slate-200">
                            <option value="">Choose a semester...</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }} — {{ $sem->course->name ?? '' }}</option>
                            @endforeach
                        </select>
                        <i class="bi bi-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                <div class="w-full md:w-64">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Academic Year</label>
                    <div class="relative group/input">
                        <input type="text" id="yearInput" placeholder="2025-2026"
                            value="{{ date('Y') . '-' . (date('Y') + 1) }}"
                            class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:border-violet-500 transition-all outline-none group-hover/input:border-slate-200">
                        <i class="bi bi-calendar3 absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                <x-button variant="primary" onclick="loadStudents()" id="loadBtn" icon="bi-cloud-download" class="w-full md:w-auto !py-4 px-8 shadow-xl shadow-violet-200">
                    Load Student List
                </x-button>
            </div>
        </x-card>

        {{-- ════════════ B. MARKS ENTRY GRID ════════════ --}}
        <x-card class="!p-0 overflow-hidden" id="marksSection" style="display: none;">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-white border border-slate-200 text-violet-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">Student Performance Entry</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" id="studentCount"></p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <x-button variant="outline" size="sm" onclick="saveMarks('draft')" id="saveDraftBtn" icon="bi-save">
                        Keep as Draft
                    </x-button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-premium" id="marksTable">
                    <thead id="marksHead"></thead>
                    <tbody id="marksBody"></tbody>
                </table>
            </div>

            {{-- Footer Actions --}}
            <div class="px-8 py-6 border-t border-slate-100 bg-slate-50/30 flex flex-col md:flex-row items-center justify-between gap-6"
                id="footerActions" style="display: none;">
                <div class="flex items-center gap-3" id="publishInfo">
                    {{-- Dynamic --}}
                </div>
                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto justify-center md:justify-end">
                    <x-button variant="outline" onclick="confirmUnlockAll()" id="unlockBtn" icon="bi-unlock-fill" class="bg-white">
                        Unlock All
                    </x-button>
                    <x-button variant="primary" onclick="saveMarks('draft')" id="saveDraftBtn2" icon="bi-save" class="bg-slate-900 border-none">
                        Save Changes
                    </x-button>
                    <x-button variant="primary" onclick="confirmPublish()" id="publishBtn" icon="bi-check-circle-fill" class="bg-emerald-600 border-none shadow-lg shadow-emerald-100">
                        Finalize & Publish
                    </x-button>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Modal Template with x-card --}}
    <div id="publishModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden animate-in fade-in duration-300">
        <div class="w-full max-w-lg mx-4">
            <x-card class="!p-10 text-center shadow-2xl relative border-none">
                <div class="h-20 w-20 rounded-[2rem] bg-emerald-50 text-emerald-500 flex items-center justify-center text-4xl mx-auto mb-8 shadow-sm">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-4">Official Publication</h3>
                <p class="text-sm text-slate-400 font-medium leading-relaxed mb-8">
                    By publishing, you are certifying these marks as official. They will be visible to students and their academic records will be updated.
                </p>

                <div id="publishModalStudentList" class="mb-10">
                    {{-- Dynamic Summary --}}
                </div>

                <div class="flex gap-4">
                    <x-button variant="outline" onclick="closePublishModal()" class="flex-1 py-4">
                        Review Again
                    </x-button>
                    <x-button variant="primary" onclick="publishAll()" id="finalPublishBtn" class="flex-1 py-4 bg-emerald-600 border-none">
                        Yes, Publish Now
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>

    <style>
        .field-label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .field-select,
        .field-input {
            width: 100%;
            background: white;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            transition: all 0.2s;
        }

        .field-select:focus,
        .field-input:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            outline: none;
        }

        /* Marks table */
        #marksTable thead th {
            padding: 12px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 5;
            white-space: nowrap;
        }

        #marksTable tbody td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            transition: background 0.2s;
        }

        #marksTable tbody tr:hover td {
            background: rgba(139, 92, 246, 0.02);
        }

        /* Inline inputs */
        .marks-input {
            width: 60px;
            text-align: center;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 4px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            background: #fafbfc;
            transition: all 0.2s;
        }

        .marks-input:focus {
            border-color: #8b5cf6;
            background: white;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
            outline: none;
        }

        .marks-input:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f1f5f9;
        }

        .grade-preview {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot--pass {
            background: #10b981;
        }

        .status-dot--fail {
            background: #f43f5e;
        }

        .total-cell {
            font-size: 14px;
            font-weight: 800;
            color: #334155;
        }

        /* Status badge */
        .draft-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid rgba(146, 64, 14, 0.1);
        }

        .published-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid rgba(6, 95, 70, 0.1);
        }

        /* Premium Publish Button */
        .btn-publish {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            background-size: 200% auto;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-publish:hover {
            background-position: right center;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }

        .bg-success {
            background-color: #10b981 !important;
        }

        @keyframes pulse-success {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .publish-success {
            animation: pulse-success 2s infinite;
        }

        /* Loading shimmer */
        .shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }
    </style>

    <script>
        const LOAD_URL = @json(route('teacher.results.load'));
        const STORE_URL = @json(route('teacher.results.store'));
        const PUBLISH_BASE_URL = "{{ url('/teacher/results/publish') }}";
        const UNLOCK_BASE_URL = "{{ url('/teacher/results/unlock') }}";
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        let loadedData = null; // { subjects, students, marks }

        // ── Load Students ──
        async function loadStudents() {
            const semId = document.getElementById('semesterSelect').value;
            const year = document.getElementById('yearInput').value.trim();

            if (!semId || !year) {
                alert('Please select a semester and enter an academic year.');
                return;
            }

            const btn = document.getElementById('loadBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Loading...';

            try {
                const res = await fetch(`${LOAD_URL}?semester_id=${semId}&academic_year=${encodeURIComponent(year)}`);
                loadedData = await res.json();
                renderMarksGrid();
                document.getElementById('marksSection').style.display = '';
                document.getElementById('footerActions').style.display = '';
                document.getElementById('studentCount').textContent = `${loadedData.students.length} students`;
            } catch (err) {
                alert('Failed to load students.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-cloud-download"></i> Load Students';
            }
        }

        // ── Render Grid ──
        function renderMarksGrid() {
            const { subjects, students, marks } = loadedData;

            // Header
            let headHtml = '<tr><th class="text-left pl-4">#</th><th class="text-left">Student</th>';
            subjects.forEach(sub => {
                headHtml += `<th colspan="2" class="text-center" style="border-left: 2px solid #e2e8f0;">${sub.name}</th>`;
            });
            headHtml += '<th class="text-center">Total</th><th class="text-center">%</th><th class="text-center">Status</th></tr>';

            // Sub-header for Internal/Final
            headHtml += '<tr><th></th><th></th>';
            subjects.forEach(() => {
                headHtml += '<th class="text-center" style="border-left: 2px solid #e2e8f0; font-size:9px; color:#94a3b8;">INT</th>';
                headHtml += '<th class="text-center" style="font-size:9px; color:#94a3b8;">FINAL</th>';
            });
            headHtml += '<th></th><th></th><th></th></tr>';
            document.getElementById('marksHead').innerHTML = headHtml;

            // Body
            let bodyHtml = '';
            students.forEach((stu, idx) => {
                const stuMarks = marks[stu.id] || null;
                const isPublished = stuMarks && stuMarks.status === 'published';
                const resultId = stuMarks ? stuMarks.result_id : null;

                bodyHtml += `<tr data-student="${stu.id}" data-result-id="${resultId || ''}">`;
                bodyHtml += `<td class="pl-4 text-sm font-bold text-slate-400">${idx + 1}</td>`;
                bodyHtml += `<td>
                                                            <div class="flex items-center gap-3">
                                                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(stu.name)}&background=ede9fe&color=7c3aed&size=32"
                                                                     class="h-8 w-8 rounded-lg" alt="">
                                                                <div>
                                                                    <div class="text-sm font-semibold text-slate-700">${stu.name}</div>
                                                                    <div class="text-[10px] font-bold text-slate-400">${stu.roll}</div>
                                                                </div>
                                                            </div>
                                                            ${isPublished ? `
                                                                <div class="flex items-center gap-2 mt-1">
                                                                    <span class="published-badge" style="font-size:9px;padding:2px 8px;"><i class="bi bi-lock-fill"></i> Published</span>
                                                                    <button onclick="unlockResult('${resultId}')" class="text-[10px] font-bold text-violet-600 hover:text-violet-700 underline">
                                                                        Unlock
                                                                    </button>
                                                                </div>
                                                            ` : ''}
                                                        </td>`;

                let rowTotal = 0;
                subjects.forEach(sub => {
                    const subMarks = stuMarks?.subjects?.[sub.id] || {};
                    const intVal = subMarks.internal ?? '';
                    const finVal = subMarks.final ?? '';
                    const total = (parseInt(intVal) || 0) + (parseInt(finVal) || 0);
                    rowTotal += total;

                    bodyHtml += `<td class="text-center" style="border-left: 2px solid #f1f5f9;">
                                                                <input type="number" min="0" max="50" value="${intVal}"
                                                                       class="marks-input" data-student="${stu.id}" data-subject="${sub.id}" data-type="internal"
                                                                       onchange="recalcRow(this)" ${isPublished ? 'disabled' : ''}>
                                                            </td>`;
                    bodyHtml += `<td class="text-center">
                                                                <input type="number" min="0" max="50" value="${finVal}"
                                                                       class="marks-input" data-student="${stu.id}" data-subject="${sub.id}" data-type="final"
                                                                       onchange="recalcRow(this)" ${isPublished ? 'disabled' : ''}>
                                                            </td>`;
                });

                const maxTotal = subjects.length * 100;
                const pct = maxTotal > 0 ? Math.round((rowTotal / maxTotal) * 100) : 0;
                const allFail = rowTotal < (subjects.length * 40); // simplified check

                bodyHtml += `<td class="text-center total-cell" data-total-for="${stu.id}">${rowTotal}</td>`;
                bodyHtml += `<td class="text-center text-sm font-bold text-slate-600" data-pct-for="${stu.id}">${pct}%</td>`;
                bodyHtml += `<td class="text-center" data-status-for="${stu.id}">
                                                            <span class="status-dot ${pct >= 40 ? 'status-dot--pass' : 'status-dot--fail'}"></span>
                                                        </td>`;
                bodyHtml += '</tr>';
            });

            document.getElementById('marksBody').innerHTML = bodyHtml;
        }

        // ── Recalculate Row ──
        function recalcRow(input) {
            const studentId = input.dataset.student;
            const row = input.closest('tr');
            const inputs = row.querySelectorAll('.marks-input');

            let total = 0;
            inputs.forEach(inp => { total += parseInt(inp.value) || 0; });

            const subjectCount = loadedData.subjects.length;
            const maxTotal = subjectCount * 100;
            const pct = maxTotal > 0 ? Math.round((total / maxTotal) * 100) : 0;

            row.querySelector(`[data-total-for="${studentId}"]`).textContent = total;
            row.querySelector(`[data-pct-for="${studentId}"]`).textContent = pct + '%';

            const statusEl = row.querySelector(`[data-status-for="${studentId}"]`);
            statusEl.innerHTML = `<span class="status-dot ${pct >= 40 ? 'status-dot--pass' : 'status-dot--fail'}"></span>`;
        }

        // ── Save Marks ──
        async function saveMarks(mode) {
            const btn = document.getElementById('saveDraftBtn2');
            const originalContent = btn.innerHTML;
            const isSilent = mode === 'silent';

            if (!isSilent) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Saving...';
            }

            const marksPayload = {};
            const rows = document.querySelectorAll('#marksBody tr');

            rows.forEach(row => {
                const studentId = row.dataset.student;
                if (row.querySelector('.marks-input:disabled')) return;

                marksPayload[studentId] = {};
                const inputs = row.querySelectorAll('.marks-input');
                inputs.forEach(inp => {
                    const subId = inp.dataset.subject;
                    const type = inp.dataset.type;
                    if (!marksPayload[studentId][subId]) marksPayload[studentId][subId] = {};
                    marksPayload[studentId][subId][type] = parseInt(inp.value) || 0;
                });
            });

            const semId = document.getElementById('semesterSelect').value;
            const year = document.getElementById('yearInput').value.trim();

            try {
                const res = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({ semester_id: semId, academic_year: year, marks: marksPayload })
                });

                const data = await res.json();
                if (data.success) {
                    if (!isSilent) showToast('Marks saved successfully!', 'success');
                    await loadStudents();
                } else {
                    if (!isSilent) showToast('Failed to save marks.', 'error');
                    throw new Error('Save failed');
                }
            } catch (err) {
                if (!isSilent) showToast('Network error. Please try again.', 'error');
                throw err;
            } finally {
                if (!isSilent) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            }
        }

        // ── Publish Modal ──
        async function confirmPublish() {
            const publishBtn = document.getElementById('publishBtn');
            const originalContent = publishBtn.innerHTML;

            // 1. Show progress
            publishBtn.disabled = true;
            publishBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Preparing...';

            try {
                // 2. Automatically save all current marks first to ensure they exist as results in DB
                await saveMarks('silent');

                // 3. Collect draft students
                const draftStudents = [];
                document.querySelectorAll('#marksBody tr').forEach(row => {
                    const resultId = row.dataset.resultId;
                    const inputs = row.querySelectorAll('.marks-input:not(:disabled)');
                    if (inputs.length > 0 && resultId) {
                        const name = row.querySelector('.text-sm.font-semibold')?.textContent || 'Student';
                        draftStudents.push({ id: resultId, name });
                    }
                });

                if (draftStudents.length === 0) {
                    showToast('No new results to publish.', 'info');
                    return;
                }

                const listEl = document.getElementById('publishModalStudentList');
                listEl.innerHTML = `
                                            <div class="bg-slate-50 border border-slate-200/60 rounded-2xl p-5 text-center shadow-sm w-full">
                                                <div class="text-[11px] uppercase tracking-widest text-slate-400 font-black mb-1">Queue Summary</div>
                                                <div class="text-2xl font-black text-slate-800">${draftStudents.length} <sub class="text-slate-400 text-xs font-bold -ml-1">Results</sub></div>
                                            </div>
                                        `;

                document.getElementById('publishModal').classList.remove('hidden');
            } catch (err) {
                showToast('Failed to prepare results for publishing.', 'error');
            } finally {
                publishBtn.disabled = false;
                publishBtn.innerHTML = originalContent;
            }
        }

        function closePublishModal() {
            document.getElementById('publishModal').classList.add('hidden');
        }

        async function publishAll() {
            const finalBtn = document.getElementById('finalPublishBtn');
            const originalFinalContent = finalBtn.innerHTML;

            finalBtn.disabled = true;
            finalBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Publishing...';

            const footerBtn = document.getElementById('publishBtn');
            const originalFooterContent = footerBtn.innerHTML;
            footerBtn.disabled = true;
            footerBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Processing...';

            const draftResults = [];
            document.querySelectorAll('#marksBody tr').forEach(row => {
                const resultId = row.dataset.resultId;
                if (resultId && !row.querySelector('.marks-input:disabled')) {
                    draftResults.push(resultId);
                }
            });

            if (draftResults.length === 0) {
                closePublishModal();
                return;
            }

            let successCount = 0;
            try {
                // Process in parallel
                const promises = draftResults.map(id =>
                    fetch(`${PUBLISH_BASE_URL}/${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
                    }).then(res => res.ok ? successCount++ : null)
                );

                await Promise.all(promises);

                closePublishModal();

                // Show success state on footer button
                footerBtn.classList.add('publish-success');
                footerBtn.innerHTML = '<i class="bi bi-check-lg"></i> Results Published!';

                showToast(`Successfully published ${successCount} result(s).`, 'success');

                setTimeout(async () => {
                    await loadStudents();
                    footerBtn.classList.remove('publish-success');
                    footerBtn.disabled = false;
                    footerBtn.innerHTML = originalFooterContent;
                }, 2000);

            } catch (err) {
                showToast('An error occurred during publishing.', 'error');
                footerBtn.disabled = false;
                footerBtn.innerHTML = originalFooterContent;
            } finally {
                finalBtn.disabled = false;
                finalBtn.innerHTML = originalFinalContent;
            }
        }

        async function unlockResult(resultId) {
            if (!confirm('Are you sure you want to unlock this result? This will allow editing again and it will remain a draft until you publish it.')) return;

            try {
                const res = await fetch(`${UNLOCK_BASE_URL}/${resultId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });

                const data = await res.json();
                if (data.success) {
                    showToast('Result unlocked for editing!', 'success');
                    await loadStudents();
                } else {
                    showToast('Failed to unlock result.', 'error');
                }
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
            }
        }

        async function confirmUnlockAll() {
            const publishedResults = [];
            document.querySelectorAll('#marksBody tr').forEach(row => {
                const resultId = row.dataset.resultId;
                if (resultId && row.querySelector('.published-badge')) {
                    publishedResults.push(resultId);
                }
            });

            if (publishedResults.length === 0) {
                showToast('No published results found in this view.', 'info');
                return;
            }

            if (!confirm(`Unlock all ${publishedResults.length} published results in this view?`)) return;

            const btn = document.getElementById('unlockBtn');
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Unlocking...';

            try {
                const promises = publishedResults.map(id =>
                    fetch(`${UNLOCK_BASE_URL}/${id}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
                    })
                );

                await Promise.all(promises);
                showToast(`Successfully unlocked ${publishedResults.length} results.`, 'success');
                await loadStudents();
            } catch (err) {
                showToast('An error occurred during bulk unlock.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        // ── Toast ──
        function showToast(msg, type) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-[100] px-5 py-3 rounded-xl text-sm font-bold shadow-xl transition-all transform translate-y-0 ${type === 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'}`;
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
    </script>
@endsection
