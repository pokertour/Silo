<?php

namespace App\Jobs;

use App\Models\IsoFile;
use App\Notifications\ChecksumMismatch;
use App\Notifications\DuplicateIsoDetected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class CalculateIsoChecksum implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(public IsoFile $isoFile)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Hash en streaming : fonctionne avec tous les disques flysystem
        // (local, S3, FTP, SFTP), contrairement à ->path() + hash_file().
        $disk = Storage::disk($this->isoFile->disk);

        if (! $disk->exists($this->isoFile->file_path)) {
            return;
        }

        $stream = $disk->readStream($this->isoFile->file_path);
        if (! $stream) {
            return;
        }

        $context = hash_init('sha256');
        hash_update_stream($context, $stream);
        fclose($stream);

        $checksum = hash_final($context);

        // Comparaison avec l'empreinte fournie à l'upload, le cas échéant
        $verified = $this->isoFile->expected_checksum
            ? hash_equals(strtolower($this->isoFile->expected_checksum), $checksum)
            : null;

        $this->isoFile->update([
            'checksum' => $checksum,
            'checksum_verified' => $verified,
        ]);

        if ($verified === false && $this->isoFile->user) {
            $this->isoFile->user->notify(new ChecksumMismatch($this->isoFile));
        }

        // Détection de doublons : signale à l'uploadeur qu'un fichier
        // identique existe déjà (même empreinte SHA-256).
        $uploader = $this->isoFile->user;

        // Seuls les doublons que l'uploadeur a le droit de voir sont signalés,
        // pour ne pas divulguer le nom de fichiers d'autres groupes.
        $duplicate = $uploader
            ? IsoFile::where('checksum', $checksum)
                ->whereKeyNot($this->isoFile->id)
                ->get()
                ->first(fn (IsoFile $candidate) => $uploader->can('view', $candidate))
            : null;

        if ($duplicate) {
            $this->isoFile->user->notify(
                new DuplicateIsoDetected($this->isoFile, $duplicate)
            );
        }
    }
}
