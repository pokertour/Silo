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
    session()->flash('avatar_message', __('app.Avatar mis à jour !'));
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

    session()->flash('info_message', __('app.Informations mises à jour !'));
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
    session()->flash('password_message', __('app.Mot de passe modifié !'));
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
    session()->flash('2fa_message', __('app.Authentification à deux facteurs activée !'));
};

$disable2FA = function (\Laravel\Fortify\Actions\DisableTwoFactorAuthentication $disable) {
    // Annuler une activation non confirmée ne nécessite pas de mot de passe
    if (auth()->user()->two_factor_confirmed_at) {
        $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
        $this->reset('password_for_2fa');
    }

    $disable(auth()->user());
    $this->showRecoveryCodes = false;
    session()->flash('2fa_message', __('app.Authentification à deux facteurs désactivée.'));
};

$regenerate2FA = function (\Laravel\Fortify\Actions\GenerateNewRecoveryCodes $generate) {
    $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
    $this->reset('password_for_2fa');

    $generate(auth()->user());
    $this->showRecoveryCodes = true;
    session()->flash('2fa_message', __('app.Codes de récupération régénérés.'));
};

$revealRecoveryCodes = function () {
    $this->validate(['password_for_2fa' => ['required', 'string', 'current_password']]);
    $this->reset('password_for_2fa');

    $this->showRecoveryCodes = true;
};

?>

