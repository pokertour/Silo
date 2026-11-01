<?php

namespace App\Http\Controllers;

use App\Http\Concerns\StreamsFileDownloads;
use App\Models\ShareLink;
use App\Notifications\ShareLinkDownloaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ShareController extends Controller
{
    use StreamsFileDownloads;

    public function show($token)
    {
        $share = ShareLink::where('token', $token)->firstOrFail();

        if (! $share->isValid()) {
            abort(410, __('app.Ce lien de partage est expiré ou a atteint sa limite de téléchargement.'));
        }

        // If no password, show the download page
        if (! $share->password) {
            return view('share.show', compact('share'));
        }

        // If password protected, check session
        if (session("share_auth_{$share->id}")) {
            return view('share.show', compact('share'));
        }

        return view('share.password', compact('share'));
    }

    public function verify(Request $request, $token)
    {
        $share = ShareLink::where('token', $token)->firstOrFail();

        $request->validate([
            'password' => 'required',
        ]);

        if (Hash::check($request->password, $share->password)) {
            session(["share_auth_{$share->id}" => true]);

            return redirect()->route('share.show', $token);
        }

        return back()->withErrors(['password' => __('app.Mot de passe incorrect.')]);
    }

    public function download(Request $request, $token)
    {
        $share = ShareLink::where('token', $token)->firstOrFail();

        if ($share->expires_at && $share->expires_at->isPast()) {
            abort(410);
        }

        if ($share->password && ! session("share_auth_{$share->id}")) {
            abort(403);
        }

        // Une reprise (Range à partir d'un offset > 0) ne consomme pas de crédit
        // max_downloads, mais uniquement pour une session ayant déjà consommé un
        // crédit sur ce lien : sinon des requêtes Range successives permettraient
        // de télécharger le fichier indéfiniment sans jamais être décomptées.
        $sessionKey = "share_download_{$share->id}";
        $isResume = preg_match('/^bytes=([1-9]\d*)-/', (string) $request->header('Range')) === 1
            && session()->has($sessionKey);

        if (! $isResume && ! $share->isValid()) {
            abort(410);
        }

        $iso = $share->isoFile;
        $path = $iso?->file_path;
        $disk = Storage::disk($iso?->disk ?: config('filesystems.default', 'local'));

        if (! $path || ! $disk->exists($path)) {
            abort(404);
        }

        if (! $isResume) {
            // Incrément atomique : deux téléchargements simultanés ne peuvent pas
            // dépasser max_downloads (l'update conditionnel sert de verrou).
            if ($share->max_downloads) {
                $claimed = ShareLink::whereKey($share->id)
                    ->where('download_count', '<', $share->max_downloads)
                    ->increment('download_count');

                if (! $claimed) {
                    abort(410);
                }
            } else {
                $share->increment('download_count');
            }

            session()->put($sessionKey, true);

            // Informe le créateur du lien qu'un téléchargement a eu lieu
            $share->refresh();
            $share->user?->notify(new ShareLinkDownloaded($share));
        }

        $filename = $iso->name.'.'.pathinfo($path, PATHINFO_EXTENSION);

        return $this->streamFileDownload($request, $disk, $path, $filename, $iso->checksum);
    }
}
