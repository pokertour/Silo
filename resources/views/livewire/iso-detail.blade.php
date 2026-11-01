@php
    $extension = pathinfo($isoFile->file_path, PATHINFO_EXTENSION);
    $specs = array_filter([
        __('app.Taille') => $isoFile->size_human,
        __('app.Système') => $isoFile->os_type,
        __('app.Version') => $isoFile->version,
        __('app.Architecture') => $isoFile->architecture,
        __('app.Format') => $extension ? strtoupper($extension) : null,
        __('app.Date d\'ajout') => $isoFile->created_at->translatedFormat('j M Y, H:i'),
    ]);
    $eventIcons = ['created' => 'ri-add-circle-line', 'updated' => 'ri-edit-circle-line', 'deleted' => 'ri-delete-bin-6-line'];
@endphp

<div class="animate-rise">
    <a href="{{ route('isos') }}" wire:navigate class="btn btn-ghost btn-sm -ml-3 mb-6"><i class="ri-arrow-left-line"></i>{{ __('app.Bibliothèque') }}</a>

    {{-- En-tête --}}
    <section class="card relative overflow-hidden p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 backdrop-dots opacity-60 [mask-image:linear-gradient(to_left,black,transparent_60%)]"></div>
        <div class="relative flex flex-col gap-6 md:flex-row md:items-center">
            <div class="art group size-[172px] shrink-0 self-start md:self-center"><x-ui.disc :file="$isoFile" :ext="false" size="xl" class="transition-transform duration-[2s] group-hover:rotate-[200deg]" /><span class="absolute right-3 bottom-2.5 text-[11px] font-semibold tracking-wide text-ink-3 uppercase">{{ $extension }}</span></div>
            <div class="min-w-0 flex-1">
                <p class="eyebrow mb-2">{{ collect([$isoFile->os_type, $isoFile->architecture])->filter()->implode(' · ') ?: __('app.Image disque') }}</p>
                <h1 class="text-balance font-display text-3xl font-semibold tracking-tight text-ink sm:text-[2.6rem] sm:leading-[1.05]">{{ $isoFile->name }}</h1>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <x-file-status :file="$isoFile" :duplicate="$duplicates->isNotEmpty()" />
                    @foreach ($isoFile->tags as $tag)
                        <span class="tag" style="--tag: {{ $tag->color }}">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="relative mt-6 flex flex-wrap items-center gap-2 border-t border-line pt-6">
            <button type="button" class="btn btn-primary"
                    @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $isoFile->uuid)), filename: @js($isoFile->name.'.'.$extension) })">
                <i class="ri-download-2-line"></i>{{ __('app.Télécharger') }} <span class="font-mono text-xs opacity-70">{{ $isoFile->size_human }}</span>
            </button>
            @can('share', $isoFile)
                <button type="button" class="btn btn-secondary" @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $isoFile->id }} } })">
                    <i class="ri-share-forward-line"></i>{{ __('app.Partager') }}
                </button>
            @endcan
            <div class="ml-auto flex items-center gap-1">
                @can('update', $isoFile)
                    <button type="button" class="btn btn-ghost" @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $isoFile->id }} } })">
                        <i class="ri-pencil-line"></i><span class="hidden sm:inline">{{ __('app.Éditer') }}</span>
                    </button>
                @endcan
                @can('delete', $isoFile)
                    <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}" @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $isoFile->id }} } })">
                        <i class="ri-delete-bin-6-line"></i>
                    </button>
                @endcan
            </div>
        </div>
    </section>

    @if ($duplicates->isNotEmpty())
        <div class="mt-4 flex items-start gap-3 rounded-2xl border border-warn/30 bg-warn/10 p-4">
            <i class="ri-file-copy-2-line mt-0.5 text-lg text-warn"></i>
            <div class="text-sm">
                <p class="font-semibold text-ink">{{ __('app.Doublon détecté') }}</p>
                <p class="mt-0.5 text-ink-2">{{ __('app.Ce fichier a la même empreinte SHA-256 que :') }}
                    @foreach ($duplicates as $dup)
                        <a href="{{ route('isos.show', $dup) }}" wire:navigate class="font-medium text-ink underline decoration-warn/50 underline-offset-2">{{ $dup->name }}</a>@if (! $loop->last), @endif
                    @endforeach
                </p>
            </div>
        </div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="min-w-0 space-y-6">
            {{-- Caractéristiques --}}
            <section class="card p-6">
                <h2 class="panel-title mb-5"><i class="ri-information-line text-accent-ink"></i>{{ __('app.Informations') }}</h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-3">
                    @foreach ($specs as $label => $value)
                        <div>
                            <dt class="spec">{{ $label }}</dt>
                            <dd class="mt-1 font-mono text-[15px] text-ink">{{ $value }}</dd>
                        </div>
                    @endforeach
                    <div>
                        <dt class="spec">{{ __('app.Téléversé par') }}</dt>
                        <dd class="mt-1.5 flex items-center gap-2 text-sm text-ink"><x-ui.avatar :user="$isoFile->user" class="size-6 text-[10px]" />{{ $isoFile->user->name }}</dd>
                    </div>
                    @role('admin')
                        <div class="col-span-2">
                            <dt class="spec">{{ __('app.Stockage') }}</dt>
                            <dd class="mt-1 truncate font-mono text-[13px] text-ink-2" title="{{ $isoFile->file_path }}">{{ $isoFile->disk }} · {{ $isoFile->file_path }}</dd>
                        </div>
                    @endrole
                </dl>
                @if ($isoFile->description)
                    <p class="mt-6 border-t border-line pt-5 text-[15px] leading-relaxed text-ink-2">{{ $isoFile->description }}</p>
                @endif
            </section>

            {{-- Intégrité --}}
            <section class="card p-6" x-data="copyable(@js($isoFile->checksum ?? ''))">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <h2 class="panel-title flex-1"><i class="ri-fingerprint-line text-accent-ink"></i>{{ __('app.Empreinte SHA-256') }}</h2>
                    @if ($isoFile->checksum_verified === true)
                        <span class="badge badge-ok" title="{{ __('app.Correspond à l\'empreinte fournie à l\'upload') }}"><i class="ri-shield-check-line"></i>{{ __('app.Intégrité vérifiée') }}</span>
                    @elseif ($isoFile->checksum_verified === false)
                        <span class="badge badge-danger" title="{{ __('app.Attendu :') }} {{ $isoFile->expected_checksum }}"><i class="ri-shield-cross-line"></i>{{ __('app.Empreinte non conforme !') }}</span>
                    @endif
                    <div class="flex items-center gap-1">
                        @if ($isoFile->checksum)
                            <button type="button" class="btn btn-secondary btn-sm" @click="copy()">
                                <i :class="copied ? 'ri-check-line text-ok' : 'ri-file-copy-line'"></i>
                                <span x-text="copied ? @js(__('app.Copié !')) : @js(__('app.Copier'))"></span>
                            </button>
                        @endif
                        @can('update', $isoFile)
                            <button type="button" wire:click="recalculateChecksum" wire:loading.attr="disabled" class="icon-btn" title="{{ __('app.Recalculer') }}">
                                <i class="ri-refresh-line" wire:loading.class="animate-spin" wire:target="recalculateChecksum"></i>
                            </button>
                        @endcan
                    </div>
                </div>
                @if ($isoFile->checksum)
                    <div class="grid grid-cols-4 gap-x-3 gap-y-1.5 rounded-xl border border-line bg-bg-2 p-4 font-code text-[13px] tracking-wide text-ink sm:grid-cols-8">
                        @foreach (str_split($isoFile->checksum, 8) as $chunk)
                            <span class="{{ $loop->index % 2 ? 'text-ink-2' : 'text-ink' }}">{{ $chunk }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-xl border border-dashed border-line-strong p-4 text-sm text-ink-2">
                        <i class="ri-loader-4-line animate-spin text-lg text-accent-ink"></i>{{ __('app.Checksum en cours de calcul…') }}
                    </div>
                @endif
            </section>

            {{-- Liens de partage --}}
            @can('share', $isoFile)
                <section class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-6 py-4">
                        <h2 class="panel-title"><i class="ri-links-line text-accent-ink"></i>{{ __('app.Liens de partage') }} <span class="font-mono text-sm font-normal text-ink-3">{{ $isoFile->shareLinks->count() }}</span></h2>
                        <button type="button" class="btn btn-secondary btn-sm" @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $isoFile->id }} } })"><i class="ri-add-line"></i>{{ __('app.Nouveau lien') }}</button>
                    </div>
                    @forelse ($isoFile->shareLinks as $link)
                        <div wire:key="share-{{ $link->id }}" x-data="copyable(@js(route('share.show', $link->token)))" class="flex flex-col gap-3 border-b border-line px-6 py-4 last:border-0 sm:flex-row sm:items-center">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span @class(['size-2 shrink-0 rounded-full', 'bg-ok' => $link->isValid(), 'bg-ink-3' => ! $link->isValid()])></span>
                                    <code class="truncate font-code text-[13px] text-ink">{{ route('share.show', $link->token) }}</code>
                                </div>
                                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 pl-4 text-xs text-ink-3">
                                    <span><i class="ri-download-2-line"></i> {{ $link->download_count }}{{ $link->max_downloads ? ' / '.$link->max_downloads : '' }} {{ __('app.téléchargements') }}</span>
                                    @if ($link->password)<span><i class="ri-lock-password-line"></i> {{ __('app.Protégé') }}</span>@endif
                                    @if ($link->expires_at)<span><i class="ri-time-line"></i> {{ __('app.Expiration') }} {{ $link->expires_at->diffForHumans() }}</span>@endif
                                    @unless ($link->isValid())<span class="text-danger">{{ __('app.Expiré') }}</span>@endunless
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-1 pl-4 sm:pl-0">
                                <button type="button" class="btn btn-secondary btn-sm" @click="copy()">
                                    <i :class="copied ? 'ri-check-line text-ok' : 'ri-file-copy-line'"></i><span x-text="copied ? @js(__('app.Lien copié !')) : @js(__('app.Copier'))"></span>
                                </button>
                                <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                                        @click="$store.dialog.confirm({ title: @js(__('app.Supprimer le lien')), message: @js(__('app.Supprimer ce lien de partage ?').' '.__('app.Cette action est irréversible.')), confirm: @js(__('app.Supprimer')), tone: 'danger' }).then(ok => ok && $wire.deleteShareLink({{ $link->id }}))">
                                    <i class="ri-delete-bin-6-line"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-ink-3">{{ __('app.Aucun lien de partage pour ce fichier.') }}</p>
                    @endforelse
                </section>
            @endcan
        </div>

        {{-- Historique --}}
        <aside>
            <section class="card p-6 xl:sticky xl:top-8">
                <h2 class="panel-title mb-5"><i class="ri-history-line text-accent-ink"></i>{{ __('app.Historique') }}</h2>
                <ol class="relative space-y-5 before:absolute before:top-2 before:bottom-2 before:left-[15px] before:w-px before:bg-line">
                    @forelse ($activities as $activity)
                        <li class="relative flex gap-3">
                            <span class="relative grid size-8 shrink-0 place-items-center rounded-full border border-line bg-surface-2 text-sm text-ink-2 ring-4 ring-surface">
                                <i class="{{ $eventIcons[$activity->event] ?? 'ri-record-circle-line' }}"></i>
                            </span>
                            <div class="min-w-0 pt-0.5">
                                <p class="text-sm text-ink"><span class="font-medium">{{ $activity->causer?->name ?? __('app.Système') }}</span> <span class="text-ink-2">· {{ __('app.'.$activity->event) }}</span></p>
                                <p class="mt-0.5 font-mono text-[11px] text-ink-3">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-3">{{ __('app.Aucune activité enregistrée.') }}</li>
                    @endforelse
                </ol>
            </section>
        </aside>
    </div>
</div>
