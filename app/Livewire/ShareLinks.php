<?php

namespace App\Livewire;

use App\Models\ShareLink;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ShareLinks extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('shareLinkDeleted')]
    public function handleShareLinkDeleted()
    {
        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Lien de partage supprimé.')
        );
    }

    public function render()
    {
        $user = auth()->user();

        $query = ShareLink::with(['isoFile', 'user']);

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('token', 'like', '%'.$this->search.'%')
                    ->orWhereHas('isoFile', function ($subQ) {
                        $subQ->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user', function ($subQ) {
                        $subQ->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        return view('livewire.share-links', [
            'links' => $query->latest()->paginate(10),
        ]);
    }
}
