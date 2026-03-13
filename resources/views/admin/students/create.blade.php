@extends('layouts.app')

@section('header_title', 'Add Student')

@section('content')
    {{-- Toast --}}
    <div id="ajaxToast" class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white
                   translate-y-20 opacity-0 transition-all duration-500 pointer-events-none" style="min-width:260px">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">New Student</h2>
            <p class="text-sm text-slate-400 mt-1">Enroll a new student into the system.</p>
        </div>
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form id="createStudentForm" action="{{ route('admin.students.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Global error box (AJAX-populated) --}}
                <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                    <ul id="formErrorList" class="text-sm text-rose-600 space-y-1"></ul>
                </div>

                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Student Information</h3>
                        <p class="text-xs text-slate-400">Enter the student's personal and academic details.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Full
                            Name</label>
                        <input type="text" name="name" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Student Name" required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="email" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Email Address" required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Roll
                            Number</label>
                        <input type="text" name="roll_number" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="CS2024001" required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">GTU
                            Enrollment No</label>
                        <input type="text" name="gtu_enrollment_no" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="GTU2024XXXXXX" required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Course</label>
                        <select name="course_id"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Current
                            Year</label>
                        <input type="number" name="current_year" value="" min="1" max="10"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Current Year">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Admission
                            Year</label>
                        <input type="number" name="admission_year" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Admission Year">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" name="phone" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="+91 xxxxxxxxxx">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                        <input type="password" name="password"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="••••••••" required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Address</label>
                        <input type="text" name="address" value=""
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            placeholder="Full address">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="reset"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Reset</button>
                    <button type="submit" id="submitBtn"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center gap-2">
                        <i id="submitIcon" class="bi bi-person-plus-fill"></i>
                        <span id="submitLabel">Enroll Student</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('createStudentForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitIcon = document.getElementById('submitIcon');
            const submitLbl = document.getElementById('submitLabel');
            const errorBox = document.getElementById('formErrorBox');
            const errorList = document.getElementById('formErrorList');
            const toast = document.getElementById('ajaxToast');
            const toastMsg = document.getElementById('ajaxToastMsg');
            const toastIcon = document.getElementById('ajaxToastIcon');
            let toastTimer = null;

            function showToast(message, type = 'success') {
                clearTimeout(toastTimer);
                toastMsg.textContent = message;
                toast.className = toast.className.replace(/bg-\S+/g, '').trim();
                if (type === 'success') {
                    toast.classList.add('bg-emerald-500');
                    toastIcon.className = 'bi bi-check-circle-fill text-lg';
                } else {
                    toast.classList.add('bg-rose-500');
                    toastIcon.className = 'bi bi-exclamation-circle-fill text-lg';
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

            function clearErrors() {
                errorBox.classList.add('hidden');
                errorList.innerHTML = '';
                form.querySelectorAll('.field').forEach(el => {
                    el.classList.remove('border-rose-400', 'bg-rose-50/30');
                });
                form.querySelectorAll('.field-error').forEach(el => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });
            }

            function showErrors(errors) {
                errorList.innerHTML = '';
                Object.entries(errors).forEach(([field, messages]) => {
                    // Inline error per field
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('border-rose-400', 'bg-rose-50/30');
                        const errEl = input.closest('div').querySelector('.field-error');
                        if (errEl) {
                            errEl.textContent = messages[0];
                            errEl.classList.remove('hidden');
                        }
                    }
                    // Also add to global box
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center gap-2';
                        li.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${msg}`;
                        errorList.appendChild(li);
                    });
                });
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function setLoading(loading) {
                submitBtn.disabled = loading;
                submitIcon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-person-plus-fill';
                submitLbl.textContent = loading ? 'Enrolling...' : 'Enroll Student';
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearErrors();
                setLoading(true);

                const formData = new FormData(form);

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token'),
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (res.status === 422) {
                        showErrors(data.errors || {});
                        showToast('Please fix the errors below.', 'error');
                        return;
                    }

                    if (!data.success) throw new Error(data.message || 'Failed');

                    showToast(data.message || 'Student enrolled successfully!', 'success');
                    form.reset();
                    setTimeout(() => { window.location.href = data.redirect; }, 1500);
                } catch (err) {
                    showToast(err.message || 'Something went wrong.', 'error');
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
@endpush