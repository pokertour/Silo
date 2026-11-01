@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('app.Bonjour') : ($hour < 18 ? __('app.Bon après-midi') : __('app.Bonsoir'));
    $firstName = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $stats = [
        ['label' => __('app.Images accessibles'), 'value' => $totalFiles, 'icon' => 'ri-album-line', 'hint' => __('app.dans la bibliothèque')],
        ['label' => __('app.Espace utilisé'), 'value' => \Illuminate\Support\Number::fileSize($totalSize, precision: 1), 'icon' => 'ri-hard-drive-3-line', 'hint' => __('app.toutes images confondues')],
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

    {{-- Tableau de bord instrumental --}}
    <section class="card grid grid-cols-2 overflow-hidden lg:grid-cols-4">
        @foreach ($stats as $index => $stat)
            <div @class([
                'relative p-5 sm:p-6',
                'border-line border-r' => $index % 2 === 0,
                'lg:border-r' => $index < 3,
                'border-b border-line lg:border-b-0' => $index < 2,
            ])>
                <div class="flex items-center justify-between">
                    <span class="spec">{{ $stat['label'] }}</span>
                    <i class="{{ $stat['icon'] }} text-lg text-ink-3"></i>
                </div>
                <p class="mt-4 font-display text-3xl font-semibold tracking-tight text-ink tabular-nums sm:text-4xl">{{ $stat['value'] }}</p>
                <p class="mt-1 text-xs text-ink-3">{{ $stat['hint'] }}</p>
                @if ($index === 0)
                    <span class="absolute inset-x-0 bottom-0 h-[3px] bg-accent"></span>
                @endif
            </div>
        @endforeach
    </section>

    <div class="mt-10 grid gap-8 xl:grid-cols-[minmax(0,1fr)_340px]">
        {{-- Derniers ajouts --}}
        <section class="min-w-0">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold text-ink">{{ __('app.Derniers ajouts') }}</h2>
                <div class="flex items-center gap-1">
                    <div class="flex rounded-xl border border-line p-0.5">
                        <button type="button" wire:click="toggleViewMode" @class(['icon-btn size-8', 'bg-surface-2 text-ink' => $viewMode === 'grid']) title="{{ __('app.Vue Grille') }}"><i class="ri-layout-grid-line"></i></button>
                        <button type="button" wire:click="toggleViewMode" @class(['icon-btn size-8', 'bg-surface-2 text-ink' => $viewMode === 'list']) title="{{ __('app.Vue Liste') }}"><i class="ri-list-check-2"></i></button>
                    </div>
                    <a href="{{ route('isos') }}" wire:navigate class="btn btn-ghost btn-sm">{{ __('app.Tout voir') }}<i class="ri-arrow-right-line"></i></a>
                </div>
            </div>

            <div class="scrollbar-none -mx-4 mb-5 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                <button type="button" wire:click="$set('filter', 'all')" @class(['chip', 'is-active' => $filter === 'all'])>{{ __('app.Tous') }}</button>
                <button type="button" wire:click="$set('filter', 'public')" @class(['chip', 'is-active' => $filter === 'public'])><i class="ri-global-line"></i>{{ __('app.Publics') }}</button>
                @foreach ($groups as $group)
                    <button type="button" wire:key="dash-group-{{ $group->id }}" wire:click="$set('filter', '{{ $group->id }}')" @class(['chip', 'is-active' => $filter === (string) $group->id])><i class="ri-lock-2-line"></i>{{ $group->name }}</button>
                @endforeach
            </div>

            @if ($isoFiles->isEmpty())
                <x-ui.empty icon="ri-album-line" :title="__('app.Aucun fichier disponible')" :text="__('app.Vous n\'avez accès à aucun fichier pour le moment.')">
                    <a href="{{ route('upload') }}" wire:navigate class="btn btn-primary"><i class="ri-add-line"></i>{{ __('app.Ajouter une image') }}</a>
                </x-ui.empty>
            @elseif ($viewMode === 'list')
                <div class="card divide-y divide-line overflow-hidden">
                    @foreach ($isoFiles as $file)
                        <x-file-row :file="$file" />
                    @endforeach
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($isoFiles as $file)
                        <x-file-card :file="$file" />
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Colonne latérale --}}
        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="panel-title mb-4"><i class="ri-pulse-line text-accent-ink"></i>{{ __('app.Activité Récente') }}</h2>
                <ol class="relative space-y-5 before:absolute before:top-2 before:bottom-2 before:left-[15px] before:w-px before:bg-line">
                    @forelse ($recentUploads as $upload)
                        <li wire:key="activity-{{ $upload->id }}" class="relative flex gap-3">
                            <x-ui.avatar :user="$upload->user" class="relative size-8 text-[11px] ring-4 ring-surface" />
                            <div class="min-w-0 pt-0.5">
                                <p class="text-sm text-ink-2"><span class="font-medium text-ink">{{ $upload->user->name }}</span> {{ __('app.a ajouté') }}</p>
                                <a href="{{ route('isos.show', $upload) }}" wire:navigate class="block truncate text-sm font-medium text-accent-ink hover:underline">{{ $upload->name }}</a>
                                <p class="mt-0.5 font-mono text-[11px] text-ink-3">{{ $upload->created_at->diffForHumans() }} · {{ $upload->size_human }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-ink-3">{{ __('app.Aucune activité récente.') }}</li>
                    @endforelse
                </ol>
            </section>

            <section class="card overflow-hidden">
                <div class="relative p-5">
                    <div class="pointer-events-none absolute -top-8 -right-8 opacity-80"><x-ui.disc size="lg" os="linux" class="animate-spin-slow" /></div>
                    <p class="eyebrow mb-2">{{ __('app.Astuce') }}</p>
                    <h3 class="max-w-[200px] font-display text-lg font-semibold text-ink">{{ __('app.Importez directement depuis une URL') }}</h3>
                    <p class="mt-1 text-sm text-ink-2">{{ __('app.Le serveur télécharge l\'image en arrière-plan et vérifie son empreinte.') }}</p>
                    <a href="{{ route('upload') }}" wire:navigate class="btn btn-secondary btn-sm mt-4"><i class="ri-link"></i>{{ __('app.Importer une URL') }}</a>
                </div>
            </section>
        </aside>
    </div>
</div>
