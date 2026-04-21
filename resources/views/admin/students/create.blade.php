@extends('layouts.app')

@section('header_title', 'Enroll Student')

@section('content')
    {{-- Toast Notification --}}
    <div id="ajaxToast" class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl text-xs font-bold text-white translate-y-20 opacity-0 transition-all duration-500 pointer-events-none">
        <i id="ajaxToastIcon" class="bi text-lg"></i>
        <span id="ajaxToastMsg"></span>
    </div>

    <x-page-header 
        title="Student Registration" 
        subtitle="Enroll a new student into the academic system with personal and departmental details."
        icon="bi-person-plus"
        back="{{ route('admin.students.index') }}"
    />

    <div class="max-w-4xl mx-auto mt-8">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Student Profile Configuration</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Academic & Personal Parameters</p>
            </div>

            <form id="createStudentForm" action="{{ route('admin.students.store') }}" method="POST" class="p-8 space-y-8">
                @csrf
                
                {{-- Global Error List --}}
                <div id="formErrorBox" class="hidden p-4 bg-rose-50 border border-rose-100 rounded-xl">
                    <ul id="formErrorList" class="text-[10px] font-bold text-rose-600 uppercase tracking-widest space-y-1"></ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Full Legal Name</label>
                        <input type="text" name="name" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" placeholder="e.g. Rahul Sharma" required pattern="[a-zA-Z\s.]+" title="Only characters, spaces and dots are allowed">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Official Email Address</label>
                        <input type="email" name="email" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" placeholder="rahul.s@college.edu" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Internal Roll Number</label>
                        <input type="text" name="roll_number" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" placeholder="e.g. CS2024042" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">GTU Enrollment No</label>
                        <input type="text" name="gtu_enrollment_no" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" placeholder="1801201100XX" required>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Academic Program (Course)</label>
                        <select name="course_id" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12 text-slate-600" required>
                            <option value="">Select Branch...</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Current Academic Year</label>
                        <input type="number" name="current_year" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12" min="1" max="5" value="1">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Admission Batch (Year)</label>
                        <input type="number" name="admission_year" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold focus:border-indigo-500 focus:ring-0 h-12" placeholder="{{ date('Y') }}" value="{{ date('Y') }}">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Contact Number</label>
                        <input type="tel" name="phone" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12" placeholder="e.g. 9876543210" pattern="\d{10}" maxlength="10" title="Exactly 10 digits are required">
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Account Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="passwordField" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 h-12 pr-12" placeholder="Create a secure password" required>
                            <button type="button" onclick="const p = document.getElementById('passwordField'); p.type = p.type === 'password' ? 'text' : 'password';" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Permanent Residence (Address)</label>
                        <textarea name="address" rows="3" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-medium focus:border-indigo-500 focus:ring-0 p-4" placeholder="Street, Building, City, Pincode..."></textarea>
                        <p class="field-error text-[10px] font-bold text-rose-500 mt-1 hidden"></p>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 flex items-center justify-between gap-6">
                    <div class="max-w-xs">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
                            Upon submission, an academic record will be initialized and the student will be granted portal access.
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="reset" class="px-6 py-3 bg-white border border-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all">Reset Form</button>
                        <button type="submit" id="submitBtn" class="px-10 py-3 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                            <span id="submitLabel">Register Student</span>
                            <i id="submitIcon" class="bi bi-person-plus"></i>
                        </button>
                    </div>
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
                submitLabel.textContent = loading ? 'Processing...' : 'Register Student';
                submitIcon.className = loading ? 'bi bi-arrow-repeat animate-spin' : 'bi bi-person-plus';
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

                    if (!data.success) throw new Error(data.message || 'Registration failed');

                    showToast(data.message || 'Student enrolled successfully!', 'success');
                    form.reset();
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 1200);
                    }
                } catch (err) {
                    showToast(err.message || 'A system error occurred.', 'error');
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
@endpush