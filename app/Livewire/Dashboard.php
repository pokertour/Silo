<?php

namespace App\Livewire;

use App\Models\Group;
use App\Models\IsoFile;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public $filter = 'all';

    public $totalSize = 0;

    public $totalFiles = 0;

    public $publicFilesCount = 0;

    public $userGroupsCount = 0;

    public $recentUploads;

    public $viewMode = 'grid';

    public function mount()
    {
        $this->viewMode = session()->get('dashboard_view_mode', 'grid');
        $this->updateStats();
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
        session()->put('dashboard_view_mode', $this->viewMode);
    }

    public function updatedFilter()
    {
        // When filter changes, we might want to do something, but render() will just pick it up.
    }

    private function getBaseQuery()
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return IsoFile::with(['user', 'group', 'tags']);
        }

        // Même règle d'accès que IsoList : ses fichiers, les publics,
        // et ceux de ses groupes — dans une closure pour que d'éventuelles
        // conditions ultérieures (filtres) ne court-circuitent pas le orWhere.
        $group_ids = $user->groups->pluck('id');

        return IsoFile::with(['user', 'group', 'tags'])
            ->where(function ($q) use ($user, $group_ids) {
                $q->where('user_id', $user->id)
                    ->orWhereNull('group_id')
                    ->orWhereIn('group_id', $group_ids);
            });
    }

    private function updateStats()
    {
        // Agrégats en SQL plutôt que de charger tous les fichiers en mémoire
        $base = $this->getBaseQuery();
        $this->totalFiles = (clone $base)->count();
        $this->totalSize = (clone $base)->sum('size');
        $this->publicFilesCount = (clone $base)->whereNull('group_id')->count();

        if (! auth()->user()->hasRole('admin')) {
            $this->userGroupsCount = auth()->user()->groups()->count();
        } else {
            $this->userGroupsCount = Group::count();
        }

        $this->recentUploads = (clone $base)->latest()->take(5)->get();
    }

    #[On('isoDeleted')]
    public function handleIsoDeleted()
    {
        $this->updateStats();
    }

    public function render()
    {
        $query = $this->getBaseQuery();

        if ($this->filter === 'public') {
            $query->whereNull('group_id');
        } elseif ($this->filter !== 'all') {
            // Assume filter is a group ID
            $query->where('group_id', $this->filter);
        }

        $isoFiles = $query->latest()->take(6)->get();

        $groups = auth()->user()->hasRole('admin') ? Group::orderBy('name')->get() : auth()->user()->groups;

        return view('livewire.dashboard', [
            'isoFiles' => $isoFiles,
            'groups' => $groups,
        ]);
    }
}
