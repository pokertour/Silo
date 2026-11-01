<?php

namespace App\Livewire;

use App\Models\IsoFile;
use Illuminate\Support\Facades\Storage;
use LivewireUI\Modal\ModalComponent;

class DeleteIsoModal extends ModalComponent
{
    public $isoId;

    public $isoName;

    public function mount($isoId)
    {
        $iso = IsoFile::findOrFail($isoId);

        if (auth()->user()->cannot('delete', $iso)) {
            abort(403);
        }

        $this->isoId = $iso->id;
        $this->isoName = $iso->name;
    }

    public function deleteIso()
    {
        $iso = IsoFile::findOrFail($this->isoId);

        if (auth()->user()->cannot('delete', $iso)) {
            abort(403, 'Unauthorized action.');
        }

        if ($iso->file_path) {
            Storage::disk($iso->disk)->delete($iso->file_path);
        }

        $iso->delete();

        $this->closeModalWithEvents([
            Dashboard::class => 'isoDeleted',
            IsoList::class => 'isoDeleted',
        ]);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Fichier ISO supprimé avec succès.')
        );
    }

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function render()
    {
        return view('livewire.delete-iso-modal');
    }
}
