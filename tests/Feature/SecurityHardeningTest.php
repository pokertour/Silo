<?php

use App\Jobs\ImportIsoFromUrl;
use App\Livewire\Admin\Groups;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Users;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\DeleteUserModal;
use App\Livewire\Iso\Upload;
use App\Models\Group;
use App\Models\Invitation;
use App\Models\IsoFile;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});

function makeAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

function enableTwoFactor(User $user): string
{
    $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();

    $user->forceFill([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    return $secret;
}

// --- Authentification -------------------------------------------------------

test('les routes HTTP de Fortify sont désactivées (pas d\'inscription sans invitation)', function () {
    $this->post('/register', [
        'name' => 'Intrus',
        'email' => 'intrus@example.com',
        'password' => 'password-solide-123',
        'password_confirmation' => 'password-solide-123',
    ]);

    expect(User::where('email', 'intrus@example.com')->exists())->toBeFalse();
    $this->post('/forgot-password', ['email' => 'x@example.com'])->assertNotFound();
});

test('l\'étape 2FA ne peut pas être forcée par le client', function () {
    Livewire::test(Login::class)->set('requiresTwoFactor', true);
})->throws(CannotUpdateLockedPropertyException::class);

test('le login avec 2FA exige un code valide puis connecte l\'utilisateur', function () {
    $user = User::factory()->create(['email' => 'mfa@example.com']);
    $secret = enableTwoFactor($user);

    $component = Livewire::test(Login::class)
        ->set('email', 'mfa@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertSet('requiresTwoFactor', true);

    $this->assertGuest();

    $component->set('twoFactorCode', '000000')->call('login')->assertHasErrors('twoFactorCode');
    $this->assertGuest();

    $component->set('twoFactorCode', app(Google2FA::class)->getCurrentOtp($secret))
        ->call('login')
        ->assertHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('les tentatives de code 2FA sont limitées par compte', function () {
    $user = User::factory()->create(['email' => 'mfa@example.com']);
    $secret = enableTwoFactor($user);

    $component = Livewire::test(Login::class)
        ->set('email', 'mfa@example.com')
        ->set('password', 'password')
        ->call('login');

    foreach (range(1, 5) as $i) {
        $component->set('twoFactorCode', 'mauvais-code')->call('login');
    }

    // Même un code valide est refusé une fois la limite atteinte
    $component->set('twoFactorCode', app(Google2FA::class)->getCurrentOtp($secret))
        ->call('login')
        ->assertHasErrors('twoFactorCode');

    $this->assertGuest();
});

test('un code de récupération ne fonctionne qu\'une fois', function () {
    $user = User::factory()->create(['email' => 'mfa@example.com']);
    enableTwoFactor($user);

    Livewire::test(Login::class)
        ->set('email', 'mfa@example.com')
        ->set('password', 'password')
        ->call('login')
        ->set('twoFactorCode', 'recovery-code-1')
        ->call('login');

    $this->assertAuthenticatedAs($user);
    expect(json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true))
        ->not->toContain('recovery-code-1');
});

// --- Inscription sur invitation --------------------------------------------

test('une invitation nominative ne sert qu\'à l\'adresse invitée et une seule fois', function () {
    Invitation::create(['email' => 'invite@example.com', 'token' => 'ISO-TOKEN', 'expires_at' => now()->addDay()]);

    Livewire::test(Register::class)
        ->set('name', 'Autre')
        ->set('email', 'autre@example.com')
        ->set('password', 'password-solide-123')
        ->set('password_confirmation', 'password-solide-123')
        ->set('invitation_token', 'ISO-TOKEN')
        ->call('register')
        ->assertHasErrors('invitation_token');

    expect(User::where('email', 'autre@example.com')->exists())->toBeFalse();

    Livewire::test(Register::class)
        ->set('name', 'Invité')
        ->set('email', 'invite@example.com')
        ->set('password', 'password-solide-123')
        ->set('password_confirmation', 'password-solide-123')
        ->set('invitation_token', 'ISO-TOKEN')
        ->call('register')
        ->assertHasNoErrors();

    expect(User::where('email', 'invite@example.com')->exists())->toBeTrue();
    expect(Invitation::first()->used_at)->not->toBeNull();
});

// --- Autorisations admin ----------------------------------------------------

test('un non-admin ne peut pas ouvrir la modale de suppression d\'utilisateur', function () {
    $victim = makeAdmin();

    Livewire::actingAs(User::factory()->create())
        ->test(DeleteUserModal::class, ['userId' => $victim->id])
        ->assertForbidden();

    expect($victim->fresh())->not->toBeNull();
});

test('les composants admin refusent un non-admin', function (string $component) {
    Livewire::actingAs(User::factory()->create())
        ->test($component)
        ->assertForbidden();
})->with([Users::class, Groups::class, Settings::class]);

test('un admin supprimant un utilisateur récupère ses ISOs', function () {
    $admin = makeAdmin();
    $user = User::factory()->create();
    $iso = IsoFile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($admin)
        ->test(DeleteUserModal::class, ['userId' => $user->id])
        ->call('deleteUser');

    expect($user->fresh())->toBeNull()
        ->and($iso->fresh()->user_id)->toBe($admin->id);
});

test('un groupe contenant des ISOs ne peut pas être supprimé (ils deviendraient publics)', function () {
    $group = Group::factory()->create();
    $iso = IsoFile::factory()->create(['group_id' => $group->id]);

    Livewire::actingAs(makeAdmin())
        ->test(Groups::class)
        ->call('delete', $group->id);

    expect($group->fresh())->not->toBeNull()
        ->and($iso->fresh()->group_id)->toBe($group->id);
});

test('la configuration des stockages est chiffrée en base', function () {
    StorageLocation::create([
        'name' => 'ftp_backup',
        'driver' => 'ftp',
        'configuration' => ['host' => 'ftp.example.com', 'password' => 'super-secret'],
    ]);

    $raw = DB::table('storage_locations')->where('name', 'ftp_backup')->value('configuration');

    expect($raw)->not->toContain('super-secret')
        ->and(StorageLocation::where('name', 'ftp_backup')->first()->configuration['password'])->toBe('super-secret');
});

// --- Upload -----------------------------------------------------------------

test('un utilisateur ne peut pas finaliser le fichier temporaire d\'un autre', function () {
    Storage::fake('local');
    $storage = StorageLocation::create(['name' => 'local', 'driver' => 'local', 'is_default' => true]);
    $owner = User::factory()->create();
    $tempPath = "temp_iso/{$owner->id}_".str_repeat('a', 40);
    Storage::disk('local')->put($tempPath, 'iso');

    Livewire::actingAs(User::factory()->create())
        ->test(Upload::class)
        ->set('name', 'Vol')
        ->set('tempFilePath', $tempPath)
        ->set('storage_id', $storage->id)
        ->call('save')
        ->assertHasErrors('tempFilePath');

    Storage::disk('local')->assertExists($tempPath);
    expect(IsoFile::count())->toBe(0);
});

test('l\'import par URL refuse une redirection vers le réseau interne', function () {
    Storage::fake('local');
    Notification::fake();
    Http::fake([
        '93.184.215.14/*' => Http::response('', 302, ['Location' => 'http://127.0.0.1/secret.iso']),
        '127.0.0.1/*' => Http::response('secret'),
    ]);
    $user = User::factory()->create();

    $job = new ImportIsoFromUrl('http://93.184.215.14/file.iso', [
        'name' => 'Evil',
        'disk' => 'local',
        'user_id' => $user->id,
        'group_id' => null,
    ]);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class);
    expect(IsoFile::count())->toBe(0);
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '127.0.0.1'));
});

// --- Divers -----------------------------------------------------------------

test('le changement de langue ne redirige pas vers un site externe', function () {
    $this->get('/lang/en', ['Referer' => 'https://evil.example.com/phishing'])
        ->assertRedirect(route('dashboard'));
});

test('la suppression du compte exige le mot de passe actuel', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('profile')
        ->call('deleteAccount')
        ->assertHasErrors('password_for_delete');

    expect($user->fresh())->not->toBeNull();
});

