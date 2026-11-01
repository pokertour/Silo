<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesAdmin;
use App\Models\Group;
use App\Models\IsoFile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StatsDashboard extends Component
{
    use AuthorizesAdmin;

    public function render()
    {
        // 1. Métriques de base
        $stats = [
            'total_isos' => IsoFile::count(),
            'total_users' => User::count(),
            'total_groups' => Group::count(),
            'total_size' => IsoFile::sum('size'),
        ];

        // 2. Utilisation par stockage (+ capacité réelle pour les disques locaux)
        $storageUsage = IsoFile::select('disk', DB::raw('SUM(size) as total_size'), DB::raw('COUNT(*) as count'))
            ->groupBy('disk')
            ->get()
            ->each(function ($usage) {
                $usage->capacity = null;
                $usage->free = null;
                try {
                    $root = Storage::disk($usage->disk)->path('');
                    if (is_dir($root)) {
                        $usage->free = disk_free_space($root) ?: null;
                        $usage->capacity = disk_total_space($root) ?: null;
                    }
                } catch (\Throwable) {
                    // Disque distant (S3, FTP…) : pas de notion de capacité locale
                }
            });

        // 3. Répartition par OS
        $osDistribution = IsoFile::select('os_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('os_type')
            ->groupBy('os_type')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // 4. Derniers ISOs ajoutés
        $recentIsos = IsoFile::with(['user', 'group'])->latest()->take(5)->get();

        return view('livewire.admin.stats-dashboard', [
            'stats' => $stats,
            'storageUsage' => $storageUsage,
            'osDistribution' => $osDistribution,
            'recentIsos' => $recentIsos,
        ]);
    }
}