<div class="p-6 lg:p-10 space-y-10 max-w-4xl mx-auto">
    <header>
        <h2 class="text-3xl font-bold">{{ __('app.Mon Profil') }}</h2>
        <p class="text-slate-500 mt-1">{{ __('app.Gérez vos informations personnelles et la sécurité de votre compte.') }}
        </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Avatar -->
        <div class="md:col-span-2 glass p-8 rounded-3xl">
            <h3 class="text-xl font-bold flex items-center gap-2 mb-6">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M3 21v-2a7 7 0 0 1 14 0v2"/></svg>
                {{ __('app.Photo de profil') }}
            </h3>

            @if (session()->has('avatar_message'))
                <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-2 rounded-xl text-sm mb-4">
                    {{ session('avatar_message') }}
                </div>
            @endif

            <div class="flex items-center gap-6 flex-wrap">
                <div class="relative shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-24 h-24 rounded-full object-cover ring-4 ring-indigo-500/30" alt="avatar">
                    @else
                        <div class="w-24 h-24 rounded-full bg-linear-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-3xl font-bold ring-4 ring-indigo-500/20">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label for="avatar-upload" class="block text-sm font-medium text-slate-300 mb-2">{{ __('app.Changer l\'avatar') }}</label>
                    <div class="flex items-center gap-3">
                        <label for="avatar-upload" class="cursor-pointer bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl border border-slate-700 text-sm flex items-center gap-2 transition-all">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            {{ __('app.Choisir une image') }}
                        </label>
                        <input id="avatar-upload" wire:model="avatarFile" type="file" accept="image/*" class="hidden">
                        @if($avatarFile)
                        <button wire:click="updateAvatar" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                            {{ __('app.Enregistrer') }}
                        </button>
                        @endif
                    </div>
                    @if($avatarFile)
                    <div class="mt-3">
                        <img src="{{ $avatarFile->temporaryUrl() }}" class="w-16 h-16 rounded-xl object-cover border border-indigo-500/30" alt="preview">
                    </div>
                    @endif
                    @error('avatarFile') <span class="text-red-400 text-xs block mt-2">{{ $message }}</span> @enderror
                    <p class="text-slate-500 text-xs mt-2">{{ __('app.PNG, JPG ou GIF — max 2 Mo') }}</p>
                </div>
            </div>
        </div>
        <div class="glass p-8 rounded-3xl space-y-6">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                {{ __('app.Informations Personnelles') }}
            </h3>

            @if (session()->has('info_message'))
                <div
                    class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-2 rounded-xl text-sm">
                    {{ session('info_message') }}
                </div>
            @endif

            <form wire:submit="updateInfo" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Nom complet') }}</label>
                    <input wire:model="name" type="text"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('name')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Email') }}</label>
                    <input wire:model.live.debounce.500ms="email" type="email"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('email')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                @if (strtolower(trim($email)) !== auth()->user()->email)
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe actuel') }}</label>
                    <input wire:model="password_for_email" type="password" autocomplete="current-password"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('password_for_email')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                @endif
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-6 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    {{ __('app.Mettre à jour') }}
                </button>
            </form>
        </div>

        <!-- Password Change -->
        <div class="glass p-8 rounded-3xl space-y-6">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                {{ __('app.Sécurité') }}
            </h3>

            @if (session()->has('password_message'))
                <div
                    class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-2 rounded-xl text-sm">
                    {{ session('password_message') }}
                </div>
            @endif

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label
                        class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe actuel') }}</label>
                    <input wire:model="current_password" type="password"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('current_password')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label
                        class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Nouveau mot de passe') }}</label>
                    <input wire:model="password" type="password"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('password')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Confirmation') }}</label>
                    <input wire:model="password_confirmation" type="password"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                </div>
                <button type="submit"
                    class="bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2 px-6 rounded-xl transition-all">
                    {{ __('app.Changer le mot de passe') }}
                </button>
            </form>
        </div>

        <!-- 2FA Section -->
        <div class="glass p-8 rounded-3xl space-y-6">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                {{ __('app.Double Authentification (2FA)') }}
            </h3>

            @if (session()->has('2fa_message'))
                <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 px-4 py-2 rounded-xl text-sm">
                    {{ session('2fa_message') }}
                </div>
            @endif

            @if(!auth()->user()->two_factor_secret)
                <p class="text-sm text-slate-400">{{ __('app.Ajoutez une sécurité supplémentaire à votre compte en utilisant l\'authentification à deux facteurs.') }}</p>
                <button wire:click="enable2FA" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-6 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    {{ __('app.Activer 2FA') }}
                </button>
            @elseif(!auth()->user()->two_factor_confirmed_at)
                <p class="text-sm text-slate-400">{{ __('app.Pour finaliser l\'activation, scannez ce QR code avec votre application Authenticator (Google Authenticator, Authy, etc.) et entrez le code généré.') }}</p>
                
                <div class="bg-white p-4 rounded-xl inline-block mt-4">
                    {!! auth()->user()->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-6 max-w-sm">
                    <input wire:model="code_2fa" type="text" placeholder="{{ __('app.Code à 6 chiffres') }}" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-white text-center tracking-[0.5em] font-mono text-xl focus:ring-2 focus:ring-indigo-500/50 outline-none mb-4 uppercase placeholder:tracking-normal placeholder:text-sm placeholder:text-slate-600">
                    <div class="flex gap-3">
                        <button wire:click="confirm2FA" class="w-1/2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition-all">
                            {{ __('app.Confirmer') }}
                        </button>
                        <button wire:click="disable2FA" class="w-1/2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition-all">
                            {{ __('app.Annuler') }}
                        </button>
                    </div>
                </div>
            @else
                <p class="text-sm text-emerald-400 font-medium mb-4 flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    {{ __('app.L\'authentification à deux facteurs est activée.') }}
                </p>
                
                <p class="text-sm text-slate-400 mt-4">{{ __('app.Conservez ces codes de récupération dans un endroit sûr. Ils vous permettront d\'accéder à votre compte si vous perdez votre appareil.') }}</p>
                @if ($showRecoveryCodes)
                <div class="bg-slate-900/50 border border-slate-700/50 rounded-xl p-4 mt-2 grid grid-cols-2 gap-2 font-mono text-sm text-slate-300">
                    @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
                @endif

                <div class="mt-6 max-w-sm">
                    <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe actuel') }}</label>
                    <input wire:model="password_for_2fa" type="password" autocomplete="current-password"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500/50 outline-none">
                    @error('password_for_2fa')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mt-4 flex gap-3 flex-wrap">
                    @unless ($showRecoveryCodes)
                    <button wire:click="revealRecoveryCodes" class="bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2 px-6 rounded-xl transition-all">
                        {{ __('app.Afficher les codes') }}
                    </button>
                    @endunless
                    <button wire:click="regenerate2FA" class="bg-slate-800 hover:bg-slate-700 text-white font-semibold py-2 px-6 rounded-xl transition-all">
                        {{ __('app.Régénérer les codes') }}
                    </button>
                    <button wire:click="disable2FA" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 font-semibold py-2 px-6 rounded-xl transition-all">
                        {{ __('app.Désactiver 2FA') }}
                    </button>
                </div>
            @endif
        </div>

        <!-- Language Selector -->
        <div class="glass p-8 rounded-3xl space-y-4">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path
                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                    </path>
                </svg>
                {{ __('app.Langue') }}
            </h3>
            <p class="text-slate-500 text-sm">{{ __('app.Sélectionnez votre langue') }}</p>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('lang.switch', 'fr') }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all border
                          {{ app()->getLocale() === 'fr' ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10' }}">
                    <span>🇫🇷</span> {{ __('app.Français') }}
                    @if (app()->getLocale() === 'fr')
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    @endif
                </a>
                <a href="{{ route('lang.switch', 'en') }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all border
                          {{ app()->getLocale() === 'en' ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10' }}">
                    <span>🇬🇧</span> {{ __('app.Anglais') }}
                    @if (app()->getLocale() === 'en')
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    @endif
                </a>
                <a href="{{ route('lang.switch', 'es') }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all border
                          {{ app()->getLocale() === 'es' ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10' }}">
                    <span>🇪🇸</span> {{ __('app.Espagnol') }}
                    @if (app()->getLocale() === 'es')
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    @endif
                </a>
                <a href="{{ route('lang.switch', 'de') }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all border
                          {{ app()->getLocale() === 'de' ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10' }}">
                    <span>🇩🇪</span> {{ __('app.Allemand') }}
                    @if (app()->getLocale() === 'de')
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    @endif
                </a>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="md:col-span-2 bg-red-500/5 border border-red-500/10 p-8 rounded-3xl mt-6">
            <h3 class="text-xl font-bold text-red-400 flex items-center gap-2 mb-4">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                    </path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                {{ __('app.Zone de Danger') }}
            </h3>
            <p class="text-slate-400 text-sm mb-6">
                {{ __("app.La suppression de votre compte est irréversible. Vos fichiers ISO seront réassignés à l'administrateur principal de la plateforme.") }}
            </p>
            <div class="max-w-sm mb-4">
                <label class="block text-sm font-medium mb-1 text-slate-300">{{ __('app.Mot de passe actuel') }}</label>
                <input wire:model="password_for_delete" type="password" autocomplete="current-password"
                    class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-2 focus:ring-2 focus:ring-red-500/50 outline-none">
                @error('password_for_delete')
                    <span class="text-red-400 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <button wire:click="deleteAccount"
                wire:confirm="{{ __('app.Êtes-vous absolument sûr de vouloir supprimer votre compte ? Cette action est définitive.') }}"
                class="bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white border border-red-500/20 px-6 py-2.5 rounded-xl font-semibold transition-all">
                {{ __('app.Supprimer mon compte') }}
            </button>
        </div>
    </div>
</div>
