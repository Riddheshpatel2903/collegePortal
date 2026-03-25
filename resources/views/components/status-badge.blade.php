@props([
    'status',
    'type' => null,
])

@php
    $status = strtolower($status);
    
    $config = [
        'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'bi-check-circle-fill'],
        'inactive' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'bi-pause-circle-fill'],
        'locked' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'bi-lock-fill'],
        'pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'bi-clock-fill'],
        'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'bi-check-all'],
        'rejected' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'bi-x-circle-fill'],
        'completed' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-100', 'icon' => 'bi-check-circle-fill'],
    ];

    $style = $config[$status] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'bi-info-circle-fill'];
    
    if ($type) {
        $typeStyles = [
            'primary' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100', 'icon' => 'bi-info-circle-fill'],
            'success' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'bi-check-circle-fill'],
            'danger' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'bi-exclamation-triangle-fill'],
            'warning' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'bi-exclamation-circle-fill'],
        ];
        $style = $typeStyles[$type] ?? $style;
    }
@endphp

<div {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border {$style['bg']} {$style['text']} {$style['border']} text-[10px] font-black uppercase tracking-wider shadow-sm"]) }}>
    <i class="bi {{ $style['icon'] }} text-[11px]"></i>
    {{ $slot->isEmpty() ? ucfirst($status) : $slot }}
</div>
