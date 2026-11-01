<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\WithFileUploads;

use function Livewire\Volt\{state, rules, mount, layout, uses};

layout('components.layouts.app');

uses([WithFileUploads::class]);

state([
    'name' => '',
    'email' => '',
    'current_password' => '',
    'password' => '',
    'password_confirmation' => '',
    'avatarFile' => null,
    'code_2fa' => '',
    // Mot de passe actuel exigé pour les actions sensibles : une session volée
    // (cookie, poste déverrouillé) ne doit pas suffire pour changer l'email,
    // désactiver la 2FA ou supprimer le compte.
    'password_for_email' => '',
    'password_for_2fa' => '',
    'password_for_delete' => '',
]);

// Verrouillé : le client ne doit pas pouvoir afficher les codes sans mot de passe
state(['showRecoveryCodes' => false])->locked();

mount(function () {
    $user = auth()->user();
    $this->name = $user->name;
    $this->email = $user->email;
});

$updateAvatar = function () {
    $this->validate(['avatarFile' => 'required|image|mimes:png,jpg,jpeg,gif,webp|max:2048']);

    $user = auth()->user();

    // Delete old avatar if exists
    if ($user->avatar) {
        Storage::disk('public')->delete($user->avatar);
    }

    $path = $this->avatarFile->store('avatars', 'public');
    $user->update(['avatar' => $path]);

    $this->avatarFile = null;
    $this->dispatch('toastMagic', status: 'success', title: __('app.Avatar mis à jour !'));
};

$updateInfo = function () {
    $this->email = strtolower(trim($this->email));

    $this->validate([
        'name' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
    ]);

    $user = auth()->user();

    // Changer l'email permet de prendre le contrôle du compte (réinitialisation) :
    // on exige le mot de passe actuel.
    if ($this->email !== $user->email) {
        $this->validate(['password_for_email' => ['required', 'string', 'current_password']]);
        $this->reset('password_for_email');
    }

    $user->update([
        'name' => $this->name,
        'email' => $this->email,
    ]);

    $this->dispatch('toastMagic', status: 'success', title: __('app.Informations mises à jour !'));
};

$updatePassword = function () {
    $this->validate([
        'current_password' => 'required|current_password',
        'password' => ['required', 'confirmed', Password::defaults()],
    ]);

    auth()
        ->user()
        ->update([
            'password' => Hash::make($this->password),
        ]);

    // Déconnecte les autres sessions ouvertes avec l'ancien mot de passe
    Auth::logoutOtherDevices($this->password);

    $this->reset(['current_password', 'password', 'password_confirmation']);
    $this->dispatch('toastMagic', status: 'success', title: __('app.Mot de passe modifié !'));
};

$deleteAccount = function () {
    $this->validate(['password_for_delete' => ['required', 'string', 'current_password']]);

    $user = auth()->user();

    if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
        $this->addError('password_for_delete', __('app.Impossible de retirer le dernier administrateur ou vous-même.'));

        return;
    }

    // Assign ISOs to the main admin (first admin found)
    $admin = User::role('admin')->where('id', '!=', $user->id)->first();

    if ($admin) {
        $user->isoFiles()->update(['user_id' => $admin->id]);
    }

    auth()->logout();
    session()->invalidate();
    session()->regenerateToken();
    $user->delete();

    return redirect()->to('/');
};

$enable2FA = function (\Laravel\Fortify\Actions\EnableTwoFactorAuthentication $enable) {
    $enable(auth()->user());
};

$confirm2FA = function (\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication $confirm) {
    $confirm(auth()->user(), $this->code_2fa);
    $this->code_2fa = '';
    $this->showRecoveryCodes = true;
    $this->dispatch('toastMagic', status: 'success', title: __('app.Authentification à deux facteurs activée !'));
};

$disable2FA = function (\Laravel\Fortify\Actions\DisableTwoFactorAuthentication $disable) {
    // Annuler une activation non confirmée ne nécessite pas de mot de passe
    if (auth()->user()->two_factor_confirmed_at) {
        $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
        $this->reset('password_for_2fa');
    }

    $disable(auth()->user());
    $this->showRecoveryCodes = false;
    $this->dispatch('toastMagic', status: 'success', title: __('app.Authentification à deux facteurs désactivée.'));
};

