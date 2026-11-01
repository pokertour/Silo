<?php

namespace App\Auth\Ldap;

interface LdapClient
{
    /**
     * Vérifie les identifiants dans l'annuaire.
     *
     * @param  array<string, mixed>  $settings  Paramètres LDAP (bind_password déchiffré)
     * @return array{dn: string, email: ?string, name: ?string, groups: list<string>}|null null si refusés
     *
     * @throws \RuntimeException si l'annuaire est injoignable ou mal configuré
     */
    public function authenticate(array $settings, string $login, string $password): ?array;

    /**
     * Teste la connexion et le compte de service.
     *
     * @return int Nombre d'utilisateurs correspondant au filtre (borné)
     *
     * @throws \RuntimeException
     */
    public function test(array $settings): int;
}
