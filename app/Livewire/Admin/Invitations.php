<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesAdmin;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Invitations extends Component
{
    use AuthorizesAdmin, WithPagination;

    public $email = '';

    public $expires_in_days = 7;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function generate()
    {
        $this->validate([
            'email' => 'required|email|unique:users,email',
            'expires_in_days' => 'required|integer|min:1|max:365',
        ]);

        $invitation = Invitation::create([
            'email' => $this->email,
            // 24 caractères aléatoires (~124 bits) : non devinable par force brute
            'token' => 'ISO-'.strtoupper(Str::random(24)),
            'expires_at' => now()->addDays($this->expires_in_days),
        ]);

        Mail::to($this->email)->send(new InvitationEmail($invitation));

        $this->email = '';

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Invitation envoyée avec succès.')
        );
    }

    public function revoke($id)
    {
        $invitation = Invitation::find($id);
        if ($invitation && is_null($invitation->used_at)) {
            $invitation->delete();
            $this->dispatch('toastMagic',
                status: 'info',
                title: __('app.Info'),
                message: __('app.Invitation révoquée.')
            );
        }
    }

    public function render()
    {
        $query = Invitation::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%'.$this->search.'%')
                    ->orWhere('token', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.admin.invitations', [
            'invitations' => $query->latest()->paginate(10),
        ]);
    }
}
