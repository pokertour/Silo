<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\IsoFile;
use LivewireUI\Modal\ModalComponent;

class EditIsoModal extends ModalComponent
{
    public $isoId;

    public $name;

    public $version;

    public $architecture;

    public $os_type;

    public $description;

    public $group_id;

    public $groups;

    public function mount($isoId)
    {
        $iso = IsoFile::findOrFail($isoId);

        if (auth()->user()->cannot('update', $iso)) {
            abort(403);
        }

        $this->isoId = $iso->id;
        $this->name = $iso->name;
        $this->version = $iso->version;
        $this->architecture = $iso->architecture;
        $this->os_type = $iso->os_type;
        $this->description = $iso->description;
        $this->group_id = $iso->group_id;

        if (auth()->user()->hasRole('admin')) {
            $this->groups = Group::all();
        } else {
            $this->groups = auth()->user()->groups;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'architecture' => 'nullable|string|max:50',
            'os_type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $iso = IsoFile::findOrFail($this->isoId);

        if (auth()->user()->cannot('update', $iso)) {
            abort(403);
        }

        // Un non-admin ne peut affecter le fichier qu'à un de ses propres groupes.
        if (! empty($this->group_id)
            && ! auth()->user()->hasRole('admin')
            && ! auth()->user()->groups->contains('id', (int) $this->group_id)) {
            $this->addError('group_id', __("app.Vous n'appartenez pas à ce groupe."));

            return;
        }

        $iso->update([
            'name' => $this->name,
            'version' => $this->version,
            'architecture' => $this->architecture,
            'os_type' => $this->os_type,
            'description' => $this->description,
            'group_id' => empty($this->group_id) ? null : $this->group_id,
        ]);

        $this->closeModalWithEvents([
            IsoList::class => 'isoUpdated',
        ]);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.ISO mis à jour avec succès.')
        );
    }

    public static function modalMaxWidth(): string
    {
        return 'lg';
    }

    public function render()
    {
        return view('livewire.edit-iso-modal');
    }
}
