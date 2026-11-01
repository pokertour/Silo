<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Users extends Component
{
    use AuthorizesAdmin, WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleAdmin($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->hasRole('admin')) {
            // Empêcher de retirer l'admin principal si c'est le seul
            if (User::role('admin')->count() > 1 && $user->id !== auth()->id()) {
                $user->removeRole('admin');
                $this->dispatch('toastMagic',
                    status: 'info',
                    title: __('app.Info'),
                    message: __('app.Rôle administrateur retiré.')
                );
            } else {
                $this->dispatch('toastMagic',
                    status: 'error',
                    title: __('app.Erreur'),
                    message: __('app.Impossible de retirer le dernier administrateur ou vous-même.')
                );
            }
        } else {
            $user->assignRole('admin');
            $this->dispatch('toastMagic',
                status: 'success',
                title: __('app.Succès'),
                message: __('app.Rôle administrateur ajouté.')
            );
        }
    }

    #[On('userDeleted')]
    public function handleUserDeleted()
    {
        // Auto updating via Livewire re-render
    }

    public function render()
    {
        $query = User::with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.admin.users', [
            'users' => $query->latest()->paginate(10),
        ]);
    }
}
