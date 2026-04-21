@extends('layouts.app')

@section('header_title', 'Edit Student')

@section('content')
    {{-- Toast Notification --}}
    <div id="ajaxToast" class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl text-xs font-bold text-white translate-y-20 opacity-0 transition-all duration-500 pointer-events-none">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>

    <x-page-header 
        title="Edit Student Profile" 
        subtitle="Modify academic records, personal information, and portal access status for this student."
        icon="bi-pencil-square"
        back="{{ route('admin.students.index') }}"
    />

    <div class="max-w-4xl mx-auto mt-8 pb-12">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Student Identity Settings</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Enrollment: {{ $student->gtu_enrollment_no }}</p>
                </div>
                <div class="h-12 w-12 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center text-xl shadow-sm">
                    <i class="bi bi-fingerprint"></i>
                </div>
            </div>

            <form id="editStudentForm" action="{{ route('admin.students.update', $student->id) }}" method="POST" class="p-8 space-y-8">
                @csrf
                @method('PUT')
                
                {{-- Global Error List --}}
                <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                    <ul id="formErrorList" class="text-[10px] font-bold text-rose-600 uppercase tracking-widest space-y-1"></ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $student->user->name) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" required pattern="[a-zA-Z\s.]+" title="Only characters, spaces and dots are allowed">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $student->user->email) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Roll Number</label>
                        <input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">GTU Enrollment No</label>
                        <input type="text" name="gtu_enrollment_no" value="{{ old('gtu_enrollment_no', $student->gtu_enrollment_no) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Academic Program</label>
                        <select name="course_id" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600">
                            <option value="">Select Branch...</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id', $student->course_id) == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Current Year</label>
                        <input type="number" name="current_year" value="{{ old('current_year', $student->current_year ?? 1) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12" min="1" max="5">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Admission Year</label>
                        <input type="number" name="admission_year" value="{{ old('admission_year', $student->admission_year) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Contact Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', $student->phone) }}" 
                            class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12"
                            placeholder="e.g. 9876543210" pattern="\d{10}" maxlength="10" title="Exactly 10 digits are required">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Permanent Address</label>
                        <textarea name="address" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 p-4">{{ old('address', $student->address) }}</textarea>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 mb-4">Portal Access Status</label>
                        <div id="toggleCard" class="flex items-center justify-between gap-6 p-6 rounded-2xl border transition-all duration-300 {{ old('is_active', $student->is_active) ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100' }}">
                            <div class="flex items-center gap-4">
                                <div id="toggleIcon" class="h-12 w-12 rounded-xl flex items-center justify-center text-xl transition-all duration-300 {{ old('is_active', $student->is_active) ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100' : 'bg-rose-600 text-white shadow-lg shadow-rose-100' }}">
                                    <i id="toggleIconInner" class="bi {{ old('is_active', $student->is_active) ? 'bi-person-check-fill' : 'bi-person-dash-fill' }}"></i>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-800">Account Access Permissions</span>
                                    <span id="toggleLabel" class="block text-[10px] font-bold uppercase tracking-widest mt-0.5 {{ old('is_active', $student->is_active) ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ old('is_active', $student->is_active) ? 'Authorized — Full Portal Access' : 'Inactive — Access Restricted' }}
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

                <div class="pt-8 border-t border-slate-100 flex items-center justify-end gap-4">
                    <button type="submit" id="submitBtn" class="px-12 py-3.5 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-3">
                        <span id="submitLabel">Update Student Record</span>
                        <i id="submitIcon" class="bi bi-person-check-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('editStudentForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitIcon = document.getElementById('submitIcon');
            const submitLabel = document.getElementById('submitLabel');
            const errorBox = document.getElementById('formErrorBox');
            const errorList = document.getElementById('formErrorList');
            const toast = document.getElementById('ajaxToast');
            const toastMsg = document.getElementById('ajaxToastMsg');
            const toastIcon = document.getElementById('ajaxToastIcon');
            let toastTimer = null;

            function showToast(message, type = 'success') {
                clearTimeout(toastTimer);
                toastMsg.textContent = message;
                toast.classList.remove('bg-emerald-500', 'bg-rose-500');
                if (type === 'success') {
                    toast.classList.add('bg-emerald-500');
                    toastIcon.className = 'bi bi-check-circle-fill';
                } else {
                    toast.classList.add('bg-rose-500');
                    toastIcon.className = 'bi bi-exclamation-circle-fill';
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
                form.querySelectorAll('input, select, textarea').forEach(el => {
                    el.classList.remove('border-rose-400', 'bg-rose-50/20');
                });
                form.querySelectorAll('.field-error').forEach(el => {
                    el.textContent = '';
                    el.classList.add('hidden');
                });
            }

            function showErrors(errors) {
                errorList.innerHTML = '';
                Object.entries(errors).forEach(([field, messages]) => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('border-rose-400', 'bg-rose-50/20');
                        const errEl = input.closest('.space-y-2').querySelector('.field-error');
                        if (errEl) {
                            errEl.textContent = messages[0];
                            errEl.classList.remove('hidden');
                        }
                    }
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorList.appendChild(li);
                    });
                });
                errorBox.classList.remove('hidden');
                window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
            }

            function setLoading(loading) {
                submitBtn.disabled = loading;
                submitLabel.textContent = loading ? 'Saving Changes...' : 'Update Student Record';
                submitIcon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-person-check-fill';
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
                        showToast('Validation failed. Please check the fields.', 'error');
                        return;
                    }

                    if (!data.success) throw new Error(data.message || 'Update failed');

                    showToast(data.message || 'Student record updated successfully!', 'success');
                } catch (err) {
                    showToast(err.message || 'A system error occurred.', 'error');
                } finally {
                    setLoading(false);
                }
            });

            // Live toggle card update
            const toggle = document.getElementById('isActiveToggle');
            const card   = document.getElementById('toggleCard');
            const icon   = document.getElementById('toggleIcon');
            const iconEl = document.getElementById('toggleIconInner');
            const label  = document.getElementById('toggleLabel');

            function updateCard(isActive) {
                if (isActive) {
                    card.classList.remove('bg-rose-50/50', 'border-rose-100');
                    card.classList.add('bg-emerald-50/50', 'border-emerald-100');
                    icon.classList.remove('bg-rose-600', 'shadow-rose-100');
                    icon.classList.add('bg-emerald-600', 'shadow-emerald-100');
                    iconEl.className = 'bi bi-person-check-fill';
                    label.classList.remove('text-rose-600');
                    label.classList.add('text-emerald-600');
                    label.textContent = 'Authorized — Full Portal Access';
                } else {
                    card.classList.remove('bg-emerald-50/50', 'border-emerald-100');
                    card.classList.add('bg-rose-50/50', 'border-rose-100');
                    icon.classList.remove('bg-emerald-600', 'shadow-emerald-100');
                    icon.classList.add('bg-rose-600', 'shadow-rose-100');
                    iconEl.className = 'bi bi-person-dash-fill';
                    label.classList.remove('text-emerald-600');
                    label.classList.add('text-rose-600');
                    label.textContent = 'Inactive — Access Restricted';
                }
            }

            if (toggle) {
                toggle.addEventListener('change', () => updateCard(toggle.checked));
            }
        })();
    </script>
@endpush
