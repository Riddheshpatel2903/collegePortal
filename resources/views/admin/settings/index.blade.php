@extends('layouts.app')

@section('header_title', 'System Settings & Access Control')

@section('content')
    <!-- Global Layout Shell -->
    <div class="max-w-7xl mx-auto space-y-8 pb-12" x-data="{ activeTab: 'rbac' }">

        <!-- Header & Navigation Tabs -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Access Control & Settings</h2>
                <p class="text-slate-500 mt-1">Enterprise Role-Based Access Control and Core Configuration.</p>
            </div>
            <div
                class="flex space-x-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200 overflow-x-auto hide-scrollbar">
                <button type="button" @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' :
                        'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    General
                </button>
                <button type="button" @click="activeTab = 'rbac'"
                    :class="activeTab === 'rbac' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' :
                        'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Access Control
                </button>
                <button type="button" @click="activeTab = 'roles'"
                    :class="activeTab === 'roles' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' :
                        'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Roles
                </button>
                <button type="button" @click="activeTab = 'modules'"
                    :class="activeTab === 'modules' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' :
                        'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Modules & Config
                </button>
                <button type="button" @click="activeTab = 'logs'"
                    :class="activeTab === 'logs' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' :
                        'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Activity Logs
                </button>
            </div>
        </div>

        <!-- Tab Contents Container -->
        <div class="relative">

            <!-- TAB 3: Modules & Features (Existing Content) -->
            <div x-show="activeTab === 'modules'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;" class="space-y-8">


                <!-- Section: Core Modules -->
                <section class="glass-card overflow-hidden">
                    <form method="POST" action="{{ route('admin.settings.modules.update') }}" class="p-6 md:p-8 space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-widest text-violet-600 mb-1 block">Section
                                    1</span>
                                <h3 class="text-lg font-bold text-slate-800">Core Modules</h3>
                                <p class="text-sm text-slate-500 mt-1">Enable or disable major sections of the portal.
                                    Disabling a module hides it globally.</p>
                            </div>
                            <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Modules</button>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            @foreach ($modules as $module)
                                <input type="hidden" name="modules[{{ $module->module_key }}]" value="0">
                                <label
                                    class="flex items-start justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <div class="pr-4">
                                        <span
                                            class="text-sm font-bold text-slate-800 block mb-0.5 group-hover:text-violet-700 transition-colors">{{ str_replace('_', ' ', Str::title($module->module_key)) }}</span>
                                        <span class="text-xs text-slate-500">Toggle this core module on or off for the
                                            entire system.</span>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                                        <input type="checkbox" name="modules[{{ $module->module_key }}]" value="1"
                                            class="sr-only peer" @checked($module->enabled)>
                                        <div
                                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600">
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </section>

                <!-- Section: Feature Toggles -->
                <section class="glass-card overflow-hidden">
                    <form method="POST" action="{{ route('admin.settings.features.update') }}"
                        class="p-6 md:p-8 space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-widest text-violet-600 mb-1 block">Section
                                    2</span>
                                <h3 class="text-lg font-bold text-slate-800">Feature Toggles</h3>
                                <p class="text-sm text-slate-500 mt-1">Activate or deactivate specific functionalities
                                    across the platform.</p>
                            </div>
                            <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Features</button>
                        </div>

                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($features as $feature)
                                <input type="hidden" name="features[{{ $feature->feature_key }}]" value="0">
                                <label
                                    class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                                    <span
                                        class="text-sm font-semibold text-slate-700 group-hover:text-violet-700 transition-colors truncate pr-3">{{ str_replace('_', ' ', Str::title($feature->feature_key)) }}</span>
                                    <div class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" name="features[{{ $feature->feature_key }}]"
                                            value="1" class="sr-only peer" @checked($feature->enabled)>
                                        <div
                                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-violet-600">
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </form>
                </section>
            </div>

            <!-- TAB: RBAC -->
            <div x-show="activeTab === 'rbac'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;" class="space-y-8">

                <!-- Section: Role & Page Management -->
                <section class="glass-card overflow-hidden" x-data="{
                    selectedRole: '{{ $roles->first()->id ?? '' }}',
                    searchQuery: '',
                
                    get pre() {
                        return {
                            'super_admin': '*',
                            'admin': 'admin',
                            'hod': 'hod',
                            'teacher': 'teacher',
                            'student': 'student',
                            'accountant': 'accountant',
                            'librarian': 'librarian'
                        };
                    },
                
                    normalizedRole(roleName) {
                        return String(roleName || '').toLowerCase().replace(/[\s-]+/g, '_');
                    },
                
                    isPageRelevant(roleName, pageRoute) {
                        const normalized = this.normalizedRole(roleName);
                        if (normalized === 'super_admin') return true;
                        if (pageRoute === 'profile.edit') return true;
                
                        const prefix = this.pre[normalized];
                        if (!prefix) return false;
                        return pageRoute.startsWith(prefix + '.');
                    },
                
                    panelEl() {
                        return document.querySelector(`[data-role-panel='${this.selectedRole}']`);
                    },
                
                    toggleAll(actionKey, value = true) {
                        const panel = this.panelEl();
                        if (!panel) return;
                        panel.querySelectorAll(`input[type='checkbox'][data-perm='${actionKey}']`)
                            .forEach(el => { el.checked = value; });
                    },
                    selectAllActions(value = true) {
                        const panel = this.panelEl();
                        if (!panel) return;
                
                        ['can_create', 'can_edit', 'can_delete', 'can_export'].forEach(action => {
                            panel.querySelectorAll(`input[type='checkbox'][data-perm='${action}']`)
                                .forEach(el => { el.checked = value; });
                        });
                    },
                
                    allowAllPages() {
                        this.toggleAll('can_view', true);
                    }
                }">
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="border-b border-slate-100 pb-5">
                            <span class="text-xs font-bold uppercase tracking-widest text-violet-600 mb-1 block">Section
                                3</span>
                            <h3 class="text-lg font-bold text-slate-800">Role & Page Management</h3>
                            <p class="text-sm text-slate-500 mt-1">Select a role to configure their specific capabilities
                                and manage which pages they can view.</p>
                        </div>

                        <!-- Step 1: Role Selection & Search -->
                        <div class="grid md:grid-cols-2 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Step 1:
                                    Select Role</label>
                                <select x-model="selectedRole"
                                    class="w-full rounded-xl border-slate-200 bg-white focus:border-violet-500 focus:ring-violet-500 text-sm shadow-sm transition-colors cursor-pointer py-2.5 font-medium text-slate-700">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Search
                                    Pages</label>
                                <div class="relative">
                                    <input type="text" x-model="searchQuery" placeholder="Search by name or route..."
                                        class="w-full rounded-xl border-slate-200 bg-white focus:border-violet-500 focus:ring-violet-500 text-sm shadow-sm transition-colors placeholder:text-slate-400 py-2.5 pl-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Panels -->
                        <div class="mt-8">
                            @foreach ($roles as $role)
                                <div data-role-panel="{{ $role->id }}"
                                    x-show="selectedRole == '{{ $role->id }}'" style="display: none;"
                                    x-init="if (selectedRole == '{{ $role->id }}') $el.style.display = 'block';
                                    else $el.style.display = 'none';
                                    $watch('selectedRole', val => { $el.style.display = (val == '{{ $role->id }}') ? 'block' : 'none'; })">

                                    <!-- Page Visibility Form -->
                                    <form method="POST" action="{{ route('admin.settings.pages.update') }}"
                                        class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <div
                                            class="flex flex-col gap-4 pb-3 border-b border-slate-50 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h4 class="text-base font-bold text-slate-800 capitalize">
                                                    {{ $role->name }} Page Access</h4>
                                                <p class="text-xs text-slate-500 mt-1">Control which pages are visible and
                                                    what actions this role can take.</p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button"
                                                    class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border border-violet-200 text-violet-700 hover:bg-violet-50"
                                                    @click="selectAllActions(true)">
                                                    Select All Actions
                                                </button>
                                                <button type="button"
                                                    class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50"
                                                    @click="allowAllPages()">
                                                    Allow All Pages
                                                </button>
                                                <button
                                                    class="btn-primary-gradient px-4 py-2 text-sm whitespace-nowrap hidden sm:block">Save
                                                    Page Access</button>
                                            </div>
                                        </div>

                                        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @php
                                                $roleKey = str_replace(
                                                    [' ', '-'],
                                                    '_',
                                                    strtolower((string) $role->name),
                                                );
                                                $rolePrefixes = [
                                                    'super_admin' => '*',
                                                    'admin' => 'admin',
                                                    'hod' => 'hod',
                                                    'teacher' => 'teacher',
                                                    'student' => 'student',
                                                    'accountant' => 'accountant',
                                                    'librarian' => 'librarian',
                                                ];
                                                $prefix = $rolePrefixes[$roleKey] ?? null;
                                                // filter pages on server-side to prevent CSS grid gaps and bloated DOM
                                                $relevantPages = collect($pages)->filter(function ($p) use (
                                                    $role,
                                                    $prefix,
                                                ) {
                                                    if (
                                                        strtolower((string) $role->name) === 'super_admin' ||
                                                        str_replace(
                                                            [' ', '-'],
                                                            '_',
                                                            strtolower((string) $role->name),
                                                        ) === 'super_admin'
                                                    ) {
                                                        return true;
                                                    }
                                                    if ($p->route === 'profile.edit') {
                                                        return true;
                                                    }
                                                    if (!$prefix) {
                                                        return false;
                                                    }
                                                    return str_starts_with($p->route, $prefix . '.');
                                                });
                                            @endphp

                                            @foreach ($relevantPages as $page)
                                                @php
                                                    $permissionMap = $page->rolePermissions->keyBy('role_id');
                                                    $currentPermission = $permissionMap[$role->id] ?? null;
                                                    $canView = (bool) ($currentPermission->can_view ?? true);
                                                    $canCreate = (bool) ($currentPermission->can_create ?? false);
                                                    $canEdit = (bool) ($currentPermission->can_edit ?? false);
                                                    $canDelete = (bool) ($currentPermission->can_delete ?? false);
                                                    $canExport = (bool) ($currentPermission->can_export ?? false);
                                                    // Make the route display cleaner by removing the prefix
                                                    $cleanRoute = ltrim(
                                                        str_replace($prefix ?? '', '', $page->route),
                                                        '.',
                                                    );
                                                    $cleanRoute = $cleanRoute ?: $page->route;
                                                @endphp

                                                <div
                                                    x-show="searchQuery === '' || '{{ strtolower($page->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($cleanRoute) }}'.includes(searchQuery.toLowerCase())">
                                                    <input type="hidden"
                                                        name="permissions[{{ $page->id }}][{{ $role->id }}][can_view]"
                                                        value="0">
                                                    <input type="hidden"
                                                        name="permissions[{{ $page->id }}][{{ $role->id }}][can_create]"
                                                        value="0">
                                                    <input type="hidden"
                                                        name="permissions[{{ $page->id }}][{{ $role->id }}][can_edit]"
                                                        value="0">
                                                    <input type="hidden"
                                                        name="permissions[{{ $page->id }}][{{ $role->id }}][can_delete]"
                                                        value="0">
                                                    <input type="hidden"
                                                        name="permissions[{{ $page->id }}][{{ $role->id }}][can_export]"
                                                        value="0">

                                                    <div
                                                        class="px-4 py-3.5 rounded-xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 hover:border-violet-200 transition-all group h-full shadow-sm">
                                                        <div class="flex items-center justify-between">
                                                            <div class="truncate pr-4 leading-tight">
                                                                <span
                                                                    class="block text-sm font-semibold text-slate-700 group-hover:text-violet-700">{{ $page->name }}</span>
                                                                <span
                                                                    class="block text-[10px] text-slate-400 font-mono mt-0.5 truncate uppercase tracking-wider text-ellipsis overflow-hidden">{{ $cleanRoute }}</span>
                                                            </div>

                                                            <label
                                                                class="relative inline-flex items-center shrink-0 cursor-pointer"
                                                                title="Toggle page visibility for this role">
                                                                <input type="checkbox"
                                                                    name="permissions[{{ $page->id }}][{{ $role->id }}][can_view]"
                                                                    value="1" class="sr-only peer"
                                                                    data-perm="can_view" @checked($canView)>
                                                                <div
                                                                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600">
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <div
                                                            class="mt-3 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                                            <label
                                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border border-slate-200 bg-white/60 hover:border-emerald-200 hover:text-emerald-600 transition-colors cursor-pointer">
                                                                <input type="checkbox"
                                                                    name="permissions[{{ $page->id }}][{{ $role->id }}][can_create]"
                                                                    value="1"
                                                                    class="h-3.5 w-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                                    data-perm="can_create" @checked($canCreate)>
                                                                Create
                                                            </label>
                                                            <label
                                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border border-slate-200 bg-white/60 hover:border-amber-200 hover:text-amber-600 transition-colors cursor-pointer">
                                                                <input type="checkbox"
                                                                    name="permissions[{{ $page->id }}][{{ $role->id }}][can_edit]"
                                                                    value="1"
                                                                    class="h-3.5 w-3.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                                                    data-perm="can_edit" @checked($canEdit)>
                                                                Edit
                                                            </label>
                                                            <label
                                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border border-slate-200 bg-white/60 hover:border-rose-200 hover:text-rose-600 transition-colors cursor-pointer">
                                                                <input type="checkbox"
                                                                    name="permissions[{{ $page->id }}][{{ $role->id }}][can_delete]"
                                                                    value="1"
                                                                    class="h-3.5 w-3.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                                                                    data-perm="can_delete" @checked($canDelete)>
                                                                Delete
                                                            </label>
                                                            <label
                                                                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border border-slate-200 bg-white/60 hover:border-sky-200 hover:text-sky-600 transition-colors cursor-pointer">
                                                                <input type="checkbox"
                                                                    name="permissions[{{ $page->id }}][{{ $role->id }}][can_export]"
                                                                    value="1"
                                                                    class="h-3.5 w-3.5 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                                                    data-perm="can_export" @checked($canExport)>
                                                                Export
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="btn-primary-gradient w-full py-2.5 text-sm sm:hidden mt-4">Save Page
                                            Access</button>
                                    </form>

                                </div>
                            @endforeach
                        </div>

                        <div x-show="searchQuery !== ''"
                            class="mt-4 p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center"
                            style="display: none;">
                            <p class="text-sm text-slate-500">Showing search results for "<span
                                    class="font-semibold text-slate-700" x-text="searchQuery"></span>"</p>
                        </div>

                    </div>
            </div>

            <!-- TAB: General Settings -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;" class="space-y-8">

                <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- General Info -->
                    <section class="glass-card overflow-hidden p-6 md:p-8">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">General Information</h3>
                                <p class="text-sm text-slate-500 mt-1">Basic details about the institution.</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">College Name</label>
                                <input type="text" name="college_name"
                                    value="{{ $portalAccess->setting('college_name', 'EduPortal Institute') }}"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Contact Email</label>
                                <input type="email" name="contact_email"
                                    value="{{ $portalAccess->setting('contact_email', 'admin@example.com') }}"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Contact Phone</label>
                                <input type="text" name="contact_phone"
                                    value="{{ $portalAccess->setting('contact_phone', '') }}"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Academic Year</label>
                                <input type="text" name="academic_year"
                                    value="{{ $portalAccess->setting('academic_year', '2023-2024') }}"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500">
                            </div>
                        </div>
                    </section>

                    <!-- System Config -->
                    <section class="glass-card overflow-hidden p-6 md:p-8">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">System Configuration</h3>
                                <p class="text-sm text-slate-500 mt-1">Controls for notifications and maintenance mode.</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <input type="hidden" name="enable_notifications" value="0">
                            <label
                                class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50 cursor-pointer">
                                <div>
                                    <span class="font-bold text-slate-800 text-sm">Enable Notifications</span>
                                    <p class="text-xs text-slate-500">Global toggle for in-app notifications.</p>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="enable_notifications" value="1"
                                        class="sr-only peer" @checked($portalAccess->setting('enable_notifications', '1') == '1')>
                                    <div
                                        class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-violet-600 after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </div>
                            </label>

                            <input type="hidden" name="maintenance_mode" value="0">
                            <label
                                class="flex items-center justify-between p-4 rounded-xl border border-rose-100 bg-rose-50/30 cursor-pointer">
                                <div>
                                    <span class="font-bold text-rose-800 text-sm">Maintenance Mode</span>
                                    <p class="text-xs text-rose-500">Only Super Admins can log in while active.</p>
                                </div>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer"
                                        @checked($portalAccess->setting('maintenance_mode', '0') == '1')>
                                    <div
                                        class="w-11 h-6 bg-rose-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-rose-600 after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary-gradient px-8 py-3 font-semibold text-sm">Save General
                            Settings</button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: Role Management -->
            <div x-show="activeTab === 'roles'" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;" class="space-y-8">
                <div class="glass-card mb-8" x-data="{
                    showCreateModal: false,
                    showEditModal: false,
                    showDeleteModal: false,
                    currentRole: { id: '', name: '' },
                    actionUrl: '',
                
                    openEdit(role) {
                        this.currentRole = role;
                        this.actionUrl = '{{ url('admin/settings/roles') }}/' + role.id;
                        this.showEditModal = true;
                    },
                    openDelete(role) {
                        this.currentRole = role;
                        this.actionUrl = '{{ url('admin/settings/roles') }}/' + role.id;
                        this.showDeleteModal = true;
                    }
                }">
                    <div class="p-6 md:p-8 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 mb-1">Role Management</h3>
                            <p class="text-sm text-slate-500">Create, edit, or remove roles. System roles cannot be
                                deleted.</p>
                        </div>
                        <button type="button"
                            class="btn-primary-gradient px-4 py-2 text-sm whitespace-nowrap flex items-center gap-2"
                            @click="showCreateModal = true">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Create New Role
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/50 border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                    <th class="p-4 pl-6 md:pl-8">Role Name</th>
                                    <th class="p-4">Assigned Users</th>
                                    <th class="p-4">Pages Accessible</th>
                                    <th class="p-4 pr-6 md:pr-8 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($roles as $role)
                                    <tr class="hover:bg-slate-50/30 transition-colors">
                                        <td class="p-4 pl-6 md:pl-8">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                                    {{ substr($role->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800 capitalize">
                                                        {{ $role->name }}</p>
                                                    @if (in_array($role->name, ['admin', 'super_admin', 'student', 'teacher']))
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 mt-1">System
                                                            Role</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 text-sm text-slate-600 font-medium">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-50 text-sky-700 border border-sky-100/50 shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                {{ App\Models\User::where('role', $role->name)->count() }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-sm text-slate-600">
                                            @php
                                                $accessCount = $role->pagePermissions->where('can_view', true)->count();
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="w-full bg-slate-100 rounded-full h-1.5 max-w-[100px]">
                                                    <div class="bg-violet-500 h-1.5 rounded-full"
                                                        style="width: {{ count($pages) > 0 ? ($accessCount / count($pages)) * 100 : 0 }}%">
                                                    </div>
                                                </div>
                                                <span class="text-xs font-semibold">{{ $accessCount }} /
                                                    {{ count($pages) }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 pr-6 md:pr-8 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                    class="p-2 rounded-lg transition-colors tooltip text-slate-400 hover:text-violet-600 hover:bg-violet-50"
                                                    data-tip="Edit Name"
                                                    @click="openEdit({ id: {{ $role->id }}, name: '{{ addslashes($role->name) }}' })">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </button>
                                                <button type="button"
                                                    class="p-2 rounded-lg transition-colors tooltip text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                                                    data-tip="Delete Role"
                                                    @click="openDelete({ id: {{ $role->id }}, name: '{{ addslashes($role->name) }}' })">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- Create Role Modal -->
                    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                                aria-hidden="true" @click="showCreateModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                                <form action="{{ route('admin.settings.roles.store') }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-violet-100 sm:mx-0 sm:h-10 sm:w-10 text-violet-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-bold text-slate-800" id="modal-title">
                                                    Create New Role</h3>
                                                <div class="mt-4 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-bold text-slate-700 mb-1">Role
                                                            Identifier Name <span class="text-rose-500">*</span></label>
                                                        <input type="text" name="name" required
                                                            placeholder="e.g. librarian, editor"
                                                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 text-sm py-2 px-3 shadow-sm transition-colors text-slate-800 lowercase">
                                                        <p class="text-xs text-slate-500 mt-1">Must be unique, lowercase,
                                                            no spaces (use underscores).</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-100">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-violet-600 text-base font-medium text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Create
                                            Role</button>
                                        <button type="button" @click="showCreateModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Role Modal -->
                    <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="edit-role-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showEditModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                                aria-hidden="true" @click="showEditModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div x-show="showEditModal" x-transition:enter="ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                                <form :action="actionUrl" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-violet-100 sm:mx-0 sm:h-10 sm:w-10 text-violet-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 6h16M4 10h16M4 14h10" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-bold text-slate-800"
                                                    id="edit-role-title">Edit Role</h3>
                                                <div class="mt-4 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-bold text-slate-700 mb-1">Role
                                                            Identifier Name</label>
                                                        <input type="text" name="name" x-model="currentRole.name"
                                                            required
                                                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 text-sm py-2 px-3 shadow-sm transition-colors text-slate-800 lowercase">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-100">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-violet-600 text-base font-medium text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Save
                                            Changes</button>
                                        <button type="button" @click="showEditModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Role Modal -->
                    <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
                        aria-labelledby="delete-role-title" role="dialog" aria-modal="true">
                        <div
                            class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
                                aria-hidden="true" @click="showDeleteModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                aria-hidden="true">&#8203;</span>
                            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300 transform"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200 transform"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                                <form :action="actionUrl" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div
                                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10 text-rose-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-bold text-slate-800"
                                                    id="delete-role-title">Delete Role</h3>
                                                <p class="text-sm text-slate-500 mt-2">Are you sure you want to delete
                                                    <span class="font-semibold" x-text="currentRole.name"></span>? This
                                                    action cannot be undone.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-100">
                                        <button type="submit"
                                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-base font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Delete
                                            Role</button>
                                        <button type="button" @click="showDeleteModal = false"
                                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Section: System Preferences -->
            <section class="glass-card overflow-hidden">
                <form method="POST" action="{{ route('admin.settings.smart.update') }}" class="p-6 md:p-8 space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-violet-600 mb-1 block">Section
                                4</span>
                            <h3 class="text-lg font-bold text-slate-800">System Preferences</h3>
                            <p class="text-sm text-slate-500 mt-1">Configure structural variables, constraints, and
                                defaults.</p>
                        </div>
                        <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Preferences</button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Teacher Max Lectures Per
                                    Day</label>
                                <p class="text-xs text-slate-500 mb-3">Maximum number of lectures a teacher can be assigned
                                    in a single day.</p>
                                <input type="number" min="1" max="12"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-violet-500 focus:ring-violet-500 transition-colors shadow-sm"
                                    name="teacher_max_lectures_per_day" value="{{ $teacherMaxLecturesPerDay }}">
                                @error('teacher_max_lectures_per_day')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-800 mb-1">Default Slots Per Day</label>
                                <p class="text-xs text-slate-500 mb-3">Total time slots available for scheduling per
                                    working day.</p>
                                <input type="number" min="1" max="{{ $maxSlots }}"
                                    class="w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-violet-500 focus:ring-violet-500 transition-colors shadow-sm"
                                    name="slots_per_day" value="{{ $slotsPerDay }}">
                                @error('slots_per_day')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-800 mb-1">Default Working Days</label>
                            <p class="text-xs text-slate-500 mb-3">Select the active operational days for the institution.
                            </p>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($availableDays as $day)
                                    <label
                                        class="flex items-center gap-2 p-3 rounded-xl border border-slate-100 bg-slate-50/50 cursor-pointer hover:border-violet-300 transition-colors">
                                        <input type="checkbox" name="working_days[]" value="{{ $day }}"
                                            class="form-checkbox h-4 w-4 text-violet-600 rounded border-slate-300 focus:ring-violet-500 transition duration-150 ease-in-out"
                                            @checked(in_array($day, $workingDays))>
                                        <span
                                            class="text-sm font-semibold text-slate-700 capitalize">{{ substr($day, 0, 3) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('working_days')
                                <p class="text-xs text-rose-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </form>
            </section>
        </div>

        <!-- TAB: Activity Logs -->
        <div x-show="activeTab === 'logs'" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            style="display: none;" class="space-y-8">
            <section class="glass-card overflow-hidden">
                <div class="p-6 md:p-8 flex items-center justify-between border-b border-slate-100 pb-5">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">System Activity Logs</h3>
                        <p class="text-sm text-slate-500 mt-1">Audit trail of critical system events and state changes.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr
                                class="bg-slate-50/50 border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                <th class="p-4 pl-6">Timestamp</th>
                                <th class="p-4">User</th>
                                <th class="p-4">Action</th>
                                <th class="p-4">Path / Context</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 pr-6">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($auditLogs as $log)
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="p-4 pl-6 text-slate-600 font-medium">
                                        {{ $log->created_at->format('M d, Y h:i A') }}
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $log->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="p-4">
                                        @if ($log->user)
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center font-bold text-[10px] uppercase">
                                                    {{ substr($log->user->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-slate-700 font-semibold block">{{ $log->user->name }}</span>
                                                    <span
                                                        class="text-[10px] text-slate-400 font-mono">{{ $log->user->email }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">System / Guest</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono tracking-wider w-max
                                                {{ $log->method === 'GET' ? 'bg-sky-50 text-sky-600 border border-sky-100' : '' }}
                                                {{ $log->method === 'POST' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : '' }}
                                                {{ in_array($log->method, ['PUT', 'PATCH']) ? 'bg-amber-50 text-amber-600 border border-amber-100' : '' }}
                                                {{ $log->method === 'DELETE' ? 'bg-rose-50 text-rose-600 border border-rose-100' : '' }}">
                                                {{ $log->method }}
                                            </span>
                                            <span class="text-slate-700 font-medium truncate max-w-[200px]"
                                                title="{{ $log->action }}">{{ $log->action }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-slate-500 font-mono text-xs truncate max-w-[250px]"
                                        title="{{ $log->path }}">
                                        {{ Str::limit($log->path, 40) }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @if ($log->status_code)
                                            <span
                                                class="inline-flex items-center justify-center min-w-[3rem] px-2 py-1 rounded-md text-xs font-bold
                                                {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-emerald-50 text-emerald-600' : '' }}
                                                {{ $log->status_code >= 300 && $log->status_code < 400 ? 'bg-blue-50 text-blue-600' : '' }}
                                                {{ $log->status_code >= 400 && $log->status_code < 500 ? 'bg-amber-50 text-amber-600' : '' }}
                                                {{ $log->status_code >= 500 ? 'bg-rose-50 text-rose-600' : '' }}">
                                                {{ $log->status_code }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="p-4 pr-6 text-slate-500 font-mono text-xs">
                                        {{ $log->ip_address ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-500">
                                        No activity logs recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($auditLogs, 'links') && $auditLogs->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            </section>
        </div>

    </div>
@endsection
