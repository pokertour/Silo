@props([
    'title',
    'value',
    'color' => 'indigo',
    'trend' => null
])

@php
    $colorClasses = [
        'indigo' => [
            'bg' => 'bg-indigo-500/10',
            'text' => 'text-indigo-400',
            'glow' => 'bg-indigo-500/5',
            'glowHover' => 'group-hover:bg-indigo-500/10'
        ],
        'purple' => [
            'bg' => 'bg-purple-500/10',
            'text' => 'text-purple-400',
            'glow' => 'bg-purple-500/5',
            'glowHover' => 'group-hover:bg-purple-500/10'
        ],
        'emerald' => [
            'bg' => 'bg-emerald-500/10',
            'text' => 'text-emerald-400',
            'glow' => 'bg-emerald-500/5',
            'glowHover' => 'group-hover:bg-emerald-500/10'
        ],
        'pink' => [
            'bg' => 'bg-pink-500/10',
            'text' => 'text-pink-400',
            'glow' => 'bg-pink-500/5',
            'glowHover' => 'group-hover:bg-pink-500/10'
        ],
        'amber' => [
            'bg' => 'bg-amber-500/10',
            'text' => 'text-amber-400',
            'glow' => 'bg-amber-500/5',
            'glowHover' => 'group-hover:bg-amber-500/10'
        ],
    ];

    $classes = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'glass group p-6 rounded-[2.5rem] relative overflow-hidden transition-all hover:scale-[1.02] active:scale-95']) }}>
    <div class="absolute -right-4 -top-4 w-24 h-24 {{ $classes['glow'] }} rounded-full blur-2xl transition-all {{ $classes['glowHover'] }}"></div>
    <div class="flex items-start justify-between">
        <div class="p-3.5 rounded-2xl {{ $classes['bg'] }} {{ $classes['text'] }}">
            {{ $icon ?? $slot }}
        </div>
        @if($trend)
            <div class="text-[10px] font-black px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-widest shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                {{ $trend }}
            </div>
        @endif
    </div>
    <div class="mt-4">
        <div class="text-4xl font-black text-white italic tracking-tighter">{{ $value }}</div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">{{ $title }}</div>
    </div>
</div>
