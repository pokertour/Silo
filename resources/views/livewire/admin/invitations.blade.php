<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Administration')" :title="__('app.Invitations')" :subtitle="__('app.Générez des jetons d\'accès pour inviter de nouveaux utilisateurs.')" />

    <section class="card relative overflow-hidden p-5 sm:p-6">
        <div class="pointer-events-none absolute -top-10 -right-10 opacity-60"><x-ui.disc size="xl" os="mac" class="animate-spin-slow" /></div>
        <div class="relative max-w-2xl">
            <h2 class="panel-title mb-4"><i class="ri-mail-send-line text-accent-ink"></i>{{ __('app.Nouvelle invitation') }}</h2>
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <i class="ri-at-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
                    <input wire:model="email" type="email" class="input pl-10" placeholder="{{ __('app.Adresse email') }}" wire:keydown.enter="generate">
                </div>
                <div class="relative sm:w-36">
                    <input wire:model="expires_in_days" type="number" min="1" max="365" class="input input-mono pr-14" title="{{ __('app.Jours de validité') }}">
                    <span class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-xs text-ink-3">{{ __('app.jours') }}</span>
                </div>
                <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" class="btn btn-primary">
                    <i class="ri-send-plane-line" wire:loading.remove wire:target="generate"></i><i class="ri-loader-4-line animate-spin" wire:loading wire:target="generate"></i>{{ __('app.Envoyer') }}
                </button>
            </div>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
            @error('expires_in_days') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </section>

    <div class="mt-8 mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-display text-xl font-semibold text-ink">{{ __('app.Historique') }} <span class="font-mono text-sm font-normal text-ink-3">{{ $invitations->total() }}</span></h2>
        <label class="relative w-full sm:w-64">
            <i class="ri-search-line pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-ink-3"></i>
            <input wire:model.live.debounce.300ms="search" type="search" class="input h-10 pl-10" placeholder="{{ __('app.Email ou jeton…') }}">
        </label>
    </div>

    @if ($invitations->isEmpty())
        <x-ui.empty icon="ri-mail-open-line" :title="__('app.Aucune invitation.')" :text="__('Générez un nouveau jeton pour permettre à un utilisateur de créer un compte.')" />
    @else
        <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($invitations as $invitation)
                @php
                    $link = route('register', ['token' => $invitation->token, 'email' => $invitation->email]);
                    $state = $invitation->used_at ? 'used' : ($invitation->expires_at < now() ? 'expired' : 'valid');
                @endphp
                <article wire:key="invitation-{{ $invitation->id }}" x-data="copyable(@js($link))" class="card flex flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink">{{ $invitation->email ?: '—' }}</p>
                            <p class="mt-1 font-mono text-[11px] break-all text-ink-3">{{ $invitation->token }}</p>
                        </div>
                        @if ($state === 'used')
                            <span class="badge badge-ok"><i class="ri-user-follow-line"></i>{{ __('app.Utilisée') }}</span>
                        @elseif ($state === 'expired')
                            <span class="badge">{{ __('app.Expirée') }}</span>
                        @else
                            <span class="badge badge-accent">{{ __('app.Valide') }}</span>
                        @endif
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4">
                        <div><dt class="spec">{{ $state === 'used' ? __('app.Enregistré le') : __('app.Expiration') }}</dt>
                            <dd class="mt-1 font-mono text-xs text-ink-2">{{ ($state === 'used' ? $invitation->used_at : $invitation->expires_at)->translatedFormat('j M Y, H:i') }}</dd></div>
                        <div><dt class="spec">{{ __('app.Créée') }}</dt><dd class="mt-1 font-mono text-xs text-ink-2">{{ $invitation->created_at->diffForHumans() }}</dd></div>
                    </dl>
                    @if ($state === 'valid')
                        <div class="mt-4 flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm flex-1" @click="copy()"><i :class="copied ? 'ri-check-line text-ok' : 'ri-link'"></i><span x-text="copied ? @js(__('app.Lien copié !')) : @js(__('app.Copier le lien'))"></span></button>
                            <button type="button" class="btn btn-ghost btn-sm text-danger"
                                    @click="$store.dialog.confirm({ title: @js(__('app.Révoquer l\'invitation ?')), message: @js($invitation->email ?? $invitation->token), confirm: @js(__('app.Révoquer')), tone: 'danger', icon: 'ri-mail-close-line' }).then(ok => ok && $wire.revoke({{ $invitation->id }}))">{{ __('app.Révoquer') }}</button>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $invitations->links('pagination.silo') }}</div>
    @endif
</div>
