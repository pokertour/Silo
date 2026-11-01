<div class="relative w-full max-w-[420px] animate-rise">
    {{-- Décor : disques en orbite derrière la carte --}}
    <div class="pointer-events-none absolute -top-24 -right-28 -z-10 opacity-60 blur-[1px]" aria-hidden="true">
        <x-ui.disc size="xl" os="linux" class="animate-spin-slow" />
    </div>
    <div class="pointer-events-none absolute -bottom-16 -left-24 -z-10 opacity-40" aria-hidden="true">
        <x-ui.disc size="lg" os="windows" class="animate-spin-slow" />
    </div>

    <div class="card p-7 sm:p-9">
        @if (! $requiresTwoFactor)
            <p class="eyebrow mb-3">{{ __('app.Accès sécurisé') }}</p>
            <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Bon retour') }}</h1>
            <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Connectez-vous pour accéder à vos images disque.') }}</p>
        @else
            <span class="mb-5 grid size-12 place-items-center rounded-2xl bg-accent-soft text-2xl text-accent-ink"><i class="ri-shield-keyhole-line"></i></span>
            <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Vérification') }}</h1>
            <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Saisissez le code de votre application d\'authentification ou un code de secours.') }}</p>
        @endif

        <form wire:submit="login" class="mt-7 space-y-5">
            @if (! $requiresTwoFactor)
                <div>
                    <label for="email" class="label">{{ __('app.Email') }}</label>
                    <input wire:model="email" type="email" id="email" autocomplete="username" autofocus class="input" placeholder="{{ __('app.votre@email.com') }}">
                    @error('email') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <div x-data="{ show: false }">
                    <label for="password" class="label">{{ __('app.Mot de passe') }}</label>
                    <div class="relative">
                        <input wire:model="password" :type="show ? 'text' : 'password'" id="password" autocomplete="current-password" class="input pr-11" placeholder="••••••••">
                        <button type="button" @click="show = !show" class="icon-btn absolute top-1 right-1" :aria-label="show ? @js(__('app.Masquer')) : @js(__('app.Afficher'))">
                            <i :class="show ? 'ri-eye-off-line' : 'ri-eye-line'"></i>
                        </button>
                    </div>
                    @error('password') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label for="twoFactorCode" class="label">{{ __('app.Code 2FA ou Code de Secours') }}</label>
                    <input wire:model="twoFactorCode" type="text" id="twoFactorCode" inputmode="numeric" autocomplete="one-time-code" autofocus
                           class="input input-mono h-14 text-center text-xl tracking-[0.4em]" placeholder="000000">
                    @error('twoFactorCode') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
            @endif

            <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                    {{ $requiresTwoFactor ? __('app.Vérifier') : __('app.Se connecter') }}<i class="ri-arrow-right-line"></i>
                </span>
                <i wire:loading wire:target="login" class="ri-loader-4-line animate-spin"></i>
            </button>

            @if ($requiresTwoFactor)
                <button type="button" wire:click="cancelTwoFactor" class="btn btn-ghost w-full"><i class="ri-arrow-left-line"></i>{{ __('app.Retour') }}</button>
            @endif
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-ink-3">
        <i class="ri-lock-line"></i> {{ __('app.Inscription sur invitation uniquement.') }}
    </p>
</div>
