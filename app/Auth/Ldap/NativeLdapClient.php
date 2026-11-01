<?php

namespace App\Auth\Ldap;

use LDAP\Connection;
use RuntimeException;

/** Client LDAP basé sur l'extension PHP ldap. */
class NativeLdapClient implements LdapClient
{
    private const TIMEOUT = 5;

    public function authenticate(array $settings, string $login, string $password): ?array
    {
        // Un mot de passe vide déclencherait un bind anonyme… qui réussit.
        if ($login === '' || $password === '') {
            return null;
        }

        $connection = $this->connect($settings);

        try {
            $this->serviceBind($connection, $settings);

            $filter = str_replace('{login}', ldap_escape($login, '', LDAP_ESCAPE_FILTER), $settings['user_filter']);
            $attributes = array_values(array_filter([$settings['email_attribute'], $settings['name_attribute'], 'memberof']));

            $search = @ldap_search($connection, $settings['base_dn'], $filter, $attributes, 0, 2);
            if ($search === false) {
                throw new RuntimeException('Recherche LDAP impossible : '.ldap_error($connection));
            }

            $entries = ldap_get_entries($connection, $search);
            // Aucun ou plusieurs résultats : on refuse plutôt que de deviner
            if (($entries['count'] ?? 0) !== 1) {
                return null;
            }

            $entry = $entries[0];
            $dn = $entry['dn'];

            if (! @ldap_bind($connection, $dn, $password)) {
                return null;
            }

            return [
                'dn' => $dn,
                'email' => $this->first($entry, $settings['email_attribute']),
                'name' => $this->first($entry, $settings['name_attribute']),
                'groups' => $this->all($entry, 'memberof'),
            ];
        } finally {
            @ldap_unbind($connection);
        }
    }

    public function test(array $settings): int
    {
        $connection = $this->connect($settings);

        try {
            $this->serviceBind($connection, $settings);

            $filter = str_replace('{login}', '*', $settings['user_filter']);
            $search = @ldap_search($connection, $settings['base_dn'], $filter, ['dn'], 1, 50);
            if ($search === false) {
                throw new RuntimeException('Recherche impossible : '.ldap_error($connection));
            }

            return (int) ldap_count_entries($connection, $search);
        } finally {
            @ldap_unbind($connection);
        }
    }

    /** @return Connection */
    private function connect(array $settings)
    {
        if (! function_exists('ldap_connect')) {
            throw new RuntimeException('L\'extension PHP ldap n\'est pas installée.');
        }

        $scheme = $settings['encryption'] === 'ssl' ? 'ldaps' : 'ldap';
        $uri = sprintf('%s://%s:%d', $scheme, $settings['host'], $settings['port']);

        if (! $settings['verify_certificate']) {
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
        }

        $connection = @ldap_connect($uri);
        if ($connection === false) {
            throw new RuntimeException('Adresse du serveur LDAP invalide.');
        }

        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, self::TIMEOUT);
        ldap_set_option($connection, LDAP_OPT_TIMELIMIT, self::TIMEOUT);

        if ($settings['encryption'] === 'starttls' && ! @ldap_start_tls($connection)) {
            throw new RuntimeException('STARTTLS a échoué : '.ldap_error($connection));
        }

        return $connection;
    }

    private function serviceBind($connection, array $settings): void
    {
        $bound = $settings['bind_dn'] !== ''
            ? @ldap_bind($connection, $settings['bind_dn'], (string) $settings['bind_password'])
            : @ldap_bind($connection); // bind anonyme pour la recherche

        if (! $bound) {
            throw new RuntimeException('Connexion du compte de service refusée : '.ldap_error($connection));
        }
    }

    private function first(array $entry, string $attribute): ?string
    {
        $values = $entry[strtolower($attribute)] ?? null;

        return is_array($values) && ($values['count'] ?? 0) > 0 ? (string) $values[0] : null;
    }

    /** @return list<string> */
    private function all(array $entry, string $attribute): array
    {
        $values = $entry[strtolower($attribute)] ?? [];
        unset($values['count']);

        return array_values(array_map('strval', is_array($values) ? $values : []));
    }
}
