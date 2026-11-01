@props(['eyebrow' => null, 'title', 'subtitle' => null])
<header {{ $attributes->merge(['class' => 'mb-7 flex flex-col gap-4 md:flex-row md:items-end md:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="eyebrow mb-1">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-balance text-[30px] leading-tight font-bold tracking-[-0.03em] text-ink sm:text-[38px]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1.5 max-w-2xl text-[15px] text-ink-3">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