test('le changement d\'email exige le mot de passe actuel', function () {
    $user = User::factory()->create(['email' => 'avant@example.com']);

    $this->actingAs($user);

    Volt::test('profile')
        ->set('email', 'apres@example.com')
        ->call('updateInfo')
        ->assertHasErrors('password_for_email');

    expect($user->fresh()->email)->toBe('avant@example.com');
});

test('la désactivation de la 2FA exige le mot de passe actuel', function () {
    $user = User::factory()->create();
    enableTwoFactor($user);

    $this->actingAs($user);

    Volt::test('profile')
        ->call('disable2FA')
        ->assertHasErrors('password_for_2fa');

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

test('le nom d\'un ISO ne peut pas injecter de JavaScript dans les boutons de téléchargement', function () {
    $user = User::factory()->create();
    IsoFile::factory()->create(['name' => "x'+alert(1)+'", 'group_id' => null]);

    $this->actingAs($user)
        ->get(route('isos'))
        ->assertOk()
        ->assertDontSee("filename: 'x&#039;+alert(1)", false)
        ->assertSee('x\\u0027+alert(1)+\\u0027', false);
});

test('les codes de récupération ne peuvent pas être affichés sans mot de passe', function () {
    $user = User::factory()->create();
    enableTwoFactor($user);
    $this->actingAs($user);

    Volt::test('profile')->set('showRecoveryCodes', true);
})->throws(CannotUpdateLockedPropertyException::class);
