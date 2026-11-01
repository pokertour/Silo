<?php

namespace App\Livewire;

use App\Jobs\CalculateIsoChecksum;
use App\Models\IsoFile;
use App\Models\ShareLink;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.app')]
class IsoDetail extends Component
{
    public IsoFile $isoFile;

    public function mount(IsoFile $isoFile)
    {
        if (auth()->user()->cannot('view', $isoFile)) {
            abort(403);
        }

        $this->isoFile = $isoFile->load(['user', 'group', 'tags', 'shareLinks.user']);
    }

    #[On('isoUpdated')]
    public function refreshIso()
    {
        $this->isoFile->refresh()->load(['user', 'group', 'tags', 'shareLinks.user']);
    }

    #[On('isoDeleted')]
    public function handleDeleted()
    {
        return $this->redirect(route('isos'), navigate: true);
    }

    public function recalculateChecksum()
    {
        if (auth()->user()->cannot('update', $this->isoFile)) {
            abort(403);
        }

        $this->isoFile->update(['checksum' => null, 'checksum_verified' => null]);
        CalculateIsoChecksum::dispatch($this->isoFile);

        $this->dispatch('toastMagic',
            status: 'info',
            title: __('app.Vérification lancée'),
            message: __('app.Le checksum est en cours de recalcul.')
        );
    }

    public function deleteShareLink(int $shareLinkId)
    {
        $link = ShareLink::where('iso_file_id', $this->isoFile->id)->findOrFail($shareLinkId);

        if (auth()->user()->cannot('share', $this->isoFile)) {
            abort(403);
        }

        $link->delete();
        $this->isoFile->load('shareLinks.user');

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Lien de partage supprimé.')
        );
    }

    public function render()
    {
        $duplicates = $this->isoFile->checksum
            ? IsoFile::where('checksum', $this->isoFile->checksum)
                ->whereKeyNot($this->isoFile->id)
                ->with('user')
                ->get()
                // Ne pas révéler l'existence de fichiers appartenant à des groupes inaccessibles
                ->filter(fn (IsoFile $duplicate) => auth()->user()->can('view', $duplicate))
            : collect();

        $activities = Activity::forSubject($this->isoFile)
            ->with('causer')
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.iso-detail', [
            'duplicates' => $duplicates,
            'activities' => $activities,
        ]);
    }
}
