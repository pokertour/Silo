@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('app.Bonjour') : ($hour < 18 ? __('app.Bon après-midi') : __('app.Bonsoir'));
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $stats = [
        ['label' => __('app.Images accessibles'), 'value' => $totalFiles, 'icon' => 'ri-disc-line', 'hint' => __('app.dans la bibliothèque')],
        ['label' => __('app.Espace utilisé'), 'value' => \Illuminate\Support\Number::fileSize($totalSize, precision: 1), 'icon' => 'ri-hard-drive-3-line', 'hint' => $diskCapacity ? __('app.sur').' '.\Illuminate\Support\Number::fileSize($diskCapacity, precision: 0) : __('app.toutes images confondues')],
        ['label' => __('app.Publiques'), 'value' => $publicFilesCount, 'icon' => 'ri-global-line', 'hint' => __('app.visibles par tous')],
        ['label' => __('app.Groupes'), 'value' => $userGroupsCount, 'icon' => 'ri-team-line', 'hint' => auth()->user()->hasRole('admin') ? __('app.sur l\'instance') : __('app.dont vous êtes membre')],
    ];
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="now()->translatedFormat('l j F')" :title="$greeting.', '.$firstName" :subtitle="__('app.Voici ce qui se passe dans votre bibliothèque d\'images disque.')">
        <x-slot:actions>
            <button type="button" class="btn btn-secondary" @click="$dispatch('open-palette')"><i class="ri-search-line"></i>{{ __('app.Rechercher') }}<kbd class="ml-1 hidden sm:inline-grid">/</kbd></button>
            <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary"><i class="ri-upload-cloud-2-line"></i>{{ __('app.Ajouter une image') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Statistiques --}}
    <section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @foreach ($stats as $index => $stat)
            <div class="card relative p-4 sm:p-5">
                @if ($index === 1)
                    @php $ratio = min(1, $totalSize / max(1, $diskCapacity ?? 0)) ; @endphp
                    @if (! empty($diskCapacity))
                        <svg class="absolute top-4 right-4 size-10 -rotate-90" viewBox="0 0 44 44" aria-hidden="true">
                            <circle cx="22" cy="22" r="18" fill="none" stroke="var(--surface-2)" stroke-width="5" />
                            <circle cx="22" cy="22" r="18" fill="none" stroke="var(--accent)" stroke-width="5" stroke-linecap="round" stroke-dasharray="113" stroke-dashoffset="{{ 113 - 113 * $ratio }}" />
                        </svg>
                    @endif
                @endif
                <p class="flex items-center gap-1.5 text-[13px] font-medium text-ink-3"><i class="{{ $stat['icon'] }} text-[15px] text-accent-ink"></i>{{ $stat['label'] }}</p>
                <p class="mt-2.5 font-mono text-[28px] leading-none font-semibold tracking-[-0.035em] text-ink sm:text-[32px]">{{ $stat['value'] }}</p>
                <p class="mt-1.5 text-[12.5px] text-ink-3">{{ $stat['hint'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="mt-9 grid gap-7 xl:grid-cols-[minmax(0,1fr)_330px]">
        {{-- Derniers ajouts --}}
        <section class="min-w-0">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-[21px] font-semibold tracking-[-0.025em] text-ink">{{ __('app.Derniers ajouts') }}</h2>
                <div class="flex items-center gap-1">
                    <div class="segmented">
                        <button type="button" wire:click="toggleViewMode" @class(['chip px-2', 'is-active' => $viewMode === 'grid']) title="{{ __('app.Vue Grille') }}"><i class="ri-layout-grid-line"></i></button>
                        <button type="button" wire:click="toggleViewMode" @class(['chip px-2', 'is-active' => $viewMode === 'list']) title="{{ __('app.Vue Liste') }}"><i class="ri-list-check-2"></i></button>
                    </div>
                    <a href="{{ route('isos') }}" wire:navigate class="btn btn-ghost btn-sm">{{ __('app.Tout voir') }}<i class="ri-arrow-right-line"></i></a>
                </div>
            </div>

            <div class="scrollbar-none -mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0"><div class="segmented">
                <button type="button" wire:click="$set('filter', 'all')" @class(['chip', 'is-active' => $filter === 'all'])>{{ __('app.Tous') }}</button>
                <button type="button" wire:click="$set('filter', 'public')" @class(['chip', 'is-active' => $filter === 'public'])><i class="ri-global-line"></i>{{ __('app.Publics') }}</button>
                @foreach ($groups as $group)
                    <button type="button" wire:key="dash-group-{{ $group->id }}" wire:click="$set('filter', '{{ $group->id }}')" @class(['chip', 'is-active' => $filter === (string) $group->id])><i class="ri-lock-2-line"></i>{{ $group->name }}</button>
                @endforeach
            </div></div>

            @if ($isoFiles->isEmpty())
                <x-ui.empty icon="ri-disc-line" :title="__('app.Aucun fichier disponible')" :text="__('app.Vous n\'avez accès à aucun fichier pour le moment.')">
                    <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary"><i class="ri-add-line"></i>{{ __('app.Ajouter une image') }}</a>
                </x-ui.empty>
            @elseif ($viewMode === 'list')
                <div class="card overflow-hidden divide-y divide-line">
                    @foreach ($isoFiles as $file)
                        <x-file-row :file="$file" />
                    @endforeach
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($isoFiles as $file)
                        <x-file-card :file="$file" />
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Colonne latérale --}}
        <aside class="space-y-6">
            <section class="card overflow-hidden">
                <h2 class="px-[18px] pt-4 pb-1.5 text-[15px] font-semibold text-ink">{{ __('app.Activité Récente') }}</h2>
                @forelse ($recentUploads as $upload)
                    <a href="{{ route('isos.show', $upload) }}" wire:navigate wire:key="activity-{{ $upload->id }}" class="list-row transition hover:bg-surface-2">
                        <span class="sym bg-accent"><i class="ri-upload-2-line"></i></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13.5px] text-ink">{{ $upload->name }}</span>
                            <span class="block truncate text-xs text-ink-3">{{ __('app.Ajouté par') }} {{ \Illuminate\Support\Str::before($upload->user->name, ' ') }} · {{ $upload->size_human }}</span>
                        </span>
                        <span class="shrink-0 text-xs text-ink-3">{{ $upload->created_at->diffForHumans(short: true) }}</span>
                    </a>
                @empty
                    <p class="px-[18px] pb-4 text-sm text-ink-3">{{ __('app.Aucune activité récente.') }}</p>
                @endforelse
            </section>

            <section class="card p-5">
                <span class="sym mb-3 bg-[#34c759]"><i class="ri-link"></i></span>
                <h3 class="text-[15px] font-semibold text-ink">{{ __('app.Importez directement depuis une URL') }}</h3>
                <p class="mt-1 text-[13px] text-ink-3">{{ __('app.Le serveur télécharge l\'image en arrière-plan et vérifie son empreinte.') }}</p>
                <a href="{{ route('upload') }}" wire:navigate class="btn btn-secondary btn-sm mt-4">{{ __('app.Importer une URL') }}</a>
            </section>
        </aside>
    </div>
</div>
