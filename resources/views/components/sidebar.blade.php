{{-- Barre latérale (desktop). --}}
@php $usage = \App\Support\Navigation::libraryUsage(auth()->user()); @endphp
<aside class="fixed inset-y-0 left-0 z-40 hidden w-[264px] flex-col border-r border-line bg-bg-2/80 backdrop-blur-xl lg:flex">
    <div class="flex h-[72px] items-center px-6">
        <a href="{{ route('dashboard') }}" wire:navigate><x-ui.logo :size="30" /></a>
    </div>

    <div class="space-y-2 px-4">
        <button type="button" @click="$dispatch('open-palette')"
                class="flex h-10 w-full items-center gap-2.5 rounded-xl border border-line bg-surface px-3 text-sm text-ink-3 transition hover:border-line-strong hover:text-ink-2">
            <i class="ri-search-line text-base"></i>
            <span class="flex-1 text-left">{{ __('app.Rechercher…') }}</span>
            <span class="flex gap-1"><kbd>⌘</kbd><kbd>K</kbd></span>
        </button>
        <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary w-full">
            <i class="ri-add-line"></i>{{ __('app.Ajouter une image') }}
        </a>
    </div>

    <div class="mt-6 flex-1 overflow-y-auto px-4 pb-4">
        <x-nav-links />
    </div>

    <div class="space-y-3 border-t border-line p-4">
        <div class="px-1">
            <div class="mb-2 flex items-baseline justify-between">
                <span class="spec">{{ __('app.Bibliothèque') }}</span>
                <span class="font-mono text-xs text-ink-2">{{ \Illuminate\Support\Number::fileSize($usage['size'], precision: 1) }}</span>
            </div>
            <div class="flex h-1.5 gap-[3px]" aria-hidden="true">
                @for ($i = 0; $i < 24; $i++)
                    <span @class(['flex-1 rounded-full', 'bg-accent' => $i < min(24, max(1, $usage['count'])), 'bg-surface-2' => $i >= min(24, max(1, $usage['count']))])></span>
                @endfor
            </div>
            <p class="mt-2 text-xs text-ink-3">{{ trans_choice('app.:count image disque|:count images disque', $usage['count'], ['count' => $usage['count']]) }}</p>
        </div>
        <x-user-card notifications-key="notifications-sidebar" />
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item w-full text-ink-3"><i class="ri-logout-box-r-line"></i>{{ __('app.Déconnexion') }}</button>
        </form>
    </div>
</aside>
