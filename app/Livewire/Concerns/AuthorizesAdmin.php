<?php

namespace App\Livewire\Concerns;

/**
 * Réserve un composant Livewire aux administrateurs.
 *
 * Le middleware de route « role:admin » ne protège que le chargement initial
 * de la page : les appels d'actions suivants (/livewire/update) et les
 * composants montés dynamiquement (ex. modales wire-elements via l'événement
 * « openModal ») doivent être contrôlés dans le composant lui-même.
 * Livewire appelle boot{NomDuTrait}() à chaque requête (montage et mises à jour).
 */
trait AuthorizesAdmin
{
    public function bootAuthorizesAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
}
