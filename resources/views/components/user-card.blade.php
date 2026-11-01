{{-- Carte utilisateur : profil, thème, notifications, déconnexion. --}}
@props(['notificationsKey' => null])
@php $user = auth()->user(); @endphp
<div class="flex items-center gap-0.5 rounded-xl p-1">
    <a href="{{ route('profile') }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-2.5 rounded-lg p-1.5 transition hover:bg-surface-2">
        <x-ui.avatar :user="$user" class="size-8 text-xs" />
        <span class="min-w-0">
            <span class="block truncate text-[13px] font-medium text-ink">{{ $user->name }}</span>
            <span class="block truncate text-xs text-ink-3">{{ $user->hasRole('admin') ? __('app.Administrateur') : __('app.Membre') }}</span>
        </span>
    </a>
    @if ($notificationsKey)
        @livewire('notifications-dropdown', [], key($notificationsKey))
    @endif
    <form method="POST" action="{{ route('logout') }}" class="contents">
        @csrf
        <button type="submit" class="icon-btn danger" title="{{ __('app.Déconnexion') }}" aria-label="{{ __('app.Déconnexion') }}"><i class="ri-logout-box-r-line"></i></button>
    </form>
</div>
