<?php

use App\Jobs\CalculateIsoChecksum;
use App\Jobs\ImportIsoFromUrl;
use App\Livewire\Iso\Upload;
use App\Models\IsoFile;
use App\Models\StorageLocation;
use App\Models\User;
use App\Notifications\IsoImportFinished;
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

test('le composant upload dispatche le job d\'import', function () {
    Queue::fake();
    $storage = StorageLocation::create(['name' => 'local', 'driver' => 'local', 'is_default' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Upload::class)
        ->set('name', 'Ubuntu Server')
        ->set('importUrl', 'https://releases.ubuntu.com/ubuntu.iso')
        ->set('storage_id', $storage->id)
        ->call('importFromUrl')
        ->assertHasNoErrors();

    Queue::assertPushed(ImportIsoFromUrl::class);
});

test('une URL invalide est rejetée', function () {
    Queue::fake();
    $storage = StorageLocation::create(['name' => 'local', 'driver' => 'local', 'is_default' => true]);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Upload::class)
        ->set('name', 'Test')
        ->set('importUrl', 'ftp://example.com/file.iso')
        ->set('storage_id', $storage->id)
        ->call('importFromUrl')
        ->assertHasErrors('importUrl');

    Queue::assertNothingPushed();
});

test('le job télécharge le fichier et crée l\'IsoFile', function () {
    Queue::fake([CalculateIsoChecksum::class]);
    Notification::fake();
    Http::fake(['releases.ubuntu.com/*' => Http::response('fake iso content')]);

    $user = User::factory()->create();

    (new ImportIsoFromUrl('https://releases.ubuntu.com/ubuntu-24.04.iso', [
        'name' => 'Ubuntu 24.04',
        'version' => null,
        'architecture' => null,
        'os_type' => null,
        'description' => null,
        'expected_checksum' => null,
        'disk' => 'local',
        'user_id' => $user->id,
        'group_id' => null,
    ]))->handle();

    $iso = IsoFile::where('name', 'Ubuntu 24.04')->first();
    expect($iso)->not->toBeNull()
        ->and($iso->size)->toBeGreaterThan(0);
    Storage::disk('local')->assertExists($iso->file_path);
    Queue::assertPushed(CalculateIsoChecksum::class);
    Notification::assertSentTo($user, IsoImportFinished::class);
});

test('une URL vers une adresse interne est refusée (SSRF)', function () {
    Notification::fake();
    $user = User::factory()->create();

    $job = new ImportIsoFromUrl('http://192.168.1.1/secret.iso', [
        'name' => 'Evil',
        'version' => null,
        'architecture' => null,
        'os_type' => null,
        'description' => null,
        'expected_checksum' => null,
        'disk' => 'local',
        'user_id' => $user->id,
        'group_id' => null,
    ]);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class);
    expect(IsoFile::count())->toBe(0);
    Notification::assertSentTo($user, IsoImportFinished::class);
});
