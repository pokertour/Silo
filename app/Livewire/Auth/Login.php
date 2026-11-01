<?php

namespace App\Livewire\Auth;

use App\Auth\ExternalAuthException;
use App\Auth\Ldap\LdapAuthenticator;
use App\Models\User;
use App\Support\LdapSettings;
use App\Support\OidcSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    /**
     * Clé de session contenant l'utilisateur ayant validé son mot de passe
     * et devant encore fournir son code 2FA. Stockée côté serveur : une
     * propriété Livewire publique serait modifiable par le client.
     */
    private const PENDING_2FA_KEY = 'login.2fa_pending';

    private const PENDING_2FA_TTL = 300; // secondes

    public $email = '';

    public $password = '';

    #[Locked]
    public $requiresTwoFactor = false;

    public $twoFactorCode = '';

    public function mount()
    {
        $this->requiresTwoFactor = $this->pendingTwoFactorUser() !== null;
    }

    public function login(TwoFactorAuthenticationProvider $provider)
    {
        if ($this->requiresTwoFactor) {
            return $this->verifyTwoFactor($provider);
        }

        $ldap = LdapSettings::enabled();

        $credentials = $this->validate([
            // Avec LDAP, l'identifiant peut être un nom d'utilisateur
            'email' => $ldap ? ['required', 'string', 'max:255'] : ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Anti-bruteforce : 5 tentatives par minute par email + IP
        $throttleKey = 'login|'.strtolower($credentials['email']).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $user = User::where('email', strtolower($credentials['email']))->first();
        $authenticated = $user && $user->auth_provider === null && Hash::check($credentials['password'], $user->password);

        // Comptes de l'annuaire, ou identifiants inconnus localement : on interroge LDAP
        if (! $authenticated && $ldap && (! $user || $user->auth_provider !== 'oidc')) {
            try {
                $ldapUser = app(LdapAuthenticator::class)->attempt($credentials['email'], $credentials['password']);
            } catch (ExternalAuthException $e) {
                $this->addError('email', $e->getMessage());

                return;
            }

            if ($ldapUser) {
                $user = $ldapUser;
                $authenticated = true;
            }
        }

        if ($authenticated) {
            RateLimiter::clear($throttleKey);

            // Ne challenger que si la 2FA a été confirmée (config confirm => true),
            // sinon un utilisateur ayant abandonné l'activation serait bloqué.
            if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
                self::startTwoFactorChallenge($user);
                $this->requiresTwoFactor = true;
                $this->password = '';

                return;
            }

            return $this->completeLogin($user);
        }

        $this->addError('email', __('app.Les identifiants fournis ne correspondent pas à nos enregistrements.'));
    }

    /** Mémorise l'utilisateur devant encore fournir son code 2FA (aussi utilisé par le SSO). */
    public static function startTwoFactorChallenge(User $user): void
    {
        session()->put(self::PENDING_2FA_KEY, [
            'id' => $user->id,
            'expires_at' => now()->addSeconds(self::PENDING_2FA_TTL)->getTimestamp(),
        ]);
    }

    private function verifyTwoFactor(TwoFactorAuthenticationProvider $provider)
    {
        $user = $this->pendingTwoFactorUser();

        if (! $user) {
            $this->cancelTwoFactor();
            $this->addError('email', __('app.Erreur lors de la connexion. Veuillez réessayer.'));

            return;
        }

        $this->validate([
            'twoFactorCode' => ['required', 'string', 'max:64'],
        ]);

        // Limitation par compte (et non par email saisi / IP) : empêche de
        // brute-forcer le code TOTP en faisant varier ces valeurs.
        $throttleKey = 'login-2fa|'.$user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('twoFactorCode', __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $code = trim($this->twoFactorCode);
        $valid = $provider->verify(decrypt($user->two_factor_secret), $code);

        if (! $valid && $user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
            $match = collect($recoveryCodes)->first(fn ($recoveryCode) => hash_equals((string) $recoveryCode, $code));

            if ($match !== null) {
                $user->replaceRecoveryCode($match);
                $valid = true;
            }
        }

        if (! $valid) {
            $this->twoFactorCode = '';
            $this->addError('twoFactorCode', __('app.Le code 2FA est invalide.'));

            return;
        }

        RateLimiter::clear($throttleKey);

        return $this->completeLogin($user);
    }

    public function cancelTwoFactor()
    {
        session()->forget(self::PENDING_2FA_KEY);
        $this->requiresTwoFactor = false;
        $this->twoFactorCode = '';
    }

    private function pendingTwoFactorUser(): ?User
    {
        $pending = session()->get(self::PENDING_2FA_KEY);

        if (! is_array($pending) || ($pending['expires_at'] ?? 0) < now()->getTimestamp()) {
            return null;
        }

        return User::find($pending['id'] ?? null);
    }

    private function completeLogin(User $user)
    {
        session()->forget(self::PENDING_2FA_KEY);

        Auth::login($user);
        session()->regenerate();

        return $this->redirectIntended(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login', [
            'ldapEnabled' => LdapSettings::enabled(),
            'ssoEnabled' => OidcSettings::enabled(),
            'ssoLabel' => OidcSettings::label(),
        ]);
    }
}
