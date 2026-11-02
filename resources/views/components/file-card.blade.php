{{-- Carte d'image disque (vue grille) : vignette avec CD, nom, détails, téléchargement. --}}
@props(['file', 'duplicate' => false, 'selectable' => false])
@php
    $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
    $version = filled($file->version) ? 'v'.ltrim(trim($file->version), 'vV') : null;
    $details = collect([$file->os_type, $file->architecture, $file->group?->name ?? __('app.Public')])->filter()->implode(' · ');
@endphp
<article wire:key="file-card-{{ $file->id }}" {{ $attributes->merge(['class' => 'card card-hover group relative flex flex-col p-3.5 has-[input:checked]:ring-2 has-[input:checked]:ring-accent']) }}>
    <div class="art h-[124px]">
        <x-ui.disc :file="$file" :ext="false" size="lg" class="transition duration-700 group-hover:rotate-[50deg]" />

        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
            @if ($file->checksum_verified === true)
                <span class="badge badge-ok h-5 px-2 text-[11px]"><i class="ri-check-line"></i>{{ __('app.Vérifié') }}</span>
            @elseif ($file->checksum_verified === false)
                <span class="badge badge-danger h-5 px-2 text-[11px]"><i class="ri-error-warning-line"></i>{{ __('app.Corrompu ?') }}</span>
            @endif
            @if ($duplicate)
                <span class="badge badge-warn h-5 px-2 text-[11px]">{{ __('app.Doublon') }}</span>
            @endif
        </div>
        @if ($version)
            <span class="badge badge-accent absolute bottom-2 left-2.5 h-5 max-w-[60%] truncate px-2 font-mono text-[11px]" title="{{ __('app.Version') }} {{ $file->version }}">{{ $version }}</span>
        @endif
        @if ($extension)
            <span class="absolute right-3 bottom-2.5 text-[10.5px] font-semibold tracking-wide text-ink-3 uppercase">{{ $extension }}</span>
        @endif
        @if ($selectable)
            <input type="checkbox" wire:model.live="selected" value="{{ $file->id }}" aria-label="{{ __('app.Sélectionner') }} {{ $file->name }}"
                   class="absolute top-2.5 right-2.5 z-20 size-[18px] cursor-pointer rounded accent-[var(--accent)] transition sm:opacity-0 sm:group-hover:opacity-100 sm:checked:opacity-100 sm:focus-visible:opacity-100">
        @endif
    </div>

    <div class="px-1 pt-3">
        <h3 class="text-[15px] leading-snug font-semibold tracking-[-0.015em] text-ink">
            <a href="{{ route('isos.show', $file) }}" wire:navigate class="after:absolute after:inset-0 after:rounded-[18px] focus:outline-none">{{ $file->name }}</a>
        </h3>
        <p class="mt-0.5 truncate text-[12.5px] text-ink-3">{{ $details }}</p>
        @if ($file->tags->isNotEmpty())
            <div class="relative z-10 mt-2.5 flex flex-wrap gap-1">
                @foreach ($file->tags as $tag)
                    <span class="tag" style="--tag: {{ $tag->color }}">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="min-h-3 flex-1"></div>
    <div class="relative z-10 flex items-center gap-1 px-1 pt-1">
        <span class="badge font-mono">{{ $file->size_human }}</span>
        <div class="ml-auto flex items-center">
            @can('share', $file)
                <button type="button" class="icon-btn" title="{{ __('app.Partager') }}"
                        @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-share-forward-line"></i></button>
            @endcan
            @can('update', $file)
                <button type="button" class="icon-btn" title="{{ __('app.Éditer') }}"
                        @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-pencil-line"></i></button>
            @endcan
            @can('delete', $file)
                <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                        @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-delete-bin-line"></i></button>
            @endcan
            <button type="button" class="btn btn-secondary btn-sm ml-1 font-semibold" title="{{ __('app.Télécharger') }}"
                    @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $file->uuid)), filename: @js($file->name.'.'.$extension) })">{{ __('app.Obtenir') }}</button>
        </div>
    </div>
</article>
