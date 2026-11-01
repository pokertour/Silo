<?php

namespace App\Http\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

trait StreamsFileDownloads
{
    /**
     * Streame un fichier avec support des Range requests (RFC 7233),
     * pour permettre la reprise des téléchargements interrompus.
     */
    protected function streamFileDownload(Request $request, Filesystem $disk, string $path, string $filename, ?string $checksum = null)
    {
        $size = $disk->size($path);

        // makeDisposition refuse « / » et « \ » ; le repli ASCII ne doit contenir ni « % » ni guillemet
        $filename = str_replace(['/', '\\'], '_', preg_replace('/[\x00-\x1F\x7F]/u', '', $filename) ?? 'download');
        $fallback = Str::ascii(str_replace(['%', '"'], '_', $filename)) ?: 'download';

        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = array_filter([
            'Content-Type' => 'application/octet-stream',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'X-Checksum-Sha256' => $checksum,
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename, $fallback),
        ]);

        $range = $request->header('Range');
        if ($range && preg_match('/^bytes=(\d*)-(\d*)$/', $range, $m) && ($m[1] !== '' || $m[2] !== '')) {
            if ($m[1] === '') {
                // Suffixe : "bytes=-500" = les 500 derniers octets
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                if ($m[2] !== '') {
                    $end = min((int) $m[2], $size - 1);
                }
            }

            if ($start > $end || $start >= $size) {
                return response('', 416, ['Content-Range' => "bytes */{$size}"]);
            }

            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = $length;

        // Prévient l'arrêt du script PHP pour les fichiers volumineux
        set_time_limit(0);

        return response()->stream(function () use ($disk, $path, $start, $length) {
            // En test, PHPUnit capture la sortie via ce même buffer : ne pas y toucher.
            if (! app()->runningUnitTests() && ob_get_level()) {
                ob_end_clean();
            }

            $stream = $disk->readStream($path);
            if (! $stream) {
                return;
            }

            // Certains flux distants (FTP…) ne sont pas seekables :
            // on lit et jette jusqu'à l'offset dans ce cas.
            if ($start > 0 && fseek($stream, $start) !== 0) {
                $toSkip = $start;
                while ($toSkip > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(1048576, $toSkip));
                    if ($chunk === false) {
                        break;
                    }
                    $toSkip -= strlen($chunk);
                }
            }

            $remaining = $length;
            while ($remaining > 0 && ! feof($stream)) {
                $chunk = fread($stream, min(1048576, $remaining));
                if ($chunk === false) {
                    break;
                }
                echo $chunk;
                $remaining -= strlen($chunk);
            }

            fclose($stream);
        }, $status, $headers);
    }
}
