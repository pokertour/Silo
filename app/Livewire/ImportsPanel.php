<?php

namespace App\Livewire;

use App\Jobs\ImportIsoFromUrl;
use App\Models\IsoImport;
use Livewire\Component;

/**
 * Panneau « Imports en cours » : progression des imports depuis une URL,
 * annulation, relance et masquage des imports terminés.
 */
class ImportsPanel extends Component
{
    private function findOwned(int $id): IsoImport
    {
        $import = IsoImport::findOrFail($id);

        abort_unless((int) $import->user_id === (int) auth()->id() || auth()->user()->hasRole('admin'), 403);

        return $import;
    }

    public function cancel(int $id): void
    {
        $import = $this->findOwned($id);

        if ($import->isActive()) {
            // Le job vérifie ce statut pendant le téléchargement et s'interrompt
            $import->update(['status' => IsoImport::CANCELLED, 'finished_at' => now(), 'bytes_per_second' => null]);
        }
    }

    public function retry(int $id): void
    {
        $import = $this->findOwned($id);

        if (! $import->isRetryable()) {
            return;
        }

        $options = $import->options ?? [];
        $attributes = $options['attributes'] ?? [];

        $import->update([
            'status' => IsoImport::PENDING,
            'bytes_downloaded' => 0,
            'bytes_total' => null,
            'bytes_per_second' => null,
            'error' => null,
            'started_at' => null,
            'finished_at' => null,
            'dismissed_at' => null,
        ]);

        ImportIsoFromUrl::dispatch($import->url, $attributes, $options['tags'] ?? [], $import->id);
    }

    public function dismiss(int $id): void
    {
        $import = $this->findOwned($id);

        if (! $import->isActive()) {
            $import->update(['dismissed_at' => now()]);
        }
    }

    public function render()
    {
        $imports = IsoImport::query()
            ->where('user_id', auth()->id())
            ->whereNull('dismissed_at')
            ->where(fn ($q) => $q->active()->orWhere('finished_at', '>=', now()->subDay()))
            ->latest()
            ->take(6)
            ->get();

        return view('livewire.imports-panel', [
            'imports' => $imports,
            'hasActive' => $imports->contains(fn (IsoImport $import) => $import->isActive()),
        ]);
    }
}
