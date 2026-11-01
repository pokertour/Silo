<div class="relative w-full max-w-[420px] animate-rise">

    <div class="card p-7 sm:p-9">
        <x-ui.mark :size="64" class="mx-auto mb-5" />
        <div class="text-center">
        @if (! $requiresTwoFactor)
            <p class="eyebrow mb-2">{{ __('app.Accès sécurisé') }}</p>
            <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Bon retour') }}</h1>
            <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Connectez-vous pour accéder à vos images disque.') }}</p>
        @else
            <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Vérification') }}</h1>
            <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Saisissez le code de votre application d\'authentification ou un code de secours.') }}</p>
        @endif

        </div>

        @if (session('sso_error'))
            <div class="mt-6 flex gap-2.5 rounded-xl bg-danger/10 px-3.5 py-3 text-sm text-danger" role="alert">
                <i class="ri-error-warning-line mt-px"></i><span>{{ session('sso_error') }}</span>
            </div>
        @endif

        @if ($ssoEnabled && ! $requiresTwoFactor)
            <a href="{{ route('sso.redirect') }}" class="btn btn-secondary btn-lg mt-7 w-full">
                <i class="ri-shield-keyhole-line"></i>{{ __('app.Continuer avec :provider', ['provider' => $ssoLabel]) }}
            </a>
            <div class="mt-6 flex items-center gap-3 text-xs text-ink-3" aria-hidden="true">
                <span class="h-px flex-1 bg-line"></span>{{ __('app.ou') }}<span class="h-px flex-1 bg-line"></span>
            </div>
        @endif

        <form wire:submit="login" @class(['space-y-5', 'mt-7' => ! $ssoEnabled || $requiresTwoFactor, 'mt-5' => $ssoEnabled && ! $requiresTwoFactor])>
            @if (! $requiresTwoFactor)
                <div>
                    <label for="email" class="label">{{ $ldapEnabled ? __('app.E-mail ou identifiant') : __('app.Email') }}</label>
                    <input wire:model="email" type="{{ $ldapEnabled ? 'text' : 'email' }}" id="email" autocomplete="username" autocapitalize="none" autofocus class="input" placeholder="{{ $ldapEnabled ? __('app.prenom.nom') : __('app.votre@email.com') }}">
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
