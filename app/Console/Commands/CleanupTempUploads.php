<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTempUploads extends Command
{
    protected $signature = 'app:cleanup-temp-uploads {--hours=24 : Âge minimum (en heures) des fichiers à supprimer}';

    protected $description = 'Supprime les chunks et fichiers temporaires d\'upload abandonnés';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $cutoff = now()->subHours((int) $this->option('hours'))->getTimestamp();
        $deleted = 0;

        // Dossiers de chunks abandonnés (un dossier par upload resumable).
        // flysystem ne fournit pas lastModified() sur un dossier : on prend
        // le chunk le plus récent comme référence.
        // Arborescence : chunks/{userId}/{identifiant}/{n}.part
        foreach ($disk->directories('chunks') as $userDir) {
            foreach ($disk->directories($userDir) as $dir) {
                $newest = collect($disk->files($dir))->map(fn ($f) => $disk->lastModified($f))->max() ?? 0;
                if ($newest < $cutoff) {
                    $disk->deleteDirectory($dir);
                    $deleted++;
                }
            }

            if ($disk->allFiles($userDir) === []) {
                $disk->deleteDirectory($userDir);
            }
        }

        // Fichiers assemblés jamais finalisés (upload abandonné avant le save())
        foreach ($disk->files('temp_iso') as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Nettoyage terminé : {$deleted} élément(s) supprimé(s).");

        return self::SUCCESS;
    }
}
