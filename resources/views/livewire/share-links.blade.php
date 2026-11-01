@php $isAdmin = auth()->user()->hasRole('admin'); @endphp
<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Partage')" :title="__('app.Liens de Partage')"
                      :subtitle="trans_choice('app.:count lien actif ou expiré|:count liens actifs ou expirés', $links->total(), ['count' => $links->total()])">
        <x-slot:actions>
            <label class="relative w-full sm:w-72">
                <i class="ri-search-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                <input wire:model.live.debounce.300ms="search" type="search" class="input pl-10" placeholder="{{ __('app.Rechercher un fichier, un jeton…') }}">
            </label>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($links->isEmpty())
        <x-ui.empty icon="ri-links-line" :title="__('app.Aucun lien de partage.')" :text="__('app.Ouvrez une image disque et cliquez sur « Partager » pour créer un lien public.')">
            <a href="{{ route('isos') }}" wire:navigate class="btn btn-secondary"><i class="ri-disc-line"></i>{{ __('app.Parcourir la bibliothèque') }}</a>
        </x-ui.empty>
    @else
        <div class="card overflow-hidden">
            <div class="hidden grid-cols-[minmax(0,2.2fr)_1fr_1.2fr_1fr_auto] gap-4 border-b border-line px-5 py-3 lg:grid">
                <span class="spec">{{ __('app.Fichier ISO') }}</span>
                <span class="spec">{{ __('app.Statut') }}</span>
                <span class="spec">{{ __('app.Téléchargements') }}</span>
                <span class="spec">{{ __('app.Expiration') }}</span>
                <span class="spec w-[148px] text-right">{{ __('app.Actions') }}</span>
            </div>
            @foreach ($links as $link)
                @php
                    $valid = $link->isValid();
                    $ratio = $link->max_downloads ? min(100, round($link->download_count / $link->max_downloads * 100)) : null;
                @endphp
                <div wire:key="link-{{ $link->id }}" x-data="copyable(@js(route('share.show', $link->token)))"
                     class="grid gap-3 border-b border-line px-5 py-4 transition last:border-0 hover:bg-surface-2 lg:grid-cols-[minmax(0,2.2fr)_1fr_1.2fr_1fr_auto] lg:items-center lg:gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-ui.disc :file="$link->isoFile" size="sm" />
                        <div class="min-w-0">
                            @if ($link->isoFile)
                                <a href="{{ route('isos.show', $link->isoFile) }}" wire:navigate class="block truncate font-medium text-ink hover:underline">{{ $link->isoFile->name }}</a>
                            @else
                                <span class="text-ink-3">—</span>
                            @endif
                            <p class="truncate font-mono text-[11px] text-ink-3">
                                {{ $link->created_at->diffForHumans() }}@if ($isAdmin && $link->user) · {{ $link->user->name }}@endif
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if ($valid)
                            <span class="badge badge-ok"><span class="size-1.5 rounded-full bg-ok"></span>{{ __('app.Actif') }}</span>
                        @else
                            <span class="badge">{{ __('app.Expiré') }}</span>
                        @endif
                        @if ($link->password)
                            <span class="badge" title="{{ __('app.Protégé par mot de passe') }}"><i class="ri-lock-password-line"></i></span>
                        @endif
                    </div>
                    <div>
                        <p class="font-mono text-[13px] text-ink">{{ $link->download_count }} <span class="text-ink-3">/ {{ $link->max_downloads ?: '∞' }}</span></p>
                        @if ($ratio !== null)
                            <div class="meter mt-1.5 max-w-36"><span style="width: {{ $ratio }}%"></span></div>
                        @endif
                    </div>
                    <p class="font-mono text-[13px] text-ink-2">
                        @if ($link->expires_at)
                            <span title="{{ $link->expires_at->translatedFormat('j M Y H:i') }}">{{ $link->expires_at->diffForHumans() }}</span>
                        @else
                            <span class="text-ink-3">∞</span>
                        @endif
                    </p>
                    <div class="flex items-center gap-1 lg:w-[148px] lg:justify-end">
                        <button type="button" class="btn btn-secondary btn-sm" @click="copy()">
                            <i :class="copied ? 'ri-check-line text-ok' : 'ri-file-copy-line'"></i><span x-text="copied ? @js(__('app.Lien copié !')) : @js(__('app.Copier'))"></span>
                        </button>
                        <a href="{{ route('share.show', $link->token) }}" target="_blank" rel="noopener" class="icon-btn" title="{{ __('app.Ouvrir') }}"><i class="ri-external-link-line"></i></a>
                        <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                                @click.prevent="$dispatch('openModal', { component: 'delete-share-link-modal', arguments: { linkId: {{ $link->id }} } })"><i class="ri-delete-bin-6-line"></i></button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $links->links('pagination.silo') }}</div>
    @endif
</div>
