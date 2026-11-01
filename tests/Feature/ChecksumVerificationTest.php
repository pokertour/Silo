<?php

use App\Jobs\CalculateIsoChecksum;
use App\Livewire\IsoList;
use App\Models\IsoFile;
use App\Models\ShareLink;
use App\Models\User;
use App\Notifications\ChecksumMismatch;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('local');
});

test('un checksum attendu conforme marque le fichier comme vérifié', function () {
    Storage::disk('local')->put('iso_files/a.iso', 'trusted content');
    $iso = IsoFile::factory()->create([
        'file_path' => 'iso_files/a.iso',
        'expected_checksum' => hash('sha256', 'trusted content'),
    ]);

    (new CalculateIsoChecksum($iso))->handle();

    expect($iso->fresh()->checksum_verified)->toBeTrue();
});

test('un checksum attendu non conforme déclenche une alerte', function () {
    Notification::fake();

    Storage::disk('local')->put('iso_files/a.iso', 'tampered content');
    $iso = IsoFile::factory()->create([
        'file_path' => 'iso_files/a.iso',
        'expected_checksum' => hash('sha256', 'original content'),
    ]);

    (new CalculateIsoChecksum($iso))->handle();

    expect($iso->fresh()->checksum_verified)->toBeFalse();
    Notification::assertSentTo($iso->user, ChecksumMismatch::class);
});

test('sans checksum attendu, le statut de vérification reste nul', function () {
    Storage::disk('local')->put('iso_files/a.iso', 'whatever');
    $iso = IsoFile::factory()->create(['file_path' => 'iso_files/a.iso']);

    (new CalculateIsoChecksum($iso))->handle();

    expect($iso->fresh()->checksum_verified)->toBeNull();
});

test('la vérification du mot de passe de partage est limitée en fréquence', function () {
    $share = ShareLink::factory()->create(['password' => 'secret123']);

    foreach (range(1, 5) as $i) {
        $this->post(route('share.verify', $share->token), ['password' => 'wrong']);
    }

    $this->post(route('share.verify', $share->token), ['password' => 'wrong'])
        ->assertStatus(429);
});

test('la recherche par checksum trouve le fichier exact', function () {
    $user = User::factory()->create();
    Role::firstOrCreate(['name' => 'admin']);
    $hash = hash('sha256', 'findme');
    $target = IsoFile::factory()->create(['name' => 'Target ISO', 'checksum' => $hash]);
    IsoFile::factory()->create(['name' => 'Other ISO']);

    Livewire\Livewire::actingAs($user)
        ->test(IsoList::class)
        ->set('search', $hash)
        ->assertSee('Target ISO')
        ->assertDontSee('Other ISO');
});
