<div x-data="{ open: false }" class="contents">
    <button type="button" @click="open = true" class="icon-btn relative" aria-label="{{ __('app.Notifications') }}">
        <i class="ri-notification-3-line"></i>
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 grid h-4 min-w-4 place-items-center rounded-full bg-accent px-1 font-mono text-[9.5px] font-bold text-on-accent ring-2 ring-surface">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @teleport('body')
        <div x-show="open" x-cloak class="fixed inset-0 z-[60]" @keydown.escape.window="open = false">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
            <aside x-show="open" x-trap.noscroll="open"
                   x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full"
                   x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="translate-x-full"
                   class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-bg-2">
                <header class="flex items-center gap-3 border-b border-line px-5 py-4">
                    <div class="flex-1">
                        <p class="eyebrow">{{ __('app.Boîte de réception') }}</p>
                        <h2 class="font-display text-xl font-semibold text-ink">{{ __('app.Notifications') }}</h2>
                    </div>
                    @if ($unreadCount > 0)
                        <button type="button" wire:click="markAllAsRead" class="btn btn-ghost btn-sm"><i class="ri-check-double-line"></i>{{ __('app.Tout lire') }}</button>
                    @endif
                    <button type="button" class="icon-btn" @click="open = false" aria-label="{{ __('app.Fermer') }}"><i class="ri-close-line"></i></button>
                </header>

                <div class="flex-1 overflow-y-auto p-3">
                    @forelse ($notifications as $notification)
                        @php
                            $icon = match ($notification->data['icon'] ?? '') {
                                'users' => 'ri-team-line',
                                'download' => 'ri-download-2-line',
                                'alert', 'warning' => 'ri-alert-line',
                                default => 'ri-notification-badge-line',
                            };
                        @endphp
                        <div wire:key="notification-{{ $notification->id }}" class="group flex gap-3 rounded-2xl p-3 transition hover:bg-surface">
                            <span @class([
                                'grid size-10 shrink-0 place-items-center rounded-xl text-lg',
                                'bg-accent-soft text-accent-ink' => ! $notification->read_at,
                                'bg-surface-2 text-ink-3' => $notification->read_at,
                            ])><i class="{{ $icon }}"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm leading-snug {{ $notification->read_at ? 'text-ink-2' : 'text-ink' }}">{{ $notification->data['message'] ?? '' }}</p>
                                <p class="mt-1 font-mono text-[11px] text-ink-3">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @unless ($notification->read_at)
                                <button type="button" wire:click="markAsRead(@js($notification->id))" class="mt-1 size-2.5 shrink-0 rounded-full bg-accent ring-4 ring-accent-soft" title="{{ __('app.Marquer comme lu') }}"></button>
                            @endunless
                        </div>
                    @empty
                        <x-ui.empty icon="ri-notification-off-line" :title="__('app.Rien de neuf')" :text="__('app.Aucune notification pour le moment.')" class="mt-6 border-none" />
                    @endforelse
                </div>

                @if ($notifications->isNotEmpty())
                    <footer class="border-t border-line p-3">
                        <button type="button"
                                @click="$store.dialog.confirm({ title: @js(__('app.Tout effacer')), message: @js(__('app.Toutes vos notifications seront supprimées.')), confirm: @js(__('app.Tout effacer')), tone: 'danger' }).then(ok => ok && $wire.clearAll())"
                                class="btn btn-ghost w-full text-ink-3"><i class="ri-delete-bin-6-line"></i>{{ __('app.Tout effacer') }}</button>
                    </footer>
                @endif
            </aside>
        </div>
    @endteleport
</div>
