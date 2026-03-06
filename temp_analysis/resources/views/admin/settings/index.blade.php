@extends('layouts.app')

@section('header_title', 'System Settings & Access Control')

@section('content')
<!-- Global Layout Shell -->
<div class="max-w-7xl mx-auto space-y-8 pb-12" x-data="{ activeTab: 'rbac' }">
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-emerald-700 font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    @endif

    <!-- Header & Navigation Tabs -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Access Control & Settings</h2>
            <p class="text-slate-500 mt-1">Enterprise Role-Based Access Control and Core Configuration.</p>
        </div>
        <div class="flex space-x-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200 overflow-x-auto hide-scrollbar">
            <button type="button" @click="activeTab = 'rbac'" 
                    :class="activeTab === 'rbac' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" 
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Role & Page Control
            </button>
            <button type="button" @click="activeTab = 'roles'" 
                    :class="activeTab === 'roles' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" 
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Role Management
            </button>
            <button type="button" @click="activeTab = 'modules'" 
                    :class="activeTab === 'modules' ? 'bg-violet-50 text-violet-700 font-semibold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'" 
                    class="px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Core Modules & Toggles
            </button>
        </div>
    </div>
    
    <!-- Tab Contents Container -->
    <div class="relative">

    <!-- Section: Core Modules -->
    <section class="glass-card overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.modules.update') }}" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Core Modules</h3>
                    <p class="text-sm text-slate-500 mt-1">Enable or disable major sections of the portal. Disabling a module hides it globally.</p>
                </div>
                <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Modules</button>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($modules as $module)
                    <label class="flex items-start justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                        <div class="pr-4">
                            <span class="text-sm font-bold text-slate-800 block mb-0.5 group-hover:text-violet-700 transition-colors">{{ str_replace('_', ' ', Str::title($module->module_key)) }}</span>
                            <span class="text-xs text-slate-500">Toggle this core module on or off for the entire system.</span>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer shrink-0 mt-1">
                            <input type="hidden" name="modules[{{ $module->module_key }}]" value="0">
                            <input type="checkbox" name="modules[{{ $module->module_key }}]" value="1" class="sr-only peer" @checked($module->enabled)>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </div>
                    </label>
                @endforeach
            </div>
        </form>
    </section>

    <!-- Section: Feature Toggles -->
    <section class="glass-card overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.features.update') }}" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Feature Toggles</h3>
                    <p class="text-sm text-slate-500 mt-1">Activate or deactivate specific functionalities across the platform.</p>
                </div>
                <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Features</button>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($features as $feature)
                    <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 transition-colors cursor-pointer group">
                        <span class="text-sm font-semibold text-slate-700 group-hover:text-violet-700 transition-colors truncate pr-3">{{ str_replace('_', ' ', Str::title($feature->feature_key)) }}</span>
                        <div class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="hidden" name="features[{{ $feature->feature_key }}]" value="0">
                            <input type="checkbox" name="features[{{ $feature->feature_key }}]" value="1" class="sr-only peer" @checked($feature->enabled)>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-violet-600"></div>
                        </div>
                    </label>
                @endforeach
            </div>
        </form>
    </section>
        </div>

        <!-- TAB 1: Role & Page Control -->
        <div x-show="activeTab === 'rbac'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="space-y-8">

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
                'accountant': 'accountant'
            };
        },

        isPageRelevant(roleName, pageRoute) {
            if (roleName === 'super_admin') return true;
            if (pageRoute === 'profile.edit') return true;
            
            const prefix = this.pre[roleName];
            if (!prefix) return false;
            return pageRoute.startsWith(prefix + '.');
        }
    }">
        <div class="p-6 md:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-5">
                <h3 class="text-lg font-bold text-slate-800">Role & Page Management</h3>
                <p class="text-sm text-slate-500 mt-1">Select a role to configure their specific capabilities and manage which pages they can view.</p>
            </div>
            
            <!-- Step 1: Role Selection & Search -->
            <div class="grid md:grid-cols-2 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Step 1: Select Role</label>
                    <select x-model="selectedRole" class="w-full rounded-xl border-slate-200 bg-white focus:border-violet-500 focus:ring-violet-500 text-sm shadow-sm transition-colors cursor-pointer py-2.5 font-medium text-slate-700">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Search Pages & Capabilities</label>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Search by name or route..." class="w-full rounded-xl border-slate-200 bg-white focus:border-violet-500 focus:ring-violet-500 text-sm shadow-sm transition-colors placeholder:text-slate-400 py-2.5 pl-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Panels -->
            <div class="mt-8">
                @foreach($roles as $role)
                    <div x-show="selectedRole == '{{ $role->id }}'" style="display: none;" x-init="if(selectedRole == '{{ $role->id }}') $el.style.display = 'block'; else $el.style.display = 'none'; $watch('selectedRole', val => { $el.style.display = (val == '{{ $role->id }}') ? 'block' : 'none'; })">
                        
                        <!-- Role Capabilities Form -->
                        <form method="POST" action="{{ route('admin.settings.permissions.update') }}" class="mb-10 space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between pb-2 border-b border-slate-50">
                                <div>
                                    <h4 class="text-base font-bold text-slate-800 capitalize">{{ $role->name }} Capabilities</h4>
                                    <p class="text-xs text-slate-500 mt-1">Enable or disable core backend functionalities for this role.</p>
                                </div>
                                <button class="btn-primary-gradient px-4 py-2 text-sm whitespace-nowrap hidden sm:block">Save Capabilities</button>
                            </div>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($permissions as $permission)
                                    @php
                                        $assignedPermissionIds = $rolePermissionMatrix[$role->id] ?? [];
                                        $isChecked = in_array($permission->id, $assignedPermissionIds, true);
                                    @endphp
                                    <div x-show="searchQuery === '' || '{{ strtolower($permission->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($permission->key) }}'.includes(searchQuery.toLowerCase())">
                                        <label class="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 hover:border-violet-200 transition-all cursor-pointer group h-full">
                                            <div class="truncate pr-3">
                                                <span class="block text-sm font-bold text-slate-700 group-hover:text-violet-700">{{ $permission->name }}</span>
                                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5 truncate uppercase tracking-widest">{{ $permission->key }}</span>
                                            </div>
                                            <div class="relative inline-flex items-center cursor-pointer shrink-0">
                                                <input type="hidden" name="permissions_matrix[{{ $role->id }}][{{ $permission->id }}]" value="0">
                                                <input type="checkbox" name="permissions_matrix[{{ $role->id }}][{{ $permission->id }}]" value="1" class="sr-only peer" @checked($isChecked)>
                                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn-primary-gradient w-full py-2.5 text-sm sm:hidden mt-4">Save Capabilities</button>
                        </form>

                        <!-- Page Visibility Form -->
                        <form method="POST" action="{{ route('admin.settings.pages.update') }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between pb-2 border-b border-slate-50">
                                <div>
                                    <h4 class="text-base font-bold text-slate-800 capitalize">{{ $role->name }} Page Access</h4>
                                    <p class="text-xs text-slate-500 mt-1">Control which pages are visible to this role in their portal.</p>
                                </div>
                                <button class="btn-primary-gradient px-4 py-2 text-sm whitespace-nowrap hidden sm:block">Save Page Access</button>
                            </div>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($pages as $page)
                                    @php
                                        $permissionMap = $page->rolePermissions->keyBy('role_id');
                                        $isChecked = (bool) ($permissionMap[$role->id]->can_view ?? true);
                                    @endphp
                                    <div x-show="isPageRelevant('{{ $role->name }}', '{{ $page->route }}') && (searchQuery === '' || '{{ strtolower($page->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($page->route) }}'.includes(searchQuery.toLowerCase()))" 
                                         style="display: none;" 
                                         x-init="$watch('selectedRole', val => { 
                                            if(!isPageRelevant('{{ $role->name }}', '{{ $page->route }}')) return;
                                            $el.style.display = (searchQuery === '' || '{{ strtolower($page->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($page->route) }}'.includes(searchQuery.toLowerCase())) ? 'block' : 'none'; 
                                         });
                                         $watch('searchQuery', val => {
                                            if(!isPageRelevant('{{ $role->name }}', '{{ $page->route }}')) return;
                                            $el.style.display = (val === '' || '{{ strtolower($page->name) }}'.includes(val.toLowerCase()) || '{{ strtolower($page->route) }}'.includes(val.toLowerCase())) ? 'block' : 'none';
                                         });
                                         setTimeout(() => { if(isPageRelevant('{{ $role->name }}', '{{ $page->route }}')) $el.style.display = 'block'; }, 10);">
                                         
                                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/30 hover:bg-slate-50 hover:border-violet-200 transition-all group h-full flex flex-col justify-between">
                                            <div class="truncate mb-3 pb-3 border-b border-slate-200/60">
                                                <span class="block text-sm font-semibold text-slate-700 group-hover:text-violet-700 whitespace-normal leading-tight">{{ $page->name }}</span>
                                                <span class="block text-[10px] text-slate-400 font-mono mt-1 w-full overflow-hidden text-ellipsis uppercase tracking-wider">{{ $page->route }}</span>
                                            </div>
                                            
                                            <div class="space-y-3 mt-auto">
                                                @php
                                                    $actions = [
                                                        'can_view' => 'View', 
                                                        'can_create' => 'Create', 
                                                        'can_edit' => 'Edit', 
                                                        'can_delete' => 'Delete', 
                                                        'can_export' => 'Export'
                                                    ];
                                                @endphp
                                                @foreach($actions as $actionKey => $actionLabel)
                                                    @php
                                                        $isActionChecked = (bool) ($permissionMap[$role->id]->{$actionKey} ?? ($actionKey === 'can_view'));
                                                    @endphp
                                                    <label class="flex items-center justify-between cursor-pointer group/toggle py-1 border-b border-slate-100/40 last:border-0 last:pb-0">
                                                        <span class="text-xs font-semibold text-slate-600 group-hover/toggle:text-violet-600 transition-colors">{{ $actionLabel }}</span>
                                                        <div class="relative inline-flex items-center shrink-0">
                                                            <input type="hidden" name="permissions[{{ $page->id }}][{{ $role->id }}][{{ $actionKey }}]" value="0">
                                                            <input type="checkbox" name="permissions[{{ $page->id }}][{{ $role->id }}][{{ $actionKey }}]" value="1" class="sr-only peer" @checked($isActionChecked)>
                                                            <div class="w-8 h-4.5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-violet-600"></div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="!isPageRelevant('{{ $role->name }}', '{{ $page->route }}')" style="display: none;">
                                        <input type="hidden" name="permissions[{{ $page->id }}][{{ $role->id }}]" value="0">
                                        <input type="checkbox" name="permissions[{{ $page->id }}][{{ $role->id }}]" value="1" class="hidden" @checked($isChecked)>
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn-primary-gradient w-full py-2.5 text-sm sm:hidden mt-4">Save Page Access</button>
                        </form>

                    </div>
                @endforeach
            </div>
            
            <div x-show="searchQuery !== ''" class="mt-4 p-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center" style="display: none;">
                <p class="text-sm text-slate-500">Showing search results for "<span class="font-semibold text-slate-700" x-text="searchQuery"></span>"</p>
            </div>
            
        </div>
        
        <!-- TAB 2: Role Management -->
        <div x-show="activeTab === 'roles'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;" class="space-y-8">
            <div class="glass-card mb-8">
                <div class="p-6 md:p-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-1">Role Management</h3>
                        <p class="text-sm text-slate-500">Create, edit, or remove roles. System roles cannot be deleted.</p>
                    </div>
                    <button type="button" class="btn-primary-gradient px-4 py-2 text-sm whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create New Role
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-semibold">
                                <th class="p-4 pl-6 md:pl-8">Role Name</th>
                                <th class="p-4">Assigned Users</th>
                                <th class="p-4">Pages Accessible</th>
                                <th class="p-4 pr-6 md:pr-8 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($roles as $role)
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="p-4 pl-6 md:pl-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                            {{ substr($role->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 capitalize">{{ $role->name }}</p>
                                            @if(in_array($role->name, ['admin', 'super_admin', 'student', 'teacher']))
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10 mt-1">System Role</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 font-medium">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-50 text-sky-700 border border-sky-100/50 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ App\Models\User::where('role', $role->name)->count() }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-600">
                                    @php
                                        $accessCount = $role->pagePermissions->where('can_view', true)->count();
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 max-w-[100px]">
                                            <div class="bg-violet-500 h-1.5 rounded-full" style="width: {{ count($pages) > 0 ? ($accessCount / count($pages)) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold">{{ $accessCount }} / {{ count($pages) }}</span>
                                    </div>
                                </td>
                                <td class="p-4 pr-6 md:pr-8 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" class="p-2 text-slate-400 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition-colors tooltip" data-tip="Edit Name">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        @if(!in_array($role->name, ['admin', 'super_admin', 'student', 'teacher']))
                                        <button type="button" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors tooltip" data-tip="Delete Role">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Modals Container (Alpine-powered) -->
                <div x-data="{
                    showCreateModal: false,
                    showEditModal: false,
                    showDeleteModal: false,
                    currentRole: { id: '', name: '', description: '' },
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
                }" @open-create-role-modal.window="showCreateModal = true">
                    
                    <!-- Trigger Event Broadcaster on main Create Button -->
                    <script>
                        document.querySelector('button[type="button"][class*="btn-primary-gradient"]').addEventListener('click', function() {
                            window.dispatchEvent(new CustomEvent('open-create-role-modal'));
                        });
                    </script>

                    <!-- Create Role Modal -->
                    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="showCreateModal = false"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div x-show="showCreateModal" x-transition:enter="ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200 transform" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-100">
                                <form action="{{ route('admin.settings.roles.store') }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-violet-100 sm:mx-0 sm:h-10 sm:w-10 text-violet-600">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-bold text-slate-800" id="modal-title">Create New Role</h3>
                                                <div class="mt-4 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-bold text-slate-700 mb-1">Role Identifier Name <span class="text-rose-500">*</span></label>
                                                        <input type="text" name="name" required placeholder="e.g. librarian, editor" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 text-sm py-2 px-3 shadow-sm transition-colors text-slate-800 lowercase">
                                                        <p class="text-xs text-slate-500 mt-1">Must be unique, lowercase, no spaces (use underscores).</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-bold text-slate-700 mb-1">Description (Optional)</label>
                                                        <textarea name="description" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:border-violet-500 focus:ring-violet-500 text-sm py-2 px-3 shadow-sm transition-colors text-slate-800"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-slate-100">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-violet-600 text-base font-medium text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Create Role</button>
                                        <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancel</button>
                                    </div>
                                </form>
                            </div>
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
                    <span class="text-xs font-bold uppercase tracking-widest text-violet-600 mb-1 block">Section 4</span>
                    <h3 class="text-lg font-bold text-slate-800">System Preferences</h3>
                    <p class="text-sm text-slate-500 mt-1">Configure structural variables, constraints, and defaults.</p>
                </div>
                <button class="btn-primary-gradient px-5 py-2 text-sm whitespace-nowrap">Save Preferences</button>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-800 mb-1">Teacher Max Lectures Per Day</label>
                        <p class="text-xs text-slate-500 mb-3">Maximum number of lectures a teacher can be assigned in a single day.</p>
                        <input type="number" min="1" max="12" class="w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-violet-500 focus:ring-violet-500 transition-colors shadow-sm" name="teacher_max_lectures_per_day" value="{{ $teacherMaxLecturesPerDay }}">
                        @error('teacher_max_lectures_per_day')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-800 mb-1">Default Slots Per Day</label>
                        <p class="text-xs text-slate-500 mb-3">Total time slots available for scheduling per working day.</p>
                        <input type="number" min="1" max="{{ $maxSlots }}" class="w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-violet-500 focus:ring-violet-500 transition-colors shadow-sm" name="slots_per_day" value="{{ $slotsPerDay }}">
                        @error('slots_per_day')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1">Default Working Days</label>
                    <p class="text-xs text-slate-500 mb-3">Select the active operational days for the institution.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($availableDays as $day)
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-100 bg-slate-50/50 cursor-pointer hover:border-violet-300 transition-colors">
                                <input type="checkbox" name="working_days[]" value="{{ $day }}" 
                                    class="form-checkbox h-4 w-4 text-violet-600 rounded border-slate-300 focus:ring-violet-500 transition duration-150 ease-in-out"
                                    @checked(in_array($day, $workingDays))>
                                <span class="text-sm font-semibold text-slate-700 capitalize">{{ substr($day, 0, 3) }}</span>
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
@endsection