$regenerate2FA = function (\Laravel\Fortify\Actions\GenerateNewRecoveryCodes $generate) {
    $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
    $this->reset('password_for_2fa');

    $generate(auth()->user());
    $this->showRecoveryCodes = true;
    $this->dispatch('toastMagic', status: 'success', title: __('app.Codes de récupération régénérés.'));
};

$revealRecoveryCodes = function () {
    $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
    $this->reset('password_for_2fa');

    $this->showRecoveryCodes = true;
};

?>

@php
    $user = auth()->user();
    $locales = ['en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch'];
@endphp

<div class="animate-rise">
    <x-ui.page-header :eyebrow="__('app.Compte')" :title="__('app.Mon Profil')" :subtitle="__('app.Gérez vos informations personnelles et la sécurité de votre compte.')" />

    {{-- Identité --}}
    <section class="card relative overflow-hidden p-6 sm:p-8">
        <div class="pointer-events-none absolute inset-0 backdrop-dots opacity-60 [mask-image:linear-gradient(to_left,black,transparent_65%)]"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">
            <div class="relative w-fit">
                @if ($avatarFile)
                    <img src="{{ $avatarFile->temporaryUrl() }}" alt="" class="size-24 rounded-full object-cover ring-4 ring-accent-soft">
                @else
                    <x-ui.avatar :user="$user" class="size-24 text-3xl ring-4 ring-accent-soft" />
                @endif
                <label for="avatar-upload" class="absolute -right-1 -bottom-1 grid size-9 cursor-pointer place-items-center rounded-full border border-line-strong bg-elevated text-ink-2 shadow-lg transition hover:text-ink" title="{{ __('app.Changer l\'avatar') }}">
                    <i class="ri-camera-line"></i>
                </label>
                <input id="avatar-upload" wire:model="avatarFile" type="file" accept="image/png,image/jpeg,image/gif,image/webp" class="hidden">
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate font-display text-2xl font-semibold text-ink">{{ $user->name }}</h2>
                <p class="truncate font-mono text-sm text-ink-2">{{ $user->email }}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="badge {{ $user->hasRole('admin') ? 'badge-accent' : '' }}"><i class="ri-shield-user-line"></i>{{ $user->hasRole('admin') ? __('app.Administrateur') : __('app.Membre') }}</span>
                    @if ($user->two_factor_confirmed_at)
                        <span class="badge badge-ok"><i class="ri-shield-check-line"></i>2FA</span>
                    @endif
                    @foreach ($user->groups as $group)
                        <span class="badge badge-info"><i class="ri-team-line"></i>{{ $group->name }}</span>
                    @endforeach
                </div>
                @error('avatarFile') <p class="field-error"><i class="ri-error-warning-line"></i>{{ $message }}</p> @enderror
            </div>
            @if ($avatarFile)
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('avatarFile', null)" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
                    <button type="button" wire:click="updateAvatar" class="btn btn-primary"><i class="ri-check-line"></i>{{ __('app.Enregistrer') }}</button>
                </div>
            @endif
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Informations --}}
        <section class="card p-6">
            <h2 class="panel-title mb-5"><i class="ri-user-3-line text-accent-ink"></i>{{ __('app.Informations Personnelles') }}</h2>
            <form wire:submit="updateInfo" class="space-y-4">
                <div>
                    <label class="label" for="profile-name">{{ __('app.Nom complet') }}</label>
                    <input wire:model="name" id="profile-name" type="text" autocomplete="name" class="input">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="profile-email">{{ __('app.Email') }}</label>
                    <input wire:model.live.debounce.500ms="email" id="profile-email" type="email" autocomplete="email" class="input">
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                @if (strtolower(trim($email)) !== $user->email)
                    <div class="animate-rise rounded-xl border border-warn/30 bg-warn/5 p-3">
                        <label class="label" for="password_for_email">{{ __('app.Mot de passe actuel') }}</label>
                        <input wire:model="password_for_email" id="password_for_email" type="password" autocomplete="current-password" class="input">
                        @error('password_for_email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div class="flex justify-end pt-1">
                    <button type="submit" class="btn btn-primary"><i class="ri-save-3-line"></i>{{ __('app.Mettre à jour') }}</button>
                </div>
            </form>
        </section>

        {{-- Mot de passe --}}
        <section class="card p-6">
            <h2 class="panel-title mb-5"><i class="ri-key-2-line text-accent-ink"></i>{{ __('app.Mot de passe') }}</h2>
            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="label" for="current_password">{{ __('app.Mot de passe actuel') }}</label>
                    <input wire:model="current_password" id="current_password" type="password" autocomplete="current-password" class="input">
                    @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="new_password">{{ __('app.Nouveau mot de passe') }}</label>
                        <input wire:model="password" id="new_password" type="password" autocomplete="new-password" class="input">
                    </div>
                    <div>
                        <label class="label" for="password_confirmation">{{ __('app.Confirmation') }}</label>
                        <input wire:model="password_confirmation" id="password_confirmation" type="password" autocomplete="new-password" class="input">
                    </div>
                </div>
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
                <div class="flex items-center justify-between gap-3 pt-1">
                    <p class="text-xs text-ink-3">{{ __('app.Les autres sessions seront déconnectées.') }}</p>
                    <button type="submit" class="btn btn-primary"><i class="ri-lock-line"></i>{{ __('app.Changer') }}</button>
                </div>
            </form>
        </section>

        {{-- Double authentification --}}
        <section class="card p-6 lg:col-span-2">
            <div class="flex flex-col gap-6 md:flex-row">
                <div class="md:w-72 md:shrink-0">
                    <h2 class="panel-title"><i class="ri-shield-keyhole-line text-accent-ink"></i>{{ __('app.Double Authentification (2FA)') }}</h2>
                    <p class="mt-2 text-sm text-ink-2">{{ __('app.Ajoutez une sécurité supplémentaire à votre compte en utilisant l\'authentification à deux facteurs.') }}</p>
                    @if ($user->two_factor_confirmed_at)
                        <span class="badge badge-ok mt-4"><span class="size-1.5 rounded-full bg-ok"></span>{{ __('app.Activée') }}</span>
                    @elseif ($user->two_factor_secret)
                        <span class="badge badge-warn mt-4">{{ __('app.En attente de confirmation') }}</span>
                    @else
                        <span class="badge mt-4">{{ __('app.Désactivée') }}</span>
                    @endif
                </div>

                <div class="min-w-0 flex-1 md:border-l md:border-line md:pl-6">
                    @if (! $user->two_factor_secret)
                        <div class="flex h-full flex-col items-start justify-center gap-4 rounded-2xl border border-dashed border-line-strong p-6">
                            <p class="text-sm text-ink-2">{{ __('app.Utilisez une application comme Aegis, 1Password, Authy ou Google Authenticator.') }}</p>
                            <button type="button" wire:click="enable2FA" class="btn btn-primary"><i class="ri-shield-check-line"></i>{{ __('app.Activer 2FA') }}</button>
                        </div>
                    @elseif (! $user->two_factor_confirmed_at)
                        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                            <div class="w-fit shrink-0 rounded-2xl bg-white p-3 shadow-lg">{!! $user->twoFactorQrCodeSvg() !!}</div>
                            <div class="flex-1 space-y-3">
                                <p class="text-sm text-ink-2">{{ __('app.Pour finaliser l\'activation, scannez ce QR code avec votre application Authenticator (Google Authenticator, Authy, etc.) et entrez le code généré.') }}</p>
                                <input wire:model="code_2fa" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" class="input input-mono h-12 max-w-56 text-center text-lg tracking-[0.4em]">
                                @error('code') <p class="field-error">{{ $message }}</p> @enderror
                                <div class="flex gap-2">
                                    <button type="button" wire:click="confirm2FA" class="btn btn-primary"><i class="ri-check-line"></i>{{ __('app.Confirmer') }}</button>
                                    <button type="button" wire:click="disable2FA" class="btn btn-secondary">{{ __('app.Annuler') }}</button>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-ink-2">{{ __('app.Conservez ces codes de récupération dans un endroit sûr. Ils vous permettront d\'accéder à votre compte si vous perdez votre appareil.') }}</p>
                        @if ($showRecoveryCodes)
                            <div x-data="copyable(@js(implode("\n", json_decode(decrypt($user->two_factor_recovery_codes), true))))" class="mt-4 rounded-2xl border border-line bg-bg-2 p-4">
                                <div class="grid grid-cols-2 gap-x-6 gap-y-2 font-mono text-sm text-ink sm:grid-cols-4">
                                    @foreach (json_decode(decrypt($user->two_factor_recovery_codes), true) as $code)
                                        <span>{{ $code }}</span>
                                    @endforeach
                                </div>
                                <button type="button" @click="copy()" class="btn btn-ghost btn-sm mt-3 -ml-2"><i :class="copied ? 'ri-check-line text-ok' : 'ri-file-copy-line'"></i>{{ __('app.Copier') }}</button>
                            </div>
                        @endif
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="sm:w-64">
                                <label class="label" for="password_for_2fa">{{ __('app.Mot de passe actuel') }}</label>
                                <input wire:model="password_for_2fa" id="password_for_2fa" type="password" autocomplete="current-password" class="input">
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @unless ($showRecoveryCodes)
                                    <button type="button" wire:click="revealRecoveryCodes" class="btn btn-secondary"><i class="ri-eye-line"></i>{{ __('app.Afficher les codes') }}</button>
                                @endunless
                                <button type="button" wire:click="regenerate2FA" class="btn btn-secondary"><i class="ri-refresh-line"></i>{{ __('app.Régénérer les codes') }}</button>
                                <button type="button" wire:click="disable2FA" class="btn btn-danger"><i class="ri-shield-cross-line"></i>{{ __('app.Désactiver 2FA') }}</button>
                            </div>
                        </div>
                        @error('password_for_2fa') <p class="field-error">{{ $message }}</p> @enderror
                    @endif
                </div>
            </div>
        </section>

        {{-- Préférences --}}
        <section class="card p-6">
            <h2 class="panel-title mb-5"><i class="ri-equalizer-line text-accent-ink"></i>{{ __('app.Préférences') }}</h2>
            <p class="label">{{ __('app.Langue') }}</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($locales as $code => $label)
                    <a href="{{ route('lang.switch', $code) }}" @class(['flex items-center justify-between rounded-xl border px-3.5 py-2.5 text-sm transition', 'border-accent-ink bg-accent-soft text-ink' => app()->getLocale() === $code, 'border-line text-ink-2 hover:border-line-strong hover:text-ink' => app()->getLocale() !== $code])>
                        {{ $label }}<span class="font-mono text-[10px] text-ink-3 uppercase">{{ $code }}</span>
                    </a>
                @endforeach
            </div>
            <p class="label mt-6">{{ __('app.Thème') }}</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="$store.theme.current !== 'dark' && $store.theme.toggle()" class="flex items-center gap-2 rounded-xl border px-3.5 py-2.5 text-sm transition" :class="$store.theme.current === 'dark' ? 'border-accent-ink bg-accent-soft text-ink' : 'border-line text-ink-2 hover:text-ink'"><i class="ri-moon-clear-line"></i>{{ __('app.Sombre') }}</button>
                <button type="button" @click="$store.theme.current !== 'light' && $store.theme.toggle()" class="flex items-center gap-2 rounded-xl border px-3.5 py-2.5 text-sm transition" :class="$store.theme.current === 'light' ? 'border-accent-ink bg-accent-soft text-ink' : 'border-line text-ink-2 hover:text-ink'"><i class="ri-sun-line"></i>{{ __('app.Clair') }}</button>
            </div>
        </section>

        {{-- Zone de danger --}}
        <section class="card border-danger/25 p-6">
            <h2 class="panel-title mb-2 text-danger"><i class="ri-alarm-warning-line"></i>{{ __('app.Zone de Danger') }}</h2>
            <p class="text-sm text-ink-2">{{ __("app.La suppression de votre compte est irréversible. Vos fichiers ISO seront réassignés à l'administrateur principal de la plateforme.") }}</p>
            <div class="mt-5">
                <label class="label" for="password_for_delete">{{ __('app.Mot de passe actuel') }}</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input wire:model="password_for_delete" id="password_for_delete" type="password" autocomplete="current-password" class="input flex-1">
                    <button type="button" class="btn btn-danger"
                            @click="$store.dialog.confirm({ title: @js(__('app.Supprimer mon compte')), message: @js(__('app.Êtes-vous absolument sûr de vouloir supprimer votre compte ? Cette action est définitive.')), confirm: @js(__('app.Supprimer mon compte')), tone: 'danger', icon: 'ri-user-unfollow-line' }).then(ok => ok && $wire.deleteAccount())">
                        <i class="ri-delete-bin-6-line"></i>{{ __('app.Supprimer mon compte') }}
                    </button>
                </div>
                @error('password_for_delete') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </section>
    </div>
</div>
