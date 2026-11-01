@php
    $tagOptions = collect([['value' => '', 'label' => __('app.Toutes les étiquettes')]])
        ->concat($availableTags->map(fn ($tag) => ['value' => $tag->id, 'label' => $tag->name]))
        ->all();
    $sortOptions = [
        ['value' => 'recent', 'label' => __('app.Plus récents')],
        ['value' => 'oldest', 'label' => __('app.Plus anciens')],
        ['value' => 'name', 'label' => __('app.Nom (A-Z)')],
        ['value' => 'size', 'label' => __('app.Taille (décroissante)')],
    ];
    $bulkGroupOptions = collect([['value' => '', 'label' => __('app.Public')]])
        ->concat($groups->map(fn ($group) => ['value' => $group->id, 'label' => $group->name]))
        ->all();
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Bibliothèque')" :title="__('app.Images disque')"
                      :subtitle="trans_choice('app.:count image disque accessible|:count images disque accessibles', $isos->total(), ['count' => $isos->total()])">
        <x-slot:actions>
            <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary"><i class="ri-upload-cloud-2-line"></i>{{ __('app.Ajouter une image') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Barre d'outils --}}
    <div class="relative z-20">
        <div class="flex flex-col gap-2 lg:flex-row">
            <label class="relative flex-1">
                <i class="ri-search-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-lg text-ink-3"></i>
                <input wire:model.live.debounce.300ms="search" type="search" class="input h-11 pl-10"
                       placeholder="{{ __('app.Nom, OS, description… ou collez un SHA-256') }}">
                <i wire:loading wire:target="search" class="ri-loader-4-line absolute top-1/2 right-3.5 -translate-y-1/2 animate-spin text-ink-3"></i>
            </label>
            <div class="grid grid-cols-2 gap-2 lg:w-[420px]">
                <x-custom-select wire:model.live="selectedTag" :options="$tagOptions" icon="ri-price-tag-3-line" />
                <x-custom-select wire:model.live="sortBy" :options="$sortOptions" icon="ri-sort-desc" />
            </div>
        </div>
        <div class="scrollbar-none mt-3 overflow-x-auto"><div class="segmented">
            <button type="button" wire:click="$set('filter', 'all')" @class(['chip', 'is-active' => $filter === 'all'])>{{ __('app.Tous') }}</button>
            <button type="button" wire:click="$set('filter', 'public')" @class(['chip', 'is-active' => $filter === 'public'])><i class="ri-global-line"></i>{{ __('app.Publics') }}</button>
            @foreach ($groups as $group)
                <button type="button" wire:key="filter-group-{{ $group->id }}" wire:click="$set('filter', '{{ $group->id }}')" @class(['chip', 'is-active' => $filter === (string) $group->id])>
                    <i class="ri-lock-2-line"></i>{{ $group->name }}
                </button>
            @endforeach
        </div></div>
    </div>

    {{-- Grille --}}
    <div class="relative mt-6" wire:loading.class="opacity-60 transition-opacity" wire:target="search,filter,selectedTag,sortBy,gotoPage,nextPage,previousPage">
        @if ($isos->isEmpty())
            <x-ui.empty icon="ri-search-eye-line" :title="__('app.Aucun ISO trouvé')" :text="__('app.Aucun fichier ne correspond à votre recherche ou filtre.')">
                @if ($search || $filter !== 'all' || $selectedTag)
                    <button type="button" class="btn btn-secondary" wire:click="resetFilters"><i class="ri-filter-off-line"></i>{{ __('app.Réinitialiser les filtres') }}</button>
                @else
                    <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary"><i class="ri-add-line"></i>{{ __('app.Ajouter une image') }}</a>
                @endif
            </x-ui.empty>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($isos as $file)
                    <x-file-card :file="$file"
                                 :duplicate="$file->checksum && in_array($file->checksum, $duplicateChecksums)"
                                 :selectable="auth()->user()->can('update', $file) || auth()->user()->can('delete', $file)" />
                @endforeach
            </div>

            <div class="mt-8">{{ $isos->links('pagination.silo') }}</div>
        @endif
    </div>

    {{-- Dock de sélection multiple --}}
    @if (count($selected) > 0)
        <div class="fixed inset-x-3 bottom-24 z-30 mx-auto max-w-3xl animate-rise lg:bottom-6 lg:left-[250px] lg:px-10">
            <div class="flex flex-col gap-3 rounded-2xl border border-line-strong bg-elevated/95 p-3 shadow-2xl backdrop-blur-xl sm:flex-row sm:items-center">
                <div class="flex items-center gap-3 pl-1">
                    <span class="grid h-8 min-w-8 place-items-center rounded-lg bg-accent px-2 font-mono text-sm font-bold text-on-accent">{{ count($selected) }}</span>
                    <span class="text-sm text-ink-2">{{ __('app.fichier(s) sélectionné(s)') }}</span>
                    <button type="button" wire:click="clearSelection" class="icon-btn size-8 sm:hidden" aria-label="{{ __('app.Tout désélectionner') }}"><i class="ri-close-line"></i></button>
                </div>
                <div class="flex flex-1 flex-wrap items-center gap-2 sm:justify-end">
                    <div class="w-full sm:w-44"><x-custom-select wire:model="bulkGroupId" :options="$bulkGroupOptions" icon="ri-folder-transfer-line" /></div>
                    <button type="button" wire:click="assignGroupSelected" class="btn btn-secondary flex-1 sm:flex-none"><i class="ri-folder-transfer-line"></i>{{ __('app.Déplacer') }}</button>
                    <button type="button" data-bulk-delete class="btn btn-danger flex-1 sm:flex-none"
                            @click="$store.dialog.confirm({
                                title: @js(__('app.Supprimer la sélection ?')),
                                message: @js(__('app.Supprimer définitivement les fichiers sélectionnés ?')),
                                confirm: @js(__('app.Supprimer')),
                                tone: 'danger',
                            }).then(ok => ok && $wire.deleteSelected())">
                        <i class="ri-delete-bin-6-line"></i>{{ __('app.Supprimer') }}
                    </button>
                    <button type="button" wire:click="clearSelection" class="icon-btn hidden sm:inline-grid" title="{{ __('app.Tout désélectionner') }}"><i class="ri-close-line"></i></button>
                </div>
            </div>
            @error('bulkGroupId') <p class="field-error mt-2 justify-center">{{ $message }}</p> @enderror
        </div>
    @endif
</div>
