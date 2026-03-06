@extends('layouts.app')

@section('header_title', 'System Error')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full p-10 text-center relative overflow-hidden animate-fade-in shadow-2xl shadow-rose-500/20">
        <!-- Background Effects -->
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="w-24 h-24 mx-auto mb-8 bg-gradient-to-br from-rose-100 to-orange-100 rounded-3xl flex items-center justify-center shadow-inner">
                <i class="bi bi-exclamation-triangle-fill text-4xl text-rose-600 bg-clip-text"></i>
            </div>
            
            <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-orange-600 tracking-tighter mb-4">500</h1>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Internal Server Matrix Error</h2>
            <p class="text-slate-500 mb-8 font-medium">The academic portal encountered an unexpected logic exception while processing this request. Our engineers have been alerted automatically.</p>

            <a href="{{ route('dashboard') }}" class="btn-primary-gradient !bg-gradient-to-r !from-rose-600 !to-orange-600 !w-full justify-center !py-3 !border-none hover:shadow-rose-500/40">
                <i class="bi bi-arrow-left-circle-fill"></i> Return to Safety
            </a>
        </div>
    </div>
</div>
@endsection
