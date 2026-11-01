<?php

namespace App\Auth\Ldap;

use App\Auth\ExternalAuthException;
use App\Auth\ExternalUserProvisioner;
use App\Models\User;
use App\Support\LdapSettings;

class LdapAuthenticator
{
    public function __construct(
        private LdapClient $client,
        private ExternalUserProvisioner $provisioner,
    ) {}

    /** Paramètres complets, mot de passe du compte de service déchiffré. */
    public static function settings(): array
    {
        return array_merge(LdapSettings::stored(), ['bind_password' => LdapSettings::secret('bind_password')]);
    }

    /**
     * @return User|null null si l'annuaire refuse les identifiants
     *
     * @throws ExternalAuthException
     */
    public function attempt(string $login, string $password): ?User
    {
        if (! LdapSettings::enabled()) {
            return null;
        }

        $settings = self::settings();

        try {
            $entry = $this->client->authenticate($settings, trim($login), $password);
        } catch (\RuntimeException $e) {
            report($e);

            throw new ExternalAuthException(__('app.L\'annuaire LDAP est injoignable. Réessayez plus tard.'));
        }

        if ($entry === null) {
            return null;
        }

        $isAdmin = null;
        if ($settings['admin_group'] !== '') {
            $wanted = strtolower($settings['admin_group']);
            $isAdmin = collect($entry['groups'])->contains(fn ($group) => strtolower($group) === $wanted);
        }

        return $this->provisioner->resolve(
            'ldap',
            strtolower($entry['dn']),
            (string) ($entry['email'] ?? ''),
            $entry['name'],
            $settings['auto_create'],
            $isAdmin,
        );
    }
}
