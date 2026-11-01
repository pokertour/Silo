<?php

use App\Auth\Ldap\LdapClient;
use App\Livewire\Admin\Settings;
use App\Livewire\Auth\Login;
use App\Models\User;
use App\Support\LdapSettings;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class FakeLdapClient implements LdapClient
{
    public array $calls = [];

    /** @param  array<string, array{password: string, dn: string, email: ?string, name: ?string, groups?: list<string>}>  $users */
    public function __construct(private array $users = [], private bool $down = false) {}

    public function authenticate(array $settings, string $login, string $password): ?array
    {
        $this->calls[] = $login;

        if ($this->down) {
            throw new RuntimeException('Can\'t contact LDAP server');
        }

        $user = $this->users[$login] ?? null;
        if (! $user || $password === '' || ! hash_equals($user['password'], $password)) {
            return null;
        }

        return ['dn' => $user['dn'], 'email' => $user['email'], 'name' => $user['name'], 'groups' => $user['groups'] ?? []];
    }

    public function test(array $settings): int
    {
        return count($this->users);
    }
}

function enableLdap(array $overrides = []): void
{
    LdapSettings::save(array_merge([
        'enabled' => true,
        'host' => 'ldap.test',
        'base_dn' => 'dc=example,dc=com',
        'auto_create' => true,
    ], $overrides));
}

function fakeLdap(array $users = [], bool $down = false): FakeLdapClient
{
    $client = new FakeLdapClient($users, $down);
    app()->instance(LdapClient::class, $client);

    return $client;
}

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

test('un utilisateur de l\'annuaire se connecte avec son identifiant et son compte est créé', function () {
    enableLdap();
    fakeLdap(['jdoe' => ['password' => 'secret', 'dn' => 'uid=jdoe,dc=example,dc=com', 'email' => 'John.Doe@example.com', 'name' => 'John Doe']]);

    Livewire::test(Login::class)
        ->set('email', 'jdoe')
        ->set('password', 'secret')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'john.doe@example.com')->sole();
    expect($user->auth_provider)->toBe('ldap')
        ->and($user->external_id)->toBe('uid=jdoe,dc=example,dc=com')
        ->and($user->name)->toBe('John Doe');
    $this->assertAuthenticatedAs($user);
});

test('un mauvais mot de passe LDAP est refusé', function () {
    enableLdap();
    fakeLdap(['jdoe' => ['password' => 'secret', 'dn' => 'uid=jdoe,dc=example,dc=com', 'email' => 'jdoe@example.com', 'name' => 'John']]);

    Livewire::test(Login::class)
        ->set('email', 'jdoe')
        ->set('password', 'wrong')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('sans création automatique, seul un compte existant peut se connecter', function () {
    enableLdap(['auto_create' => false]);
    fakeLdap([
        'jdoe' => ['password' => 'secret', 'dn' => 'uid=jdoe,dc=example,dc=com', 'email' => 'jdoe@example.com', 'name' => 'John'],
        'alice' => ['password' => 'secret', 'dn' => 'uid=alice,dc=example,dc=com', 'email' => 'alice@example.com', 'name' => 'Alice'],
    ]);
    $alice = User::factory()->create(['email' => 'alice@example.com']);

    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login')->assertHasErrors('email');
    $this->assertGuest();

    Livewire::test(Login::class)->set('email', 'alice')->set('password', 'secret')->call('login')->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($alice);
});

test('les comptes locaux fonctionnent toujours sans interroger l\'annuaire', function () {
    enableLdap();
    $ldap = fakeLdap();
    $user = User::factory()->create(['email' => 'local@example.com', 'password' => 'password']);

    Livewire::test(Login::class)->set('email', 'local@example.com')->set('password', 'password')->call('login')->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($ldap->calls)->toBe([]);
});

test('le mot de passe local d\'un compte LDAP n\'est pas accepté', function () {
    enableLdap();
    fakeLdap();
    User::factory()->create(['email' => 'jdoe@example.com', 'password' => 'local-password', 'auth_provider' => 'ldap', 'external_id' => 'uid=jdoe,dc=example,dc=com']);

    Livewire::test(Login::class)->set('email', 'jdoe@example.com')->set('password', 'local-password')->call('login')->assertHasErrors('email');

    $this->assertGuest();
});

test('un annuaire injoignable affiche une erreur claire', function () {
    enableLdap();
    fakeLdap(down: true);

    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login')->assertHasErrors('email');

    $this->assertGuest();
});

test('le groupe administrateur donne et retire le rôle admin', function () {
    enableLdap(['admin_group' => 'cn=silo-admins,ou=groups,dc=example,dc=com']);
    $entry = ['password' => 'secret', 'dn' => 'uid=jdoe,dc=example,dc=com', 'email' => 'jdoe@example.com', 'name' => 'John', 'groups' => ['CN=silo-admins,ou=groups,dc=example,dc=com']];
    fakeLdap(['jdoe' => $entry]);

    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login');
    $user = User::where('email', 'jdoe@example.com')->sole();
    expect($user->hasRole('admin'))->toBeTrue();

    auth()->logout();
    fakeLdap(['jdoe' => array_merge($entry, ['groups' => []])]);
    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login');
    expect($user->fresh()->hasRole('admin'))->toBeFalse();
});

test('la 2FA de Silo reste exigée pour un compte LDAP', function () {
    enableLdap();
    fakeLdap(['jdoe' => ['password' => 'secret', 'dn' => 'uid=jdoe,dc=example,dc=com', 'email' => 'jdoe@example.com', 'name' => 'John']]);
    User::factory()->create([
        'email' => 'jdoe@example.com',
        'auth_provider' => 'ldap',
        'external_id' => 'uid=jdoe,dc=example,dc=com',
    ])->forceFill(['two_factor_secret' => encrypt('SECRET'), 'two_factor_confirmed_at' => now()])->save();

    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login')->assertSet('requiresTwoFactor', true);

    $this->assertGuest();
});

test('sans LDAP, l\'identifiant doit être une adresse e-mail', function () {
    Livewire::test(Login::class)->set('email', 'jdoe')->set('password', 'secret')->call('login')->assertHasErrors(['email' => 'email']);
});

test('l\'admin enregistre la configuration LDAP, mot de passe de service chiffré', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(Settings::class)
        ->set('ldap.enabled', true)
        ->set('ldap.host', 'ldap.example.com')
        ->set('ldap.base_dn', 'dc=example,dc=com')
        ->set('ldap.bind_dn', 'cn=silo,dc=example,dc=com')
        ->set('ldap_bind_password', 'bind-secret')
        ->call('saveLdap')
        ->assertHasNoErrors()
        ->assertSet('ldap_bind_password_set', true);

    expect(LdapSettings::enabled())->toBeTrue()
        ->and(LdapSettings::stored()['bind_password'])->not->toBe('bind-secret')
        ->and(LdapSettings::secret('bind_password'))->toBe('bind-secret');
});

test('le filtre LDAP doit contenir {login}', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(Settings::class)
        ->set('ldap.user_filter', '(objectClass=person)')
        ->call('saveLdap')
        ->assertHasErrors('ldap.user_filter');
});
