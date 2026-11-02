<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{
    public function upload(Request $request)
    {
        // L'identifiant et les numéros de chunk sont injectés dans des chemins :
        // on les contraint strictement pour empêcher toute traversée de répertoire.
        $validated = $request->validate([
            'resumableIdentifier' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
            'resumableTotalChunks' => ['required', 'integer', 'min:1', 'max:100000'],
            'resumableChunkNumber' => ['required', 'integer', 'min:1', 'max:100000', 'lte:resumableTotalChunks'],
            'resumableFilename' => ['required', 'string', 'max:255'],
        ]);

        $userId = (int) $request->user()->id;
        $identifier = $validated['resumableIdentifier'];
        $chunkNumber = (int) $validated['resumableChunkNumber'];
        $totalChunks = (int) $validated['resumableTotalChunks'];
        $filename = basename($validated['resumableFilename']);

        // L'identifiant Resumable.js par défaut est prévisible (« taille-nomdufichier ») :
        // les chunks sont rangés par utilisateur pour qu'un tiers ne puisse ni
        // sonder ni injecter des morceaux dans l'upload de quelqu'un d'autre.
        $tempPath = "chunks/{$userId}/{$identifier}";
        $disk = Storage::disk('local');

        // Handles Resumable.js test GET request to check chunk existence
        if ($request->isMethod('get')) {
            $exists = $disk->exists("{$tempPath}/{$chunkNumber}.part");

            // Chunk absent : 204 et non 404. Resumable.js renvoie le chunk pour tout
            // statut autre que 200/201, et une rafale de 404 (une par chunk) est
            // prise pour un scan par les IDS comme CrowdSec (http-probing).
            return $exists ? response()->json(['status' => 'exists']) : response()->noContent();
        }

        $chunk = $request->file('file');
        if (! $chunk || ! $chunk->isValid()) {
            return response()->json(['error' => 'No file'], 400);
        }

        $chunk->storeAs($tempPath, "{$chunkNumber}.part", 'local');

        // Plusieurs chunks arrivent en parallèle : l'assemblage est sérialisé.
        $lockKey = "chunk-assemble:{$userId}:{$identifier}";
        $doneKey = "chunk-done:{$userId}:{$identifier}";
        $lock = Cache::lock($lockKey, 600);
        $lock->block(120);

        try {
            // Un autre chunk concurrent a déjà assemblé le fichier (le nôtre a été
            // consommé) : on renvoie le même résultat, Resumable.js lisant la
            // réponse du dernier chunk terminé.
            if (! $disk->exists("{$tempPath}/{$chunkNumber}.part") && ($assembled = Cache::get($doneKey))) {
                return response()->json($assembled);
            }

            for ($i = 1; $i <= $totalChunks; $i++) {
                if (! $disk->exists("{$tempPath}/{$i}.part")) {
                    return response()->json(['status' => 'chunk saved']);
                }
            }

            // Le nom temporaire est préfixé par l'utilisateur : Upload::save()
            // refuse de finaliser un fichier appartenant à quelqu'un d'autre.
            $finalTempName = 'temp_iso/'.$userId.'_'.Str::random(40);
            $disk->makeDirectory('temp_iso');
            $absoluteFinal = $disk->path($finalTempName);

            $out = fopen($absoluteFinal, 'wb');
            for ($i = 1; $i <= $totalChunks; $i++) {
                $inFile = $disk->path("{$tempPath}/{$i}.part");
                $in = fopen($inFile, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
            fclose($out);
            $disk->deleteDirectory($tempPath);

            $result = [
                'status' => 'done',
                'temp_path' => $finalTempName,
                'original_name' => $filename,
            ];
            Cache::put($doneKey, $result, now()->addMinutes(10));
        } finally {
            $lock->release();
        }

        return response()->json($result);
    }
}
