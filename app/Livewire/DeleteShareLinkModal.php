<?php

namespace App\Livewire;

use App\Models\ShareLink;
use LivewireUI\Modal\ModalComponent;

class DeleteShareLinkModal extends ModalComponent
{
    public $linkId;

    public string $isoName = '';

    public function mount($linkId)
    {
        $link = ShareLink::with('isoFile')->findOrFail($linkId);

        // Authorization check
        if (! auth()->user()->hasRole('admin') && $link->user_id !== auth()->id()) {
            abort(403);
        }

        $this->linkId = $link->id;
        $this->isoName = $link->isoFile?->name ?? '—';
    }

    public function deleteLink()
    {
        $link = ShareLink::findOrFail($this->linkId);

        if (! auth()->user()->hasRole('admin') && $link->user_id !== auth()->id()) {
            abort(403);
        }

        $link->delete();

        $this->closeModalWithEvents([
            ShareLinks::class => 'shareLinkDeleted',
            Admin\ShareLinks::class => 'shareLinkDeleted',
        ]);
    }

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function render()
    {
        return view('livewire.delete-share-link-modal');
    }
}
