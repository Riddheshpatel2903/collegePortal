@extends('layouts.app')

@section('header_title', 'Add Student')

@section('content')
    {{-- Toast --}}
    <div id="ajaxToast" class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white
                   translate-y-20 opacity-0 transition-all duration-500 pointer-events-none" style="min-width:260px">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>

    <x-page-header 
        title="Enroll New Student" 
        subtitle="Initialize a new identity in the academic nexus."
        icon="bi-person-plus-fill"
        back="{{ route('admin.students.index') }}"
    />

    <div class="max-w-4xl mx-auto">
        <x-form-card 
            id="createStudentForm"
            action="{{ route('admin.students.store') }}" 
            method="POST"
            title="Identity Configuration"
            subtitle="Personal & Academic Parameters"
            icon="bi-shield-lock-fill"
            submitLabel="Authenticate & Enroll"
            submitIcon="bi-person-plus-fill"
            reset="true"
        >
            {{-- Global error box (AJAX-populated) --}}
            <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                <ul id="formErrorList" class="text-sm text-rose-600 space-y-1"></ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Legal Full Name</label>
                    <input type="text" name="name" class="field input-premium w-full" placeholder="e.g. John Doe" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Nexus Email</label>
                    <input type="email" name="email" class="field input-premium w-full" placeholder="email@institution.edu" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Roll Identifier</label>
                    <input type="text" name="roll_number" class="field input-premium w-full" placeholder="CS2024001" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">GTU Enrollment</label>
                    <input type="text" name="gtu_enrollment_no" class="field input-premium w-full" placeholder="GTU2024XXXXXX" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Primary Domain</label>
                    <select name="course_id" class="field input-premium w-full !py-2.5">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Current Academic Year</label>
                    <input type="number" name="current_year" class="field input-premium w-full" min="1" max="10" placeholder="1">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Admission Batch</label>
                    <input type="number" name="admission_year" class="field input-premium w-full" placeholder="{{ date('Y') }}">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Contact Protocol</label>
                    <input type="tel" name="phone" class="field input-premium w-full" placeholder="+91 xxxxxxxxxx">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Nexus Credentials (Password)</label>
                    <input type="password" name="password" class="field input-premium w-full" placeholder="••••••••" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Physical Residence</label>
                    <input type="text" name="address" class="field input-premium w-full" placeholder="Street, City, Postal Code">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>
            </div>
        </x-form-card>
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
                const btn = document.getElementById('createStudentForm-submit');
                const lbl = document.getElementById('createStudentForm-label');
                const icon = btn?.querySelector('i');
                
                if (btn) btn.disabled = loading;
                if (icon) icon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-person-plus-fill';
                if (lbl) lbl.textContent = loading ? 'Enrolling...' : 'Authenticate & Enroll';
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