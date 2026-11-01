<?php

namespace App\Support;

/** Authentification unique OpenID Connect / OAuth 2 (Admin → Configuration → Authentification). */
class OidcSettings extends EncryptedSettings
{
    protected static function key(): string
    {
        return 'auth.oidc';
    }

    protected static function defaults(): array
    {
        return [
            'enabled' => false,
            'label' => 'SSO',
            'issuer' => '',            // découverte OpenID (.well-known/openid-configuration)
            'client_id' => '',
            'client_secret' => null,   // chiffré
            'scopes' => 'openid email profile',
            // Points de terminaison manuels (OAuth 2 sans découverte, ex. GitHub) — prioritaires
            'authorize_url' => '',
            'token_url' => '',
            'userinfo_url' => '',
            'email_claim' => 'email',
            'name_claim' => 'name',
            'auto_create' => false,
            'allowed_domains' => '',   // ex. « example.com, example.org »
            'admin_claim' => '',       // ex. « groups »
            'admin_value' => '',       // ex. « silo-admins »
        ];
    }

    protected static function secrets(): array
    {
        return ['client_secret'];
    }

    public static function enabled(): bool
    {
        $settings = static::stored();

        return $settings['enabled']
            && $settings['client_id'] !== ''
            && ($settings['issuer'] !== '' || ($settings['authorize_url'] !== '' && $settings['token_url'] !== ''));
    }

    public static function label(): string
    {
        return static::stored()['label'] ?: 'SSO';
    }
}
