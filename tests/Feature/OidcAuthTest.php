<?php

use App\Livewire\Admin\Settings;
use App\Models\User;
use App\Support\OidcSettings;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

const IDP = 'https://idp.example.com/realms/main';

function enableOidc(array $overrides = []): void
{
    OidcSettings::save(array_merge([
        'enabled' => true,
        'label' => 'Keycloak',
        'issuer' => IDP,
        'client_id' => 'silo',
        'auto_create' => true,
    ], $overrides), ['client_secret' => 'client-secret']);
}

function b64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function idToken(array $claims): string
{
    return b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.b64url(json_encode($claims)).'.'.b64url('signature');
}

function fakeIdp(array $claims = [], array $userinfo = []): void
{
    $claims = array_merge([
        'iss' => IDP,
        'aud' => 'silo',
        'sub' => 'user-123',
        'exp' => now()->addMinutes(5)->getTimestamp(),
        'nonce' => 'the-nonce',
        'email' => 'Jane@Example.com',
        'email_verified' => true,
        'name' => 'Jane Doe',
    ], $claims);

    Http::fake([
        IDP.'/.well-known/openid-configuration' => Http::response([
            'issuer' => IDP,
            'authorization_endpoint' => IDP.'/protocol/openid-connect/auth',
            'token_endpoint' => IDP.'/protocol/openid-connect/token',
            'userinfo_endpoint' => IDP.'/protocol/openid-connect/userinfo',
        ]),
        IDP.'/protocol/openid-connect/token' => Http::response([
            'access_token' => 'access-token',
            'token_type' => 'Bearer',
            'id_token' => idToken($claims),
        ]),
        IDP.'/protocol/openid-connect/userinfo' => Http::response(array_merge(['sub' => $claims['sub']], $userinfo)),
    ]);
}

function ssoFlow(array $overrides = []): array
{
    return ['oidc.flow' => array_merge([
        'state' => 'the-state',
        'nonce' => 'the-nonce',
        'verifier' => 'the-verifier',
        'expires_at' => now()->addMinutes(5)->getTimestamp(),
    ], $overrides)];
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('le bouton SSO n\'apparaît que si le SSO est configuré', function () {
    $this->get(route('login'))->assertDontSee('Keycloak');

    enableOidc();

    $this->get(route('login'))->assertSee('Keycloak')->assertSee(route('sso.redirect'), false);
});

test('les routes SSO sont désactivées par défaut', function () {
    $this->get(route('sso.redirect'))->assertNotFound();
    $this->get(route('sso.callback'))->assertNotFound();
});

test('la redirection envoie vers le fournisseur avec state, nonce et PKCE', function () {
    enableOidc();
    fakeIdp();

    $response = $this->get(route('sso.redirect'));

    $response->assertRedirect();
    $location = $response->headers->get('Location');
    expect($location)->toStartWith(IDP.'/protocol/openid-connect/auth?');

    parse_str(parse_url($location, PHP_URL_QUERY), $query);
    $flow = session('oidc.flow');
    expect($query['client_id'])->toBe('silo')
        ->and($query['redirect_uri'])->toBe(route('sso.callback'))
        ->and($query['state'])->toBe($flow['state'])
        ->and($query['nonce'])->toBe($flow['nonce'])
        ->and($query['code_challenge_method'])->toBe('S256')
        ->and($query['code_challenge'])->toBe(b64url(hash('sha256', $flow['verifier'], true)));
});

test('le retour du fournisseur connecte et crée l\'utilisateur', function () {
    enableOidc();
    fakeIdp();

    $this->withSession(ssoFlow())
        ->get(route('sso.callback', ['code' => 'auth-code', 'state' => 'the-state']))
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'jane@example.com')->sole();
    expect($user->auth_provider)->toBe('oidc')
        ->and($user->external_id)->toBe('user-123')
        ->and($user->name)->toBe('Jane Doe');
    $this->assertAuthenticatedAs($user);

    Http::assertSent(fn ($request) => $request->url() === IDP.'/protocol/openid-connect/token'
        && $request['code'] === 'auth-code'
        && $request['code_verifier'] === 'the-verifier'
        && $request['client_secret'] === 'client-secret');
});

test('un state invalide est refusé', function () {
    enableOidc();
    fakeIdp();

    $this->withSession(ssoFlow())
        ->get(route('sso.callback', ['code' => 'auth-code', 'state' => 'forged']))
        ->assertRedirect(route('login'))
        ->assertSessionHas('sso_error');

    $this->assertGuest();
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/token'));
});

test('un nonce ou une audience invalide sont refusés', function (array $claims) {
    enableOidc();
    fakeIdp($claims);

    $this->withSession(ssoFlow())
        ->get(route('sso.callback', ['code' => 'auth-code', 'state' => 'the-state']))
        ->assertSessionHas('sso_error');

    $this->assertGuest();
})->with([
    'nonce' => [['nonce' => 'other']],
    'audience' => [['aud' => 'another-client']],
    'émetteur' => [['iss' => 'https://evil.example.com']],
    'expiré' => [['exp' => now()->subHour()->getTimestamp()]],
]);

test('une adresse non vérifiée est refusée', function () {
    enableOidc();
    fakeIdp(['email_verified' => false]);

    $this->withSession(ssoFlow())->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))->assertSessionHas('sso_error');

    $this->assertGuest();
});

test('les domaines autorisés sont respectés', function () {
    enableOidc(['allowed_domains' => 'corp.example.com']);
    fakeIdp();

    $this->withSession(ssoFlow())->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))->assertSessionHas('sso_error');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('sans création automatique, un compte existant est associé par e-mail', function () {
    enableOidc(['auto_create' => false]);
    fakeIdp();

    $this->withSession(ssoFlow())->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))->assertSessionHas('sso_error');
    $this->assertGuest();

    $jane = User::factory()->create(['email' => 'jane@example.com']);
    $this->withSession(ssoFlow())->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($jane);
});

test('le claim de groupe donne le rôle admin', function () {
    enableOidc(['admin_claim' => 'groups', 'admin_value' => 'silo-admins']);
    fakeIdp(userinfo: ['groups' => ['staff', 'silo-admins']]);

    $this->withSession(ssoFlow())->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))->assertRedirect(route('dashboard'));

    expect(User::where('email', 'jane@example.com')->sole()->hasRole('admin'))->toBeTrue();
});

test('la 2FA de Silo est demandée après le SSO si elle est activée', function () {
    enableOidc();
    fakeIdp();
    User::factory()->create(['email' => 'jane@example.com'])
        ->forceFill(['two_factor_secret' => encrypt('SECRET'), 'two_factor_confirmed_at' => now()])->save();

    $this->withSession(ssoFlow())
        ->get(route('sso.callback', ['code' => 'c', 'state' => 'the-state']))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    expect(session('login.2fa_pending'))->not->toBeNull();
});

test('l\'admin enregistre la configuration SSO, secret chiffré', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire\Livewire::actingAs($admin)
        ->test(Settings::class)
        ->set('oidc.enabled', true)
        ->set('oidc.issuer', IDP)
        ->set('oidc.client_id', 'silo')
        ->set('oidc_client_secret', 's3cret')
        ->call('saveOidc')
        ->assertHasNoErrors()
        ->assertSet('oidc_client_secret_set', true);

    expect(OidcSettings::enabled())->toBeTrue()
        ->and(OidcSettings::secret('client_secret'))->toBe('s3cret')
        ->and(OidcSettings::stored()['client_secret'])->not->toBe('s3cret');
});
