<?php

namespace App\Auth\Oidc;

use App\Auth\ExternalAuthException;
use App\Support\OidcSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Flux « authorization code » OpenID Connect (avec PKCE), compatible OAuth 2
 * générique quand les points de terminaison sont saisis manuellement.
 *
 * L'ID token est reçu directement du point de terminaison « token » via TLS :
 * conformément à OpenID Connect Core §3.1.3.7, la validation TLS du serveur
 * tient lieu de vérification de signature. On contrôle iss, aud, exp et nonce.
 */
class OidcClient
{
    private const SESSION_KEY = 'oidc.flow';

    private const FLOW_TTL = 600; // secondes

    /** Paramètres complets, secret client déchiffré. */
    public function settings(): array
    {
        return array_merge(OidcSettings::stored(), ['client_secret' => OidcSettings::secret('client_secret')]);
    }

    public static function redirectUri(): string
    {
        return route('sso.callback');
    }

    /**
     * Points de terminaison : saisis manuellement, sinon découverts.
     *
     * @return array{authorization_endpoint: string, token_endpoint: string, userinfo_endpoint: ?string, issuer: ?string}
     */
    public function endpoints(?array $settings = null, bool $fresh = false): array
    {
        $settings ??= $this->settings();
        $discovered = [];

        if ($settings['issuer'] !== '') {
            $issuer = rtrim($settings['issuer'], '/');
            $cacheKey = 'oidc.discovery.'.sha1($issuer);

            if ($fresh) {
                Cache::forget($cacheKey);
            }

            $discovered = Cache::remember($cacheKey, 3600, function () use ($issuer) {
                $response = Http::timeout(10)->acceptJson()->get($issuer.'/.well-known/openid-configuration');

                if (! $response->successful() || ! is_array($response->json())) {
                    throw new RuntimeException("Découverte OpenID impossible ({$response->status()}).");
                }

                return $response->json();
            });
        }

        $endpoints = [
            'authorization_endpoint' => $settings['authorize_url'] ?: ($discovered['authorization_endpoint'] ?? null),
            'token_endpoint' => $settings['token_url'] ?: ($discovered['token_endpoint'] ?? null),
            'userinfo_endpoint' => $settings['userinfo_url'] ?: ($discovered['userinfo_endpoint'] ?? null),
            'issuer' => $discovered['issuer'] ?? null,
        ];

        foreach (['authorization_endpoint', 'token_endpoint'] as $required) {
            if (! is_string($endpoints[$required]) || ! Str::startsWith($endpoints[$required], ['https://', 'http://'])) {
                throw new RuntimeException("Point de terminaison « {$required} » introuvable.");
            }
        }

        return $endpoints;
    }

    /** URL vers laquelle rediriger le navigateur ; mémorise state, nonce et PKCE en session. */
    public function authorizationUrl(): string
    {
        $settings = $this->settings();
        $endpoints = $this->endpoints($settings);

        $state = Str::random(40);
        $nonce = Str::random(40);
        $verifier = Str::random(64);

        session()->put(self::SESSION_KEY, [
            'state' => $state,
            'nonce' => $nonce,
            'verifier' => $verifier,
            'expires_at' => now()->addSeconds(self::FLOW_TTL)->getTimestamp(),
        ]);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $settings['client_id'],
            'redirect_uri' => self::redirectUri(),
            'scope' => trim($settings['scopes']) ?: 'openid email profile',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        $url = $endpoints['authorization_endpoint'];

        return $url.(str_contains($url, '?') ? '&' : '?').$query;
    }

