<?php

namespace App\Support;

/** Connexion via un annuaire LDAP / Active Directory (Admin → Configuration → Authentification). */
class LdapSettings extends EncryptedSettings
{
    public const DEFAULT_FILTER = '(&(objectClass=person)(|(uid={login})(mail={login})(sAMAccountName={login})))';

    protected static function key(): string
    {
        return 'auth.ldap';
    }

    protected static function defaults(): array
    {
        return [
            'enabled' => false,
            'host' => '',
            'port' => 389,
            'encryption' => 'starttls', // none, starttls, ssl
            'verify_certificate' => true,
            'base_dn' => '',
            'bind_dn' => '',
            'bind_password' => null, // chiffré
            'user_filter' => self::DEFAULT_FILTER,
            'email_attribute' => 'mail',
            'name_attribute' => 'cn',
            'auto_create' => false,
            'admin_group' => '', // DN d'un groupe (attribut memberOf) donnant le rôle admin
        ];
    }

    protected static function secrets(): array
    {
        return ['bind_password'];
    }

    public static function enabled(): bool
    {
        $settings = static::stored();

        return $settings['enabled'] && $settings['host'] !== '' && $settings['base_dn'] !== '';
    }
}
