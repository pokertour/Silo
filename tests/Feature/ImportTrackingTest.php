<?php

use App\Jobs\CalculateIsoChecksum;
use App\Jobs\ImportIsoFromUrl;
use App\Livewire\ImportsPanel;
use App\Livewire\Iso\Upload;
use App\Models\IsoFile;
use App\Models\IsoImport;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Storage::fake('local');
});

function importAttributes(User $user, string $name = 'Debian 13'): array
{
    return [
        'name' => $name,
        'version' => null,
        'architecture' => null,
        'os_type' => null,
        'description' => null,
        'expected_checksum' => null,
        'disk' => 'local',
        'user_id' => $user->id,
        'group_id' => null,
    ];
}

test('le lien du tableau de bord ouvre l\'onglet URL', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('upload', ['source' => 'url']))
        ->assertOk()
        ->assertSee('&quot;initialSource&quot;:&quot;url&quot;', false);

    $this->actingAs($user)
        ->get(route('upload'))
        ->assertOk()
        ->assertSee('&quot;initialSource&quot;:&quot;file&quot;', false);
});

test('un import depuis le formulaire crée un suivi en attente', function () {
    Queue::fake();
    $storage = StorageLocation::create(['name' => 'local', 'driver' => 'local', 'is_default' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Upload::class)
        ->set('name', 'Debian 13')
        ->set('importUrl', 'https://cdimage.debian.org/debian-13.iso')
        ->set('storage_id', $storage->id)
        ->call('importFromUrl')
        ->assertHasNoErrors();

    $import = IsoImport::sole();
    expect($import->user_id)->toBe($user->id)
        ->and($import->status)->toBe(IsoImport::PENDING)
        ->and($import->name)->toBe('Debian 13');

    Queue::assertPushed(ImportIsoFromUrl::class);
});

test('le job met à jour le suivi jusqu\'à la fin', function () {
    Queue::fake([CalculateIsoChecksum::class]);
    Notification::fake();
    Http::fake(['cdimage.debian.org/*' => Http::response('fake iso content')]);

    $user = User::factory()->create();
    $import = IsoImport::create([
        'user_id' => $user->id,
        'url' => 'https://cdimage.debian.org/debian-13.iso',
        'name' => 'Debian 13',
        'status' => IsoImport::PENDING,
    ]);

    (new ImportIsoFromUrl($import->url, importAttributes($user), [], $import->id))->handle();

    $import->refresh();
    expect($import->status)->toBe(IsoImport::COMPLETED)
        ->and($import->iso_file_id)->toBe(IsoFile::sole()->id)
        ->and($import->finished_at)->not->toBeNull()
        ->and($import->progress())->toBe(100);
});

test('un import sans suivi en crée un', function () {
    Queue::fake([CalculateIsoChecksum::class]);
    Notification::fake();
    Http::fake(['cdimage.debian.org/*' => Http::response('fake iso content')]);

    $user = User::factory()->create();

    (new ImportIsoFromUrl('https://cdimage.debian.org/debian-13.iso', importAttributes($user)))->handle();

    expect(IsoImport::sole()->status)->toBe(IsoImport::COMPLETED);
});

test('une URL interne marque l\'import en échec', function () {
    Notification::fake();
    $user = User::factory()->create();

    $job = new ImportIsoFromUrl('http://127.0.0.1/secret.iso', importAttributes($user));

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'URL refusée');

    $import = IsoImport::sole();
    expect($import->status)->toBe(IsoImport::FAILED)
        ->and($import->error)->not->toBeEmpty();
    expect(IsoFile::count())->toBe(0);
});

test('un import annulé avant le démarrage n\'est pas téléchargé', function () {
    Http::fake();
    $user = User::factory()->create();
    $import = IsoImport::create([
        'user_id' => $user->id,
        'url' => 'https://cdimage.debian.org/debian-13.iso',
        'name' => 'Debian 13',
        'status' => IsoImport::CANCELLED,
    ]);

    (new ImportIsoFromUrl($import->url, importAttributes($user), [], $import->id))->handle();

    Http::assertNothingSent();
    expect(IsoFile::count())->toBe(0)
        ->and($import->fresh()->status)->toBe(IsoImport::CANCELLED);
});

test('le panneau liste les imports de l\'utilisateur et permet d\'annuler', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $mine = IsoImport::create(['user_id' => $user->id, 'url' => 'https://a.example/a.iso', 'name' => 'Mon import', 'status' => IsoImport::DOWNLOADING]);
    IsoImport::create(['user_id' => $other->id, 'url' => 'https://b.example/b.iso', 'name' => 'Import d\'un autre', 'status' => IsoImport::DOWNLOADING]);

    Livewire::actingAs($user)
        ->test(ImportsPanel::class)
        ->assertSee('Mon import')
        ->assertDontSee('Import d\'un autre')
        ->call('cancel', $mine->id);

    expect($mine->fresh()->status)->toBe(IsoImport::CANCELLED);
});

test('un utilisateur ne peut pas agir sur l\'import d\'un autre', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $import = IsoImport::create(['user_id' => $other->id, 'url' => 'https://b.example/b.iso', 'name' => 'Autre', 'status' => IsoImport::DOWNLOADING]);

    Livewire::actingAs($user)
        ->test(ImportsPanel::class)
        ->call('cancel', $import->id)
        ->assertForbidden();

    expect($import->fresh()->status)->toBe(IsoImport::DOWNLOADING);
});

test('un import en échec peut être relancé puis masqué', function () {
    Queue::fake();
    $user = User::factory()->create();
    $import = IsoImport::create([
        'user_id' => $user->id,
        'url' => 'https://a.example/a.iso',
        'name' => 'Relance',
        'status' => IsoImport::FAILED,
        'error' => 'timeout',
        'finished_at' => now(),
        'options' => ['attributes' => importAttributes($user, 'Relance'), 'tags' => []],
    ]);

    Livewire::actingAs($user)->test(ImportsPanel::class)->call('retry', $import->id);

    expect($import->fresh()->status)->toBe(IsoImport::PENDING)
        ->and($import->fresh()->error)->toBeNull();
    Queue::assertPushed(ImportIsoFromUrl::class);

    $import->fresh()->update(['status' => IsoImport::FAILED, 'finished_at' => now()]);
    Livewire::actingAs($user)->test(ImportsPanel::class)->call('dismiss', $import->id)->assertDontSee('Relance');

    expect($import->fresh()->dismissed_at)->not->toBeNull();
});
