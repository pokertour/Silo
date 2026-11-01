@props(['eyebrow' => null, 'title', 'subtitle' => null])
<header {{ $attributes->merge(['class' => 'mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="eyebrow mb-2">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-balance font-display text-3xl font-semibold tracking-tight text-ink sm:text-[2.6rem] sm:leading-[1.05]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 max-w-2xl text-[15px] text-ink-2">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
