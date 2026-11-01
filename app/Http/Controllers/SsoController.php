<?php

namespace App\Http\Controllers;

use App\Auth\ExternalAuthException;
use App\Auth\ExternalUserProvisioner;
use App\Auth\Oidc\OidcClient;
use App\Livewire\Auth\Login;
use App\Support\OidcSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    public function redirect(OidcClient $client): RedirectResponse
    {
        abort_unless(OidcSettings::enabled(), 404);

        try {
            return redirect()->away($client->authorizationUrl());
        } catch (\Throwable $e) {
            report($e);

            return $this->fail(__('app.Impossible de joindre le fournisseur d\'identité.'));
        }
    }

    public function callback(Request $request, OidcClient $client, ExternalUserProvisioner $provisioner): RedirectResponse
    {
        abort_unless(OidcSettings::enabled(), 404);

        try {
            $identity = $client->handleCallback($request->query());
            $settings = $client->settings();

            if ($identity['email_verified'] === false) {
                throw new ExternalAuthException(__('app.Votre adresse e-mail n\'est pas vérifiée chez le fournisseur d\'identité.'));
            }

            $email = Str::lower((string) $identity['email']);
            $domains = collect(explode(',', Str::lower($settings['allowed_domains'])))->map(fn ($d) => trim($d, " \t@"))->filter();
            if ($domains->isNotEmpty() && ! $domains->contains(Str::after($email, '@'))) {
                throw new ExternalAuthException(__('app.Les comptes de ce domaine ne sont pas autorisés.'));
            }

            $isAdmin = null;
            if ($settings['admin_claim'] !== '' && $settings['admin_value'] !== '') {
                $value = data_get($identity['claims'], $settings['admin_claim']);
                $isAdmin = in_array($settings['admin_value'], array_map('strval', array_filter((array) $value, 'is_scalar')), true);
            }

            $user = $provisioner->resolve('oidc', $identity['sub'], $email, $identity['name'], $settings['auto_create'], $isAdmin);
        } catch (ExternalAuthException $e) {
            return $this->fail($e->getMessage());
        }

        // La double authentification de Silo reste exigée si l'utilisateur l'a activée
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            Login::startTwoFactorChallenge($user);

            return redirect()->route('login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->with('sso_error', $message);
    }
}
