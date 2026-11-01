<?php

namespace App\Policies;

use App\Models\IsoFile;
use App\Models\User;

class IsoFilePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    /**
     * Un fichier sans groupe est visible par tous les utilisateurs connectés,
     * sinon il faut être membre du groupe ou en être le propriétaire
     * (même règle que les listes IsoList / Dashboard).
     */
    public function view(User $user, IsoFile $isoFile): bool
    {
        return $isoFile->group_id === null
            || $isoFile->user_id === $user->id
            || $user->groups->contains('id', $isoFile->group_id);
    }

    public function download(User $user, IsoFile $isoFile): bool
    {
        return $this->view($user, $isoFile);
    }

    /**
     * Créer un lien de partage public expose le fichier hors du groupe :
     * réservé au propriétaire (les admins passent par before()).
     */
    public function share(User $user, IsoFile $isoFile): bool
    {
        return $isoFile->user_id === $user->id;
    }

    public function update(User $user, IsoFile $isoFile): bool
    {
        return $isoFile->user_id === $user->id;
    }

    public function delete(User $user, IsoFile $isoFile): bool
    {
        return $isoFile->user_id === $user->id;
    }
}
