<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Configuration SMTP définie par l'administrateur (Admin → Configuration →
 * E-mail). Stockée dans les paramètres système, mot de passe chiffré avec
 * APP_KEY. Tant qu'elle n'est pas activée, la configuration du .env s'applique.
 */
class MailSettings
{
    private const KEY = 'mail';

    private const DEFAULTS = [
        'enabled' => false,
        'host' => '',
        'port' => 587,
        'encryption' => 'tls', // tls (STARTTLS), ssl (TLS implicite), none
        'username' => '',
        'password' => null,    // chiffré
        'from_address' => '',
        'from_name' => '',
    ];

    /** @return array<string, mixed> Paramètres enregistrés (mot de passe toujours chiffré). */
    public static function stored(): array
    {
        $value = SystemSetting::get(self::KEY);

        return array_merge(self::DEFAULTS, is_array($value) ? $value : []);
    }

    public static function hasPassword(): bool
    {
        return ! empty(self::stored()['password']);
    }

    /**
     * Enregistre les paramètres. Un mot de passe vide conserve l'actuel,
     * sauf si $clearPassword est vrai.
     *
     * @param  array<string, mixed>  $values
     */
    public static function save(array $values, ?string $password = null, bool $clearPassword = false): void
    {
        $current = self::stored();

        $settings = array_merge($current, array_intersect_key($values, self::DEFAULTS));
        $settings['port'] = (int) $settings['port'];
        $settings['enabled'] = (bool) $settings['enabled'];

        if ($clearPassword) {
            $settings['password'] = null;
        } elseif ($password !== null && $password !== '') {
            $settings['password'] = Crypt::encryptString($password);
        } else {
            $settings['password'] = $current['password'];
        }

        SystemSetting::set(self::KEY, $settings);
    }

    /** Applique la configuration enregistrée au mailer, si elle est activée. */
    public static function apply(): void
    {
        $settings = self::stored();

        if (! $settings['enabled'] || empty($settings['host'])) {
            return;
        }

        try {
            $password = $settings['password'] ? Crypt::decryptString($settings['password']) : null;
        } catch (\Throwable) {
            $password = null; // APP_KEY changée : mot de passe illisible
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $settings['encryption'] === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => $settings['port'],
            'mail.mailers.smtp.username' => $settings['username'] ?: null,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.url' => null,
        ]);

        if (! empty($settings['from_address'])) {
            config(['mail.from.address' => $settings['from_address']]);
        }
        if (! empty($settings['from_name'])) {
            config(['mail.from.name' => $settings['from_name']]);
        }

        // Le mailer a pu être instancié avant : on force sa reconstruction
        if (app()->resolved('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }
}
