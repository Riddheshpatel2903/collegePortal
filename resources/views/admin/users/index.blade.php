@extends('layouts.app')

@section('header_title', 'User Management')

@section('content')
    <div class="space-y-8 animate-fade-in">
        {{-- ════════════ A. HEADER & ACTIONS ════════════ --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2">User Control</h2>
                <div class="flex items-center gap-3">
                    <x-badge type="info"
                        class="!bg-violet-50 !text-violet-600 border-none font-black text-[10px] uppercase tracking-widest">
                        Total Records: {{ $users->total() }}
                    </x-badge>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <p class="text-xs font-bold text-slate-400">Global user management and permissions.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                {{-- Role Filter --}}
                <div class="relative group">
                    <form action="{{ route('admin.users.index') }}" method="GET" id="filterForm"
                        class="flex flex-col sm:flex-row items-center gap-3">
                        {{-- Global Search Bar --}}
                        <div class="relative w-full sm:w-64">
                            <i class="absolute left-4 top-1/2 -translate-y-1/2 bi bi-search text-slate-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search ID, Name, Email..."
                                class="w-full pl-11 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm text-slate-700 focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 transition-all font-medium"
                                onkeypress="if(event.keyCode == 13) { document.getElementById('filterForm').submit(); return false; }">
                        </div>
                        <select name="role" onchange="document.getElementById('filterForm').submit()"
                            class="w-full sm:w-auto pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-violet-500/10 focus:border-violet-500 transition-all appearance-none cursor-pointer">
                            <option value="">All Identities</option>
                            @foreach($roles as $role)
                                @php
                                    $label = \Illuminate\Support\Str::title(str_replace('_', ' ', $role->name));
                                @endphp
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <x-button variant="primary" href="{{ route('admin.users.create') }}" icon="bi-person-plus-fill"
                    class="!py-3 !px-6 shadow-xl shadow-violet-200">
                    Register Identity
                </x-button>
            </div>
        </div>

        {{-- ════════════ B. MAIN DATA TABLE ════════════ --}}
        <x-card class="!p-0 overflow-hidden border-none shadow-2xl shadow-slate-200/50">
            <x-table :headers="['Identity', 'Authentication', 'Role Authority', 'Profile Details', 'Registry Actions']">
                @forelse($users as $user)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        {{-- Identity Column --}}
                        <td class="py-5">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    @php
                                        $avatarBg = match ($user->role) {
                                            'super_admin' => '111827',
                                            'admin' => 'f59e0b',
                                            'teacher' => '7c3aed',
                                            'student' => '0ea5e9',
                                            'accountant' => '10b981',
                                            'hod' => '0f766e',
                                            default => '64748b',
                                        };
                                    @endphp
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $avatarBg }}&color=fff&bold=true"
                                        class="h-12 w-12 rounded-2xl shadow-sm ring-2 ring-white group-hover:scale-105 transition-transform"
                                        alt="">
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-slate-800 tracking-tight truncate">{{ $user->name }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">ID:
                                        #SYS{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Authentication Column --}}
                        <td>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-700">{{ $user->email }}</span>
                                <span
                                    class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1 flex items-center gap-1">
                                    <i class="bi bi-patch-check-fill"></i> Verified
                                </span>
                            </div>
                        </td>

                        {{-- Role Authority Column --}}
                        <td>
                            @php
                                $roleConfig = [
                                    'super_admin' => ['bg' => 'bg-slate-900/10', 'text' => 'text-slate-900', 'label' => 'Super Admin'],
                                    'admin' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Administrator'],
                                    'hod' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-600', 'label' => 'HOD'],
                                    'teacher' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'label' => 'Faculty'],
                                    'student' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'label' => 'Student'],
                                    'accountant' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Financial Officer'],
                                ][$user->role] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'label' => \Illuminate\Support\Str::title(str_replace('_', ' ', $user->role ?? 'Guest'))];
                            @endphp
                            <x-badge type="info"
                                class="!{{ $roleConfig['bg'] }} !{{ $roleConfig['text'] }} border-none font-black text-[9px] uppercase tracking-widest px-3 py-1.5 rounded-xl">
                                {{ $roleConfig['label'] }}
                            </x-badge>
                        </td>

                        {{-- Profile Details Column --}}
                        <td>
                            <div class="text-sm text-slate-600">
                                @if($user->role === 'student')
                                    <div class="font-bold">{{ $user->student->course->name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                        {{ $user->student->gtu_enrollment_no ?? 'GTU-N/A' }} | Sem
                                        {{ $user->student->current_semester_number ?? 'N/A' }}
                                    </div>
                                @elseif($user->role === 'teacher')
                                    <div class="font-bold">{{ $user->teacher->department->name ?? 'Faculty' }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">
                                        {{ $user->teacher->qualification ?? 'Professor' }}</div>
                                @elseif($user->role === 'accountant')
                                    <div class="font-bold">Financial Officer</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Global Fee Access</div>
                                @elseif($user->role === 'hod')
                                    <div class="font-bold">Head of Department</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Department Oversight</div>
                                @else
                                    <div class="font-bold">
                                        {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $user->role ?? 'User')) }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-widest">Global Access</div>
                                @endif
                            </div>
                        </td>

                        {{-- Registry Actions Column --}}
                        <td class="text-right py-5">
                            @include('admin.users.partials.actions', ['user' => $user])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="py-20 text-center">
                                <div
                                    class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 text-3xl mx-auto mb-6">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight">No identities detected</h3>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-2">Adjust your filters
                                    or register a new user</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            {{-- ════════════ C. REGISTRY FOOTER & PAGINATION ════════════ --}}
            @if($users->hasPages())
                <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                            Displaying {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} Identities
                        </p>
                        <div class="pagination-premium">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </x-card>
    </div>
@endsection