    /**
     * Termine le flux à partir des paramètres du retour.
     *
     * @return array{sub: string, email: ?string, email_verified: ?bool, name: ?string, claims: array}
     *
     * @throws ExternalAuthException
     */
    public function handleCallback(array $query): array
    {
        $flow = session()->pull(self::SESSION_KEY);

        if (isset($query['error'])) {
            throw new ExternalAuthException(__('app.Le fournisseur d\'identité a refusé la connexion.').' ('.Str::limit((string) $query['error'], 60).')');
        }

        if (! is_array($flow) || ($flow['expires_at'] ?? 0) < now()->getTimestamp()
            || ! is_string($query['state'] ?? null) || ! hash_equals($flow['state'], $query['state'])) {
            throw new ExternalAuthException(__('app.Session de connexion expirée ou invalide. Réessayez.'));
        }

        if (! is_string($query['code'] ?? null) || $query['code'] === '') {
            throw new ExternalAuthException(__('app.Réponse du fournisseur d\'identité invalide.'));
        }

        $settings = $this->settings();

        try {
            $endpoints = $this->endpoints($settings);

            $response = Http::timeout(15)->acceptJson()->asForm()->post($endpoints['token_endpoint'], [
                'grant_type' => 'authorization_code',
                'code' => $query['code'],
                'redirect_uri' => self::redirectUri(),
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret'],
                'code_verifier' => $flow['verifier'],
            ]);

            if (! $response->successful() || ! is_string($response->json('access_token'))) {
                throw new RuntimeException("Échange du code refusé ({$response->status()}) : ".Str::limit($response->body(), 300));
            }
        } catch (\Throwable $e) {
            report($e);

            throw new ExternalAuthException(__('app.Impossible de joindre le fournisseur d\'identité.'));
        }

        $claims = [];

        if (is_string($idToken = $response->json('id_token'))) {
            $claims = $this->validateIdToken($idToken, $settings, $endpoints, $flow['nonce']);
        }

        if ($endpoints['userinfo_endpoint']) {
            try {
                $userinfo = Http::timeout(10)->acceptJson()->withToken($response->json('access_token'))->get($endpoints['userinfo_endpoint']);
            } catch (\Throwable $e) {
                report($e);
                $userinfo = null;
            }

            if ($userinfo?->successful() && is_array($userinfo->json())) {
                $info = $userinfo->json();
                // Le « sub » de userinfo doit correspondre à celui de l'ID token
                if (isset($claims['sub'], $info['sub']) && (string) $claims['sub'] !== (string) $info['sub']) {
                    throw new ExternalAuthException(__('app.Réponse du fournisseur d\'identité invalide.'));
                }
                $claims = array_merge($info, $claims);
            }
        }

        // OAuth 2 sans OpenID (ex. GitHub) : « id » à la place de « sub »
        $sub = $claims['sub'] ?? $claims['id'] ?? null;
        if ($sub === null || $sub === '') {
            throw new ExternalAuthException(__('app.Réponse du fournisseur d\'identité invalide.'));
        }

        $verified = $claims['email_verified'] ?? null;

        return [
            'sub' => (string) $sub,
            'email' => $this->claim($claims, $settings['email_claim'] ?: 'email'),
            'email_verified' => $verified === null ? null : filter_var($verified, FILTER_VALIDATE_BOOLEAN),
            'name' => $this->claim($claims, $settings['name_claim'] ?: 'name') ?? ($claims['preferred_username'] ?? null),
            'claims' => $claims,
        ];
    }

    private function validateIdToken(string $idToken, array $settings, array $endpoints, string $nonce): array
    {
        $parts = explode('.', $idToken);
        $payload = count($parts) === 3 ? json_decode($this->base64UrlDecode($parts[1]), true) : null;

        $invalid = fn () => new ExternalAuthException(__('app.Réponse du fournisseur d\'identité invalide.'));

        if (! is_array($payload)) {
            throw $invalid();
        }

        $expectedIssuer = $endpoints['issuer'] ?? null;
        if ($expectedIssuer && rtrim((string) ($payload['iss'] ?? ''), '/') !== rtrim($expectedIssuer, '/')) {
            throw $invalid();
        }

        $audiences = (array) ($payload['aud'] ?? []);
        if (! in_array($settings['client_id'], $audiences, true)) {
            throw $invalid();
        }

        $leeway = 60;
        if (! isset($payload['exp']) || (int) $payload['exp'] < now()->getTimestamp() - $leeway) {
            throw $invalid();
        }

        if (! isset($payload['nonce']) || ! hash_equals($nonce, (string) $payload['nonce'])) {
            throw $invalid();
        }

        return $payload;
    }

    private function claim(array $claims, string $path): ?string
    {
        $value = Arr::get($claims, $path);

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }

    private function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4));
    }
}
