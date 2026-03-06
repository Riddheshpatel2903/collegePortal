@props(['variant' => 'primary', 'size' => 'md', 'icon' => null])

@php
    $variants = [
        'primary' => 'btn-primary-gradient',
        'outline' => 'btn-outline',
        'danger' => 'bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-500/30',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-8 py-4 text-base',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button {{ $attributes->merge(['class' => "inline-flex items-center gap-2 font-bold rounded-xl transition-all duration-200 active:scale-95 $variantClass $sizeClass"]) }}>
    @if($icon)
        <i class="bi {{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>
