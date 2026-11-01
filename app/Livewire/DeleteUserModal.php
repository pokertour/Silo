<?php

namespace App\Livewire;

use App\Livewire\Admin\Users;
use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LivewireUI\Modal\ModalComponent;

class DeleteUserModal extends ModalComponent
{
    // Les modales peuvent être ouvertes par n'importe quel utilisateur connecté
    // via l'événement « openModal » : l'autorisation doit être faite ici.
    use AuthorizesAdmin;

    public $userId;

    public $userName;

    public $userEmail;

    public function mount($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->userId);

        if ($user->id === auth()->id()) {
            $this->dispatch('toastMagic',
                status: 'error',
                title: __('app.Erreur'),
                message: __('app.Vous ne pouvez pas vous supprimer vous-même.')
            );
            $this->closeModal();

            return;
        }

        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            $this->dispatch('toastMagic',
                status: 'error',
                title: __('app.Erreur'),
                message: __('app.Impossible de retirer le dernier administrateur ou vous-même.')
            );
            $this->closeModal();

            return;
        }

        DB::transaction(function () use ($user) {
            // Les ISOs sont transférés à l'administrateur qui supprime le compte :
            // la contrainte cascadeOnDelete supprimerait sinon les enregistrements
            // en laissant les fichiers orphelins sur le stockage.
            $user->isoFiles()->update(['user_id' => auth()->id()]);
            $user->delete();
        });

        $this->closeModalWithEvents([
            Users::class => 'userDeleted',
        ]);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Utilisateur supprimé avec succès.')
        );
    }

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function render()
    {
        return view('livewire.delete-user-modal');
    }
}
