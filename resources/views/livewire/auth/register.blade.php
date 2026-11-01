<div class="relative w-full max-w-[460px] animate-rise">

    <div class="card p-7 sm:p-9">
        <x-ui.mark :size="56" class="mb-6" />
        <p class="eyebrow mb-3">{{ __('app.Invitation') }}</p>
        <h1 class="font-display text-3xl font-semibold text-ink">{{ __('app.Créer un compte') }}</h1>
        <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Vous avez été invité à rejoindre cet espace.') }}</p>

        <form wire:submit="register" class="mt-7 space-y-4">
            <div>
                <label for="invitation_token" class="label">{{ __('app.Jeton d\'invitation') }}</label>
                <input wire:model="invitation_token" type="text" id="invitation_token" class="input input-mono" placeholder="ISO-XXXXXXXXXXXXXXXX" spellcheck="false">
                @error('invitation_token') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">{{ __('app.Nom complet') }}</label>
                    <input wire:model="name" type="text" id="name" autocomplete="name" class="input" placeholder="{{ __('app.Prénom Nom') }}">
                    @error('name') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('app.Email') }}</label>
                    <input wire:model="email" type="email" id="email" autocomplete="email" class="input" placeholder="{{ __('app.votre@email.com') }}">
                    @error('email') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="label">{{ __('app.Mot de passe') }}</label>
                    <input wire:model="password" type="password" id="password" autocomplete="new-password" class="input" placeholder="••••••••">
                </div>
                <div>
                    <label for="password_confirmation" class="label">{{ __('app.Confirmation') }}</label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password" class="input" placeholder="••••••••">
                </div>
            </div>
            @error('password') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror

            <button type="submit" class="btn btn-primary btn-lg mt-2 w-full" wire:loading.attr="disabled" wire:target="register">
                <span wire:loading.remove wire:target="register" class="inline-flex items-center gap-2">{{ __('app.S\'inscrire') }}<i class="ri-arrow-right-line"></i></span>
                <i wire:loading wire:target="register" class="ri-loader-4-line animate-spin"></i>
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-ink-2">
        {{ __('app.Déjà un compte ?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-accent-ink hover:underline">{{ __('app.Connexion') }}</a>
    </p>
</div>
