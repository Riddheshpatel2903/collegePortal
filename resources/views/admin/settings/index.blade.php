@extends('layouts.app')

@section('header_title', 'Configuration Command Center')

@section('content')
<div class="pb-20" x-data="{ 
    activeTab: 'general',
    saving: false,
    async toggleSetting(url, payload) {
        this.saving = true;
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) {
                window.toast.success(data.message || 'Settings Synchronized');
            } else {
                window.toast.error('Synchronization Failed');
            }
        } catch (e) {
            window.toast.error('Network Communication Error');
        } finally {
            this.saving = false;
        }
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-[288px_1fr] gap-8">
        
        <!-- ─── Sidebar Navigation ─── -->
        <aside class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="bi bi-shield-lock text-4xl text-indigo-500"></i>
                </div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Internal Controls</h2>
                
                <nav class="space-y-1">
                    @foreach([
                        ['id' => 'general', 'label' => 'Identity & Contact', 'icon' => 'bi-info-circle'],
                        ['id' => 'academic', 'label' => 'Academic Logic', 'icon' => 'bi-mortarboard'],
                        ['id' => 'access', 'label' => 'Access (RBAC)', 'icon' => 'bi-safe2'],
                        ['id' => 'modules', 'label' => 'System Modules', 'icon' => 'bi-box-seam'],
                        ['id' => 'security', 'label' => 'Security Logs', 'icon' => 'bi-clock-history']
                    ] as $item)
                        <button @click="activeTab = '{{ $item['id'] }}'" 
                            :class="activeTab === '{{ $item['id'] }}' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-600 hover:bg-slate-50'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all group">
                            <i class="bi {{ $item['icon'] }} text-lg" :class="activeTab === '{{ $item['id'] }}' ? 'text-white' : 'text-slate-400 group-hover:text-indigo-500'"></i>
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                </nav>

                <div class="mt-8 pt-8 border-t border-slate-50">
                    <div class="flex items-center gap-3 px-2">
                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Core Engine Active</span>
                    </div>
                </div>
            </div>

            {{-- Contextual Info Card --}}
            <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 transition-transform group-hover:scale-110">
                    <i class="bi bi-cpu text-8xl"></i>
                </div>
                <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">System Health</h4>
                <p class="text-xs text-slate-300 font-medium leading-relaxed mb-4">Current session matrix is optimized for the {{ $accessService->setting('academic_year', '2023-24') }} cycle.</p>
                <div class="space-y-2">
                    <div class="h-1 w-full bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 w-3/4"></div>
                    </div>
                    <span class="text-[9px] font-bold text-slate-500">Resource Allocation: 75% Optimal</span>
                </div>
            </div>
        </aside>

        <!-- ─── Main Configuration Area ─── -->
        <div class="space-y-8 min-w-0">
            
            {{-- General Identity Section --}}
            <section x-show="activeTab === 'general'" x-transition.opacity.duration.200ms class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm">
                    <div class="flex items-center gap-5 mb-10">
                        <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl border border-indigo-100">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Institution Identity</h3>
                            <p class="text-sm text-slate-400 font-medium">Define the core metadata for your educational portal.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.general.update') }}" method="POST" class="space-y-10">
                        @csrf @method('PUT')
                        <div class="grid md:grid-cols-2 gap-x-12 gap-y-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Institute Branding Name</label>
                                <input type="text" name="college_name" value="{{ $accessService->setting('college_name', 'College Management Portal Institute') }}" 
                                    class="w-full h-14 bg-slate-50 border-slate-200 rounded-2xl px-6 font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Active Academic Term</label>
                                <input type="text" name="academic_year" value="{{ $accessService->setting('academic_year', '2023-2024') }}" 
                                    class="w-full h-14 bg-slate-50 border-slate-200 rounded-2xl px-6 font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Administrative Registry Email</label>
                                <input type="email" name="contact_email" value="{{ $accessService->setting('contact_email', 'admin@example.com') }}" 
                                    class="w-full h-14 bg-slate-50 border-slate-200 rounded-2xl px-6 font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Central Support Hotline</label>
                                <input type="text" name="contact_phone" value="{{ $accessService->setting('contact_phone', '') }}" 
                                    class="w-full h-14 bg-slate-50 border-slate-200 rounded-2xl px-6 font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                        </div>

                        <div class="pt-8 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-shield-check text-emerald-500 text-xl"></i>
                                <span class="text-xs font-bold text-slate-400">Settings changes are logged for auditing purpose.</span>
                            </div>
                            <button type="submit" class="h-14 px-10 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 transition-all active:scale-95">
                                Persist Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Global System Toggles --}}
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-sm group hover:border-indigo-200 transition-all">
                        <div class="flex items-center justify-between mb-6">
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg border border-indigo-100">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" @change="toggleSetting('{{ route('admin.settings.general.update') }}', {enable_notifications: $event.target.checked})" @checked($accessService->setting('enable_notifications', '1') == '1') class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all shadow-inner"></div>
                            </label>
                        </div>
                        <h4 class="text-lg font-black text-slate-800 mb-2">Omni-Channel Alerts</h4>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed">Broadcast critical notifications to all portal users instantly across their dashboards.</p>
                    </div>

                    <div class="bg-rose-50 border border-rose-100 rounded-[2.5rem] p-8 shadow-sm group hover:bg-rose-100 hover:border-rose-200 transition-all">
                        <div class="flex items-center justify-between mb-6">
                            <div class="h-12 w-12 rounded-xl bg-white text-rose-600 flex items-center justify-center text-lg border border-rose-200">
                                <i class="bi bi-cone-striped"></i>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" @change="toggleSetting('{{ route('admin.settings.general.update') }}', {maintenance_mode: $event.target.checked})" @checked($accessService->setting('maintenance_mode', '0') == '1') class="sr-only peer">
                                <div class="w-14 h-7 bg-white/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-rose-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all shadow-inner"></div>
                            </label>
                        </div>
                        <h4 class="text-lg font-black text-rose-900 mb-2">Deployment Isolation</h4>
                        <p class="text-xs text-rose-600/60 font-medium leading-relaxed">Suspend all non-admin access for critical database maintenance or system updates.</p>
                    </div>
                </div>
            </section>

            {{-- Academic Logic Section --}}
            <section x-show="activeTab === 'academic'" x-transition.opacity.duration.200ms class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm">
                    <div class="flex items-center gap-5 mb-10">
                        <div class="h-14 w-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl border border-amber-100">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Scheduling Architecture</h3>
                            <p class="text-sm text-slate-400 font-medium">Fine-tune the algorithms governing timetable slots and faculty availability.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.smart.update') }}" method="POST" class="space-y-12">
                        @csrf @method('PUT')
                        <div class="grid lg:grid-cols-2 gap-16">
                            <div class="space-y-8">
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 relative group overflow-hidden">
                                    <div class="absolute -right-6 -bottom-6 opacity-5 group-hover:rotate-12 transition-transform">
                                        <i class="bi bi-lightning-charge-fill text-9xl"></i>
                                    </div>
                                    <h5 class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-4">Slot Saturation Limit</h5>
                                    <div class="flex items-end gap-4 mb-4">
                                        <input type="number" min="1" max="12" name="teacher_max_lectures_per_day" value="{{ $teacherMaxLecturesPerDay }}" 
                                            class="w-24 h-16 bg-white border-slate-200 rounded-2xl text-center text-2xl font-black text-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                                        <div class="pb-2">
                                            <span class="text-sm font-black text-slate-700 uppercase">Lectures / Day</span>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Maximum faculty engagement</p>
                                        </div>
                                    </div>
                                    <p class="relative z-10 text-xs text-slate-500 font-medium leading-relaxed">This parameter prevents over-scheduling of faculty members during the automated generation cycle.</p>
                                </div>

                                <div class="bg-amber-50 rounded-3xl p-6 border border-amber-100 flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-amber-600 shadow-sm border border-amber-200">
                                        <i class="bi bi-magic text-xl"></i>
                                    </div>
                                    <div>
                                        <h5 class="text-xs font-black text-amber-900 uppercase mb-1">Dynamic Structuring Enabled</h5>
                                        <p class="text-[11px] text-amber-800/70 font-medium leading-relaxed">Timetable slot durations and intervals are now managed by the system's Intelligent Optimizer.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Academic Operational Cycle</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach ($availableDays as $day)
                                        <label class="relative flex items-center justify-center h-14 rounded-2xl border-2 transition-all cursor-pointer group"
                                            :class="{'bg-slate-900 border-slate-900' : {{ in_array($day, $workingDays) ? 'true' : 'false' }}, 'bg-white border-slate-100 hover:border-slate-200' : !{{ in_array($day, $workingDays) ? 'true' : 'false' }}}">
                                            <input type="checkbox" name="working_days[]" value="{{ $day }}" class="hidden" @checked(in_array($day, $workingDays))>
                                            <span class="text-xs font-black transition-colors uppercase tracking-widest"
                                                :class="{'text-white' : {{ in_array($day, $workingDays) ? 'true' : 'false' }}, 'text-slate-400 group-hover:text-slate-600' : !{{ in_array($day, $workingDays) ? 'true' : 'false' }}}">
                                                {{ substr($day, 0, 3) }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-400 font-medium mt-4 italic">Select the days during which the institution remains operationally open for sessions.</p>
                            </div>
                        </div>

                        <div class="pt-10 border-t border-slate-50 flex justify-end">
                            <button type="submit" class="h-14 px-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 transition-all active:scale-95">
                                Update Academic Logic
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- Access Control (RBAC) Section --}}
            <section x-show="activeTab === 'access'" x-transition.opacity.duration.200ms class="space-y-8" x-data="{ rbacSearch: '', currentRole: '{{ $roles->first()->id ?? '' }}' }">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-10 opacity-5">
                        <i class="bi bi-fingerprint text-[120px]"></i>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-12 relative z-10">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Security & RBAC Matrix</h3>
                            <p class="text-sm text-slate-400 font-medium mt-1">Regulate system visibility and operational permissions across roles.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                                <input type="text" x-model="rbacSearch" placeholder="Search routes..." 
                                    class="h-12 w-64 bg-slate-50 border-slate-100 rounded-xl pl-12 pr-6 text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                            <div class="bg-slate-900 rounded-xl p-1.5 flex gap-1">
                                @foreach ($roles as $role)
                                    <button @click="currentRole = '{{ $role->id }}'"
                                        :class="currentRole === '{{ $role->id }}' ? 'bg-white text-indigo-600' : 'text-slate-400 hover:text-white'"
                                        class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">
                                        {{ $role->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @foreach ($roles as $role)
                            <div x-show="currentRole === '{{ $role->id }}'" x-transition>
                                <form action="{{ route('admin.settings.pages.update') }}" method="POST">
                                    @csrf @method('PUT')
                                    
                                    @php
                                        $roleName = strtolower($role->name);
                                        $prefix = [
                                            'super_admin' => '*', 'admin' => 'admin', 'hod' => 'hod',
                                            'teacher' => 'teacher', 'student' => 'student',
                                            'accountant' => 'accountant', 'librarian' => 'librarian'
                                        ][$roleName] ?? null;
                                        $rolePages = collect($pages)->filter(function($p) use ($roleName, $prefix) {
                                            if ($roleName === 'super_admin') return true;
                                            if ($p->route === 'profile.edit' || $p->route === 'dashboard') return true;
                                            return $prefix && str_starts_with($p->route, $prefix . '.');
                                        });
                                    @endphp

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        @forelse($rolePages as $page)
                                            @php
                                                $perm = $page->rolePermissions->where('role_id', $role->id)->first();
                                                $canView = (bool)($perm->can_view ?? true);
                                            @endphp
                                            <div data-search="{{ strtolower($page->name . ' ' . $page->route) }}"
                                                x-show="rbacSearch === '' || $el.dataset.search.includes(rbacSearch.toLowerCase())"
                                                class="bg-slate-50 border border-slate-100 rounded-3xl p-6 transition-all hover:border-indigo-200 group">
                                                <div class="flex items-center justify-between mb-4">
                                                    <div class="min-w-0">
                                                        <h5 class="text-sm font-black text-slate-800 truncate">{{ $page->name }}</h5>
                                                        <p class="text-[9px] font-mono text-slate-400 uppercase tracking-tighter mt-1">{{ $page->route }}</p>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="hidden" name="permissions[{{ $page->id }}][{{ $role->id }}][can_view]" value="0">
                                                        <input type="checkbox" name="permissions[{{ $page->id }}][{{ $role->id }}][can_view]" value="1" class="sr-only peer" @checked($canView)>
                                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                                                    </label>
                                                </div>
                                                <div class="flex flex-wrap gap-2 pt-4 border-t border-white">
                                                    @foreach(['can_create' => 'Add', 'can_edit' => 'Edit', 'can_delete' => 'Delete', 'can_export' => 'Export'] as $key => $label)
                                                        <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-white rounded-xl border border-slate-100 text-[9px] font-black uppercase tracking-widest cursor-pointer hover:bg-slate-50 transition-colors">
                                                            <input type="hidden" name="permissions[{{ $page->id }}][{{ $role->id }}][{{ $key }}]" value="0">
                                                            <input type="checkbox" name="permissions[{{ $page->id }}][{{ $role->id }}][{{ $key }}]" value="1" 
                                                                class="h-3 w-3 rounded border-slate-300 text-indigo-600 focus:ring-0" 
                                                                @checked((bool)($perm->$key ?? false))>
                                                            <span class="text-slate-500">{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-span-full py-20 text-center opacity-30">
                                                <i class="bi bi-shield-slash text-4xl block mb-2"></i>
                                                <p class="text-xs font-black uppercase tracking-widest">No routes found for {{ $role->name }} context</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    @if($rolePages->isNotEmpty())
                                        <div class="mt-8 flex justify-end">
                                            <button type="submit" class="h-12 px-8 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black shadow-lg shadow-slate-100 transition-all">
                                                Commit Matrix for {{ $role->name }}
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Modules & Features Section --}}
            <section x-show="activeTab === 'modules'" x-transition.opacity.duration.200ms class="space-y-8">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    
                    {{-- Subsystem Operational Toggles --}}
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm">
                        <div class="flex items-center justify-between mb-10">
                            <div>
                                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Core Subsystems</h3>
                                <p class="text-sm text-slate-400 font-medium">Toggle high-level functional architecture.</p>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ($modules as $module)
                                <div class="flex items-center justify-between p-6 rounded-3xl border border-slate-50 bg-slate-50/20 hover:bg-white hover:border-indigo-100 transition-all group">
                                    <div class="flex items-center gap-6">
                                        @php
                                            $icon = [
                                                'timetable' => 'bi-calendar3', 'fees' => 'bi-wallet2', 
                                                'notice' => 'bi-megaphone', 'leave' => 'bi-envelope-paper',
                                                'attendance' => 'bi-person-check', 'exam' => 'bi-journal-check'
                                            ][$module->module_key] ?? 'bi-layers';
                                        @endphp
                                        <div class="h-12 w-12 rounded-2xl bg-white text-slate-400 flex items-center justify-center text-xl border border-slate-100 group-hover:text-indigo-600 group-hover:scale-110 group-hover:shadow-lg transition-all">
                                            <i class="bi {{ $icon }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-base font-black text-slate-700 capitalize block truncate">{{ str_replace('_', ' ', $module->module_key) }} Engine</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Active Application Layer</span>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                            data-url="{{ route('admin.settings.modules.update') }}"
                                            data-key="modules[{{ $module->module_key }}]"
                                            @change="toggleSetting($el.dataset.url, { [$el.dataset.key]: $event.target.checked })"
                                            @checked($module->enabled) class="sr-only peer">
                                        <div class="w-14 h-7 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all shadow-inner"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Granular Functional Highlights --}}
                    <div class="bg-indigo-900 rounded-[2.5rem] p-10 shadow-xl relative overflow-hidden">
                        <div class="absolute -right-20 -bottom-20 opacity-5 rotate-12">
                            <i class="bi bi-lightning-charge text-[300px] text-white"></i>
                        </div>

                        <div class="flex items-center justify-between mb-10 relative z-10">
                            <div>
                                <h3 class="text-2xl font-black text-white tracking-tight">Granular Highlights</h3>
                                <p class="text-sm text-indigo-300 font-medium">Toggle micro-functionality across modules.</p>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-white/10 text-white flex items-center justify-center text-xl border border-white/20">
                                <i class="bi bi-stars"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
                            @foreach ($features as $feature)
                                <div class="flex items-center justify-between p-5 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/20 hover:border-white/30 transition-all group">
                                    <span class="text-xs font-black text-white capitalize pr-4 truncate">{{ str_replace('_', ' ', $feature->feature_key) }}</span>
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox"
                                            data-url="{{ route('admin.settings.features.update') }}"
                                            data-key="features[{{ $feature->feature_key }}]"
                                            @change="toggleSetting($el.dataset.url, { [$el.dataset.key]: $event.target.checked })"
                                            @checked($feature->enabled) class="sr-only peer">
                                        <div class="w-10 h-5 bg-white/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-amber-400 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all shadow-inner"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </section>

            {{-- Security Logs Section --}}
            <section x-show="activeTab === 'security'" x-transition.opacity.duration.200ms class="space-y-8">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden">
                    <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Security Audit Trail</h3>
                            <p class="text-sm text-slate-400 font-medium mt-1">Live monitoring of system events and administrative access logs.</p>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl border border-rose-100">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="px-10 py-6">Event Timestamp</th>
                                    <th class="px-10 py-6">Administrative User</th>
                                    <th class="px-10 py-6">Contextual Operation</th>
                                    <th class="px-10 py-6 text-center">Status</th>
                                    <th class="px-10 py-6">Network Identifier</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($auditLogs as $log)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-10 py-6">
                                            <span class="text-xs font-black text-slate-700 block leading-none">{{ $log->created_at->format('M d, H:i') }}</span>
                                            <span class="text-[9px] text-slate-400 uppercase font-black tracking-tighter mt-1 block">{{ $log->created_at->diffForHumans() }}</span>
                                        </td>
                                        <td class="px-10 py-6">
                                            @if($log->user)
                                                <div class="flex items-center gap-4">
                                                    <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black border border-slate-800 shadow-md">
                                                        {{ substr($log->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <span class="text-sm font-bold text-slate-700 block truncate max-w-[150px]">{{ $log->user->name }}</span>
                                                        <span class="text-[10px] text-slate-400 font-mono tracking-tighter uppercase">{{ $log->user->role }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-3 text-slate-300 italic">
                                                    <i class="bi bi-person-x"></i>
                                                    <span class="text-xs font-bold">Unidentified Entity</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-10 py-6">
                                            <div class="flex items-center gap-3 mb-1.5">
                                                <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest border shadow-xs 
                                                    {{ $log->method === 'GET' ? 'bg-sky-50 text-sky-600 border-sky-100' : 
                                                       ($log->method === 'POST' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                                       'bg-amber-50 text-amber-600 border-amber-100') }}">
                                                    {{ $log->method }}
                                                </span>
                                                <span class="text-xs font-black text-slate-800 truncate max-w-[200px]" title="{{ $log->action }}">{{ $log->action }}</span>
                                            </div>
                                            <p class="text-[10px] text-slate-400 font-mono tracking-tighter truncate max-w-[300px]">{{ $log->path }}</p>
                                        </td>
                                        <td class="px-10 py-6 text-center">
                                            @if($log->status_code)
                                                <span class="px-3 py-1 rounded-full text-[10px] font-black {{ $log->status_code >= 400 ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                                    {{ $log->status_code }}
                                                </span>
                                            @else
                                                <span class="text-slate-200">/ / /</span>
                                            @endif
                                        </td>
                                        <td class="px-10 py-6">
                                            <span class="text-xs font-mono text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100">{{ $log->ip_address }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-10 py-32 text-center">
                                            <div class="max-w-xs mx-auto opacity-30">
                                                <i class="bi bi-hdd-network text-6xl block mb-4"></i>
                                                <p class="text-sm font-black uppercase tracking-widest">No Security Events Intercepted</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($auditLogs->hasPages())
                        <div class="p-10 border-t border-slate-50 bg-slate-50/50">
                            {{ $auditLogs->links() }}
                        </div>
                    @endif
                </div>
            </section>

        </div>
    </div>

    {{-- Global Saving Indicator Overlay --}}
    <div x-show="saving" x-cloak 
        class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-3xl p-8 flex flex-col items-center gap-4 shadow-2xl border border-slate-100">
            <div class="relative">
                <div class="w-16 h-16 rounded-full border-4 border-slate-100 border-t-indigo-600 animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="bi bi-cloud-arrow-up text-indigo-600 text-xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h4 class="text-base font-black text-slate-800">Synchronizing...</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Committing changes to secure core</p>
            </div>
        </div>
    </div>

</div>


@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    
    /* Custom Scrollbar for Main Content */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    /* Animation Refinements */
    @keyframes pulse-soft {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .animate-pulse-soft {
        animation: pulse-soft 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endpush
