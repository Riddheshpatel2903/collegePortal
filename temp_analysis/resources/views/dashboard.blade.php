@extends('layouts.app')

@section('header_title', 'Dashboard')

@section('content')
    <div class="glass-card p-10 text-center relative overflow-hidden group">
        <div
            class="absolute -right-10 -top-10 w-40 h-40 bg-violet-50 rounded-full blur-3xl group-hover:scale-150 transition-transform">
        </div>

        <div class="relative z-10 flex flex-col items-center space-y-5">
            <div
                class="h-16 w-16 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 text-white flex items-center justify-center text-3xl shadow-lg shadow-violet-500/30">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Welcome to EduPortal</h3>
                <p class="text-sm text-slate-400 mt-2">You are logged in. Use the sidebar to navigate to your dashboard.</p>
            </div>
            <div class="flex gap-3 pt-4">
                <span class="gradient-badge bg-teal-50 text-teal-600"><i class="bi bi-circle-fill text-[6px] mr-1.5"></i>
                    Active Session</span>
                <span class="gradient-badge bg-violet-50 text-violet-600">Role: {{ auth()->user()->role ?? 'User' }}</span>
            </div>
        </div>
    </div>
@endsection