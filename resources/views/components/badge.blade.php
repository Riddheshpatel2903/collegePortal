@props(['type' => 'default'])

@php
    $colors = [
        'default' => 'bg-slate-50 text-slate-600 border-slate-100',
        'primary' => 'bg-violet-50 text-violet-600 border-violet-100',
        'success' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
        'warning' => 'bg-amber-50 text-amber-600 border-amber-100',
        'danger' => 'bg-rose-50 text-rose-600 border-rose-100',
        'info' => 'bg-sky-50 text-sky-600 border-sky-100',
    ];
    
    $colorClass = $colors[$type] ?? $colors['default'];
@endphp

<div {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border $colorClass"]) }}>
    {{ $slot }}
</div>
