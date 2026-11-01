{{-- Actions sur une image disque : éditer, supprimer, partager, télécharger. --}}
@props(['file', 'compact' => false])
@php $user = auth()->user(); @endphp
<div {{ $attributes->merge(['class' => 'flex items-center gap-0.5']) }}>
    @can('update', $file)
        <button type="button" class="icon-btn" title="{{ __('app.Éditer') }}"
                @click.prevent="$dispatch('openModal', { component: 'edit-iso-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-pencil-line"></i></button>
    @endcan
    @can('delete', $file)
        <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                @click.prevent="$dispatch('openModal', { component: 'delete-iso-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-delete-bin-6-line"></i></button>
    @endcan
    @can('share', $file)
        <button type="button" class="icon-btn" title="{{ __('app.Partager') }}"
                @click.prevent="$dispatch('openModal', { component: 'share-modal', arguments: { isoId: {{ $file->id }} } })"><i class="ri-share-forward-line"></i></button>
    @endcan
    <button type="button" @class(['btn btn-secondary btn-sm ml-1', 'px-2.5' => $compact]) title="{{ __('app.Télécharger') }}"
            @click.prevent="$dispatch('start-mega-download', { url: @js(route('download', $file->uuid)), filename: @js($file->name.'.'.pathinfo($file->file_path, PATHINFO_EXTENSION)) })">
        <i class="ri-download-2-line"></i>@unless ($compact)<span>{{ __('app.Télécharger') }}</span>@endunless
    </button>
</div>
