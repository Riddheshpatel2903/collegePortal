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
    <x-page-header 
        title="Edit Student Nexus" 
        subtitle="Update identity parameters and portal access permissions."
        icon="bi-pencil-square"
        back="{{ route('admin.students.index') }}"
    />

    <div class="max-w-4xl mx-auto">
        <x-form-card 
            id="editStudentForm"
            action="{{ route('admin.students.update', $student->id) }}" 
            method="PUT"
            title="Identity Update"
            subtitle="Record ID: {{ $student->gtu_enrollment_no ?? $student->id }}"
            icon="bi-fingerprint"
            submitLabel="Commit Changes"
            submitIcon="bi-floppy-fill"
        >
            {{-- Global error box (AJAX-populated) --}}
            <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                <ul id="formErrorList" class="text-sm text-rose-600 space-y-1"></ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Legal Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $student->user->name) }}" class="field input-premium w-full" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Nexus Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->user->email) }}" class="field input-premium w-full" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Roll Identifier</label>
                    <input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}" class="field input-premium w-full" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">GTU Enrollment</label>
                    <input type="text" name="gtu_enrollment_no" value="{{ old('gtu_enrollment_no', $student->gtu_enrollment_no) }}" class="field input-premium w-full" required>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Primary Domain</label>
                    <select name="course_id" class="field input-premium w-full !py-2.5">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id', $student->course_id) == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Current Academic Year</label>
                    <input type="number" name="current_year" value="{{ old('current_year', $student->current_year ?? 1) }}" class="field input-premium w-full" min="1" max="10">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Admission Batch</label>
                    <input type="number" name="admission_year" value="{{ old('admission_year', $student->admission_year) }}" class="field input-premium w-full">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Contact Protocol</label>
                    <input type="tel" name="phone" value="{{ old('phone', $student->phone) }}" class="field input-premium w-full">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Physical Residence</label>
                    <input type="text" name="address" value="{{ old('address', $student->address) }}" class="field input-premium w-full">
                    <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                </div>

                <div class="md:col-span-2 space-y-1">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Portal Access Matrix</label>
                    <div id="toggleCard" class="flex items-center justify-between gap-4 p-5 rounded-2xl border transition-all duration-300
                        {{ old('is_active', $student->is_active) ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200' }}">

                        <div class="flex items-center gap-4">
                            <div id="toggleIcon" class="h-12 w-12 rounded-2xl flex items-center justify-center text-2xl transition-all duration-300
                                {{ old('is_active', $student->is_active) ? 'bg-emerald-100 text-emerald-600 shadow-sm shadow-emerald-200/50' : 'bg-rose-100 text-rose-500 shadow-sm shadow-rose-200/50' }}">
                                <i id="toggleIconInner" class="bi {{ old('is_active', $student->is_active) ? 'bi-person-check-fill' : 'bi-person-dash-fill' }}"></i>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-800 tracking-tight">Active Identity Status</span>
                                <span id="toggleLabel" class="block text-[10px] font-black uppercase tracking-widest transition-colors duration-300
                                    {{ old('is_active', $student->is_active) ? 'text-emerald-600' : 'text-rose-500' }}">
                                    {{ old('is_active', $student->is_active) ? 'Nexus Authorized — Full Access' : 'Nexus Locked — Access Revoked' }}
                                </span>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="isActiveToggle" value="1" {{ old('is_active', $student->is_active) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                        </label>
                    </div>
                </div>
            </div>
        </x-form-card>
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
        const btn = document.getElementById('editStudentForm-submit');
        const lbl = document.getElementById('editStudentForm-label');
        const icon = btn?.querySelector('i');
        
        if (btn) btn.disabled = loading;
        if (icon) icon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-floppy-fill';
        if (lbl) lbl.textContent = loading ? 'Saving...' : 'Commit Changes';
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
