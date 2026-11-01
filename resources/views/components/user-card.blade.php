{{-- Carte utilisateur : profil, thème, notifications, déconnexion. --}}
@props(['notificationsKey' => null])
@php $user = auth()->user(); @endphp
<div class="flex items-center gap-2 rounded-2xl border border-line bg-surface p-2">
    <a href="{{ route('profile') }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-2.5 rounded-xl p-1 transition hover:bg-surface-2">
        <x-ui.avatar :user="$user" class="size-9 text-sm" />
        <span class="min-w-0">
            <span class="block truncate text-sm font-semibold text-ink">{{ $user->name }}</span>
            <span class="block truncate text-xs text-ink-3">{{ $user->hasRole('admin') ? __('app.Administrateur') : __('app.Membre') }}</span>
        </span>
    </a>
    <button type="button" class="icon-btn" @click="$store.theme.toggle()" :title="$store.theme.current === 'light' ? @js(__('app.Thème sombre')) : @js(__('app.Thème clair'))">
        <i :class="$store.theme.current === 'light' ? 'ri-moon-clear-line' : 'ri-sun-line'"></i>
    </button>
    @if ($notificationsKey)
        @livewire('notifications-dropdown', [], key($notificationsKey))
    @endif
</div>
