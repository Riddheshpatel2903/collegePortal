@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'action',
    'method' => 'POST',
    'id' => null,
    'submitLabel' => 'Submit',
    'submitIcon' => 'bi-check-circle',
    'cancelRoute' => null,
    'reset' => false,
])

<div class="glass-card overflow-hidden">
    <div class="p-8">
        <form action="{{ $action }}" method="{{ $method === 'GET' ? 'GET' : 'POST' }}" id="{{ $id }}" {{ $attributes }}>
            @if($method !== 'GET')
                @csrf
                @if(!in_array($method, ['POST', 'GET']))
                    @method($method)
                @endif
            @endif

            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-slate-100">
                @if($icon)
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-sm">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                {{ $slot }}
            </div>

            <div class="mt-10 pt-8 border-t border-slate-100 flex items-center justify-end gap-3">
                @if($cancelRoute)
                    <a href="{{ $cancelRoute }}" class="px-6 py-2.5 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                        Cancel
                    </a>
                @endif
                
                @if($reset)
                    <button type="reset" class="px-6 py-2.5 text-sm font-bold text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                        Reset
                    </button>
                @endif

                <x-button type="submit" id="{{ $id ? $id.'-submit' : '' }}" icon="{{ $submitIcon }}" size="md">
                    <span id="{{ $id ? $id.'-label' : '' }}">{{ $submitLabel }}</span>
                </x-button>
            </div>
        </form>
    </div>
</div>
