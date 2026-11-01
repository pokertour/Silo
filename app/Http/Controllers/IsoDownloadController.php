<?php

namespace App\Http\Controllers;

use App\Http\Concerns\StreamsFileDownloads;
use App\Models\IsoFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IsoDownloadController extends Controller
{
    use StreamsFileDownloads;

    public function download(Request $request, IsoFile $isoFile)
    {
        // 1. Vérification des permissions (voir App\Policies\IsoFilePolicy)
        if (auth()->user()->cannot('download', $isoFile)) {
            abort(403, "Vous n'avez pas l'autorisation de télécharger ce fichier.");
        }

        // 2. Résolution du disk de stockage
        $diskName = $isoFile->disk ?: config('filesystems.default', 'local');
        $disk = Storage::disk($diskName);

        // 3. Vérification de l'existence du fichier
        if (! $isoFile->file_path || ! $disk->exists($isoFile->file_path)) {
            abort(404, "Le fichier est introuvable sur le stockage « {$diskName} ».");
        }

        // 4. Téléchargement avec extension (reprise supportée via Range)
        $extension = pathinfo($isoFile->file_path, PATHINFO_EXTENSION);
        $filename = $isoFile->name.($extension ? '.'.$extension : '');

        return $this->streamFileDownload($request, $disk, $isoFile->file_path, $filename, $isoFile->checksum);
    }
}
