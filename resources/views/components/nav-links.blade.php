{{-- Liens de navigation (barre latérale desktop et tiroir mobile). --}}
@php
    $sections = \App\Support\Navigation::sections(auth()->user());
    $titles = ['workspace' => __('app.Espace de travail'), 'admin' => __('app.Administration')];
@endphp
<nav class="space-y-4" aria-label="{{ __('app.Navigation') }}">
    @foreach ($sections as $key => $entries)
        <div>
            <p class="mb-1 px-2.5 text-[11px] font-semibold text-ink-3">{{ $titles[$key] }}</p>
            <div class="space-y-px">
                @foreach ($entries as $entry)
                    <a href="{{ route($entry['route']) }}" wire:navigate
                       @class(['nav-item', 'is-active' => request()->routeIs($entry['active'])])
                       @if (request()->routeIs($entry['active'])) aria-current="page" @endif>
                        <i class="{{ $entry['icon'] }}"></i>
                        <span class="truncate">{{ $entry['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
