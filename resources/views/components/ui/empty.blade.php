@props(['icon' => 'ri-inbox-2-line', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-line-strong px-6 py-16 text-center']) }}>
    <span class="mb-4 grid size-14 place-items-center rounded-2xl bg-surface-2 text-2xl text-ink-3"><i class="{{ $icon }}"></i></span>
    <h3 class="font-display text-lg font-semibold text-ink">{{ $title }}</h3>
    @if ($text)
        <p class="mt-1 max-w-sm text-sm text-ink-2">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
