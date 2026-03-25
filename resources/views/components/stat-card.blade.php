@props([
    'label',
    'value',
    'icon',
    'tone' => 'indigo',
    'trend' => null,
    'trendUp' => true,
    'route' => null,
    'manageLabel' => 'Manage',
])

<div {{ $attributes->merge(['class' => 'stat-card group relative overflow-hidden']) }}>
    <div class="flex items-center justify-between mb-4 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-{{ $tone }}-50 text-{{ $tone }}-600 flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
            <i class="bi {{ $icon }}"></i>
        </div>
        @if($trend)
            <div class="flex items-center gap-1 px-2 py-1 rounded-lg {{ $trendUp ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} text-[10px] font-bold">
                <i class="bi {{ $trendUp ? 'bi-graph-up' : 'bi-graph-down' }}"></i>
                {{ $trend }}
            </div>
        @endif
    </div>
    
    <div class="relative z-10">
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ $label }}</p>
        <div class="flex items-end justify-between gap-2">
            <h3 class="text-3xl font-black text-slate-900 tracking-tight leading-none">{{ $value }}</h3>
            @if($route)
                <a href="{{ $route }}" class="text-[10px] font-black text-{{ $tone }}-600 hover:text-{{ $tone }}-700 hover:underline uppercase tracking-tighter">
                    {{ $manageLabel }}
                </a>
            @endif
        </div>
    </div>

    <!-- Decoration -->
    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-{{ $tone }}-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
</div>
