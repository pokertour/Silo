<?php

namespace App\Support;

use App\Models\IsoFile;
use App\Models\User;

/**
 * Entrées de navigation partagées par la barre latérale, le tiroir mobile
 * et la palette de commandes.
 */
class Navigation
{
    /**
     * @return array<string, list<array{label: string, route: string, icon: string, active: string}>>
     */
    public static function sections(?User $user): array
    {
        $sections = [
            'workspace' => [
                ['label' => __('app.Tableau de Bord'), 'route' => 'dashboard', 'icon' => 'ri-home-5-line', 'active' => 'dashboard'],
                ['label' => __('app.Bibliothèque'), 'route' => 'isos', 'icon' => 'ri-disc-line', 'active' => 'isos*'],
                ['label' => __('app.Téléverser'), 'route' => 'upload', 'icon' => 'ri-upload-cloud-2-line', 'active' => 'upload'],
                ['label' => __('app.Mes Partages'), 'route' => 'share-links', 'icon' => 'ri-links-line', 'active' => 'share-links'],
            ],
        ];

        if ($user?->hasRole('admin')) {
            $sections['admin'] = [
                ['label' => __('app.Vue d\'ensemble'), 'route' => 'admin.stats', 'icon' => 'ri-pulse-line', 'active' => 'admin.stats'],
                ['label' => __('app.Utilisateurs'), 'route' => 'admin.users', 'icon' => 'ri-user-3-line', 'active' => 'admin.users'],
                ['label' => __('app.Groupes'), 'route' => 'admin.groups', 'icon' => 'ri-team-line', 'active' => 'admin.groups'],
                ['label' => __('app.Invitations'), 'route' => 'admin.invitations', 'icon' => 'ri-mail-send-line', 'active' => 'admin.invitations'],
                ['label' => __('app.Tous les partages'), 'route' => 'admin.share-links', 'icon' => 'ri-share-forward-line', 'active' => 'admin.share-links'],
                ['label' => __('app.Configuration'), 'route' => 'admin.settings', 'icon' => 'ri-settings-4-line', 'active' => 'admin.settings'],
                ['label' => __('app.Journal d\'audit'), 'route' => 'admin.audit', 'icon' => 'ri-history-line', 'active' => 'admin.audit'],
            ];
        }

        return $sections;
    }

    /**
     * Entrées de la palette de commandes (navigation + profil).
     *
     * @return list<array{label: string, url: string, icon: string, group: string}>
     */
    public static function paletteItems(?User $user): array
    {
        $groups = [
            'workspace' => __('app.Espace de travail'),
            'admin' => __('app.Administration'),
        ];

        $items = [];
        foreach (self::sections($user) as $section => $entries) {
            foreach ($entries as $entry) {
                $items[] = [
                    'label' => $entry['label'],
                    'url' => route($entry['route']),
                    'icon' => $entry['icon'],
                    'group' => $groups[$section],
                ];
            }
        }

        $items[] = ['label' => __('app.Mon Profil'), 'url' => route('profile'), 'icon' => 'ri-user-settings-line', 'group' => __('app.Compte')];

        return $items;
    }

    /**
     * Volume des fichiers accessibles à l'utilisateur (jauge de la barre latérale).
     *
     * @return array{count: int, size: int}
     */
    public static function libraryUsage(User $user): array
    {
        $query = IsoFile::query();

        if (! $user->hasRole('admin')) {
            $groupIds = $user->groups->pluck('id');
            $query->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereNull('group_id')
                ->orWhereIn('group_id', $groupIds));
        }

        return ['count' => (clone $query)->count(), 'size' => (int) (clone $query)->sum('size')];
    }
}
