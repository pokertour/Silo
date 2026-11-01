<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Groupe de paramètres système stocké sous une clé unique, dont certains
 * champs (secrets) sont chiffrés avec APP_KEY.
 */
abstract class EncryptedSettings
{
    /** Clé dans system_settings. */
    abstract protected static function key(): string;

    /** @return array<string, mixed> */
    abstract protected static function defaults(): array;

    /** @return list<string> Champs chiffrés. */
    protected static function secrets(): array
    {
        return [];
    }

    /** @return array<string, mixed> Paramètres enregistrés (secrets toujours chiffrés). */
    public static function stored(): array
    {
        $value = SystemSetting::get(static::key());

        return array_merge(static::defaults(), is_array($value) ? array_intersect_key($value, static::defaults()) : []);
    }

    public static function enabled(): bool
    {
        return (bool) (static::stored()['enabled'] ?? false);
    }

    public static function hasSecret(string $field): bool
    {
        return ! empty(static::stored()[$field]);
    }

    /** Secret déchiffré, ou null s'il est absent ou illisible (APP_KEY changée). */
    public static function secret(string $field): ?string
    {
        $value = static::stored()[$field] ?? null;

        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Enregistre les paramètres. Un secret vide (ou absent) conserve la valeur
     * actuelle, sauf s'il figure dans $clear.
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, string|null>  $secrets
     * @param  list<string>  $clear
     */
    public static function save(array $values, array $secrets = [], array $clear = []): void
    {
        $current = static::stored();
        $settings = array_merge($current, array_intersect_key($values, static::defaults()));

        foreach (static::defaults() as $field => $default) {
            if (is_bool($default)) {
                $settings[$field] = (bool) $settings[$field];
            } elseif (is_int($default)) {
                $settings[$field] = (int) $settings[$field];
            }
        }

        foreach (static::secrets() as $field) {
            if (in_array($field, $clear, true)) {
                $settings[$field] = null;
            } elseif (($secrets[$field] ?? '') !== '') {
                $settings[$field] = Crypt::encryptString($secrets[$field]);
            } else {
                $settings[$field] = $current[$field];
            }
        }

        SystemSetting::set(static::key(), $settings);
    }
}
