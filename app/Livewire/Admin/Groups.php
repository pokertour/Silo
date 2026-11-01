<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\Group;
use App\Models\User;
use App\Notifications\UserAddedToGroup;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Groups extends Component
{
    use AuthorizesAdmin, WithPagination;

    public $name = '';

    public $description = '';

    public $search = '';

    public $userSearch = '';

    public $activeGroupId = null;

    public function setActiveGroup($id)
    {
        $this->activeGroupId = $id;
        $this->userSearch = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|unique:groups,name|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Group::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'description']);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Groupe créé avec succès.')
        );
    }

    public function addUser($groupId, $userId)
    {
        $group = Group::find($groupId);
        $user = User::find($userId);

        if ($group && $user) {
            $group->users()->syncWithoutDetaching([$userId]);
            $this->userSearch = '';

            $user->notify(new UserAddedToGroup($group));

            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Succès'),
                message: __('app.Utilisateur ajouté au groupe.')
            );
        }
    }

    public function removeUser($groupId, $userId)
    {
        $group = Group::find($groupId);
        if ($group) {
            $group->users()->detach($userId);

            $this->dispatch('toastMagic',
                status: 'info',
                title: __('app.Info'),
                message: __('app.Utilisateur retiré du groupe.')
            );
        }
    }

    public function delete($groupId)
    {
        $group = Group::find($groupId);
        if ($group) {
            // La contrainte nullOnDelete rendrait les fichiers du groupe
            // visibles par tous les utilisateurs : on refuse la suppression
            // tant que des ISOs y sont rattachés.
            if ($group->isoFiles()->exists()) {
                $this->dispatch('toastMagic',
                    status: 'error',
                    title: __('app.Erreur'),
                    message: __('app.Ce groupe contient encore des fichiers ISO : réaffectez-les avant de le supprimer.')
                );

                return;
            }

            $group->delete();
            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Succès'),
                message: __('app.Groupe supprimé.')
            );
        }
    }

    public function render()
    {
        $query = Group::with('users');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        $searchResults = [];
        if (strlen($this->userSearch) >= 2) {
            $searchResults = User::where('name', 'like', '%'.$this->userSearch.'%')
                ->orWhere('email', 'like', '%'.$this->userSearch.'%')
                ->limit(5)
                ->get();
        }

        return view('livewire.admin.groups', [
            'groups' => $query->latest()->paginate(10),
            'searchResults' => $searchResults,
        ]);
    }
}
