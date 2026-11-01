<?php

namespace App\Jobs;

use App\Livewire\Iso\Upload;
use App\Models\IsoFile;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\IsoImportFinished;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportIsoFromUrl implements ShouldQueue
{
    use Queueable;

    public $timeout = 7200;

    public $tries = 1;

    private const MAX_REDIRECTS = 5;

    /**
     * @param  array  $attributes  Métadonnées du futur IsoFile (name, disk, user_id, group_id…)
     * @param  array  $tags  Noms de tags à associer
     */
    public function __construct(
        public string $url,
        public array $attributes,
        public array $tags = [],
    ) {}

    public function handle(): void
    {
        $user = User::find($this->attributes['user_id']);

        try {
            // Téléchargement en streaming vers un fichier temporaire local
            $tempName = 'temp_iso/import_'.Str::random(40);
            Storage::disk('local')->makeDirectory('temp_iso');
            $absoluteTemp = Storage::disk('local')->path($tempName);

            $finalUrl = $this->download($this->url, $absoluteTemp);

            $size = filesize($absoluteTemp);
            if (! $size) {
                throw new \RuntimeException('Le fichier téléchargé est vide.');
            }

            // Extension depuis l'URL, contrainte à la même whitelist que l'upload
            $extension = strtolower(pathinfo(parse_url($finalUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            if (! in_array($extension, Upload::ALLOWED_EXTENSIONS, true)) {
                $extension = 'iso';
            }

            $finalPath = 'iso_files/'.basename($tempName).'.'.$extension;
            $in = fopen($absoluteTemp, 'rb');
            Storage::disk($this->attributes['disk'])->writeStream($finalPath, $in);
            fclose($in);
            unlink($absoluteTemp);

            $isoFile = IsoFile::create([
                ...$this->attributes,
                'file_path' => $finalPath,
                'size' => $size,
                'checksum' => null,
            ]);

            if (! empty($this->tags)) {
                $tagIds = collect($this->tags)
                    ->map(fn ($name) => Tag::firstOrCreate(
                        ['name' => $name],
                        ['color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))]
                    )->id)
                    ->all();
                $isoFile->tags()->sync($tagIds);
            }

            CalculateIsoChecksum::dispatch($isoFile);

            $user?->notify(new IsoImportFinished(true, $this->attributes['name'], $isoFile->id));
        } catch (\Throwable $e) {
            if (isset($absoluteTemp) && file_exists($absoluteTemp)) {
                unlink($absoluteTemp);
            }

            $user?->notify(new IsoImportFinished(false, $this->attributes['name'], null, $e->getMessage()));

            throw $e;
        }
    }

    /**
     * Télécharge l'URL dans $destination en suivant au plus MAX_REDIRECTS
     * redirections. Chaque saut est revalidé (protection SSRF) et la connexion
     * est épinglée sur l'IP vérifiée, ce qui empêche le DNS rebinding.
     *
     * @return string L'URL finale effectivement téléchargée
     */
    private function download(string $url, string $destination): string
    {
        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            [$host, $port, $ip] = $this->guardAgainstSsrf($url);

            $response = Http::timeout($this->timeout)
                ->withOptions([
                    'sink' => $destination,
                    'allow_redirects' => false,
                    'curl' => [
                        CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"],
                    ],
                ])
                ->get($url);

            if ($response->redirect()) {
                $location = $response->header('Location');
                if ($location === '') {
                    throw new \RuntimeException('Redirection sans destination.');
                }

                $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                continue;
            }

            if (! $response->successful()) {
                throw new \RuntimeException("Le serveur distant a répondu {$response->status()}.");
            }

            return $url;
        }

        throw new \RuntimeException('Trop de redirections.');
    }

    /**
     * Refuse les URLs pointant vers le réseau interne (protection SSRF).
     *
     * @return array{0: string, 1: int, 2: string} [hôte, port, IP validée]
     */
    private function guardAgainstSsrf(string $url): array
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new \RuntimeException('URL invalide : seuls http et https sont acceptés.');
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \RuntimeException('URL invalide : identifiants intégrés non autorisés.');
        }

        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        // Résolution IPv4 uniquement (la connexion est ensuite épinglée sur cette IP)
        $ips = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? [$host] : (gethostbynamel($host) ?: []);

        if ($ips === []) {
            throw new \RuntimeException('URL refusée : hôte introuvable ou non IPv4.');
        }

        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                throw new \RuntimeException('URL refusée : adresse interne ou réservée.');
            }
        }

        return [$host, $port, $ips[0]];
    }

    private function isPublicIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        // Plages non couvertes par les filtres PHP : CGNAT (100.64.0.0/10)
        // et 0.0.0.0/8, souvent routées vers l'hôte ou le réseau interne.
        $long = ip2long($ip);

        return ! (($long & 0xFFC00000) === (ip2long('100.64.0.0') & 0xFFC00000)
            || ($long & 0xFF000000) === 0);
    }
}
