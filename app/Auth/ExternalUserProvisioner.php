<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Associe une identité externe (LDAP, OIDC) à un compte Silo : par
 * identifiant externe, puis par adresse e-mail ; le crée si autorisé.
 */
class ExternalUserProvisioner
{
    /**
     * @param  bool|null  $isAdmin  null = pas de synchronisation du rôle
     */
    public function resolve(string $provider, string $externalId, string $email, ?string $name, bool $autoCreate, ?bool $isAdmin = null): User
    {
        $email = Str::lower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ExternalAuthException(__('app.Votre compte ne fournit pas d\'adresse e-mail valide.'));
        }

        return DB::transaction(function () use ($provider, $externalId, $email, $name, $autoCreate, $isAdmin) {
            $user = User::where('auth_provider', $provider)->where('external_id', $externalId)->first()
                ?? User::where('email', $email)->first();

            if (! $user) {
                if (! $autoCreate) {
                    throw new ExternalAuthException(__('app.Aucun compte Silo n\'est associé à :email. Demandez une invitation à un administrateur.', ['email' => $email]));
                }

                $user = User::create([
                    'name' => Str::limit(trim((string) $name) ?: Str::before($email, '@'), 250, ''),
                    'email' => $email,
                    // Mot de passe local inutilisable : l'authentification passe par le fournisseur
                    'password' => Str::random(64),
                    'auth_provider' => $provider,
                    'external_id' => $externalId,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            } elseif ($user->auth_provider === $provider && $user->external_id !== $externalId) {
                // Compte du même fournisseur retrouvé par e-mail : on met à jour l'identifiant
                $user->forceFill(['external_id' => $externalId])->save();
            }

            $this->syncAdminRole($user, $provider, $isAdmin);

            return $user;
        });
    }

    /**
     * Accorde le rôle admin aux membres du groupe configuré. Il n'est retiré
     * qu'aux comptes créés par ce fournisseur : un administrateur local qui se
     * connecte par SSO ne perd jamais ses droits.
     */
    private function syncAdminRole(User $user, string $provider, ?bool $isAdmin): void
    {
        if ($isAdmin === null) {
            return;
        }

        if ($isAdmin && ! $user->hasRole('admin')) {
            Role::findOrCreate('admin');
            $user->assignRole('admin');
        } elseif (! $isAdmin && $user->auth_provider === $provider && $user->hasRole('admin')) {
            $user->removeRole('admin');
        }
    }
}
