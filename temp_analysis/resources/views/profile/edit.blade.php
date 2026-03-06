@extends('layouts.app')

@section('header_title', 'Profile Settings')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Profile Settings</h2>
        <p class="text-sm text-slate-400 mt-1">Manage your account information and security.</p>
    </div>

    <div class="max-w-3xl space-y-6">
        <!-- Profile Info -->
        <div class="glass-card p-8 group relative overflow-hidden">
            <div
                class="absolute -right-10 -top-10 w-32 h-32 bg-violet-50 rounded-full blur-3xl group-hover:scale-150 transition-transform">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
                        <i class="bi bi-person-lines-fill"></i></div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Profile Information</h3>
                        <p class="text-xs text-slate-400">Update your name and email address.</p>
                    </div>
                </div>
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Password -->
        <div class="glass-card p-8 group relative overflow-hidden">
            <div
                class="absolute -right-10 -top-10 w-32 h-32 bg-amber-50 rounded-full blur-3xl group-hover:scale-150 transition-transform">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i
                            class="bi bi-shield-lock-fill"></i></div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Change Password</h3>
                        <p class="text-xs text-slate-400">Use a strong, unique password to secure your account.</p>
                    </div>
                </div>
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account -->
        <div class="glass-card p-8 group relative overflow-hidden border border-rose-100">
            <div
                class="absolute -right-10 -top-10 w-32 h-32 bg-rose-50 rounded-full blur-3xl group-hover:scale-150 transition-transform">
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg"><i
                            class="bi bi-trash3-fill"></i></div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Delete Account</h3>
                        <p class="text-xs text-slate-400">Permanently remove your account and all associated data.</p>
                    </div>
                </div>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection