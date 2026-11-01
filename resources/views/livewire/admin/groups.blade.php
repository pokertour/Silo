<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Groupes')" :subtitle="__('app.Organisez vos utilisateurs pour des partages ISO ciblés.')">
        <x-slot:actions>
            <label class="relative w-full sm:w-64">
                <i class="ri-search-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                <input wire:model.live.debounce.300ms="search" type="search" class="input pl-10" placeholder="{{ __('app.Rechercher un groupe…') }}">
            </label>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
        <section class="card h-fit p-6 lg:sticky lg:top-8">
            <span class="mb-4 grid size-11 place-items-center rounded-2xl bg-accent-soft text-xl text-accent-ink"><i class="ri-team-line"></i></span>
            <h2 class="font-display text-lg font-semibold text-ink">{{ __('app.Créer un groupe') }}</h2>
            <p class="mt-1 mb-5 text-sm text-ink-2">{{ __('app.Les fichiers d\'un groupe ne sont visibles que par ses membres.') }}</p>
            <form wire:submit="create" class="space-y-4">
                <div>
                    <label class="label" for="group-name">{{ __('app.Nom du groupe') }}</label>
                    <input wire:model="name" id="group-name" type="text" class="input" placeholder="{{ __('app.ex: Developpeurs') }}">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="group-description">{{ __('app.Description') }}</label>
                    <textarea wire:model="description" id="group-description" rows="2" class="input" placeholder="{{ __('app.Accès aux ISO liés au développement') }}"></textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-full"><i class="ri-add-line"></i>{{ __('app.Créer') }}</button>
            </form>
        </section>

        <div class="space-y-4">
            @forelse ($groups as $group)
                <section wire:key="group-{{ $group->id }}" class="card p-5">
                    <div class="flex items-start gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl border border-line bg-surface-2 font-display text-lg font-semibold text-ink">{{ mb_strtoupper(mb_substr($group->name, 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-display text-lg font-semibold text-ink">{{ $group->name }}</h3>
                            <p class="text-sm text-ink-2">{{ $group->description ?? __('app.Aucune description') }}</p>
                        </div>
                        <span class="badge">{{ trans_choice('app.:count membre|:count membres', $group->users->count(), ['count' => $group->users->count()]) }}</span>
                        <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                                @click="$store.dialog.confirm({ title: @js(__('app.Supprimer le groupe').' « '.$group->name.' »'), message: @js(__('app.Les membres perdront l\'accès aux fichiers de ce groupe.')), confirm: @js(__('app.Supprimer')), tone: 'danger' }).then(ok => ok && $wire.delete({{ $group->id }}))">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-line pt-4">
                        @foreach ($group->users as $user)
                            <span wire:key="member-{{ $group->id }}-{{ $user->id }}" class="group/member inline-flex h-8 items-center gap-2 rounded-full border border-line bg-surface-2 pr-1 pl-1 text-sm text-ink">
                                <x-ui.avatar :user="$user" class="size-6 text-[10px]" />
                                {{ $user->name }}
                                <button type="button" wire:click="removeUser({{ $group->id }}, {{ $user->id }})" class="grid size-6 place-items-center rounded-full text-ink-3 hover:bg-danger/15 hover:text-danger" title="{{ __('app.Retirer') }}"><i class="ri-close-line"></i></button>
                            </span>
                        @endforeach

                        @if ($activeGroupId === $group->id)
                            <div class="relative w-full sm:w-72">
                                <i class="ri-user-search-line pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-ink-3"></i>
                                <input wire:model.live.debounce.300ms="userSearch" autofocus type="text" class="input h-9 pl-9" placeholder="{{ __('app.Ajouter un membre...') }}" @keydown.escape="$wire.setActiveGroup(null)">
                                @if (! empty($searchResults))
                                    <div class="absolute z-30 mt-1.5 w-full overflow-hidden rounded-xl border border-line-strong bg-elevated p-1 shadow-2xl">
                                        @php $candidates = collect($searchResults)->reject(fn ($result) => $group->users->contains('id', $result->id)); @endphp
                                        @forelse ($candidates as $result)
                                            <button type="button" wire:click="addUser({{ $group->id }}, {{ $result->id }})" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left hover:bg-surface-2">
                                                <x-ui.avatar :user="$result" class="size-7 text-[10px]" />
                                                <span class="min-w-0"><span class="block truncate text-sm text-ink">{{ $result->name }}</span><span class="block truncate font-mono text-[11px] text-ink-3">{{ $result->email }}</span></span>
                                            </button>
                                        @empty
                                            <p class="px-3 py-2 text-xs text-ink-3">{{ __('app.Aucun utilisateur trouvé ou déjà membre.') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @else
                            <button type="button" wire:click="setActiveGroup({{ $group->id }})" class="inline-flex h-8 items-center gap-1.5 rounded-full border border-dashed border-line-strong px-3 text-sm text-ink-3 transition hover:border-ink-3 hover:text-ink">
                                <i class="ri-add-line"></i>{{ __('app.Ajouter un membre...') }}
                            </button>
                        @endif
                    </div>
                </section>
            @empty
                <x-ui.empty icon="ri-team-line" :title="__('app.Aucun groupe créé.')" :text="__('app.Créez un premier groupe avec le formulaire.')" />
            @endforelse
            <div class="pt-2">{{ $groups->links('pagination.silo') }}</div>
        </div>
    </div>
</div>
