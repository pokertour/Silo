<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\IsoFile;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class IsoList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $filter = 'all'; // all, public, ou un ID de groupe

    #[Url(as: 'tag', except: '')]
    public $selectedTag = '';

    #[Url(as: 'sort', except: 'recent')]
    public $sortBy = 'recent'; // recent, oldest, name, size

    public array $selected = [];

    public $bulkGroupId = '';

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function deleteSelected()
    {
        $deleted = 0;
        $skipped = 0;

        foreach (IsoFile::whereIn('id', $this->selected)->get() as $iso) {
            if (auth()->user()->cannot('delete', $iso)) {
                $skipped++;

                continue;
            }

            if ($iso->file_path) {
                Storage::disk($iso->disk)->delete($iso->file_path);
            }
            $iso->delete();
            $deleted++;
        }

        $this->selected = [];
        $this->resetPage();

        $this->dispatch('toastMagic',
            status: $skipped ? 'warning' : 'success',
            title: __('app.Succès'),
            message: trans_choice('app.:count fichier(s) supprimé(s)', $deleted, ['count' => $deleted])
                .($skipped ? ' — '.trans_choice('app.:count ignoré(s) (non autorisé)', $skipped, ['count' => $skipped]) : '')
        );
    }

    public function assignGroupSelected()
    {
        $user = auth()->user();

        // Un non-admin ne peut cibler qu'un de ses groupes (ou « public »)
        if (! empty($this->bulkGroupId)
            && ! $user->hasRole('admin')
            && ! $user->groups->contains('id', (int) $this->bulkGroupId)) {
            $this->addError('bulkGroupId', __("app.Vous n'appartenez pas à ce groupe."));

            return;
        }

        $updated = 0;
        $skipped = 0;

        foreach (IsoFile::whereIn('id', $this->selected)->get() as $iso) {
            if ($user->cannot('update', $iso)) {
                $skipped++;

                continue;
            }

            $iso->update(['group_id' => empty($this->bulkGroupId) ? null : (int) $this->bulkGroupId]);
            $updated++;
        }

        $this->selected = [];
        $this->bulkGroupId = '';

        $this->dispatch('toastMagic',
            status: $skipped ? 'warning' : 'success',
            title: __('app.Succès'),
            message: trans_choice('app.:count fichier(s) mis à jour', $updated, ['count' => $updated])
                .($skipped ? ' — '.trans_choice('app.:count ignoré(s) (non autorisé)', $skipped, ['count' => $skipped]) : '')
        );
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter', 'selectedTag']);
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selected = [];
    }

    public function updatingSelectedTag()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    #[On('isoDeleted')]
    #[On('isoUpdated')]
    public function handleIsoChanged()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = IsoFile::with(['user', 'group', 'tags']);

        // Appliquer la recherche (un hash hexadécimal cherche par checksum exact)
        if (! empty($this->search)) {
            if (preg_match('/^[a-fA-F0-9]{40,64}$/', trim($this->search))) {
                $query->where('checksum', strtolower(trim($this->search)));
            } else {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('os_type', 'like', '%'.$this->search.'%');
                });
            }
        }

        // Appliquer les droits d'accès
        if (! auth()->user()->hasRole('admin')) {
            $userGroupsList = auth()->user()->groups->pluck('id')->toArray();
            $query->where(function ($q) use ($userGroupsList) {
                $q->where('user_id', auth()->id()) // Ses propres ISOs
                    ->orWhereNull('group_id') // ISOs Publics
                    ->orWhereIn('group_id', $userGroupsList); // ISOs de ses groupes
            });
        }

        // Appliquer le filtre (Tous, Public, Groupe)
        if ($this->filter === 'public') {
            $query->whereNull('group_id');
        } elseif ($this->filter !== 'all') {
            $query->where('group_id', $this->filter);
        }

        // Appliquer le tag filter
        if (! empty($this->selectedTag)) {
            $query->whereHas('tags', function ($q) {
                $q->where('tags.id', $this->selectedTag);
            });
        }

        match ($this->sortBy) {
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            'size' => $query->orderByDesc('size'),
            default => $query->latest(),
        };

        // Empreintes présentes plus d'une fois : sert au badge « Doublon »
        $duplicateChecksums = IsoFile::whereNotNull('checksum')
            ->groupBy('checksum')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('checksum')
            ->all();

        return view('livewire.iso-list', [
            'isos' => $query->paginate(18),
            'groups' => auth()->user()->hasRole('admin') ? Group::orderBy('name')->get() : auth()->user()->groups,
            'availableTags' => Tag::orderBy('name')->get(),
            'duplicateChecksums' => $duplicateChecksums,
        ]);
    }
}
