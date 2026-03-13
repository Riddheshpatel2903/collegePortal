@extends('layouts.app')

@section('header_title', 'Edit Student')

@section('content')
    {{-- Toast --}}
    <div id="ajaxToast"
        class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold text-white
               translate-y-20 opacity-0 transition-all duration-500 pointer-events-none"
        style="min-width:260px">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Edit Student</h2>
            <p class="text-sm text-slate-400 mt-1">Update student enrollment details.</p>
        </div>
        <a href="{{ route('admin.students.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:text-violet-600 hover:border-violet-200 transition-all">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="glass-card max-w-3xl">
        <div class="p-8">
            <form id="editStudentForm" action="{{ route('admin.students.update', $student->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Global error box (AJAX-populated) --}}
                <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                    <ul id="formErrorList" class="text-sm text-rose-600 space-y-1"></ul>
                </div>

                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i
                            class="bi bi-pencil-square"></i></div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Update Student</h3>
                        <p class="text-xs text-slate-400">Modify student details and enrollment.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $student->user->name) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $student->user->email) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Roll Number</label>
                        <input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">GTU Enrollment No</label>
                        <input type="text" name="gtu_enrollment_no" value="{{ old('gtu_enrollment_no', $student->gtu_enrollment_no) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all"
                            required>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Course</label>
                        <select name="course_id"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                            <option value="">Select Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $student->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Current Year</label>
                        <input type="number" name="current_year"
                            value="{{ old('current_year', $student->current_year ?? 1) }}" min="1" max="10"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Admission Year</label>
                        <input type="number" name="admission_year"
                            value="{{ old('admission_year', $student->admission_year) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $student->phone) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Address</label>
                        <input type="text" name="address" value="{{ old('address', $student->address) }}"
                            class="field w-full border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-700 bg-slate-50/50 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-300 transition-all">
                        <p class="field-error text-xs text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-2">
                        <div id="toggleCard" class="flex items-center justify-between gap-4 p-5 rounded-2xl border transition-all duration-300
                            {{ old('is_active', $student->is_active) ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200' }}">

                            <div class="flex items-center gap-3">
                                <div id="toggleIcon" class="h-10 w-10 rounded-xl flex items-center justify-center text-xl transition-colors duration-300
                                    {{ old('is_active', $student->is_active) ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-500' }}">
                                    <i id="toggleIconInner" class="bi {{ old('is_active', $student->is_active) ? 'bi-person-check-fill' : 'bi-person-dash-fill' }}"></i>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-700">Account Status</span>
                                    <span id="toggleLabel" class="block text-[10px] font-semibold uppercase tracking-wider transition-colors duration-300
                                        {{ old('is_active', $student->is_active) ? 'text-emerald-500' : 'text-rose-400' }}">
                                        {{ old('is_active', $student->is_active) ? '● Active — portal access enabled' : '● Inactive — portal access disabled' }}
                                    </span>
                                </div>
                            </div>

                            <label class="flex items-center cursor-pointer flex-shrink-0" for="isActiveToggle">
                                <input type="checkbox" name="is_active" id="isActiveToggle"
                                    {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                                    class="h-5 w-5 rounded-md border-2 cursor-pointer transition-all duration-200
                                        border-slate-300 bg-white
                                        checked:bg-emerald-500 checked:border-emerald-500
                                        focus:ring-2 focus:ring-emerald-200 focus:outline-none
                                        accent-emerald-500">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.students.index') }}"
                        class="px-6 py-2.5 text-sm font-semibold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</a>
                    <button type="submit" id="submitBtn"
                        class="px-8 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-xl hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center gap-2">
                        <i id="submitIcon" class="bi bi-floppy-fill"></i>
                        <span id="submitLabel">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    // ─── AJAX form submission ─────────────────────────────────────────────
    const form       = document.getElementById('editStudentForm');
    const submitBtn  = document.getElementById('submitBtn');
    const submitIcon = document.getElementById('submitIcon');
    const submitLbl  = document.getElementById('submitLabel');
    const errorBox   = document.getElementById('formErrorBox');
    const errorList  = document.getElementById('formErrorList');
    const toast      = document.getElementById('ajaxToast');
    const toastMsg   = document.getElementById('ajaxToastMsg');
    const toastIcon  = document.getElementById('ajaxToastIcon');
    let toastTimer   = null;

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
        form.querySelectorAll('.field').forEach(el => el.classList.remove('border-rose-400', 'bg-rose-50/30'));
        form.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
    }

    function showErrors(errors) {
        errorList.innerHTML = '';
        Object.entries(errors).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('border-rose-400', 'bg-rose-50/30');
                const errEl = input.closest('div').querySelector('.field-error');
                if (errEl) { errEl.textContent = messages[0]; errEl.classList.remove('hidden'); }
            }
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
        submitIcon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-floppy-fill';
        submitLbl.textContent = loading ? 'Saving...' : 'Save Changes';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',        // FormData carries _method=PUT
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

            showToast(data.message || 'Student updated successfully!', 'success');
        } catch (err) {
            showToast(err.message || 'Something went wrong.', 'error');
        } finally {
            setLoading(false);
        }
    });

    // ─── Live toggle card update ──────────────────────────────────────────
    const toggle = document.getElementById('isActiveToggle');
    const card   = document.getElementById('toggleCard');
    const icon   = document.getElementById('toggleIcon');
    const iconEl = document.getElementById('toggleIconInner');
    const label  = document.getElementById('toggleLabel');

    function updateCard(isActive) {
        if (isActive) {
            card.className  = card.className.replace(/bg-rose-\S+|border-rose-\S+/g, '').trim() + ' bg-emerald-50/60 border-emerald-200';
            icon.className  = icon.className.replace(/bg-rose-\S+|text-rose-\S+/g, '').trim() + ' bg-emerald-100 text-emerald-600';
            iconEl.className = 'bi bi-person-check-fill';
            label.className  = label.className.replace(/text-rose-\S+/g, '').trim() + ' text-emerald-500';
            label.textContent = '● Active — portal access enabled';
        } else {
            card.className  = card.className.replace(/bg-emerald-\S+|border-emerald-\S+/g, '').trim() + ' bg-rose-50/60 border-rose-200';
            icon.className  = icon.className.replace(/bg-emerald-\S+|text-emerald-\S+/g, '').trim() + ' bg-rose-100 text-rose-500';
            iconEl.className = 'bi bi-person-dash-fill';
            label.className  = label.className.replace(/text-emerald-\S+/g, '').trim() + ' text-rose-400';
            label.textContent = '● Inactive — portal access disabled';
        }
    }

    if (toggle) {
        toggle.addEventListener('change', () => updateCard(toggle.checked));
    }
})();
</script>
@endpush
