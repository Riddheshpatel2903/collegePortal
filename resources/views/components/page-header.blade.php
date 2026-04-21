@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'back' => null,
    'action' => null,
    'actionIcon' => null,
    'actionRoute' => null,
    'actionLabel' => null,
    'actionVariant' => 'primary-gradient',
])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8']) }}>
    <div class="flex items-center gap-4">
        @if($back)
            <a href="{{ $back }}" class="h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:text-slate-600 hover:border-slate-300 transition-all shrink-0">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
        <div>
            <div class="flex items-center gap-3 mb-1">
                @if($icon)
                    <div class="h-7 w-7 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                @endif
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                    {{ $title }}
                </h1>
            </div>
            @if($subtitle)
                <p class="text-slate-500 font-medium text-sm">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if($actionRoute || $action)
        <div class="flex items-center gap-3">
            @if($actionRoute)
                <x-button href="{{ $actionRoute }}" icon="{{ $actionIcon }}" variant="{{ $actionVariant }}">
                    {{ $actionLabel }}
                </x-button>
            @else
                {{ $action }}
            @endif
        </div>
    @endif
</div>
