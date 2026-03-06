@extends('layouts.app')

@section('header_title', 'Page Not Found')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full p-10 text-center relative overflow-hidden animate-fade-in shadow-2xl shadow-violet-500/20">
        <!-- Background Effects -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-violet-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-fuchsia-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="w-24 h-24 mx-auto mb-8 bg-gradient-to-br from-violet-100 to-fuchsia-100 rounded-3xl flex items-center justify-center shadow-inner">
                <i class="bi bi-search text-4xl text-violet-600 bg-clip-text"></i>
            </div>
            
            <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600 tracking-tighter mb-4">404</h1>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Endpoint Not Responding</h2>
            <p class="text-slate-500 mb-8 font-medium">The requested academic parameter or system resource could not be located in the portal.</p>

            <a href="{{ route('dashboard') }}" class="btn-primary-gradient !w-full justify-center !py-3">
                <i class="bi bi-house-door-fill"></i> Return to Command Center
            </a>
        </div>
    </div>
</div>
@endsection
