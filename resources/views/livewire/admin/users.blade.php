<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Utilisateurs')"
                      :subtitle="trans_choice('app.:count membre sur la plateforme|:count membres sur la plateforme', $users->total(), ['count' => $users->total()])">
        <x-slot:actions>
            <label class="relative w-full sm:w-64">
                <i class="ri-search-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                <input wire:model.live.debounce.300ms="search" type="search" class="input pl-10" placeholder="{{ __('app.Nom ou email…') }}">
            </label>
            <a href="{{ route('admin.invitations') }}" wire:navigate class="btn btn-primary"><i class="ri-user-add-line"></i>{{ __('app.Inviter') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('app.Membre') }}</th>
                        <th>{{ __('app.Rôle') }}</th>
                        <th>{{ __('app.Statut') }}</th>
                        <th class="hidden md:table-cell">{{ __('app.Dernière activité') }}</th>
                        <th class="text-right">{{ __('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php $isOnline = $user->last_activity_at && $user->last_activity_at->diffInMinutes(now()) < 15; @endphp
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="relative">
                                        <x-ui.avatar :user="$user" class="size-9 text-xs" />
                                        @if ($isOnline)<span class="absolute -right-0.5 -bottom-0.5 size-3 rounded-full bg-ok ring-2 ring-surface"></span>@endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink">{{ $user->name }}@if ($user->id === auth()->id()) <span class="text-ink-3">· {{ __('app.vous') }}</span>@endif</p>
                                        <p class="truncate font-mono text-[11.5px] text-ink-3">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($user->hasRole('admin'))
                                    <span class="badge badge-accent"><i class="ri-shield-star-line"></i>Admin</span>
                                @else
                                    <span class="badge">{{ __('app.Membre') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($isOnline)
                                    <span class="badge badge-ok"><span class="size-1.5 rounded-full bg-ok"></span>{{ __('app.En ligne') }}</span>
                                @elseif ($user->last_activity_at)
                                    <span class="text-sm text-ink-2">{{ $user->last_activity_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-sm text-ink-3">{{ __('app.Jamais') }}</span>
                                @endif
                            </td>
                            <td class="hidden font-mono text-xs text-ink-3 md:table-cell">{{ $user->last_activity_at?->translatedFormat('j M Y, H:i') ?? '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @if ($user->id !== auth()->id())
                                        <button type="button" class="btn btn-ghost btn-sm"
                                                @click="$store.dialog.confirm({
                                                    title: @js($user->hasRole('admin') ? __('app.Retirer Admin') : __('app.Rendre Admin')),
                                                    message: @js($user->name.' · '.$user->email),
                                                    confirm: @js($user->hasRole('admin') ? __('app.Retirer Admin') : __('app.Rendre Admin')),
                                                    icon: 'ri-shield-star-line',
                                                    tone: @js($user->hasRole('admin') ? 'danger' : 'default'),
                                                }).then(ok => ok && $wire.toggleAdmin({{ $user->id }}))">
                                            <i class="{{ $user->hasRole('admin') ? 'ri-shield-cross-line' : 'ri-shield-star-line' }}"></i>
                                            <span class="hidden lg:inline">{{ $user->hasRole('admin') ? __('app.Retirer Admin') : __('app.Rendre Admin') }}</span>
                                        </button>
                                        <button type="button" class="icon-btn danger" title="{{ __('app.Supprimer') }}"
                                                @click.prevent="$dispatch('openModal', { component: 'delete-user-modal', arguments: { userId: {{ $user->id }} } })"><i class="ri-delete-bin-6-line"></i></button>
                                    @else
                                        <span class="spec">{{ __('app.Vous (Admin)') }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $users->links('pagination.silo') }}</div>
</div>
