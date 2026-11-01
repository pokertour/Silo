<x-layouts.guest :title="__('app.Lien Protégé')">
    <div class="w-full max-w-[420px] animate-rise">
        <div class="card p-7 text-center sm:p-9">
            <span class="mx-auto mb-6 grid size-16 place-items-center rounded-3xl bg-accent-soft text-3xl text-accent-ink"><i class="ri-lock-password-line"></i></span>
            <h1 class="font-display text-2xl font-semibold text-ink">{{ __('app.Lien Protégé') }}</h1>
            <p class="mt-1.5 text-sm text-ink-2">{{ __('app.Ce lien de téléchargement est protégé par un mot de passe.') }}</p>
            <form action="{{ route('share.verify', $share->token) }}" method="POST" class="mt-7 space-y-4 text-left">
                @csrf
                <div>
                    <label for="password" class="label">{{ __('app.Mot de passe') }}</label>
                    <input type="password" name="password" id="password" class="input" placeholder="••••••••" required autofocus>
                    @error('password') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-full">{{ __('app.Accéder au fichier') }}<i class="ri-arrow-right-line"></i></button>
            </form>
        </div>
    </div>
</x-layouts.guest>
