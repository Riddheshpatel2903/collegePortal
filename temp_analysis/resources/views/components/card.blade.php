@props(['title' => null, 'icon' => null, 'headerAction' => null])

<div {{ $attributes->merge(['class' => 'glass-card overflow-hidden']) }}>
    @if($title || $icon || $headerAction)
        <div class="px-6 py-4 border-b border-slate-100/50 flex items-center justify-between bg-white/30">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                @if($icon)
                    <i class="bi {{ $icon }} text-violet-500"></i>
                @endif
                {{ $title }}
            </h3>
            @if($headerAction)
                <div>
                    {{ $headerAction }}
                </div>
            @endif
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
